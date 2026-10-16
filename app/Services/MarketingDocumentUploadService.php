<?php

namespace App\Services;

use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class MarketingDocumentUploadService
{
    public function __construct(
        private readonly MarketingAssetAuthorization $policy,
        private readonly MarketingAssetMutationAudit $audit,
    ) {}

    public function upload(Authenticatable $user, int $clientId, ?int $documentId, UploadedFile $file, array $metadata): array
    {
        $permission = $documentId ? MarketingCrudPermissions::ASSET_METADATA_UPDATE : MarketingCrudPermissions::ASSET_METADATA_CREATE;
        $this->policy->assert($user, $clientId, $permission);
        $document = null;
        if ($documentId) {
            $document = $this->policy->object($user, $documentId, $permission);
            abort_unless((int) $document->client_id === $clientId, 403, 'Dokumen bukan milik client yang dipilih.');
            abort_if($document->archived_at, 409, 'Dokumen sudah diarsipkan.');
        }

        abort_unless($file->isValid() && $file->getSize() > 0, 422, 'File PDF kosong atau gagal diunggah.');
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';
        abort_unless($detectedMime === 'application/pdf', 422, 'File harus PDF dengan MIME application/pdf.');
        abort_if($file->getSize() > ((int) config('marketing-mcp.document_upload_max_kb', 20480) * 1024), 422, 'Ukuran file PDF melebihi batas server.');

        $disk = (string) config('marketing-mcp.document_storage_disk', 'local');
        $safeOriginal = Str::limit(basename(str_replace('\\', '/', $metadata['file_name'])), 255, '');
        abort_unless(Str::endsWith(Str::lower($safeOriginal), '.pdf'), 422, 'Nama file harus menggunakan ekstensi .pdf.');
        $path = 'marketing/documents/'.$clientId.'/'.Str::uuid().'.pdf';
        $stored = false;

        try {
            $result = DB::transaction(function () use ($user, $clientId, $documentId, $document, $file, $metadata, $disk, $path, $safeOriginal, &$stored): array {
                if (! Storage::disk($disk)->put($path, $file->getContent(), ['visibility' => 'private'])) {
                    throw new \RuntimeException('Private storage menolak upload.');
                }
                $stored = true;
                $id = $documentId;
                if (! $id) {
                    $id = DB::table('marketing_asset_metadata')->insertGetId([
                        'client_id' => $clientId, 'created_by' => $user->getAuthIdentifier(),
                        'name' => $metadata['name'], 'asset_type' => 'document', 'mime_type' => 'application/pdf',
                        'description' => $metadata['description'] ?? null, 'approval_status' => 'draft',
                        'current_version' => 0, 'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
                $current = (int) DB::table('marketing_asset_metadata')->where('id', $id)->lockForUpdate()->value('current_version');
                $version = $current + 1;
                DB::table('marketing_asset_versions')->insert([
                    'asset_metadata_id' => $id, 'version_number' => $version, 'label' => 'v'.$version,
                    'note' => $metadata['description'] ?? null, 'created_by' => $user->getAuthIdentifier(),
                    'original_name' => $safeOriginal, 'mime_type' => 'application/pdf', 'file_size' => $file->getSize(),
                    'storage_disk' => $disk, 'storage_path' => $path, 'upload_status' => 'uploaded', 'created_at' => now(),
                ]);
                DB::table('marketing_asset_metadata')->where('id', $id)->update(['mime_type' => 'application/pdf', 'current_version' => $version, 'updated_at' => now()]);
                return ['document_id' => (int) $id, 'client_id' => $clientId, 'file_name' => $safeOriginal, 'mime' => 'application/pdf', 'size' => (int) $file->getSize(), 'version' => $version, 'upload_status' => 'uploaded', 'timestamp' => now('Asia/Jakarta')->toIso8601String()];
            });
        } catch (Throwable $exception) {
            if ($stored) Storage::disk($disk)->delete($path);
            throw $exception;
        }

        $this->audit->record($user, 'UPLOAD', $result['document_id'], $clientId, [
            'file_name' => $result['file_name'], 'mime' => $result['mime'], 'size' => $result['size'],
            'version' => $result['version'], 'upload_status' => 'uploaded',
        ], 'marketing_upload_document_file');
        return $result;
    }
}
