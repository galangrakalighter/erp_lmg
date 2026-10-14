<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\userDivision;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'isAdmin',
        'jabatan',
        'photo_profile',
        'phone',
        'is_locked',
        'monitoring_share_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'isAdmin' => 'boolean',
            'is_locked' => 'boolean',
            'monitoring_share_enabled' => 'boolean',
        ];
    }

    /** Batasi daftar pilihan akun ke akun yang masih aktif. */
    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('is_locked'), false);
    }

    public function userDivisions() {
        return $this->hasMany(userDivision::class, 'user_id', 'id');
    }

    public function userClients()
    {
        return $this->hasMany(UserClient::class, 'user_id');
    }

    public function clients() {
        return $this->belongsToMany(ProdukClient::class, 'user_client', 'user_id', 'client_id');
    }

    public function kpis()
    {
        return $this->belongsToMany(KPI::class, 'kpi_user', 'user_id', 'kpi_id');
    }

    public function accountMasterFiles()
    {
        return $this->hasMany(AccountMasterFile::class, 'owner_user_id');
    }

    public function monitoringSessions()
    {
        return $this->hasMany(MonitoringSession::class, 'target_user_id');
    }

    public function monitoringTargets()
    {
        return $this->belongsToMany(User::class, 'monitoring_accesses', 'viewer_user_id', 'target_user_id')->withTimestamps();
    }

    public function monitoringViewers()
    {
        return $this->belongsToMany(User::class, 'monitoring_accesses', 'target_user_id', 'viewer_user_id')->withTimestamps();
    }
}
