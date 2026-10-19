<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilePdf extends Model
{
    protected $table = 'file_pdf';

    protected $fillable = [
        'nama_file',
        'client',
        'division_id',
        'uploaded_by',
        'location',
        'editor_content',
        'is_generated_document',
        'is_system_template',
        'is_pinned',
        'pin_order',
    ];

    protected $casts = [
        'is_generated_document' => 'boolean',
        'is_system_template' => 'boolean',
        'is_pinned' => 'boolean',
        'pin_order' => 'integer',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function versions()
    {
        return $this->hasMany(FilePdfVersion::class)->orderByDesc('version_number');
    }
}
