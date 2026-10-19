<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Swot extends Model
{
    protected $table = "analize_swot";
    protected $fillable = [
        "produk_client_id",
        "strenght",
        "weakness",
        "oportunity",
        "threat",
        "platform"
    ];
}
