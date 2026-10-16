<?php

namespace App\Services;

use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class ContentPlanCrudService
{
    public function __construct(private readonly ContentPlanCrudAuthorization $policy, private readonly MarketingConfirmationTokenService $confirmation, private readonly ContentPlanMutationAudit $audit) {}

    public function create(Authenticatable $user, array $data): array
    {
        $clientId = (int) $data['client_id'];
        $this->policy->assert($user, $clientId, MarketingCrudPermissions::CONTENT_PLAN_CREATE);
        $payload = $this->payload($data);
        $this->validateMasters($clientId, $payload);
        $this->confirmation->consume($user, $data['confirmation_token'], 'create', 'content_plan', 0, $this->hash($payload));
        $id = DB::table('data_jasa')->insertGetId($payload + ['produk_client_id' => $clientId, 'created_at' => now(), 'updated_at' => now()]);
        $this->audit->record($user, 'CREATE', $id, $clientId, [], $this->safe($payload));
        return $this->read($user, $id);
    }

    public function update(Authenticatable $user, int $id, array $data): array
    {
        $plan = $this->policy->assertObject($user, $id, MarketingCrudPermissions::CONTENT_PLAN_UPDATE);
        abort_unless(isset($data['client_id']) && (int) $data['client_id'] === (int) $plan->produk_client_id, 403, 'client_id tidak sesuai dengan record content plan.');
        abort_if($plan->archived_at, 409, 'Content plan sudah diarsipkan.');
        $payload = $this->payload($data);
        $this->validateMasters((int) $plan->produk_client_id, $payload);
        $this->confirmation->consume($user, $data['confirmation_token'], 'update', 'content_plan', $id, $this->hash($payload));
        DB::table('data_jasa')->where('id', $id)->update($payload + ['updated_at' => now()]);
        $this->audit->record($user, 'UPDATE', $id, (int) $plan->produk_client_id, [], $this->safe($payload));
        return $this->read($user, $id);
    }

    public function archive(Authenticatable $user, int $id, array $data): array
    {
        $plan = $this->policy->assertObject($user, $id, MarketingCrudPermissions::CONTENT_PLAN_ARCHIVE);
        abort_if($plan->archived_at, 409, 'Content plan sudah diarsipkan.');
        $payload = ['reason' => $data['reason']];
        $this->confirmation->consume($user, $data['confirmation_token'], 'archive', 'content_plan', $id, $this->hash($payload));
        DB::table('data_jasa')->where('id', $id)->update(['archived_at' => now(), 'archived_by' => $user->getAuthIdentifier(), 'updated_at' => now()]);
        $this->audit->record($user, 'ARCHIVE', $id, (int) $plan->produk_client_id, [], $payload);
        return ['id' => $id, 'archived_at' => now('Asia/Jakarta')->toIso8601String(), 'scope' => 'client'];
    }

    public function confirmation(Authenticatable $user, string $action, int $clientId, int $objectId, array $payload): array
    {
        $permission = match ($action) { 'create' => MarketingCrudPermissions::CONTENT_PLAN_CREATE, 'update' => MarketingCrudPermissions::CONTENT_PLAN_UPDATE, 'archive' => MarketingCrudPermissions::CONTENT_PLAN_ARCHIVE, default => null };
        abort_unless($permission, 422, 'Operasi confirmation tidak valid.');
        if ($objectId > 0) $this->policy->assertObject($user, $objectId, $permission);
        else $this->policy->assert($user, $clientId, $permission);
        $token = app(MarketingConfirmationTokenService::class)->issue($user, $action, 'content_plan', $objectId, hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)));
        return ['action' => $action, 'module' => 'content_plan', 'object_id' => $objectId, 'client_id' => $clientId, 'expires_in' => 300, 'confirmation_token' => $token];
    }

    private function read(Authenticatable $user, int $id): array
    {
        $plan = DB::table('data_jasa')->where('id', $id)->first(['id', 'produk_client_id', 'judul', 'inspirasi', 'platform', 'tanggal_mulai', 'tanggal_deadline', 'status_id', 'pic', 'priority', 'menit', 'archived_at']);
        $this->policy->assert($user, (int) $plan->produk_client_id, MarketingCrudPermissions::CONTENT_PLAN_UPDATE);
        return (array) $plan;
    }

    private function payload(array $data): array
    {
        return array_filter(array_intersect_key($data, array_flip(['judul', 'inspirasi', 'type_produk_id', 'strategy_id', 'pilar_id', 'hooks_id', 'body_id', 'cta_id', 'durasi', 'background', 'komposisi', 'note', 'tanggal_posting', 'tanggal_deadline', 'tanggal_mulai', 'link', 'platform', 'status_id', 'pic', 'priority', 'menit'])), fn ($v) => $v !== null);
    }
    private function safe(array $data): array { return array_diff_key($data, array_flip(['link'])); }
    private function hash(array $data): string { return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR)); }

    private function validateMasters(int $clientId, array $payload): void
    {
        $masters = [
            'type_produk_id' => ['type_produk', 'id'],
            'strategy_id' => ['strategy', 'id'],
            'pilar_id' => ['pilar', 'id'],
            'hooks_id' => ['hooks', 'id'],
            'body_id' => ['jenis_body', 'id'],
            'cta_id' => ['jenis_cta', 'id'],
        ];
        foreach ($masters as $field => [$table, $column]) {
            if (!array_key_exists($field, $payload)) continue;
            // Legacy/unit test installations may not include the master tables; production migrations do.
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) continue;
            $query = DB::table($table)->where($column, (int) $payload[$field]);
            if (in_array($table, ['type_produk', 'strategy', 'hooks', 'jenis_body', 'jenis_cta'], true)) {
                $name = ['type_produk' => 'type', 'strategy' => 'strategy', 'hooks' => 'hooks', 'jenis_body' => 'nama_body', 'jenis_cta' => 'nama_cta'][$table];
                $query->whereNotNull($name)->where($name, '!=', '');
            }
            if ($table === 'pilar') $query->where('status', 1);
            abort_unless($query->exists(), 422, "Master content plan {$field} tidak aktif atau tidak valid.");
        }
    }
}
