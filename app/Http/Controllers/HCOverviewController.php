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
        $user = Auth::user();
        $id_client = (int)$id;

        // 1. Definisi List
        $divisi_list = [1 => 'Digital Marketing', 2 => 'Business Dev', 3 => 'Human Capital'];
        $prioritas = [1 => 'Rendah', 2 => 'Sedang', 3 => 'Tinggi'];

        // 2. Query Utama Task
        $taskQuery = Task::leftjoin('data_jasa', 'data_jasa.id', '=', 'task.id_manajement_konten')
            ->leftjoin('produk_client', 'produk_client.id', '=', 'task.project')
            ->leftjoin('users', 'users.id', '=', 'task.pic')
            ->select(
                'task.*',
                'data_jasa.tanggal_mulai as tgl_mulai_jasa',
                'data_jasa.tanggal_deadline as tgl_deadline_jasa',
                'users.email as vic_email',
                'produk_client.nama as nama_client'
            )
            ->where('task.project', $id_client)
            ->whereNot('task.status', 5);

        // --- LOGIKA HAK AKSES DATA (Visibility) ---
        // Agar atasan bisa melihat task bawahannya, bukan cuma task dirinya sendiri
        $taskQuery->where(function($q) use ($user) {
            if ($user->role == 'digital_marketing') {
                $q->where('task.pic', $user->id);
            } elseif ($user->role == 'digital_marketing_manager') {
                $q->where('task.pic', $user->id)
                ->orWhere('users.dmm', $user->id);
            } elseif ($user->role == 'head_of_division') {
                $q->where('task.pic', $user->id)
                ->orWhere('users.hod', $user->id);
            }
        });

        // --- LOGIKA FILTER DARI REQUEST ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $taskQuery->whereBetween('task.tanggal_mulai', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('status'))    $taskQuery->where('task.status', $request->status);
        if ($request->filled('pic'))       $taskQuery->where('task.pic', $request->pic);
        if ($request->filled('priority'))  $taskQuery->where('task.priority', $request->priority);
        if ($request->filled('divisi'))    $taskQuery->where('task.divisi', $request->divisi);

        $data = $taskQuery->orderBy('task.tanggal_mulai', 'ASC')->get();

        // 3. Ambil Daftar VIC untuk Dropdown Filter
        $userQuery = User::whereIn("role", ["digital_marketing", "content_creator", "digital_marketing_manager"])
                    ->whereJsonContains('client', $id_client)
                    ->select('id', 'email', 'name');

        if ($user->role == "head_of_division") {
            $vic_list = $userQuery->where(function($q) use ($user) {
                $q->where('id', $user->id)->orWhere('hod', $user->id);
            })->get();
        } else if ($user->role == "digital_marketing_manager") {
            $vic_list = $userQuery->where(function($q) use ($user) {
                $q->where('id', $user->id)->orWhere('dmm', $user->id);
            })->get();
        } else {
            $vic_list = $userQuery->where('id', $user->id)->get();
        }

        // 4. Filter Divisi untuk UI
        $result = [];
        foreach($divisi_list as $divId => $nama) {
            if(in_array($divId, (array)$user->divisi)) {
                $result[$divId] = $nama;
            }
        }

        $client = ProdukClient::where('id', $id_client)->first();

        return view('hc.overview', compact('title', 'data', 'vic_list', 'result', 'prioritas', 'id', 'client'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id_client)
    {   
        $data = Task::where('pic', (int)$request->pic)->where('project', (int)$request->project)->get();
        
        $dailyLoad = [];
        foreach ($data as $job) {
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

            if ($totalPrediksi > 420) {
                $overloadDates[] = "$tgl (Total: $totalPrediksi menit)";
            }
        }
        if(!empty($overloadDates)){
            $list_tanggal_string = implode(', ', $overloadDates);
            return redirect()->back()->with('error', "Gagal! Durasi melebihi 7 Jam (420 Menit) pada tanggal: " . $list_tanggal_string);
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
