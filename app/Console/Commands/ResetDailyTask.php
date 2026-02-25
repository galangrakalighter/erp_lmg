<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class ResetDailyTask extends Command
{
    /**
     * Nama perintah yang dipanggil lewat terminal atau scheduler.
     */
    protected $signature = 'task:reset-daily';

    /**
     * Deskripsi perintah.
     */
    protected $description = 'Reset task menggantung ke pool dengan mengosongkan jam_mulai berdasarkan deadline di tanggal_mulai';

    /**
     * Jalankan perintah.
     */
    public function handle()
    {
        // 1. Ambil waktu sekarang
        $now = Carbon::now();

        // 2. Ambil task yang sedang terjadwal (jam_mulai tidak null)
        // dan statusnya belum Done atau Waiting Approval
        $tasks = Task::whereIn('status', [0, 1, 2, 5, 8]) 
                     ->get();

        $resetCount = 0;

        foreach ($tasks as $task) {
            // Kita gunakan isi dari tanggal_mulai (format H:i:s) untuk menghitung deadline hari ini
            // Jika tanggal_mulai null, kita lewati untuk menghindari error parse
            $deadlineTime = Carbon::parse($task->tanggal_deadline);

            // LOGIKA RESET:
            // Jika waktu sekarang sudah melewati deadline tugas tersebut hari ini
            if ($now->greaterThan($deadlineTime)) {
                    // JIKA STATUS LAIN (0,1,2,5): Ubah ke Overdue (7)
                    // Jam mulai tetap ada agar tetap tampil di grid dengan warna merah
                    $task->update([
                        'status' => 7,
                        'jam_mulai' => null
                    ]);
                $resetCount++;
            }else{
                $task->update([
                    'status' => 0,
                    'jam_mulai' => null
                ]);
                $resetCount++;
            }
        }

        $this->info("Pembersihan selesai: $resetCount task dikembalikan ke pool (jam_mulai di-reset).");
    }
}