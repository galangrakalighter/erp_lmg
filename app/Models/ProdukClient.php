<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KategoriJasa;
class ProdukClient extends Model
{
    protected $table = "produk_client";

    protected $fillable = [
        'kategori_jasa_id',
        'nama',
        'link_excel',
        'status',
        'access_token'
    ];

    protected $casts = [
        'access_token' => 'array',
    ];

    public function group(){
        return $this->belongsTo(KategoriJasa::class);
    }

    public function teams() {
        return $this->belongsToMany(User::class, 'user_client', 'client_id', 'user_id');
    }
}
