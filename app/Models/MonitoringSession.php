<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringSession extends Model
{
    protected $fillable = [
        'target_user_id',
        'viewer_user_id',
        'status',
        'consented_at',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function viewerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_user_id');
    }

    public function signals(): HasMany
    {
        return $this->hasMany(MonitoringSignal::class);
    }
}
