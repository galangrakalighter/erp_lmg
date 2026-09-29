<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAdsService
{
    public function getPaidPosts(
        string $adAccountId,
        string $accessToken,
        string $platform,
        ?Carbon $since = null,
        ?Carbon $until = null,
    ): array {
        $version = config('services.meta.graph_version', 'v23.0');
        $accountId = str_starts_with($adAccountId, 'act_') ? $adAccountId : 'act_'.$adAccountId;
        $query = [
            'level' => 'ad',
            'fields' => 'ad_id,ad_name,campaign_name,reach,impressions,spend,clicks,actions,date_start,date_stop',
            'breakdowns' => 'publisher_platform',
            'limit' => 500,
            'access_token' => $accessToken,
        ];

        if ($since || $until) {
            $query['time_range'] = json_encode([
                'since' => ($since ?? now()->subDays(30))->toDateString(),
                'until' => ($until ?? now())->toDateString(),
            ], JSON_THROW_ON_ERROR);
        } else {
            $query['date_preset'] = 'last_30d';
        }

        $response = Http::timeout(120)
            ->withOptions(['verify' => false])
            ->get("https://graph.facebook.com/{$version}/{$accountId}/insights", $query);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?: 'Meta Ads tidak dapat mengembalikan data.';
            Log::warning('Meta Ads Insights gagal dimuat.', [
                'account_id' => $accountId,
                'platform' => $platform,
                'status' => $response->status(),
                'error' => $message,
            ]);

            return ['data' => [], 'error' => $message];
        }

        $platformKey = strtolower($platform);
        $rows = collect($response->json('data', []))
            ->filter(fn ($row) => strtolower((string) ($row['publisher_platform'] ?? '')) === $platformKey)
            ->map(function ($row) use ($platformKey) {
                $actions = collect($row['actions'] ?? [])->mapWithKeys(
                    fn ($action) => [(string) ($action['action_type'] ?? '') => (int) round((float) ($action['value'] ?? 0))]
                );
                $engagement = (int) ($actions->get('post_engagement')
                    ?? $actions->get('page_engagement')
                    ?? $actions->sum());
                $date = $row['date_start'] ?? null;

                return [
                    'id' => $row['ad_id'] ?? null,
                    'judul' => $row['ad_name'] ?? 'Iklan tanpa nama',
                    'campaign' => $row['campaign_name'] ?? '-',
                    'platform' => $platformKey,
                    'tanggal_posting' => $date ? Carbon::parse($date)->format('d M Y') : '-',
                    'timestamp' => $date ? Carbon::parse($date)->timestamp : 0,
                    'reach' => (int) round((float) ($row['reach'] ?? 0)),
                    'impressions' => (int) round((float) ($row['impressions'] ?? 0)),
                    'clicks' => (int) round((float) ($row['clicks'] ?? 0)),
                    'engagement' => $engagement,
                    'spend' => (float) ($row['spend'] ?? 0),
                ];
            })
            ->sortBy('timestamp')
            ->values()
            ->all();

        return ['data' => $rows, 'error' => null];
    }
}
