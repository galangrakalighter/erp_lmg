<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use App\Models\userDivision as UserDivision;
use App\Support\DashboardAccess;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    public static function selfOnlyRoles(): array
    {
        return [
            ['Advertising'],
            ['Sosial Media Spesialis'],
            ['Content Creator'],
            ['Digital Marketing'],
        ];
    }

    #[DataProvider('selfOnlyRoles')]
    public function test_leaf_roles_can_only_view_their_own_dashboard_data(string $roleName): void
    {
        $this->assertTrue(DashboardAccess::isSelfOnly($this->userWithRole($roleName)));
    }

    public function test_admin_is_not_limited_to_its_own_data(): void
    {
        $admin = new User(['isAdmin' => true]);
        $this->assertFalse(DashboardAccess::isSelfOnly($admin));
    }

    private function userWithRole(string $roleName): User
    {
        $role = new Role(['role_name' => $roleName]);
        $division = new UserDivision();
        $division->setRelation('role', $role);

        $user = new User(['isAdmin' => false]);
        $user->setRelation('userDivisions', new Collection([$division]));

        return $user;
    }
}
