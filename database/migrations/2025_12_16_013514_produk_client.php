<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('produk_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_jasa_id')->nullable()->constrained("kategori_jasa")->cascadeOnDelete();
            $table->string('nama');
            $table->integer('status');
            $table->json('access_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_client');
    }
};
