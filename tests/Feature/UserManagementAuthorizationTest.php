<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ProtectMultimediaSharedModules;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserManagementAuthorizationTest extends TestCase
{
    public function test_every_user_management_route_requires_admin_middleware(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->getName() ?? '', 'manajemen_user.'));

        $this->assertCount(8, $routes);

        foreach ($routes as $route) {
            $this->assertContains(EnsureUserIsAdmin::class, $route->gatherMiddleware());
        }
    }

    public function test_user_json_endpoint_requires_admin_middleware(): void
    {
        $route = Route::getRoutes()->getByName('users.json');

        $this->assertNotNull($route);
        $this->assertContains(EnsureUserIsAdmin::class, $route->gatherMiddleware());
    }

    public function test_non_admin_cannot_open_user_management(): void
    {
        $user = (new User)->forceFill([
            'id' => 999,
            'isAdmin' => false,
            'is_locked' => false,
        ]);

        $this->actingAs($user)
            ->get(route('manajemen_user.index'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_submit_admin_account_request(): void
    {
        $this->withoutMiddleware(ProtectMultimediaSharedModules::class);

        $user = (new User)->forceFill([
            'id' => 999,
            'isAdmin' => false,
            'is_locked' => false,
        ]);

        $this->actingAs($user)
            ->post(route('manajemen_user.store'), [
                'name' => 'Unauthorized Admin',
                'email' => 'unauthorized-admin@example.com',
                'password' => 'password',
                'divisi_id' => 'lmg',
                'jabatan' => 'ceo',
            ])
            ->assertForbidden();
    }
}
