<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PLATFORMS = [
        'Facebook', 'Instagram', 'TikTok', 'Threads', 'Google', 'YouTube',
    ];

    public function up(): void
    {
        $clientIds = DB::table('client_division')
            ->where('division_id', 1)
            ->distinct()
            ->pluck('client_id');

        DB::table('produk_client')
            ->whereIn('id', $clientIds)
            ->orderBy('id')
            ->get(['id', 'access_token'])
            ->each(function ($client): void {
                $tokens = is_array($client->access_token)
                    ? $client->access_token
                    : json_decode((string) $client->access_token, true);
                $tokens = is_array($tokens) ? $tokens : [];
                $platforms = array_values((array) ($tokens['platform'] ?? []));
                $access = array_values((array) ($tokens['access'] ?? []));

                foreach (self::PLATFORMS as $defaultPlatform) {
                    $exists = collect($platforms)->contains(
                        fn ($platform) => strcasecmp(trim((string) $platform), $defaultPlatform) === 0
                    );
                    if (! $exists) {
                        $platforms[] = $defaultPlatform;
                        $access[] = '';
                    }
                }

                DB::table('produk_client')->where('id', $client->id)->update([
                    'access_token' => json_encode([
                        'platform' => $platforms,
                        'access' => $access,
                    ]),
                ]);
            });
    }

    public function down(): void
    {
        // Tidak menghapus label platform karena sebagian mungkin sudah ada
        // sebelum migrasi dan token lama harus tetap dipertahankan.
    }
};
