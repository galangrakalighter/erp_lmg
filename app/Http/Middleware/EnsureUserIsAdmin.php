<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((bool) $request->user()?->isAdmin, 403, 'Halaman ini hanya dapat dilihat oleh admin.');

        return $next($request);
    }
}
