<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

final class CampaignMutationAudit
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function record(Authenticatable $user, string $action, int $id, int $clientId, array $after = []): void
    {
        $safe = in_array(strtoupper($action), ['CREATE', 'ARCHIVE'], true) ? [] : array_diff_key($after, array_flip(['confirmation_token', 'password', 'credential', 'secret', 'private_key']));
        $this->activityLog->record([
            'action' => strtoupper($action),
            'module' => 'campaign',
            'target_id' => $id,
            'target_type' => 'marketing_campaigns',
            'description' => 'Campaign mutation '.$action,
            'context' => [
                'client_id' => $clientId,
                'tool' => 'marketing_'.strtolower($action).'_campaign',
                'record_id' => $id,
                'operation' => strtolower($action),
                'result' => 'success',
                'status' => 'completed',
                'occurred_at' => now()->toIso8601String(),
            ],
            'new_data' => $safe,
        ], $user);
    }
}
