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
        Schema::create('adjustment_journal_details', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key ke tabel header (Cascading: Jika header dihapus, detail ikut terhapus otomatis)
            $table->foreignId('adjustment_journal_id')
                  ->constrained('adjustment_journals')
                  ->onDelete('cascade');
            
            // Foreign Key ke tabel Chart of Accounts (COA) kamu
            // Sesuaikan 'chart_of_accounts' dengan nama tabel riil COA-mu di PostgreSQL jika berbeda
            $table->foreignId('chart_of_account_id')
                  ->constrained('chart_of_account') 
                  ->onDelete('restrict'); // Mencegah COA dihapus jika sudah dipakai di jurnal
            
            // Tipe baris akuntansi (debit atau kredit)
            $table->string('keterangan_item')->nullable();
            
            // Menggunakan jenis 'decimal' agar presisi perhitungan keuangan di PostgreSQL akurat
            // 15 digit total angka dengan 2 angka di belakang koma (Rp 999.999.999.999,00)
            $table->decimal('debit', 15, 2)->default(0.00);
            $table->decimal('kredit', 15, 2)->default(0.00);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustment_journal_details');
    }
};
