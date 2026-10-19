<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_mirror_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('produk_client')->cascadeOnDelete();
            $table->string('table_name');
            $table->unsignedBigInteger('source_id');
            $table->string('source_platform');
            $table->unsignedBigInteger('target_id');
            $table->string('target_platform');
            $table->timestamps();
            $table->unique(
                ['client_id', 'table_name', 'source_id', 'target_platform'],
                'platform_mirror_copy_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_mirror_copies');
    }
};
