<?php

namespace App\Http\Controllers;

use App\Services\InstagramService as ServicesInstagramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ProdukClient;
class InstagramController extends Controller
{

    protected $instagram;
    public function __construct(ServicesInstagramService $instagram)
    {
        $this->instagram = $instagram;
    }
    public function index($produk, $platform,Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $since = $startDate ? Carbon::createFromFormat('d M Y', $startDate)->startOfDay()->timestamp : null;
        $until = $endDate ? Carbon::createFromFormat('d M Y', $endDate)->endOfDay()->timestamp : null;
        $fetch = false;
        if($since != null && $until != null){
            $fetch = true;
        }
        
        $produkBaru = ProdukClient::findOrFail($produk);
        $title = 'Analisis Konten ' . $produkBaru->nama;

        
        $data = DB::table("data_jasa")
        ->join('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
        ->join('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
        ->join('status', 'status.id', '=', 'data_jasa.status_id')
        ->join('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
        ->join('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
        ->where('data_jasa.produk_client_id', $produk)
            ->whereNull('data_jasa.archived_at')
        ->select('data_jasa.*', 'type_produk.type', 'strategy.strategy', 'status.nama_status', 'pilar.pilar', 'hooks.hooks')
        ->paginate(10);
        
        switch ($platform) {
            // PENGAMBILAN DATA DARI API GRAPH IG
            // DATA DATA YANG YANG DIAMBIL ADA DI INSTAGRAM SERVICE
            case 'Instagram':
                $posts = [];
                $maxFollowers = 0;
                $token = $produkBaru->platformAccessToken($platform);
                $accountId = $produkBaru->platformAccountId($platform);

                foreach ($this->instagram->getMedia($since, $until, $fetch, $token, $accountId) as $post) {
                    $insightsRaw = $this->instagram->getInsights($post['id'], $since, $until, $fetch, $token, $accountId);
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
                # code...
                break;
            default:
                # code...
                break;
        }
    }
}
