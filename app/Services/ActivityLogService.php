<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogService
{
    private const HIDDEN_KEYS = [
        'password', 'password_confirmation', 'remember_token', 'token',
        'access_token', 'refresh_token', 'api_key', 'secret', 'private_key',
        'authorization',
    ];

    private array $pending = [];

    private bool $flushRegistered = false;

    public function __construct(
        private readonly FirestoreService $firestore,
        private readonly Request $request,
    ) {}

    public function record(array $activity, ?Authenticatable $user = null): void
    {
        $user ??= Auth::user();

        if (! $user) {
            return;
        }

        $this->pending[] = array_merge($this->userContext($user), [
            'action' => strtoupper((string) ($activity['action'] ?? 'ACTIVITY')),
            'module' => (string) ($activity['module'] ?? 'application'),
            'description' => (string) ($activity['description'] ?? 'Aktivitas pengguna'),
            'target_id' => isset($activity['target_id']) ? (string) $activity['target_id'] : null,
            'target_name' => isset($activity['target_name']) ? (string) $activity['target_name'] : null,
            'target_type' => isset($activity['target_type']) ? (string) $activity['target_type'] : null,
            'context' => $this->sanitize($activity['context'] ?? []),
            'old_data' => $this->sanitize($activity['old_data'] ?? []),
            'new_data' => $this->sanitize($activity['new_data'] ?? []),
            'ip_address' => $this->request->ip(),
            'user_agent' => Str::limit((string) $this->request->userAgent(), 500, ''),
            'created_at' => now(),
        ]);

        $this->registerFlush();
    }

    public function recordModelEvent(string $eventName, Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $event = Str::before(Str::after($eventName, 'eloquent.'), ':');
        $action = match ($event) {
            'created' => 'CREATE',
            'updated' => 'UPDATE',
            'deleted' => 'DELETE',
            default => strtoupper($event),
        };
        $targetType = Str::snake(class_basename($model));
        $targetName = $this->targetName($model);
        $context = $this->modelContext($model);
        $oldData = $event === 'updated'
            ? Arr::only($model->getRawOriginal(), array_keys($model->getChanges()))
            : ($event === 'deleted' ? $model->getRawOriginal() : []);
        $newData = $event === 'deleted'
            ? []
            : ($event === 'created' ? $model->getAttributes() : $model->getChanges());

        $this->record([
            'action' => $action,
            'module' => $model->getTable(),
            'description' => $this->description($action, $targetType, $targetName),
            'target_id' => $model->getKey(),
            'target_name' => $targetName,
            'target_type' => $targetType,
            'context' => $context,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);
    }

    public function flush(): void
    {
        $activities = $this->pending;
        $this->pending = [];

        foreach ($activities as $activity) {
            try {
                $this->firestore->create(
                    (string) config('firestore.activity_log_collection', 'logs'),
                    $activity
                );
            } catch (Throwable $exception) {
                Log::warning('Gagal menyimpan activity log ke Firestore.', [
                    'error' => $exception->getMessage(),
                    'action' => $activity['action'] ?? null,
                    'module' => $activity['module'] ?? null,
                    'user_id' => $activity['user_id'] ?? null,
                ]);
            }
        }
    }

    private function registerFlush(): void
    {
        if ($this->flushRegistered) {
            return;
        }

        $this->flushRegistered = true;
        app()->terminating(fn () => $this->flush());
    }

    private function userContext(Authenticatable $user): array
    {
        $divisionIds = [];
        $legacyDivision = data_get($user, 'divisi');

        if (is_array($legacyDivision)) {
            $divisionIds = $legacyDivision;
        } elseif ($legacyDivision !== null && $legacyDivision !== '') {
            $decoded = is_string($legacyDivision) ? json_decode($legacyDivision, true) : null;
            $divisionIds = is_array($decoded) ? $decoded : [$legacyDivision];
        }

        return [
            'user_id' => (string) $user->getAuthIdentifier(),
            'email' => (string) data_get($user, 'email', ''),
            'user_name' => (string) data_get($user, 'name', ''),
            'role' => (string) data_get($user, 'role', ''),
            'divisi' => $divisionIds[0] ?? null,
            'division_ids' => array_values($divisionIds),
        ];
    }

    private function targetName(Model $model): ?string
    {
        foreach (['name', 'nama', 'nama_task', 'nama_kpi', 'title', 'judul', 'original_name', 'filename', 'client_name', 'email'] as $field) {
            $value = $model->getAttribute($field);

            if (is_scalar($value) && (string) $value !== '') {
                return Str::limit((string) $value, 250, '');
            }
        }

        return $model->getKey() !== null ? class_basename($model).' #'.$model->getKey() : null;
    }

    private function modelContext(Model $model): array
    {
        if ($model instanceof \App\Models\MasterFolderFile) {
            $folder = $model->folder()->first();

            return [
                'kind' => 'master_folder_file',
                'folder_id' => $model->master_folder_id,
                'folder_name' => $folder?->name ?? 'Folder #'.$model->master_folder_id,
                'file_name' => $model->original_name,
                'file_path' => $model->path,
                'folder_link' => url('/master-folder/'.$model->master_folder_id),
            ];
        }

        if ($model instanceof \App\Models\MasterFolder) {
            return [
                'kind' => 'master_folder',
                'folder_id' => $model->getKey(),
                'folder_name' => $model->name,
                'folder_link' => url('/master-folder/'.$model->getKey()),
            ];
        }

        if ($model instanceof \App\Models\AccountMasterFile) {
            $owner = $model->owner()->first();

            return [
                'kind' => 'account_master_file',
                'owner_id' => $model->owner_user_id,
                'owner_name' => $owner?->name ?? $owner?->email ?? 'Akun #'.$model->owner_user_id,
                'file_name' => $model->original_name,
                'file_path' => $model->path,
                'folder_link' => url('/file-master/'.$model->owner_user_id),
            ];
        }

        return [];
    }

    private function description(string $action, string $type, ?string $name): string
    {
        $verb = match ($action) {
            'CREATE' => 'Menambahkan',
            'UPDATE' => 'Mengubah',
            'DELETE' => 'Menghapus',
            default => 'Memproses',
        };

        return trim($verb.' '.str_replace('_', ' ', $type).($name ? ': '.$name : ''));
    }

    private function sanitize(mixed $value, ?string $key = null, int $depth = 0): mixed
    {
        if ($key !== null && $this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        if ($depth >= 5) {
            return '[TRUNCATED]';
        }

        if (is_array($value)) {
            $result = [];

            foreach (array_slice($value, 0, 100, true) as $childKey => $childValue) {
                $result[$childKey] = $this->sanitize($childValue, (string) $childKey, $depth + 1);
            }

            return $result;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        if (is_string($value)) {
            return Str::limit($value, 5000, '');
        }

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        return Str::limit((string) $value, 5000, '');
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::HIDDEN_KEYS as $hidden) {
            if ($normalized === $hidden || str_ends_with($normalized, '_'.$hidden)) {
                return true;
            }
        }

        return false;
    }
}
