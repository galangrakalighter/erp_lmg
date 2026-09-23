<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSession;
use App\Models\MonitoringSignal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();

        if ($viewer->isAdmin && ! $request->filled('user')) {
            $users = User::query()
                ->with(['userDivisions.division', 'userDivisions.role'])
                ->withCount(['monitoringSessions as active_monitoring_count' => fn ($query) => $query->where('status', 'active')])
                ->orderBy('email')
                ->get(['id', 'name', 'email', 'photo_profile']);

            return view('monitoring.directory', compact('users'));
        }

        $users = $viewer->isAdmin
            ? User::query()->orderBy('email')->get(['id', 'name', 'email', 'photo_profile'])
            : collect([$viewer]);
        $target = $users->firstWhere('id', $viewer->isAdmin ? $request->integer('user') : $viewer->id) ?? $viewer;
        $activeSession = MonitoringSession::query()->where('target_user_id', $target->id)->where('status', 'active')->latest('started_at')->first();

        return view('monitoring.index', [
            'title' => 'Monitoring',
            'users' => $users,
            'target' => $target,
            'activeSession' => $activeSession,
            // Admin only observes other users. Starting a browser capture,
            // downloading the helper extension, and its setup guide belong to
            // non-admin target users on their own device.
            'canStart' => ! $viewer->isAdmin && (int) $viewer->id === (int) $target->id,
            'canView' => $viewer->isAdmin && (int) $viewer->id !== (int) $target->id,
        ]);
    }

    public function start(Request $request)
    {
        $request->validate(['consent' => ['accepted']]);
        $user = $request->user();
        MonitoringSession::query()->where('target_user_id', $user->id)->where('status', 'active')->update(['status' => 'ended', 'ended_at' => now()]);
        $session = MonitoringSession::create(['target_user_id' => $user->id, 'status' => 'active', 'consented_at' => now(), 'started_at' => now()]);

        return response()->json(['session' => ['id' => $session->id]]);
    }

    public function join(Request $request, MonitoringSession $session)
    {
        abort_unless($request->user()->isAdmin, 403);
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

        return response()->json(['session' => ['id' => $session->id]]);
    }

    public function active(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin, 403);

        $session = MonitoringSession::query()
            ->where('target_user_id', $user->id)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        return response()->json([
            'session' => $session ? ['id' => $session->id] : null,
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
                $recipientId > 0 && User::query()->whereKey($recipientId)->where('isAdmin', true)->exists(),
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

        return response()->json(['ok' => true]);
    }

    private function authorizeParticipant(User $user, MonitoringSession $session): void
    {
        $isTarget = (int) $user->id === (int) $session->target_user_id;
        $isViewer = (bool) $user->isAdmin && (int) $user->id !== (int) $session->target_user_id;
        abort_unless($isTarget || $isViewer, 403);
    }
}
