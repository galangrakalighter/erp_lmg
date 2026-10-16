<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketingApprovalRecords;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class MarketingApprovalAuthorization
{
    public function __construct(private readonly MarketingAccessService $access) {}

    public function assertRecord(Authenticatable $user, int $clientId, string $recordType, int $recordId, string $permission): object
    {
        $record = MarketingApprovalRecords::find($recordType, $recordId);
        abort_unless($record, 404, 'Record Digital Marketing tidak ditemukan.');
        abort_if($record->archived_at !== null, 409, 'Record yang diarsipkan tidak dapat diproses untuk approval.');
        abort_unless((int) $record->client_id === $clientId, 403, 'client_id tidak sesuai dengan record approval.');
        $this->access->assertClientAccess($user, $clientId);
        abort_unless((bool) data_get($user, 'isAdmin', false) || DB::table('user_client')->where('user_id', $user->getAuthIdentifier())->where('client_id', $clientId)->exists(), 403, 'Anda tidak memiliki assignment langsung ke client ini.');

        $role = DigitalMarketingPermissions::role($user);
        $manager = DigitalMarketingPermissions::isManager($role);
        $allowed = (bool) data_get($user, 'isAdmin', false)
            || ($permission === MarketingCrudPermissions::APPROVAL_SUBMIT ? $role !== '' : $manager);
        abort_unless($allowed, 403, 'Anda tidak memiliki permission tindakan approval ini.');

        return $record;
    }
}
