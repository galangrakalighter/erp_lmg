<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarketingAnalyticsService
{
    public function __construct(
        private readonly MarketingAccessService $access,
    ) {}

    public const MAX_PERIOD_DAYS = 366;

    public const ALLOWED_METRICS = [
        'posts', 'reach', 'impressions', 'engagement', 'likes', 'comments',
        'shares', 'clicks', 'spend', 'views', 'followers', 'followers_gained',
        'saves', 'reactions',
    ];

    public const TOP_CONTENT_METRICS = [
        'reach', 'impressions', 'engagement', 'likes', 'comments', 'shares', 'clicks', 'spend',
    ];

    public const PAID_SORT_METRICS = [
        'spend', 'impressions', 'reach', 'clicks', 'engagement', 'leads', 'conversions',
    ];

    public function accessibleClients(Authenticatable $user, array $filters): LengthAwarePaginator
    {
        $query = $this->access->accessibleClientQuery($user)
            ->leftJoin('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
            ->select('produk_client.id', 'produk_client.nama', 'produk_client.status', 'kategori_jasa.category')
            ->distinct();

        if (filled($filters['search'] ?? null)) {
            $term = mb_strtolower(trim((string) $filters['search']));
            $query->whereRaw('LOWER(produk_client.nama) LIKE ?', ['%'.$term.'%']);
        }
        if (isset($filters['status'])) {
            $query->where('produk_client.status', (int) $filters['status']);
        }

        return $query->orderBy('produk_client.nama')
            ->paginate((int) $filters['per_page'], ['*'], 'page', (int) $filters['page']);
    }

    public function assertClientAccess(Authenticatable $user, int $clientId): void
    {
        $this->access->assertClientAccess($user, $clientId);
    }

    public function divisionClientSummary(Authenticatable $user): array
    {
        return $this->access->divisionClientSummary($user);
    }

    public function dataAvailability(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $platform = $this->normalizedPlatform($filters['platform'] ?? null);

        $snapshotRows = DB::table('analysis_snapshots')
            ->where('client_id', $clientId)
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
            ->selectRaw('LOWER(platform) as platform, LOWER(source) as source, COUNT(*) as record_count, MIN(snapshot_date) as first_date, MAX(snapshot_date) as last_date, MAX(captured_at) as last_captured_at')
            ->groupByRaw('LOWER(platform), LOWER(source)')
            ->get()
            ->map(fn ($row) => [
                'dataset' => 'performance_snapshot',
                'platform' => $row->platform,
                'source' => $row->source,
                'record_count' => (int) $row->record_count,
                'first_date' => $row->first_date,
                'last_date' => $row->last_date,
                'last_updated_at' => $row->last_captured_at,
            ]);

        $planRows = DB::table('data_jasa')
            ->leftJoin('platform_mirror_copies as mirror_copy', function ($join): void {
                $join->on('mirror_copy.client_id', '=', 'data_jasa.produk_client_id')
                    ->where('mirror_copy.table_name', '=', 'data_jasa')
                    ->on('mirror_copy.target_id', '=', 'data_jasa.id');
            })
            ->where('produk_client_id', $clientId)
            ->whereBetween('tanggal_posting', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
            ->selectRaw('LOWER(data_jasa.platform) as platform, LOWER(data_jasa.content_source) as source, COUNT(DISTINCT data_jasa.id) as record_count, COUNT(DISTINCT CASE WHEN mirror_copy.id IS NOT NULL THEN data_jasa.id END) as mirrored_record_count, MIN(data_jasa.tanggal_posting) as first_date, MAX(data_jasa.tanggal_posting) as last_date, MAX(data_jasa.updated_at) as last_updated_at')
            ->groupByRaw('LOWER(data_jasa.platform), LOWER(data_jasa.content_source)')
            ->get()
            ->map(fn ($row) => [
                'dataset' => 'content_plan',
                'platform' => $row->platform,
                'source' => $row->source,
                'record_count' => (int) $row->record_count,
                'direct_record_count' => (int) $row->record_count - (int) $row->mirrored_record_count,
                'mirrored_record_count' => (int) $row->mirrored_record_count,
                'first_date' => $row->first_date,
                'last_date' => $row->last_date,
                'last_updated_at' => $row->last_updated_at,
            ]);

        $rows = $snapshotRows->concat($planRows)->sortBy([
            ['dataset', 'asc'], ['platform', 'asc'], ['source', 'asc'],
        ])->values();

        return $this->paginatedPayload($rows, $filters, $start, $end, [
            'source' => ['analysis_snapshots', 'data_jasa'],
            'freshness' => ['latest_updated_at' => $rows->max('last_updated_at')],
            'completeness' => [
                'status' => $rows->isEmpty() ? 'no_data' : 'unknown',
                'reasons' => $rows->isEmpty()
                    ? ['Tidak ada data pada filter dan periode yang dipilih.']
                    : ['Ketersediaan snapshot bergantung pada proses capture yang berjalan di luar endpoint read-only ini.'],
            ],
            'mirror_handling' => 'Tidak ada total lintas platform; record mirror ditandai terpisah agar tidak dijumlahkan sebagai konten unik.',
        ]);
    }

    public function performanceSummary(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $platform = $this->normalizedPlatform($filters['platform'] ?? null);
        $source = filled($filters['source'] ?? null) ? strtolower((string) $filters['source']) : null;

        $snapshots = DB::table('analysis_snapshots')
            ->where('client_id', $clientId)
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
            ->when($source, fn (Builder $query) => $query->whereRaw('LOWER(source) = ?', [$source]))
            ->orderBy('snapshot_date')
            ->get(['platform', 'source', 'snapshot_date', 'captured_at', 'metrics'])
            ->groupBy(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source))
            ->map(function (Collection $rows): array {
                $first = $rows->first();
                $latest = $rows->last();
                $firstNormalized = $this->normalizeMetrics($first->metrics);
                $latestNormalized = $this->normalizeMetrics($latest->metrics);
                $firstMetrics = $firstNormalized['metrics'];
                $latestMetrics = $latestNormalized['metrics'];
                $keys = collect(array_keys($firstMetrics))->merge(array_keys($latestMetrics))->unique();
                $delta = $keys->mapWithKeys(fn ($key) => [
                    $key => array_key_exists($key, $firstMetrics) && array_key_exists($key, $latestMetrics)
                        ? round($latestMetrics[$key] - $firstMetrics[$key], 4)
                        : null,
                ])->all();

                $invalidKeys = collect($firstNormalized['invalid_keys'])
                    ->merge($latestNormalized['invalid_keys'])->unique()->values()->all();
                $ignoredKeys = collect($firstNormalized['ignored_keys'])
                    ->merge($latestNormalized['ignored_keys'])->unique()->values()->all();

                return [
                    'platform' => strtolower($latest->platform),
                    'source' => strtolower($latest->source),
                    'snapshot_count' => $rows->count(),
                    'first_snapshot_date' => (string) $first->snapshot_date,
                    'latest_snapshot_date' => (string) $latest->snapshot_date,
                    'last_captured_at' => (string) $latest->captured_at,
                    'metrics' => $latestMetrics,
                    'change_since_first_snapshot' => $delta,
                    'data_quality' => [
                        'status' => $invalidKeys === [] && $ignoredKeys === [] ? 'complete' : 'partial',
                        'invalid_metric_keys' => $invalidKeys,
                        'ignored_metric_count' => count($ignoredKeys),
                        'non_comparable_metrics' => collect($delta)->filter(fn ($value) => $value === null)->keys()->values()->all(),
                    ],
                ];
            })->sortBy([['platform', 'asc'], ['source', 'asc']])->values();

        $hasQualityIssue = $snapshots->contains(fn ($row) => data_get($row, 'data_quality.status') !== 'complete');
        $payload = $this->paginatedPayload($snapshots, $filters, $start, $end, [
            'source' => 'analysis_snapshots',
            'freshness' => ['latest_captured_at' => $snapshots->max('last_captured_at')],
            'completeness' => [
                'status' => $snapshots->isEmpty() ? 'no_data' : ($hasQualityIssue ? 'partial' : 'unknown'),
                'reasons' => $snapshots->isEmpty()
                    ? ['Tidak ada snapshot pada filter dan periode yang dipilih.']
                    : array_values(array_filter([
                        $hasQualityIssue ? 'Sebagian metrik invalid, tidak dikenal, atau tidak dapat dibandingkan.' : null,
                        'Kelengkapan historis tidak dapat dijamin karena endpoint ini hanya membaca snapshot yang tersedia.',
                    ])),
            ],
            'mirror_handling' => 'Setiap baris tetap dipisahkan per platform dan source; tidak ada penjumlahan lintas platform.',
        ]);
        $payload['notice'] = 'Metrik berasal dari snapshot tersimpan; endpoint ini tidak memanggil API platform dan tidak membuat snapshot.';

        return $payload;
    }

    public function performanceTrend(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $requestedMetrics = $filters['metrics'] ?? self::ALLOWED_METRICS;
        $snapshots = $this->snapshotQuery($clientId, $filters, $start, $end)
            ->orderBy('snapshot_date')->orderBy('captured_at')
            ->get(['platform', 'source', 'snapshot_date', 'captured_at', 'metrics']);
        $granularity = $filters['granularity'];
        $qualityIssueCount = 0;

        $series = $snapshots->groupBy(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source))
            ->map(function (Collection $rows) use ($requestedMetrics, $granularity, &$qualityIssueCount): array {
                $points = $rows->groupBy(fn ($row) => $this->periodBucket((string) $row->snapshot_date, $granularity))
                    ->map(function (Collection $bucket) use ($requestedMetrics, $granularity, &$qualityIssueCount): array {
                        $snapshot = $bucket->sortBy('captured_at')->last();
                        $normalized = $this->normalizeMetrics($snapshot->metrics);
                        if ($normalized['invalid_keys'] !== [] || $normalized['ignored_keys'] !== []) {
                            $qualityIssueCount++;
                        }

                        $selected = collect($requestedMetrics)->mapWithKeys(fn ($metric) => [
                            $metric => array_key_exists($metric, $normalized['metrics']) ? $normalized['metrics'][$metric] : null,
                        ])->all();
                        if (collect($selected)->contains(fn ($value) => $value === null)) {
                            $qualityIssueCount++;
                        }

                        return [
                            'period' => $this->periodBucket((string) $snapshot->snapshot_date, $granularity),
                            'snapshot_date' => (string) $snapshot->snapshot_date,
                            'captured_at' => (string) $snapshot->captured_at,
                            'metrics' => $selected,
                        ];
                    })->sortBy('period')->values();
                $first = $points->first();
                $last = $points->last();
                $changes = collect($requestedMetrics)->mapWithKeys(function ($metric) use ($first, $last, $points): array {
                    $initial = data_get($first, 'metrics.'.$metric);
                    $current = data_get($last, 'metrics.'.$metric);
                    if ($points->count() < 2 || $initial === null || $current === null) {
                        return [$metric => ['absolute' => null, 'percentage' => null, 'direction' => 'insufficient_data']];
                    }
                    $delta = round($current - $initial, 4);

                    return [$metric => [
                        'absolute' => $delta,
                        'percentage' => (float) $initial === 0.0 ? null : round($delta * 100 / abs((float) $initial), 2),
                        'direction' => $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'stable'),
                    ]];
                })->all();
                $gaps = $this->missingBuckets($points->pluck('period')->all(), $granularity);

                return [
                    'platform' => strtolower($rows->first()->platform),
                    'source' => strtolower($rows->first()->source),
                    'available_range' => ['start_date' => (string) $rows->min('snapshot_date'), 'end_date' => (string) $rows->max('snapshot_date')],
                    'points' => $points->all(),
                    'changes' => $changes,
                    'missing_periods' => $gaps,
                    'last_captured_at' => (string) $rows->max('captured_at'),
                ];
            })->values();

        $hasGaps = $series->contains(fn ($row) => $row['missing_periods'] !== []);
        $insufficient = $series->isNotEmpty() && $series->every(fn ($row) => count($row['points']) < 2);

        return [
            'data' => $series->all(),
            'pagination' => null,
            'meta' => $this->snapshotMetadata($start, $end, $snapshots, $series->isEmpty() ? 'no_data' : ($insufficient ? 'insufficient_data' : (($hasGaps || $qualityIssueCount > 0) ? 'partial' : 'complete')), [
                'granularity' => $granularity,
                'metrics' => array_values($requestedMetrics),
                'quality_issue_count' => $qualityIssueCount,
                'missing_period_count' => $series->sum(fn ($row) => count($row['missing_periods'])),
                'notice' => 'Tidak ada interpolasi; setiap titik memakai snapshot terakhir yang benar-benar tersedia pada bucket.',
            ]),
        ];
    }

    public function topContent(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $snapshots = $this->snapshotQuery($clientId, $filters, $start, $end)
            ->orderBy('captured_at')->get();
        $invalidSnapshots = 0;
        $candidates = collect();

        foreach ($snapshots as $snapshot) {
            $items = $this->decodeItems($snapshot->items, $invalidSnapshots);
            foreach ($items as $item) {
                if (! is_array($item['metrics'] ?? null)) {
                    $invalidSnapshots++;

                    continue;
                }
                $metrics = $this->normalizeItemMetrics($item['metrics'] ?? []);
                $metricValue = $metrics[$filters['metric']] ?? null;
                if ($metricValue === null) {
                    continue;
                }
                $identity = (string) ($item['id'] ?? '');
                $dedupeKey = strtolower((string) $snapshot->source).'|'.($identity !== '' ? $identity : sha1(($item['title'] ?? '').'|'.($item['date'] ?? '')));
                $candidate = [
                    '_key' => $dedupeKey,
                    '_raw_id' => $identity,
                    'content_key' => hash('sha256', $dedupeKey),
                    'title' => filled($item['title'] ?? null) ? (string) $item['title'] : null,
                    'date' => $item['date'] ?? null,
                    'platform' => strtolower((string) $snapshot->platform),
                    'source' => strtolower((string) $snapshot->source),
                    'metric' => $filters['metric'],
                    'value' => $metricValue,
                    'supporting_metrics' => $metrics,
                    'last_updated_at' => (string) $snapshot->captured_at,
                ];
                $existing = $candidates->get($dedupeKey);
                if (! $existing || $candidate['last_updated_at'] > $existing['last_updated_at']) {
                    $candidates->put($dedupeKey, $candidate);
                }
            }
        }

        $plans = $this->canonicalContentPlans($clientId, $start, $end);
        $ranked = ($filters['direction'] === 'best' ? $candidates->sortByDesc('value') : $candidates->sortBy('value'))
            ->take((int) $filters['limit'])->values()->map(function (array $item, int $index) use ($plans): array {
                $match = $plans->first(fn ($plan) => filled($item['title'])
                    && mb_strtolower(trim((string) $plan->judul)) === mb_strtolower(trim((string) $item['title']))
                    && strtolower((string) $plan->platform) === $item['platform']
                    && (blank($item['date']) || (string) $plan->tanggal_posting === substr((string) $item['date'], 0, 10)));
                unset($item['_key'], $item['_raw_id']);
                $item['rank'] = $index + 1;
                $item['content_plan_match'] = $match ? ['matched' => true, 'content_plan_id' => (int) $match->id] : ['matched' => false, 'content_plan_id' => null];

                return $item;
            });

        return [
            'data' => $ranked->all(),
            'pagination' => null,
            'meta' => $this->snapshotMetadata($start, $end, $snapshots, $ranked->isEmpty() ? 'no_data' : ($invalidSnapshots > 0 ? 'partial' : 'complete'), [
                'metric' => $filters['metric'], 'direction' => $filters['direction'], 'limit' => (int) $filters['limit'],
                'invalid_snapshot_count' => $invalidSnapshots,
                'mirror_handling' => 'Item dengan source dan identitas konten sama dideduplikasi lintas platform.',
            ]),
        ];
    }

    public function paidPerformance(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $filters['source'] = 'paid';
        $snapshots = $this->latestSnapshotsBySeries($this->snapshotQuery($clientId, $filters, $start, $end)->get());
        $invalidSnapshots = 0;
        $items = collect();
        $currencies = collect();

        foreach ($snapshots as $snapshot) {
            $snapshotMetrics = $this->decodeAssociativeJson($snapshot->metrics);
            if (is_string($snapshotMetrics['currency'] ?? null) && filled($snapshotMetrics['currency'])) {
                $currencies->push(strtoupper($snapshotMetrics['currency']));
            }
            foreach ($this->decodeItems($snapshot->items, $invalidSnapshots) as $item) {
                if (! is_array($item['metrics'] ?? null)) {
                    $invalidSnapshots++;

                    continue;
                }
                $campaignId = filled($item['campaign_id'] ?? null) ? (string) $item['campaign_id'] : null;
                if (filled($filters['campaign_id'] ?? null) && $campaignId !== (string) $filters['campaign_id']) {
                    continue;
                }
                if (is_string($item['currency'] ?? null) && filled($item['currency'])) {
                    $currencies->push(strtoupper($item['currency']));
                }
                $identity = (string) ($item['id'] ?? sha1(json_encode([$item['title'] ?? null, $item['date'] ?? null])));
                $key = $identity;
                $row = [
                    'content_key' => hash('sha256', $identity),
                    'campaign_id' => $campaignId,
                    'campaign' => filled($item['campaign'] ?? null) ? (string) $item['campaign'] : null,
                    'ad_set' => filled($item['adset'] ?? null) ? (string) $item['adset'] : null,
                    'title' => filled($item['title'] ?? null) ? (string) $item['title'] : null,
                    'date' => $item['date'] ?? null,
                    'platform' => strtolower((string) $snapshot->platform),
                    'metrics' => $this->normalizePaidMetrics($item['metrics'] ?? []),
                    'last_updated_at' => (string) $snapshot->captured_at,
                ];
                $existing = $items->get($key);
                if (! $existing || $row['last_updated_at'] > $existing['last_updated_at']) {
                    $items->put($key, $row);
                }
            }
        }

        $currencies = $currencies->unique()->values();
        $currency = $currencies->count() === 1 ? $currencies->first() : null;
        $metricKeys = ['spend', 'impressions', 'reach', 'clicks', 'engagement', 'leads', 'conversions', 'revenue'];
        $totals = collect($metricKeys)->mapWithKeys(function ($metric) use ($items): array {
            $values = $items->pluck('metrics.'.$metric)->filter(fn ($value) => $value !== null);

            return [$metric => $values->isEmpty() ? null : round($values->sum(), 4)];
        })->all();
        if ($currency === null) {
            $totals['spend'] = null;
            $totals['revenue'] = null;
        }
        $calculated = $this->paidCalculatedMetrics($totals, $currency);
        $sortMetric = $filters['metric'];
        $sorted = $items->values()->sortByDesc(fn ($item) => $item['metrics'][$sortMetric] ?? -INF)->values();
        $page = (int) $filters['page'];
        $perPage = (int) $filters['per_page'];
        $reasons = [];
        if ($currency === null) {
            $reasons[] = $currencies->count() > 1 ? 'Mata uang berbeda; spend lintas mata uang tidak digabungkan.' : 'Currency tidak tersimpan pada snapshot.';
        }
        if ($items->isEmpty() && $snapshots->isNotEmpty()) {
            $reasons[] = 'Snapshot paid tersedia tetapi tidak memiliki item yang dapat diidentifikasi; agregat kumulatif tidak dijumlahkan untuk menghindari double counting.';
        }
        if ($invalidSnapshots > 0) {
            $reasons[] = 'Sebagian JSON item snapshot rusak atau berbeda struktur.';
        }

        return [
            'data' => [
                'totals' => $totals + ['currency' => $currency],
                'calculated_metrics' => $calculated['values'],
                'unavailable_metrics' => $calculated['unavailable'],
                'identified_campaign_count' => $items->pluck('campaign_id')->filter()->unique()->count(),
                'identified_paid_content_count' => $items->count(),
                'items' => $sorted->forPage($page, $perPage)->values()->all(),
            ],
            'pagination' => ['page' => $page, 'per_page' => $perPage, 'total' => $sorted->count(), 'last_page' => max(1, (int) ceil($sorted->count() / $perPage))],
            'meta' => $this->snapshotMetadata($start, $end, $snapshots, $snapshots->isEmpty() ? 'no_data' : ($reasons === [] ? 'complete' : 'partial'), [
                'aggregation_level' => 'latest identifiable paid items per platform; duplicate item identities are collapsed across platforms',
                'limitations' => $reasons,
                'attribution' => 'Tidak ada atribusi penjualan di luar field leads, conversions, dan revenue yang tersimpan eksplisit pada snapshot.',
            ]),
        ];
    }

    public function contentPlan(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $query = $this->contentPlanQuery($clientId, $filters, $start, $end);
        $freshness = (clone $query)->max('data_jasa.updated_at');
        $missingCount = (clone $query)->where(function (Builder $missing): void {
            $missing->whereNull('data_jasa.platform')->orWhereNull('data_jasa.content_source')
                ->orWhereNull('type_produk.type')->orWhereNull('strategy.strategy')
                ->orWhereNull('pilar.pilar')->orWhereNull('hooks.hooks')
                ->orWhereNull('jenis_body.nama_body')->orWhereNull('jenis_cta.nama_cta')
                ->orWhereNull('status.nama_status');
        })->count('data_jasa.id');

        $plans = $query->select([
            'data_jasa.id as content_plan_id', 'data_jasa.tanggal_posting as date',
            'data_jasa.jam_posting as time', 'data_jasa.platform',
            'data_jasa.content_source as source', 'type_produk.type as content_type',
            'strategy.strategy', 'pilar.pilar', 'hooks.hooks as hook',
            'jenis_body.nama_body as body', 'jenis_cta.nama_cta as cta',
            'status.nama_status as status',
        ])->orderBy('data_jasa.tanggal_posting')->orderBy('data_jasa.id')
            ->paginate((int) $filters['per_page'], ['*'], 'page', (int) $filters['page']);

        $rows = collect($plans->items())->map(function ($row): array {
            $values = (array) $row;
            $values['platform'] = filled($values['platform']) ? strtolower((string) $values['platform']) : null;
            $values['source'] = filled($values['source']) ? strtolower((string) $values['source']) : null;

            return $values;
        })->all();

        return [
            'data' => $rows,
            'pagination' => ['page' => $plans->currentPage(), 'per_page' => $plans->perPage(), 'total' => $plans->total(), 'last_page' => $plans->lastPage()],
            'meta' => $this->contentMetadata($start, $end, $plans->total(), $missingCount, $freshness),
        ];
    }

    public function contentMix(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $rows = $this->contentPlanQuery($clientId, $filters, $start, $end)->get([
            'data_jasa.platform', 'data_jasa.content_source as source',
            'type_produk.type as content_type', 'strategy.strategy', 'pilar.pilar',
            'hooks.hooks as hook', 'jenis_cta.nama_cta as cta', 'status.nama_status as status',
            'jenis_body.nama_body as body', 'data_jasa.updated_at',
        ]);
        $total = $rows->count();
        $missingCount = $rows->filter(fn ($row) => collect([
            $row->platform, $row->source, $row->content_type, $row->strategy, $row->pilar,
            $row->hook, $row->body, $row->cta, $row->status,
        ])->contains(fn ($value) => blank($value)))->count();
        $organic = $rows->filter(fn ($row) => strtolower((string) $row->source) === 'organic')->count();
        $paid = $rows->filter(fn ($row) => strtolower((string) $row->source) === 'paid')->count();

        return [
            'data' => [
                'total_content_plan' => $total,
                'source_mix' => [
                    'organic' => $this->countAndPercentage($organic, $total),
                    'paid' => $this->countAndPercentage($paid, $total),
                    'unclassified' => $this->countAndPercentage($total - $organic - $paid, $total),
                ],
                'distributions' => [
                    'platform' => $this->distribution($rows, 'platform', $total, true),
                    'content_type' => $this->distribution($rows, 'content_type', $total),
                    'strategy' => $this->distribution($rows, 'strategy', $total),
                    'pilar' => $this->distribution($rows, 'pilar', $total),
                    'hook' => $this->distribution($rows, 'hook', $total),
                    'cta' => $this->distribution($rows, 'cta', $total),
                    'status' => $this->distribution($rows, 'status', $total),
                ],
            ],
            'pagination' => null,
            'meta' => $this->contentMetadata($start, $end, $total, $missingCount, $rows->max('updated_at')),
        ];
    }

    private function snapshotQuery(int $clientId, array $filters, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        $platform = $this->normalizedPlatform($filters['platform'] ?? null);
        $source = filled($filters['source'] ?? null) ? strtolower((string) $filters['source']) : null;

        return DB::table('analysis_snapshots')->where('client_id', $clientId)
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
            ->when($source, fn (Builder $query) => $query->whereRaw('LOWER(source) = ?', [$source]));
    }

    private function latestSnapshotsBySeries(Collection $snapshots): Collection
    {
        return $snapshots->groupBy(fn ($row) => strtolower($row->platform).'|'.strtolower($row->source))
            ->map(fn (Collection $rows) => $rows->sortBy('captured_at')->last())->values();
    }

    private function periodBucket(string $date, string $granularity): string
    {
        $value = CarbonImmutable::parse($date, 'Asia/Jakarta');

        return match ($granularity) {
            'weekly' => $value->startOfWeek()->toDateString(),
            'monthly' => $value->startOfMonth()->format('Y-m'),
            default => $value->toDateString(),
        };
    }

    private function missingBuckets(array $present, string $granularity): array
    {
        if (count($present) < 2) {
            return [];
        }
        $present = array_values(array_unique($present));
        sort($present);
        $cursor = CarbonImmutable::parse($present[0].($granularity === 'monthly' ? '-01' : ''), 'Asia/Jakarta');
        $last = CarbonImmutable::parse(end($present).($granularity === 'monthly' ? '-01' : ''), 'Asia/Jakarta');
        $missing = [];
        while ($cursor->lessThanOrEqualTo($last)) {
            $label = $granularity === 'monthly' ? $cursor->format('Y-m') : $cursor->toDateString();
            if (! in_array($label, $present, true)) {
                $missing[] = $label;
            }
            $cursor = match ($granularity) {
                'weekly' => $cursor->addWeek(),
                'monthly' => $cursor->addMonth(),
                default => $cursor->addDay(),
            };
        }

        return $missing;
    }

    private function decodeItems(mixed $items, int &$invalidCount): array
    {
        $decoded = is_array($items) ? $items : json_decode((string) $items, true);
        if (! is_array($decoded) || ! array_is_list($decoded)) {
            $invalidCount++;

            return [];
        }

        return collect($decoded)->filter(function ($item) use (&$invalidCount): bool {
            if (! is_array($item)) {
                $invalidCount++;

                return false;
            }

            return true;
        })->values()->all();
    }

    private function decodeAssociativeJson(mixed $value): array
    {
        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeItemMetrics(mixed $metrics): array
    {
        $metrics = is_array($metrics) ? $metrics : [];

        return collect(self::TOP_CONTENT_METRICS)->mapWithKeys(fn ($key) => [
            $key => array_key_exists($key, $metrics) && is_numeric($metrics[$key]) ? (float) $metrics[$key] : null,
        ])->filter(fn ($value) => $value !== null)->all();
    }

    private function normalizePaidMetrics(mixed $metrics): array
    {
        $metrics = is_array($metrics) ? $metrics : [];

        return collect(['spend', 'impressions', 'reach', 'clicks', 'engagement', 'leads', 'conversions', 'revenue'])
            ->mapWithKeys(fn ($key) => [
                $key => array_key_exists($key, $metrics) && is_numeric($metrics[$key]) ? (float) $metrics[$key] : null,
            ])->all();
    }

    private function paidCalculatedMetrics(array $totals, ?string $currency): array
    {
        $definitions = [
            'ctr' => ['numerator' => 'clicks', 'denominator' => 'impressions', 'factor' => 100, 'currency' => false],
            'cpc' => ['numerator' => 'spend', 'denominator' => 'clicks', 'factor' => 1, 'currency' => true],
            'cpm' => ['numerator' => 'spend', 'denominator' => 'impressions', 'factor' => 1000, 'currency' => true],
            'cpl' => ['numerator' => 'spend', 'denominator' => 'leads', 'factor' => 1, 'currency' => true],
            'cpa' => ['numerator' => 'spend', 'denominator' => 'conversions', 'factor' => 1, 'currency' => true],
            'roas' => ['numerator' => 'revenue', 'denominator' => 'spend', 'factor' => 1, 'currency' => false],
        ];
        $values = [];
        $unavailable = [];
        foreach ($definitions as $name => $definition) {
            $numerator = $totals[$definition['numerator']] ?? null;
            $denominator = $totals[$definition['denominator']] ?? null;
            if ($definition['currency'] && $currency === null) {
                $unavailable[$name] = 'Currency tidak tersedia atau tidak konsisten.';
            } elseif ($numerator === null || $denominator === null) {
                $unavailable[$name] = 'Komponen '.$definition['numerator'].' atau '.$definition['denominator'].' tidak tersedia.';
            } elseif ((float) $denominator === 0.0) {
                $unavailable[$name] = 'Pembagi '.$definition['denominator'].' bernilai nol.';
            } else {
                $values[$name] = round((float) $numerator * $definition['factor'] / (float) $denominator, 4);
            }
        }

        return ['values' => $values, 'unavailable' => $unavailable];
    }

    private function canonicalContentPlans(int $clientId, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return DB::table('data_jasa')->leftJoin('platform_mirror_copies as mirror_copy', function ($join): void {
            $join->on('mirror_copy.client_id', '=', 'data_jasa.produk_client_id')
                ->where('mirror_copy.table_name', '=', 'data_jasa')
                ->on('mirror_copy.target_id', '=', 'data_jasa.id');
        })->where('data_jasa.produk_client_id', $clientId)->whereNull('mirror_copy.id')->whereNull('data_jasa.archived_at')
            ->whereBetween('data_jasa.tanggal_posting', [$start->toDateString(), $end->toDateString()])
            ->get(['data_jasa.id', 'data_jasa.judul', 'data_jasa.tanggal_posting', 'data_jasa.platform']);
    }

    private function snapshotMetadata(CarbonImmutable $start, CarbonImmutable $end, Collection $snapshots, string $status, array $extra = []): array
    {
        return [
            'period' => ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'max_days' => self::MAX_PERIOD_DAYS, 'timezone' => 'Asia/Jakarta'],
            'source' => 'analysis_snapshots',
            'freshness' => ['latest_captured_at' => $snapshots->max('captured_at') ? (string) $snapshots->max('captured_at') : null],
            'completeness' => ['status' => $status],
        ] + $extra;
    }

    private function contentPlanQuery(int $clientId, array $filters, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        $platform = $this->normalizedPlatform($filters['platform'] ?? null);
        $source = filled($filters['source'] ?? null) ? strtolower((string) $filters['source']) : null;

        return DB::table('data_jasa')
            ->leftJoin('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
            ->leftJoin('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
            ->leftJoin('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
            ->leftJoin('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
            ->leftJoin('jenis_body', 'jenis_body.id', '=', 'data_jasa.body_id')
            ->leftJoin('jenis_cta', 'jenis_cta.id', '=', 'data_jasa.cta_id')
            ->leftJoin('status', 'status.id', '=', 'data_jasa.status_id')
            ->leftJoin('platform_mirror_copies as mirror_copy', function ($join): void {
                $join->on('mirror_copy.client_id', '=', 'data_jasa.produk_client_id')
                    ->where('mirror_copy.table_name', '=', 'data_jasa')
                    ->on('mirror_copy.target_id', '=', 'data_jasa.id');
            })
            ->where('data_jasa.produk_client_id', $clientId)->whereNull('mirror_copy.id')->whereNull('data_jasa.archived_at')
            ->whereBetween('data_jasa.tanggal_posting', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(data_jasa.platform) = ?', [$platform]))
            ->when($source, fn (Builder $query) => $query->whereRaw('LOWER(data_jasa.content_source) = ?', [$source]))
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('data_jasa.status_id', (int) $filters['status']));
    }

    private function distribution(Collection $rows, string $field, int $total, bool $lowercase = false): array
    {
        return $rows->map(function ($row) use ($field, $lowercase) {
            $value = $row->{$field};

            return $lowercase && filled($value) ? strtolower((string) $value) : $value;
        })->countBy(fn ($value) => filled($value) ? (string) $value : '__missing__')
            ->map(fn ($count, $value) => ['value' => $value === '__missing__' ? null : $value, ...$this->countAndPercentage((int) $count, $total)])
            ->sortByDesc('count')->values()->all();
    }

    private function countAndPercentage(int $count, int $total): array
    {
        return ['count' => $count, 'percentage' => $total === 0 ? 0.0 : round($count * 100 / $total, 2)];
    }

    private function contentMetadata(CarbonImmutable $start, CarbonImmutable $end, int $total, int $missingCount, mixed $freshness): array
    {
        return [
            'period' => ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString(), 'max_days' => self::MAX_PERIOD_DAYS, 'timezone' => 'Asia/Jakarta'],
            'source' => 'data_jasa',
            'freshness' => ['latest_updated_at' => $freshness ? (string) $freshness : null],
            'completeness' => [
                'status' => $total === 0 ? 'no_data' : ($missingCount > 0 ? 'partial' : 'complete'),
                'missing_record_count' => $missingCount,
                'reasons' => $total === 0 ? ['Tidak ada content plan pada filter dan periode yang dipilih.'] : ($missingCount > 0 ? ['Sebagian content plan tidak memiliki satu atau lebih dimensi yang diminta.'] : []),
            ],
            'mirror_handling' => 'canonical_records_only; salinan target platform mirror dikecualikan untuk mencegah double counting.',
        ];
    }

    private function period(array $filters): array
    {
        $end = CarbonImmutable::parse($filters['end_date'] ?? now('Asia/Jakarta')->toDateString(), 'Asia/Jakarta')->endOfDay();
        $start = CarbonImmutable::parse($filters['start_date'] ?? $end->subDays(29)->toDateString(), 'Asia/Jakarta')->startOfDay();
        abort_if($start->greaterThan($end), 422, 'start_date harus sebelum atau sama dengan end_date.');
        abort_if($start->diffInDays($end) + 1 > self::MAX_PERIOD_DAYS, 422, 'Rentang tanggal maksimum adalah '.self::MAX_PERIOD_DAYS.' hari.');

        return [$start, $end];
    }

    private function normalizedPlatform(?string $platform): ?string
    {
        return filled($platform) ? strtolower(trim($platform)) : null;
    }

    private function normalizeMetrics(mixed $metrics): array
    {
        $decoded = is_array($metrics) ? $metrics : json_decode((string) $metrics, true);
        if (! is_array($decoded)) {
            return ['metrics' => [], 'invalid_keys' => ['__payload__'], 'ignored_keys' => []];
        }

        $values = [];
        $invalidKeys = [];
        $ignoredKeys = [];
        foreach ($decoded as $key => $value) {
            $key = (string) $key;
            if (! in_array($key, self::ALLOWED_METRICS, true)) {
                $ignoredKeys[] = $key;

                continue;
            }
            if (! is_numeric($value)) {
                $invalidKeys[] = $key;

                continue;
            }
            $values[$key] = (float) $value;
        }

        return ['metrics' => $values, 'invalid_keys' => $invalidKeys, 'ignored_keys' => $ignoredKeys];
    }

    private function paginatedPayload(Collection $rows, array $filters, CarbonImmutable $start, CarbonImmutable $end, array $metadata): array
    {
        $page = (int) $filters['page'];
        $perPage = (int) $filters['per_page'];
        $total = $rows->count();

        return [
            'data' => $rows->forPage($page, $perPage)->values()->all(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
            'meta' => ['period' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'max_days' => self::MAX_PERIOD_DAYS,
            ]] + $metadata,
        ];
    }
}
