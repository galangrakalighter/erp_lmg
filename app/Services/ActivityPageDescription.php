<?php

namespace App\Services;

use App\Models\MasterFolder;
use App\Models\MasterFolderFile;
use Illuminate\Http\Request;

class ActivityPageDescription
{
    public function describe(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if ($routeName === 'master-folder.index') {
            return 'Masuk ke halaman Master Folder';
        }

        if ($routeName === 'master-folder.mine') {
            return 'Masuk ke halaman Folder Saya';
        }

        if ($routeName === 'master-folder.show') {
            return 'Masuk ke detail folder '.$this->folderName($request);
        }

        if (in_array($routeName, [
            'master-folder.files.view',
            'master-folder.files.preview',
            'master-folder.files.download',
        ], true)) {
            $verb = match ($routeName) {
                'master-folder.files.download' => 'Mengunduh',
                'master-folder.files.preview' => 'Melihat preview',
                default => 'Membuka',
            };

            return sprintf(
                '%s file %s dari folder %s',
                $verb,
                $this->fileName($request),
                $this->folderName($request),
            );
        }

        return 'Mengunjungi halaman '.($routeName ?: '/'.ltrim($request->path(), '/'));
    }

    private function folderName(Request $request): string
    {
        $folder = $request->route('folder');

        if ($folder instanceof MasterFolder) {
            return $folder->name;
        }

        return 'Master Folder';
    }

    private function fileName(Request $request): string
    {
        $file = $request->route('file');

        if ($file instanceof MasterFolderFile) {
            return $file->original_name;
        }

        return is_string($file) && $file !== '' ? $file : 'file';
    }
}
