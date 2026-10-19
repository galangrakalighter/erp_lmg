<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_signals', function (Blueprint $table) {
            $table->index(['monitoring_session_id', 'recipient_user_id', 'read_at', 'id'], 'monitoring_signals_pending_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_signals', function (Blueprint $table) {
            $table->dropIndex('monitoring_signals_pending_lookup_index');
        });
    }
};
