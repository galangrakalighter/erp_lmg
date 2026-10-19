<?php

namespace App\Services\MarketResearch;

use LaravelEasyRepository\ServiceApi;
use App\Repositories\MarketResearch\MarketResearchRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class MarketResearchServiceImplement extends ServiceApi implements MarketResearchService{

    /**
     * set title message api for CRUD
     * @param string $title
     */
     protected string $title = "";
     /**
     * uncomment this to override the default message
     * protected string $create_message = "";
     * protected string $update_message = "";
     * protected string $delete_message = "";
     */

     /**
     * don't change $this->mainRepository variable name
     * because used in extends service class
     */

    public function getMarket(int $id, string|array $platform)
    {
        $platforms = (array) $platform;
        $data = [];
        $jumlah = [];
        $getData = ['analize_4p', 'analize_swot', 'positioning', 'segmentasi', 'target_audience'];

        for ($i=0; $i < count($getData); $i++) { 
          $market = DB::table($getData[$i])
                    ->join('produk_client', 'produk_client.id', '=', $getData[$i].'.produk_client_id')
                    ->where($getData[$i].'.produk_client_id', $id)
                    ->when(Schema::hasColumn($getData[$i], 'archived_at'), fn ($query) => $query->whereNull($getData[$i].'.archived_at'))
                    ->whereIn($getData[$i].'.platform', $platforms)
                    ->when(count($platforms) > 1, fn ($query) => $query->whereNotIn(
                        $getData[$i].'.id',
                        DB::table('platform_mirror_copies')->where('client_id', $id)->where('table_name', $getData[$i])->pluck('target_id')
                    ))
                    ->select('produk_client.nama', $getData[$i].'.*')
                    ->get();
          array_push($data, $market);
          
          $jumlahData = DB::table($getData[$i])
                    ->select('produk_client_id', DB::raw('count(*) as total'))
                    ->where('produk_client_id', $id)
                    ->when(Schema::hasColumn($getData[$i], 'archived_at'), fn ($query) => $query->whereNull('archived_at'))
                    ->whereIn('platform', $platforms)
                    ->when(count($platforms) > 1, fn ($query) => $query->whereNotIn(
                        'id',
                        DB::table('platform_mirror_copies')->where('client_id', $id)->where('table_name', $getData[$i])->pluck('target_id')
                    ))
                    ->groupBy('produk_client_id')
                    ->get();

          array_push($jumlah, $jumlahData);
        }
        $result = [
          "data" => $data,
          "jumlah" => $jumlah
        ];
        return $result;
    }
    // Define your custom methods :)
}
