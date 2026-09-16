<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_pdf', function (Blueprint $table) {
            $table->boolean('is_system_template')->default(false)->after('is_generated_document');
            $table->boolean('is_pinned')->default(false)->after('is_system_template');
            $table->unsignedSmallInteger('pin_order')->nullable()->after('is_pinned');
        });
    }

    public function down(): void
    {
        Schema::table('file_pdf', function (Blueprint $table) {
            $table->dropColumn(['is_system_template', 'is_pinned', 'pin_order']);
        });
    }
};
