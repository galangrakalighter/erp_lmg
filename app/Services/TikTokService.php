<?php

namespace App\Services;

use App\Models\OAuthConnection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService
{
    public function getAnalysis(OAuthConnection $connection, ?int $since = null, ?int $until = null, bool $fetch = false): array
    {
        try {
            $token = $this->validAccessToken($connection);
            $scopes = collect($connection->scopes ?? []);
            $fields = collect(['open_id', 'union_id', 'avatar_url', 'display_name']);
            if ($scopes->contains('user.info.profile')) {
                $fields = $fields->merge(['username', 'bio_description', 'profile_deep_link', 'is_verified']);
            }
            if ($scopes->contains('user.info.stats')) {
                $fields = $fields->merge(['follower_count', 'following_count', 'likes_count', 'video_count']);
            }

            $profileResponse = Http::withToken($token)->timeout(30)->get('https://open.tiktokapis.com/v2/user/info/', [
                'fields' => $fields->unique()->implode(','),
            ]);
            $profilePayload = $profileResponse->json();
            if (! $profileResponse->successful() || ! in_array(data_get($profilePayload, 'error.code'), [null, 'ok'], true)) {
                throw new \RuntimeException($this->message($profilePayload));
            }
            $profile = data_get($profilePayload, 'data.user', []);

            if (! $scopes->contains('video.list')) {
                return [
                    'data' => [],
                    'profile' => $profile,
                    'error' => 'Izin video.list belum diberikan pada token ini. Tambahkan scope video.list pada produk TikTok API di Developer Portal, lalu putuskan dan hubungkan ulang akun TikTok.',
                ];
            }

            $videos = [];
            $cursor = null;
            do {
                $body = ['max_count' => 20];
                if ($cursor !== null) $body['cursor'] = $cursor;
                $response = Http::withToken($token)->asJson()->timeout(45)
                    ->post('https://open.tiktokapis.com/v2/video/list/?fields=id,title,video_description,create_time,duration,cover_image_url,share_url,embed_link,like_count,comment_count,share_count,view_count', $body);
                $payload = $response->json();
                if (! $response->successful() || ! in_array(data_get($payload, 'error.code'), [null, 'ok'], true)) {
                    throw new \RuntimeException($this->message($payload));
                }
                $videos = array_merge($videos, data_get($payload, 'data.videos', []));
                $hasMore = $fetch && (bool) data_get($payload, 'data.has_more', false) && count($videos) < 200;
                $cursor = data_get($payload, 'data.cursor');
            } while ($hasMore && $cursor !== null);

            $items = collect($videos)->map(function (array $video) use ($since, $until) {
                $timestamp = (int) ($video['create_time'] ?? 0);
                if (! $timestamp || ($since && $timestamp < $since) || ($until && $timestamp > $until)) return null;
                $title = trim((string) ($video['title'] ?? $video['video_description'] ?? ''));
                $likes = (int) ($video['like_count'] ?? 0);
                $comments = (int) ($video['comment_count'] ?? 0);
                $shares = (int) ($video['share_count'] ?? 0);
                return [
                    'id' => (string) ($video['id'] ?? ''), 'judul' => $title !== '' ? $title : 'Video TikTok',
                    'tanggal_posting' => Carbon::createFromTimestamp($timestamp)->format('d M Y'), 'timestamp' => $timestamp,
                    'jenis' => 'VIDEO', 'link' => $video['share_url'] ?? $video['embed_link'] ?? '#',
                    'cover_image_url' => $video['cover_image_url'] ?? null, 'duration' => (int) ($video['duration'] ?? 0),
                    'views' => (int) ($video['view_count'] ?? 0), 'likes' => $likes, 'comments' => $comments,
                    'shares' => $shares, 'engagement' => $likes + $comments + $shares,
                ];
            })->filter()->sortBy('timestamp')->values()->all();

            $connection->forceFill([
                'provider_user_name' => $profile['username'] ?? $profile['display_name'] ?? $connection->provider_user_name,
                'assets' => array_merge($connection->assets ?? [], ['profile' => $profile]),
                'last_synced_at' => now(),
            ])->save();

            return ['data' => $items, 'profile' => $profile, 'error' => null];
        } catch (\Throwable $exception) {
            Log::warning('TikTok API gagal mengambil data analisis.', ['message' => $exception->getMessage()]);
            return ['data' => [], 'profile' => data_get($connection->assets, 'profile', []), 'error' => 'Data TikTok belum dapat diambil: '.$exception->getMessage()];
        }
    }

    private function validAccessToken(OAuthConnection $connection): string
    {
        if (! $connection->expires_at || $connection->expires_at->isAfter(now()->addMinutes(10))) return (string) $connection->access_token;
        $assets = $connection->assets ?? [];
        $refreshToken = $assets['refresh_token'] ?? null;
        if (! $refreshToken) throw new \RuntimeException('Refresh token TikTok tidak tersedia. Hubungkan ulang akun TikTok.');

        $response = Http::asForm()->timeout(30)->post('https://open.tiktokapis.com/v2/oauth/token/', [
            'client_key' => config('services.tiktok.client_key'), 'client_secret' => config('services.tiktok.client_secret'),
            'grant_type' => 'refresh_token', 'refresh_token' => $refreshToken,
        ]);
        $payload = $response->json();
        if (! $response->successful() || filled($payload['error'] ?? null) || blank($payload['access_token'] ?? null)) throw new \RuntimeException($this->message($payload));

        $assets['refresh_token'] = $payload['refresh_token'] ?? $refreshToken;
        $assets['refresh_expires_at'] = isset($payload['refresh_expires_in']) ? now()->addSeconds((int) $payload['refresh_expires_in'])->toIso8601String() : ($assets['refresh_expires_at'] ?? null);
        $connection->forceFill([
            'access_token' => $payload['access_token'], 'token_type' => $payload['token_type'] ?? 'Bearer',
            'expires_at' => now()->addSeconds((int) ($payload['expires_in'] ?? 86400)),
            'scopes' => array_values(array_filter(explode(',', (string) ($payload['scope'] ?? implode(',', $connection->scopes ?? []))))),
            'assets' => $assets,
        ])->save();
        return (string) $payload['access_token'];
    }

    private function message(array $payload): string
    {
        return data_get($payload, 'error.message') ?: data_get($payload, 'error_description') ?: data_get($payload, 'message') ?: 'TikTok menolak permintaan data.';
    }
}
