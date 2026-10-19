<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $guidance = [
        'Functional' => [
            'Fokus pada fungsi, fitur, dan manfaat utama produk/jasa.',
            'Fitur ini bantu kamu atur konten otomatis setiap minggu.',
        ],
        'Education' => [
            'Memberikan pengetahuan baru atau insight ke audiens.',
            'Ternyata engagement naik 40% kalau kamu upload di jam ini.',
        ],
        'Creative' => [
            'Menonjolkan ide unik, visual menarik, atau storytelling.',
            'Eksperimen desain ini bikin CTR naik 3x lipat.',
        ],
        'User Experience' => [
            'Berisi pengalaman nyata pengguna atau klien.',
            'Awalnya ragu, tapi setelah pakai jasa ini penjualan langsung naik.',
        ],
        'Call To Action' => [
            'Fokus pada ajakan bertindak untuk mendorong konversi.',
            'Klik link di bio dan mulai campaign kamu sekarang.',
        ],
        'How-To' => [
            'Memberi tutorial atau panduan langkah demi langkah.',
            'Cara bikin konten viral tanpa modal besar.',
        ],
        'Entertainment' => [
            'Membuat audiens terhibur, ringan, dan relatable.',
            'Kalau edit video udah 3 jam tapi hasilnya malah begini 😭.',
        ],
        'Trend Jacking' => [
            'Mengikuti tren viral dan mengaitkannya dengan brand.',
            "Tren video 'get ready with me' tapi versi digital marketing.",
        ],
        'Giveaway' => [
            'Membangun interaksi dengan iming-iming hadiah.',
            'Comment alasan kenapa kamu harus menang dan tag 2 teman kamu!',
        ],
        'Inspiration' => [
            'Mengangkat kisah motivasi atau semangat.',
            'Semua bisnis besar dimulai dari satu langkah kecil.',
        ],
        'Quest' => [
            'Menguji audiens berpartisipasi dalam tantangan atau misi.',
            'Coba upload ulang versi kamu dan tag akun kita!',
        ],
        'Testimoni' => [
            'Menunjukkan bukti nyata atau hasil dari pengguna lain.',
            'Setelah 3 minggu, omzet klien ini naik 2x lipat!',
        ],
        'POV' => [
            'Menggunakan sudut pandang personal (brand/user).',
            'Dari sudut pandang marketer yang udah gagal 10x.',
        ],
        'Tips' => [
            'Memberi saran atau cara praktis menyelesaikan masalah.',
            '5 cara biar konten kamu gak tenggelam di explore.',
        ],
        'Edutainment' => [
            'Edukatif tapi tetap menghibur dan ringan.',
            'Belajar algoritma TikTok tapi gaya stand-up comedy.',
        ],
        'Mirroring' => [
            'Membuat audiens merasa related dengan situasi tertentu.',
            'Kamu juga suka panik pas liat reach turun?',
        ],
    ];

    public function up(): void
    {
        foreach ($this->guidance as $name => [$fungsiSingkat, $contoh]) {
            DB::table('pilar')
                ->whereRaw('LOWER(TRIM(pilar)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => $fungsiSingkat,
                    'contoh' => $contoh,
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->guidance) as $name) {
            DB::table('pilar')
                ->whereRaw('LOWER(TRIM(pilar)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => null,
                    'contoh' => null,
                ]);
        }
    }
};
