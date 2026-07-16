<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KPI extends Model
{
    protected $table = "kpi";
    protected $fillable = [
        "nama_kpi",
        "divisi",
        "point",
        "tanggal_mulai",
        "tanggal_deadline",
        "note",
        "status",
        "project"
    ];

    public $timestamps = false;

    public function pics()
    {
        return $this->belongsToMany(User::class, 'kpi_user', 'kpi_id', 'user_id');
    }
    
    public function projectClient()
    {
        return $this->belongsTo(ProdukClient::class, 'project', 'id');
    }
}
