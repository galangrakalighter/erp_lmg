<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    protected string $token;
    private ?string $lastError = null;

    public function __construct(private readonly PlatformErrorPresenter $errorPresenter)
    {
    }

    public function consumeError(): ?string
    {
        $error = $this->lastError;
        $this->lastError = null;

        return $error;
    }

    public function getAudienceBreakdowns($token = null, $accountId = null): array
    {
        $empty = ['gender' => [], 'age' => [], 'country' => [], 'city' => []];
        if (! filled($token) || ! filled($accountId)) {
            return $empty;
        }

        $version = config('services.meta.graph_version', 'v23.0');
        $url = "https://graph.facebook.com/{$version}/{$accountId}/insights";
        $result = $empty;

        foreach (array_keys($empty) as $breakdown) {
            try {
                $response = Http::timeout(45)->withOptions(['verify' => false])->get($url, [
                    'metric' => 'follower_demographics',
                    'period' => 'lifetime',
                    'metric_type' => 'total_value',
                    'breakdown' => $breakdown,
                    'access_token' => $token,
                ]);
                if ($response->successful()) {
                    $result[$breakdown] = $this->parseDemographicBreakdown($response->json('data.0', []));
                } else {
                    Log::info('Breakdown audiens Instagram tidak tersedia.', ['breakdown' => $breakdown, 'error' => $response->json('error.message')]);
                }
            } catch (\Throwable $exception) {
                Log::info('Breakdown audiens Instagram gagal dimuat.', ['breakdown' => $breakdown, 'message' => $exception->getMessage()]);
            }
        }

        return $result;
    }

    private function parseDemographicBreakdown(array $metric): array
    {
        $output = [];
        foreach (data_get($metric, 'total_value.breakdowns.0.results', []) as $row) {
            $label = implode(' / ', array_filter((array) ($row['dimension_values'] ?? []), 'strlen'));
            if ($label !== '') {
                $output[$label] = (float) ($row['value'] ?? 0);
            }
        }

        if ($output === []) {
            foreach ((array) data_get($metric, 'values.0.value', []) as $label => $value) {
                if (is_numeric($value)) {
                    $output[(string) $label] = (float) $value;
                }
            }
        }

        arsort($output);
        return array_slice($output, 0, 10, true);
    }

    /**
     * Mengambil media dengan penambahan Timeout 120 detik
     */
    public function getMedia($since = null, $until = null, bool $fetch = false, $token = null, $accountId = null)
    {
        $this->lastError = null;
        if (! filled($token) || ! filled($accountId)) {
            Log::warning('Instagram OAuth belum memiliki token atau Instagram Business Account ID.');
            return [];
        }

        $allMedia = [];
        $start = $since ?? strtotime("-1 year");
        $end = $until ?? time();
        $version = config('services.meta.graph_version', 'v23.0');
        $url = "https://graph.facebook.com/{$version}/{$accountId}/media";

        $params = [
            'fields' => 'id,caption,permalink,timestamp,media_type,like_count,comments_count,insights.metric(reach,views,saved,shares)',
            'since' => $start,
            'until' => $end,
            'access_token' => $token,
            'limit' => $fetch ? 100 : 15,
        ];
        
        try {
            // MENAMBAHKAN .timeout(120) agar koneksi tidak terputus saat paging banyak data
            $response = Http::timeout(120)->withOptions(['verify' => false])->get($url, $params);
            
            if ($response->successful()) {
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
            } else {
                $this->lastError = $this->errorPresenter->message(
                    'Instagram',
                    $response->json('error.message'),
                    (int) $response->json('error.code')
                );
                Log::warning('Instagram Graph API menolak permintaan media.', [
                    'status' => $response->status(),
                    'error' => $response->json('error.message'),
                ]);
            }

            if ($since || $until) {
                $allMedia = array_filter($allMedia, function($item) use ($since, $until) {
                    $itemTime = strtotime($item['timestamp']);
                    $checkSince = $since ? ($itemTime >= $since) : true;
                    $checkUntil = $until ? ($itemTime <= $until) : true;
                    return $checkSince && $checkUntil;
                });
            }

            $data = [];
            foreach ($allMedia as $item) {
                $caption = $item['caption'] ?? "";
                $judul = explode("\n", $caption)[0] ?? 'No Title';
                $insightData = $item['insights']['data'] ?? [];
                $insights = collect($insightData)->mapWithKeys(function ($insight) {
                    return [$insight['name'] => $insight['values'][0]['value'] ?? 0];
                });

                $data[] = [
                    'id' => $item['id'],
                    'judul' => $judul,
                    'tanggal_posting' => Carbon::parse($item['timestamp'])->format('Y-m-d'),
                    'timestamp' => strtotime($item['timestamp']),
                    'jenis_konten' => $item['media_type'] ?? 'UNKNOWN',
                    'link_content' => $item['permalink'] ?? '#',
                    'reach_count' => $insights->get('reach', 0),
                    'save_count' => $insights->get('saved', 0),
                    'share_count' => $insights->get('shares', 0),
                    'like_count' => $item['like_count'] ?? 0,
                    'comment_count' => $item['comments_count'] ?? 0,
                    'impresi' => $insights->get('views', $insights->get('impressions', 0))
                ];
            }
            return collect($data)->sortBy('timestamp')->values()->all();
        } catch (\Exception $e) {
            Log::error("Instagram Media Timeout/Error: " . $e->getMessage());
            $this->lastError = $this->errorPresenter->network('Instagram');
            return [];
        }
    }

    /**
     * Mengambil Insights dengan optimasi Timeout dan pengecekan Paging
     */
    public function getInsights(string $mediaId, $since = null, $until = null, bool $fetch = false, $token = null, $accountId = null)
    {   
        if (! filled($token) || ! filled($accountId)) {
            return [[], ['Followers_sebulan' => 0], []];
        }

        try {
            $version = config('services.meta.graph_version', 'v23.0');
            $ownerId = $accountId;
            
            $start = $since ?? strtotime("-1 year");
            $end = $until ?? time();

            // 2. Ambil semua timestamp media
            $resMedia = Http::timeout(60)->withOptions(['verify' => false])
                ->get("https://graph.facebook.com/{$version}/{$ownerId}/media", [
                    'fields' => 'timestamp',
                    'limit' => $fetch ? 100 : 15,
                    'since' => $start,
                    'until' => $end,
                    'access_token' => $token,
                ]);
            
            $data_waktu = $resMedia->json()['data'] ?? [];
            if (empty($data_waktu)) return [[], ['Followers_sebulan' => 0], []];

            // 3. Ambil Data Follower dengan Timeout Panjang (Karena data 1 tahun berat)
            $allFollowerValues = [];
            $urlFollower = "https://graph.facebook.com/{$version}/{$ownerId}/insights";
            $paramsFollower = [
                'metric' => 'follower_count',
                'period' => 'day',
                'since' => $start,
                'until' => $end,
                'access_token' => $token,
            ];

            $resFollower = Http::timeout(120)->withOptions(['verify' => false])->get($urlFollower, $paramsFollower);

            if ($resFollower->successful()) {
                $json = $resFollower->json();
                $allFollowerValues = $json['data'][0]['values'] ?? [];

                while (isset($json['paging']['next'])) {
                    // Beri timeout di setiap paging
                    $resNext = Http::timeout(60)->withOptions(['verify' => false])->get($json['paging']['next']);
                    if ($resNext->successful()) {
                        $json = $resNext->json();
                        $newValues = $json['data'][0]['values'] ?? [];
                        $allFollowerValues = array_merge($allFollowerValues, $newValues);
                    } else { break; }
                }
            }

            // 4. Hitung Follower per Video
            $resultFollowersPerVideo = [];
            for ($i = 0; $i < count($data_waktu); $i++) {
                $currentPostDate = strtotime($data_waktu[$i]['timestamp']);
                $nextPostDate = ($i > 0) ? strtotime($data_waktu[$i-1]['timestamp']) : time();

                $sumGained = 0;
                foreach ($allFollowerValues as $day) {
                    $dayTime = strtotime($day['end_time']);
                    if ($dayTime >= $currentPostDate && $dayTime < $nextPostDate) {
                        $sumGained += $day['value'];
                    }
                }
                $resultFollowersPerVideo[] = ['followers_gained' => $sumGained];
            }

            // 5. Lokasi (Demographics)
            $resLoc = Http::timeout(30)->withOptions(['verify' => false])
                ->get("https://graph.facebook.com/{$version}/{$ownerId}/insights", [
                    'metric' => 'follower_demographics',
                    'period' => 'lifetime',
                    'breakdown' => 'city',
                    'access_token' => $token,
                ]);
            
            $locationData = [];
            if ($resLoc->successful()) {
                $rawLoc = $resLoc->json()['data'][0]['values'][0]['value'] ?? [];
                arsort($rawLoc);
                $locationData = array_slice($rawLoc, 0, 10, true);
            }

            return [
                0 => $resultFollowersPerVideo,
                1 => ['Followers_sebulan' => array_sum(array_column($allFollowerValues, 'value'))],
                2 => ['Top_Locations' => $locationData]
            ];

        } catch (\Exception $e) {
            Log::error("Instagram Insights Timeout/Error: " . $e->getMessage());
            return [[], ['Followers_sebulan' => 0], []];
        }
    }   
}
