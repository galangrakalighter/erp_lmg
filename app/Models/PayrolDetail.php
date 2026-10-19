<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrolDetail extends Model
{
    protected $table = "payroll_detail";

    protected $guarded = [];

    public function payroll() {
        return $this->belongsTo(Payrol::class);
    }
}
