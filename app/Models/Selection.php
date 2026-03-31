<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Selection extends Model
{
    protected $table = "affiliator_gafi";
    public $timestamps = false;
    protected $fillable = [
        'id_eikyoo',
        'done_4_konten',
        'cb',
        'td_pd',
        'cek',
        'harga_co_real',
        'type',
        'gmv'
    ];
}
