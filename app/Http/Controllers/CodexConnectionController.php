<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class CodexConnectionController extends Controller
{
    private const TOKEN_PREFIX = 'codex-erp-lmg:';

    private const MAX_ACTIVE_TOKENS = 5;

    public function index(Request $request)
    {
        return $this->render($request);
    }

    public function store(Request $request, ActivityLogService $activityLog)
    {
        abort_if((bool) $request->user()->is_locked, 403, 'Akun terkunci tidak dapat membuat koneksi Codex.');

        $validated = $request->validate([
            'device_name' => ['required', 'string', 'max:60'],
            'current_password' => ['required', 'current_password'],
        ], [
            'device_name.required' => 'Nama perangkat wajib diisi.',
            'device_name.max' => 'Nama perangkat maksimal 60 karakter.',
            'current_password.required' => 'Password akun wajib diisi.',
            'current_password.current_password' => 'Password akun tidak sesuai.',
        ]);

        $activeTokens = $this->tokens($request)->count();
        if ($activeTokens >= self::MAX_ACTIVE_TOKENS) {
            throw ValidationException::withMessages([
                'device_name' => 'Maksimal lima koneksi Codex aktif. Cabut salah satu koneksi terlebih dahulu.',
            ]);
        }

        $deviceName = Str::of($validated['device_name'])->squish()->limit(60, '')->toString();
        $plainTextToken = $request->user()
            ->createToken(self::TOKEN_PREFIX.' '.$deviceName, ['marketing:read'], now()->addDays(90))
            ->plainTextToken;

        $activityLog->record([
            'action' => 'CREATE',
            'module' => 'marketing_mcp_connection',
            'description' => 'Membuat koneksi Codex untuk analitik marketing',
            'target_type' => 'personal_access_token',
            'target_name' => $deviceName,
            'context' => ['abilities' => ['marketing:read'], 'expires_in_days' => 90],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'device_name' => $deviceName,
                'token' => $plainTextToken,
                'api_url' => rtrim((string) config('app.url'), '/'),
                'abilities' => ['marketing:read'],
                'expires_at' => now()->addDays(90)->toIso8601String(),
            ], 201);
        }

        return $this->render($request, $plainTextToken, $deviceName);
    }

    public function destroy(Request $request, int $token, ActivityLogService $activityLog)
    {
        $personalAccessToken = $this->tokens($request)->firstWhere('id', $token);
        abort_unless($personalAccessToken instanceof PersonalAccessToken, 404);

        $deviceName = Str::after($personalAccessToken->name, self::TOKEN_PREFIX);
        $personalAccessToken->delete();

        $activityLog->record([
            'action' => 'DELETE',
            'module' => 'marketing_mcp_connection',
            'description' => 'Mencabut koneksi Codex untuk analitik marketing',
            'target_type' => 'personal_access_token',
            'target_id' => $token,
            'target_name' => trim($deviceName),
        ]);

        return redirect()->route('codex-connection.index')->with('success', 'Koneksi Codex berhasil dicabut.');
    }

    public function destroyOAuth(Request $request, string $client, ActivityLogService $activityLog)
    {
        $tokenIds = DB::table('oauth_access_tokens')
            ->join('oauth_clients', 'oauth_clients.id', '=', 'oauth_access_tokens.client_id')
            ->where('oauth_access_tokens.user_id', $request->user()->getAuthIdentifier())
            ->where('oauth_access_tokens.client_id', $client)
            ->where('oauth_clients.provider', 'oauth_users')
            ->where('oauth_access_tokens.revoked', false)
            ->pluck('oauth_access_tokens.id');

        abort_if($tokenIds->isEmpty(), 404);

        DB::transaction(function () use ($tokenIds): void {
            DB::table('oauth_refresh_tokens')
                ->whereIn('access_token_id', $tokenIds)
                ->update(['revoked' => true]);
            DB::table('oauth_access_tokens')
                ->whereIn('id', $tokenIds)
                ->update(['revoked' => true]);
        });

        $activityLog->record([
            'action' => 'DELETE',
            'module' => 'marketing_mcp_connection',
            'description' => 'Mencabut koneksi OAuth aplikasi AI',
            'target_type' => 'oauth_client',
            'context' => ['revoked_token_count' => $tokenIds->count()],
        ]);

        return redirect()->route('ai-connections.index')->with('success', 'Koneksi aplikasi AI berhasil dicabut.');
    }

    private function render(Request $request, ?string $newToken = null, ?string $deviceName = null)
    {
        return response()->view('profile.codex-connection', [
            'title' => 'Koneksi AI',
            'tokens' => $this->tokens($request),
            'oauthConnections' => $this->oauthConnections($request),
            'chatGptAppUrl' => $this->safeChatGptAppUrl(),
            'mcpUrl' => rtrim((string) config('marketing-mcp.resource'), '/'),
            'newToken' => $newToken,
            'deviceName' => $deviceName,
            'apiUrl' => rtrim((string) config('app.url'), '/'),
            'maxActiveTokens' => self::MAX_ACTIVE_TOKENS,
        ], $newToken ? 201 : 200);
    }

    private function tokens(Request $request)
    {
        return $request->user()->tokens()
            ->where('name', 'like', self::TOKEN_PREFIX.'%')
            ->latest()
            ->get();
    }

    private function oauthConnections(Request $request)
    {
        return DB::table('oauth_access_tokens')
            ->join('oauth_clients', 'oauth_clients.id', '=', 'oauth_access_tokens.client_id')
            ->where('oauth_access_tokens.user_id', $request->user()->getAuthIdentifier())
            ->where('oauth_clients.provider', 'oauth_users')
            ->where('oauth_access_tokens.revoked', false)
            ->where(function ($query): void {
                $query->whereNull('oauth_access_tokens.expires_at')
                    ->orWhere('oauth_access_tokens.expires_at', '>', now());
            })
            ->groupBy('oauth_clients.id', 'oauth_clients.name')
            ->orderByDesc(DB::raw('MAX(oauth_access_tokens.created_at)'))
            ->get([
                'oauth_clients.id as client_id',
                'oauth_clients.name',
                DB::raw('MAX(oauth_access_tokens.created_at) as connected_at'),
                DB::raw('MAX(oauth_access_tokens.expires_at) as expires_at'),
                DB::raw('COUNT(oauth_access_tokens.id) as active_token_count'),
            ]);
    }

    private function safeChatGptAppUrl(): ?string
    {
        $url = trim((string) config('services.chatgpt.erp_app_url'));

        return filter_var($url, FILTER_VALIDATE_URL) && Str::startsWith($url, ['https://chatgpt.com/', 'https://www.chatgpt.com/'])
            ? $url
            : null;
    }
}
