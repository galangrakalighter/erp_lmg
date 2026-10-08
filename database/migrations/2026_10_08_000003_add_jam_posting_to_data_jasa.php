<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_jasa', function (Blueprint $table) {
            $table->time('jam_posting')->nullable()->after('tanggal_posting');
        });
    }

    public function down(): void
    {
        Schema::table('data_jasa', function (Blueprint $table) {
            $table->dropColumn('jam_posting');
        });
    }
};
