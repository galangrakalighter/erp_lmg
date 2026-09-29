<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('produk_client')->cascadeOnDelete();
            $table->string('platform', 32);
            $table->string('source', 16);
            $table->date('snapshot_date');
            $table->json('metrics');
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->unique(['client_id', 'platform', 'source', 'snapshot_date'], 'analysis_snapshot_daily_unique');
            $table->index(['client_id', 'platform', 'source', 'captured_at'], 'analysis_snapshot_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_snapshots');
    }
};
