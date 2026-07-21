<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserDivision;
use App\Models\Role;
use App\Models\Division;
use App\Models\KPI;
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
        $id_client = (int)$id;
        $divisi_list = Division::pluck('name', 'id');
        $client = ProdukClient::with('divisions')->findOrFail($id_client);
        $divisi = $client->divisions->pluck('name', 'id');
        
        $user = Auth::user();
        $divisi = User::find($user->id)->userDivisions[0] ?? "Super Admin";
        $RoleSekarang = $user->isAdmin == false ? DB::table('roles')->where('id', $divisi['role_id'])->value('role_name') : "Super Admin";
        // Label kesulitan tetap perlu dikirim ke view untuk filter select
        $difficultyLabels = [
            1 => 'Sangat Mudah', 2 => 'Mudah', 3 => 'Sedang', 5 => 'Sulit', 8 => 'Sangat Sulit'
        ];

        $result = [];
        if($RoleSekarang == "Super Admin"){
            $result = $divisi_list;
        }else{
            foreach($divisi_list as $divId => $nama) {
                if($divId == $divisi->division_id) {
                    $result[$divId] = $nama;
                }
            }
        }

         $handed_client = User::leftjoin('user_division', 'user_division.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->distinct();

        if ($RoleSekarang == "Super Admin") {
            $vic_list = $handed_client->where('users.isAdmin', false)->get();
        } else {
            $vic_list = $handed_client->where(function($q) use ($user) {
                $q->where('users.id', $user->id)
                ->orWhere('user_division.parent_id', $user->id);
            })->get();
        }

        return view('hc.overview', compact('title', 'id', 'client', 'difficultyLabels', 'divisi_list', 'result', 'vic_list'));
    }

    public function tambahKPI(Request $request){
        $request->validate([
            'nama_kpi'         => 'required',
            'divisi'           => 'required',
            'pic'              => 'required|array', // Pastikan datang sebagai array
            'point'            => 'required',
            'tanggal_mulai'    => 'required',
            'tanggal_deadline' => 'required',
            'note'             => 'nullable'
        ]);

        // Ambil semua data kecuali 'pic'
        $data = $request->except('pic');
        $data['status'] = 0;
        $data['create_by'] = Auth::id();

        // Simpan KPI
        $kpi = KPI::create($data);

        // Hubungkan dengan PIC di tabel pivot
        $kpi->pics()->sync($request->pic);

        return redirect()->back()->with('success', 'KPI Berhasil Dibuat');
    }

    public function editKPI($id, Request $request){
        $request->validate([
            'nama_kpi'         => 'required',
            'divisi'           => 'required',
            'pic'              => 'required|array',
            'point'            => 'required',
            'tanggal_mulai'    => 'required',
            'tanggal_deadline' => 'required',
            'note'             => 'nullable'
        ]);

        $kpi = KPI::findOrFail($id);

        // Update atribut utama
        $kpi->update($request->except(['pic', '_token', '_method']));

        // Update relasi PIC
        $kpi->pics()->sync($request->pic);

        return redirect()->back()->with('success', 'KPI Berhasil Di Update');
    }

    public function endKPI($id){
        $kpi = Kpi::findOrFail($id);

        // Jika sudah selesai
        if ($kpi->status == 1) {
            return response()->json([
                'success' => false,
                'message' => 'KPI sudah selesai.'
            ]);
        }

        $kpi->status = 1;
        $kpi->save();

        return response()->json([
            'success' => true,
            'message' => 'KPI berhasil diselesaikan.'
        ]);
    }

    public function deleteKPI($id)
    {
        $kpi = KPI::findOrFail($id);
        $kpi->delete();
        return redirect()->back()->with('success', 'Berhasil Di Hapus');
    }

    public function getUsers($id, $id_user){
        $user = User::find($id_user);
        $userQuery = User::join('user_division', 'user_division.user_id', '=', 'users.id')
                    ->where('user_division.division_id', $id)
                    ->select('users.id', 'users.email')
                    ->distinct();
        $RoleSekarang = $user->isAdmin == false ? DB::table('roles')->where('id', $user->userDivisions[0]->role_id)->value('role_name') : "Super Admin";
        $level = Role::where('role_name', $RoleSekarang)->first();
        $currentLevel = 0;
        if($level != null){
            $currentLevel = $level->level;
        }

        if ($currentLevel == 0) {
            $vic_list = $userQuery->get();
        }else{
            $vic_list = $userQuery->where(function($q) use ($user) {
                $q->where('users.id', $user->id)->orWhere('user_division.parent_id', $user->id);
            })->get();
        }


        return response()->json($vic_list->map(function($u) {
            return [
                'id' => $u->id,
                'email' => $u->email,
            ];
        }));
        
    }

    public function getKPIData(Request $request, $id, $id_user)
    {
        $user = User::findOrFail($id_user);
        $division = $user->userDivisions->first();

        $query = KPI::with(['pics.userDivisions.role', 'pics.userDivisions.division']);

        if ($user->isAdmin) {
            $query->where('project', $id);
        } else {
            $query->where('project', $id)
                ->where('divisi', $division->division_id);
        }

        // Batasi data untuk non-admin
        if (!$user->isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->whereHas('pics', function ($qq) use ($user) {
                    $qq->where('users.id', $user->id);
                })->orWhereHas('pics.userDivisions', function ($qq) use ($user) {
                    $qq->where('parent_id', $user->id);
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter
        |--------------------------------------------------------------------------
        */

        $query
            ->when($request->filled('start_date'), function ($q) use ($request) {
                $q->whereDate('tanggal_mulai', '>=', $request->start_date);
            })

            ->when($request->filled('end_date'), function ($q) use ($request) {
                $q->whereDate('tanggal_deadline', '<=', $request->end_date);
            })

            ->when($request->filled('divisi'), function ($q) use ($request) {
                $q->where('divisi', $request->divisi);
            })

            ->when($request->filled('priority'), function ($q) use ($request) {
                $q->where('point', $request->priority);
            })

            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })

            ->when($request->filled('pic'), function ($q) use ($request) {
                $q->whereHas('pics', function ($qq) use ($request) {
                    $qq->where('users.id', $request->pic);
                });
            });

        $data = $query->get();

        $result = $data->map(function ($kpi) {

            $listEmail = $kpi->pics->pluck('email')->implode(', ');

            $firstPic = $kpi->pics->first();
            $division = $firstPic?->userDivisions->first();
            $pembuat = User::find($kpi->create_by);

            return [
                'id'               => $kpi->id,
                'nama_kpi'         => $kpi->nama_kpi,
                'create_by'        => $kpi->create_by,
                'point'            => $kpi->point,
                'tanggal_mulai'    => $kpi->tanggal_mulai,
                'tanggal_deadline' => $kpi->tanggal_deadline,
                'status'           => $kpi->status,
                'note'             => $kpi->note,
                'vic_email'        => $listEmail,
                'nama_divisi'      => $division->division->name ?? '-',
                'role_pic'         => $division->role->role_name ?? '-',
                'divisi'           => $division->division_id ?? null,
                'pics'             => $kpi->pics,
                'pembuat'          => $pembuat->email
            ];
        });

        return response()->json([
            'data' => $result
        ]);
    }

    public function getKPIDataDaily(Request $request, $id, $id_user){
        $kpis = KPI::with('pics')->where('project', $id)->get();

        $result = $kpis->map(function($kpi) {
            return [
                'id'               => $kpi->id,
                'nama_kpi'         => $kpi->nama_kpi,
                'create_by'        => $kpi->create_by,
                'point'            => $kpi->point,
                'tanggal_mulai'    => $kpi->tanggal_mulai,
                'tanggal_deadline' => $kpi->tanggal_deadline,
                'status'           => $kpi->status,
                'note'             => $kpi->note,
                'divisi'           => $kpi->divisi,
                // Mengambil ID PIC untuk kebutuhan edit modal
                'pic_ids'          => $kpi->pics->pluck('id')->toArray(),
                // Mengambil email PIC untuk kebutuhan display tabel
                'vic_email'        => $kpi->pics->pluck('email')->implode(', '),
            ];
        });

        return response()->json(['data' => $result]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id_client)
    {   
        $data = Task::where('pic', (int)$request->pic)->get();

        $newStart = Carbon::parse($request->tanggal_mulai . ' ' . $request->jam_mulai);

        $newEnd = (clone $newStart)->addMinutes((int)$request->menit);

        $conflict = [];
        
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
                $existingStart = Carbon::parse($job->tanggal_mulai . ' ' . $job->jam_mulai);
    
                $existingEnd = (clone $existingStart)->addMinutes((int)$job->menit);
    
                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    $conflict[] = $job->nama_task . "|" . $job->tanggal_mulai . " jam " . $job->jam_mulai;
                }
            }
        }

        if(!empty($conflict)){
            $list_jam = implode(', ', $conflict);
            return redirect()->back()->with('error', "Gagal! Waktu Bentrok " . $list_jam);
        }

        $newPeriod = CarbonPeriod::create($request->tanggal_mulai, $request->tanggal_deadline);
        $newMenit  = (int) $request->menit;
        $overloadDates = [];
        $batasWaktu = 420;
        if((int)$request->pic == 39){
            $batasWaktu = 480;
        }

        foreach ($newPeriod as $date) {
            $tgl = $date->format('Y-m-d');

            $currentLoad = $dailyLoad[$tgl] ?? 0;

            $totalPrediksi = $currentLoad + $newMenit;

            if ($totalPrediksi > $batasWaktu) {
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
                'jam_mulai'        => $request->jam_mulai,
                'note'             => $request->note,
                'status'           => 0, // Default ke Pool
                'jira'             => $request->jira_input
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

        $newStart = Carbon::parse($request->tanggal_mulai . ' ' . $request->jam_mulai);

        $newEnd = (clone $newStart)->addMinutes((int)$request->menit);

        $allTask = Task::where('pic', (int)$request->pic)
                ->where('id', '!=', $id) // <-- INI KUNCINYA
                ->get();

        $conflict = [];

        foreach($allTask as $job){
            $existingStart = Carbon::parse($job->tanggal_mulai . ' ' . $job->jam_mulai);
    
            $existingEnd = (clone $existingStart)->addMinutes((int)$job->menit);

            if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                $conflict[] = $job->nama_task . " | " . $job->tanggal_mulai . " jam " . $job->jam_mulai;
            }
        }

        if(!empty($conflict)){
            $list_jam = implode(', ', $conflict);
            return redirect()->back()->with('error', "Gagal! Waktu Bentrok " . $list_jam);
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
            'id_kpi' => $request->kpi_id,
            'tanggal_deadline' => $request->tanggal_deadline,
            'jam_mulai'        => $request->jam_mulai,
            'note' => $request->note,
            'status' => 0,
            'jira' => $request->jira_input
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

        $userHandledClients = Auth::user()->clients->pluck('id')->toArray(); // Contoh isinya: [13, 14]

        // $userHanded = ProdukClient::join('users', 'users.id', '=', )
        if (!is_array($userHandledClients)) {
            $userHandledClients = json_decode($userHandledClients, true) ?: [];
        }

        // 2. Query ProdukClient hanya untuk ID yang ada di array tersebut
        $project = ProdukClient::join('client_division', 'client_division.client_id', 'produk_client.id')->join('divisions', 'divisions.id', '=', 'client_division.division_id')->select('produk_client.id', 'produk_client.nama as nama_client', 'divisions.name as nama_divisi');
            if(Auth::user()->isAdmin == false){
                $projects = $project->whereIn('produk_client.id', $userHandledClients); // Filter Utama
            }
            $projects = $project->get()
            ->map(function ($client) {
                // Hitung total task milik client ini
                $totalKPI = KPI::where('project', $client->id)->count();
                $totalTask = Task::where('project', $client->id)->whereNot('status', 5)->count();
                
                // Hitung task yang sudah DONE (status 4)
                $doneTask = Task::where('project', $client->id)
                    ->where('status', 4)
                    ->count();

                $doneKPI = KPI::where('project', $client->id)->where('status', 1)->count();

                // Hitung persentase
                $percentage = ($totalKPI > 0) ? round(($doneKPI / $totalKPI) * 100) : 0;

                return [
                    'project_id'  => $client->id,
                    'nama_client' => $client->nama_client,
                    'nama_divisi' => $client->nama_divisi,
                    'percentage'  => $percentage,
                    'total'       => $totalKPI,
                    'done'        => $doneKPI,
                    'color'       => ($totalKPI == 0) ? 'secondary' : ($percentage == 100 ? 'success' : ($percentage > 50 ? 'primary' : 'warning'))
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
            $baseQuery->whereBetween('task.tanggal_mulai', [$startDate, $endDate]);
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
            'waiting_approval_overdue' => (clone $baseQuery)->where('status', 9)->count(),
            'approved'         => (clone $baseQuery)->where('status', 4)->count(),
            'overdue'          => (clone $baseQuery)->where('status', 7)->whereNull('tanggal_approve')->count(),
            'overdue_done'     => (clone $baseQuery)->where('status', 7)->whereNotNull('tanggal_approve')->count(),
        ];

        // 3. Logika Chart (Hanya satu logika yang dijalankan)
        $labels = [];
        $chartData = [];
        $queryChart = clone $baseQuery; // Clone dari base yang bersih
        
        if (!$startDate && !$endDate) {
            $queryChart->whereYear('task.tanggal_mulai', $currentYear);
        }

        // --- FIX UNTUK LOGIKA KLIK CHART ---
        // Logika klik diletakkan di sini agar Chart dan Tabel tersinkronisasi
        if ($clickedDate) {
            $queryChart->whereDate('task.tanggal_mulai', $clickedDate);
        } elseif ($clickedMonth) {
            $queryChart->whereRaw('EXTRACT(MONTH FROM task.tanggal_mulai) = ?', [$clickedMonth])
                       ->whereRaw('EXTRACT(YEAR FROM task.tanggal_mulai) = ?', [$currentYear]);
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
            $data = (clone $queryChart)->selectRaw('task.tanggal_mulai, SUM(menit) as total')
                ->groupBy('task.tanggal_mulai')
                ->orderBy('task.tanggal_mulai')
                ->get();
            foreach ($data as $item) {
                $labels[] = \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M');
                $chartData[] = (int) $item->total;
            }
        } elseif ($type == 'weekly') {
            // Gunakan GROUP BY RAW untuk PGSQL agar tidak error alias
            $data = (clone $queryChart)->selectRaw('EXTRACT(WEEK FROM task.tanggal_mulai) as week, EXTRACT(YEAR FROM tanggal_mulai) as year, SUM(menit) as total')
                ->groupByRaw('year, week') 
                ->orderByRaw('year ASC, week ASC')
                ->get();
            foreach ($data as $item) {
                $labels[] = "Minggu-" . (int)$item->week;
                $chartData[] = (int) $item->total;
            }
        } else {
            $data = (clone $queryChart)->selectRaw('EXTRACT(MONTH FROM task.tanggal_mulai) as month, SUM(menit) as total')
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
            ->leftJoin('kpi', 'kpi.id', '=', 'task.id_kpi')
            ->select('task.*', 'produk_client.nama', 'kpi.nama_kpi')
            ->latest('task.tanggal_deadline')
            ->get();

        return response()->json([
            'summary'      => $summary,
            'status_count' => $statusCount,
            'monthly_data' => $chartData,
            'labels'       => $labels,
            'recent_tasks' => $recentTasks
        ]);
    }

    public function getTasksByDate(Request $request)
    {
        $tanggal = $request->query('tanggal');
        $picId = $request->query('pic');

        if (!$tanggal || !$picId) {
            return response()->json(['error' => 'Parameter tidak lengkap'], 400);
        }

        // Mengambil task pada tanggal tertentu untuk PIC tertentu
        $tasks = Task::join('produk_client', 'produk_client.id', '=', 'task.project')->where('tanggal_mulai', $tanggal)
                     ->where('pic', $picId)
                     ->select('task.*', 'produk_client.nama')
                     ->orderBy('jam_mulai', 'asc')
                     ->get();
        return response()->json($tasks);
    }
}
