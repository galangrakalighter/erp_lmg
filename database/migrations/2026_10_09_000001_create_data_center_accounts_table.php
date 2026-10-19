<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_center_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index();
            $table->string('provider', 100)->nullable()->index();
            $table->text('account_name');
            $table->text('username')->nullable();
            $table->text('email')->nullable();
            $table->text('password')->nullable();
            $table->text('recovery_email')->nullable();
            $table->text('phone')->nullable();
            $table->text('url')->nullable();
            $table->longText('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_center_accounts');
    }
};
