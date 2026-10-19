<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetAudience extends Model
{
    protected $table = "target_audience";
    protected $fillable = [
        "produk_client_id",
        "indikator",
        "usia",
        "gender",
        "negara",
        "wilayah",
        "pekerjaan",
        "gaya_hidup",
        "status_sosial",
        "penggunaan",
        "manfaat",
        "platform",
    ];
}
