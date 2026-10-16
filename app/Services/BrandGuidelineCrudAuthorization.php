<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class BrandGuidelineCrudAuthorization
{
    public function __construct(private readonly MarketingAccessService $access) {}

    public function assert(Authenticatable $user, int $clientId, string $permission): void
    {
        $this->access->assertClientAccess($user, $clientId);
        abort_unless(in_array($permission, [MarketingCrudPermissions::BRAND_GUIDELINE_CREATE, MarketingCrudPermissions::BRAND_GUIDELINE_UPDATE, MarketingCrudPermissions::BRAND_GUIDELINE_ARCHIVE], true), 403, 'Permission tidak valid.');
        $role = DigitalMarketingPermissions::role($user);
        $allowed = (bool) data_get($user, 'isAdmin', false)
            || DigitalMarketingPermissions::isManager($role)
            || ($permission !== MarketingCrudPermissions::BRAND_GUIDELINE_ARCHIVE && $role === 'Digital Marketing');
        abort_unless($allowed, 403, 'Anda tidak memiliki permission operasi branding ini.');
    }

    public function assertObject(Authenticatable $user, int $id, string $permission): object
    {
        $record = DB::table('brand_identify')->where('id', $id)->first(['id', 'produk_client_id', 'archived_at']);
        abort_unless($record, 404, 'Brand guideline tidak ditemukan.');
        $this->assert($user, (int) $record->produk_client_id, $permission);
        return $record;
    }
}
