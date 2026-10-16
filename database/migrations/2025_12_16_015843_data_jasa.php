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
        Schema::create('data_jasa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_client_id')->constrained("produk_client")->cascadeOnDelete();
            $table->string('judul');
            $table->string('inspirasi')->nullable();
            $table->foreignId('type_produk_id')->constrained("type_produk")->cascadeOnDelete();
            $table->foreignId('strategy_id')->constrained("strategy")->cascadeOnDelete();
            $table->foreignId('pilar_id')->constrained("pilar")->cascadeOnDelete();
            $table->foreignId('hooks_id')->constrained("hooks")->cascadeOnDelete();
            $table->foreignId('body_id')->constrained("jenis_body")->cascadeOnDelete();
            $table->foreignId('cta_id')->constrained("jenis_cta")->cascadeOnDelete();
            $table->string('durasi')->nullable();
            $table->string('background')->nullable();
            $table->string('komposisi')->nullable();
            $table->text('note')->nullable();
            $table->date('tanggal_posting')->nullable();
            $table->string('link')->nullable();
            $table->string('platform')->nullable();
            $table->integer('status_id')->constrained("status")->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_jasa');
    }
};
