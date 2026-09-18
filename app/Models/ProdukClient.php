<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\KategoriJasa;
use Illuminate\Database\Eloquent\Builder;
class ProdukClient extends Model
{
    protected $table = "produk_client";

    protected $fillable = [
        'kategori_jasa_id',
        'nama',
        'status',
        'access_token'
    ];

    protected $casts = [
        'access_token' => 'array',
        'is_internal' => 'boolean',
    ];

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereRaw("LOWER(TRIM(produk_client.nama)) = 'internal'")
                ->orWhereIn('produk_client.kategori_jasa_id', function ($subquery) {
                    $subquery->select('id')->from('kategori_jasa')
                        ->whereRaw("LOWER(TRIM(category)) LIKE 'internal%'");
                });
        });
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->where(function (Builder $builder) {
            $builder->whereRaw("LOWER(TRIM(produk_client.nama)) <> 'internal'")
                ->whereNotIn('produk_client.kategori_jasa_id', function ($subquery) {
                    $subquery->select('id')->from('kategori_jasa')
                        ->whereRaw("LOWER(TRIM(category)) LIKE 'internal%'");
                })
                ->orWhere(function (Builder $nullCategory) {
                    $nullCategory->whereRaw("LOWER(TRIM(produk_client.nama)) <> 'internal'")
                        ->whereNull('produk_client.kategori_jasa_id');
                });
        });
    }

    public function getIsInternalAttribute(): bool
    {
        if (strtolower(trim((string) $this->nama)) === 'internal') {
            return true;
        }

        $category = $this->relationLoaded('group') ? $this->group?->category : KategoriJasa::whereKey($this->kategori_jasa_id)->value('category');
        return str_starts_with(strtolower(trim((string) $category)), 'internal');
    }

    public function group(){
        return $this->belongsTo(KategoriJasa::class, 'kategori_jasa_id');
    }

    public function teams() {
        return $this->belongsToMany(User::class, 'user_client', 'client_id', 'user_id');
    }

    public function divisions() {
        return $this->belongsToMany(Division::class, 'client_division', 'client_id', 'division_id');
    }

    public function kpis()
    {
        return $this->hasMany(KPI::class, 'project', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'id');
    }
}
