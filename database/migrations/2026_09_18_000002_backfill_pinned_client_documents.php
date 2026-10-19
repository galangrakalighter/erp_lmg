<?php

use App\Models\FilePdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produk_client')
            || ! Schema::hasTable('file_pdf')
            || ! Schema::hasColumn('file_pdf', 'is_pinned')) {
            return;
        }

        $documents = [
            'Contract Status.pdf',
            'Core SOW.pdf',
            'Contract Operational Notes.pdf',
            'Project Handover.pdf',
        ];

        DB::table('produk_client')->orderBy('id')->each(function ($client) use ($documents) {
            foreach ($documents as $index => $fileName) {
                $exists = FilePdf::query()
                    ->where('client', $client->id)
                    ->whereRaw('LOWER(nama_file) = ?', [mb_strtolower($fileName)])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $documentName = pathinfo($fileName, PATHINFO_FILENAME);
                $content = '<p><br></p>';
                $storedName = (string) str()->uuid().'.pdf';
                $path = 'pdf/'.$client->id.'/'.$storedName;
                $pdfContent = Pdf::loadView(
                    'pdf.client_document',
                    compact('client', 'documentName', 'content')
                )->setPaper('a4', 'portrait')->output();

                if (! Storage::disk('public')->put($path, $pdfContent)) {
                    throw new RuntimeException('Dokumen bawaan client lama gagal dibuat.');
                }

                try {
                    FilePdf::create([
                        'nama_file' => $fileName,
                        'client' => $client->id,
                        'uploaded_by' => null,
                        'location' => $path,
                        'editor_content' => $content,
                        'is_generated_document' => true,
                        'is_system_template' => true,
                        'is_pinned' => true,
                        'pin_order' => $index + 1,
                    ]);
                } catch (Throwable $exception) {
                    Storage::disk('public')->delete($path);
                    throw $exception;
                }
            }
        });
    }

    public function down(): void
    {
        // File dibuat sebagai data operasional client dan sengaja dipertahankan saat rollback.
    }
};
