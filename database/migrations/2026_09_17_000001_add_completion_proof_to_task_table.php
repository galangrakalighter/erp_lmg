<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task', function (Blueprint $table) {
            $table->string('completion_proof_path')->nullable();
            $table->string('completion_proof_name')->nullable();
            $table->string('completion_proof_mime')->nullable();
            $table->unsignedBigInteger('completion_proof_size')->nullable();
            $table->timestamp('completion_proof_uploaded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('task', function (Blueprint $table) {
            $table->dropColumn([
                'completion_proof_path',
                'completion_proof_name',
                'completion_proof_mime',
                'completion_proof_size',
                'completion_proof_uploaded_at',
            ]);
        });
    }
};
