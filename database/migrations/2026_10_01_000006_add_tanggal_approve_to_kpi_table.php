<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi', function (Blueprint $table) {
            $table->date('tanggal_approve')->nullable()->after('tanggal_deadline');
        });

        DB::table('kpi')
            ->where('status', 1)
            ->update(['tanggal_approve' => DB::raw('tanggal_deadline')]);

        DB::table('kpi')
            ->where('status', 0)
            ->update(['tanggal_approve' => null]);
    }

    public function down(): void
    {
        Schema::table('kpi', function (Blueprint $table) {
            $table->dropColumn('tanggal_approve');
        });
    }
};
