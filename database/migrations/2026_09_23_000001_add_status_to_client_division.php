<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Visibility belongs to the client assignment, not the shared client. */
    public function up(): void
    {
        Schema::table('client_division', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')->default(1)->after('division_id');
        });
    }

    public function down(): void
    {
        Schema::table('client_division', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
