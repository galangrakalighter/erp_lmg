<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_asset_versions', function (Blueprint $table): void {
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('storage_disk', 50)->nullable();
            $table->string('storage_path', 500)->nullable();
            $table->string('upload_status', 30)->default('metadata_only');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_asset_versions', function (Blueprint $table): void {
            $table->dropColumn(['original_name', 'mime_type', 'file_size', 'storage_disk', 'storage_path', 'upload_status']);
        });
    }
};
