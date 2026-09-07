<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_master_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->longText('editor_content')->nullable();
            $table->boolean('is_generated_document')->default(false);
            $table->timestamps();

            $table->unique(['owner_user_id', 'original_name']);
            $table->index(['owner_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_master_files');
    }
};
