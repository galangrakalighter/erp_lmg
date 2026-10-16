<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class ContentPlanCrudAuthorization
{
    public function __construct(private readonly MarketingAccessService $access) {}

    public function assert(Authenticatable $user, int $clientId, string $permission): void
    {
        $this->access->assertClientAccess($user, $clientId);
        abort_unless(in_array($permission, MarketingCrudPermissions::all(), true), 403, 'Permission tidak valid.');
        abort_unless($this->hasPermission($user, $permission), 403, 'Anda tidak memiliki permission operasi ini.');
    }

    public function assertObject(Authenticatable $user, int $contentPlanId, string $permission): object
    {
        $plan = DB::table('data_jasa')->where('id', $contentPlanId)->first(['id', 'produk_client_id', 'archived_at']);
        abort_unless($plan, 404, 'Content plan tidak ditemukan.');
        $this->assert($user, (int) $plan->produk_client_id, $permission);
        return $plan;
    }

    private function hasPermission(Authenticatable $user, string $permission): bool
    {
        if ((bool) data_get($user, 'isAdmin', false)) return true;
        $role = DigitalMarketingPermissions::role($user);
        if ($permission === MarketingCrudPermissions::CONTENT_PLAN_CREATE) {
            return DigitalMarketingPermissions::isManager($role)
                || in_array($role, ['Content Creator', 'Sosial Media Spesialis', 'Advertising'], true);
        }
        if ($permission === MarketingCrudPermissions::CONTENT_PLAN_UPDATE) {
            return DigitalMarketingPermissions::isManager($role)
                || in_array($role, ['Content Creator', 'Sosial Media Spesialis', 'Advertising'], true);
        }
        return DigitalMarketingPermissions::isManager($role);
    }
}
