<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureMultimediaClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->isAdmin) {
            return $next($request);
        }

        $multimediaId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');
        $clientId = (int) ($request->route('id') ?? $request->route('produk') ?? $request->route('produk_id'));

        $belongsToMultimedia = $multimediaId && DB::table('user_division')
            ->where('user_id', $user->id)
            ->where('division_id', $multimediaId)
            ->exists();
        $hasClientAccess = DB::table('user_client')
            ->where('user_id', $user->id)
            ->where('client_id', $clientId)
            ->exists();
        $isMultimediaClient = $multimediaId && DB::table('client_division')
            ->where('client_id', $clientId)
            ->where('division_id', $multimediaId)
            ->where('status', 1)
            ->exists();

        abort_unless($belongsToMultimedia && $hasClientAccess && $isMultimediaClient, 403);

        return $next($request);
    }
}
