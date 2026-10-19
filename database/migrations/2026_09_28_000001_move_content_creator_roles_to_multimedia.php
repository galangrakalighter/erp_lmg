<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $digitalMarketingId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])
            ->value('id');

        $multimediaId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');

        if (! $multimediaId) {
            $multimediaId = ((int) DB::table('divisions')->max('id')) + 1;
            DB::table('divisions')->insert([
                'id' => $multimediaId,
                'name' => 'Multimedia',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $businessUnitHead = DB::table('roles')
            ->where('division_id', $multimediaId)
            ->where('role_name', 'Busines Unit Head')
            ->first();

        if (! $businessUnitHead) {
            $businessUnitHeadId = ((int) DB::table('roles')->max('id')) + 1;
            DB::table('roles')->insert([
                'id' => $businessUnitHeadId,
                'division_id' => $multimediaId,
                'role_name' => 'Busines Unit Head',
                'level' => 1,
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $businessUnitHeadId = $businessUnitHead->id;
            DB::table('roles')->where('id', $businessUnitHeadId)->update([
                'level' => 1,
                'parent_id' => null,
                'updated_at' => now(),
            ]);
        }

        $managerId = DB::table('roles')
            ->where('role_name', 'Content Creator Manager')
            ->value('id');

        if ($managerId) {
            DB::table('roles')->where('id', $managerId)->update([
                'division_id' => $multimediaId,
                'level' => 2,
                'parent_id' => $businessUnitHeadId,
                'updated_at' => now(),
            ]);
        }

        $creatorId = DB::table('roles')
            ->where('role_name', 'Content Creator')
            ->value('id');

        if ($creatorId) {
            DB::table('roles')->where('id', $creatorId)->update([
                'division_id' => $multimediaId,
                'level' => 3,
                'parent_id' => $managerId,
                'updated_at' => now(),
            ]);
        }

        $contentRoleIds = array_values(array_filter([$managerId, $creatorId]));

        if ($contentRoleIds !== []) {
            DB::table('user_division')
                ->whereIn('role_id', $contentRoleIds)
                ->update([
                    'division_id' => $multimediaId,
                    'updated_at' => now(),
                ]);
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('divisions', 'id'), COALESCE((SELECT MAX(id) FROM divisions), 1), true)");
            DB::statement("SELECT setval(pg_get_serial_sequence('roles', 'id'), COALESCE((SELECT MAX(id) FROM roles), 1), true)");
        }
    }

    public function down(): void
    {
        $digitalMarketingId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])
            ->value('id');

        $multimediaId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');

        if (! $digitalMarketingId || ! $multimediaId) {
            return;
        }

        $digitalMarketingHeadId = DB::table('roles')
            ->where('division_id', $digitalMarketingId)
            ->where('role_name', 'Busines Unit Head')
            ->value('id');

        $managerId = DB::table('roles')
            ->where('division_id', $multimediaId)
            ->where('role_name', 'Content Creator Manager')
            ->value('id');

        $creatorId = DB::table('roles')
            ->where('division_id', $multimediaId)
            ->where('role_name', 'Content Creator')
            ->value('id');

        if ($managerId) {
            DB::table('roles')->where('id', $managerId)->update([
                'division_id' => $digitalMarketingId,
                'level' => 2,
                'parent_id' => $digitalMarketingHeadId,
                'updated_at' => now(),
            ]);
        }

        if ($creatorId) {
            DB::table('roles')->where('id', $creatorId)->update([
                'division_id' => $digitalMarketingId,
                'level' => 3,
                'parent_id' => $managerId,
                'updated_at' => now(),
            ]);
        }

        $contentRoleIds = array_values(array_filter([$managerId, $creatorId]));

        if ($contentRoleIds !== []) {
            DB::table('user_division')
                ->whereIn('role_id', $contentRoleIds)
                ->update([
                    'division_id' => $digitalMarketingId,
                    'updated_at' => now(),
                ]);
        }
    }
};
