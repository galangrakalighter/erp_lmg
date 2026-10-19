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
        Schema::create('period_locks', function (Blueprint $table) {
            $table->id();
            // Periode format YYYY-MM (contoh: 2026-06)
            $table->string('periode', 7)->unique(); 
            
            // Status kunci
            $table->boolean('is_locked')->default(false);
            
            // Kolom audit untuk keamanan
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_locks');
    }
};
