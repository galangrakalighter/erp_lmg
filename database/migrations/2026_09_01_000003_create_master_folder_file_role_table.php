<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_folder_file_role', function (Blueprint $table) {
            $table->foreignId('master_folder_file_id')->constrained('master_folder_files')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['master_folder_file_id', 'role_id']);
        });

        DB::table('master_folder_file_role')->insertUsing(
            ['master_folder_file_id', 'role_id'],
            DB::table('master_folder_files')
                ->join(
                    'master_folder_role',
                    'master_folder_role.master_folder_id',
                    '=',
                    'master_folder_files.master_folder_id'
                )
                ->select('master_folder_files.id', 'master_folder_role.role_id')
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('master_folder_file_role');
    }
};
