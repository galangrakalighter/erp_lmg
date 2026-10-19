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
        Schema::create('opening_balances', function (Blueprint $table) {
            $table->id();
            // Menyimpan format "YYYY-MM" (Contoh: "2026-01", "2026-02")
            $table->string('periode', 7); 
            
            $table->unsignedBigInteger('chart_of_account_id');
            
            // Menggunakan nilai murni saldo tanpa debit/kredit terpisah agar kalkulasi report lebih mudah
            $table->decimal('saldo', 15, 2)->default(0.00); 
            
            $table->timestamps();

            // Indexing untuk optimasi query pembacaan report yang cepat
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_account')->onDelete('cascade');
            $table->unique(['periode', 'chart_of_account_id'], 'idx_periode_coa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_balances');
    }
};
