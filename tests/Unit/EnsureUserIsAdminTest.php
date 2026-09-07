<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureUserIsAdminTest extends TestCase
{
    public function test_admin_can_continue(): void
    {
        $request = Request::create('/activity-logs');
        $request->setUserResolver(fn () => (new User)->forceFill(['isAdmin' => true]));

        $response = (new EnsureUserIsAdmin)->handle(
            $request,
            fn () => new Response('OK'),
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_non_admin_is_forbidden(): void
    {
        $request = Request::create('/activity-logs');
        $request->setUserResolver(fn () => (new User)->forceFill(['isAdmin' => false]));

        try {
            (new EnsureUserIsAdmin)->handle($request, fn () => new Response('OK'));
            $this->fail('Akun non-admin seharusnya ditolak.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }
}
