<?php

namespace App\Services;

use App\Models\AnalysisSnapshot;

class AnalysisSnapshotService
{
    public function captureAndCompare(int $clientId, string $platform, string $source, array $metrics, array $items = []): array
    {
        $metrics = collect($metrics)->map(fn ($value) => is_numeric($value) ? (float) $value : 0)->all();
        $items = $this->normalizeItems($items);
        $today = now()->toDateString();

        AnalysisSnapshot::updateOrCreate(
            [
                'client_id' => $clientId,
                'platform' => strtolower($platform),
                'source' => strtolower($source),
                'snapshot_date' => $today,
            ],
            ['metrics' => $metrics, 'items' => $items, 'captured_at' => now()]
        );

        return [
            'current' => $metrics,
            'three_days' => $this->comparison($clientId, $platform, $source, $metrics, $items, 3),
            'seven_days' => $this->comparison($clientId, $platform, $source, $metrics, $items, 7),
        ];
    }

    public function latestComparisons(int $clientId, string $platform, string $source): array
    {
        $latest = AnalysisSnapshot::query()
            ->where('client_id', $clientId)
            ->where('platform', strtolower($platform))
            ->where('source', strtolower($source))
            ->latest('snapshot_date')
            ->first();

        if (! $latest) {
            $unavailable = fn (int $days) => ['available' => false, 'days' => $days, 'previous_date' => null, 'metrics' => [], 'items' => []];
            return ['current' => [], 'three_days' => $unavailable(3), 'seven_days' => $unavailable(7)];
        }

        $metrics = $latest->metrics ?? [];
        $items = $latest->items ?? [];
        return [
            'current' => $metrics,
            'three_days' => $this->comparison($clientId, $platform, $source, $metrics, $items, 3),
            'seven_days' => $this->comparison($clientId, $platform, $source, $metrics, $items, 7),
        ];
    }

    private function comparison(int $clientId, string $platform, string $source, array $current, array $currentItems, int $days): array
    {
        $previous = AnalysisSnapshot::query()
            ->where('client_id', $clientId)
            ->where('platform', strtolower($platform))
            ->where('source', strtolower($source))
            ->where('snapshot_date', '<=', now()->subDays($days)->toDateString())
            ->latest('snapshot_date')
            ->first();

        if (! $previous) {
            return ['available' => false, 'days' => $days, 'previous_date' => null, 'metrics' => [], 'items' => []];
        }

        $previousMetrics = $previous->metrics ?? [];
        $comparisons = [];
        foreach ($current as $key => $currentValue) {
            $oldValue = (float) ($previousMetrics[$key] ?? 0);
            $delta = (float) $currentValue - $oldValue;
            $comparisons[$key] = [
                'current' => (float) $currentValue,
                'previous' => $oldValue,
                'delta' => $delta,
                'percent' => $oldValue != 0.0 ? ($delta / abs($oldValue)) * 100 : null,
            ];
        }

        return [
            'available' => true,
            'days' => $days,
            'previous_date' => $previous->snapshot_date->toDateString(),
            'metrics' => $comparisons,
            'items' => $this->compareItems($currentItems, $previous->items ?? []),
        ];
    }

    private function normalizeItems(array $items): array
    {
        return collect($items)->map(function ($item) {
            $item = (array) $item;
            $id = (string) ($item['id'] ?? $item['id_post'] ?? $item['link'] ?? $item['permalink'] ?? '');
            $title = trim((string) ($item['title'] ?? $item['judul'] ?? 'Konten tanpa judul'));
            if ($id === '') {
                $id = sha1($title.'|'.($item['date'] ?? $item['tanggal_posting'] ?? ''));
            }

            return [
                'id' => $id,
                'title' => $title !== '' ? $title : 'Konten tanpa judul',
                'date' => $item['date'] ?? $item['tanggal_posting'] ?? null,
                'campaign' => $item['campaign'] ?? null,
                'adset' => $item['adset'] ?? null,
                'metrics' => collect([
                    'reach' => $item['reach'] ?? $item['views'] ?? 0,
                    'impressions' => $item['impressions'] ?? $item['impresi'] ?? $item['views'] ?? 0,
                    'engagement' => $item['engagement'] ?? 0,
                    'likes' => $item['likes'] ?? $item['reaction'] ?? 0,
                    'comments' => $item['comments'] ?? $item['replies'] ?? 0,
                    'shares' => $item['shares'] ?? 0,
                    'clicks' => $item['clicks'] ?? 0,
                    'spend' => $item['spend'] ?? 0,
                ])->map(fn ($value) => is_numeric($value) ? (float) $value : 0)->all(),
            ];
        })->values()->all();
    }

    private function compareItems(array $currentItems, array $previousItems): array
    {
        $previousById = collect($previousItems)->keyBy(fn ($item) => (string) ($item['id'] ?? ''));

        return collect($currentItems)->map(function ($item) use ($previousById) {
            $previous = $previousById->get((string) ($item['id'] ?? ''));
            $metricProgress = [];
            foreach (($item['metrics'] ?? []) as $key => $currentValue) {
                $oldValue = (float) data_get($previous, 'metrics.'.$key, 0);
                $delta = (float) $currentValue - $oldValue;
                $metricProgress[$key] = [
                    'current' => (float) $currentValue,
                    'previous' => $oldValue,
                    'delta' => $delta,
                    'percent' => $oldValue != 0.0 ? ($delta / abs($oldValue)) * 100 : null,
                ];
            }

            $progressScore = (float) data_get($metricProgress, 'engagement.delta', 0)
                ?: (float) data_get($metricProgress, 'reach.delta', 0);

            return array_merge($item, [
                'is_new' => $previous === null,
                'status' => $previous === null ? 'new' : ($progressScore > 0 ? 'up' : ($progressScore < 0 ? 'down' : 'steady')),
                'progress_score' => $progressScore,
                'progress' => $metricProgress,
            ]);
        })->sortByDesc(fn ($item) => $item['is_new'] ? PHP_FLOAT_MAX : $item['progress_score'])
            ->take(50)->values()->all();
    }
}
