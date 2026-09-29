<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class OAuthConnection extends Model
{
    protected $table = 'oauth_connections';

    protected $fillable = [
        'client_id', 'connected_by', 'provider', 'access_token', 'token_type',
        'expires_at', 'provider_user_id', 'provider_user_name', 'scopes',
        'assets', 'selected_assets', 'last_synced_at',
    ];

    protected $hidden = ['access_token'];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected function accessToken(): Attribute
    {
        return $this->encryptedAttribute();
    }

    protected function scopes(): Attribute
    {
        return $this->encryptedArrayAttribute();
    }

    protected function assets(): Attribute
    {
        return $this->encryptedArrayAttribute();
    }

    protected function selectedAssets(): Attribute
    {
        return $this->encryptedArrayAttribute();
    }

    private function encryptedAttribute(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->decryptSafely($value),
            set: fn ($value) => filled($value) ? Crypt::encryptString((string) $value) : null,
        );
    }

    private function encryptedArrayAttribute(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $decrypted = $this->decryptSafely($value);

                if (! is_string($decrypted)) {
                    return [];
                }

                $decoded = json_decode($decrypted, true);

                return is_array($decoded) ? $decoded : [];
            },
            set: fn ($value) => Crypt::encryptString(json_encode($value ?? [], JSON_THROW_ON_ERROR)),
        );
    }

    private function decryptSafely(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function client()
    {
        return $this->belongsTo(ProdukClient::class, 'client_id');
    }

    public function saveSelectedMetaAssets(array $selection): void
    {
        $selection = [
            'page_id' => filled($selection['page_id'] ?? null) ? (string) $selection['page_id'] : null,
            'instagram_account_id' => filled($selection['instagram_account_id'] ?? null) ? (string) $selection['instagram_account_id'] : null,
            'ad_account_id' => filled($selection['ad_account_id'] ?? null) ? (string) $selection['ad_account_id'] : null,
        ];

        $assets = $this->assets ?? [];
        $pages = collect($assets['pages'] ?? []);
        $instagramPages = $pages->filter(fn ($page) => filled(data_get($page, 'instagram_business_account.id')));

        if ($selection['page_id'] && ! $pages->contains('id', $selection['page_id'])) {
            throw ValidationException::withMessages(['page_id' => 'Facebook Page tidak tersedia pada koneksi ini.']);
        }
        if ($selection['instagram_account_id'] && ! $instagramPages->contains(
            fn ($page) => (string) data_get($page, 'instagram_business_account.id') === $selection['instagram_account_id']
        )) {
            throw ValidationException::withMessages(['instagram_account_id' => 'Instagram Page tidak tersedia pada koneksi ini.']);
        }

        if ($selection['ad_account_id']) {
            $adAccount = collect($assets['ad_accounts'] ?? [])->firstWhere('id', $selection['ad_account_id']);
            if (! $adAccount) {
                throw ValidationException::withMessages(['ad_account_id' => 'Akun iklan tidak tersedia pada koneksi ini.']);
            }

            $selectedPageIds = collect([$selection['page_id']])
                ->merge($instagramPages
                    ->filter(fn ($page) => (string) data_get($page, 'instagram_business_account.id') === $selection['instagram_account_id'])
                    ->pluck('id'))
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->unique();
            $promotablePageIds = collect($adAccount['page_ids'] ?? [])->map(fn ($id) => (string) $id);

            if ($selectedPageIds->isEmpty() || $promotablePageIds->intersect($selectedPageIds)->isEmpty()) {
                throw ValidationException::withMessages([
                    'ad_account_id' => 'Akun iklan tidak terkait dengan Facebook/Instagram Page yang dipilih. Hubungkan ulang Meta bila daftar akun belum diperbarui.',
                ]);
            }
        }

        $this->update(['selected_assets' => $selection]);
    }
}
