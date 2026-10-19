<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDigitalMarketingClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 401);
        if ($user->isAdmin) return $next($request);

        $clientId = (int) ($request->route('id') ?? $request->route('produk') ?? $request->route('produk_id'));
        $divisionId = DB::table('divisions')->whereRaw('LOWER(TRIM(name)) = ?', ['digital marketing'])->value('id');
        $assignment = $divisionId ? $user->userDivisions()->with('role')->where('division_id', $divisionId)->first() : null;
        $isDivisionClient = $divisionId && DB::table('client_division')->where('client_id', $clientId)
            ->where('division_id', $divisionId)->where('status', 1)->exists();
        $hasClientAccess = $assignment?->role?->role_name === 'Busines Unit Head'
            || DB::table('user_client')->where('user_id', $user->id)->where('client_id', $clientId)->exists();
        $allowed = $assignment && $isDivisionClient && $hasClientAccess;
        abort_unless($allowed, 403);

        return $next($request);
    }
}
