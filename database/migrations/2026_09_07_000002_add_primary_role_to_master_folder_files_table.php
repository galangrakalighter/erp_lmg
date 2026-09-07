<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_folder_files', function (Blueprint $table) {
            $table->foreignId('primary_role_id')->nullable()->after('uploaded_by')
                ->constrained('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('master_folder_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_role_id');
        });
    }
};
