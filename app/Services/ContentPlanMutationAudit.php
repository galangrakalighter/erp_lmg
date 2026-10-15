<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

class ContentPlanMutationAudit
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function record(Authenticatable $user, string $action, int $id, int $clientId, array $before = [], array $after = []): void
    {
        $this->activityLog->record([
            'action' => strtoupper($action), 'module' => 'content_plan', 'target_id' => $id,
            'target_type' => 'data_jasa', 'description' => 'Content plan mutation '.$action,
            'context' => ['client_id' => $clientId, 'module' => 'content_plan', 'tool' => 'marketing_'.strtolower($action).'_content_plan', 'result' => 'success', 'status' => 'completed', 'record_id' => $id, 'occurred_at' => now()->toIso8601String()],
            'old_data' => $before, 'new_data' => $after,
        ], $user);
    }
}
