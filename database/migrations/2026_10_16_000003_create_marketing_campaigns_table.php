<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('owner_user_id')->nullable()->index();
            $table->string('name', 200);
            $table->string('status', 50);
            $table->string('platform', 50)->nullable();
            $table->string('source', 20)->default('organic');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('objective')->nullable();
            $table->decimal('budget', 18, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('target')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'start_date', 'end_date'], 'marketing_campaigns_client_period_idx');
            $table->foreign('client_id')->references('id')->on('produk_client')->restrictOnDelete();
            $table->foreign('owner_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('archived_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};
