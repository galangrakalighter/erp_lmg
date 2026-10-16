<?php

namespace App\Support;

final class MarketingCrudPermissions
{
    public const CONTENT_PLAN_CREATE = 'marketing.content_plan.create';
    public const CONTENT_PLAN_UPDATE = 'marketing.content_plan.update';
    public const CONTENT_PLAN_ARCHIVE = 'marketing.content_plan.archive';
    public const BRAND_GUIDELINE_CREATE = 'marketing.brand_guideline.create';
    public const BRAND_GUIDELINE_UPDATE = 'marketing.brand_guideline.update';
    public const BRAND_GUIDELINE_ARCHIVE = 'marketing.brand_guideline.archive';
    public const MARKET_RESEARCH_CREATE = 'marketing.market_research.create';
    public const MARKET_RESEARCH_UPDATE = 'marketing.market_research.update';
    public const MARKET_RESEARCH_ARCHIVE = 'marketing.market_research.archive';
    public const CAMPAIGN_CREATE = 'marketing.campaign.create';
    public const CAMPAIGN_UPDATE = 'marketing.campaign.update';
    public const CAMPAIGN_ARCHIVE = 'marketing.campaign.archive';
    public const APPROVAL_SUBMIT = 'marketing.approval.submit';
    public const APPROVAL_APPROVE = 'marketing.approval.approve';
    public const APPROVAL_REJECT = 'marketing.approval.reject';
    public const APPROVAL_REQUEST_REVISION = 'marketing.approval.request_revision';
    public const TASK_CREATE = 'marketing.task.create';
    public const TASK_UPDATE = 'marketing.task.update';
    public const TASK_ARCHIVE = 'marketing.task.archive';
    public const TASK_CHANGE_PIC = 'marketing.task.change_pic';
    public const TASK_CHANGE_DEADLINE = 'marketing.task.change_deadline';
    public const TASK_CHANGE_STATUS = 'marketing.task.change_status';
    public const TASK_COMPLETE = 'marketing.task.complete';
    public const ASSET_METADATA_CREATE = 'marketing.asset_metadata.create';
    public const ASSET_METADATA_UPDATE = 'marketing.asset_metadata.update';
    public const ASSET_METADATA_ARCHIVE = 'marketing.asset_metadata.archive';

    public static function all(): array
    {
        return [self::CONTENT_PLAN_CREATE, self::CONTENT_PLAN_UPDATE, self::CONTENT_PLAN_ARCHIVE, self::BRAND_GUIDELINE_CREATE, self::BRAND_GUIDELINE_UPDATE, self::BRAND_GUIDELINE_ARCHIVE, self::MARKET_RESEARCH_CREATE, self::MARKET_RESEARCH_UPDATE, self::MARKET_RESEARCH_ARCHIVE, self::CAMPAIGN_CREATE, self::CAMPAIGN_UPDATE, self::CAMPAIGN_ARCHIVE, self::APPROVAL_SUBMIT, self::APPROVAL_APPROVE, self::APPROVAL_REJECT, self::APPROVAL_REQUEST_REVISION, self::TASK_CREATE, self::TASK_UPDATE, self::TASK_ARCHIVE, self::TASK_CHANGE_PIC, self::TASK_CHANGE_DEADLINE, self::TASK_CHANGE_STATUS, self::TASK_COMPLETE, self::ASSET_METADATA_CREATE, self::ASSET_METADATA_UPDATE, self::ASSET_METADATA_ARCHIVE];
    }
}
