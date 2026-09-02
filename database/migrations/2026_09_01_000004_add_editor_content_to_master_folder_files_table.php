<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_folder_files', function (Blueprint $table) {
            $table->longText('editor_content')->nullable();
            $table->boolean('is_generated_document')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('master_folder_files', function (Blueprint $table) {
            $table->dropColumn(['editor_content', 'is_generated_document']);
        });
    }
};
