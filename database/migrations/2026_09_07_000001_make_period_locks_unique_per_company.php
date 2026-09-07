<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('period_locks', function (Blueprint $table) {
            $table->dropUnique('period_locks_periode_unique');
            $table->unique(['perusahaan', 'periode'], 'period_locks_company_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('period_locks', function (Blueprint $table) {
            $table->dropUnique('period_locks_company_period_unique');
            $table->unique('periode', 'period_locks_periode_unique');
        });
    }
};
