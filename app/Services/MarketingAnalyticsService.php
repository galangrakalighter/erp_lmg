<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarketingAnalyticsService
{
    public const MAX_PERIOD_DAYS = 366;

    private const ALLOWED_METRICS = [
        'posts', 'reach', 'impressions', 'engagement', 'likes', 'comments',
        'shares', 'clicks', 'spend', 'views', 'followers', 'followers_gained',
        'saves', 'reactions',
    ];

    public function accessibleClients(User $user, array $filters): LengthAwarePaginator
    {
        $query = $this->accessibleClientQuery($user)
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

    public function assertClientAccess(User $user, int $clientId): void
    {
        abort_unless($this->accessibleClientQuery($user)->where('produk_client.id', $clientId)->exists(), 403);
    }

    public function dataAvailability(int $clientId, array $filters): array
    {
        [$start, $end] = $this->period($filters);
        $platform = $this->normalizedPlatform($filters['platform'] ?? null);

        $snapshotRows = DB::table('analysis_snapshots')
            ->where('client_id', $clientId)
            ->whereBetween('snapshot_date', [$start->toDateString(), $end->toDateString()])
            ->when($platform, fn (Builder $query) => $query->whereRaw('LOWER(platform) = ?', [$platform]))
            ->selectRaw("LOWER(platform) as platform, LOWER(source) as source, COUNT(*) as record_count, MIN(snapshot_date) as first_date, MAX(snapshot_date) as last_date, MAX(captured_at) as last_captured_at")
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
            ->selectRaw("LOWER(data_jasa.platform) as platform, LOWER(data_jasa.content_source) as source, COUNT(DISTINCT data_jasa.id) as record_count, COUNT(DISTINCT CASE WHEN mirror_copy.id IS NOT NULL THEN data_jasa.id END) as mirrored_record_count, MIN(data_jasa.tanggal_posting) as first_date, MAX(data_jasa.tanggal_posting) as last_date, MAX(data_jasa.updated_at) as last_updated_at")
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

    private function accessibleClientQuery(User $user): Builder
    {
        $query = DB::table('produk_client');
        if ($user->isAdmin) {
            return $query;
        }

        $divisionId = DB::table('divisions')->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])->value('id');
        if (! $divisionId) {
            return $query->whereRaw('1 = 0');
        }

        $assignment = DB::table('user_division')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->where('user_division.user_id', $user->id)
            ->where('user_division.division_id', $divisionId)
            ->select('roles.role_name')
            ->first();
        if (! $assignment) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereExists(function ($subquery) use ($divisionId): void {
            $subquery->selectRaw('1')->from('client_division')
                ->whereColumn('client_division.client_id', 'produk_client.id')
                ->where('client_division.division_id', $divisionId)
                ->where('client_division.status', 1);
        });

        if ($assignment->role_name !== 'Busines Unit Head') {
            $query->whereExists(function ($subquery) use ($user): void {
                $subquery->selectRaw('1')->from('user_client')
                    ->whereColumn('user_client.client_id', 'produk_client.id')
                    ->where('user_client.user_id', $user->id);
            });
        }

        return $query;
    }

    private function period(array $filters): array
    {
        $end = CarbonImmutable::parse($filters['end_date'] ?? now()->toDateString())->endOfDay();
        $start = CarbonImmutable::parse($filters['start_date'] ?? $end->subDays(29)->toDateString())->startOfDay();
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
