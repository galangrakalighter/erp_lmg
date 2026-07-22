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
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); 
            $table->date('payroll_date');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('unpaid'); // unpaid, partial, paid
            $table->text('keterangan')->nullable();
            $table->string('invoice')->nullable(); // unpaid, partial, paid
            $table->timestamps();
        });

        Schema::create('payroll_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payroll')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('invoice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_detail');
        Schema::dropIfExists('payroll');
    }
};
