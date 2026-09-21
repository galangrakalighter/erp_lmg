<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_pdf_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_pdf_id')->constrained('file_pdf')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('nama_file');
            $table->string('location');
            $table->longText('editor_content')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['file_pdf_id', 'version_number']);
        });

        DB::table('file_pdf')
            ->where('is_pinned', true)
            ->orderBy('id')
            ->each(function ($file) {
                DB::table('file_pdf_versions')->insert([
                    'file_pdf_id' => $file->id,
                    'version_number' => 1,
                    'nama_file' => $file->nama_file,
                    'location' => $file->location,
                    'editor_content' => $file->editor_content,
                    'changed_by' => null,
                    'created_at' => $file->created_at ?? now(),
                    'updated_at' => $file->updated_at ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_pdf_versions');
    }
};
