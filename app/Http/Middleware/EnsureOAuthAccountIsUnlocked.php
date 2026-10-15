<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOAuthAccountIsUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_locked) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Akun Anda sedang dikunci. Hubungi administrator.',
        ], 423);
    }
}
