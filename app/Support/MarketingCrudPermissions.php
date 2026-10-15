<?php

namespace App\Support;

final class MarketingCrudPermissions
{
    public const CONTENT_PLAN_CREATE = 'marketing.content_plan.create';
    public const CONTENT_PLAN_UPDATE = 'marketing.content_plan.update';
    public const CONTENT_PLAN_ARCHIVE = 'marketing.content_plan.archive';

    public static function all(): array
    {
        return [self::CONTENT_PLAN_CREATE, self::CONTENT_PLAN_UPDATE, self::CONTENT_PLAN_ARCHIVE];
    }
}
