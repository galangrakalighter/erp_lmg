<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSession;
use App\Models\MonitoringSignal;
use App\Models\User;
use Illuminate\Http\Request;
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
            'canStart' => (int) $viewer->id === (int) $target->id,
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
        abort_if($session->viewer_user_id && (int) $session->viewer_user_id !== (int) $request->user()->id, 409, 'Sesi sedang dipantau oleh admin lain.');
        $session->update(['viewer_user_id' => $request->user()->id]);

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

    public function sendSignal(Request $request, MonitoringSession $session)
    {
        $sender = $request->user();
        $this->authorizeParticipant($sender, $session);
        abort_unless($session->status === 'active', 409, 'Sesi monitoring sudah berakhir.');
        $validated = $request->validate(['type' => ['required', 'in:offer,answer,candidate'], 'payload' => ['nullable', 'array']]);
        $recipientId = (int) $sender->id === (int) $session->target_user_id ? $session->viewer_user_id : $session->target_user_id;
        abort_unless($recipientId, 409, 'Pemantau belum terhubung.');

        MonitoringSignal::create([
            'monitoring_session_id' => $session->id,
            'sender_user_id' => $sender->id,
            'recipient_user_id' => $recipientId,
            'type' => $validated['type'],
            'payload' => $validated['payload'] ?? [],
        ]);

        return response()->json(['ok' => true]);
    }

    public function signals(Request $request, MonitoringSession $session)
    {
        $user = $request->user();
        $this->authorizeParticipant($user, $session);
        $signals = MonitoringSignal::query()->where('monitoring_session_id', $session->id)->where('recipient_user_id', $user->id)->whereNull('read_at')->oldest()->get(['id', 'type', 'payload']);
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
        $isViewer = (int) $user->id === (int) $session->viewer_user_id && $user->isAdmin;
        abort_unless($isTarget || $isViewer, 403);
    }
}
