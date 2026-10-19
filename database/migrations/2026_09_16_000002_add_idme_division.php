<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $divisionId = DB::table('divisions')->whereRaw('LOWER(name) = ?', ['idme'])->value('id');

        if (! $divisionId) {
            $divisionId = ((int) DB::table('divisions')->max('id')) + 1;
            DB::table('divisions')->insert([
                'id' => $divisionId,
                'name' => 'IDME',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ([1 => 'IDME Head', 2 => 'IDME Specialist'] as $level => $roleName) {
            $role = DB::table('roles')
                ->where('division_id', $divisionId)
                ->where('role_name', $roleName)
                ->first();

            if ($role) {
                DB::table('roles')->where('id', $role->id)->update([
                    'level' => $level,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('roles')->insert([
                    'id' => ((int) DB::table('roles')->max('id')) + 1,
                    'division_id' => $divisionId,
                    'role_name' => $roleName,
                    'level' => $level,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('divisions', 'id'), COALESCE((SELECT MAX(id) FROM divisions), 1), true)");
            DB::statement("SELECT setval(pg_get_serial_sequence('roles', 'id'), COALESCE((SELECT MAX(id) FROM roles), 1), true)");
        }
    }

    public function down(): void
    {
        $divisionId = DB::table('divisions')->whereRaw('LOWER(name) = ?', ['idme'])->value('id');
        if ($divisionId) {
            DB::table('roles')->where('division_id', $divisionId)->delete();
            DB::table('divisions')->where('id', $divisionId)->delete();
        }
    }
};
