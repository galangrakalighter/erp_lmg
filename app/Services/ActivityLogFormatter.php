<?php

namespace App\Services;

use Illuminate\Support\Str;

class ActivityLogFormatter
{
    private const CRUD_ACTIONS = ['CREATE', 'UPDATE', 'DELETE'];

    private const IGNORED_FIELDS = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

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

    private function attributeItems(array $data): array
    {
        $items = [];

        foreach ($data as $field => $value) {
            $items[] = [
                'label' => Str::headline((string) $field),
                'value' => $this->displayValue($value),
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
                'label' => Str::headline((string) $field),
                'old' => $this->displayValue($old),
                'new' => $this->displayValue($new),
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
            fn (string|int $field): bool => ! in_array(strtolower((string) $field), self::IGNORED_FIELDS, true),
            ARRAY_FILTER_USE_KEY,
        );
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
