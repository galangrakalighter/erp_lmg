<?php

namespace App\Services;

use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class MarketingApprovalService
{
    private const MODULE = 'approval_event';

    public function __construct(private readonly MarketingApprovalAuthorization $policy, private readonly MarketingConfirmationTokenService $confirmation, private readonly MarketingApprovalMutationAudit $audit) {}

    public function confirmation(Authenticatable $user, array $data): array
    {
        $permission = $this->permission($data['action']);
        $this->policy->assertRecord($user, (int) $data['client_id'], $data['record_type'], (int) $data['record_id'], $permission);
        $payload = $this->payload($data);
        $token = $this->confirmation->issue($user, $data['action'], self::MODULE, (int) $data['record_id'], $this->hash((int) $data['client_id'], $data['record_type'], $payload));

        return ['action' => $data['action'], 'module' => self::MODULE, 'record_type' => $data['record_type'], 'record_id' => (int) $data['record_id'], 'client_id' => (int) $data['client_id'], 'expires_in' => 300, 'confirmation_token' => $token];
    }

    public function append(Authenticatable $user, string $action, array $data): array
    {
        abort_unless($action === $data['action'], 422, 'Action request tidak sesuai dengan endpoint.');
        $clientId = (int) $data['client_id'];
        $recordId = (int) $data['record_id'];
        $this->policy->assertRecord($user, $clientId, $data['record_type'], $recordId, $this->permission($action));
        $payload = $this->payload($data);
        $this->confirmation->consume($user, $data['confirmation_token'], $action, self::MODULE, $recordId, $this->hash($clientId, $data['record_type'], $payload));

        $event = DB::transaction(function () use ($user, $action, $data, $clientId, $recordId, $payload): object {
            $latest = DB::table('marketing_approval_events')->where('client_id', $clientId)->where('record_type', $data['record_type'])->where('record_id', $recordId)->orderByDesc('id')->lockForUpdate()->first(['status']);
            if ($action === 'submit_for_approval') {
                abort_unless(! $latest || in_array($latest->status, ['rejected', 'revision_requested'], true), 409, 'Record sudah menunggu atau telah memperoleh keputusan approval.');
            } else {
                abort_unless($latest && $latest->status === 'pending', 409, 'Tindakan ini hanya berlaku pada record berstatus pending.');
            }
            $status = match ($action) { 'submit_for_approval' => 'pending', 'approve' => 'approved', 'reject' => 'rejected', 'request_revision' => 'revision_requested' };
            $id = DB::table('marketing_approval_events')->insertGetId([
                'user_id' => $user->getAuthIdentifier(), 'client_id' => $clientId, 'record_type' => $data['record_type'], 'record_id' => $recordId,
                'action' => $action, 'status' => $status, 'note' => $payload['note'], 'audit_reference' => (string) Str::uuid(), 'created_at' => now(),
            ]);
            if ($data['record_type'] === 'asset_metadata') {
                DB::table('marketing_asset_metadata')->where('id', $recordId)->whereNull('archived_at')->update(['approval_status' => $status, 'updated_at' => now()]);
            }

            return DB::table('marketing_approval_events')->where('id', $id)->first();
        });
        $this->audit->record($user, $event);

        return $this->response($event);
    }

    private function permission(string $action): string
    {
        return match ($action) {
            'submit_for_approval' => MarketingCrudPermissions::APPROVAL_SUBMIT,
            'approve' => MarketingCrudPermissions::APPROVAL_APPROVE,
            'reject' => MarketingCrudPermissions::APPROVAL_REJECT,
            'request_revision' => MarketingCrudPermissions::APPROVAL_REQUEST_REVISION,
            default => abort(422, 'Action approval tidak valid.'),
        };
    }

    private function payload(array $data): array
    {
        return ['note' => $data['note'] ?? null];
    }

    private function hash(int $clientId, string $recordType, array $payload): string
    {
        return hash('sha256', json_encode(['client_id' => $clientId, 'record_type' => $recordType, 'payload' => $payload], JSON_THROW_ON_ERROR));
    }

    private function response(object $event): array
    {
        return ['event_id' => (int) $event->id, 'user_id' => (int) $event->user_id, 'client_id' => (int) $event->client_id, 'record_type' => $event->record_type, 'record_id' => (int) $event->record_id, 'action' => $event->action, 'status' => $event->status, 'note' => $event->note, 'audit_reference' => $event->audit_reference, 'created_at' => (string) $event->created_at];
    }
}
