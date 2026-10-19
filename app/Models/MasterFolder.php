<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFolder extends Model
{
    protected $fillable = ['division_id', 'created_by', 'name', 'description', 'is_personal'];

    protected $casts = [
        'is_personal' => 'boolean',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'master_folder_role');
    }

    public function files()
    {
        return $this->hasMany(MasterFolderFile::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
