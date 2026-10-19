<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class RequireMarketingMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        abort_unless(
            filled($request->bearerToken())
                && $token instanceof PersonalAccessToken
                && $token->can('marketing:read')
                && ! (bool) $request->user()?->is_locked,
            403,
            'Token tidak diizinkan mengakses analitik marketing.'
        );

        return $next($request);
    }
}
