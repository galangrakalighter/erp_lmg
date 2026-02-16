<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\HariLibur;
use Carbon\Carbon;

class SyncHariLibur extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-hari-libur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tahun = date('Y');
        // Contoh menggunakan API publik harilibur
        $response = Http::get("https://hari-libur-api.vercel.app/api?year=$tahun");

        if ($response->successful()) {
            $data = $response->json();
            
            foreach ($data as $row) {
                // Simpan atau update jika sudah ada
                HariLibur::updateOrCreate(
                    ['tanggal' => $row['tanggal']],
                    ['keterangan' => $row['keterangan']]
                );
            }
            $this->info("Berhasil sinkronisasi " . count($data) . " hari libur tahun $tahun.");
        } else {
            $this->error("Gagal mengambil data dari API.");
        }
    }
}
