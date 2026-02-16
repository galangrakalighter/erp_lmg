<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use App\Models\ProdukClient;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
class HCOverviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        $title = "Project Overview";
    
        // List definisi untuk filter di Blade
        $divisi_list = [
            1 => 'Digital Marketing',
            2 => 'Business Dev',
            3 => 'Human Capital'
        ];

        $prioritas = [
            1 => 'Rendah',
            2 => 'Sedang',
            3 => 'Tinggi',
        ];

        $query = Task::leftjoin('data_jasa', 'data_jasa.id', '=', 'task.id_manajement_konten')
            ->leftjoin('produk_client', 'produk_client.id', '=', 'task.project') // Langsung join ke 'project' sesuai tabel Anda
            ->leftjoin('users', 'users.id', '=', 'task.pic')
            ->select(
                'task.*',
                'data_jasa.tanggal_mulai as tgl_mulai_jasa',
                'data_jasa.tanggal_deadline as tgl_deadline_jasa',
                'users.email as vic_email',
                'produk_client.nama as nama_client'
            )
            // PENTING: Filter berdasarkan ID Client yang diklik
            ->where('task.project', $id)
            ->whereNot('task.status', 5);

        // --- LOGIKA FILTER (Sama seperti script Anda) ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('task.tanggal_mulai', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')){
            $query->where('task.status', $request->status);
        }

        if ($request->filled('pic')) {
            $query->where('task.pic', $request->pic);
        }

        if ($request->filled('priority')) {
            $query->where('task.priority', $request->priority);
        }

        if ($request->filled('divisi')) {
            $query->where('task.divisi', $request->divisi);
        }

        // Ambil daftar VIC untuk dropdown filter di Blade
        $vic_list = [];
        $vic = User::whereIn("role", ["digital_marketing", "content_creator", 'digital_marketing_manager']);
        if(Auth::user()->role == "head_of_division"){
            $vic_list = $vic->where('hod', Auth::user()->id)->select('id', 'email') ->get();
        }else if(Auth::user()->role == "digital_marketing_manager"){
            $vic_list = $vic->where('dmm', Auth::user()->id)->select('id', 'email') ->get();
        }            

        $result = [];
        foreach($divisi_list as $divId => $nama){
            if(in_array($divId, Auth::user()->divisi)){
                $result[$divId] = $nama;
            }
        }

        $data = $query->orderBy('task.tanggal_mulai', 'ASC')->get();
        $client = ProdukClient::where('id', $id)->first();
        return view('hc.overview', compact('title', 'data', 'vic_list', 'result', 'prioritas', 'id', 'client'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id_client)
    {
        // 1. Hitung beban kerja PIC yang sudah ada KHUSUS BULAN INI
        $data = Task::where('pic', (int)$request->pic)
                    ->whereMonth('tanggal_mulai', Carbon::now()->month)
                    ->whereYear('tanggal_mulai', Carbon::now()->year)
                    ->sum('menit');
                    
        $inputMenit = $data + (int)$request->menit;
        
        // 2. Tentukan Rentang: Dari HARI INI sampai AKHIR BULAN
        $start = Carbon::now(); 
        $endOfMonth = Carbon::now()->endOfMonth(); 
        $tahun = $start->year;

        // 3. Ambil Data Libur dari API
        $listLibur = [];
        try {
            $response = Http::timeout(5)->get("https://hari-libur-api.vercel.app/api?year=$tahun");
            if($response->successful()){
                $listLibur = collect($response->json())
                    ->filter(function ($item) {
                        return isset($item['is_national_holiday']) && $item['is_national_holiday'] === true;
                    })
                    ->pluck('event_date')
                    ->toArray();
            }
        } catch (\Exception $e) {
            // Jika API gagal, listLibur tetap kosong agar aplikasi tidak mati
            $listLibur = [];
        }

        // 4. Hitung Kapasitas Menit dari Hari Ini s/d Akhir Bulan
        $sisaKapasitasMenit = 0;
        $periode = CarbonPeriod::create($start, $endOfMonth);
        
        foreach ($periode as $date) {
            $currentDate = $date->format('Y-m-d');

            // Lewati jika hari Minggu atau hari Libur Nasional
            if ($date->isSunday() || in_array($currentDate, $listLibur)) {
                continue;
            }

            // Sabtu 5 jam (300m), Senin-Jumat 7 jam (420m)
            $sisaKapasitasMenit += $date->isSaturday() ? 300 : 420;
        }

        // 5. CEK OVERLOAD
        if ($inputMenit > $sisaKapasitasMenit) {
            $jamKapasitas = round($sisaKapasitasMenit / 60, 1);
            $tglSekarang = $start->translatedFormat('d F Y');
            
            return back()->withInput()->with('overload', "Peringatan: Kapasitas waktu kerja PIC dari hari ini ($tglSekarang) sampai akhir bulan hanya tersisa $sisaKapasitasMenit menit ($jamKapasitas jam). Total beban PIC jika ditambah task ini menjadi $inputMenit menit!");
        }

        // 6. SIMPAN DATA
        Task::create([
            'project'          => (int)$id_client, // Hubungkan ke ID Client dari URL
            'nama_task'        => $request->nama_task,
            'divisi'           => (int)$request->divisi,
            'pic'              => (int)$request->pic,
            'priority'         => (int)$request->priority,
            'menit'            => (int)$request->menit,
            'tanggal_mulai'    => $request->tanggal_mulai,
            'tanggal_deadline' => $request->tanggal_deadline,
            'note'             => $request->note,
            'status'           => 0, // Default ke Pool
        ]);

        return redirect()->back()->with('success', 'Task berhasil ditambahkan ke project!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id_client, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return back()->with('error', 'Task tidak ditemukan!');
        }

        // Hitung akumulasi menit PIC (Kecuali task yang sedang diedit ini agar tidak double count)
        $totalMenitPIC = Task::where('pic', (int)$request->pic)
                            ->where('id', '!=', $id)
                            ->whereMonth('tanggal_mulai', Carbon::now()->month)
                            ->sum('menit');

        $inputMenit = $totalMenitPIC + (int)$request->menit;
        
        // 1. Tentukan Rentang: Dari HARI INI sampai AKHIR BULAN
        $start = Carbon::now(); // Hari ini
        $endOfMonth = Carbon::now()->endOfMonth(); // Akhir bulan ini
        $tahun = $start->year;
        // 2. Ambil Data Libur dari API (Cache 24 jam)
        $listLibur = [];
        $response = Http::get("https://hari-libur-api.vercel.app/api?year=$tahun");
        if($response->successful()){
            $listLibur = collect($response->json())
            ->filter(function ($item) {
                return $item['is_national_holiday'] === true;
            })
            ->pluck('event_date')
            ->unique()
            ->values()
            ->toArray();
        }

        // 3. Hitung Kapasitas Menit dari Hari Ini s/d Akhir Bulan
        $sisaKapasitasMenit = 0;
        
        // CarbonPeriod membuat list tanggal dari hari ini sampai akhir bulan
        $periode = CarbonPeriod::create($start, $endOfMonth);
        foreach ($periode as $date) {
            $currentDate = $date->format('Y-m-d');

            if ($date->isSunday() || in_array($currentDate, $listLibur)) {
                continue;
            }

            // Sabtu 5 jam (300m), Senin-Jumat 7 jam (420m)
            if ($date->isSaturday()) {
                $sisaKapasitasMenit += 300; 
            } else {
                $sisaKapasitasMenit += 420;
            }
        }

        // 4. CEK OVERLOAD
        if ($inputMenit > $sisaKapasitasMenit) {
            $jamKapasitas = round($sisaKapasitasMenit / 60, 1);
            $tglSekarang = $start->translatedFormat('d F Y');
            
            return back()->withInput()->with('overload', "Peringatan: Kapasitas waktu kerja dari hari ini ($tglSekarang) sampai akhir bulan hanya tersisa $sisaKapasitasMenit menit ($jamKapasitas jam). Task yang Anda input ($inputMenit menit) melebihi jatah waktu tersebut!");
        }


        $task = Task::where('id', $id)->update([
            'nama_task' => $request->nama_task,
            'divisi' => (int)$request->divisi,
            'pic' => (int)$request->pic,
            'priority' => (int)$request->priority,
            'menit' => (int)$request->menit,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_deadline' => $request->tanggal_deadline,
            'note' => $request->note,
            'status' => 0
        ]);
        

        return redirect()->back()->with('success', 'Berhasil Di Update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id_client, string $id)
    {
        Task::destroy($id);

        return redirect()->back()->with('error', "Berhasil Dihapus");
    }

    public function list_client(){
        $title = "Client Progress Monitor";

        // Ambil data client beserta task mereka
        // Diasumsikan ada kolom 'client_id' di tabel tasks
        $userHandledClients = Auth::user()->client; // Contoh isinya: [13, 14]
        // Pastikan $userHandledClients adalah array, jika di DB tersimpan sebagai string JSON
        // Laravel biasanya otomatis melakukan cast jika sudah diatur di Model User
        if (!is_array($userHandledClients)) {
            $userHandledClients = json_decode($userHandledClients, true) ?: [];
        }

        // 2. Query ProdukClient hanya untuk ID yang ada di array tersebut
        $project = ProdukClient::select('id', 'nama as nama_client');
            if(Auth::user()->role != "super_admin"){
                $projects = $project->whereIn('id', $userHandledClients); // Filter Utama
            }
            $projects = $project->get()
            ->map(function ($client) {
                // Hitung total task milik client ini
                $totalTask = Task::where('project', $client->id)->whereNot('status', 5)->count();
                
                // Hitung task yang sudah DONE (status 4)
                $doneTask = Task::where('project', $client->id)
                    ->where('status', 4)
                    ->count();

                // Hitung persentase
                $percentage = ($totalTask > 0) ? round(($doneTask / $totalTask) * 100) : 0;

                return [
                    'project_id'  => $client->id,
                    'nama_client' => $client->nama_client,
                    'percentage'  => $percentage,
                    'total'       => $totalTask,
                    'done'        => $doneTask,
                    'color'       => ($totalTask == 0) ? 'secondary' : ($percentage == 100 ? 'success' : ($percentage > 50 ? 'primary' : 'warning'))
                ];
            });

        return view('hc.list_client', compact('title', 'projects'));
    }

    private function getProgressColor($percentage) {
        if ($percentage <= 30) return 'danger';
        if ($percentage <= 70) return 'warning';
        return 'success';
    }
}
