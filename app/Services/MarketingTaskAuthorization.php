<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class MarketingTaskAuthorization
{
    private const PERMISSIONS = [
        MarketingCrudPermissions::TASK_CREATE, MarketingCrudPermissions::TASK_UPDATE, MarketingCrudPermissions::TASK_ARCHIVE,
        MarketingCrudPermissions::TASK_CHANGE_PIC, MarketingCrudPermissions::TASK_CHANGE_DEADLINE,
        MarketingCrudPermissions::TASK_CHANGE_STATUS, MarketingCrudPermissions::TASK_COMPLETE,
    ];

    public function __construct(private readonly MarketingAccessService $access) {}

    public function assert(Authenticatable $user, int $clientId, string $permission, ?object $task = null): void
    {
        abort_unless(in_array($permission, self::PERMISSIONS, true), 403, 'Permission task tidak valid.');
        $this->access->assertClientAccess($user, $clientId);
        abort_unless((bool) data_get($user, 'isAdmin', false) || DB::table('user_client')->where('user_id', $user->getAuthIdentifier())->where('client_id', $clientId)->exists(), 403, 'Anda tidak memiliki assignment langsung ke client task ini.');
        $role = DigitalMarketingPermissions::role($user);
        $manager = DigitalMarketingPermissions::isManager($role);
        $picCanChangeStatus = $permission === MarketingCrudPermissions::TASK_CHANGE_STATUS && $task && (int) $task->pic === (int) $user->getAuthIdentifier();
        abort_unless((bool) data_get($user, 'isAdmin', false) || $manager || $picCanChangeStatus, 403, 'Anda tidak memiliki permission operasi task ini.');
    }

    public function object(Authenticatable $user, int $id, string $permission): object
    {
        $task = DB::table('task')->where('id', $id)->first(['id', 'project', 'divisi', 'pic', 'status', 'tanggal_mulai', 'tanggal_deadline', 'completion_proof_path', 'archived_at']);
        abort_unless($task, 404, 'Task tidak ditemukan.');
        $this->assert($user, (int) $task->project, $permission, $task);

        return $task;
    }
}
