<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $groups = DB::table('produk_client')
                ->selectRaw('LOWER(TRIM(nama)) AS normalized_name, MIN(id) AS canonical_id, ARRAY_AGG(id ORDER BY id) AS ids')
                ->whereNotNull('nama')
                ->whereRaw("TRIM(nama) <> ''")
                ->groupByRaw('LOWER(TRIM(nama))')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            $references = [
                ['client_division', 'client_id'],
                ['user_client', 'client_id'],
                ['invoices', 'customer_id'],
                ['file_pdf', 'client'],
                ['data_jasa', 'produk_client_id'],
                ['analize_4p', 'produk_client_id'],
                ['analize_swot', 'produk_client_id'],
                ['segmentasi', 'produk_client_id'],
                ['target_audience', 'produk_client_id'],
                ['positioning', 'produk_client_id'],
                ['brand_identify', 'produk_client_id'],
                ['brand_image', 'produk_client_id'],
                ['brand_value', 'produk_client_id'],
                ['brand_tagline', 'produk_client_id'],
                ['gaya_komunikasi', 'produk_client_id'],
                ['komposisi', 'produk_client_id'],
                ['audio_brand', 'produk_client_id'],
                ['moodboard', 'produk_client_id'],
                ['alat_branding', 'produk_client_id'],
                ['kpi', 'project'],
                ['task', 'project'],
            ];

            foreach ($groups as $group) {
                $ids = array_map('intval', explode(',', trim((string) $group->ids, '{}')));
                $canonicalId = (int) $group->canonical_id;
                $duplicateIds = array_values(array_diff($ids, [$canonicalId]));

                if ($duplicateIds === []) {
                    continue;
                }

                foreach ($references as [$table, $column]) {
                    if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                        continue;
                    }

                    DB::table($table)
                        ->whereIn($column, $duplicateIds)
                        ->update([$column => $canonicalId]);
                }

                DB::table('produk_client')->whereIn('id', $duplicateIds)->delete();
            }

            DB::statement(<<<'SQL'
                DELETE FROM client_division duplicate
                USING client_division canonical
                WHERE duplicate.id > canonical.id
                  AND duplicate.client_id = canonical.client_id
                  AND duplicate.division_id = canonical.division_id
            SQL);

            DB::statement(<<<'SQL'
                DELETE FROM user_client duplicate
                USING user_client canonical
                WHERE duplicate.id > canonical.id
                  AND duplicate.client_id = canonical.client_id
                  AND duplicate.user_id = canonical.user_id
            SQL);

            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX IF NOT EXISTS produk_client_normalized_name_unique
                ON produk_client (LOWER(TRIM(nama)))
                WHERE nama IS NOT NULL AND TRIM(nama) <> ''
            SQL);
        });
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS produk_client_normalized_name_unique');
    }
};
