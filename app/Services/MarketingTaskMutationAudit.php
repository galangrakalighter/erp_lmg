<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

final class MarketingTaskMutationAudit
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function record(Authenticatable $user, string $action, int $id, int $clientId, array $data = []): void
    {
        $safe = array_diff_key($data, array_flip(['confirmation_token', 'password', 'credential', 'secret', 'private_key', 'completion_proof_path']));
        $tool = match (strtoupper($action)) {
            'CREATE' => 'marketing_create_task', 'UPDATE' => 'marketing_update_task', 'ARCHIVE' => 'marketing_archive_task',
            'CHANGE_PIC' => 'marketing_change_task_pic', 'CHANGE_DEADLINE' => 'marketing_change_task_deadline',
            'CHANGE_STATUS' => 'marketing_change_task_status', 'COMPLETE' => 'marketing_complete_task',
            default => 'marketing_task_mutation',
        };
        $this->activityLog->record([
            'action' => strtoupper($action), 'module' => 'marketing_task', 'target_id' => $id, 'target_type' => 'task',
            'description' => 'Digital Marketing task mutation '.$action,
            'context' => ['client_id' => $clientId, 'tool' => $tool, 'record_id' => $id, 'operation' => strtolower($action), 'result' => 'success', 'status' => 'completed', 'occurred_at' => now()->toIso8601String()],
            'new_data' => $safe,
        ], $user);
    }
}
