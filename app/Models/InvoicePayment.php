<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $table = "invoice_payments";

    protected $guarded = [];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
    
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
