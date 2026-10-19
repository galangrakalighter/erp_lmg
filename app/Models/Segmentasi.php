<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segmentasi extends Model
{
    protected $table = "segmentasi";
    protected $fillable = [
        "produk_client_id",
        "usia",
        "gender",
        "negara",
        "wilayah",
        "gaya_hidup",
        "status_sosial",
        "minat",
        "penggunaan",
        "loyalitas",
        "manfaat",
        "platform",
    ];
}
