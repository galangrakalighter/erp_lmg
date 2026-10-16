<?php

namespace App\Services;

use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class BrandGuidelineCrudService
{
    public function __construct(private readonly BrandGuidelineCrudAuthorization $policy, private readonly MarketingConfirmationTokenService $confirmation, private readonly BrandGuidelineMutationAudit $audit) {}

    public function confirmation(Authenticatable $user, string $action, int $clientId, int $id, array $payload): array
    {
        $permission = match ($action) { 'create' => MarketingCrudPermissions::BRAND_GUIDELINE_CREATE, 'update' => MarketingCrudPermissions::BRAND_GUIDELINE_UPDATE, 'archive' => MarketingCrudPermissions::BRAND_GUIDELINE_ARCHIVE, default => null };
        abort_unless($permission, 422, 'Operasi confirmation tidak valid.');
        if ($action === 'archive') $this->assertArchiveReason($payload['reason'] ?? null);
        if ($id) {
            $record = $this->policy->assertObject($user, $id, $permission);
            abort_unless((int) $record->produk_client_id === $clientId, 403, 'client_access_denied');
        } else {
            $this->policy->assert($user, $clientId, $permission);
        }
        $canonical = $action === 'archive' ? ['reason' => $payload['reason']] : $this->payload($payload);
        $token = $this->confirmation->issue($user, $action, 'brand_guideline', $id, $this->hash($clientId, $canonical));
        return ['action' => $action, 'module' => 'brand_guideline', 'record_id' => $id, 'client_id' => $clientId, 'expires_in' => 300, 'confirmation_token' => $token];
    }

    public function create(Authenticatable $user, array $data): array
    {
        $clientId = (int) $data['client_id']; $payload = $this->payload($data);
        $this->policy->assert($user, $clientId, MarketingCrudPermissions::BRAND_GUIDELINE_CREATE);
        $this->confirmation->consume($user, $data['confirmation_token'], 'create', 'brand_guideline', 0, $this->hash($clientId, $payload));
        $id = DB::table('brand_identify')->insertGetId($payload + ['produk_client_id' => $clientId, 'created_at' => now(), 'updated_at' => now()]);
        $this->audit->record($user, 'CREATE', $id, $clientId, $payload);
        return $this->read($id);
    }

    public function update(Authenticatable $user, int $id, array $data): array
    {
        $record = $this->policy->assertObject($user, $id, MarketingCrudPermissions::BRAND_GUIDELINE_UPDATE);
        abort_if($record->archived_at, 409, 'Brand guideline sudah diarsipkan.');
        $this->assertArchiveReason($data['reason'] ?? null);
        abort_unless((int) ($data['client_id'] ?? 0) === (int) $record->produk_client_id, 403, 'client_id tidak sesuai dengan record brand guideline.');
        $payload = $this->payload($data);
        $this->confirmation->consume($user, $data['confirmation_token'], 'update', 'brand_guideline', $id, $this->hash((int) $record->produk_client_id, $payload));
        DB::table('brand_identify')->where('id', $id)->update($payload + ['updated_at' => now()]);
        $this->audit->record($user, 'UPDATE', $id, (int) $record->produk_client_id, $payload);
        return $this->read($id);
    }

    public function archive(Authenticatable $user, int $id, array $data): array
    {
        $record = $this->policy->assertObject($user, $id, MarketingCrudPermissions::BRAND_GUIDELINE_ARCHIVE);
        abort_if($record->archived_at, 409, 'Brand guideline sudah diarsipkan.');
        $payload = ['reason' => $data['reason']];
        $this->confirmation->consume($user, $data['confirmation_token'], 'archive', 'brand_guideline', $id, $this->hash((int) $record->produk_client_id, $payload));
        DB::table('brand_identify')->where('id', $id)->update(['archived_at' => now(), 'archived_by' => $user->getAuthIdentifier(), 'updated_at' => now()]);
        $this->audit->record($user, 'ARCHIVE', $id, (int) $record->produk_client_id, $payload);
        return ['id' => $id, 'archived' => true, 'scope' => 'client'];
    }

    private function payload(array $data): array { return array_intersect_key($data, array_flip(['identify', 'platform'])); }
    private function assertArchiveReason(?string $reason): void { abort_if($reason === null || trim($reason) === '', 422, 'reason_required'); abort_if(mb_strlen(trim($reason)) < 5 || mb_strlen($reason) > 500, 422, 'reason_invalid'); }
    private function hash(int $clientId, array $payload): string { return hash('sha256', json_encode(['client_id' => $clientId, 'payload' => $payload], JSON_THROW_ON_ERROR)); }
    private function read(int $id): array { return (array) DB::table('brand_identify')->where('id', $id)->first(['id', 'produk_client_id', 'identify', 'platform', 'archived_at']); }
}
