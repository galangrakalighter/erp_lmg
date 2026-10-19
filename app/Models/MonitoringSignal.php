<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringSignal extends Model
{
    protected $fillable = [
        'monitoring_session_id',
        'sender_user_id',
        'recipient_user_id',
        'type',
        'payload',
        'read_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'read_at' => 'datetime'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(MonitoringSession::class, 'monitoring_session_id');
    }
}
