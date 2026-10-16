<?php

namespace App\Services;

use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class CampaignCrudService
{
    private const MODULE = 'campaign';

    public function __construct(private readonly CampaignCrudAuthorization $policy, private readonly MarketingConfirmationTokenService $confirmation, private readonly CampaignMutationAudit $audit) {}

    public function confirmation(Authenticatable $user, string $action, int $clientId, int $id, array $payload): array
    {
        $permission = match ($action) {
            'create' => MarketingCrudPermissions::CAMPAIGN_CREATE,
            'update' => MarketingCrudPermissions::CAMPAIGN_UPDATE,
            'archive' => MarketingCrudPermissions::CAMPAIGN_ARCHIVE,
            default => null,
        };
        abort_unless($permission, 422, 'Operasi confirmation tidak valid.');
        if ($id > 0) {
            $record = $this->policy->assertObject($user, $id, $permission);
            abort_unless((int) $record->client_id === $clientId, 403, 'client_id tidak sesuai dengan record campaign.');
        } else {
            abort_unless($action === 'create', 422, 'record_id wajib untuk update atau archive.');
            $this->policy->assert($user, $clientId, $permission);
        }
        $normalized = $action === 'archive' ? ['reason' => $payload['reason'] ?? null] : $this->payload($payload);
        $token = $this->confirmation->issue($user, $action, self::MODULE, $id, $this->hash($clientId, $normalized));

        return ['action' => $action, 'module' => self::MODULE, 'record_id' => $id, 'client_id' => $clientId, 'expires_in' => 300, 'confirmation_token' => $token];
    }

    public function create(Authenticatable $user, array $data): array
    {
        $clientId = (int) $data['client_id'];
        $payload = $this->payload($data);
        $this->policy->assert($user, $clientId, MarketingCrudPermissions::CAMPAIGN_CREATE);
        $this->assertOwner($clientId, $payload['owner_user_id'] ?? null);
        $this->confirmation->consume($user, $data['confirmation_token'], 'create', self::MODULE, 0, $this->hash($clientId, $payload));
        $id = DB::table('marketing_campaigns')->insertGetId($payload + ['client_id' => $clientId, 'created_at' => now(), 'updated_at' => now()]);
        $this->audit->record($user, 'CREATE', $id, $clientId, $payload);

        return $this->read($id);
    }

    public function update(Authenticatable $user, int $id, array $data): array
    {
        $record = $this->policy->assertObject($user, $id, MarketingCrudPermissions::CAMPAIGN_UPDATE);
        abort_if($record->archived_at, 409, 'Campaign sudah diarsipkan.');
        abort_unless((int) $data['client_id'] === (int) $record->client_id, 403, 'client_id tidak sesuai dengan record campaign.');
        $payload = $this->payload($data);
        $this->assertOwner((int) $record->client_id, $payload['owner_user_id'] ?? null);
        $this->confirmation->consume($user, $data['confirmation_token'], 'update', self::MODULE, $id, $this->hash((int) $record->client_id, $payload));
        DB::table('marketing_campaigns')->where('id', $id)->update($payload + ['updated_at' => now()]);
        $this->audit->record($user, 'UPDATE', $id, (int) $record->client_id, $payload);

        return $this->read($id);
    }

    public function archive(Authenticatable $user, int $id, array $data): array
    {
        $record = $this->policy->assertObject($user, $id, MarketingCrudPermissions::CAMPAIGN_ARCHIVE);
        abort_if($record->archived_at, 409, 'Campaign sudah diarsipkan.');
        abort_unless((int) $data['client_id'] === (int) $record->client_id, 403, 'client_id tidak sesuai dengan record campaign.');
        $payload = ['reason' => $data['reason']];
        $this->confirmation->consume($user, $data['confirmation_token'], 'archive', self::MODULE, $id, $this->hash((int) $record->client_id, $payload));
        DB::table('marketing_campaigns')->where('id', $id)->update(['archived_at' => now(), 'archived_by' => $user->getAuthIdentifier(), 'updated_at' => now()]);
        $this->audit->record($user, 'ARCHIVE', $id, (int) $record->client_id, $payload);

        return ['id' => $id, 'client_id' => (int) $record->client_id, 'archived' => true, 'scope' => 'client'];
    }

    private function payload(array $data): array
    {
        return array_intersect_key($data, array_flip(['owner_user_id', 'name', 'status', 'platform', 'source', 'start_date', 'end_date', 'objective', 'budget', 'currency', 'target']));
    }

    private function hash(int $clientId, array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode(['client_id' => $clientId, 'payload' => $payload], JSON_THROW_ON_ERROR));
    }

    private function assertOwner(int $clientId, mixed $ownerId): void
    {
        if ($ownerId === null) {
            return;
        }
        abort_unless(DB::table('user_client')->where('user_id', (int) $ownerId)->where('client_id', $clientId)->exists(), 422, 'Owner/PIC harus memiliki assignment ke client campaign.');
        abort_unless(DB::table('user_division')->join('divisions', 'divisions.id', '=', 'user_division.division_id')->where('user_division.user_id', (int) $ownerId)->whereRaw('LOWER(TRIM(divisions.name)) = ?', ['digital marketing'])->exists(), 422, 'Owner/PIC harus berasal dari Divisi Digital Marketing.');
    }

    private function read(int $id): array
    {
        return (array) DB::table('marketing_campaigns')->where('id', $id)->first(['id', 'client_id', 'owner_user_id', 'name', 'status', 'platform', 'source', 'start_date', 'end_date', 'objective', 'budget', 'currency', 'target', 'archived_at']);
    }
}
