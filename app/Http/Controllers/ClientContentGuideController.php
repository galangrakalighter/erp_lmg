<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Support\Facades\DB;

class ClientContentGuideController extends Controller
{
    public function index(int $client)
    {
        $clientModel = ProdukClient::query()->findOrFail($client);
        abort_if($clientModel->is_internal, 404);

        $sections = collect([
            ['key' => 'pilar', 'label' => 'Pilar', 'table' => 'pilar', 'name' => 'pilar'],
            ['key' => 'hook', 'label' => 'Hook', 'table' => 'hooks', 'name' => 'hooks'],
            ['key' => 'body', 'label' => 'Body', 'table' => 'jenis_body', 'name' => 'nama_body'],
            ['key' => 'cta', 'label' => 'CTA', 'table' => 'jenis_cta', 'name' => 'nama_cta'],
        ])->map(function (array $section) {
            $section['items'] = DB::table($section['table'])
                ->where('status', 1)
                ->select([
                    'id',
                    DB::raw($section['name'].' as name'),
                    'fungsi_singkat',
                    'contoh',
                ])
                ->orderBy('id')
                ->get()
                ->unique(fn ($item) => mb_strtolower(trim((string) $item->name)))
                ->values();

            return $section;
        });

        return view('client.guide', [
            'title' => 'Content Guide · '.$clientModel->nama,
            'client' => $clientModel,
            'sections' => $sections,
        ]);
    }
}
