<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_asset_metadata', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->unsignedBigInteger('content_plan_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->index();
            $table->string('name', 255);
            $table->string('asset_type', 30);
            $table->string('mime_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('approval_status', 30)->default('draft');
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('produk_client')->restrictOnDelete();
            $table->foreign('project_id')->references('id')->on('produk_client')->nullOnDelete();
            $table->foreign('campaign_id')->references('id')->on('marketing_campaigns')->nullOnDelete();
            $table->foreign('content_plan_id')->references('id')->on('data_jasa')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('archived_by')->references('id')->on('users')->nullOnDelete();
        });
        Schema::create('marketing_asset_versions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('asset_metadata_id');
            $table->unsignedInteger('version_number');
            $table->string('label', 100)->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['asset_metadata_id', 'version_number']);
            $table->foreign('asset_metadata_id')->references('id')->on('marketing_asset_metadata')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_asset_versions');
        Schema::dropIfExists('marketing_asset_metadata');
    }
};
