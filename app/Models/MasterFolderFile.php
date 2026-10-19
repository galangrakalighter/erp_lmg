<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFolderFile extends Model
{
    protected $fillable = [
        'master_folder_id', 'uploaded_by', 'primary_role_id', 'original_name', 'stored_name',
        'path', 'mime_type', 'size', 'editor_content', 'is_generated_document',
        'is_system_template', 'is_pinned', 'pin_order',
    ];

    protected $casts = [
        'is_generated_document' => 'boolean',
        'is_system_template' => 'boolean',
        'is_pinned' => 'boolean',
        'pin_order' => 'integer',
    ];

    public function folder()
    {
        return $this->belongsTo(MasterFolder::class, 'master_folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function primaryRole()
    {
        return $this->belongsTo(Role::class, 'primary_role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'master_folder_file_role');
    }
}
