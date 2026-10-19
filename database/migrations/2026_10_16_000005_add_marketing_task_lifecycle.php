<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->foreign('archived_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('task', function (Blueprint $table): void {
            $table->dropForeign(['archived_by']);
            $table->dropColumn(['archived_at', 'archived_by']);
        });
    }
};
