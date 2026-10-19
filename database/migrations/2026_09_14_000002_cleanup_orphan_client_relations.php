<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::statement(<<<'SQL'
                DELETE FROM client_division relation
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM produk_client product
                    WHERE product.id = relation.client_id
                )
            SQL);

            DB::statement(<<<'SQL'
                DELETE FROM user_client relation
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM produk_client product
                    WHERE product.id = relation.client_id
                )
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE client_division
                ADD CONSTRAINT client_division_client_id_foreign
                FOREIGN KEY (client_id)
                REFERENCES produk_client(id)
                ON UPDATE CASCADE
                ON DELETE CASCADE
            SQL);

            DB::statement(<<<'SQL'
                ALTER TABLE user_client
                ADD CONSTRAINT user_client_client_id_foreign
                FOREIGN KEY (client_id)
                REFERENCES produk_client(id)
                ON UPDATE CASCADE
                ON DELETE CASCADE
            SQL);
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_client DROP CONSTRAINT IF EXISTS user_client_client_id_foreign');
        DB::statement('ALTER TABLE client_division DROP CONSTRAINT IF EXISTS client_division_client_id_foreign');
    }
};
