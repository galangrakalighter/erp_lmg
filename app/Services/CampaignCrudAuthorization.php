<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class CampaignCrudAuthorization
{
    public function __construct(private readonly MarketingAccessService $access) {}

    public function assert(Authenticatable $user, int $clientId, string $permission): void
    {
        $this->access->assertClientAccess($user, $clientId);
        abort_unless((bool) data_get($user, 'isAdmin', false) || DB::table('user_client')->where('user_id', $user->getAuthIdentifier())->where('client_id', $clientId)->exists(), 403, 'Anda tidak memiliki assignment langsung ke client campaign ini.');
        abort_unless(in_array($permission, [MarketingCrudPermissions::CAMPAIGN_CREATE, MarketingCrudPermissions::CAMPAIGN_UPDATE, MarketingCrudPermissions::CAMPAIGN_ARCHIVE], true), 403, 'Permission campaign tidak valid.');
        abort_unless(DigitalMarketingPermissions::isManager(DigitalMarketingPermissions::role($user)), 403, 'Anda tidak memiliki permission operasi campaign ini.');
    }

    public function assertObject(Authenticatable $user, int $id, string $permission): object
    {
        $record = DB::table('marketing_campaigns')->where('id', $id)->first(['id', 'client_id', 'archived_at']);
        abort_unless($record, 404, 'Campaign tidak ditemukan.');
        $this->assert($user, (int) $record->client_id, $permission);

        return $record;
    }
}
