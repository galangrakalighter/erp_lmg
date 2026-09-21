<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilePdfVersion extends Model
{
    protected $fillable = [
        'file_pdf_id', 'version_number', 'nama_file', 'location',
        'editor_content', 'changed_by',
    ];

    protected $casts = ['version_number' => 'integer'];

    public function file()
    {
        return $this->belongsTo(FilePdf::class, 'file_pdf_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
