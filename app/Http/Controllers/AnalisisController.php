<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InstagramService as ServicesInstagramService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ProdukClient;
use App\Services\YoutubeService as Youtube;
use App\Services\FacebookService as Facebook;
use App\Services\MetaAdsService;

class AnalisisController extends Controller
{
    protected $instagram;
    protected $facebook;
    protected $youtube;
    protected $metaAds;
    public function __construct(ServicesInstagramService $instagram, Facebook $facebook, Youtube $youtube, MetaAdsService $metaAds)
    {
        $this->instagram = $instagram;
        $this->facebook = $facebook;
        $this->youtube = $youtube;
        $this->metaAds = $metaAds;
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
        $legacyAccess = is_array($produkBaru->access_token) ? $produkBaru->access_token : [];
        $index = array_search($platform, $legacyAccess['platform'] ?? [], true);
        $platformToken = $produkBaru->platformAccessToken($platform)
            ?? ($index !== false ? ($legacyAccess['access'][$index] ?? null) : null);
        $platformAccountId = $produkBaru->platformAccountId($platform);
        $paidPosts = [];
        $paidError = null;
        $paidCurrency = 'IDR';
        $adsConnection = $produkBaru->metaAdsConnection();
        if (! $adsConnection) {
            $paidError = 'Pilih akun iklan Meta pada Edit Client untuk menampilkan konten Paid.';
        } elseif (! in_array('ads_read', $adsConnection['scopes'] ?? [], true)) {
            $paidError = 'Koneksi Meta belum memiliki izin ads_read. Hubungkan ulang Meta setelah izin tersebut ditambahkan.';
        } else {
            $paidResult = $this->metaAds->getPaidPosts(
                $adsConnection['id'],
                $adsConnection['access_token'],
                $platform,
                $sinceDate,
                $untilDate,
            );
            $paidPosts = $paidResult['data'] ?? [];
            $paidError = $paidResult['error'] ?? null;
            $paidCurrency = $adsConnection['currency'] ?: 'IDR';
        }

        switch ($platform) {
            // PENGAMBILAN DATA DARI API GRAPH IG
            // DATA DATA YANG YANG DIAMBIL ADA DI INSTAGRAM SERVICE
            case 'Instagram':
                if (! $platformToken || ! $platformAccountId) {
                    return response()->view('analisis.connection-error', [
                        'platform' => 'Instagram',
                        'message' => 'Koneksi Meta belum menemukan Instagram Business. Pastikan Instagram Professional terhubung ke Facebook Page, lalu hubungkan ulang Meta dan pilih Page tersebut.',
                    ], 422);
                }
                $posts = [];
                $maxFollowers = 0;
                foreach ($this->instagram->getMedia($since, $until, $fetch, $platformToken, $platformAccountId) as $post) {
                    $insightsRaw = $this->instagram->getInsights($post['id'], $since, $until, $fetch, $platformToken, $platformAccountId);
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
                    'title',
                    'paidPosts',
                    'paidError',
                    'paidCurrency'
                ));
                break;
            case 'Facebook':
                if (! $platformToken || ! $platformAccountId) {
                    return response()->view('analisis.connection-error', [
                        'platform' => 'Facebook',
                        'message' => 'Belum ada Facebook Page yang dapat digunakan. Hubungkan ulang Meta dan pastikan izin Page diberikan.',
                    ], 422);
                }
                $data = $this->facebook->getPost($since, $until, $platformToken, $fetch, $platformAccountId);
                $dataBaru = $data['data'] ?? [];
                $dataBaru = collect($dataBaru ?? [])->sortBy('timestamp')->values()->all();
                $totalReach = array_sum(array_column($dataBaru, 'reach'));
                $totalEngagement = array_sum(array_column($dataBaru, 'engagement'));
                $totalReaction = array_sum(array_column($dataBaru, 'reaction'));
                $totalImpressions = array_sum(array_column($dataBaru, 'impresi'));
                $maxReach = $dataBaru ? max(array_column($dataBaru, 'reach')) : 0;
                $maxViews = $dataBaru ? max(array_column($dataBaru, 'impresi')) : 0;
                $maxLikes = $dataBaru ? max(array_column($dataBaru, 'reaction')) : 0;
                $maxComment = $dataBaru ? max(array_column($dataBaru, 'comments')) : 0;
                $maxShares = $dataBaru ? max(array_column($dataBaru, 'shares')) : 0;
                $maxEngangement = $dataBaru ? max(array_column($dataBaru, 'engagement')) : 0;
                $jumlah_follower = $data['followers'][0] ?? 0;
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
                    'paidPosts',
                    'paidError',
                    'paidCurrency',
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
