<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $advertisingUserIds = DB::table('user_division')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->where('roles.role_name', 'Advertising')
            ->pluck('user_division.user_id');

        if ($advertisingUserIds->isNotEmpty()) {
            DB::table('user_division')
                ->whereIn('parent_id', $advertisingUserIds)
                ->update(['parent_id' => null, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Relasi bawahan lama tidak dapat ditebak kembali dengan aman.
    }
};
