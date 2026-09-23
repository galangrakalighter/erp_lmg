<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DivisionClientWorkspaceController extends Controller
{
    private const DIVISIONS = [
        'mcn' => ['id' => 3, 'label' => 'MCN'],
        'tech' => ['id' => 5, 'label' => 'Tech'],
        'ecommerce' => ['id' => 7, 'label' => 'Ecommerce'],
    ];

    public function show(string $division, int $id_produk)
    {
        $config = self::DIVISIONS[$division] ?? null;
        abort_unless($config, 404);

        $client = ProdukClient::query()->findOrFail($id_produk);
        abort_if($client->is_internal, 404);
        abort_unless(DB::table('client_division')
            ->where('client_id', $client->id)
            ->where('division_id', $config['id'])
            ->where('status', 1)
            ->exists(), 404);

        $user = Auth::user();
        $user->loadMissing(['userDivisions.role', 'userClients']);
        $isPowerUser = (bool) $user->isAdmin || $user->userDivisions->contains(
            fn ($row) => in_array($row->role?->role_name, ['Busines Unit Head', 'Performance & Reporting'], true)
        );
        $belongsToDivision = $user->userDivisions->contains('division_id', $config['id']);
        $hasClientAccess = $user->userClients->contains('client_id', $client->id);
        abort_unless($isPowerUser || $belongsToDivision || $hasClientAccess, 403);

        $tabs = match ($division) {
            'tech' => [
                ['key' => 'overview', 'label' => 'Overview', 'message' => 'Halaman Overview Tech belum tersedia.'],
                ['key' => 'github', 'label' => 'GitHub', 'message' => 'Integrasi GitHub client belum tersedia.'],
                ['key' => 'monitoring', 'label' => 'Monitoring', 'message' => 'Halaman Monitoring client belum tersedia.'],
                ['key' => 'documents', 'label' => 'File Dokumen', 'url' => route('PDF', ['id_produk' => $client->id, 'division_id' => $config['id']])],
            ],
            default => [
                ['key' => 'documents', 'label' => 'File Dokumen', 'url' => route('PDF', ['id_produk' => $client->id, 'division_id' => $config['id']])],
            ],
        };

        return view('division_client.workspace', [
            'title' => $client->nama.' · '.$config['label'],
            'division' => $division,
            'divisionLabel' => $config['label'],
            'client' => $client,
            'tabs' => $tabs,
        ]);
    }
}
