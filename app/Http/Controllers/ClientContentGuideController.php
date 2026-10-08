<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientContentGuideController extends Controller
{
    public function index(int $client)
    {
        $clientModel = ProdukClient::query()->findOrFail($client);
        abort_if($clientModel->is_internal, 404);

        $sections = collect($this->sections())->map(function (array $section) use ($clientModel) {
            $section['items'] = DB::table($section['table'])
                ->where('kategori_id', $clientModel->kategori_jasa_id)
                ->select([
                    'id',
                    DB::raw($section['name'].' as name'),
                    $section['guidance'] ? 'fungsi_singkat' : DB::raw('NULL as fungsi_singkat'),
                    $section['guidance'] ? 'contoh' : DB::raw('NULL as contoh'),
                    'status',
                ])
                ->orderBy('id')
                ->get()
                ->unique(fn ($item) => mb_strtolower(trim((string) $item->name)))
                ->values();

            return $section;
        });

        $user = Auth::user();
        $user->loadMissing('userDivisions.role', 'userDivisions.division');
        $canManage = ! request()->boolean('readonly') && ((bool) $user->isAdmin || $user->userDivisions->contains(function ($assignment) {
            return strtolower(trim((string) $assignment->division?->name)) === 'digital marketing'
                && in_array($assignment->role?->role_name, ['Digital Marketer', 'Busines Unit Head'], true);
        }));

        return view('client.guide', [
            'title' => 'Content Guide · '.$clientModel->nama,
            'client' => $clientModel,
            'sections' => $sections,
            'canManage' => $canManage,
        ]);
    }

    public function store(Request $request, int $client, string $section)
    {
        $clientModel = ProdukClient::query()->findOrFail($client);
        $config = $this->section($section);
        $this->authorizeManage();
        $validated = $this->validated($request, $config);
        $validated['kategori_id'] = $clientModel->kategori_jasa_id;
        DB::table($config['table'])->insert($validated);

        return redirect()->back()->with('success', $config['label'].' berhasil ditambahkan.');
    }

    public function update(Request $request, int $client, string $section, int $item)
    {
        $clientModel = ProdukClient::query()->findOrFail($client);
        $config = $this->section($section);
        $this->authorizeManage();
        DB::table($config['table'])->where('id', $item)
            ->where('kategori_id', $clientModel->kategori_jasa_id)
            ->update($this->validated($request, $config));

        return redirect()->back()->with('success', $config['label'].' berhasil diperbarui.');
    }

    public function destroy(int $client, string $section, int $item)
    {
        $clientModel = ProdukClient::query()->findOrFail($client);
        $config = $this->section($section);
        $this->authorizeManage();
        DB::table($config['table'])->where('id', $item)
            ->where('kategori_id', $clientModel->kategori_jasa_id)->delete();

        return redirect()->back()->with('success', $config['label'].' berhasil dihapus.');
    }

    private function sections(): array
    {
        return [
            'strategy' => ['key' => 'strategy', 'label' => 'Strategi', 'table' => 'strategy', 'name' => 'strategy', 'guidance' => false],
            'status' => ['key' => 'status', 'label' => 'Status', 'table' => 'status', 'name' => 'nama_status', 'guidance' => false],
            'type' => ['key' => 'type', 'label' => 'Type', 'table' => 'type_produk', 'name' => 'type', 'guidance' => false],
            'pilar' => ['key' => 'pilar', 'label' => 'Pilar', 'table' => 'pilar', 'name' => 'pilar', 'guidance' => true],
            'hook' => ['key' => 'hook', 'label' => 'Hook', 'table' => 'hooks', 'name' => 'hooks', 'guidance' => true],
            'body' => ['key' => 'body', 'label' => 'Body', 'table' => 'jenis_body', 'name' => 'nama_body', 'guidance' => true],
            'cta' => ['key' => 'cta', 'label' => 'CTA', 'table' => 'jenis_cta', 'name' => 'nama_cta', 'guidance' => true],
            'composition' => ['key' => 'composition', 'label' => 'Komposisi', 'table' => 'content_compositions', 'name' => 'nama', 'guidance' => true],
        ];
    }

    private function section(string $key): array
    {
        abort_unless(isset($this->sections()[$key]), 404);
        return $this->sections()[$key];
    }

    private function validated(Request $request, array $config): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'integer', 'in:1,2'],
        ];
        if ($config['guidance']) {
            $rules['fungsi_singkat'] = ['nullable', 'string', 'max:1000'];
            $rules['contoh'] = ['nullable', 'string', 'max:3000'];
        }
        $validated = $request->validate($rules);
        $validated[$config['name']] = $validated['name'];
        unset($validated['name']);
        return $validated;
    }

    private function authorizeManage(): void
    {
        $user = Auth::user();
        $user->loadMissing('userDivisions.role', 'userDivisions.division');
        $allowed = (bool) $user->isAdmin || $user->userDivisions->contains(function ($assignment) {
            return strtolower(trim((string) $assignment->division?->name)) === 'digital marketing'
                && in_array($assignment->role?->role_name, ['Digital Marketer', 'Busines Unit Head'], true);
        });
        abort_unless($allowed, 403);
    }
}
