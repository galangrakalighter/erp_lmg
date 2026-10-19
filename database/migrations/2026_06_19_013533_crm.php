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
        Schema::create('crm', function (Blueprint $table) {
            $table->id();
            $table->timestamp('tanggal_chat');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('id_proteksi')->nullbale();
            $table->integer('project_stage')->nullable();
            $table->string('phone')->nullable();
            $table->string('layanan')->nullable();
            $table->string('chat_ketiga')->nullable();
            $table->string('project_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm');
    }
};
