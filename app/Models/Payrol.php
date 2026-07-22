<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payrol extends Model
{
    protected $table = "payroll";
    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'payroll_date'     => 'date',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
