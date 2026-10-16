<?php

namespace App\Http\Controllers;

use App\Models\OAuthConnection;
use App\Models\ProdukClient;
use App\Services\MetaConnectionSyncService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MetaOAuthController extends Controller
{
    public function redirect(Request $request, ProdukClient $client): RedirectResponse
    {
        $this->authorizeClient($client);
        return $this->beginOAuth($request, $client->id, null);
    }

    public function redirectDraft(Request $request): RedirectResponse
    {
        $data = $request->validate(['draft_key' => ['required', 'uuid']]);
        return $this->beginOAuth($request, null, $data['draft_key']);
    }

    private function beginOAuth(Request $request, ?int $clientId, ?string $draftKey): RedirectResponse
    {
        $config = $this->config();
        $state = Str::random(64);

        $request->session()->put('meta_oauth', [
            'state' => $state,
            'client_id' => $clientId,
            'draft_key' => $draftKey,
        ]);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'state' => $state,
            'config_id' => $config['config_id'],
            'response_type' => 'code',
            'override_default_response_type' => 'true',
        ]);

        return redirect()->away('https://www.facebook.com/dialog/oauth?'.$query);
    }

    public function callback(Request $request, MetaConnectionSyncService $connectionSync)
    {
        $pending = $request->session()->pull('meta_oauth');
        abort_unless(is_array($pending), 419, 'Sesi OAuth Meta tidak ditemukan atau sudah kedaluwarsa.');
        abort_unless(hash_equals((string) ($pending['state'] ?? ''), (string) $request->query('state')), 419, 'State OAuth Meta tidak valid.');

        $client = filled($pending['client_id'] ?? null) ? ProdukClient::findOrFail($pending['client_id']) : null;
        if ($client) {
            $this->authorizeClient($client);
        }

        if ($request->filled('error')) {
            return response()->view('oauth.popup-result', [
                'success' => false,
                'message' => 'Koneksi Meta dibatalkan: '.($request->query('error_description') ?: $request->query('error')),
            ]);
        }

        $request->validate(['code' => ['required', 'string']]);
        $config = $this->config();

        try {
            $tokenPayload = Http::timeout(20)->get($this->graphUrl('oauth/access_token'), [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
                'code' => $request->string('code')->toString(),
            ])->throw()->json();

            $accessToken = $tokenPayload['access_token'] ?? null;
            throw_unless($accessToken, ValidationException::withMessages(['oauth' => 'Meta tidak mengembalikan access token.']));

            // Tukarkan token singkat menjadi token berumur panjang jika didukung.
            $longLived = Http::timeout(20)->get($this->graphUrl('oauth/access_token'), [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'fb_exchange_token' => $accessToken,
            ]);
            if ($longLived->successful() && filled($longLived->json('access_token'))) {
                $tokenPayload = array_merge($tokenPayload, $longLived->json());
                $accessToken = $tokenPayload['access_token'];
            }

            $profile = $this->graph('me', $accessToken, ['fields' => 'id,name,email']);
            $pages = $this->graph('me/accounts', $accessToken, [
                'fields' => 'id,name,access_token,tasks,picture,instagram_business_account{id,username,name,profile_picture_url}',
                'limit' => 100,
            ])['data'] ?? [];
            $adAccounts = $this->graph('me/adaccounts', $accessToken, [
                'fields' => 'id,account_id,name,account_status,currency,timezone_name',
                'limit' => 100,
            ], false)['data'] ?? [];
            $adAccounts = collect($adAccounts)->map(function ($account) use ($accessToken) {
                $promotablePages = $this->graph(($account['id'] ?? '').'/promote_pages', $accessToken, [
                    'fields' => 'id,name',
                    'limit' => 100,
                ], false)['data'] ?? [];
                $account['page_ids'] = collect($promotablePages)
                    ->pluck('id')->filter()->map(fn ($id) => (string) $id)->values()->all();

                return $account;
            })->values()->all();
            $permissions = collect($this->graph('me/permissions', $accessToken, [], false)['data'] ?? [])
                ->where('status', 'granted')->pluck('permission')->values()->all();

            $existing = $client ? OAuthConnection::where('client_id', $client->id)->where('provider', 'meta')->first() : null;
            $selected = $existing?->selected_assets ?? [];
            if (! collect($pages)->contains('id', $selected['page_id'] ?? null)) {
                $selected['page_id'] = Arr::get($pages, '0.id');
            }
            $availablePageIds = collect($pages)->pluck('id')->filter()->map(fn ($id) => (string) $id);
            $selected['page_ids'] = collect($selected['page_ids'] ?? [$selected['page_id'] ?? null])
                ->filter()->map(fn ($id) => (string) $id)->intersect($availablePageIds)->values()->all();
            $selected['page_ids'] = $selected['page_ids'] ?: array_values(array_filter([$selected['page_id'] ?? null]));
            $instagramAccounts = collect($pages)
                ->pluck('instagram_business_account')
                ->filter(fn ($account) => filled($account['id'] ?? null));
            if (! $instagramAccounts->contains('id', $selected['instagram_account_id'] ?? null)) {
                $selected['instagram_account_id'] = $instagramAccounts->first()['id'] ?? null;
            }
            $availableInstagramIds = $instagramAccounts->pluck('id')->filter()->map(fn ($id) => (string) $id);
            $selected['instagram_account_ids'] = collect($selected['instagram_account_ids'] ?? [$selected['instagram_account_id'] ?? null])
                ->filter()->map(fn ($id) => (string) $id)->intersect($availableInstagramIds)->values()->all();
            $selected['instagram_account_ids'] = $selected['instagram_account_ids'] ?: array_values(array_filter([$selected['instagram_account_id'] ?? null]));
            $selectedPageIds = collect($selected['page_ids'])
                ->merge(collect($pages)
                    ->filter(fn ($page) => in_array((string) Arr::get($page, 'instagram_business_account.id'), $selected['instagram_account_ids'], true))
                    ->pluck('id'))
                ->filter()->map(fn ($id) => (string) $id)->unique();
            $eligibleAdAccounts = collect($adAccounts)->filter(fn ($account) =>
                collect($account['page_ids'] ?? [])->map(fn ($id) => (string) $id)->intersect($selectedPageIds)->isNotEmpty()
            );
            if (! $eligibleAdAccounts->contains('id', $selected['ad_account_id'] ?? null)) {
                $selected['ad_account_id'] = $eligibleAdAccounts->first()['id'] ?? null;
            }

            $connectionData = [
                'connected_by' => Auth::id(),
                'access_token' => $accessToken,
                'token_type' => $tokenPayload['token_type'] ?? 'bearer',
                'expires_at' => isset($tokenPayload['expires_in']) ? now()->addSeconds((int) $tokenPayload['expires_in'])->toIso8601String() : null,
                'provider_user_id' => $profile['id'] ?? null,
                'provider_user_name' => $profile['name'] ?? null,
                'scopes' => $permissions,
                'assets' => ['pages' => $pages, 'ad_accounts' => $adAccounts],
                'selected_assets' => $selected,
                'last_synced_at' => now()->toIso8601String(),
            ];

            if ($client) {
                OAuthConnection::updateOrCreate(
                    ['client_id' => $client->id, 'provider' => 'meta'],
                    $connectionData
                );
                $connectionSync->refreshSiblingConnections($client->id, $connectionData);
                $this->ensurePlatformLabels($client, $pages);
            } else {
                $draftKey = $pending['draft_key'] ?? null;
                abort_unless(filled($draftKey), 419, 'Kunci form OAuth tidak tersedia.');
                $request->session()->put('meta_oauth_pending.'.$draftKey, $connectionData);
            }

            return response()->view('oauth.popup-result', [
                'success' => true,
                'message' => 'Akun Meta berhasil dihubungkan.',
                'connection' => [
                    'provider_user_name' => $profile['name'] ?? 'Pengguna Meta',
                    'pages_count' => count($pages),
                    'ad_accounts_count' => count($adAccounts),
                    'client_id' => $client?->id,
                ],
            ]);
        } catch (RequestException $exception) {
            report($exception);
            $message = $exception->response?->json('error.message') ?: 'Meta menolak proses OAuth. Periksa Redirect URI, Configuration ID, dan izin aplikasi.';
            return response()->view('oauth.popup-result', ['success' => false, 'message' => $message]);
        }
    }

    public function selectAssets(Request $request, ProdukClient $client)
    {
        $this->authorizeClient($client);
        $connection = $client->oauthConnections()->where('provider', 'meta')->firstOrFail();
        $data = $request->validate([
            'page_id' => ['nullable', 'string', 'max:255'],
            'instagram_account_id' => ['nullable', 'string', 'max:255'],
            'page_ids' => ['nullable', 'array'],
            'page_ids.*' => ['string', 'max:255'],
            'instagram_account_ids' => ['nullable', 'array'],
            'instagram_account_ids.*' => ['string', 'max:255'],
            'ad_account_id' => ['nullable', 'string', 'max:255'],
        ]);
        $connection->saveSelectedMetaAssets($data);
        $connection->refresh();

        return response()->json([
            'message' => 'Aset Meta berhasil dipilih.',
            'selected_assets' => $connection->selected_assets ?? [],
        ]);
    }

    public function disconnect(ProdukClient $client)
    {
        $this->authorizeClient($client);
        $client->oauthConnections()->where('provider', 'meta')->delete();
        return response()->json(['message' => 'Koneksi Meta berhasil diputus.']);
    }

    private function graph(string $path, string $token, array $query = [], bool $required = true): array
    {
        $response = Http::timeout(20)->get($this->graphUrl($path), array_merge($query, ['access_token' => $token]));
        if ($required) {
            $response->throw();
        }
        return $response->successful() ? $response->json() : [];
    }

    private function graphUrl(string $path): string
    {
        return 'https://graph.facebook.com/'.config('services.meta.graph_version').'/'.ltrim($path, '/');
    }

    private function config(): array
    {
        $config = config('services.meta');
        foreach (['client_id', 'client_secret', 'config_id', 'redirect_uri'] as $key) {
            abort_unless(filled($config[$key] ?? null), 503, 'Konfigurasi META_'.strtoupper($key).' belum tersedia di server.');
        }
        return $config;
    }

    private function authorizeClient(ProdukClient $client): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin || $client->teams()->whereKey($user->id)->exists()), 403);
    }

    private function ensurePlatformLabels(ProdukClient $client, array $pages): void
    {
        $current = is_array($client->access_token) ? $client->access_token : [];
        $platforms = collect($current['platform'] ?? []);
        $access = collect($current['access'] ?? []);

        foreach (['Facebook', 'Instagram'] as $platform) {
            if ($platform === 'Facebook' && empty($pages)) {
                continue;
            }
            if ($platform === 'Instagram' && ! collect($pages)->contains(fn ($page) => filled(Arr::get($page, 'instagram_business_account.id')))) {
                continue;
            }
            if (! $platforms->contains($platform)) {
                $platforms->push($platform);
                $access->push('');
            }
        }

        $client->update(['access_token' => ['platform' => $platforms->values()->all(), 'access' => $access->values()->all()]]);
    }
}
