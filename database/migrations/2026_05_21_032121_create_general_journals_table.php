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
        Schema::create('general_journals', function (Blueprint $table) {
            $table->id();
            $table->string('no_jurnal')->unique(); // Contoh: JV-202605-0001
            $table->date('tgl_transaksi');
            $table->string('no_invoice')->nullable(); // Opsional untuk referensi invoice
            $table->string('status')->nullable(); // Opsional untuk referensi invoice
            $table->text('keterangan')->nullable();
            
            // Relasi ke tabel users untuk mencatat siapa yang menginput (Audit Trail)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_journals');
    }
};
