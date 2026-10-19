<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformMirroringService
{
    private const SCOPE_TABLES = [
        'market-research' => ['analize_4p', 'analize_swot', 'positioning', 'segmentasi', 'target_audience'],
        'branding' => ['brand_identify', 'brand_image', 'brand_value', 'brand_tagline', 'gaya_komunikasi', 'komposisi', 'audio_brand', 'moodboard', 'alat_branding'],
        'social-media-research' => [],
        'advertising-research' => [],
        'content-plan-organic' => ['data_jasa'],
        'content-plan-paid' => ['data_jasa'],
    ];

    /**
     * Return the complete active mirror group. Records stay on their original
     * platform; callers only use this list to build a non-destructive union.
     */
    public function platforms(int $clientId, string $platform, string $scope): array
    {
        $edges = DB::table('client_platform_mirrors')
            ->where('client_id', $clientId)
            ->where('scope', $scope)
            ->where('active', true)
            ->get(['platform_a', 'platform_b']);

        $labels = collect([$platform]);
        $queue = collect([$platform]);

        while ($queue->isNotEmpty()) {
            $current = $queue->shift();
            foreach ($edges as $edge) {
                $other = null;
                if (strcasecmp($edge->platform_a, $current) === 0) {
                    $other = $edge->platform_b;
                } elseif (strcasecmp($edge->platform_b, $current) === 0) {
                    $other = $edge->platform_a;
                }

                if ($other !== null && ! $labels->contains(fn ($item) => strcasecmp($item, $other) === 0)) {
                    $labels->push($other);
                    $queue->push($other);
                }
            }
        }

        return $labels->values()->all();
    }

    public function activePairs(int $clientId): Collection
    {
        return DB::table('client_platform_mirrors')
            ->where('client_id', $clientId)
            ->where('active', true)
            ->get(['scope', 'platform_a', 'platform_b']);
    }

    public function copiedTargetIds(int $clientId, string $table): Collection
    {
        return DB::table('platform_mirror_copies')
            ->where('client_id', $clientId)
            ->where('table_name', $table)
            ->pluck('target_id');
    }

    /**
     * Materialise a two-way snapshot. This is called before a mirror is
     * enabled and again before it is disabled, so each platform keeps an
     * independent copy after the live union is turned off.
     */
    public function snapshotPair(int $clientId, string $platformA, string $platformB, string $scope): void
    {
        foreach (self::SCOPE_TABLES[$scope] ?? [] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'produk_client_id') || ! Schema::hasColumn($table, 'platform')) {
                continue;
            }

            $this->copyPlatformRows($clientId, $table, $platformA, $platformB, $scope);
            $this->copyPlatformRows($clientId, $table, $platformB, $platformA, $scope);
        }
    }

    /** Sinkronkan satu baris yang baru dibuat atau diedit selama mirror aktif. */
    public function syncRow(int $clientId, string $table, int $rowId, string $scope): void
    {
        if (! in_array($table, self::SCOPE_TABLES[$scope] ?? [], true) || ! Schema::hasTable($table)) {
            return;
        }

        $source = DB::table($table)->where('id', $rowId)->where('produk_client_id', $clientId)
            ->when(Schema::hasColumn($table, 'archived_at'), fn ($query) => $query->whereNull('archived_at'))->first();
        if (! $source || blank($source->platform ?? null)) {
            return;
        }

        $platforms = $this->platforms($clientId, (string) $source->platform, $scope);
        foreach ($platforms as $targetPlatform) {
            if (strcasecmp($targetPlatform, (string) $source->platform) === 0) {
                continue;
            }
            $this->copyPlatformRows($clientId, $table, (string) $source->platform, $targetPlatform, $scope, $rowId);
        }
    }

    /** Hapus salinan pada platform mirror aktif ketika sumber dihapus. */
    public function deleteMirroredRow(int $clientId, string $table, int $rowId, string $scope): void
    {
        $source = DB::table($table)->where('id', $rowId)->where('produk_client_id', $clientId)
            ->when(Schema::hasColumn($table, 'archived_at'), fn ($query) => $query->whereNull('archived_at'))->first();
        if (! $source || blank($source->platform ?? null)) {
            return;
        }

        $activePlatforms = collect($this->platforms($clientId, (string) $source->platform, $scope))
            ->map(fn ($platform) => strtolower((string) $platform));
        if ($activePlatforms->count() <= 1) {
            return;
        }

        $copies = DB::table('platform_mirror_copies')
            ->where('client_id', $clientId)
            ->where('table_name', $table)
            ->where('source_id', $rowId)
            ->get();

        foreach ($copies as $copy) {
            if (! $activePlatforms->contains(strtolower((string) $copy->target_platform))) {
                continue;
            }
            DB::table($table)->where('id', $copy->target_id)->delete();
            DB::table('platform_mirror_copies')->where('id', $copy->id)->delete();
        }
    }

    private function copyPlatformRows(int $clientId, string $table, string $sourcePlatform, string $targetPlatform, string $scope, ?int $onlySourceId = null): void
    {
        $copiedTargetIds = DB::table('platform_mirror_copies')
            ->where('client_id', $clientId)
            ->where('table_name', $table)
            ->pluck('target_id');
        $columns = collect(Schema::getColumnListing($table))->reject(fn ($column) => $column === 'id')->values();

        DB::table($table)
            ->where('produk_client_id', $clientId)
            ->when(Schema::hasColumn($table, 'archived_at'), fn ($query) => $query->whereNull('archived_at'))
            ->whereRaw('LOWER(platform) = ?', [strtolower($sourcePlatform)])
            ->when($onlySourceId !== null, fn ($query) => $query->where('id', $onlySourceId))
            ->when($table === 'data_jasa' && $scope === 'content-plan-organic', fn ($query) => $query->where('content_source', 'organic'))
            ->when($table === 'data_jasa' && $scope === 'content-plan-paid', fn ($query) => $query->where('content_source', 'paid'))
            ->when($copiedTargetIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $copiedTargetIds))
            ->orderBy('id')
            ->get()
            ->each(function ($source) use ($clientId, $table, $sourcePlatform, $targetPlatform, $columns): void {
                $existing = DB::table('platform_mirror_copies')
                    ->where('client_id', $clientId)
                    ->where('table_name', $table)
                    ->where('source_id', $source->id)
                    ->whereRaw('LOWER(target_platform) = ?', [strtolower($targetPlatform)])
                    ->first();

                $payload = collect((array) $source)->only($columns)->all();
                $payload['platform'] = $targetPlatform;
                if (array_key_exists('updated_at', $payload)) {
                    $payload['updated_at'] = now();
                }

                if ($existing && DB::table($table)->where('id', $existing->target_id)->exists()) {
                    DB::table($table)->where('id', $existing->target_id)->update($payload);
                    DB::table('platform_mirror_copies')->where('id', $existing->id)->update(['updated_at' => now()]);
                    return;
                }

                if (array_key_exists('created_at', $payload)) {
                    $payload['created_at'] = now();
                }
                $targetId = DB::table($table)->insertGetId($payload);
                DB::table('platform_mirror_copies')->updateOrInsert(
                    [
                        'client_id' => $clientId,
                        'table_name' => $table,
                        'source_id' => $source->id,
                        'target_platform' => $targetPlatform,
                    ],
                    [
                        'source_platform' => $sourcePlatform,
                        'target_id' => $targetId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });
    }
}
