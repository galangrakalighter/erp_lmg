<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Social_Creator extends Model
{
    protected $table = "social_commerce_creator";
    public $timestamps = false;
    protected $fillable = [
        'user_tt',
        'url_tt',
        'fol_tt',
        'user_shopee',
        'url_shopee',
        'fol_shopee',
        'user_ig',
        'url_ig',
        'fol_ig',
        'category',
        'gmv_tt',
        'gmv_shopee',
        'provinsi',
        'domisili',
        'alamat',
        'gender',
        'contact',
        'detail',
        'reels',
        'vt',
        'mirroring',
        'kerkun',
        'story',
    ];

    protected $casts = [
        'kategory' => 'array',
    ];
}
