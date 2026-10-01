<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MultimediaWorkspaceController extends Controller
{
    public function show(int $id_produk)
    {
        $client = ProdukClient::query()->findOrFail($id_produk);
        abort_if($client->is_internal, 404);

        $multimediaId = DB::table('divisions')
            ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
            ->value('id');

        abort_unless($multimediaId && DB::table('client_division')
            ->where('client_id', $client->id)
            ->where('division_id', $multimediaId)
            ->where('status', 1)
            ->exists(), 404);

        $user = Auth::user();
        $user->loadMissing(['userDivisions.role', 'userClients']);

        $isPowerUser = (bool) $user->isAdmin;
        $belongsToMultimedia = $user->userDivisions->contains('division_id', $multimediaId);
        $hasClientAccess = $user->userClients->contains('client_id', $client->id);

        abort_unless($isPowerUser || ($belongsToMultimedia && $hasClientAccess), 403);

        $accessToken = is_array($client->access_token)
            ? $client->access_token
            : json_decode((string) $client->access_token, true);
        $platforms = collect($accessToken['platform'] ?? [])
            ->filter(fn ($platform) => is_string($platform) && trim($platform) !== '')
            ->map(fn ($platform) => trim($platform))
            ->unique()
            ->values();

        return view('digital_marketing.workspace', [
            'title' => $client->nama.' · Multimedia',
            'client' => $client,
            'platforms' => $platforms,
            'workspaceLabel' => 'Multimedia',
            'workspaceDescription' => 'Lihat riset, branding, dan analisis serta lengkapi link output Content Plan.',
            'workspaceApiNamespace' => 'api.multimedia',
            'workspaceDivisionId' => $multimediaId,
            'workspaceTabs' => ['market-research', 'branding', 'analisis', 'content-plan', 'dokumen', 'timeline'],
        ]);
    }
}
