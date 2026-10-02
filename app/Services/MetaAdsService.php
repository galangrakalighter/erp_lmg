<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
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
            'fields' => implode(',', [
                'ad_id', 'ad_name', 'adset_id', 'adset_name', 'campaign_id', 'campaign_name',
                'reach', 'impressions', 'frequency',
                'spend', 'social_spend', 'clicks', 'unique_clicks', 'ctr', 'unique_ctr',
                'cpc', 'cpm', 'cpp', 'inline_link_clicks', 'inline_link_click_ctr',
                'inline_post_engagement', 'outbound_clicks', 'conversions', 'conversion_values',
                'actions', 'video_play_actions', 'video_thruplay_watched_actions',
                'video_p25_watched_actions', 'video_p50_watched_actions',
                'video_p75_watched_actions', 'video_p100_watched_actions', 'date_start', 'date_stop',
            ]),
            'breakdowns' => 'publisher_platform',
            'limit' => 500,
        ];

        if ($since || $until) {
            $query['time_range'] = json_encode([
                'since' => ($since ?? now()->subDays(30))->toDateString(),
                'until' => ($until ?? now())->toDateString(),
            ], JSON_THROW_ON_ERROR);
        } else {
            $query['date_preset'] = 'last_30d';
        }

        try {
            $response = Http::connectTimeout(15)
                ->timeout(120)
                ->retry(2, 600, throw: false)
                ->withToken($accessToken)
                ->withOptions(['verify' => false])
                ->get("https://graph.facebook.com/{$version}/{$accountId}/insights", $query);
        } catch (ConnectionException) {
            Log::warning('Meta Ads Insights gagal terhubung karena gangguan jaringan.', [
                'account_id' => $accountId,
                'platform' => $platform,
            ]);

            return [
                'data' => [],
                'error' => 'Server belum dapat terhubung ke Meta Ads. Periksa koneksi internet atau DNS server, lalu coba muat ulang halaman.',
            ];
        }

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
                $sumValues = static fn ($values): float => collect($values ?? [])->sum(
                    fn ($value) => (float) ($value['value'] ?? 0)
                );
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
                    'campaign_id' => $row['campaign_id'] ?? null,
                    'campaign' => $row['campaign_name'] ?? '-',
                    'adset_id' => $row['adset_id'] ?? null,
                    'adset' => $row['adset_name'] ?? 'Set iklan tanpa nama',
                    'platform' => $platformKey,
                    'tanggal_posting' => $date ? Carbon::parse($date)->format('d M Y') : '-',
                    'timestamp' => $date ? Carbon::parse($date)->timestamp : 0,
                    'reach' => (int) round((float) ($row['reach'] ?? 0)),
                    'impressions' => (int) round((float) ($row['impressions'] ?? 0)),
                    'clicks' => (int) round((float) ($row['clicks'] ?? 0)),
                    'unique_clicks' => (int) round((float) ($row['unique_clicks'] ?? 0)),
                    'engagement' => $engagement,
                    'spend' => (float) ($row['spend'] ?? 0),
                    'social_spend' => (float) ($row['social_spend'] ?? 0),
                    'frequency' => (float) ($row['frequency'] ?? 0),
                    'ctr' => (float) ($row['ctr'] ?? 0),
                    'unique_ctr' => (float) ($row['unique_ctr'] ?? 0),
                    'cpc' => (float) ($row['cpc'] ?? 0),
                    'cpm' => (float) ($row['cpm'] ?? 0),
                    'cpp' => (float) ($row['cpp'] ?? 0),
                    'inline_link_clicks' => (int) round((float) ($row['inline_link_clicks'] ?? 0)),
                    'inline_link_click_ctr' => (float) ($row['inline_link_click_ctr'] ?? 0),
                    'inline_post_engagement' => (int) round((float) ($row['inline_post_engagement'] ?? 0)),
                    'outbound_clicks' => (int) round($sumValues($row['outbound_clicks'] ?? [])),
                    'conversions' => $sumValues($row['conversions'] ?? []),
                    'conversion_values' => $sumValues($row['conversion_values'] ?? []),
                    'video_plays' => (int) round($sumValues($row['video_play_actions'] ?? [])),
                    'video_thruplays' => (int) round($sumValues($row['video_thruplay_watched_actions'] ?? [])),
                    'video_p25' => (int) round($sumValues($row['video_p25_watched_actions'] ?? [])),
                    'video_p50' => (int) round($sumValues($row['video_p50_watched_actions'] ?? [])),
                    'video_p75' => (int) round($sumValues($row['video_p75_watched_actions'] ?? [])),
                    'video_p100' => (int) round($sumValues($row['video_p100_watched_actions'] ?? [])),
                ];
            })
            ->sortBy('timestamp')
            ->values()
            ->all();

        return ['data' => $rows, 'error' => null];
    }
}
