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
            $table->string('jira')->nullable();
            $table->foreignId('divisi')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('pic')->constrained('users')->cascadeOnDelete();
            $table->integer('priority');
            $table->integer('menit');
            $table->foreignId('id_manajement_konten')->nullable()->constrained('data_jasa')->cascadeOnDelete();
            $table->foreignId('id_kpi')->nullable()->constrained('kpi')->cascadeOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_deadline');
            $table->text('note')->nullable();
            $table->integer('status');
            $table->foreignId('project')->constrained('produk_client')->cascadeOnDelete();
            $table->time('jam_mulai')->nullable();
            $table->date('tanggal_approve')->nullable();
            $table->date('tanggal_revisi')->nullable();
            $table->date('tanggal_split')->nullable();
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
