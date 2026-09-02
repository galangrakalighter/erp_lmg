<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['division_id', 'name']);
        });

        Schema::create('master_folder_role', function (Blueprint $table) {
            $table->foreignId('master_folder_id')->constrained('master_folders')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['master_folder_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_folder_role');
        Schema::dropIfExists('master_folders');
    }
};
