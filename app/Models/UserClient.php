<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserClient extends Model
{
    protected $table = "user_client";
    protected $fillable = [
        'user_id',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(ProdukClient::class, 'client_id');
    }
}
