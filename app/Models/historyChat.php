<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class historyChat extends Model
{
    protected $connection = 'pgsql_supabase';
    protected $table = "n8n_chat_histories";
    
    protected $fillable = [
        "session_id",
        "message",
        "created_at",
    ];

    protected $casts = [
        'message' => 'array', // Supaya format JSONB otomatis dibaca sebagai array/object PHP
        'created_at' => 'datetime',
    ];
}
