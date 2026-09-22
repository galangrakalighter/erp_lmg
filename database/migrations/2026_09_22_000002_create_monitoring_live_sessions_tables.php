<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('viewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('active');
            $table->timestamp('consented_at');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['target_user_id', 'status']);
        });

        Schema::create('monitoring_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_session_id')->constrained('monitoring_sessions')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->json('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['recipient_user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_signals');
        Schema::dropIfExists('monitoring_sessions');
    }
};
