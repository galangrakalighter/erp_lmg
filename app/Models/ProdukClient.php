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

    public function divisions() {
        return $this->belongsToMany(Division::class, 'client_division', 'client_id', 'division_id');
    }
}
