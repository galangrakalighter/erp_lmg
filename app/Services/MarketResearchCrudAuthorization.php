<?php

namespace App\Services;

use App\Support\DigitalMarketingPermissions;
use App\Support\MarketResearchCrudDefinitions;
use App\Support\MarketingCrudPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

final class MarketResearchCrudAuthorization
{
    public function __construct(private readonly MarketingAccessService $access) {}
    public function assert(Authenticatable $user, int $clientId, string $permission): void { $this->access->assertClientAccess($user, $clientId); $role = DigitalMarketingPermissions::role($user); $allowed = (bool) data_get($user,'isAdmin',false) || DigitalMarketingPermissions::isManager($role) || ($permission !== MarketingCrudPermissions::MARKET_RESEARCH_ARCHIVE && $role === 'Digital Marketing'); abort_unless($allowed, 403, 'Anda tidak memiliki permission market research.'); }
    public function object(Authenticatable $user, string $entity, int $id, string $permission): object { $def = MarketResearchCrudDefinitions::get($entity); $row = DB::table($def['table'])->where('id',$id)->first(['id','produk_client_id','archived_at']); abort_unless($row,404,'Record market research tidak ditemukan.'); $this->assert($user,(int)$row->produk_client_id,$permission); return $row; }
}
