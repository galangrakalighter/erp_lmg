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
        Schema::create('black_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_creator')->constrained('social_commerce_creator')->cascadeOnDelete();
            $table->string('bulan');
            $table->string('job');
            $table->string('link_report');
            $table->integer('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('black_list');
    }
};
