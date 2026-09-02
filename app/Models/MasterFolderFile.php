<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterFolderFile extends Model
{
    protected $fillable = [
        'master_folder_id', 'uploaded_by', 'original_name', 'stored_name',
        'path', 'mime_type', 'size', 'editor_content', 'is_generated_document',
    ];

    protected $casts = ['is_generated_document' => 'boolean'];

    public function folder()
    {
        return $this->belongsTo(MasterFolder::class, 'master_folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'master_folder_file_role');
    }
}
