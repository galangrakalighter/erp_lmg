<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogFormatter
{
    private const CRUD_ACTIONS = ['CREATE', 'UPDATE', 'DELETE'];

    private const IGNORED_FIELDS = [
        'id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token',
        'stored_name', 'editor_content',
    ];

    private array $lookupCache = [];

    private bool $formattingTask = false;

    public function format(array $log): ?array
    {
        $action = strtoupper((string) ($log['action'] ?? ''));

        if (! in_array($action, self::CRUD_ACTIONS, true)) {
            return null;
        }

        $target = $log['target_name']
            ?? Str::headline((string) ($log['target_type'] ?? 'data'));
        $oldData = $this->filterData($log['old_data'] ?? []);
        $newData = $this->filterData($log['new_data'] ?? []);
        $context = is_array($log['context'] ?? null) ? $log['context'] : [];
        $this->formattingTask = ($log['target_type'] ?? null) === 'task'
            || ($log['module'] ?? null) === 'task';

        if ($this->formattingTask) {
            $target = $newData['nama_task'] ?? $oldData['nama_task'] ?? $target;
        }

        if (($context['kind'] ?? null) === 'master_folder_file') {
            return $this->formatMasterFolderFile($action, $context, $oldData, $newData);
        }

        if (($context['kind'] ?? null) === 'master_folder') {
            return $this->formatMasterFolder($action, $context, $oldData, $newData);
        }

        if (($context['kind'] ?? null) === 'account_master_file') {
            return $this->formatAccountMasterFile($action, $context, $oldData, $newData);
        }

        return match ($action) {
            'CREATE' => [
                'summary' => "Menambahkan data {$target} beserta atributnya:",
                'items' => $this->attributeItems($newData),
            ],
            'UPDATE' => [
                'summary' => "Mengubah data {$target}:",
                'items' => $this->changeItems($oldData, $newData),
            ],
            'DELETE' => [
                'summary' => "Menghapus data {$target} beserta atribut sebelumnya:",
                'items' => $this->attributeItems($oldData),
            ],
        };
    }

    private function formatMasterFolderFile(string $action, array $context, array $oldData, array $newData): array
    {
        $file = $context['file_name'] ?? 'file';
        $folder = $context['folder_name'] ?? 'Master Folder';
        $verb = match ($action) {
            'CREATE' => 'Menambahkan',
            'UPDATE' => 'Mengubah',
            'DELETE' => 'Menghapus',
        };
        $connector = $action === 'DELETE' ? 'dari' : 'di';
        $items = $action === 'UPDATE'
            ? $this->changeItems($oldData, $newData)
            : $this->attributeItems($action === 'DELETE' ? $oldData : $newData);

        array_unshift($items, [
            'label' => 'Master Folder',
            'value' => $folder,
            'url' => $context['folder_link'] ?? null,
        ]);

        return [
            'summary' => "{$verb} file {$file} {$connector} folder {$folder}:",
            'items' => $items,
        ];
    }

    private function formatMasterFolder(string $action, array $context, array $oldData, array $newData): array
    {
        $folder = $context['folder_name'] ?? 'Master Folder';
        $verb = match ($action) {
            'CREATE' => 'Membuat',
            'UPDATE' => 'Mengubah',
            'DELETE' => 'Menghapus',
        };
        $items = $action === 'UPDATE'
            ? $this->changeItems($oldData, $newData)
            : $this->attributeItems($action === 'DELETE' ? $oldData : $newData);

        return [
            'summary' => "{$verb} Master Folder {$folder}:",
            'items' => $items,
        ];
    }

    private function formatAccountMasterFile(string $action, array $context, array $oldData, array $newData): array
    {
        $file = $context['file_name'] ?? 'file';
        $owner = $context['owner_name'] ?? 'akun';
        $verb = match ($action) {
            'CREATE' => 'Menambahkan',
            'UPDATE' => 'Mengubah',
            'DELETE' => 'Menghapus',
        };
        $items = $action === 'UPDATE'
            ? $this->changeItems($oldData, $newData)
            : $this->attributeItems($action === 'DELETE' ? $oldData : $newData);

        array_unshift($items, [
            'label' => 'Folder akun',
            'value' => $owner,
            'url' => $context['folder_link'] ?? null,
        ]);

        return [
            'summary' => "{$verb} file {$file} pada folder akun {$owner}:",
            'items' => $items,
        ];
    }

    private function attributeItems(array $data): array
    {
        $items = [];

        foreach ($data as $field => $value) {
            $items[] = [
                'label' => $this->fieldLabel((string) $field),
                'value' => $this->displayFieldValue((string) $field, $value),
                'url' => $this->url($value),
            ];
        }

        return $items;
    }

    private function changeItems(array $oldData, array $newData): array
    {
        $items = [];

        foreach (array_unique([...array_keys($oldData), ...array_keys($newData)]) as $field) {
            $old = $oldData[$field] ?? null;
            $new = $newData[$field] ?? null;

            if ($old === $new) {
                continue;
            }

            $items[] = [
                'label' => $this->fieldLabel((string) $field),
                'old' => $this->displayFieldValue((string) $field, $old),
                'new' => $this->displayFieldValue((string) $field, $new),
                'old_url' => $this->url($old),
                'new_url' => $this->url($new),
            ];
        }

        return $items;
    }

    private function filterData(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        return array_filter(
            $data,
            fn (string|int $field): bool => ! in_array(strtolower((string) $field), self::IGNORED_FIELDS, true)
                && ! str_ends_with(strtolower((string) $field), '_id'),
            ARRAY_FILTER_USE_KEY,
        );
    }

    private function displayFieldValue(string $field, mixed $value): string
    {
        if ($this->formattingTask) {
            $resolved = $this->taskFieldValue($field, $value);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        if ($field === 'size' && is_numeric($value)) {
            $bytes = (float) $value;

            if ($bytes >= 1048576) {
                return round($bytes / 1048576, 2).' MB';
            }

            if ($bytes >= 1024) {
                return round($bytes / 1024, 2).' KB';
            }

            return (int) $bytes.' byte';
        }

        return $this->displayValue($value);
    }

    private function fieldLabel(string $field): string
    {
        if (! $this->formattingTask) {
            return Str::headline($field);
        }

        return match ($field) {
            'pic' => 'PIC',
            'id_kpi' => 'KPI',
            'priority' => 'Prioritas',
            'divisi' => 'Divisi',
            'project' => 'Project',
            default => Str::headline($field),
        };
    }

    private function taskFieldValue(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field) {
            'project' => $this->lookup('produk_client', 'nama', $value, 'Project'),
            'pic' => $this->lookupUser($value),
            'divisi' => $this->lookup('divisions', 'name', $value, 'Divisi'),
            'id_kpi' => $this->lookup('kpi', 'nama_kpi', $value, 'KPI'),
            'priority' => match ((int) $value) {
                1 => 'Rendah',
                2 => 'Sedang',
                3 => 'Tinggi',
                default => 'Prioritas '.$value,
            },
            'status' => match ((int) $value) {
                0 => 'Not Started',
                1 => 'In Progress',
                2 => 'Split',
                3 => 'Tunggu Approval',
                4 => 'Done',
                default => 'Status '.$value,
            },
            default => null,
        };
    }

    private function lookup(string $table, string $column, mixed $id, string $fallback): string
    {
        $key = "{$table}:{$id}";

        if (array_key_exists($key, $this->lookupCache)) {
            return $this->lookupCache[$key];
        }

        try {
            $name = DB::table($table)->where('id', $id)->value($column);
        } catch (Throwable) {
            $name = null;
        }

        return $this->lookupCache[$key] = $name ? (string) $name : "{$fallback} #{$id}";
    }

    private function lookupUser(mixed $id): string
    {
        $key = "users:{$id}";

        if (array_key_exists($key, $this->lookupCache)) {
            return $this->lookupCache[$key];
        }

        try {
            $user = DB::table('users')->select(['name', 'email'])->where('id', $id)->first();
        } catch (Throwable) {
            $user = null;
        }

        if (! $user) {
            return $this->lookupCache[$key] = "User #{$id}";
        }

        $label = $user->name ?: $user->email;

        return $this->lookupCache[$key] = $user->email && $user->email !== $label
            ? "{$label} ({$user->email})"
            : (string) $label;
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'kosong';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            if ($value === []) {
                return 'kosong';
            }

            if (array_is_list($value)) {
                return implode(', ', array_map(fn (mixed $item): string => $this->displayValue($item), $value));
            }

            return implode('; ', array_map(
                fn (string|int $key, mixed $item): string => Str::headline((string) $key).': '.$this->displayValue($item),
                array_keys($value),
                array_values($value),
            ));
        }

        return (string) $value;
    }

    private function url(mixed $value): ?string
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) ? $value : null;
    }
}
