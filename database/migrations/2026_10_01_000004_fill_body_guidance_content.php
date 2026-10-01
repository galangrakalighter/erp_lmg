<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $guidance = [
        'Problem Statement' => [
            'Menunjukkan masalah utama audiens.',
            'Bingung kenapa kontenmu gak dapet engagement?',
        ],
        'Solution / Value' => [
            'Menawarkan solusi atau nilai yang diberikan.',
            'Kita bantu kelola campaign dari briefing sampai laporan.',
        ],
        'Key Benefit' => [
            'Menonjolkan hasil atau keunggulan utama produk/jasa.',
            'Lebih cepat, lebih hemat, hasil bisa kamu pantau langsung.',
        ],
        'Detail Informasi' => [
            'Menyampaikan info penting (harga, jadwal, syarat).',
            'Promo berlaku 1–30 November, hanya untuk 10 klien pertama.',
        ],
        'Social Proof / Credibility' => [
            'Memberi bukti nyata untuk meningkatkan kepercayaan.',
            'Sudah dipercaya 150+ brand lokal di seluruh Indonesia.',
        ],
        'Emotional Trigger' => [
            'Memicu rasa penasaran, semangat, atau urgensi.',
            "Kamu bisa mulai hari ini juga tanpa harus nunggu 'sempurna'.",
        ],
        'Reminder / Highlight' => [
            'Menegaskan poin penting atau batas waktu.',
            'Ingat, promo ini gak akan diulang bulan depan!',
        ],
    ];

    public function up(): void
    {
        foreach ($this->guidance as $name => [$fungsiSingkat, $contoh]) {
            DB::table('jenis_body')
                ->whereRaw('LOWER(TRIM(nama_body)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => $fungsiSingkat,
                    'contoh' => $contoh,
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->guidance) as $name) {
            DB::table('jenis_body')
                ->whereRaw('LOWER(TRIM(nama_body)) = ?', [strtolower($name)])
                ->update([
                    'fungsi_singkat' => null,
                    'contoh' => null,
                ]);
        }
    }
};
