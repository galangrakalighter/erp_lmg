<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_locked) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Akun Anda sedang dikunci. Hubungi administrator.',
            ], 423);
        }

        return redirect('/')->withErrors([
            'email' => 'Akun Anda sedang dikunci. Hubungi administrator.',
        ]);
    }
}
