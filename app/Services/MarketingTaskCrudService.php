<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class MarketingTaskCrudService
{
    private const MODULE = 'marketing_task';

    public function __construct(private readonly MarketingTaskAuthorization $policy, private readonly MarketingConfirmationTokenService $confirmation, private readonly MarketingTaskMutationAudit $audit) {}

    public function confirmation(Authenticatable $user, string $action, int $clientId, int $id, array $payload): array
    {
        $permission = $this->permission($action);
        if ($action === 'create') {
            abort_unless($id === 0, 422, 'record_id create harus 0.');
            $this->policy->assert($user, $clientId, $permission);
        } else {
            $task = $this->policy->object($user, $id, $permission);
            abort_unless((int) $task->project === $clientId, 403, 'client_id tidak sesuai dengan task.');
        }
        $normalized = $this->payload($action, $payload);
        $token = $this->confirmation->issue($user, $action, self::MODULE, $id, $this->hash($clientId, $normalized));

        return ['action' => $action, 'module' => self::MODULE, 'record_id' => $id, 'client_id' => $clientId, 'expires_in' => 300, 'confirmation_token' => $token];
    }

    public function create(Authenticatable $user, array $data): array
    {
        $clientId = (int) $data['client_id'];
        $payload = $this->payload('create', $data);
        $this->policy->assert($user, $clientId, MarketingCrudPermissions::TASK_CREATE);
        $divisionId = $this->digitalMarketingDivision();
        $this->assertPic($clientId, (int) $payload['pic']);
        $this->assertContentPlan($clientId, $payload['id_manajement_konten'] ?? null);
        $this->confirmation->consume($user, $data['confirmation_token'], 'create', self::MODULE, 0, $this->hash($clientId, $payload));
        $id = DB::table('task')->insertGetId($payload + ['project' => $clientId, 'divisi' => $divisionId, 'status' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $this->audit->record($user, 'CREATE', $id, $clientId, $payload);

        return $this->read($id);
    }

    public function update(Authenticatable $user, int $id, array $data): array
    {
        $task = $this->active($this->policy->object($user, $id, MarketingCrudPermissions::TASK_UPDATE));
        $this->assertClient($task, $data);
        $payload = $this->payload('update', $data);
        abort_if(isset($payload['tanggal_mulai']) && $payload['tanggal_mulai'] > $task->tanggal_deadline, 422, 'Tanggal mulai tidak boleh setelah deadline task.');
        $this->assertContentPlan((int) $task->project, $payload['id_manajement_konten'] ?? null);
        $this->confirmation->consume($user, $data['confirmation_token'], 'update', self::MODULE, $id, $this->hash((int) $task->project, $payload));
        DB::table('task')->where('id', $id)->update($payload + ['updated_at' => now()]);
        $this->audit->record($user, 'UPDATE', $id, (int) $task->project, $payload);

        return $this->read($id);
    }

    public function archive(Authenticatable $user, int $id, array $data): array
    {
        $task = $this->active($this->policy->object($user, $id, MarketingCrudPermissions::TASK_ARCHIVE));
        $this->assertClient($task, $data);
        $payload = ['reason' => $data['reason']];
        $this->confirmation->consume($user, $data['confirmation_token'], 'archive', self::MODULE, $id, $this->hash((int) $task->project, $payload));
        DB::table('task')->where('id', $id)->update(['archived_at' => now(), 'archived_by' => $user->getAuthIdentifier(), 'updated_at' => now()]);
        $this->audit->record($user, 'ARCHIVE', $id, (int) $task->project, $payload);

        return ['id' => $id, 'client_id' => (int) $task->project, 'archived' => true, 'scope' => 'client'];
    }

    public function changePic(Authenticatable $user, int $id, array $data): array
    {
        return $this->change($user, $id, $data, 'change_pic', MarketingCrudPermissions::TASK_CHANGE_PIC, function (object $task, array $payload): void { $this->assertPic((int) $task->project, (int) $payload['pic']); });
    }

    public function changeDeadline(Authenticatable $user, int $id, array $data): array
    {
        return $this->change($user, $id, $data, 'change_deadline', MarketingCrudPermissions::TASK_CHANGE_DEADLINE, function (object $task, array $payload): void { abort_if($payload['tanggal_deadline'] < $task->tanggal_mulai, 422, 'Deadline tidak boleh sebelum tanggal mulai task.'); });
    }

    public function changeStatus(Authenticatable $user, int $id, array $data): array
    {
        $task = $this->active($this->policy->object($user, $id, MarketingCrudPermissions::TASK_CHANGE_STATUS));
        $this->assertClient($task, $data);
        $payload = $this->payload('change_status', $data);
        $role = DigitalMarketingPermissions::role($user);
        if (! (bool) data_get($user, 'isAdmin', false) && ! DigitalMarketingPermissions::isManager($role)) {
            abort_unless(in_array((int) $payload['status'], [0, 1, 3], true), 403, 'PIC hanya dapat mengubah status belum dimulai, berjalan, atau menunggu approval.');
        }
        $this->confirmation->consume($user, $data['confirmation_token'], 'change_status', self::MODULE, $id, $this->hash((int) $task->project, $payload));
        $update = $payload + ['updated_at' => now()];
        if ((int) $payload['status'] === 2) $update['tanggal_revisi'] = now('Asia/Jakarta')->toDateString();
        DB::table('task')->where('id', $id)->update($update);
        $this->audit->record($user, 'CHANGE_STATUS', $id, (int) $task->project, $payload);

        return $this->read($id);
    }

    public function complete(Authenticatable $user, int $id, array $data): array
    {
        $task = $this->active($this->policy->object($user, $id, MarketingCrudPermissions::TASK_COMPLETE));
        $this->assertClient($task, $data);
        abort_unless(in_array((int) $task->status, [3, 9], true), 409, 'Task hanya dapat diselesaikan dari status menunggu approval.');
        abort_unless(filled($task->completion_proof_path) && Storage::disk('local')->exists($task->completion_proof_path), 422, 'Task belum memiliki bukti penyelesaian yang tersimpan.');
        $payload = ['note' => $data['note'] ?? null];
        $this->confirmation->consume($user, $data['confirmation_token'], 'complete', self::MODULE, $id, $this->hash((int) $task->project, $payload));
        $status = (int) $task->status === 9 ? 7 : 4;
        DB::table('task')->where('id', $id)->update(['status' => $status, 'tanggal_approve' => now('Asia/Jakarta')->toDateString(), 'updated_at' => now()]);
        $this->audit->record($user, 'COMPLETE', $id, (int) $task->project, ['status' => $status, 'note' => $payload['note']]);

        return $this->read($id);
    }

    private function change(Authenticatable $user, int $id, array $data, string $action, string $permission, ?\Closure $guard = null): array
    {
        $task = $this->active($this->policy->object($user, $id, $permission));
        $this->assertClient($task, $data);
        $payload = $this->payload($action, $data);
        $guard?->__invoke($task, $payload);
        $this->confirmation->consume($user, $data['confirmation_token'], $action, self::MODULE, $id, $this->hash((int) $task->project, $payload));
        DB::table('task')->where('id', $id)->update($payload + ['updated_at' => now()]);
        $this->audit->record($user, strtoupper($action), $id, (int) $task->project, $payload);

        return $this->read($id);
    }

    private function payload(string $action, array $data): array
    {
        $mapping = match ($action) {
            'create' => ['name' => 'nama_task', 'pic_id' => 'pic', 'priority' => 'priority', 'estimated_minutes' => 'menit', 'content_plan_id' => 'id_manajement_konten', 'start_date' => 'tanggal_mulai', 'deadline' => 'tanggal_deadline', 'note' => 'note', 'start_time' => 'jam_mulai'],
            'update' => ['name' => 'nama_task', 'priority' => 'priority', 'estimated_minutes' => 'menit', 'content_plan_id' => 'id_manajement_konten', 'start_date' => 'tanggal_mulai', 'note' => 'note', 'start_time' => 'jam_mulai'],
            'archive' => ['reason' => 'reason'], 'change_pic' => ['pic_id' => 'pic'], 'change_deadline' => ['deadline' => 'tanggal_deadline'],
            'change_status' => ['status' => 'status'], 'complete' => ['note' => 'note'], default => [],
        };
        $payload = [];
        foreach ($mapping as $input => $column) if (array_key_exists($input, $data)) $payload[$column] = $data[$input];
        return $payload;
    }

    private function permission(string $action): string
    {
        return match ($action) {
            'create' => MarketingCrudPermissions::TASK_CREATE, 'update' => MarketingCrudPermissions::TASK_UPDATE,
            'archive' => MarketingCrudPermissions::TASK_ARCHIVE, 'change_pic' => MarketingCrudPermissions::TASK_CHANGE_PIC,
            'change_deadline' => MarketingCrudPermissions::TASK_CHANGE_DEADLINE, 'change_status' => MarketingCrudPermissions::TASK_CHANGE_STATUS,
            'complete' => MarketingCrudPermissions::TASK_COMPLETE, default => abort(422, 'Action task tidak valid.'),
        };
    }

    private function assertPic(int $clientId, int $picId): void
    {
        abort_unless(DB::table('user_client')->where('user_id', $picId)->where('client_id', $clientId)->exists(), 422, 'PIC harus memiliki assignment ke client task.');
        abort_unless(DB::table('user_division')->join('divisions', 'divisions.id', '=', 'user_division.division_id')->where('user_division.user_id', $picId)->whereRaw('LOWER(TRIM(divisions.name)) = ?', ['digital marketing'])->exists(), 422, 'PIC harus berasal dari Divisi Digital Marketing.');
    }

    private function assertContentPlan(int $clientId, mixed $contentPlanId): void
    {
        if ($contentPlanId === null) return;
        abort_unless(DB::table('data_jasa')->where('id', (int) $contentPlanId)->where('produk_client_id', $clientId)->whereNull('archived_at')->exists(), 422, 'Content plan tidak tersedia untuk client task.');
    }

    private function digitalMarketingDivision(): int
    {
        $id = DB::table('divisions')->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])->value('id');
        abort_unless($id, 422, 'Divisi Digital Marketing tidak tersedia.');
        return (int) $id;
    }

    private function assertClient(object $task, array $data): void { abort_unless((int) $data['client_id'] === (int) $task->project, 403, 'client_id tidak sesuai dengan task.'); }
    private function active(object $task): object { abort_if($task->archived_at, 409, 'Task sudah diarsipkan.'); return $task; }
    private function hash(int $clientId, array $payload): string { ksort($payload); return hash('sha256', json_encode(['client_id' => $clientId, 'payload' => $payload], JSON_THROW_ON_ERROR)); }
    private function read(int $id): array { return (array) DB::table('task')->where('id', $id)->first(['id', 'nama_task', 'project as client_id', 'divisi as division_id', 'pic as pic_id', 'priority', 'menit as estimated_minutes', 'id_manajement_konten as content_plan_id', 'tanggal_mulai as start_date', 'tanggal_deadline as deadline', 'note', 'status', 'jam_mulai as start_time', 'tanggal_approve as completed_at', 'archived_at']); }
}
