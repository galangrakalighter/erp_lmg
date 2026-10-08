<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $divisionId = DB::table('divisions')->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])->value('id');
        if (! $divisionId) return;

        $headId = DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Busines Unit Head')->value('id');
        $managerId = DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Digital Marketing Manager')->value('id');
        if (! $managerId) {
            $managerId = DB::table('roles')->insertGetId(['division_id' => $divisionId, 'role_name' => 'Digital Marketing Manager', 'level' => 2, 'parent_id' => $headId, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Digital Marketer')->update(['role_name' => 'Digital Marketing', 'level' => 3, 'parent_id' => $managerId, 'updated_at' => now()]);
        DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Advertising')->update(['level' => 3, 'parent_id' => $managerId, 'updated_at' => now()]);

        DB::table('roles')->updateOrInsert(
            ['division_id' => $divisionId, 'role_name' => 'Sosial Media Spesialis'],
            ['level' => 3, 'parent_id' => $managerId, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        $divisionId = DB::table('divisions')->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])->value('id');
        if (! $divisionId) return;
        $headId = DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Busines Unit Head')->value('id');
        DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Digital Marketing')->update(['role_name' => 'Digital Marketer', 'level' => 2, 'parent_id' => $headId, 'updated_at' => now()]);
        DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Advertising')->update(['level' => 2, 'parent_id' => $headId, 'updated_at' => now()]);
        DB::table('roles')->where('division_id', $divisionId)->whereIn('role_name', ['Digital Marketing Manager', 'Sosial Media Spesialis'])->delete();
    }
};
