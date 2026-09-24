<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Keep existing users able to share until an administrator changes
            // their access in Monitoring. New permission pairs still decide
            // who is allowed to watch whom.
            $table->boolean('monitoring_share_enabled')->default(true)->after('is_locked');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('monitoring_share_enabled'));
    }
};
