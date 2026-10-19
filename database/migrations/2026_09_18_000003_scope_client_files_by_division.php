<?php

use App\Models\FilePdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_pdf', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('client')
                ->constrained('divisions')->nullOnDelete();
            $table->index(['client', 'division_id']);
        });

        FilePdf::query()->orderBy('id')->each(function (FilePdf $file) {
            $divisionIds = DB::table('client_division')
                ->where('client_id', $file->client)
                ->orderBy('division_id')
                ->pluck('division_id');

            if ($divisionIds->isEmpty()) {
                return;
            }

            $file->update(['division_id' => (int) $divisionIds->first()]);

            if (! $file->is_system_template || ! $file->is_pinned) {
                return;
            }

            $client = DB::table('produk_client')->where('id', $file->client)->first();
            $documentName = pathinfo($file->nama_file, PATHINFO_FILENAME);
            $content = $file->editor_content ?: '<p><br></p>';

            foreach ($divisionIds->skip(1) as $divisionId) {
                $storedName = (string) str()->uuid().'.pdf';
                $path = 'pdf/'.$file->client.'/'.$divisionId.'/'.$storedName;
                $stored = Storage::disk('public')->exists($file->location)
                    ? Storage::disk('public')->copy($file->location, $path)
                    : Storage::disk('public')->put(
                        $path,
                        Pdf::loadView(
                            'pdf.client_document',
                            compact('client', 'documentName', 'content')
                        )->setPaper('a4', 'portrait')->output()
                    );

                if (! $stored) {
                    throw new RuntimeException('Dokumen bawaan per divisi gagal dibuat.');
                }

                try {
                    FilePdf::create([
                        'nama_file' => $file->nama_file,
                        'client' => $file->client,
                        'division_id' => (int) $divisionId,
                        'uploaded_by' => null,
                        'location' => $path,
                        'editor_content' => $content,
                        'is_generated_document' => true,
                        'is_system_template' => true,
                        'is_pinned' => true,
                        'pin_order' => $file->pin_order,
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
        Schema::table('file_pdf', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
        });
    }
};
