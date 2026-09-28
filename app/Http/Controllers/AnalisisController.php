<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InstagramService as ServicesInstagramService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ProdukClient;
use App\Services\YoutubeService as Youtube;
use App\Services\FacebookService as Facebook;

class AnalisisController extends Controller
{
    protected $instagram;
    protected $facebook;
    protected $youtube;
    public function __construct(ServicesInstagramService $instagram, Facebook $facebook, Youtube $youtube)
    {
        $this->instagram = $instagram;
        $this->facebook = $facebook;
        $this->youtube = $youtube;
    }
    public function index($produk, $platform, Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $sinceDate = $startDate
            ? Carbon::createFromFormat('d M Y', $startDate)->startOfDay()
            : null;

        $untilDate = $endDate
            ? Carbon::createFromFormat('d M Y', $endDate)->endOfDay()
            : null;

        // Unix timestamp khusus pemanggilan API
        $since = $sinceDate?->timestamp;
        $until = $untilDate?->timestamp;
        $fetch = $since !== null && $until !== null;
        
        $produkBaru = ProdukClient::findOrFail($produk);
        $title = 'Analisis Konten ' . $produkBaru->nama;

        

        
        $data = DB::table('data_jasa')
            ->join('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
            ->join('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
            ->join('status', 'status.id', '=', 'data_jasa.status_id')
            ->join('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
            ->join('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
            ->where('data_jasa.produk_client_id', $produk)
            ->when($sinceDate, function ($query) use ($sinceDate) {
                $query->where(
                    'data_jasa.tanggal_posting',
                    '>=',
                    $sinceDate->toDateTimeString()
                );
            })
            ->when($untilDate, function ($query) use ($untilDate) {
                $query->where(
                    'data_jasa.tanggal_posting',
                    '<=',
                    $untilDate->toDateTimeString()
                );
            })
            ->select(
                'data_jasa.*',
                'type_produk.type',
                'strategy.strategy',
                'status.nama_status',
                'pilar.pilar',
                'hooks.hooks'
            )
            ->orderBy('data_jasa.tanggal_posting', 'asc')
            ->get();
        $index = array_search($platform, $produkBaru->access_token['platform']);
        $platformToken = $produkBaru->platformAccessToken($platform)
            ?? ($index !== false ? ($produkBaru->access_token['access'][$index] ?? null) : null);

        switch ($platform) {
            // PENGAMBILAN DATA DARI API GRAPH IG
            // DATA DATA YANG YANG DIAMBIL ADA DI INSTAGRAM SERVICE
            case 'Instagram':
                $posts = [];
                $maxFollowers = 0;
                foreach ($this->instagram->getMedia($since, $until, $fetch, $platformToken) as $post) {
                    $insightsRaw = $this->instagram->getInsights($post['id'], $since, $until, $fetch, $platformToken);
                    $frekuensi = ($post['reach_count'] > 0) ? round($post['impresi'] / $post['reach_count']) : 0;
                    // Data Untuk Chart
                    $posts[] = [
                        'judul'    => $post['judul'] ?? null,
                        'link'    => $post['link_content'],
                        'jenis'   => $post['jenis_konten'],
                        'tanggal_posting'    => date('d M Y', strtotime($post['tanggal_posting'])),
                        'timestamp' => strtotime($post['tanggal_posting']),
                        'likes'        => $post['like_count'],
                        'comments'     => $post['comment_count'],
                        'saved'        => $post['save_count'] ?? 0,
                        'shares'       => $post['share_count'] ?? 0,
                        'reach'        => $post['reach_count'] ?? 0,
                        'impressions'  => $post['impresi'] ?? 0,
                        'frekuensi'    => $frekuensi,
                        'engagement'   => $post['like_count'] + $post['comment_count'] + ($post['save_count'] ?? 0) + ($post['share_count'] ?? 0),
                        // 'followers1' => $followers1
                    ];
                    // dd($insightsRaw[0]);
                    $currentMaxFollowers = collect($insightsRaw[0] ?? [])->max('followers_gained') ?? 0;
                    if ($currentMaxFollowers > $maxFollowers) {
                        $maxFollowers = $currentMaxFollowers;
                    }
                }
                $posts = collect($posts ?? [])->sortBy('timestamp')->values()->all();
                $date = [];
                $views = [];
                $reach = [];
                foreach($posts as $post){
                    $date[] = $post['tanggal_posting'];
                    $reach[] = $post['reach'];
                    $views[] = $post['impressions'];
                }
                if(!empty($posts)){
                    $maxReach = max(array_column($posts, 'reach'));
                    $maxViews = max(array_column($posts, 'impressions'));
                    $maxLikes = max(array_column($posts, 'likes'));
                    $maxComment = max(array_column($posts, 'comments'));
                    $maxShares = max(array_column($posts, 'shares'));
                    $maxEngangement = max(array_column($posts, 'engagement'));
                    
                    $totalReach = array_sum(array_column($posts, 'reach'));
                    $totalImpressions = array_sum(array_column($posts, 'impressions'));
                    $totalComments = array_sum(array_column($posts, 'comments'));
                    $totalLikes = array_sum(array_column($posts, 'likes'));
                    $totalEngagement = $totalLikes + $totalComments + array_sum(array_column($posts, 'shares'));
                }else{
                    $maxReach = 0;
                    $maxViews = 0;
                    $maxLikes = 0;
                    $maxComment = 0;
                    $maxShares = 0;
                    $maxEngangement = 0;
                    
                    $totalReach = 0;
                    $totalImpressions = 0;
                    $totalComments = 0;
                    $totalLikes = 0;
                    $totalEngagement = 0;
                    $insightsRaw[0][0]['followers_gained'] = 0;
                    $insightsRaw[1]['Followers_sebulan'] = 0;
                }
                return view('analisis.instagram', compact(
                    'posts', 
                    'data', 
                    'views', 
                    'reach', 
                    'date', 
                    'maxReach', 
                    'maxViews', 
                    'maxLikes', 
                    'maxComment', 
                    'maxShares', 
                    'maxFollowers',
                    'maxEngangement',
                    'totalReach', 
                    'totalImpressions', 
                    'totalEngagement', 
                    'insightsRaw', 
                    'totalLikes', 
                    'totalComments',
                    'title'
                ));
                break;
            case 'Facebook':
                $data = $this->facebook->getPost($since, $until, $platformToken, $fetch);
                $dataBaru = $data['data'];
                $dataBaru = collect($dataBaru ?? [])->sortBy('timestamp')->values()->all();
                $totalReach = array_sum(array_column($dataBaru, 'reach'));
                $totalEngagement = array_sum(array_column($dataBaru, 'engagement'));
                $totalReaction = array_sum(array_column($dataBaru, 'reaction'));
                $totalImpressions = array_sum(array_column($dataBaru, 'impresi'));
                $maxReach = max(array_column($dataBaru, 'reach'));
                $maxViews = max(array_column($dataBaru, 'impresi'));
                $maxLikes = max(array_column($dataBaru, 'reaction'));
                $maxComment = max(array_column($dataBaru, 'comments'));
                $maxShares = max(array_column($dataBaru, 'shares'));
                $maxEngangement = max(array_column($dataBaru, 'engagement'));
                $jumlah_follower = $data['followers'][0];
                return view('analisis.facebook', compact(
                    'title',
                    'data',
                    'dataBaru',
                    'totalReach',
                    'totalEngagement',
                    'totalReaction',
                    'totalImpressions',
                    'jumlah_follower',
                    'maxReach', 
                    'maxViews', 
                    'maxLikes', 
                    'maxComment', 
                    'maxShares', 
                    'maxEngangement',
                ));
                break;
            case 'Youtube':
                $this->youtube->getAnalyticsReport(null, null, $platformToken);
                break;
            default:
                # code...
                break;
        }
    }
}
