<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $guidance = [
        'FOMO' => [
            'Menimbulkan rasa takut ketinggalan momen penting.',
            'Yang lain udah mulai, kamu masih mikir?',
        ],
        'Provokatif' => [
            'Menggugah rasa penasaran atau memancing opini.',
            'Kamu yakin konten kamu udah cukup menarik?',
        ],
        'Statistik' => [
            'Menggunakan angka/data untuk menarik perhatian.',
            '70% brand gagal karena gak punya strategi konten.',
        ],
        'Emotional' => [
            'Menyentuh sisi perasaan audiens (motivasi, empati, dll).',
            'Capek jualan tapi gak ada hasil? Kamu gak sendiri.',
        ],
        'Pengetahuan' => [
            'Memberikan insight baru yang belum diketahui banyak orang.',
            'Tahukah kamu? Caption panjang ternyata tingkatkan engagement.',
        ],
        'Kontroversial' => [
            'Mengangkat topik yang memancing debat ringan.',
            'Posting tiap hari itu gak selalu bikin akunmu naik!',
        ],
        'Naratif' => [
            'Membuka dengan cerita atau pengalaman pribadi.',
            'Waktu pertama kali handle klien, kita hampir gagal total.',
        ],
        'Visual' => [
            'Mengandalkan visual kuat atau perubahan drastis.',
            'Before-after tampilan feed setelah rebranding.',
        ],
    ];

    public function up(): void
    {
        foreach ($this->guidance as $name => [$fungsiSingkat, $contoh]) {
            DB::table('hooks')
                ->whereRaw('LOWER(TRIM(hooks)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => $fungsiSingkat,
                    'contoh' => $contoh,
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->guidance) as $name) {
            DB::table('hooks')
                ->whereRaw('LOWER(TRIM(hooks)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => null,
                    'contoh' => null,
                ]);
        }
    }
};
