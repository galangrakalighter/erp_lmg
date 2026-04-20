<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = "task";
    public $timestamps = false;
    protected $fillable = [
        'nama_task',
        'divisi',
        'pic',
        'priority',
        'menit',
        'id_manajement_konten',
        'tanggal_mulai',
        'tanggal_deadline',
        'note',
        'status',
        'project',
        'jam_mulai',
        'tanggal_approve',
        'tanggal_revisi',
        'tanggal_split',
        'jira'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'pic');
    }
}
