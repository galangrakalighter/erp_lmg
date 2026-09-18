<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DigitalMarketingWorkspaceController extends Controller
{
    public function show(int $id_produk)
    {
        $client = ProdukClient::query()->findOrFail($id_produk);
        abort_if($client->is_internal, 404);

        $isWorkspaceClient = DB::table('client_division')
            ->where('client_id', $client->id)
            ->where('division_id', 1)
            ->exists();

        abort_unless($isWorkspaceClient && (int) $client->status === 1, 404);

        $user = Auth::user();
        $user->loadMissing(['userDivisions.division', 'userDivisions.role', 'userClients']);

        $isPowerUser = (bool) $user->isAdmin
            || $user->userDivisions->contains(fn ($row) => $row->role?->role_name === 'Performance & Reporting');
        $isEcommerce = $user->userDivisions->contains(fn ($row) => $row->division?->name === 'Ecommerce');
        $isWorkspaceDivision = $user->userDivisions->contains('division_id', 1);
        $hasClientAccess = $user->userClients->contains('client_id', $client->id);

        abort_unless($isPowerUser || $isEcommerce || ($isWorkspaceDivision && $hasClientAccess), 403);

        $accessToken = is_array($client->access_token)
            ? $client->access_token
            : json_decode((string) $client->access_token, true);
        $platforms = collect($accessToken['platform'] ?? [])
            ->filter(fn ($platform) => is_string($platform) && trim($platform) !== '')
            ->map(fn ($platform) => trim($platform))
            ->unique()
            ->values();

        return view('digital_marketing.workspace', [
            'title' => $client->nama.' · Digital Marketing',
            'client' => $client,
            'platforms' => $platforms,
            'workspaceLabel' => 'Digital Marketing',
            'workspaceDescription' => 'Kelola seluruh area marketing client tanpa berpindah halaman.',
            'workspaceApiNamespace' => 'api.digital-marketing',
            'workspaceDivisionId' => 1,
        ]);
    }
}
