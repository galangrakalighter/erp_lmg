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
        Schema::create('affiliator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_eikyoo')->constrained('eikyoo')->cascadeOnDelete();
            $table->string('done_4_konten')->default('false');
            $table->string('cb')->default('false');
            $table->string('td_pd')->default('false');
            $table->string('cek')->default('false');
            $table->integer('harga_co_real');
            $table->string('type');
            $table->integer('gmv');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliator');
    }
};
