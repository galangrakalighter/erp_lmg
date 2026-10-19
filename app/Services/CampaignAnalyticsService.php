<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CampaignAnalyticsService
{
    public const METRICS = ['reach', 'impressions', 'engagement', 'clicks', 'spend', 'leads', 'conversions'];

    public function campaigns(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $rows = DB::table('marketing_campaigns')
            ->where('client_id', $clientId)
            ->whereNull('archived_at')
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->when(filled($filters['platform'] ?? null), fn ($query) => $query->whereRaw('LOWER(platform) = ?', [strtolower($filters['platform'])]))
            ->when(filled($filters['source'] ?? null), fn ($query) => $query->whereRaw('LOWER(source) = ?', [strtolower($filters['source'])]))
            ->when(filled($filters['campaign_id'] ?? null), fn ($query) => $query->where('id', (int) $filters['campaign_id']))
            ->orderByDesc('updated_at')
            ->get(['id', 'owner_user_id', 'name', 'status', 'platform', 'source', 'start_date', 'end_date', 'objective', 'budget', 'currency', 'target', 'updated_at'])
            ->map(fn ($row): array => [
                'campaign_id' => (int) $row->id,
                'campaign' => (string) $row->name,
                'owner_user_id' => $row->owner_user_id === null ? null : (int) $row->owner_user_id,
                'status' => (string) $row->status,
                'platform' => $row->platform,
                'source' => (string) $row->source,
                'start_date' => (string) $row->start_date,
                'end_date' => (string) $row->end_date,
                'objective' => $row->objective,
                'budget' => $row->budget === null ? null : (float) $row->budget,
                'currency' => $row->currency,
                'target' => $row->target,
                'last_updated_at' => (string) $row->updated_at,
            ]);

        return $this->paginate($rows, $filters, [
            'period' => ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()],
            'source' => 'marketing_campaigns',
            'completeness' => ['status' => $rows->isEmpty() ? 'no_data' : 'complete', 'reasons' => $rows->isEmpty() ? ['Tidak ada campaign kanonik aktif pada periode dan filter yang dipilih.'] : []],
            'budget_availability' => $this->availability($rows, 'budget'),
            'target_availability' => $this->availability($rows, 'target'),
            'notice' => 'Daftar campaign berasal dari tabel kanonik; metrik performa tetap berasal dari snapshot melalui tool performa.',
        ]);
    }

    public function performance(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $snapshots = $this->latestBySeries($this->snapshots($clientId, $filters, $start, $end));
        $rows = $snapshots->map(function ($snapshot): array {
            $raw = $this->decodeObject($snapshot->metrics);

            return [
                'platform' => strtolower((string) $snapshot->platform),
                'source' => strtolower((string) $snapshot->source),
                'snapshot_date' => (string) $snapshot->snapshot_date,
                'currency' => $this->currency($raw['currency'] ?? null),
                'metrics' => $this->withRates($this->metrics($raw)),
                'last_updated_at' => (string) $snapshot->captured_at,
            ];
        })->values();

        $comparisons = $rows->groupBy('platform')->map(function (Collection $platformRows, string $platform): ?array {
            $paid = $platformRows->firstWhere('source', 'paid');
            $organic = $platformRows->firstWhere('source', 'organic');
            if (! $paid || ! $organic) {
                return null;
            }
            $metrics = [];
            foreach ([...self::METRICS, 'ctr', 'cpc', 'cpm'] as $metric) {
                $paidValue = data_get($paid, 'metrics.'.$metric);
                $organicValue = data_get($organic, 'metrics.'.$metric);
                if (is_numeric($paidValue) && is_numeric($organicValue)) {
                    $metrics[$metric] = ['paid' => $paidValue, 'organic' => $organicValue, 'difference' => round($paidValue - $organicValue, 6)];
                }
            }

            return ['platform' => $platform, 'metrics' => $metrics];
        })->filter()->values();

        return ['data' => $rows, 'pagination' => null, 'paid_vs_organic' => $comparisons, 'meta' => $this->meta($start, $end, $snapshots, $rows->isEmpty() ? 'no_data' : 'complete', [
            'calculation' => ['ctr' => 'clicks / impressions * 100', 'cpc' => 'spend / clicks', 'cpm' => 'spend / impressions * 1000'],
            'notice' => 'Setiap seri memakai snapshot terbaru dalam periode; nilai lintas platform tidak dijumlahkan.',
        ])];
    }

    public function comparison(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $compareStart = CarbonImmutable::parse($filters['compare_start_date'], 'Asia/Jakarta')->startOfDay();
        $compareEnd = CarbonImmutable::parse($filters['compare_end_date'], 'Asia/Jakarta')->endOfDay();
        $current = $this->latestBySeries($this->snapshots($clientId, $filters, $start, $end));
        $previous = $this->latestBySeries($this->snapshots($clientId, $filters, $compareStart, $compareEnd));
        $keys = $current->concat($previous)->map(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source))->unique();

        $rows = $keys->map(function (string $key) use ($current, $previous): array {
            $now = $current->first(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source) === $key);
            $before = $previous->first(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source) === $key);
            [$platform, $source] = explode('|', $key, 2);
            $nowMetrics = $now ? $this->withRates($this->metrics($this->decodeObject($now->metrics))) : [];
            $beforeMetrics = $before ? $this->withRates($this->metrics($this->decodeObject($before->metrics))) : [];
            $changes = [];
            foreach ([...self::METRICS, 'ctr', 'cpc', 'cpm'] as $metric) {
                if (! isset($nowMetrics[$metric], $beforeMetrics[$metric])) {
                    continue;
                }
                $difference = round($nowMetrics[$metric] - $beforeMetrics[$metric], 6);
                $changes[$metric] = [
                    'current' => $nowMetrics[$metric], 'comparison' => $beforeMetrics[$metric], 'difference' => $difference,
                    'change_percent' => $beforeMetrics[$metric] == 0 ? null : round($difference / $beforeMetrics[$metric] * 100, 6),
                ];
            }

            return ['platform' => $platform, 'source' => $source, 'metrics' => $changes];
        })->values();

        return ['data' => $rows, 'pagination' => null, 'meta' => $this->meta($start, $end, $current->concat($previous), $rows->isEmpty() ? 'no_data' : 'complete', [
            'comparison_period' => ['start_date' => $compareStart->toDateString(), 'end_date' => $compareEnd->toDateString()],
            'notice' => 'Perubahan hanya dihitung saat kedua periode memiliki nilai numerik; persentase kosong bila baseline nol.',
        ])];
    }

    public function channelAnalysis(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $snapshots = $this->latestBySeries($this->snapshots($clientId, $filters, $start, $end));
        $classified = collect();
        $unclassified = 0;
        foreach ($snapshots as $snapshot) {
            $invalid = 0;
            foreach ($this->decodeList($snapshot->items, $invalid) as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $classification = $this->safeString($item['channel_classification'] ?? $item['classification'] ?? $item['category'] ?? null, 100);
                if (! $classification) {
                    $unclassified++;

                    continue;
                }
                $snapshotMetrics = $this->decodeObject($snapshot->metrics);
                $classified->push([
                    'classification' => $classification,
                    'currency' => $this->currency($item['currency'] ?? ($snapshotMetrics['currency'] ?? null)),
                    'metrics' => $this->metrics($item['metrics'] ?? []),
                ]);
            }
        }
        $data = $classified->groupBy('classification')->map(function (Collection $rows, string $classification): array {
            $metrics = [];
            $spendRows = $rows->filter(fn ($row) => isset($row['metrics']['spend']));
            $currencies = $spendRows->pluck('currency')->filter()->unique()->values();
            $safeSpend = $spendRows->isEmpty() || ($currencies->count() === 1 && $currencies->first() !== null && $spendRows->whereNull('currency')->isEmpty());
            foreach (self::METRICS as $metric) {
                if ($metric === 'spend' && ! $safeSpend) {
                    continue;
                }
                $values = $rows->pluck('metrics.'.$metric)->filter(fn ($value) => is_numeric($value));
                if ($values->isNotEmpty()) {
                    $metrics[$metric] = round($values->sum(), 6);
                }
            }

            return [
                'classification' => $classification,
                'classification_source' => 'stored',
                'item_count' => $rows->count(),
                'currency' => $safeSpend && $currencies->count() === 1 ? $currencies->first() : null,
                'spend_aggregation_status' => $safeSpend ? 'available' : 'withheld_mixed_or_missing_currency',
                'metrics' => $this->withRates($metrics),
            ];
        })->values();

        $recommendations = [];
        if ($snapshots->isNotEmpty() && $classified->isEmpty()) {
            $recommendations[] = ['recommendation' => 'Lengkapi klasifikasi kanal pada data sumber sebelum membandingkan ATL, TTL, BTL, atau kategori lain.', 'evidence' => ['unclassified_item_count' => $unclassified]];
        }
        foreach ($data as $row) {
            if (isset($row['metrics']['spend']) && ! isset($row['metrics']['conversions'])) {
                $recommendations[] = ['recommendation' => 'Lengkapi atribusi conversion untuk kategori '.$row['classification'].' sebelum menilai efisiensi spend.', 'evidence' => ['classification' => $row['classification'], 'spend' => $row['metrics']['spend']]];
            }
        }

        return ['data' => $data, 'pagination' => null, 'conclusions' => $data->isEmpty()
            ? ['Data belum cukup untuk menyimpulkan performa berdasarkan klasifikasi kanal.']
            : ['Analisis hanya mencakup klasifikasi dan angka yang tersimpan eksplisit pada snapshot.'],
            'recommendations' => $recommendations,
            'meta' => $this->meta($start, $end, $snapshots, $snapshots->isEmpty() ? 'no_data' : (($unclassified > 0) ? 'partial' : 'complete'), [
                'unclassified_item_count' => $unclassified,
                'classification_method' => 'Field channel_classification, classification, atau category pada item snapshot; tidak ada klasifikasi yang ditebak.',
            ])];
    }

    private function snapshots(int $clientId, array $filters, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return DB::table('analysis_snapshots')->where('client_id', $clientId)
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->when(filled($filters['platform'] ?? null), fn ($query) => $query->whereRaw('LOWER(platform) = ?', [strtolower($filters['platform'])]))
            ->when(filled($filters['source'] ?? null), fn ($query) => $query->whereRaw('LOWER(source) = ?', [strtolower($filters['source'])]))
            ->orderBy('captured_at')->get(['platform', 'source', 'snapshot_date', 'captured_at', 'metrics', 'items']);
    }

    private function latestBySeries(Collection $rows): Collection
    {
        return $rows->groupBy(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source))->map->last()->values();
    }

    private function period(array $filters): array
    {
        return [CarbonImmutable::parse($filters['start_date'], 'Asia/Jakarta')->startOfDay(), CarbonImmutable::parse($filters['end_date'], 'Asia/Jakarta')->endOfDay()];
    }

    private function metrics(mixed $raw): array
    {
        $raw = is_array($raw) ? $raw : $this->decodeObject($raw);
        $metrics = [];
        foreach (self::METRICS as $metric) {
            $value = $this->number($raw[$metric] ?? null);
            if ($value !== null) {
                $metrics[$metric] = $value;
            }
        }

        return $metrics;
    }

    private function withRates(array $metrics): array
    {
        if (isset($metrics['clicks'], $metrics['impressions']) && $metrics['impressions'] > 0) {
            $metrics['ctr'] = round($metrics['clicks'] / $metrics['impressions'] * 100, 6);
        }
        if (isset($metrics['spend'], $metrics['clicks']) && $metrics['clicks'] > 0) {
            $metrics['cpc'] = round($metrics['spend'] / $metrics['clicks'], 6);
        }
        if (isset($metrics['spend'], $metrics['impressions']) && $metrics['impressions'] > 0) {
            $metrics['cpm'] = round($metrics['spend'] / $metrics['impressions'] * 1000, 6);
        }

        return $metrics;
    }

    private function decodeObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    private function decodeList(mixed $value, int &$invalid): array
    {
        $decoded = $this->decodeObject($value);
        if ($value !== null && $value !== '' && ($decoded === [] && $value !== '[]')) {
            $invalid++;
        }

        return array_is_list($decoded) ? $decoded : [];
    }

    private function number(mixed $value): ?float
    {
        return is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)) ? (float) $value : null;
    }

    private function safeString(mixed $value, int $max): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }
        $value = trim(strip_tags((string) $value));

        return $value === '' ? null : mb_substr($value, 0, $max);
    }

    private function safeDate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private function currency(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z]{3}$/', trim($value)) ? strtoupper(trim($value)) : null;
    }

    private function availability(Collection $rows, string $field): array
    {
        $available = $rows->filter(fn ($row) => $row[$field] !== null)->count();

        return ['status' => $rows->isEmpty() ? 'no_data' : ($available === $rows->count() ? 'complete' : ($available ? 'partial' : 'unavailable')), 'available_count' => $available];
    }

    private function meta(CarbonImmutable $start, CarbonImmutable $end, Collection $snapshots, string $status, array $extra = []): array
    {
        return ['period' => ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()], 'source' => 'analysis_snapshots',
            'freshness' => ['latest_captured_at' => $snapshots->max('captured_at') ? (string) $snapshots->max('captured_at') : null],
            'completeness' => ['status' => $status, 'reasons' => $status === 'no_data' ? ['Tidak ada snapshot pada periode dan filter yang dipilih.'] : []], ...$extra];
    }

    private function paginate(Collection $rows, array $filters, array $meta): array
    {
        $page = (int) $filters['page'];
        $perPage = (int) $filters['per_page'];
        $total = $rows->count();

        return ['data' => $rows->forPage($page, $perPage)->values(), 'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => max(1, (int) ceil($total / $perPage))], 'meta' => $meta];
    }
}
