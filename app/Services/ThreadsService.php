<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThreadsService
{
    public function getAnalysis(string $token, string $accountId, ?int $since = null, ?int $until = null, bool $fetch = false): array
    {
        $version = config('services.threads.graph_version', 'v1.0');
        $baseUrl = "https://graph.threads.net/{$version}";
        $items = [];
        $error = null;

        try {
            $response = Http::timeout(60)->get("{$baseUrl}/{$accountId}/threads", [
                'fields' => 'id,text,timestamp,media_type,media_product_type,permalink,shortcode,thumbnail_url',
                'limit' => $fetch ? 100 : 15,
                'access_token' => $token,
            ]);

            if (! $response->successful()) {
                return ['data' => [], 'followers' => 0, 'error' => $this->message($response->json())];
            }

            $payload = $response->json();
            $media = $payload['data'] ?? [];
            if ($fetch) {
                while (filled(data_get($payload, 'paging.next')) && count($media) < 1000) {
                    $next = Http::timeout(60)->get(data_get($payload, 'paging.next'));
                    if (! $next->successful()) {
                        break;
                    }
                    $payload = $next->json();
                    $media = array_merge($media, $payload['data'] ?? []);
                }
            }

            foreach ($media as $item) {
                $timestamp = isset($item['timestamp']) ? Carbon::parse($item['timestamp']) : null;
                if (! $timestamp || ($since && $timestamp->timestamp < $since) || ($until && $timestamp->timestamp > $until)) {
                    continue;
                }

                $insights = $this->mediaInsights($baseUrl, (string) $item['id'], $token);
                $text = trim((string) ($item['text'] ?? ''));
                $items[] = [
                    'id' => (string) $item['id'],
                    'judul' => $text !== '' ? strtok($text, "\r\n") : 'Postingan Threads',
                    'text' => $text,
                    'tanggal_posting' => $timestamp->format('d M Y'),
                    'timestamp' => $timestamp->timestamp,
                    'jenis' => $item['media_type'] ?? $item['media_product_type'] ?? 'THREADS',
                    'link' => $item['permalink'] ?? '#',
                    'views' => $insights['views'] ?? 0,
                    'likes' => $insights['likes'] ?? 0,
                    'replies' => $insights['replies'] ?? 0,
                    'reposts' => $insights['reposts'] ?? 0,
                    'quotes' => $insights['quotes'] ?? 0,
                    'shares' => $insights['shares'] ?? 0,
                ];
            }

            $followers = $this->profileMetric($baseUrl, $accountId, $token, 'followers_count');

            return [
                'data' => collect($items)->sortBy('timestamp')->values()->all(),
                'followers' => $followers,
                'error' => $error,
            ];
        } catch (\Throwable $exception) {
            Log::warning('Threads API gagal mengambil data analisis.', ['message' => $exception->getMessage()]);

            return ['data' => [], 'followers' => 0, 'error' => 'Data Threads belum dapat diambil. Periksa token dan izin threads_manage_insights.'];
        }
    }

    private function mediaInsights(string $baseUrl, string $mediaId, string $token): array
    {
        $response = Http::timeout(30)->get("{$baseUrl}/{$mediaId}/insights", [
            'metric' => 'views,likes,replies,reposts,quotes,shares',
            'access_token' => $token,
        ]);

        if (! $response->successful()) {
            Log::info('Insight postingan Threads tidak tersedia.', ['media_id' => $mediaId, 'error' => $this->message($response->json())]);
            return [];
        }

        return collect($response->json('data', []))->mapWithKeys(function (array $metric) {
            $value = data_get($metric, 'total_value.value', data_get($metric, 'values.0.value', 0));
            return [($metric['name'] ?? '') => is_numeric($value) ? (float) $value : 0];
        })->filter(fn ($value, $key) => $key !== '')->all();
    }

    private function profileMetric(string $baseUrl, string $accountId, string $token, string $metric): float
    {
        $response = Http::timeout(30)->get("{$baseUrl}/{$accountId}/threads_insights", [
            'metric' => $metric,
            'access_token' => $token,
        ]);
        if (! $response->successful()) {
            return 0;
        }

        $item = collect($response->json('data', []))->firstWhere('name', $metric);
        $value = data_get($item, 'total_value.value', data_get($item, 'values.0.value', 0));

        return is_numeric($value) ? (float) $value : 0;
    }

    private function message(array $payload): string
    {
        return data_get($payload, 'error.message')
            ?? data_get($payload, 'error_description')
            ?? 'Threads menolak permintaan data.';
    }
}
