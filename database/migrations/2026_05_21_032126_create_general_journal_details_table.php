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
        Schema::create('general_journal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('general_journal_id')
                  ->constrained('general_journals')
                  ->onDelete('cascade');

            // Menghubungkan ke tabel Chart of Account kamu
            $table->foreignId('chart_of_account_id')
                  ->constrained('chart_of_account')
                  ->onDelete('restrict'); // Mencegah COA dihapus jika sudah dipakai bertransaksi

            $table->string('keterangan_item')->nullable(); // Detail keterangan per baris akun
            
            // Menggunakan decimal (15,2) agar aman untuk nominal besar dan pecahan sen
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
        Schema::dropIfExists('general_journal_details');
    }
};
