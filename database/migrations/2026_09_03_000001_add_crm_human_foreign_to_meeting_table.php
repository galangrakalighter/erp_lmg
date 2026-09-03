<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql_supabase');

        if (empty($schema->getForeignKeys('meeting'))) {
            $schema->table('meeting', function (Blueprint $table) {
                $table->foreign('id_crm_human')
                    ->references('id')
                    ->on('crm_human')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('pgsql_supabase');

        if (!empty($schema->getForeignKeys('meeting'))) {
            $schema->table('meeting', function (Blueprint $table) {
                $table->dropForeign(['id_crm_human']);
            });
        }
    }
};
