<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
class PDFExport
{
    protected string $token;

    /**
     * Mengambil media dengan penambahan Timeout 120 detik
     */
    public function getDataManajemen(
        $id,
        $startDate = null,
        $endDate = null,
        array $platforms = [],
        string $contentSource = 'organic',
        ?PlatformMirroringService $mirroring = null
    )
    {
        $startDate = !empty($startDate) ? $startDate : now()->startOfMonth()->toDateString();
        
        $endDate = !empty($endDate) ? $endDate : now()->endOfMonth()->toDateString();

        $query = DB::table("data_jasa")
            ->leftJoin('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
            ->leftJoin('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
            ->leftJoin('status', 'status.id', '=', 'data_jasa.status_id')
            ->leftJoin('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
            ->leftJoin('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
            ->leftJoin('jenis_body', 'jenis_body.id', '=', 'data_jasa.body_id')
            ->leftJoin('jenis_cta', 'jenis_cta.id', '=', 'data_jasa.cta_id')
            ->where('data_jasa.produk_client_id', $id)
            ->when($platforms !== [], fn ($query) => $query->whereIn('data_jasa.platform', $platforms))
            ->when(count($platforms) > 1 && $mirroring, fn ($query) => $query->whereNotIn(
                'data_jasa.id',
                $mirroring->copiedTargetIds((int) $id, 'data_jasa')
            ))
            ->where('data_jasa.content_source', $contentSource)
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

    public function getDataMarket($id, array $platforms = [], ?PlatformMirroringService $mirroring = null)
    {
        $data = [];
        $jumlah = [];
        $getData = ['analize_4p', 'analize_swot', 'positioning', 'segmentasi', 'target_audience'];
        for ($i=0; $i < count($getData); $i++) { 
          $market = DB::table($getData[$i])
                    ->join('produk_client', 'produk_client.id', '=', $getData[$i].'.produk_client_id')
                    ->where($getData[$i].'.produk_client_id', $id)
                    ->when($platforms !== [], fn ($query) => $query->whereIn($getData[$i].'.platform', $platforms))
                    ->when(count($platforms) > 1 && $mirroring, fn ($query) => $query->whereNotIn(
                        $getData[$i].'.id',
                        $mirroring->copiedTargetIds((int) $id, $getData[$i])
                    ))
                    ->select('produk_client.nama', $getData[$i].'.*')
                    ->get();
          array_push($data, $market);
          
          $jumlahData = DB::table($getData[$i])
                    ->select('produk_client_id', DB::raw('count(*) as total'))
                    ->where('produk_client_id', $id)
                    ->when($platforms !== [], fn ($query) => $query->whereIn('platform', $platforms))
                    ->when(count($platforms) > 1 && $mirroring, fn ($query) => $query->whereNotIn(
                        'id',
                        $mirroring->copiedTargetIds((int) $id, $getData[$i])
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

    public function getDataBranding($id, array $platforms = [], ?PlatformMirroringService $mirroring = null)
    {
        $data = [];
        $getData = ['brand_image', 'brand_value', 'brand_tagline', 'gaya_komunikasi', 'komposisi', 'audio_brand', 'alat_branding'];
        for ($i=0; $i < count($getData); $i++) { 
          $branding = DB::table($getData[$i])
                    ->join('produk_client', 'produk_client.id', '=', $getData[$i].'.produk_client_id')
                    ->where($getData[$i].'.produk_client_id', $id)
                    ->when($platforms !== [], fn ($query) => $query->whereIn($getData[$i].'.platform', $platforms))
                    ->when(count($platforms) > 1 && $mirroring, fn ($query) => $query->whereNotIn(
                        $getData[$i].'.id',
                        $mirroring->copiedTargetIds((int) $id, $getData[$i])
                    ))
                    ->select('produk_client.nama', $getData[$i].'.*')
                    ->get();
          array_push($data, $branding);
        }
        $identify = DB::table('brand_identify')
          ->join('produk_client', 'produk_client.id', '=', 'brand_identify.produk_client_id')
          ->select("produk_client.nama", 'brand_identify.*')
          ->where("brand_identify.produk_client_id", $id)
          ->when($platforms !== [], fn ($query) => $query->whereIn('brand_identify.platform', $platforms))
          ->when(count($platforms) > 1 && $mirroring, fn ($query) => $query->whereNotIn(
              'brand_identify.id',
              $mirroring->copiedTargetIds((int) $id, 'brand_identify')
          ))
          ->first();
          array_push($data, $identify);
          
        $moodboard = DB::table('moodboard')
          ->join('produk_client', 'produk_client.id', '=', 'moodboard.produk_client_id')
          ->select("produk_client.nama", 'moodboard.*')
          ->where("moodboard.produk_client_id", $id)
          ->when($platforms !== [], fn ($query) => $query->whereIn('moodboard.platform', $platforms))
          ->when(count($platforms) > 1 && $mirroring, fn ($query) => $query->whereNotIn(
              'moodboard.id',
              $mirroring->copiedTargetIds((int) $id, 'moodboard')
          ))
          ->first();
          array_push($data, $moodboard);
        $result = [
          "data" => $data
        ];
        return $result;
    }
}
