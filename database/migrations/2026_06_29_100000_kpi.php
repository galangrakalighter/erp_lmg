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
        Schema::create('kpi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kpi');
            $table->foreignId('divisi')->constrained('divisions')->cascadeOnDelete();
            $table->integer('point');
            $table->date('tanggal_mulai');
            $table->date('tanggal_deadline');
            $table->text('note')->nullable();
            $table->integer('status');
            $table->foreignId('project')->constrained('produk_client')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi');
    }
};
