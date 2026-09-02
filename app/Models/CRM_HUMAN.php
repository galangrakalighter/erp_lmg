<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CRM_HUMAN extends Model
{
    protected $connection = 'pgsql_supabase';

    protected $table = 'crm_human';

    protected $fillable = [
        'id_proteksi',
        'name',
        'brand',
        'sosial_media',
        'kategori',
        'chanelling',
        'prospek',
        'can_push',
        'created_at',
        'tanggal_chat',
        'link_wa',
        'layanan',
        'status',
        'note',
        'kebutuhan',
        'service_plan',
        'need_analysys',
        'done_meeting'
    ];

    protected $casts = [
        'tanggal_chat' => 'datetime',
        'created_at' => 'datetime',
        'can_push' => 'boolean',
        'service_plan' => 'boolean',
        'need_analysys' => 'boolean',
        'done_meeting' => 'boolean',
    ];
}