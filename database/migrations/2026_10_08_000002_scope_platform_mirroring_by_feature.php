<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_platform_mirrors', function (Blueprint $table) {
            $table->dropUnique('client_platform_mirror_unique');
            $table->string('scope')->default('market-research')->after('client_id');
        });

        $scopes = [
            'branding', 'social-media-research', 'advertising-research',
            'content-plan-organic', 'content-plan-paid',
        ];

        foreach (DB::table('client_platform_mirrors')->get() as $pair) {
            foreach ($scopes as $scope) {
                DB::table('client_platform_mirrors')->insert([
                    'client_id' => $pair->client_id,
                    'scope' => $scope,
                    'platform_a' => $pair->platform_a,
                    'platform_b' => $pair->platform_b,
                    'active' => $pair->active,
                    'created_at' => $pair->created_at,
                    'updated_at' => $pair->updated_at,
                ]);
            }
        }

        Schema::table('client_platform_mirrors', function (Blueprint $table) {
            $table->unique(
                ['client_id', 'scope', 'platform_a', 'platform_b'],
                'client_platform_mirror_scope_unique'
            );
        });
    }

    public function down(): void
    {
        DB::table('client_platform_mirrors')->where('scope', '!=', 'market-research')->delete();
        Schema::table('client_platform_mirrors', function (Blueprint $table) {
            $table->dropUnique('client_platform_mirror_scope_unique');
            $table->dropColumn('scope');
            $table->unique(
                ['client_id', 'platform_a', 'platform_b'],
                'client_platform_mirror_unique'
            );
        });
    }
};
