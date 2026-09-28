<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectMultimediaSharedModules
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin && ! $request->isMethodSafe()) {
            $isMultimediaUser = $user->userDivisions()
                ->whereHas('division', fn ($query) => $query->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia']))
                ->exists();
            $isReadOnlyModule = $request->is('cms_market/*') || $request->is('cms_branding/*');

            abort_if($isMultimediaUser && $isReadOnlyModule, 403, 'Modul ini hanya dapat dilihat oleh divisi Multimedia.');
        }

        return $next($request);
    }
}
