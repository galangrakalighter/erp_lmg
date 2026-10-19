<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        collect(Storage::disk('public')->allFiles('pdf'))
            ->each(function (string $path): void {
                if (! Storage::disk('local')->exists($path)) {
                    $stored = Storage::disk('local')->put(
                        $path,
                        Storage::disk('public')->get($path),
                    );

                    if (! $stored) {
                        throw new RuntimeException("Dokumen lama gagal diamankan: {$path}");
                    }
                }

                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            });
    }

    public function down(): void
    {
        collect(Storage::disk('local')->allFiles('pdf'))
            ->each(function (string $path): void {
                if (! Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->put(
                        $path,
                        Storage::disk('local')->get($path),
                    );
                }
            });
    }
};
