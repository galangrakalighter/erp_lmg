<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class FacebookService
{
    protected string $token;

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
