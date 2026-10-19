<?php

namespace App\Http\Middleware;

use App\Support\DigitalMarketingPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectDigitalMarketingModules
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ($request->is('cms_market/*') || $request->is('cms_branding/*'))) {
            $role = DigitalMarketingPermissions::role($request->user());
            abort_unless(DigitalMarketingPermissions::isManager($role) || $role === 'Digital Marketing', 403);
        }

        return $next($request);
    }
}
