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

        if (! $digitalMarketingId) {
            return;
        }

        $digitalMarketingRole = DB::table('roles')
            ->where('division_id', $digitalMarketingId)
            ->where('role_name', 'Digital Marketing')
            ->first();
        $contentCreator = DB::table('roles')
            ->where('role_name', 'Content Creator')
            ->first();

        if (! $digitalMarketingRole || ! $contentCreator) {
            return;
        }

        DB::table('roles')->where('id', $contentCreator->id)->update([
            'division_id' => $digitalMarketingId,
            'level' => ((int) $digitalMarketingRole->level) + 1,
            'parent_id' => $digitalMarketingRole->id,
            'updated_at' => now(),
        ]);

        DB::table('user_division')
            ->where('role_id', $contentCreator->id)
            ->update([
                'division_id' => $digitalMarketingId,
                // Parent lama adalah user Content Creator Manager di Multimedia.
                // Admin memilih user Digital Marketing yang tepat dari Manajemen User.
                'parent_id' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $multimediaId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');
        $managerRole = $multimediaId
            ? DB::table('roles')
                ->where('division_id', $multimediaId)
                ->where('role_name', 'Content Creator Manager')
                ->first()
            : null;
        $contentCreator = DB::table('roles')
            ->where('role_name', 'Content Creator')
            ->first();

        if (! $multimediaId || ! $managerRole || ! $contentCreator) {
            return;
        }

        DB::table('roles')->where('id', $contentCreator->id)->update([
            'division_id' => $multimediaId,
            'level' => ((int) $managerRole->level) + 1,
            'parent_id' => $managerRole->id,
            'updated_at' => now(),
        ]);

        DB::table('user_division')
            ->where('role_id', $contentCreator->id)
            ->update([
                'division_id' => $multimediaId,
                'parent_id' => null,
                'updated_at' => now(),
            ]);
    }
};
