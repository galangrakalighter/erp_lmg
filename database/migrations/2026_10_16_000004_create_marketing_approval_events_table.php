<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_approval_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('client_id')->index();
            $table->string('record_type', 50);
            $table->unsignedBigInteger('record_id');
            $table->string('action', 30);
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->uuid('audit_reference')->unique();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['client_id', 'record_type', 'record_id', 'id'], 'marketing_approval_record_history_idx');
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('client_id')->references('id')->on('produk_client')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_approval_events');
    }
};
