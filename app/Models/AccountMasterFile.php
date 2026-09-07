<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountMasterFile extends Model
{
    protected $fillable = [
        'owner_user_id', 'uploaded_by', 'original_name', 'stored_name', 'path',
        'mime_type', 'size', 'editor_content', 'is_generated_document',
    ];

    protected $casts = ['is_generated_document' => 'boolean'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
