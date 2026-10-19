<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $guidance = [
        'Ajakan Langsung' => [
            'Mengarahkan audiens langsung bertindak.',
            'Klik link di bio dan mulai campaign kamu hari ini.',
        ],
        'Ajakan Bertahap' => [
            'Mengajak langkah kecil dulu sebelum aksi utama.',
            'DM kami dulu buat lihat contoh laporan campaign.',
        ],
        'Ajakan Soft Selling' => [
            'Ajakan halus tanpa kesan memaksa.',
            'Cek dulu aja portofolio kita, siapa tahu cocok.',
        ],
        'Ajakan Berbasis Urgensi' => [
            'Menekankan batas waktu atau kesempatan terbatas.',
            'Slot tinggal 3 lagi, langsung amankan sekarang!',
        ],
        'Ajakan Berbasis Emosi' => [
            'Mengajak lewat sentuhan emosional.',
            'Yuk bareng-bareng bantu brand lokal makin dikenal.',
        ],
        'Ajakan Berbasis Benefit' => [
            'Fokus pada keuntungan yang akan didapat.',
            'Dapatkan bonus fee dan exposure tambahan di campaign ini!',
        ],
        'Ajakan Berbasis Interaksi' => [
            'Mengundang audiens untuk engage.',
            'Tag temen kamu yang perlu tahu insight ini!',
        ],
    ];

    public function up(): void
    {
        foreach ($this->guidance as $name => [$fungsiSingkat, $contoh]) {
            DB::table('jenis_cta')
                ->whereRaw('LOWER(TRIM(nama_cta)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => $fungsiSingkat,
                    'contoh' => $contoh,
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->guidance) as $name) {
            DB::table('jenis_cta')
                ->whereRaw('LOWER(TRIM(nama_cta)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => null,
                    'contoh' => null,
                ]);
        }
    }
};
