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
        Schema::create('task', function (Blueprint $table) {
            $table->id();
            $table->string('nama_task');
            $table->foreignId('divisi')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('pic')->constrained('users')->cascadeOnDelete();
            $table->integer('priority');
            $table->integer('menit');
            $table->foreignId('id_manajement')->nullable()->constrained('data_jasa')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_deadline');
            $table->text('note')->nullable();
            $table->integer('status');
            $table->foreignId('project')->constrained('produk_client')->cascadeOnDelete();
            $table->time('jam_mulai')->nullable();
            $table->date('tanggal_approve');
            $table->date('tanggal_revisi');
            $table->date('tanggal_split');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task');
    }
};
