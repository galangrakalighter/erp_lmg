<?php

namespace App\Services;

use App\Models\AnalysisSnapshot;

class AnalysisSnapshotService
{
    public function captureAndCompare(int $clientId, string $platform, string $source, array $metrics): array
    {
        $metrics = collect($metrics)->map(fn ($value) => is_numeric($value) ? (float) $value : 0)->all();
        $today = now()->toDateString();

        AnalysisSnapshot::updateOrCreate(
            [
                'client_id' => $clientId,
                'platform' => strtolower($platform),
                'source' => strtolower($source),
                'snapshot_date' => $today,
            ],
            ['metrics' => $metrics, 'captured_at' => now()]
        );

        return [
            'current' => $metrics,
            'three_days' => $this->comparison($clientId, $platform, $source, $metrics, 3),
            'seven_days' => $this->comparison($clientId, $platform, $source, $metrics, 7),
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
            $unavailable = fn (int $days) => ['available' => false, 'days' => $days, 'previous_date' => null, 'metrics' => []];
            return ['current' => [], 'three_days' => $unavailable(3), 'seven_days' => $unavailable(7)];
        }

        $metrics = $latest->metrics ?? [];
        return [
            'current' => $metrics,
            'three_days' => $this->comparison($clientId, $platform, $source, $metrics, 3),
            'seven_days' => $this->comparison($clientId, $platform, $source, $metrics, 7),
        ];
    }

    private function comparison(int $clientId, string $platform, string $source, array $current, int $days): array
    {
        $previous = AnalysisSnapshot::query()
            ->where('client_id', $clientId)
            ->where('platform', strtolower($platform))
            ->where('source', strtolower($source))
            ->where('snapshot_date', '<=', now()->subDays($days)->toDateString())
            ->latest('snapshot_date')
            ->first();

        if (! $previous) {
            return ['available' => false, 'days' => $days, 'previous_date' => null, 'metrics' => []];
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
        ];
    }
}
