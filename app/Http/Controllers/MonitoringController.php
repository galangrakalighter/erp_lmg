<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSession;
use App\Models\MonitoringSignal;
use App\Models\MonitoringAccess;
use App\Models\User;
use App\Services\FirebaseCustomTokenService;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        $availableTargets = $viewer->isAdmin
            ? collect()
            : $viewer->monitoringTargets()
                ->with(['userDivisions.division', 'userDivisions.role'])
                ->withCount(['monitoringSessions as active_monitoring_count' => fn ($query) => $query->where('status', 'active')])
                ->orderBy('email')
                ->get(['users.id', 'name', 'email', 'photo_profile']);

        if (! $request->filled('user') && ($viewer->isAdmin || $availableTargets->isNotEmpty())) {
            $users = $viewer->isAdmin
                ? User::query()
                    ->with(['userDivisions.division', 'userDivisions.role'])
                    ->withCount(['monitoringSessions as active_monitoring_count' => fn ($query) => $query->where('status', 'active')])
                    ->orderBy('email')
                    ->get(['id', 'name', 'email', 'photo_profile'])
                : $availableTargets;

            return view('monitoring.directory', [
                'users' => $users,
                'canManage' => (bool) $viewer->isAdmin,
                'canShareOwnScreen' => $this->canShare($viewer),
                'currentUser' => $viewer,
            ]);
        }
        $requestedTargetId = $request->integer('user');
        $target = $requestedTargetId ? User::query()->findOrFail($requestedTargetId) : $viewer;
        $isSelf = (int) $target->id === (int) $viewer->id;
        $canViewTarget = (bool) $viewer->isAdmin || $availableTargets->contains('id', $target->id);
        abort_unless($isSelf || $canViewTarget, 403, 'Anda tidak memiliki akses untuk memantau pengguna ini.');
        $activeSession = MonitoringSession::query()->where('target_user_id', $target->id)->where('status', 'active')->latest('started_at')->first();

        return view('monitoring.index', [
            'title' => 'Monitoring',
            'users' => $availableTargets,
            'availableTargets' => $availableTargets,
            'target' => $target,
            'activeSession' => $activeSession,
            'canStart' => $isSelf && $this->canShare($viewer),
            'canView' => ! $isSelf && $canViewTarget,
            'firebaseWebConfig' => array_filter(config('firestore.web', [])),
        ]);
    }

    public function start(Request $request)
    {
        $request->validate(['consent' => ['accepted']]);
        $user = $request->user();
        abort_unless($this->canShare($user), 403, 'Akses untuk membagikan layar belum diizinkan oleh administrator.');
        MonitoringSession::query()->where('target_user_id', $user->id)->where('status', 'active')->update(['status' => 'ended', 'ended_at' => now()]);
        $session = MonitoringSession::create(['target_user_id' => $user->id, 'status' => 'active', 'consented_at' => now(), 'started_at' => now()]);

        // The document is created by the server so browser users cannot
        // impersonate another target or open an arbitrary monitoring room.
        app(FirestoreService::class)->set('monitoring_sessions', (string) $session->id, [
            'target_uid' => 'erp_'.$user->id,
            'status' => 'active',
            'started_at' => now(),
            'viewer_uids' => [],
        ]);

        return response()->json(['session' => ['id' => $session->id]]);
    }

    public function join(Request $request, MonitoringSession $session)
    {
        abort_unless($this->canView($request->user(), $session), 403);
        abort_unless($session->status === 'active', 409, 'Sesi monitoring sudah berakhir.');
        abort_if((int) $session->target_user_id === (int) $request->user()->id, 422);
        // A target may be observed by more than one admin. Every admin has a
        // separate WebRTC peer connection and receives only their own signals.
        MonitoringSignal::create([
            'monitoring_session_id' => $session->id,
            'sender_user_id' => $request->user()->id,
            'recipient_user_id' => $session->target_user_id,
            'type' => 'ready',
            'payload' => [],
        ]);
        $firestore = app(FirestoreService::class);
        $liveRoom = $firestore->get('monitoring_sessions', (string) $session->id);
        $viewerUids = array_values(array_unique(array_merge(
            $liveRoom['data']['viewer_uids'] ?? [],
            ['erp_'.$request->user()->id],
        )));
        $firestore->set('monitoring_sessions', (string) $session->id, [
            'target_uid' => 'erp_'.$session->target_user_id,
            'status' => 'active',
            'started_at' => $session->started_at,
            'viewer_uids' => $viewerUids,
        ]);

        return response()->json(['session' => ['id' => $session->id]]);
    }

    public function active(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin || $request->user()->monitoringTargets()->whereKey($user->id)->exists(), 403);

        $session = MonitoringSession::query()
            ->where('target_user_id', $user->id)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        return response()->json([
            'session' => $session ? ['id' => $session->id] : null,
        ]);
    }

    /** Return a Firebase custom token only to an authorised session participant. */
    public function firebaseToken(Request $request, MonitoringSession $session, FirebaseCustomTokenService $tokens)
    {
        $this->authorizeParticipant($request->user(), $session);
        abort_unless($session->status === 'active', 409, 'Sesi monitoring sudah berakhir.');

        return response()->json([
            'token' => $tokens->forUser((int) $request->user()->id, (bool) $request->user()->isAdmin, 'erp_'.$session->target_user_id),
        ]);
    }

    /**
     * Provides short-lived Cloudflare TURN credentials to authorised target
     * and administrator browsers participating in this monitoring session.
     */
    public function iceServers(Request $request, MonitoringSession $session)
    {
        $this->authorizeParticipant($request->user(), $session);
        abort_unless($session->status === 'active', 409, 'Sesi monitoring sudah berakhir.');

        $keyId = config('services.cloudflare_turn.key_id');
        $apiToken = config('services.cloudflare_turn.api_token');
        abort_unless($keyId && $apiToken, 503, 'Cloudflare TURN belum dikonfigurasi pada server ERP.');

        $ttl = min(max((int) config('services.cloudflare_turn.ttl', 3600), 300), 86_400);
        $iceServers = Cache::remember("monitoring-turn-{$session->id}", now()->addSeconds($ttl - 60), function () use ($keyId, $apiToken, $ttl) {
            $response = Http::acceptJson()
                ->withToken($apiToken)
                ->timeout(12)
                ->post("https://rtc.live.cloudflare.com/v1/turn/keys/{$keyId}/credentials/generate-ice-servers", [
                    'ttl' => $ttl,
                ]);

            if (! $response->successful() || ! is_array($response->json('iceServers'))) {
                abort(503, 'Credential Cloudflare TURN tidak dapat dibuat. Periksa TURN Key dan API Token server.');
            }

            return $response->json('iceServers');
        });

        return response()->json(['iceServers' => $iceServers]);
    }

    public function sendSignal(Request $request, MonitoringSession $session)
    {
        $sender = $request->user();
        $this->authorizeParticipant($sender, $session);
        abort_unless($session->status === 'active', 409, 'Sesi monitoring sudah berakhir.');
        $validated = $request->validate([
            'type' => ['required', 'in:offer,answer,candidate'],
            'payload' => ['nullable', 'array'],
            'recipient_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
        $payload = $validated['payload'] ?? [];

        // Chrome requires every SDP line, including the last one, to end in
        // CRLF. getOffer()/getAnswer() may omit it, so normalise it before the
        // description is persisted and delivered to the other browser.
        if (in_array($validated['type'], ['offer', 'answer'], true) && isset($payload['sdp']) && is_string($payload['sdp'])) {
            $payload['sdp'] = preg_replace("/\\r?\\n/", "\r\n", rtrim($payload['sdp'])) . "\r\n";
        }

        $recipientId = (int) $sender->id === (int) $session->target_user_id
            ? (int) ($validated['recipient_user_id'] ?? 0)
            : (int) $session->target_user_id;
        if ((int) $sender->id === (int) $session->target_user_id) {
            abort_unless(
                $recipientId > 0 && $this->canView(User::query()->findOrFail($recipientId), $session),
                422,
                'Tujuan pemantau tidak valid.'
            );
        }
        abort_unless($recipientId, 409, 'Pemantau belum terhubung.');

        MonitoringSignal::create([
            'monitoring_session_id' => $session->id,
            'sender_user_id' => $sender->id,
            'recipient_user_id' => $recipientId,
            'type' => $validated['type'],
            'payload' => $payload,
        ]);

        return response()->json(['ok' => true]);
    }

    public function signals(Request $request, MonitoringSession $session)
    {
        $user = $request->user();
        $this->authorizeParticipant($user, $session);
        $signals = MonitoringSignal::query()
            ->where('monitoring_session_id', $session->id)
            ->where('recipient_user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('id')
            ->get(['id', 'sender_user_id', 'type', 'payload']);
        if ($signals->isNotEmpty()) MonitoringSignal::whereIn('id', $signals->pluck('id'))->update(['read_at' => now()]);

        return response()->json(['status' => $session->fresh()->status, 'signals' => $signals]);
    }

    public function stop(Request $request, MonitoringSession $session)
    {
        abort_unless((int) $session->target_user_id === (int) $request->user()->id, 403);
        $session->update(['status' => 'ended', 'ended_at' => now()]);
        app(FirestoreService::class)->set('monitoring_sessions', (string) $session->id, [
            'target_uid' => 'erp_'.$session->target_user_id,
            'status' => 'ended',
            'ended_at' => now(),
            'viewer_uids' => [],
        ]);

        return response()->json(['ok' => true]);
    }

    public function accessIndex(Request $request): View
    {
        abort_unless($request->user()->isAdmin, 403);

        $users = User::query()
            ->orderBy('name')
            ->orderBy('email')
            ->get(['id', 'name', 'email', 'photo_profile']);
        $accesses = User::query()
            ->with('monitoringViewers:id,name,email')
            ->whereHas('monitoringViewers')
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        return view('monitoring.access', compact('users', 'accesses'));
    }

    public function storeAccess(Request $request)
    {
        abort_unless($request->user()->isAdmin, 403);
        $data = $this->validatedAccess($request);
        User::query()->findOrFail($data['target_user_id'])
            ->monitoringViewers()
            ->sync($data['viewer_ids']);

        return back()->with('success', 'Akses monitoring berhasil ditambahkan.');
    }

    public function updateAccess(Request $request, User $access)
    {
        abort_unless($request->user()->isAdmin, 403);
        $data = $this->validatedAccess($request);
        if ((int) $access->id !== (int) $data['target_user_id']) {
            $access->monitoringViewers()->sync([]);
        }
        User::query()->findOrFail($data['target_user_id'])
            ->monitoringViewers()
            ->sync($data['viewer_ids']);

        return back()->with('success', 'Akses monitoring berhasil diperbarui.');
    }

    public function destroyAccess(Request $request, User $access)
    {
        abort_unless($request->user()->isAdmin, 403);
        $access->monitoringViewers()->sync([]);

        return back()->with('success', 'Akses monitoring berhasil dihapus.');
    }

    private function authorizeParticipant(User $user, MonitoringSession $session): void
    {
        $isTarget = (int) $user->id === (int) $session->target_user_id;
        $isViewer = $this->canView($user, $session);
        abort_unless($isTarget || $isViewer, 403);
    }

    private function canView(User $user, MonitoringSession $session): bool
    {
        return (int) $user->id !== (int) $session->target_user_id
            && ((bool) $user->isAdmin || MonitoringAccess::query()
                ->where('viewer_user_id', $user->id)
                ->where('target_user_id', $session->target_user_id)
                ->exists());
    }

    private function canShare(User $user): bool
    {
        return (bool) $user->isAdmin || MonitoringAccess::query()
            ->where('target_user_id', $user->id)
            ->exists();
    }

    private function validatedAccess(Request $request): array
    {
        $data = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'viewer_ids' => ['required', 'array', 'min:1'],
            'viewer_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);
        abort_if(in_array((int) $data['target_user_id'], array_map('intval', $data['viewer_ids']), true), 422, 'Pengguna target tidak dapat memantau dirinya sendiri.');

        return $data;
    }
}
