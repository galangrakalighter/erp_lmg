<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class MarketingApprovalRecords
{
    private const DEFINITIONS = [
        'content_plan' => ['table' => 'data_jasa', 'client' => 'produk_client_id'],
        'campaign' => ['table' => 'marketing_campaigns', 'client' => 'client_id'],
        'brand_guideline' => ['table' => 'brand_identify', 'client' => 'produk_client_id'],
        'four_p' => ['table' => 'analize_4p', 'client' => 'produk_client_id'],
        'swot' => ['table' => 'analize_swot', 'client' => 'produk_client_id'],
        'segmentation' => ['table' => 'segmentasi', 'client' => 'produk_client_id'],
        'target_audience' => ['table' => 'target_audience', 'client' => 'produk_client_id'],
        'positioning' => ['table' => 'positioning', 'client' => 'produk_client_id'],
        'task' => ['table' => 'task', 'client' => 'project'],
        'asset_metadata' => ['table' => 'marketing_asset_metadata', 'client' => 'client_id'],
    ];

    public static function types(): array
    {
        return array_keys(self::DEFINITIONS);
    }

    public static function find(string $type, int $id): ?object
    {
        $definition = self::DEFINITIONS[$type] ?? null;
        if (! $definition) {
            return null;
        }

        return DB::table($definition['table'])->where('id', $id)->first(['id', $definition['client'].' as client_id', 'archived_at']);
    }
}
