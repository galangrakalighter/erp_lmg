<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KategoriJasa;
use Illuminate\Database\Eloquent\Builder;
class ProdukClient extends Model
{
    protected $table = "produk_client";

    protected $fillable = [
        'kategori_jasa_id',
        'nama',
        'status',
        'access_token'
    ];

    protected $casts = [
        'access_token' => 'array',
        'is_internal' => 'boolean',
    ];

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereRaw("LOWER(TRIM(produk_client.nama)) = 'internal'")
                ->orWhereIn('produk_client.kategori_jasa_id', function ($subquery) {
                    $subquery->select('id')->from('kategori_jasa')
                        ->whereRaw("LOWER(TRIM(category)) LIKE 'internal%'");
                });
        });
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereRaw("LOWER(TRIM(produk_client.nama)) <> 'internal'")
                ->whereNotIn('produk_client.kategori_jasa_id', function ($subquery) {
                    $subquery->select('id')->from('kategori_jasa')
                        ->whereRaw("LOWER(TRIM(category)) LIKE 'internal%'");
                })
                ->orWhere(function (Builder $nullCategory) {
                    $nullCategory->whereRaw("LOWER(TRIM(produk_client.nama)) <> 'internal'")
                        ->whereNull('produk_client.kategori_jasa_id');
                });
        });
    }

    /** Batasi user Advertising ke client yang ditugaskan kepadanya. */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user || $user->isAdmin) {
            return $query;
        }

        $isAdvertising = $user->userDivisions()
            ->whereHas('role', fn (Builder $role) => $role->where('role_name', 'Advertising'))
            ->exists();

        if (! $isAdvertising) {
            return $query;
        }

        return $query->where(function (Builder $visible) use ($user) {
            $visible->whereExists(function ($subquery) use ($user) {
                $subquery->selectRaw('1')
                    ->from('user_client')
                    ->whereColumn('user_client.client_id', 'produk_client.id')
                    ->where('user_client.user_id', $user->id);
            })->orWhereRaw("LOWER(TRIM(produk_client.nama)) = 'internal'");
        });
    }

    public function getIsInternalAttribute(): bool
    {
        if (strtolower(trim((string) $this->nama)) === 'internal') {
            return true;
        }

        $category = $this->relationLoaded('group') ? $this->group?->category : KategoriJasa::whereKey($this->kategori_jasa_id)->value('category');
        return str_starts_with(strtolower(trim((string) $category)), 'internal');
    }

    public function group(){
        return $this->belongsTo(KategoriJasa::class, 'kategori_jasa_id');
    }

    public function teams() {
        return $this->belongsToMany(User::class, 'user_client', 'client_id', 'user_id');
    }

    public function divisions() {
        return $this->belongsToMany(Division::class, 'client_division', 'client_id', 'division_id');
    }

    public function kpis()
    {
        return $this->hasMany(KPI::class, 'project', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'id');
    }

    public function oauthConnections()
    {
        return $this->hasMany(OAuthConnection::class, 'client_id');
    }

    public function platformAccessToken(string $platform): ?string
    {
        if (strtolower($platform) === 'threads') {
            $connection = $this->oauthConnections()->where('provider', 'threads')->first();
            if ($connection && filled($connection->access_token)) {
                return $connection->access_token;
            }
        }

        if (strtolower($platform) === 'tiktok') {
            $connection = $this->oauthConnections()->where('provider', 'tiktok')->first();
            if ($connection && filled($connection->access_token)) {
                return $connection->access_token;
            }
        }

        if (in_array(strtolower($platform), ['facebook', 'instagram'], true)) {
            $connection = $this->oauthConnections()->where('provider', 'meta')->first();

            if ($connection) {
                $assets = $connection->assets ?? [];
                $selectedAssets = $connection->selected_assets ?? [];
                $pages = collect($assets['pages'] ?? []);
                $page = strtolower($platform) === 'instagram'
                    ? $pages->first(fn ($item) => data_get($item, 'instagram_business_account.id') === ($selectedAssets['instagram_account_id'] ?? null))
                    : $pages->firstWhere('id', $selectedAssets['page_id'] ?? null);
                $page ??= $pages->first();

                // Insight organik Page dan Instagram menggunakan Page access token.
                // User token tetap menjadi cadangan untuk koneksi lama tanpa token Page.
                $oauthToken = $page['access_token'] ?? $connection->access_token;
                if (filled($oauthToken)) {
                    return $oauthToken;
                }
            }
        }

        // Kompatibilitas untuk client lama yang belum menggunakan OAuth.
        $legacy = $this->access_token;
        if (is_array($legacy) && isset($legacy['platform'], $legacy['access'])) {
            $index = array_search($platform, $legacy['platform'], true);
            if ($index !== false && filled($legacy['access'][$index] ?? null)) {
                return $legacy['access'][$index];
            }
        }

        return null;
    }

    public function platformAccountId(string $platform): ?string
    {
        if (strtolower($platform) === 'threads') {
            $connection = $this->oauthConnections()->where('provider', 'threads')->first();

            return $connection?->provider_user_id;
        }

        if (strtolower($platform) === 'tiktok') {
            return $this->oauthConnections()->where('provider', 'tiktok')->first()?->provider_user_id;
        }

        $connection = $this->oauthConnections()->where('provider', 'meta')->first();
        if (! $connection) {
            return null;
        }

        $assets = $connection->assets ?? [];
        $selectedAssets = $connection->selected_assets ?? [];
        $pages = collect($assets['pages'] ?? []);

        if (strtolower($platform) === 'instagram') {
            return $selectedAssets['instagram_account_id']
                ?? data_get($pages->first(fn ($item) => filled(data_get($item, 'instagram_business_account.id'))), 'instagram_business_account.id');
        }

        $page = $pages->firstWhere('id', $selectedAssets['page_id'] ?? null) ?? $pages->first();

        return $page['id'] ?? null;
    }

    /**
     * Daftar akun yang dapat dipilih pada Data Performance.
     * Token sengaja tidak disertakan agar aman dikirim ke view.
     */
    public function performanceAccounts(string $platform): array
    {
        $platform = strtolower(trim($platform));

        if (in_array($platform, ['facebook', 'instagram'], true)) {
            $connection = $this->oauthConnections()->where('provider', 'meta')->first();
            $pages = collect($connection ? ($connection->assets['pages'] ?? []) : []);
            $selected = $connection?->selected_assets ?? [];
            $selectionKey = $platform === 'instagram' ? 'instagram_account_ids' : 'page_ids';
            $legacySelectionKey = $platform === 'instagram' ? 'instagram_account_id' : 'page_id';
            $hasSelectionConfiguration = array_key_exists($selectionKey, $selected) || filled($selected[$legacySelectionKey] ?? null);
            $selectedIds = $platform === 'instagram'
                ? collect($selected['instagram_account_ids'] ?? [$selected['instagram_account_id'] ?? null])->filter()->map(fn ($id) => (string) $id)
                : collect($selected['page_ids'] ?? [$selected['page_id'] ?? null])->filter()->map(fn ($id) => (string) $id);

            return $pages->map(function (array $page) use ($platform, $selected) {
                if ($platform === 'instagram') {
                    $id = data_get($page, 'instagram_business_account.id');
                    if (! filled($id)) {
                        return null;
                    }

                    $label = data_get($page, 'instagram_business_account.username')
                        ?? data_get($page, 'instagram_business_account.name')
                        ?? ($page['name'] ?? 'Instagram');
                    $selectedId = $selected['instagram_account_id'] ?? null;
                } else {
                    $id = $page['id'] ?? null;
                    $label = $page['name'] ?? 'Facebook Page';
                    $selectedId = $selected['page_id'] ?? null;
                }

                return [
                    'id' => (string) $id,
                    'label' => (string) $label,
                    'selected' => (string) $id === (string) $selectedId,
                ];
            })->filter(fn ($account) => $account && (! $hasSelectionConfiguration || $selectedIds->contains((string) $account['id'])))->values()->all();
        }

        $id = $this->platformAccountId($platform);
        if (! filled($id)) {
            return [];
        }

        $connection = $this->oauthConnections()->where('provider', $platform)->first();

        return [[
            'id' => (string) $id,
            'label' => (string) ($connection?->provider_user_name ?: ucfirst($platform)),
            'selected' => true,
        ]];
    }

    /** @return array{id:string, access_token:string}|null */
    public function performanceAccountCredentials(string $platform, ?string $accountId = null): ?array
    {
        $platform = strtolower(trim($platform));

        if (in_array($platform, ['facebook', 'instagram'], true)) {
            $connection = $this->oauthConnections()->where('provider', 'meta')->first();
            if (! $connection) {
                return null;
            }

            $pages = collect($connection->assets['pages'] ?? []);
            $selected = $connection->selected_assets ?? [];
            $page = $platform === 'instagram'
                ? $pages->first(fn ($item) => (string) data_get($item, 'instagram_business_account.id') === (string) ($accountId ?: ($selected['instagram_account_id'] ?? '')))
                : $pages->first(fn ($item) => (string) ($item['id'] ?? '') === (string) ($accountId ?: ($selected['page_id'] ?? '')));
            $page ??= $platform === 'instagram'
                ? $pages->first(fn ($item) => filled(data_get($item, 'instagram_business_account.id')))
                : $pages->first();

            $id = $platform === 'instagram'
                ? data_get($page, 'instagram_business_account.id')
                : data_get($page, 'id');
            $token = data_get($page, 'access_token') ?: $connection->access_token;

            return filled($id) && filled($token)
                ? ['id' => (string) $id, 'access_token' => (string) $token]
                : null;
        }

        $id = $this->platformAccountId($platform);
        $token = $this->platformAccessToken($platform);

        return filled($id) && filled($token)
            ? ['id' => (string) $id, 'access_token' => (string) $token]
            : null;
    }

    public function metaAdsConnection(): ?array
    {
        $connection = $this->oauthConnections()->where('provider', 'meta')->first();
        if (! $connection || blank($connection->access_token)) {
            return null;
        }

        $selectedAssets = $connection->selected_assets ?? [];
        $assets = $connection->assets ?? [];
        $account = collect($assets['ad_accounts'] ?? [])->firstWhere('id', $selectedAssets['ad_account_id'] ?? null);

        if (! $account) {
            return null;
        }

        return [
            'id' => $account['id'] ?? null,
            'currency' => $account['currency'] ?? null,
            'access_token' => $connection->access_token,
            'scopes' => $connection->scopes ?? [],
        ];
    }
}
