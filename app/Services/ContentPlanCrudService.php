<?php

namespace App\Services;

use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

final class ContentPlanCrudService
{
    public function __construct(private readonly ContentPlanCrudAuthorization $policy, private readonly MarketingConfirmationTokenService $confirmation, private readonly ContentPlanMutationAudit $audit) {}

    public function create(Authenticatable $user, array $data): array
    {
        $clientId = (int) $data['client_id'];
        $this->policy->assert($user, $clientId, MarketingCrudPermissions::CONTENT_PLAN_CREATE);
        abort_if(array_key_exists('pic', $data), 422, 'PIC ditetapkan otomatis dari user OAuth.');
        $payload = $this->payload($data) + ['pic' => (int) $user->getAuthIdentifier()];
        $this->validateDraft($payload, true);
        $this->validateMasters($clientId, $payload);
        $this->validateStatusAndPic($payload);
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
        abort_if(array_key_exists('pic', $data), 422, 'PIC tidak dapat diubah melalui input Content Plan.');
        $payload = $this->payload($data);
        $this->validateDraft($payload, false);
        $this->validateMasters((int) $plan->produk_client_id, $payload);
        $this->validateStatusAndPic($payload);
        $this->confirmation->consume($user, $data['confirmation_token'], 'update', 'content_plan', $id, $this->hash($payload));
        DB::table('data_jasa')->where('id', $id)->update($payload + ['updated_at' => now()]);
        $this->audit->record($user, 'UPDATE', $id, (int) $plan->produk_client_id, [], $this->safe($payload));
        return $this->read($user, $id);
    }

    public function archive(Authenticatable $user, int $id, array $data): array
    {
        $plan = $this->policy->assertObject($user, $id, MarketingCrudPermissions::CONTENT_PLAN_ARCHIVE);
        abort_if($plan->archived_at, 409, 'Content plan sudah diarsipkan.');
        $this->assertArchiveReason($data['reason'] ?? null);
        $payload = ['reason' => $data['reason']];
        $this->confirmation->consume($user, $data['confirmation_token'], 'archive', 'content_plan', $id, $this->hash(['client_id' => (int) $plan->produk_client_id, 'payload' => $payload]));
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
        abort_if($action !== 'archive' && array_key_exists('pic', $payload), 422, 'PIC ditetapkan otomatis dari user OAuth.');
        if ($action === 'archive') $this->assertArchiveReason($payload['reason'] ?? null);
        $canonicalPayload = $action === 'archive' ? ['reason' => $payload['reason']] : $this->payload($payload);
        if ($action === 'create') $canonicalPayload['pic'] = (int) $user->getAuthIdentifier();
        if ($action !== 'archive') {
            $this->validateDraft($canonicalPayload, $action === 'create');
            $this->validateMasters($clientId, $canonicalPayload);
            $this->validateStatusAndPic($canonicalPayload);
        }
        $tokenPayload = $action === 'archive' ? ['client_id' => $clientId, 'payload' => $canonicalPayload] : $canonicalPayload;
        $token = app(MarketingConfirmationTokenService::class)->issue($user, $action, 'content_plan', $objectId, $this->hash($tokenPayload));
        $draft = array_intersect_key($canonicalPayload, array_flip(['judul', 'note', 'platform', 'content_source', 'status_id', 'type_produk_id', 'strategy_id', 'pilar_id', 'hooks_id', 'body_id', 'cta_id', 'priority', 'menit', 'pic']));
        $draft['masters'] = $this->masterLabels($canonicalPayload);
        if (isset($draft['pic']) && Schema::hasTable('users')) $draft['pic_name'] = DB::table('users')->where('id', $draft['pic'])->value('name');
        return ['action' => $action, 'module' => 'content_plan', 'object_id' => $objectId, 'client_id' => $clientId, 'draft' => $draft, 'expires_in' => 300, 'confirmation_token' => $token];
    }

    private function read(Authenticatable $user, int $id): array
    {
        $plan = DB::table('data_jasa')->where('id', $id)->first(['id', 'produk_client_id', 'judul', 'inspirasi', 'note', 'platform', 'content_source', 'tanggal_posting', 'status_id', 'pic', 'priority', 'menit', 'archived_at']);
        $this->policy->assert($user, (int) $plan->produk_client_id, MarketingCrudPermissions::CONTENT_PLAN_UPDATE);
        return (array) $plan;
    }

