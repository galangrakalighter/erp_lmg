<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\CarbonPeriod;
use App\Models\ProdukClient;

class ManajemenKontenController extends Controller
{
    public function index(Request $request, $produk, $platform){
        $produk = DB::table('produk_client')->where('id', $produk)->first();
        $kategory = DB::table("kategori_jasa")->where('id', $produk->kategori_jasa_id)->first();
        $query = DB::table("data_jasa")
        ->leftjoin('type_produk', 'type_produk.id', '=', 'data_jasa.type_produk_id')
        ->leftjoin('strategy', 'strategy.id', '=', 'data_jasa.strategy_id')
        ->leftjoin('status', 'status.id', '=', 'data_jasa.status_id')
        ->leftjoin('pilar', 'pilar.id', '=', 'data_jasa.pilar_id')
        ->leftjoin('hooks', 'hooks.id', '=', 'data_jasa.hooks_id')
        ->leftjoin('jenis_body', 'jenis_body.id', '=', 'data_jasa.body_id')
        ->leftjoin('jenis_cta', 'jenis_cta.id', '=', 'data_jasa.cta_id')
        ->leftjoin('users', 'users.id', '=', 'data_jasa.pic')
        ->where('data_jasa.produk_client_id', $produk->id)
        ->where('data_jasa.platform', $platform)
        ->select('data_jasa.*', 'type_produk.type', 'strategy.strategy', 'status.nama_status', 'pilar.pilar', 'hooks.hooks', 'jenis_body.nama_body', 'jenis_cta.nama_cta', 'users.id as users_id', 'users.email as vic_email');
        if ($request->filled('start_date')) {
            $query->whereDate('data_jasa.tanggal_posting', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('data_jasa.tanggal_posting', '<=', $request->end_date);
        }
        $data = $query->orderBy('data_jasa.tanggal_posting', 'ASC')->get();

        // LIST TABLE YANG AKAN DIKONDISIKAN
        $table = ['strategy', 'status', 'pilar', 'hooks', 'type_produk', 'jenis_body', 'jenis_cta'];

        $option = [];

        $title = "Manajemen Konten " . $produk->nama;

        $halaman = "Manajemen Konten";

        foreach($table as $tab){
            $datatable = DB::table($tab);
            if($kategory->id == 1){
                $option[$tab] = $datatable->where('kategori_id', 1);
            }else{
                $option[$tab] = $datatable->where('kategori_id', 2);
            }
            $option[$tab] = $datatable->where('status', 1)->get();
        }
        $users = User::whereJsonContains('client', $produk->id)
                ->leftJoin('data_jasa', function($join) use ($produk) {
                    $join->on('data_jasa.pic', '=', 'users.id')
                        // Pindahkan filter ke sini agar Left Join tidak rusak
                        ->where('data_jasa.produk_client_id', $produk->id);
                })
                ->select(
                    'users.id', 
                    'users.email', 
                    'users.name',
                    DB::raw('SUM(COALESCE(data_jasa.menit, 0)) as total_beban')
                )
                ->where('users.role', '!=', 'head_of_division')
                ->groupBy('users.id', 'users.email', 'users.name')
                ->get();
        return view("manajemen.index", compact('data', 'produk', 'option', 'title', 'kategory', 'platform', 'halaman', 'users'));
    }

    public function store($id, Request $request){
        $id = (int)$id;
        // Request yang valuenya diambil dari table yang berbeda
        $arr = ['type', 'strategy', 'pilar', 'hooks',   'status', 'jenis_body', 'jenis_cta'];
        $value = [];
        foreach($arr as $d){
            $value[$d] = (int)$request->$d; 
        }
        $request->pic = (int)$request->pic;
        $request->prioritas = (int)$request->prioritas;

        // Validasi Terlebih Dahulu
        $request->validate([
            'judul'             => 'required|string|max:255',
            'inspirasi'         => 'nullable|string',
            'menit'             => 'nullable|integer',
            'pic'               => 'nullable|integer',
            'prioritas'         => 'nullable|integer',
            'platform'          => 'nullable|string',
            'type'              => 'required|integer',
            'strategy'          => 'required|integer',
            'pilar'             => 'required|integer',
            'hooks'             => 'required|integer',
            'jenis_body'        => 'required|integer',
            'jenis_cta'         => 'required|integer',
            'warna'             => 'nullable|string',
            'komposisi'         => 'nullable|string',
            'tanggal_mulai'     => 'nullable|date',
            'tanggal_posting'   => 'nullable|date',
            'tanggal_deadline'  => 'nullable|date',
            'link'              => 'nullable|string',
            'status'            => 'required|integer',
            'note'              => 'nullable|string',
        ]);

        $BATAS_HARIAN = 420;
        $existingJobs = DB::table('data_jasa')
                    ->where('pic', $request->pic)
                    ->get();

        $dailyLoad = [];
        foreach ($existingJobs as $job) {
            if($job->tanggal_mulai != null){
                $period = CarbonPeriod::create($job->tanggal_mulai, $job->tanggal_deadline);
                
                foreach ($period as $date) {
                    $tgl = $date->format('Y-m-d');
                    
                    if (!isset($dailyLoad[$tgl])) {
                        $dailyLoad[$tgl] = 0;
                    }
                    
                    $dailyLoad[$tgl] += $job->menit;
                }
            }
        }
        
        $newPeriod = CarbonPeriod::create($request->tanggal_mulai, $request->tanggal_deadline);
        $newMenit  = (int) $request->menit;
        $overloadDates = [];

        foreach ($newPeriod as $date) {
            $tgl = $date->format('Y-m-d');

            $currentLoad = $dailyLoad[$tgl] ?? 0;

            $totalPrediksi = $currentLoad + $newMenit;

            if ($totalPrediksi > $BATAS_HARIAN) {
                $overloadDates[] = "$tgl (Total: $totalPrediksi menit)";
            }
        }

        if(!empty($overloadDates)){
            $list_tanggal_string = implode(', ', $overloadDates);
            return redirect()->back()->with('error', "Gagal! Durasi melebihi 7 Jam (420 Menit) pada tanggal: " . $list_tanggal_string);
        }

        // Kirim Data Ke Database
        try {
            $data_jasa = DB::table('data_jasa')->insertGetId([
                'produk_client_id' => (int)$id,
                'judul'            => $request->judul,
                'inspirasi'        => $request->inspirasi,
                'menit'            => $request->menit,
                'pic'              => $request->pic,
                'priority'         => $request->prioritas,
                'type_produk_id'   => $value['type'],
                'strategy_id'      => $value['strategy'],
                'pilar_id'         => $value['pilar'],
                'hooks_id'         => $value['hooks'],
                'body_id'          => $value['jenis_body'],
                'cta_id'           => $value['jenis_cta'],
                'background'       => $request->warna,
                'durasi'           => $request->durasi,
                'komposisi'        => $request->komposisi,
                'note'             => $request->note,
                'tanggal_mulai'    => $request->tanggal_mulai,
                'tanggal_posting'  => $request->tanggal_posting,
                'tanggal_deadline' => $request->tanggal_deadline,
                'link'             => $request->link,
                'status_id'        => $value['status'],
                'platform'         => $request->platform,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $produk = ProdukClient::find((int)$id);
            $divisi = 0;
            switch ($produk->kategori_jasa_id) {
                case 1:
                    $divisi = 1;
                    break;
                case 2:
                case 3:
                    $divisi = 2;
                    break;
                default:
            }

            DB::table("task")->insert([
                "nama_task" => $request->judul,
                "divisi" => $divisi,
                "pic" => $request->pic,
                "priority" => $request->prioritas,
                "menit" => $request->menit,
                "id_manajement_konten" => $data_jasa,
                "tanggal_mulai" => $request->tanggal_mulai,
                "tanggal_deadline" => $request->tanggal_deadline,
                "note" => $request->note,
                "status" => 0,
                "project" => (int)$id
            ]);

            return redirect()->back()->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function update($id, Request $request){
        $data = DB::table('data_jasa')->where('id', $id)->first();
        $BATAS_HARIAN = 420;
        $otherJobs = DB::table('data_jasa')
                    ->where('pic', (int)$request->pic)
                    ->whereNot('id', $id)
                    ->get();

        // 2. Siapkan Wadah Beban Harian
        $dailyLoad = [];

        // --- LOOP 1: Hitung Beban Job LAIN ---
        foreach ($otherJobs as $job) {
            $period = CarbonPeriod::create($job->tanggal_mulai, $job->tanggal_deadline);
            foreach ($period as $date) {
                $tgl = $date->format('Y-m-d');
                
                if (!isset($dailyLoad[$tgl])) $dailyLoad[$tgl] = 0;
                
                $dailyLoad[$tgl] += $job->menit;
            }
        }

        // --- LOOP 2: Masukkan Data EDITAN BARU (Simulasi) ---
        // Sekarang kita masukkan data baru dari form edit ke dalam wadah
        $newPeriod = CarbonPeriod::create($request->tanggal_mulai, $request->tanggal_deadline);
        $newMenit  = (int) $request->menit;
        // dd($dailyLoad);
        $overloadDates = [];
        $arr_total = [];
        foreach ($newPeriod as $date) {
            $tgl = $date->format('Y-m-d');
            // Ambil beban existing (dari job lain)
            $currentLoad = $dailyLoad[$tgl] ?? 0;
            // Hitung Total Prediksi
            $totalPrediksi = $currentLoad + $newMenit;
            array_push($arr_total, $totalPrediksi);
            // Cek Validasi
            if ($totalPrediksi > $BATAS_HARIAN) {
                $overloadDates[] = "$tgl (Total: $totalPrediksi menit)";
            }
        }

        if (!empty($overloadDates)) {
            $list_tanggal_string = implode(', ', $overloadDates);
            return redirect()->back()->with('error', "Gagal! Durasi melebihi 7 Jam (420 Menit) pada tanggal: " . $list_tanggal_string);
        }
    
        $arr = ['type', 'strategy', 'pilar', 'hooks', 'status', 'jenis_body', 'jenis_cta'];
        $value = [];
        foreach($arr as $d){
            $value[$d] = (int)$request->$d; 
        }
        $request->pic = (int)$request->pic;
        $request->prioritas = (int)$request->prioritas;
        // dd($request->all());
        // Validasi Terlebih Dahulu
        $request->validate([
            'judul'             => 'required|string|max:255',
            'inspirasi'         => 'nullable|string',
            'menit'               => 'nullable|integer',
            'pic'               => 'nullable|integer',
            'prioritas'         => 'nullable|integer',
            'platform'          => 'nullable|string',
            'type'              => 'required|integer',
            'strategy'          => 'required|integer',
            'pilar'             => 'required|integer',
            'hooks'             => 'required|integer',
            'jenis_body'        => 'required|integer',
            'jenis_cta'         => 'required|integer',
            'durasi'            => 'nullable|string|max:50',
            'warna'             => 'nullable|string',
            'komposisi'         => 'nullable|string',
            'tanggal_mulai'     => 'nullable|date',
            'tanggal_posting'   => 'nullable|date',
            'tanggal_deadline'  => 'nullable|date',
            'link'              => 'nullable|string',
            'status'            => 'required|integer',
            'note'              => 'nullable|string',
        ]);

        try {
            DB::table('data_jasa')->where('id', $id)->update([
                'judul'            => $request->judul,
                'inspirasi'        => $request->inspirasi,
                'menit'              => $request->menit,
                'pic'              => $request->pic,
                'priority'        => $request->prioritas,
                'type_produk_id'   => $value['type'],
                'strategy_id'      => $value['strategy'],
                'pilar_id'         => $value['pilar'],
                'hooks_id'         => $value['hooks'],
                'body_id'          => $value['jenis_body'],
                'cta_id'           => $value['jenis_cta'],
                'durasi'           => $request->durasi,
                'background'       => $request->warna,
                'komposisi'        => $request->komposisi,
                'note'             => $request->note,
                'tanggal_mulai'    => $request->tanggal_mulai,
                'tanggal_posting'  => $request->tanggal_posting,
                'tanggal_deadline' => $request->tanggal_deadline,
                'link'             => $request->link,
                'status_id'        => $value['status'],
                'platform'         => $request->platform,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            if(Auth::user()->role != "content_creator"){
                DB::table("task")->where('id_manajement_konten', $id)->update([
                    "nama_task" => $request->judul,
                    "pic" => $request->pic,
                    "priority" => $request->prioritas,
                    "menit" => $request->menit,
                    "tanggal_mulai" => $request->tanggal_mulai,
                    "tanggal_deadline" => $request->tanggal_deadline,
                    "note" => $request->note
                ]);
            }

            return redirect()->back()->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        $id = (int)$id;
        DB::table("data_jasa")->delete($id);
        DB::table("task")->where('id_manajement_konten', $id)->delete();
        return redirect()->back()->with('success', 'Data Berhasil Di Hapus');
    }
}
