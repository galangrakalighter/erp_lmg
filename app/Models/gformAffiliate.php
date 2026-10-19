<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class gformAffiliate extends Model
{
    protected $table = "eikyoo";
    public $timestamps = false;
    protected $fillable = [
        'id_affiliator',
        'social_media',
        'wajib_follow',
        'surat_pernyataan',
        'project',
        'status'
    ];
}
