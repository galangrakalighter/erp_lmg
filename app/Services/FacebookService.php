<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class FacebookService
{
    protected string $token;

    public function getAudienceBreakdowns($token, $pageId, $since = null, $until = null): array
    {
        $result = ['gender' => [], 'age' => [], 'country' => [], 'city' => [], 'reaction_type' => []];
        if (! filled($token) || ! filled($pageId)) {
            return $result;
        }

        $version = config('services.meta.graph_version', 'v23.0');
        $url = "https://graph.facebook.com/{$version}/{$pageId}/insights";
        foreach (['gender_age' => 'page_fans_gender_age', 'country' => 'page_fans_country', 'city' => 'page_fans_city'] as $key => $metric) {
            try {
                $response = Http::timeout(45)->withOptions(['verify' => false])->get($url, [
                    'metric' => $metric,
                    'period' => 'lifetime',
                    'access_token' => $token,
                ]);
                if (! $response->successful()) {
                    Log::info('Breakdown audiens Facebook tidak tersedia.', ['metric' => $metric, 'error' => $response->json('error.message')]);
                    continue;
                }
                $values = (array) data_get($response->json(), 'data.0.values.0.value', []);
                if ($key === 'gender_age') {
                    foreach ($values as $label => $value) {
                        [$gender, $age] = array_pad(explode('.', (string) $label, 2), 2, 'Tidak diketahui');
                        $result['gender'][$gender] = ($result['gender'][$gender] ?? 0) + (float) $value;
                        $result['age'][$age] = ($result['age'][$age] ?? 0) + (float) $value;
                    }
                } else {
                    $values = array_map(fn ($value) => (float) $value, $values);
                    arsort($values);
                    $result[$key] = array_slice($values, 0, 10, true);
                }
            } catch (\Throwable $exception) {
                Log::info('Breakdown audiens Facebook gagal dimuat.', ['metric' => $metric, 'message' => $exception->getMessage()]);
            }
        }

        try {
            $response = Http::timeout(45)->withOptions(['verify' => false])->get($url, array_filter([
                'metric' => 'page_actions_post_reactions_total',
                'period' => 'day',
                'since' => $since,
                'until' => $until,
                'access_token' => $token,
            ], fn ($value) => $value !== null));
            foreach ($response->successful() ? $response->json('data.0.values', []) : [] as $day) {
                foreach ((array) ($day['value'] ?? []) as $type => $value) {
                    $result['reaction_type'][$type] = ($result['reaction_type'][$type] ?? 0) + (float) $value;
                }
            }
        } catch (\Throwable $exception) {
            Log::info('Reaction Type Facebook gagal dimuat.', ['message' => $exception->getMessage()]);
        }

        arsort($result['gender']);
        arsort($result['age']);
        arsort($result['reaction_type']);
        return $result;
    }

    /**
     * Mengambil media dengan penambahan Timeout 120 detik
     */
    public function getPost($since, $until, $token, $fetch = false, $pageId = null){
        if (! filled($token) || ! filled($pageId)) {
            Log::warning('Facebook OAuth belum memiliki token atau Facebook Page ID.');
            return ['data' => [], 'followers' => [0]];
        }

        $version = config('services.meta.graph_version', 'v23.0');
        $url = "https://graph.facebook.com/{$version}/{$pageId}/feed";
        $params = [
            'fields' => 'id,message,created_time,permalink_url,attachments{target}',
            'access_token' => $token,
            'since' => $since,
            'until' => $until,
            'limit' => $fetch ? 100 : 15
        ];

        $data = [];
        $post = [];
        $response = Http::timeout(150)->withOptions(['verify' => false])->get($url, $params);
        if($response->successful()){
            $json = $response->json();
            $allMedia = $json['data'] ?? [];

            if ($fetch) {
                while (isset($json['paging']['next'])) {
                    // Early break jika sudah di luar rentang waktu
                    if ($since) {
                        $lastItem = end($allMedia);
                        if (isset($lastItem['timestamp']) && strtotime($lastItem['timestamp']) < $since) {
                            break;
                        }
                    }

                    $nextResponse = Http::timeout(60)->withOptions(['verify' => false])->get($json['paging']['next']);
                    
                    if ($nextResponse->successful()) {
                        $json = $nextResponse->json();
                        $allMedia = array_merge($allMedia, $json['data'] ?? []);
                        if (count($allMedia) >= 1000) break; 
                    } else { break; }
                }
            }

            foreach($allMedia as $item){
                $date = Carbon::parse($item['created_time'])->format('Y-m-d');
                $data[] = [
                    'id_post' => $item['id'],
                    'judul' => $item['message'] ?? "Tidak ada Judul",
                    'tanggal_posting' => $date,
                    'timestamp' => $item['created_time'],
                    'id_video' => $item['attachments']['data'][0]['target']['id'] ?? null,
                    'link' => $item['permalink_url'],
                ];
            }

            foreach ($data as $key) {
                $url_result = "https://graph.facebook.com/{$version}/{$key['id_post']}/insights";
                $params_result = [
                    'metric' => 'post_media_view,post_total_media_view_unique',
                    'period' => 'lifetime',
                    'access_token' => $token,
                ];
                
                $jumlah_reach = 0;
                if($key['id_video'] != null){
                    $url_reach = "https://graph.facebook.com/{$version}/{$key['id_video']}";
                    $params_reach = [
                        'fields' => "views",
                        'access_token' => $token,
                    ];
                    $reach = Http::timeout(120)->withOptions(['verify' => false])->get($url_reach, $params_reach);
                    $jumlah_reach = $reach->json()['views'] ?? 0;
                }

                $result = Http::timeout(120)->withOptions(['verify' => false])->get($url_result, $params_result);
                $dataGabung = $result->json()['data'] ?? [];
                // Inisialisasi default agar tidak error jika data kosong
                $reachValue = 0; 
                $impresiValue = 0;
                $pemirsaValue = 0;

                // Looping untuk memasukkan data ke variabel yang tepat berdasarkan NAMA metrik
                foreach ($dataGabung as $item) {
                    switch ($item['name']) {
                        case 'post_media_view':
                        case 'post_impressions':
                            $impresiValue = $item['values'][0]['value'] ?? 0;
                            break;
                        case 'post_total_media_view_unique':
                        case 'post_impressions_unique':
                            $reachValue = $item['values'][0]['value'] ?? 0;
                            break;
                    }
                }

                $url_insight = "https://graph.facebook.com/{$version}/{$key['id_post']}";
                $params_insights = [
                    'fields' => "reactions.summary(true), comments.summary(true), shares",
                    'period' => 'lifetime',
                    'access_token' => $token,
                ];

                $insights = Http::timeout(120)->withOptions(['verify' => false])->get($url_insight, $params_insights);
                $reaction_count = data_get($insights->json(), 'reactions.summary.total_count', 0);
                $comment_count = data_get($insights->json(), 'comments.summary.total_count', 0);
                $shares_count =  data_get($insights->json(), 'shares.count', 0);
                $engagement = $reaction_count + $comment_count + $shares_count;

                $post['data'][] = [
                    'id' => $key['id_post'],
                    'reach' => $reachValue ?: $jumlah_reach,
                    'judul' => $key['judul'],
                    'pemirsa' => $pemirsaValue,
                    'tanggal_posting' => date('d M Y', strtotime($key['tanggal_posting'])),
                    'timestamp' => strtotime($key['timestamp']),
                    'impresi' => $impresiValue,
                    'reaction' => $reaction_count,
                    'comments' => $comment_count,
                    'shares' => $shares_count,
                    'engagement' => $engagement,
                    'link' => $key['link']
                ];
            }
            $url_follower = "https://graph.facebook.com/{$version}/{$pageId}";
            $params_follower = [
                'fields' => "followers_count",
                'access_token' => $token,
            ];

            $follower_count = Http::timeout(120)->withOptions(['verify' => false])->get($url_follower, $params_follower);
            $post['followers'][] = $follower_count->json()['followers_count'] ?? 0;
        } else {
            Log::warning('Facebook Graph API menolak permintaan feed.', [
                'status' => $response->status(),
                'error' => $response->json('error.message'),
            ]);
        }
        return array_merge(['data' => [], 'followers' => [0]], $post);
    }
}
