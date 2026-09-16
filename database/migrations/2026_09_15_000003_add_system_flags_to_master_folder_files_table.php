<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_folder_files', function (Blueprint $table) {
            $table->boolean('is_system_template')->default(false)->index();
            $table->boolean('is_pinned')->default(false)->index();
            $table->unsignedSmallInteger('pin_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('master_folder_files', function (Blueprint $table) {
            $table->dropColumn(['is_system_template', 'is_pinned', 'pin_order']);
        });
    }
};
