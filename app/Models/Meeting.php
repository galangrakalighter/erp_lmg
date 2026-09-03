<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    protected $connection = 'pgsql_supabase';

    protected $table = 'meeting';

    public $timestamps = false;

    protected $fillable = [
        'id_crm_human',
        'waktu_meeting',
        'link_meeting',
    ];

    protected $casts = [
        'waktu_meeting' => 'datetime',
    ];

    public function crmHuman(): BelongsTo
    {
        return $this->belongsTo(
            CRM_HUMAN::class,
            'id_crm_human',
            'id'
        );
    }
}
