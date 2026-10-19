<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = "invoices";

    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_date'     => 'date',
        'items' => 'array',
    ];

    public function payments() {
        return $this->hasMany(InvoicePayment::class);
    }

    // Helper untuk menghitung sisa tagihan
    public function getRemainingAttribute() {
        return $this->total_amount - $this->paid_amount;
    }

    public function customer() {
        return $this->belongsTo(ProdukClient::class, 'customer_id'); // Sesuaikan nama model Customer Anda
    }
}
