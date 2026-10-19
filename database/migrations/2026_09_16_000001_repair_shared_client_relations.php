<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            if (!Schema::hasTable('produk_client')) {
                return;
            }

            // Bersihkan pasangan pivot yang benar-benar sama sebelum indeks unik dibuat.
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

            // Pulihkan client_division yang sempat hilang ketika produk duplikat
            // dihapus. Divisi disimpulkan dari tim yang masih menangani client.
            $inferredRelations = DB::table('user_client')
                ->join('user_division', 'user_division.user_id', '=', 'user_client.user_id')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->join('produk_client', 'produk_client.id', '=', 'user_client.client_id')
                ->whereNotNull('roles.division_id')
                ->select('user_client.client_id', 'roles.division_id')
                ->distinct()
                ->get();

            foreach ($inferredRelations as $relation) {
                DB::table('client_division')->updateOrInsert(
                    [
                        'client_id' => (int) $relation->client_id,
                        'division_id' => (int) $relation->division_id,
                    ],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }

            // Client Digital Marketing dan Business Development lama dapat
            // dipulihkan dari kategori jasa ketika belum mempunyai relasi tim.
            $categoryDivisionMap = [1 => 1, 2 => 2, 3 => 2];
            foreach ($categoryDivisionMap as $categoryId => $divisionId) {
                $clientIds = DB::table('produk_client')
                    ->where('kategori_jasa_id', $categoryId)
                    ->pluck('id');

                foreach ($clientIds as $clientId) {
                    DB::table('client_division')->updateOrInsert(
                        ['client_id' => (int) $clientId, 'division_id' => $divisionId],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }

            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX IF NOT EXISTS client_division_client_division_unique
                ON client_division (client_id, division_id)
            SQL);

            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX IF NOT EXISTS user_client_user_client_unique
                ON user_client (user_id, client_id)
            SQL);
        });
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS user_client_user_client_unique');
        DB::statement('DROP INDEX IF EXISTS client_division_client_division_unique');
    }
};
