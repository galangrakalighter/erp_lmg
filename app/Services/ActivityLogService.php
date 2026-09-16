<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

    private bool $crudRecorded = false;

    private bool $activityRecorded = false;

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
        $this->activityRecorded = true;

        if (in_array(strtoupper((string) ($activity['action'] ?? '')), ['CREATE', 'UPDATE', 'DELETE'], true)) {
            $this->crudRecorded = true;
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

    public function hasRecordedCrud(): bool
    {
        return $this->crudRecorded;
    }

    public function hasRecordedActivity(): bool
    {
        return $this->activityRecorded;
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
        try {
            $context = $this->modelContext($model);
        } catch (Throwable $exception) {
            $context = [];
            Log::debug('Konteks activity log tidak dapat dilengkapi.', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }
        $oldData = $event === 'updated'
            ? Arr::only($model->getRawOriginal(), array_keys($model->getChanges()))
            : ($event === 'deleted' ? $model->getRawOriginal() : []);
        $newData = $event === 'deleted'
            ? []
            : ($event === 'created' ? $model->getAttributes() : $model->getChanges());

        $this->record([
            'action' => $action,
            'module' => $model->getTable(),
            'description' => $this->modelDescription($action, $targetType, $targetName, $context),
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

        try {
            $relatedDivisionIds = method_exists($user, 'userDivisions')
                ? ($user->relationLoaded('userDivisions')
                    ? $user->getRelation('userDivisions')->pluck('division_id')->all()
                    : $user->userDivisions()->pluck('division_id')->all())
                : [];
            $divisionIds = array_merge($divisionIds, $relatedDivisionIds);
        } catch (Throwable) {
            // Activity logging must never interrupt the user's primary action.
        }

        $divisionIds = array_values(array_unique(array_filter(
            $divisionIds,
            fn (mixed $id): bool => $id !== null && $id !== '',
        )));

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
        foreach (['name', 'nama', 'nama_file', 'nama_task', 'nama_kpi', 'nama_akun', 'nama_perusahaan', 'category', 'title', 'judul', 'original_name', 'filename', 'client_name', 'email'] as $field) {
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

        if ($model instanceof \App\Models\FilePdf) {
            $client = \App\Models\ProdukClient::query()->find($model->client);

            return [
                'kind' => 'client_file',
                'client_id' => $model->client,
                'client_name' => $client?->nama ?? 'Client #'.$model->client,
                'file_name' => $model->nama_file,
                'client_link' => url('/digital-marketing/client/'.$model->client.'?tab=dokumen'),
                'division_names' => $this->clientDivisionNames((int) $model->client),
            ];
        }

        $context = ['kind' => 'model'];
        $clientId = $this->numericAttribute($model, ['client_id', 'produk_client_id', 'id_produk', 'project']);

        if ($model instanceof \App\Models\ProdukClient) {
            $clientId = (int) $model->getKey();
        }

        if ($clientId) {
            $clientName = $model instanceof \App\Models\ProdukClient
                ? $model->nama
                : DB::table('produk_client')->where('id', $clientId)->value('nama');
            $context['client_id'] = $clientId;
            $context['client_name'] = $clientName ?: 'Client #'.$clientId;
            $context['client_link'] = url('/digital-marketing/client/'.$clientId);
            $context['division_names'] = $this->clientDivisionNames($clientId);
        }

        $divisionId = $this->numericAttribute($model, ['division_id', 'divisi']);
        if ($divisionId) {
            $context['division_id'] = $divisionId;
            $context['division_name'] = DB::table('divisions')->where('id', $divisionId)->value('name')
                ?: 'Divisi #'.$divisionId;
        }

        return count($context) > 1 ? $context : [];
    }

    private function numericAttribute(Model $model, array $fields): ?int
    {
        foreach ($fields as $field) {
            $value = $model->getAttribute($field);
            if (is_numeric($value) && (int) $value > 0) {
                return (int) $value;
            }
        }

        return null;
    }

    private function clientDivisionNames(int $clientId): array
    {
        try {
            return DB::table('client_division')
                ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
                ->where('client_division.client_id', $clientId)
                ->orderBy('divisions.name')
                ->pluck('divisions.name')
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function modelDescription(string $action, string $type, ?string $name, array $context): string
    {
        $verb = match ($action) {
            'CREATE' => 'Membuat',
            'UPDATE' => 'Mengubah',
            'DELETE' => 'Menghapus',
            default => 'Memproses',
        };

        if (($context['kind'] ?? null) === 'master_folder_file') {
            return "{$verb} file ".($context['file_name'] ?? $name).' di folder '.($context['folder_name'] ?? 'Master Folder');
        }

        if (($context['kind'] ?? null) === 'client_file') {
            return "{$verb} file ".($context['file_name'] ?? $name).' di client '.($context['client_name'] ?? 'Client');
        }

        $description = trim($verb.' '.Str::headline($type).($name ? ' '.$name : ''));
        if (! empty($context['client_name'])) {
            $description .= ' pada client '.$context['client_name'];
        }
        if (! empty($context['division_name'])) {
            $description .= ' di divisi '.$context['division_name'];
        } elseif (! empty($context['division_names'])) {
            $description .= ' di divisi '.implode(', ', $context['division_names']);
        }

        return $description;
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
