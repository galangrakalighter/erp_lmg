<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use App\Services\PlatformMirroringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\DigitalMarketingPermissions;

class DigitalMarketingWorkspaceController extends Controller
{
    public function show(int $id_produk, PlatformMirroringService $mirroring)
    {
        $client = ProdukClient::query()->findOrFail($id_produk);
        abort_if($client->is_internal, 404);
        $this->authorizeWorkspace($client);

        $accessToken = is_array($client->access_token)
            ? $client->access_token
            : json_decode((string) $client->access_token, true);
        $platforms = collect($accessToken['platform'] ?? [])
            ->filter(fn ($platform) => is_string($platform) && trim($platform) !== '')
            ->map(fn ($platform) => trim($platform))
            ->unique()
            ->values();
        $role = DigitalMarketingPermissions::role(Auth::user());
        $workspaceTabs = match ($role) {
            'Sosial Media Spesialis' => ['social-media-research', 'content-plan', 'guide'],
            'Advertising' => ['market-research', 'social-media-research', 'advertising-research', 'content-plan'],
            default => ['market-research', 'branding', 'social-media-research', 'advertising-research', 'content-plan', 'guide', 'analisis', 'explore', 'dokumen', 'timeline'],
        };

        return view('digital_marketing.workspace', [
            'title' => $client->nama.' · Digital Marketing',
            'client' => $client,
            'platforms' => $platforms,
            'workspaceLabel' => 'Digital Marketing',
            'workspaceDescription' => 'Kelola seluruh area marketing client tanpa berpindah halaman.',
            'workspaceApiNamespace' => 'api.digital-marketing',
            'workspaceDivisionId' => 1,
            'useMrCrepeNavigation' => true,
            'workspaceTabs' => $workspaceTabs,
            'platformMirrorPairs' => $mirroring->activePairs($client->id),
        ]);
    }

    public function updateMirroring(int $id_produk, Request $request, PlatformMirroringService $mirroring): JsonResponse
    {
        $client = ProdukClient::query()->findOrFail($id_produk);
        abort_if($client->is_internal, 404);
        $this->authorizeWorkspace($client);

        $platforms = collect(data_get($client->access_token, 'platform', []))
            ->filter(fn ($platform) => is_string($platform) && trim($platform) !== '')
            ->map(fn ($platform) => trim($platform))->unique()->values();

        $validated = $request->validate([
            'source' => ['required', 'string'],
            'targets' => ['array'],
            'targets.*' => ['string'],
        ]);
        $source = $platforms->first(fn ($platform) => strcasecmp($platform, $validated['source']) === 0);
        abort_unless($source, 422, 'Platform sumber tidak valid.');
        $targets = collect($validated['targets'] ?? [])
            ->map(fn ($target) => $platforms->first(fn ($platform) => strcasecmp($platform, $target) === 0))
            ->filter(fn ($target) => $target && strcasecmp($target, $source) !== 0)
            ->unique(fn ($target) => strtolower($target))->values();

        DB::transaction(function () use ($client, $source, $targets, $mirroring) {
            $existingPairs = DB::table('client_platform_mirrors')
                ->where('client_id', $client->id)
                ->where('active', true)
                ->where(fn ($query) => $query->where('platform_a', $source)->orWhere('platform_b', $source))
                ->get(['platform_a', 'platform_b']);

            foreach ($existingPairs as $pair) {
                $otherPlatform = strcasecmp($pair->platform_a, $source) === 0
                    ? $pair->platform_b
                    : $pair->platform_a;
                $isStillEnabled = $targets->contains(
                    fn ($target) => strcasecmp($target, $otherPlatform) === 0
                );
                if (! $isStillEnabled) {
                    $mirroring->snapshotPair($client->id, $pair->platform_a, $pair->platform_b);
                }
            }

            DB::table('client_platform_mirrors')
                ->where('client_id', $client->id)
                ->where(fn ($query) => $query->where('platform_a', $source)->orWhere('platform_b', $source))
                ->update(['active' => false, 'updated_at' => now()]);

            foreach ($targets as $target) {
                [$platformA, $platformB] = strcasecmp($source, $target) <= 0
                    ? [$source, $target]
                    : [$target, $source];
                DB::table('client_platform_mirrors')->updateOrInsert(
                    ['client_id' => $client->id, 'platform_a' => $platformA, 'platform_b' => $platformB],
                    ['active' => true, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        });

        return response()->json(['message' => 'Pengaturan mirroring berhasil disimpan.']);
    }

    private function authorizeWorkspace(ProdukClient $client): void
    {
        abort_unless(DB::table('client_division')->where('client_id', $client->id)
            ->where('division_id', 1)->where('status', 1)->exists(), 404);

        $user = Auth::user();
        $user->loadMissing(['userDivisions.division', 'userDivisions.role', 'userClients']);
        $allowed = (bool) $user->isAdmin
            || $user->userDivisions->contains(fn ($row) => $row->role?->role_name === 'Performance & Reporting')
            || $user->userDivisions->contains(fn ($row) => $row->division?->name === 'Ecommerce')
            || $user->userDivisions->contains(fn ($row) => (int) $row->division_id === 1 && $row->role?->role_name === 'Busines Unit Head')
            || ($user->userDivisions->contains('division_id', 1) && $user->userClients->contains('client_id', $client->id));
        abort_unless($allowed, 403);
    }
}
