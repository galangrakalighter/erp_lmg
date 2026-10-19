<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

final class BrandGuidelineMutationAudit
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function record(Authenticatable $user, string $action, int $id, int $clientId, array $after = []): void
    {
        $this->activityLog->record([
            'action' => strtoupper($action), 'module' => 'brand_guideline', 'target_id' => $id, 'target_type' => 'brand_identify',
            'description' => 'Brand guideline mutation '.$action,
            'context' => ['client_id' => $clientId, 'tool' => 'marketing_'.strtolower($action).'_brand_guideline', 'record_id' => $id, 'result' => 'success', 'status' => 'completed', 'occurred_at' => now()->toIso8601String()],
            'new_data' => in_array(strtoupper($action), ['CREATE', 'ARCHIVE'], true) ? [] : array_diff_key($after, array_flip(['confirmation_token'])),
        ], $user);
    }
}
