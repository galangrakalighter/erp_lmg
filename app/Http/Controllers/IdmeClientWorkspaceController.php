<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Support\Facades\Auth;

class IdmeClientWorkspaceController extends Controller
{
    public function show(int $id_produk)
    {
        $user = Auth::user();
        $user->loadMissing('userDivisions.division');
        abort_unless(
            (bool) $user->isAdmin
            || $user->userDivisions->contains(fn ($row) => strcasecmp((string) $row->division?->name, 'IDME') === 0),
            403
        );

        $client = ProdukClient::query()
            ->with(['divisions' => fn ($query) => $query->orderBy('name')])
            ->where('status', 1)
            ->findOrFail($id_produk);

        $accessToken = is_array($client->access_token)
            ? $client->access_token
            : json_decode((string) $client->access_token, true);
        $platforms = collect($accessToken['platform'] ?? [])->filter()->unique()->values();

        $divisionTabs = $client->divisions->map(function ($division) use ($client, $platforms) {
            $normalized = strtolower(trim($division->name));
            $isDigitalMarketing = $normalized === 'digital marketing';
            $platform = $platforms->first();

            $tabs = $isDigitalMarketing ? [
                ['key' => 'market-research', 'label' => 'Market Research', 'url' => $platform ? route('api.digital-marketing.market-research', [$client->id, $platform]) : null, 'platform' => true],
                ['key' => 'analisis', 'label' => 'Analisis', 'url' => $platform ? route('api.digital-marketing.analisis', [$client->id, $platform]) : null, 'platform' => true],
                ['key' => 'content-plan', 'label' => 'Content Plan', 'url' => $platform ? route('api.digital-marketing.content-plan', [$client->id, $platform]) : null, 'platform' => true],
                ['key' => 'documents', 'label' => 'File Dokumen', 'url' => route('PDF', $client->id), 'platform' => false],
            ] : [
                ['key' => 'documents', 'label' => 'File Dokumen', 'url' => route('PDF', $client->id), 'platform' => false],
            ];

            return [
                'id' => $division->id,
                'name' => $division->name,
                'is_digital_marketing' => $isDigitalMarketing,
                'tabs' => $tabs,
            ];
        })->values();

        return view('idme.workspace', [
            'title' => $client->nama.' · IDME',
            'client' => $client,
            'platforms' => $platforms,
            'divisionTabs' => $divisionTabs,
        ]);
    }
}
