<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Positioning extends Model
{
    protected $table = "positioning";
    protected $fillable = [
        "produk_client_id",
        "indikator",
        "deskripsi",
        "platform",
    ];
}
