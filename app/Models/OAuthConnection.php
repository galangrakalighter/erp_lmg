<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
}
