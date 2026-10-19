<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataCenterAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'provider',
        'account_name',
        'username',
        'email',
        'password',
        'recovery_email',
        'phone',
        'url',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'account_name' => 'encrypted',
            'username' => 'encrypted',
            'email' => 'encrypted',
            'password' => 'encrypted',
            'recovery_email' => 'encrypted',
            'phone' => 'encrypted',
            'url' => 'encrypted',
            'notes' => 'encrypted',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
