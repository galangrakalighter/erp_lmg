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
