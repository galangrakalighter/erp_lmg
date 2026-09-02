<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('master_folders')->update([
            'division_id' => null,
            'is_personal' => false,
        ]);
    }

    public function down(): void
    {
        // Folder lama tidak dapat dipetakan kembali ke divisi secara aman.
    }
};
