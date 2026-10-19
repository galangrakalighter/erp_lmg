<?php

namespace App\Services;

use App\Models\MasterFolder;
use App\Models\MasterFolderFile;
use App\Models\ProdukClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityPageDescription
{
    public function describeMutation(Request $request): array
    {
        $action = match (strtoupper($request->method())) {
            'POST' => 'CREATE',
            'PUT', 'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE',
            default => 'ACTIVITY',
        };
        $verb = ['CREATE' => 'Membuat', 'UPDATE' => 'Mengubah', 'DELETE' => 'Menghapus'][$action] ?? 'Memproses';
        $target = $this->requestTargetName($request);
        $description = trim($verb.' '.$target);

        if ($folder = $request->route('folder')) {
            $folderName = $folder instanceof MasterFolder ? $folder->name : 'Master Folder #'.$folder;
            $description .= ' di folder '.$folderName;
        } elseif ($client = $this->clientContext($request)) {
            $description .= ' pada client '.$client['name'];
            if ($client['divisions'] !== []) {
                $description .= ' di divisi '.implode(', ', $client['divisions']);
            }
        }

        return [
            'action' => $action,
            'description' => $description,
            'target_name' => $target,
        ];
    }

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
            return 'Masuk ke folder '.$this->folderName($request);
        }

        if ($client = $this->clientContext($request)) {
            $description = 'Masuk ke client '.$client['name'];
            if ($client['divisions'] !== []) {
                $description .= ' di divisi '.implode(', ', $client['divisions']);
            }

            return $description;
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

        if (! str_starts_with($description, 'Mengunjungi halaman ')
            && ! preg_match('/^Masuk ke halaman [\w.-]+$/', $description)) {
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

    private function clientContext(Request $request): ?array
    {
        $value = null;
        foreach (['id_produk', 'client', 'produk', 'project'] as $parameter) {
            $candidate = $request->route($parameter);
            if ($candidate instanceof ProdukClient || (is_numeric($candidate) && (int) $candidate > 0)) {
                $value = $candidate;
                break;
            }
        }

        if ($value === null) {
            return null;
        }

        $client = $value instanceof ProdukClient ? $value : ProdukClient::query()->find((int) $value);
        if (! $client) {
            return null;
        }

        $divisions = DB::table('client_division')
            ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
            ->where('client_division.client_id', $client->id)
            ->orderBy('divisions.name')
            ->pluck('divisions.name')
            ->all();

        return ['name' => $client->nama, 'divisions' => $divisions];
    }

    private function requestTargetName(Request $request): string
    {
        foreach (['name', 'nama', 'nama_file', 'client', 'nama_task', 'nama_kpi', 'title', 'judul', 'email'] as $field) {
            $value = $request->input($field);
            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        foreach (['file', 'folder', 'id_produk', 'client', 'id'] as $parameter) {
            $value = $request->route($parameter);
            if ($value instanceof MasterFolderFile) return $value->original_name;
            if ($value instanceof MasterFolder) return $value->name;
            if ($value instanceof ProdukClient) return $value->nama;
        }

        return $this->pageName($request->route()?->getName(), $request);
    }
}
