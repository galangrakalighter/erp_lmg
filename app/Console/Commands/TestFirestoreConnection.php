<?php

namespace App\Console\Commands;

use App\Services\FirestoreService;
use Illuminate\Console\Command;
use Throwable;

class TestFirestoreConnection extends Command
{
    protected $signature = 'firestore:test {collection? : Collection yang digunakan untuk tes baca}';

    protected $description = 'Menguji kredensial dan koneksi ke Cloud Firestore';

    public function handle(FirestoreService $firestore): int
    {
        try {
            $result = $firestore->testConnection($this->argument('collection'));

            $this->info('Koneksi Firestore berhasil.');
            $this->table(['Project', 'Database', 'Collection', 'Dokumen sampel'], [[
                $result['project_id'],
                $result['database_id'],
                $result['collection'],
                $result['sample_count'],
            ]]);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Koneksi Firestore gagal: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
