<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarketingAccessService
{
    private const DIVISION = 'digital marketing';

    private const DIVISION_WIDE_ROLES = [
        'Busines Unit Head',
        'Digital Marketing Manager',
    ];

    /**
     * Build the canonical client/project scope for a user identity.
     */
    public function accessibleClientQuery(Authenticatable $user): Builder
    {
        $query = DB::table('produk_client');

        $this->assertAccountIsActive($user);

        if ((bool) data_get($user, 'isAdmin', false)) {
            return $query;
        }

        $divisionId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', [self::DIVISION])
            ->value('id');

        if (! $divisionId) {
            return $this->deny($query);
        }

        $assignment = DB::table('user_division')
            ->join('roles', function ($join): void {
                $join->on('roles.id', '=', 'user_division.role_id')
                    ->on('roles.division_id', '=', 'user_division.division_id');
            })
            ->where('user_division.user_id', $user->getAuthIdentifier())
            ->where('user_division.division_id', $divisionId)
            ->select('roles.role_name')
            ->first();

        if (! $assignment) {
            return $this->deny($query);
        }

        $query->whereExists(function ($subquery) use ($divisionId): void {
            $subquery->selectRaw('1')
                ->from('client_division')
                ->whereColumn('client_division.client_id', 'produk_client.id')
                ->where('client_division.division_id', $divisionId)
                ->where('client_division.status', 1);
        });

        if (! in_array($assignment->role_name, self::DIVISION_WIDE_ROLES, true)) {
            $query->whereExists(function ($subquery) use ($user): void {
                $subquery->selectRaw('1')
                    ->from('user_client')
                    ->whereColumn('user_client.client_id', 'produk_client.id')
                    ->where('user_client.user_id', $user->getAuthIdentifier());
            });
        }

        return $query;
    }

    public function assertClientAccess(Authenticatable $user, int $clientId): void
    {
        abort_unless(
            $this->accessibleClientQuery($user)->where('produk_client.id', $clientId)->exists(),
            403,
            'Anda tidak memiliki akses ke client atau project ini.',
        );
    }

    public function divisionClientSummary(Authenticatable $user): array
    {
        $this->assertAccountIsActive($user);
        $division = DB::table('divisions')->whereRaw('LOWER(TRIM(name)) = ?', [self::DIVISION])->first(['id', 'name']);
        abort_unless($division, 404, 'Divisi Digital Marketing tidak ditemukan.');

        $assignment = (bool) data_get($user, 'isAdmin', false) ? null : $this->divisionAssignment($user, (int) $division->id);
        abort_unless((bool) data_get($user, 'isAdmin', false) || $assignment, 403, 'Anda tidak memiliki akses ke Divisi Digital Marketing.');

        $divisionClients = DB::table('produk_client')
            ->join('client_division', 'client_division.client_id', '=', 'produk_client.id')
            ->where('client_division.division_id', $division->id)
            ->where('client_division.status', 1)
            ->get(['produk_client.id', 'produk_client.status'])
            ->unique('id');
        $accessibleCount = $this->accessibleClientQuery($user)
            ->whereExists(function ($query) use ($division): void {
                $query->selectRaw('1')->from('client_division')
                    ->whereColumn('client_division.client_id', 'produk_client.id')
                    ->where('client_division.division_id', $division->id)
                    ->where('client_division.status', 1);
            })->distinct()->count('produk_client.id');
        $canViewTotals = (bool) data_get($user, 'isAdmin', false)
            || in_array($assignment?->role_name, self::DIVISION_WIDE_ROLES, true);

        return [
            'division_name' => (string) $division->name,
            'total_clients' => $canViewTotals ? $divisionClients->count() : null,
            'active_clients' => $canViewTotals ? $divisionClients->where('status', 1)->count() : null,
            'inactive_clients' => $canViewTotals ? $divisionClients->where('status', '!=', 1)->count() : null,
            'accessible_clients' => $accessibleCount,
            'scope' => $canViewTotals ? 'division' : 'user_access_only',
            'source' => 'produk_client_client_division_access_scope',
            'as_of' => Carbon::now('Asia/Jakarta')->toIso8601String(),
            ...($canViewTotals ? [] : ['access_note' => 'Akses terbatas: total seluruh Divisi Digital Marketing hanya tersedia untuk admin atau pimpinan yang memiliki akses tingkat divisi.']),
        ];
    }

    public function assertAccountIsActive(Authenticatable $user): void
    {
        abort_if(
            (bool) data_get($user, 'is_locked', false) || data_get($user, 'deleted_at') !== null,
            403,
            'Akun tidak aktif atau sedang dikunci.',
        );
    }

    private function deny(Builder $query): Builder
    {
        return $query->whereRaw('1 = 0');
    }

    private function divisionAssignment(Authenticatable $user, int $divisionId): ?object
    {
        return DB::table('user_division')
            ->join('roles', function ($join): void {
                $join->on('roles.id', '=', 'user_division.role_id')
                    ->on('roles.division_id', '=', 'user_division.division_id');
            })
            ->where('user_division.user_id', $user->getAuthIdentifier())
            ->where('user_division.division_id', $divisionId)
            ->select('roles.role_name')
            ->first();
    }
}