    private function payload(array $data): array
    {
        $ordered = [];
        foreach (['judul', 'note', 'platform', 'content_source', 'type_produk_id', 'strategy_id', 'pilar_id', 'hooks_id', 'body_id', 'cta_id', 'status_id', 'priority', 'menit', 'inspirasi', 'durasi', 'background', 'komposisi', 'tanggal_posting', 'tanggal_deadline', 'tanggal_mulai', 'link'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) $ordered[$field] = $data[$field];
        }
        return $ordered;
    }
    private function safe(array $data): array { return array_diff_key($data, array_flip(['link'])); }
    private function hash(array $data): string { return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR)); }
    private function assertArchiveReason(mixed $reason): void { abort_if(!is_string($reason) || trim($reason) === '', 422, 'reason_required'); abort_if(mb_strlen(trim($reason)) < 5 || mb_strlen($reason) > 500, 422, 'reason_invalid'); }

    private function validateDraft(array $payload, bool $create): void
    {
        $required = $create ? 'required' : 'sometimes';
        Validator::make($payload, [
            'judul' => [$required, 'string', 'min:2', 'max:255'],
            'note' => [$required, 'string', 'min:2', 'max:10000'],
            'platform' => [$required, 'string', 'in:facebook,instagram,tiktok,threads,google,youtube'],
            'content_source' => [$required, 'string', 'in:organic,paid'],
            'type_produk_id' => [$required, 'integer', 'min:1'], 'strategy_id' => [$required, 'integer', 'min:1'],
            'pilar_id' => [$required, 'integer', 'min:1'], 'hooks_id' => [$required, 'integer', 'min:1'],
            'body_id' => [$required, 'integer', 'min:1'], 'cta_id' => [$required, 'integer', 'min:1'],
            'status_id' => [$required, 'integer', 'min:1'], 'priority' => [$required, 'integer', 'min:0', 'max:100'],
            'menit' => [$required, 'integer', 'min:0', 'max:10000'], 'pic' => [$required, 'integer', 'min:1'],
        ])->validate();
    }

    private function masterLabels(array $payload): array
    {
        $definitions = [
            'type_produk_id' => ['type_produk', 'type'], 'strategy_id' => ['strategy', 'strategy'],
            'pilar_id' => ['pilar', 'pilar'], 'hooks_id' => ['hooks', 'hooks'],
            'body_id' => ['jenis_body', 'nama_body'], 'cta_id' => ['jenis_cta', 'nama_cta'],
            'status_id' => ['status', 'nama_status'],
        ];
        $labels = [];
        foreach ($definitions as $field => [$table, $name]) {
            if (!isset($payload[$field]) || !Schema::hasTable($table)) continue;
            $labels[$field] = ['id' => (int) $payload[$field], 'name' => DB::table($table)->where('id', $payload[$field])->value($name)];
        }
        return $labels;
    }

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
            if (in_array($table, ['type_produk', 'pilar'], true) && Schema::hasColumn($table, 'kategori_id')) {
                $categoryId = DB::table('produk_client')->where('id', $clientId)->value('kategori_jasa_id');
                if ($categoryId !== null) $query->where($table.'.kategori_id', $categoryId);
            }
            abort_unless($query->exists(), 422, "Master content plan {$field} tidak aktif atau tidak valid.");
        }
    }

    private function validateStatusAndPic(array $payload): void
    {
        if (array_key_exists('status_id', $payload)) {
            if (!\Illuminate\Support\Facades\Schema::hasTable('status')) return;
            $statusQuery = DB::table('status')->where('id', (int) $payload['status_id']);
            if (\Illuminate\Support\Facades\Schema::hasColumn('status', 'status')) $statusQuery->where('status', 1);
            $status = $statusQuery->first();
            abort_unless($status, 422, 'status_id tidak aktif atau tidak valid.');
        }
        if (array_key_exists('pic', $payload) && Schema::hasTable('users')) {
            $pic = DB::table('users')->where('id', (int) $payload['pic']);
            if (Schema::hasColumn('users', 'deleted_at')) $pic->whereNull('deleted_at');
            if (Schema::hasColumn('users', 'is_locked')) $pic->where('is_locked', false);
            abort_unless($pic->exists(), 422, 'PIC OAuth tidak aktif atau tidak valid.');
        }
    }
}
