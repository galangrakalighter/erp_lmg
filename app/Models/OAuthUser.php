<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Passport identity for remote MCP OAuth requests.
 *
 * The regular User model intentionally keeps Sanctum so the existing local
 * token integration remains backwards compatible.
 */
class OAuthUser extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isAdmin' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }
}
