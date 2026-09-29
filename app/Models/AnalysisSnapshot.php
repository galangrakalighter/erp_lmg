<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisSnapshot extends Model
{
    protected $fillable = [
        'client_id', 'platform', 'source', 'snapshot_date', 'metrics', 'captured_at',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'captured_at' => 'datetime',
        'metrics' => 'array',
    ];
}
