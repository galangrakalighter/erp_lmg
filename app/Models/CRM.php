<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CRM extends Model
{
    protected $connection = 'pgsql_supabase';

    protected $table = 'crm_baru';

    protected $fillable = [
        'session_id',
        'nama',
        'nama_usaha',
        'email',
        'sosial_media',
        'website',
        'budget',
        'kategori',
        'hot_prospek',
        'meeting',
        'created_at',
        'chat_terbaru',
        'phone',
        'layanan',
        'kendala',
        'kebutuhan',
        'kesimpulan',
        'status',
        'link_meet',
    ];

    protected $casts = [
        'chat_terbaru' => 'datetime',
        'meeting' => 'datetime',
        'created_at' => 'datetime',
        'hot_prospek' => 'boolean',
    ];

    /**
     * Data yang ditangani AI.
     */
    public function scopeAi(Builder $query): Builder
    {
        return $query->whereNotNull('session_id');
    }
}