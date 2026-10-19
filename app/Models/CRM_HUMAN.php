<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CRM_HUMAN extends Model
{
    protected $connection = 'pgsql_supabase';

    protected $table = 'crm_human';

    protected $fillable = [
        'id_proteksi',
        'name',
        'brand',
        'phone',
        'sosial_media',
        'kategori',
        'chanelling',
        'prospek',
        'can_push',
        'created_at',
        'tanggal_chat',
        'link_wa',
        'layanan',
        'price',
        'status',
        'note',
        'kebutuhan',
        'service_plan',
        'need_analysys',
        'done_meeting',
        'jumlah_follow_up',
        'today'
    ];

    protected $casts = [
        'tanggal_chat' => 'datetime',
        'created_at' => 'datetime',
        'can_push' => 'boolean',
        'service_plan' => 'boolean',
        'need_analysys' => 'boolean',
        'done_meeting' => 'boolean',
        'jumlah_follow_up' => 'integer',
        'price' => 'decimal:2',
        'today' => 'boolean',
        'rate_balasan' => 'string',
    ];

    public function meetings(): HasMany
    {
        return $this->hasMany(
            Meeting::class,
            'id_crm_human',
            'id'
        );
    }
}
