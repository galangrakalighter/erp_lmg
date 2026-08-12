<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CRM extends Model
{
    protected $connection = 'pgsql_supabase';
    protected $table = "crm_baru";
    protected $fillable = [
        "session_id",
        "nama",
        "nama_usaha",
        "email",
        "sosial_media",
        "website",
        "budget",
        "kategori",
        "hot_prospek",
        "meeting",
        "created_at",
        "chat_terbaru",
        "phone",
        "layanan",
        "status",
        "link_meet",
    ];
    protected $casts = [
        'chat_terbaru' => 'datetime',
        'meeting' => 'datetime', // 'meeting' sekarang otomatis jadi objek Carbon
        'created_at' => 'datetime',
    ];
}
