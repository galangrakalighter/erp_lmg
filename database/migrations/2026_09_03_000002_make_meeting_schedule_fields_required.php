<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql_supabase')
            ->table('meeting', function (Blueprint $table) {
                $table->timestampTz('waktu_meeting')
                    ->nullable(false)
                    ->change();

                $table->string('link_meeting')
                    ->nullable(false)
                    ->change();
            });
    }

    public function down(): void
    {
        Schema::connection('pgsql_supabase')
            ->table('meeting', function (Blueprint $table) {
                $table->timestampTz('waktu_meeting')
                    ->nullable()
                    ->change();

                $table->string('link_meeting')
                    ->nullable()
                    ->change();
            });
    }
};
