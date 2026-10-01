<?php

namespace App\Http\Controllers;

use App\Models\OAuthConnection;
use App\Models\ProdukClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TikTokOAuthController extends Controller
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

        $request->session()->put('tiktok_oauth', [
            'state' => $state,
            'client_id' => $clientId,
            'draft_key' => $draftKey,
        ]);

        return redirect()->away('https://www.tiktok.com/v2/auth/authorize/?'.http_build_query([
            'client_key' => $config['client_key'],
            'response_type' => 'code',
            'scope' => implode(',', $config['scopes']),
            'redirect_uri' => $config['redirect_uri'],
            'state' => $state,
        ]));
    }

    public function callback(Request $request)
    {
        $pending = $request->session()->pull('tiktok_oauth');
        abort_unless(is_array($pending), 419, 'Sesi OAuth TikTok tidak ditemukan atau sudah kedaluwarsa.');
        abort_unless(hash_equals((string) ($pending['state'] ?? ''), (string) $request->query('state')), 419, 'State OAuth TikTok tidak valid.');

        $client = filled($pending['client_id'] ?? null) ? ProdukClient::findOrFail($pending['client_id']) : null;
        if ($client) {
            $this->authorizeClient($client);
        }

        if ($request->filled('error')) {
            return $this->popup(false, 'Koneksi TikTok dibatalkan: '.($request->query('error_description') ?: $request->query('error')));
        }

        $request->validate(['code' => ['required', 'string']]);
        $config = $this->config();

        try {
            $tokenResponse = Http::asForm()->timeout(25)->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key' => $config['client_key'],
                'client_secret' => $config['client_secret'],
                'code' => $request->string('code')->toString(),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $config['redirect_uri'],
            ]);
            $tokenResponse->throw();
            $tokenPayload = $tokenResponse->json();
            if (filled($tokenPayload['error'] ?? null)) {
                throw ValidationException::withMessages(['oauth' => $tokenPayload['error_description'] ?? $tokenPayload['error']]);
            }

            $accessToken = $tokenPayload['access_token'] ?? null;
            throw_unless($accessToken, ValidationException::withMessages(['oauth' => 'TikTok tidak mengembalikan access token.']));

            $profileResponse = Http::withToken($accessToken)->timeout(25)
                ->get('https://open.tiktokapis.com/v2/user/info/', [
                    'fields' => 'open_id,union_id,avatar_url,display_name',
                ]);
            $profileResponse->throw();
            $profilePayload = $profileResponse->json();
            if (($profilePayload['error']['code'] ?? 'ok') !== 'ok') {
                throw ValidationException::withMessages(['oauth' => $profilePayload['error']['message'] ?? 'Profil TikTok gagal diambil.']);
            }
            $profile = $profilePayload['data']['user'] ?? [];

            $connectionData = [
                'connected_by' => Auth::id(),
                'access_token' => $accessToken,
                'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
                'expires_at' => isset($tokenPayload['expires_in']) ? now()->addSeconds((int) $tokenPayload['expires_in'])->toIso8601String() : null,
                'provider_user_id' => $tokenPayload['open_id'] ?? $profile['open_id'] ?? null,
                'provider_user_name' => $profile['display_name'] ?? null,
                'scopes' => array_values(array_filter(explode(',', (string) ($tokenPayload['scope'] ?? implode(',', $config['scopes']))))),
                'assets' => [
                    'profile' => $profile,
                    'refresh_token' => $tokenPayload['refresh_token'] ?? null,
                    'refresh_expires_at' => isset($tokenPayload['refresh_expires_in']) ? now()->addSeconds((int) $tokenPayload['refresh_expires_in'])->toIso8601String() : null,
                ],
                'selected_assets' => ['open_id' => $tokenPayload['open_id'] ?? $profile['open_id'] ?? null],
                'last_synced_at' => now()->toIso8601String(),
            ];

            if ($client) {
                OAuthConnection::updateOrCreate(['client_id' => $client->id, 'provider' => 'tiktok'], $connectionData);
                $this->ensurePlatformLabel($client);
            } else {
                $draftKey = $pending['draft_key'] ?? null;
                abort_unless(filled($draftKey), 419, 'Kunci form OAuth tidak tersedia.');
                $request->session()->put('tiktok_oauth_pending.'.$draftKey, $connectionData);
            }

            return $this->popup(true, 'Akun TikTok berhasil dihubungkan.', [
                'provider_user_name' => $profile['display_name'] ?? 'Pengguna TikTok',
                'client_id' => $client?->id,
            ]);
        } catch (ValidationException $exception) {
            return $this->popup(false, collect($exception->errors())->flatten()->first() ?: 'Koneksi TikTok gagal divalidasi.');
        } catch (RequestException $exception) {
            report($exception);
            $message = $exception->response?->json('error.message')
                ?: $exception->response?->json('error_description')
                ?: $exception->response?->json('message')
                ?: 'TikTok menolak proses OAuth. Periksa Client Key, Client Secret, Redirect URI, dan scope aplikasi.';

            return $this->popup(false, $message);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->popup(false, 'Koneksi TikTok gagal diproses. Periksa konfigurasi aplikasi dan coba kembali.');
        }
    }

    public function disconnect(ProdukClient $client)
    {
        $this->authorizeClient($client);
        $connection = $client->oauthConnections()->where('provider', 'tiktok')->first();

        if ($connection && filled($connection->access_token)) {
            Http::asForm()->timeout(15)->post('https://open.tiktokapis.com/v2/oauth/revoke/', [
                'client_key' => $this->config()['client_key'],
                'client_secret' => $this->config()['client_secret'],
                'token' => $connection->access_token,
            ]);
        }
        $connection?->delete();

        return response()->json(['message' => 'Koneksi TikTok berhasil diputus.']);
    }

    private function popup(bool $success, string $message, ?array $connection = null)
    {
        return response()->view('oauth.popup-result', compact('success', 'message', 'connection') + [
            'provider' => 'tiktok',
            'providerLabel' => 'TikTok',
        ]);
    }

    private function config(): array
    {
        $config = config('services.tiktok');
        foreach (['client_key', 'client_secret', 'redirect_uri'] as $key) {
            abort_unless(filled($config[$key] ?? null), 503, 'Konfigurasi TIKTOK_'.strtoupper($key).' belum tersedia di server.');
        }
        $config['scopes'] = array_values(array_filter(array_map('trim', explode(',', (string) ($config['scopes'] ?? 'user.info.basic')))));

        return $config;
    }

    private function authorizeClient(ProdukClient $client): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin || $client->teams()->whereKey($user->id)->exists()), 403);
    }

    private function ensurePlatformLabel(ProdukClient $client): void
    {
        $current = is_array($client->access_token) ? $client->access_token : [];
        $platforms = collect($current['platform'] ?? []);
        $access = collect($current['access'] ?? []);
        if (! $platforms->contains('TikTok')) {
            $platforms->push('TikTok');
            $access->push('');
            $client->update(['access_token' => ['platform' => $platforms->values()->all(), 'access' => $access->values()->all()]]);
        }
    }
}
