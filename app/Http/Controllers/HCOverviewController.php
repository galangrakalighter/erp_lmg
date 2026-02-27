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

            switch($user->role) {
                case "digital_marketing_manager":
                    $taskQuery->where(function($q) use ($user) {
                        // Melihat task milik sendiri OR task yang manager/bawahannya adalah dia
                        $q->where('task.pic', $user->id)
                        ->orWhere('users.dmm', $user->id); // Pastikan nama kolom di tabel users benar (dmm atau dm)
                    });
                    break;

                case "digital_marketing":
                    $taskQuery->where(function($q) use ($user) {
                        $q->where('task.pic', $user->id)
                        ->orWhere('users.dm', $user->id);
                    });
                    break;

                case "content_creator":
                    $taskQuery->where('task.pic', $user->id);
                    break;

                case "head_of_division":
                    // Biasanya HOD bisa melihat semua task di project tersebut
                    // Jadi biarkan kosong agar tidak terfilter pic
                    break;

                default:
                    // Jika role lain, batasi hanya melihat milik sendiri demi keamanan
                    $taskQuery->where('task.pic', $user->id);
                    break;
            }

        // --- LOGIKA FILTER DARI REQUEST ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $taskQuery->whereBetween('task.tanggal_mulai', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('status')) {   $taskQuery->where('task.status', $request->status); }
        if ($request->filled('pic'))   {    $taskQuery->where('task.pic', $request->pic); }
        if ($request->filled('priority')) { $taskQuery->where('task.priority', $request->priority); }
        if ($request->filled('divisi'))   { $taskQuery->where('task.divisi', $request->divisi); }

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
        } else if ($user->role == "digital_marketing") {
            $vic_list = $userQuery->where(function($q) use ($user) {
                $q->where('id', $user->id)->orWhere('dm', $user->id);
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
        $data = Task::where('pic', (int)$request->pic)->get();
        
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
        foreach ($newPeriod as $date) {
            Task::create([
                'project'          => (int)$id_client,
                'nama_task'        => $request->nama_task,
                'divisi'           => (int)$request->divisi,
                'pic'              => (int)$request->pic,
                'priority'         => (int)$request->priority,
                'menit'            => (int)$request->menit,
                'tanggal_mulai'    => $date->format('Y-m-d'),
                'tanggal_deadline' => $date->format('Y-m-d'),
                'note'             => $request->note,
                'status'           => 0, // Default ke Pool
            ]);
        }

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

        $userHandledClients = Auth::user()->client; // Contoh isinya: [13, 14]
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

    public function getDataDashboardBawahan(Request $request, $id) {
        $type = $request->query('type', 'monthly');
        $now = Carbon::now();
        $currentYear = $now->year;
        
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $clickedDate = $request->query('clicked_date');
        $clickedMonth = $request->query('clicked_month');
        $clickedStatus = $request->query('clicked_status');

        // Query Dasar
        $baseQuery = Task::where('pic', $id);

        // Filter Global (Mempengaruhi Summary & Recent Tasks)
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('tanggal_mulai', [$startDate, $endDate]);
        }

        // 1. Statistik Utama (Summary)
        $summary = [
            'total_this_month' => (clone $baseQuery)->count(),
            'done'             => (clone $baseQuery)->where('status', 4)->count(),
            'overdue'          => (clone $baseQuery)->where('status', 7)->whereNull('tanggal_approve')->count(),
            'overdue_done'          => (clone $baseQuery)->where('status', 7)->whereNotNull('tanggal_approve')->count(),
            'monthly_hours'    => (clone $baseQuery)->sum('menit') ?? 0,
        ];

        // 2. Data Status untuk Doughnut Chart
        $statusCount = [
            'pending'          => (clone $baseQuery)->where('status', 0)->count(),
            'on_progress'      => (clone $baseQuery)->where('status', 1)->count(),
            'revisi'           => (clone $baseQuery)->where('status', 2)->count(),
            'waiting_approval' => (clone $baseQuery)->where('status', 3)->count(),
            'approved'         => (clone $baseQuery)->where('status', 4)->count(),
            'overdue'          => (clone $baseQuery)->where('status', 7)->whereNull('tanggal_approve')->count(),
            'overdue_done'     => (clone $baseQuery)->where('status', 7)->whereNotNull('tanggal_approve')->count(),
        ];

        // 3. Logika Chart (Hanya satu logika yang dijalankan)
        $labels = [];
        $chartData = [];
        $queryChart = clone $baseQuery; // Clone dari base yang bersih
        
        if (!$startDate && !$endDate) {
            $queryChart->whereYear('tanggal_mulai', $currentYear);
        }

        // --- FIX UNTUK LOGIKA KLIK CHART ---
        // Logika klik diletakkan di sini agar Chart dan Tabel tersinkronisasi
        if ($clickedDate) {
            $queryChart->whereDate('tanggal_mulai', $clickedDate);
        } elseif ($clickedMonth) {
            $queryChart->whereRaw('EXTRACT(MONTH FROM tanggal_mulai) = ?', [$clickedMonth])
                       ->whereRaw('EXTRACT(YEAR FROM tanggal_mulai) = ?', [$currentYear]);
        }

        if ($clickedStatus !== null) {
            if ($clickedStatus === '7_done') {
                // Status 7 dan sudah di-approve (Done)
                $queryChart->where('task.status', 7)
                        ->whereNotNull('task.tanggal_approve');
            } elseif ($clickedStatus == 7) {
                // Status 7 dan belum di-approve (Masih Overdue)
                $queryChart->where('task.status', 7)
                        ->whereNull('task.tanggal_approve');
            } else {
                // Status lainnya (0, 1, 2, 3, 4)
                $queryChart->where('task.status', $clickedStatus);
            }
        }

        if ($type == 'daily') {
            $data = (clone $queryChart)->selectRaw('tanggal_mulai, SUM(menit) as total')
                ->groupBy('tanggal_mulai')
                ->orderBy('tanggal_mulai')
                ->get();
            foreach ($data as $item) {
                $labels[] = \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M');
                $chartData[] = (int) $item->total;
            }
        } elseif ($type == 'weekly') {
            // Gunakan GROUP BY RAW untuk PGSQL agar tidak error alias
            $data = (clone $queryChart)->selectRaw('EXTRACT(WEEK FROM tanggal_mulai) as week, EXTRACT(YEAR FROM tanggal_mulai) as year, SUM(menit) as total')
                ->groupByRaw('year, week') 
                ->orderByRaw('year ASC, week ASC')
                ->get();
            foreach ($data as $item) {
                $labels[] = "Minggu-" . (int)$item->week;
                $chartData[] = (int) $item->total;
            }
        } else {
            $data = (clone $queryChart)->selectRaw('EXTRACT(MONTH FROM tanggal_mulai) as month, SUM(menit) as total')
                ->groupByRaw('month')
                ->orderByRaw('month ASC')
                ->get()
                ->pluck('total', 'month');
            
            for ($m = 1; $m <= 12; $m++) {
                $monthName = \Carbon\Carbon::create($currentYear, $m, 1)->translatedFormat('F');
                $total = $data->get((float)$m) ?? $data->get($m) ?? 0;
                if ($total > 0) {
                    $labels[] = $monthName;
                    $chartData[] = (int) $total;
                }
            }
        }

        // 4. Daftar Task Terbaru (MENGGUNAKAN QUERY BERSIH)
        // Gunakan $queryChart yang sudah difilter Klik, tapi TANPA selectRaw/groupBy
        $recentTasks = $queryChart
            ->join('produk_client', 'produk_client.id', '=', 'task.project')
            ->select('task.*', 'produk_client.nama')
            ->latest('task.tanggal_deadline')
            ->take(20) // Tambah limit jika perlu
            ->get();

        return response()->json([
            'summary'      => $summary,
            'status_count' => $statusCount,
            'monthly_data' => $chartData,
            'labels'       => $labels,
            'recent_tasks' => $recentTasks
        ]);
    }

    public function dashboard(){
        $title = 'Dashboard';
        $user = Auth::user();

        $karyawan = [];
        switch ($user->role) {
            case 'head_of_division':
                $allUser = User::where('hod', $user->id)->orWhere('id', $user->id)->get();
                break;
            case 'digital_marketing_manager':
                $allUser = User::where('dmm', $user->id)->orWhere('id', $user->id)->get();
                break;
            case 'digital_marketing':
                $allUser = User::where('dm', $user->id)->orWhere('id', $user->id)->get();
                break;
            case 'conten_creator':
                $allUser = User::where('id', $user->id)->get();
                break;
            default:
                $allUser = User::whereNot('role', 'super_admin')->get();
                break;
        }

        return view('hc.dashboard', compact('title', 'allUser'));
    }
}
