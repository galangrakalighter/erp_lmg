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
        Schema::create('eikyoo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_affiliator')->constrained('social_commerce_creator')->cascadeOnDelete();
            $table->string('social_media');
            $table->string('wajib_follow');
            $table->text('surat_pernyataan');
            $table->string('project');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eikyoo');
    }
};
