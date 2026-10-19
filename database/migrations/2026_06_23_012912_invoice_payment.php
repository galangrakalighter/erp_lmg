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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->foreignId('customer_id')->constrained('produk_client')->cascadeOnDelete(); 
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('unpaid'); // unpaid, partial, paid
            $table->date('due_date');
            $table->timestamps();
        });

        // Tabel Invoice Payments (Riwayat Pembayaran)
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('method');
            $table->string('file_path')->nullable();
            $table->string('no_invoice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoice_payments');
    }
};
