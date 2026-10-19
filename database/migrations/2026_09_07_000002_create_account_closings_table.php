<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_closings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('perusahaan');
            $table->string('periode', 7);
            $table->date('closing_date');
            $table->foreignId('chart_of_account_id')->constrained('chart_of_account')->cascadeOnDelete();
            $table->foreignId('equity_account_id')->constrained('chart_of_account')->restrictOnDelete();
            $table->decimal('saldo_sebelum_tutup', 15, 2);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->decimal('dampak_laba_rugi', 15, 2)->default(0);
            $table->boolean('saldo_awal_berikutnya_sudah_ada')->default(false);
            $table->decimal('saldo_awal_berikutnya_lama', 15, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['perusahaan', 'periode', 'chart_of_account_id'], 'account_closings_company_period_coa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_closings');
    }
};
