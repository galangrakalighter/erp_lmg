<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $table = 'audit_trails';

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    // Jika ingin otomatis mengubah format kolom JSON dari database ke array PHP
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Relasi ke User (siapa yang melakukan aksi)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}