<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdjustmentJournalDetail extends Model
{
    protected $table = "adjustment_journal_details";

    protected $fillable = [
        'adjustment_journal_id',
        'chart_of_account_id',
        'keterangan_item',
        'debit',
        'kredit'
    ];

    public function account()
    {
        // Menghubungkan baris detail ke data COA-nya untuk mengambil nama_akun & kode
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
