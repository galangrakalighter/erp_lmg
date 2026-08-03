<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
class PDFExport
{
    protected string $token;

    /**
     * Mengambil media dengan penambahan Timeout 120 detik
     */
    public function getDataManajemen($id, $startDate = null, $endDate = null)
    {
        $startDate = !empty($startDate) ? $startDate : now()->startOfMonth()->toDateString();
        
        $endDate = !empty($endDate) ? $endDate : now()->endOfMonth()->toDateString();

        $query = DB::table("data_jasa")
            ->join('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
            ->join('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
            ->join('status', 'status.id', '=', 'data_jasa.status_id')
            ->join('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
            ->join('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
            ->join('jenis_body', 'jenis_body.id', '=', 'data_jasa.body_id')
            ->join('jenis_cta', 'jenis_cta.id', '=', 'data_jasa.cta_id')
            ->where('data_jasa.produk_client_id', $id)
            ->whereBetween('data_jasa.tanggal_posting', [$startDate, $endDate]);

        return $query->select(
                'data_jasa.*', 
                'type_produk.type', 
                'strategy.strategy', 
                'status.nama_status', 
                'pilar.pilar', 
                'hooks.hooks', 
                'jenis_body.nama_body', 
                'jenis_cta.nama_cta'
            )
            ->orderBy('data_jasa.tanggal_posting', 'ASC')
            ->get();
    }

    public function getDataMarket($id)
    {
        $data = [];
        $jumlah = [];
        $getData = ['analize_4p', 'analize_swot', 'positioning', 'segmentasi', 'target_audience'];
        for ($i=0; $i < count($getData); $i++) { 
          $market = DB::table($getData[$i])
                    ->join('produk_client', 'produk_client.id', '=', $getData[$i].'.produk_client_id')
                    ->where($getData[$i].'.produk_client_id', $id)
                    ->select('produk_client.nama', $getData[$i].'.*')
                    ->get();
          array_push($data, $market);
          
          $jumlahData = DB::table($getData[$i])
                    ->select('produk_client_id', DB::raw('count(*) as total'))
                    ->where('produk_client_id', $id)
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

    public function getDataBranding($id)
    {
        $data = [];
        $getData = ['brand_image', 'brand_value', 'brand_tagline', 'gaya_komunikasi', 'komposisi', 'audio_brand', 'alat_branding'];
        for ($i=0; $i < count($getData); $i++) { 
          $branding = DB::table($getData[$i])
                    ->join('produk_client', 'produk_client.id', '=', $getData[$i].'.produk_client_id')
                    ->where($getData[$i].'.produk_client_id', $id)
                    ->select('produk_client.nama', $getData[$i].'.*')
                    ->get();
          array_push($data, $branding);
        }
        $identify = DB::table('brand_identify')
          ->join('produk_client', 'produk_client.id', '=', 'brand_identify.produk_client_id')
          ->select("produk_client.nama", 'brand_identify.*')
          ->where("brand_identify.produk_client_id", $id)
          ->first();
          array_push($data, $identify);
          
        $moodboard = DB::table('moodboard')
          ->join('produk_client', 'produk_client.id', '=', 'moodboard.produk_client_id')
          ->select("produk_client.nama", 'moodboard.*')
          ->where("moodboard.produk_client_id", $id)
          ->first();
          array_push($data, $moodboard);
        $result = [
          "data" => $data
        ];
        return $result;
    }
}