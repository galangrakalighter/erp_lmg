<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('brand_identify', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->index();
            $table->unsignedBigInteger('archived_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('brand_identify', fn (Blueprint $table) => $table->dropColumn(['archived_at', 'archived_by']));
    }
};
