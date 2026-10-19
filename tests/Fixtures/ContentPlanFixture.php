<?php

namespace Tests\Fixtures;

/** Deterministic, non-production identities and records for content-plan tests. */
final class ContentPlanFixture
{
    public const OWNER_USER_ID = 1;
    public const SECOND_USER_ID = 2;
    public const MARKETING_DIVISION_ID = 10;
    public const OTHER_DIVISION_ID = 20;
    public const FIRST_CLIENT_ID = 1;
    public const SECOND_CLIENT_ID = 2;

    public static function permissions(): array
    {
        return [
            'marketing.content_plan.create',
            'marketing.content_plan.update',
            'marketing.content_plan.archive',
        ];
    }

    public static function activePlan(int $clientId = self::FIRST_CLIENT_ID): array
    {
        return ['produk_client_id' => $clientId, 'judul' => 'Fixture active content plan', 'archived_at' => null, 'archived_by' => null];
    }

    public static function archivedPlan(int $clientId = self::FIRST_CLIENT_ID): array
    {
        return ['produk_client_id' => $clientId, 'judul' => 'Fixture archived content plan', 'archived_at' => now(), 'archived_by' => self::OWNER_USER_ID];
    }
}
