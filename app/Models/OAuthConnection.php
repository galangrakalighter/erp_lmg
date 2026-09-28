<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OAuthConnection extends Model
{
    protected $fillable = [
        'client_id', 'connected_by', 'provider', 'access_token', 'token_type',
        'expires_at', 'provider_user_id', 'provider_user_name', 'scopes',
        'assets', 'selected_assets', 'last_synced_at',
    ];

    protected $hidden = ['access_token'];

    protected $casts = [
        'access_token' => 'encrypted',
        'scopes' => 'encrypted:array',
        'assets' => 'encrypted:array',
        'selected_assets' => 'encrypted:array',
        'expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(ProdukClient::class, 'client_id');
    }
}
