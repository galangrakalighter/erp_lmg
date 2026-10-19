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

class ThreadsOAuthController extends Controller
{
    private const SCOPES = ['threads_basic', 'threads_manage_insights'];

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

        $request->session()->put('threads_oauth', [
            'state' => $state,
            'client_id' => $clientId,
            'draft_key' => $draftKey,
        ]);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => implode(',', self::SCOPES),
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect()->away('https://threads.net/oauth/authorize?'.$query);
    }

    public function callback(Request $request)
    {
        $pending = $request->session()->pull('threads_oauth');
        abort_unless(is_array($pending), 419, 'Sesi OAuth Threads tidak ditemukan atau sudah kedaluwarsa.');
        abort_unless(hash_equals((string) ($pending['state'] ?? ''), (string) $request->query('state')), 419, 'State OAuth Threads tidak valid.');

        $client = filled($pending['client_id'] ?? null) ? ProdukClient::findOrFail($pending['client_id']) : null;
        if ($client) {
            $this->authorizeClient($client);
        }

        if ($request->filled('error')) {
            return $this->popup(false, 'Koneksi Threads dibatalkan: '.($request->query('error_description') ?: $request->query('error')));
        }

        $request->validate(['code' => ['required', 'string']]);
        $config = $this->config();

        try {
            $tokenPayload = Http::asForm()->timeout(25)
                ->post('https://graph.threads.net/oauth/access_token', [
                    'client_id' => $config['client_id'],
                    'client_secret' => $config['client_secret'],
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $config['redirect_uri'],
                    'code' => $request->string('code')->toString(),
                ])->throw()->json();

            $accessToken = $tokenPayload['access_token'] ?? null;
            throw_unless($accessToken, ValidationException::withMessages(['oauth' => 'Threads tidak mengembalikan access token.']));

            $longLived = Http::timeout(25)->get('https://graph.threads.net/access_token', [
                'grant_type' => 'th_exchange_token',
                'client_secret' => $config['client_secret'],
                'access_token' => $accessToken,
            ]);
            if ($longLived->successful() && filled($longLived->json('access_token'))) {
                $tokenPayload = array_merge($tokenPayload, $longLived->json());
                $accessToken = $tokenPayload['access_token'];
            }

            $profile = Http::timeout(25)->get($this->graphUrl('me'), [
                'fields' => 'id,username,name,threads_profile_picture_url,threads_biography',
                'access_token' => $accessToken,
            ])->throw()->json();

            $connectionData = [
                'connected_by' => Auth::id(),
                'access_token' => $accessToken,
                'token_type' => $tokenPayload['token_type'] ?? 'bearer',
                'expires_at' => isset($tokenPayload['expires_in']) ? now()->addSeconds((int) $tokenPayload['expires_in'])->toIso8601String() : null,
                'provider_user_id' => $profile['id'] ?? null,
                'provider_user_name' => $profile['username'] ?? $profile['name'] ?? null,
                'scopes' => self::SCOPES,
                'assets' => ['profile' => $profile],
                'selected_assets' => ['threads_user_id' => $profile['id'] ?? null],
                'last_synced_at' => now()->toIso8601String(),
            ];

            if ($client) {
                OAuthConnection::updateOrCreate(
                    ['client_id' => $client->id, 'provider' => 'threads'],
                    $connectionData
                );
                $this->ensurePlatformLabel($client);
            } else {
                $draftKey = $pending['draft_key'] ?? null;
                abort_unless(filled($draftKey), 419, 'Kunci form OAuth tidak tersedia.');
                $request->session()->put('threads_oauth_pending.'.$draftKey, $connectionData);
            }

            return $this->popup(true, 'Akun Threads berhasil dihubungkan.', [
                'provider_user_name' => $profile['username'] ?? $profile['name'] ?? 'Pengguna Threads',
                'client_id' => $client?->id,
            ]);
        } catch (RequestException $exception) {
            report($exception);
            $message = $exception->response?->json('error.message')
                ?: $exception->response?->json('error_description')
                ?: 'Threads menolak proses OAuth. Periksa App ID, App Secret, Redirect URI, dan izin aplikasi.';

            return $this->popup(false, $message);
        } catch (\Throwable $exception) {
            report($exception);

            return $this->popup(false, 'Koneksi Threads gagal diproses. Periksa konfigurasi aplikasi dan coba kembali.');
        }
    }

    public function disconnect(ProdukClient $client)
    {
        $this->authorizeClient($client);
        $client->oauthConnections()->where('provider', 'threads')->delete();

        return response()->json(['message' => 'Koneksi Threads berhasil diputus.']);
    }

    public function deauthorize(Request $request)
    {
        $payload = $this->signedRequestPayload($request);
        OAuthConnection::where('provider', 'threads')
            ->where('provider_user_id', (string) ($payload['user_id'] ?? ''))
            ->delete();

        return response()->json(['success' => true]);
    }

    public function dataDeletion(Request $request)
    {
        $payload = $this->signedRequestPayload($request);
        OAuthConnection::where('provider', 'threads')
            ->where('provider_user_id', (string) ($payload['user_id'] ?? ''))
            ->delete();

        $confirmationCode = Str::uuid()->toString();

        return response()->json([
            'url' => route('legal.data-deletion').'?confirmation_code='.$confirmationCode,
            'confirmation_code' => $confirmationCode,
        ]);
    }

    private function popup(bool $success, string $message, ?array $connection = null)
    {
        return response()->view('oauth.popup-result', compact('success', 'message', 'connection') + [
            'provider' => 'threads',
            'providerLabel' => 'Threads',
        ]);
    }

    private function signedRequestPayload(Request $request): array
    {
        $signedRequest = $request->string('signed_request')->toString();
        abort_unless(str_contains($signedRequest, '.'), 400, 'Signed request Threads tidak valid.');

        [$encodedSignature, $encodedPayload] = explode('.', $signedRequest, 2);
        $decode = static function (string $value): string|false {
            $padding = strlen($value) % 4;
            if ($padding) {
                $value .= str_repeat('=', 4 - $padding);
            }

            return base64_decode(strtr($value, '-_', '+/'), true);
        };

        $signature = $decode($encodedSignature);
        $json = $decode($encodedPayload);
        abort_unless(is_string($signature) && is_string($json), 400, 'Signed request Threads tidak dapat dibaca.');

        $expected = hash_hmac('sha256', $encodedPayload, (string) $this->config()['client_secret'], true);
        abort_unless(hash_equals($expected, $signature), 400, 'Tanda tangan callback Threads tidak valid.');

        $payload = json_decode($json, true);
        abort_unless(is_array($payload) && filled($payload['user_id'] ?? null), 400, 'ID pengguna Threads tidak tersedia.');

        return $payload;
    }

    private function graphUrl(string $path): string
    {
        return 'https://graph.threads.net/'.config('services.threads.graph_version', 'v1.0').'/'.ltrim($path, '/');
    }

    private function config(): array
    {
        $config = config('services.threads');
        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            abort_unless(filled($config[$key] ?? null), 503, 'Konfigurasi THREADS_'.strtoupper($key).' belum tersedia di server.');
        }

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

        if (! $platforms->contains('Threads')) {
            $platforms->push('Threads');
            $access->push('');
            $client->update(['access_token' => [
                'platform' => $platforms->values()->all(),
                'access' => $access->values()->all(),
            ]]);
        }
    }
}
