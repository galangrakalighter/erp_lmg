<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;

final class MarketingApprovalMutationAudit
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function record(Authenticatable $user, object $event): void
    {
        $this->activityLog->record([
            'action' => strtoupper((string) $event->action),
            'module' => 'marketing_approval_event',
            'target_id' => $event->id,
            'target_type' => 'marketing_approval_events',
            'description' => 'Marketing approval event '.strtolower((string) $event->action),
            'context' => [
                'client_id' => (int) $event->client_id,
                'tool' => 'marketing_'.strtolower((string) $event->action),
                'record_type' => (string) $event->record_type,
                'record_id' => (int) $event->record_id,
                'operation' => strtolower((string) $event->action),
                'result' => 'success',
                'status' => (string) $event->status,
                'audit_reference' => (string) $event->audit_reference,
                'occurred_at' => (string) $event->created_at,
            ],
        ], $user);
    }
}
