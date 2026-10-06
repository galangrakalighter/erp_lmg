<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_platform_mirrors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('produk_client')->cascadeOnDelete();
            $table->string('platform_a');
            $table->string('platform_b');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['client_id', 'platform_a', 'platform_b'], 'client_platform_mirror_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_platform_mirrors');
    }
};
