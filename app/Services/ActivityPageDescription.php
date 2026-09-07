<?php

namespace App\Services;

use App\Models\MasterFolder;
use App\Models\MasterFolderFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        return 'Masuk ke halaman '.$this->pageName($routeName, $request);
    }

    public function describeStoredLog(array $log): string
    {
        $description = (string) ($log['description'] ?? 'Aktivitas pengguna');

        if (strtoupper((string) ($log['action'] ?? '')) !== 'VISIT') {
            return $description;
        }

        $routeName = data_get($log, 'new_data.route_name');

        if (! is_string($routeName) || $routeName === '') {
            return $description;
        }

        return 'Masuk ke halaman '.$this->pageName($routeName);
    }

    private function pageName(?string $routeName, ?Request $request = null): string
    {
        if (! $routeName) {
            $path = preg_replace('#/\d+(?=/|$)#', '', '/'.ltrim($request?->path() ?? '', '/'));

            return Str::headline(trim($path, '/') ?: 'Dashboard');
        }

        $knownPages = [
            'home' => 'Dashboard',
            'activity-logs' => 'Activity Log',
            'hc_daily' => 'Daily Work',
            'hc_overview' => 'HC Overview',
            'overview' => 'HC Dashboard',
            'manajemen_user' => 'Manajemen User',
            'master-folder' => 'Master Folder',
            'file-master' => 'File Master',
        ];
        $segments = explode('.', $routeName);
        $resource = $segments[0];

        if (isset($knownPages[$resource])) {
            return $knownPages[$resource];
        }

        $routeActions = ['index', 'show', 'create', 'edit', 'store', 'update', 'destroy'];
        $segments = array_values(array_filter(
            $segments,
            fn (string $segment): bool => ! in_array(strtolower($segment), $routeActions, true),
        ));

        return Str::headline(implode(' ', $segments) ?: $resource);
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
