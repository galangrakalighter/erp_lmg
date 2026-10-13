<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $this->documentPaths()->each(function (string $path): void {
            if (! Storage::disk('local')->exists($path)) {
                if (! Storage::disk('public')->exists($path)) {
                    return;
                }

                $stored = Storage::disk('local')->put(
                    $path,
                    Storage::disk('public')->get($path),
                );

                if (! $stored) {
                    throw new RuntimeException("Dokumen client gagal dipindahkan ke storage privat: {$path}");
                }
            }

            // Salinan publik baru dihapus setelah salinan privat terverifikasi.
            if (Storage::disk('local')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    public function down(): void
    {
        $this->documentPaths()->each(function (string $path): void {
            if (! Storage::disk('local')->exists($path)) {
                return;
            }

            Storage::disk('public')->put(
                $path,
                Storage::disk('local')->get($path),
            );
        });
    }

    private function documentPaths()
    {
        $paths = DB::table('file_pdf')
            ->whereNotNull('location')
            ->pluck('location');

        if (DB::getSchemaBuilder()->hasTable('file_pdf_versions')) {
            $paths = $paths->merge(
                DB::table('file_pdf_versions')
                    ->whereNotNull('location')
                    ->pluck('location'),
            );
        }

        return $paths
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn ($path) => trim($path))
            ->unique()
            ->values();
    }
};
