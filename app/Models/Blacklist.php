<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    protected $table = "black_list";
    public $timestamps = false;
    protected $fillable = [
        'id_creator',
        'bulan',
        'job',
        'link_report',
        'keterangan',
        'note'
    ];
}
