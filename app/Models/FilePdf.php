<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilePdf extends Model
{
    protected $table = 'file_pdf';

    protected $fillable = [
        'nama_file',
        'client',
        'location',
        'editor_content',
        'is_generated_document'
    ];

    protected $casts = ['is_generated_document' => 'boolean'];
}
