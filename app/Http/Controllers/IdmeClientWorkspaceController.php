<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Support\Facades\Auth;

class IdmeClientWorkspaceController extends Controller
{
    public function show(int $id_produk)
    {
        $user = Auth::user();
        // Jabatan bukan syarat akses. Admin dengan jabatan terisi maupun null
        // tetap dapat membuka IDME, sedangkan seluruh non-admin ditolak.
        abort_unless((bool) $user->isAdmin, 403);

        $client = ProdukClient::query()
            ->with(['divisions' => fn ($query) => $query
                ->whereRaw("LOWER(TRIM(divisions.name)) <> 'finance'")
                ->orderBy('name')])
            ->findOrFail($id_produk);
        abort_if($client->is_internal, 404);
        abort_unless($client->divisions->unique('id')->count() > 1, 404);

        $accessToken = is_array($client->access_token)
            ? $client->access_token
            : json_decode((string) $client->access_token, true);
        $platforms = collect($accessToken['platform'] ?? [])->filter()->unique()->values();

        $divisionTabs = $client->divisions->map(function ($division) use ($client, $platforms) {
            $normalized = strtolower(trim($division->name));
            $isDigitalMarketing = $normalized === 'digital marketing';
            $platform = $platforms->first();

            $documentTab = ['key' => 'documents', 'label' => 'File Dokumen', 'url' => route('PDF', ['id_produk' => $client->id, 'division_id' => $division->id]), 'platform' => false];
            $timelineTab = ['key' => 'timeline', 'label' => 'Timeline', 'url' => route('client.kpi-timeline', ['client' => $client->id]), 'platform' => false];
            $guideTab = ['key' => 'guide', 'label' => 'Guide', 'url' => route('client.content-guide', ['client' => $client->id]), 'platform' => false];

            $tabs = $isDigitalMarketing ? [
                ['key' => 'market-research', 'label' => 'Market Research', 'url' => $platform ? route('api.digital-marketing.market-research', [$client->id, $platform]) : null, 'platform' => true],
                ['key' => 'analisis', 'label' => 'Analisis', 'url' => $platform ? route('api.digital-marketing.analisis', [$client->id, $platform]) : null, 'platform' => true],
                ['key' => 'content-plan', 'label' => 'Content Plan', 'url' => $platform ? route('api.digital-marketing.content-plan', [$client->id, $platform]) : null, 'platform' => true],
                $documentTab,
                $guideTab,
                $timelineTab,
            ] : [$documentTab, $guideTab, $timelineTab];

            // Relasi Digital Marketing tetap valid walaupun platform belum
            // disetel. Tampilkan dokumen terlebih dahulu agar tidak terlihat
            // seolah-olah client tersebut tidak ada pada tab Digital Marketing.
            if ($isDigitalMarketing && ! $platform) {
                $tabs = [
                    $documentTab,
                    $guideTab,
                    $timelineTab,
                    [
                        'key' => 'platform-needed',
                        'label' => 'Informasi Client',
                        'url' => null,
                        'platform' => false,
                        'message' => 'Platform Digital Marketing belum disetel untuk client ini. File Dokumen tetap dapat digunakan.',
                    ],
                ];
            }

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
