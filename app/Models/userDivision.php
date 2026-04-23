<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class userDivision extends Model
{
    protected $table = "user_division";
    protected $fillable = [
        'user_id',
        'division_id',
        'role_id',
        'parent_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relasi ke tabel divisions
    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }
}

