<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'dian@lightermediagroup.com';

    public function up(): void
    {
        $userId = DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->value('id');
        $divisionId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])
            ->value('id');
        $roleId = $divisionId
            ? DB::table('roles')
                ->where('division_id', $divisionId)
                ->where('role_name', 'Content Creator')
                ->value('id')
            : null;

        if (! $userId || ! $divisionId || ! $roleId) {
            return;
        }

        DB::table('user_division')->updateOrInsert(
            ['user_id' => $userId],
            [
                'division_id' => $divisionId,
                'role_id' => $roleId,
                'parent_id' => null,
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        $userId = DB::table('users')->whereRaw('LOWER(email) = ?', [self::EMAIL])->value('id');
        $divisionId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');
        $roleId = $divisionId
            ? DB::table('roles')
                ->where('division_id', $divisionId)
                ->where('role_name', 'Busines Unit Head')
                ->value('id')
            : null;

        if (! $userId || ! $divisionId || ! $roleId) {
            return;
        }

        DB::table('user_division')->updateOrInsert(
            ['user_id' => $userId],
            [
                'division_id' => $divisionId,
                'role_id' => $roleId,
                'parent_id' => null,
                'updated_at' => now(),
            ]
        );
    }
};
