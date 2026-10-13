<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BusinessApiAuthenticationTest extends TestCase
{
    public function test_every_api_route_requires_authentication(): void
    {
        $apiRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'));

        $this->assertNotEmpty($apiRoutes);

        foreach ($apiRoutes as $route) {
            $middleware = $route->gatherMiddleware();
            $requiresAuthentication = in_array('auth', $middleware, true)
                || in_array('auth:sanctum', $middleware, true);

            $this->assertTrue(
                $requiresAuthentication,
                sprintf('Route [%s] %s tidak dilindungi autentikasi.', implode('|', $route->methods()), $route->uri()),
            );
        }
    }

    public function test_session_based_business_api_routes_use_web_middleware(): void
    {
        $sessionRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/'))
            ->filter(fn ($route) => in_array('auth', $route->gatherMiddleware(), true));

        $this->assertNotEmpty($sessionRoutes);

        foreach ($sessionRoutes as $route) {
            $this->assertContains(
                'web',
                $route->gatherMiddleware(),
                sprintf('Route [%s] %s tidak menggunakan sesi web.', implode('|', $route->methods()), $route->uri()),
            );
        }
    }
}
