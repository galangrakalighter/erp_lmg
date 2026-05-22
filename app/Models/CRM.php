<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CRM extends Model
{
    protected $connection = 'pgsql_supabase';
    protected $table = "crm";
    protected $fillable = [
        "tanggal_chat",
        "name",
        "email",
        "id_proteksi",
        "project_stage",
        "phone",
        "layanan",
        "chat_ketiga",
        "project_value"
    ];
    protected $casts = [
        'tanggal_chat' => 'datetime',
    ];
}
