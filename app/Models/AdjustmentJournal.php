<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdjustmentJournal extends Model
{
    protected $table = "adjustment_journals";

    protected $fillable = [
        'no_jurnal',
        'tgl_transaksi',
        'keterangan',
        'create_by'
    ];

    public function details()
    {
        return $this->hasMany(AdjustmentJournalDetail::class, 'adjustment_journal_id');
    }

    public function getIsLockedAttribute()
    {
        $periode = Carbon::parse($this->tgl_transaksi)->format('Y-m');
        
        return DB::table('period_locks')
            ->where('periode', $periode)
            ->where('is_locked', true)
            ->exists();
    }
}
