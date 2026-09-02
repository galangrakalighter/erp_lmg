<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "roles";
    protected $fillable = [
        'role_name',
        'level',
        'division_id'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
