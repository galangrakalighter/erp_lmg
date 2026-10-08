<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectClientManagement
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('cms.produk', 'produk_create', 'produk_edit', 'produk_delete')) {
            $user = $request->user();
            abort_unless($user, 401);
            $user->loadMissing('userDivisions.role');
            $allowed = (bool) $user->isAdmin || $user->userDivisions->contains(
                fn ($assignment) => $assignment->role?->role_name === 'Busines Unit Head'
            );
            abort_unless($allowed, 403);
        }

        return $next($request);
    }
}
