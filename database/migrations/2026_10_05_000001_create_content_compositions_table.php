<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_compositions', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('fungsi_singkat')->nullable();
            $table->text('contoh')->nullable();
            $table->unsignedSmallInteger('status')->default(1);
            $table->unsignedBigInteger('kategori_id');
            $table->unique(['kategori_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_compositions');
    }
};
