<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $divisionId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])
            ->value('id');

        if (! $divisionId) {
            return;
        }

        $manager = DB::table('roles')
            ->where('division_id', $divisionId)
            ->where('role_name', 'Digital Marketing Manager')
            ->first();
        $creator = DB::table('roles')
            ->where('division_id', $divisionId)
            ->where('role_name', 'Content Creator')
            ->first();

        if (! $manager || ! $creator) {
            return;
        }

        DB::table('roles')->where('id', $creator->id)->update([
            'level' => ((int) $manager->level) + 1,
            'parent_id' => $manager->id,
            'updated_at' => now(),
        ]);

        $validManagerIds = DB::table('user_division')
            ->where('division_id', $divisionId)
            ->where('role_id', $manager->id)
            ->pluck('user_id');

        DB::table('user_division')
            ->where('role_id', $creator->id)
            ->when($validManagerIds->isNotEmpty(), fn ($query) => $query->whereNotIn('parent_id', $validManagerIds))
            ->when($validManagerIds->isEmpty(), fn ($query) => $query)
            ->update(['parent_id' => null, 'updated_at' => now()]);
    }

    public function down(): void
    {
        $divisionId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])
            ->value('id');
        $digitalMarketing = $divisionId
            ? DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Digital Marketing')->first()
            : null;
        $creator = $divisionId
            ? DB::table('roles')->where('division_id', $divisionId)->where('role_name', 'Content Creator')->first()
            : null;

        if ($digitalMarketing && $creator) {
            DB::table('roles')->where('id', $creator->id)->update([
                'level' => ((int) $digitalMarketing->level) + 1,
                'parent_id' => $digitalMarketing->id,
                'updated_at' => now(),
            ]);
            DB::table('user_division')->where('role_id', $creator->id)->update([
                'parent_id' => null,
                'updated_at' => now(),
            ]);
        }
    }
};
