<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
class KPIController extends Controller
{
    public function getKpiStats(Request $request) 
    {
        $isAdmin = Auth::user()->isAdmin == true;

        // 1. Base Query (Filter User/Project saja, TANPA filter tanggal)
        if ($isAdmin) {
            if ($request->filled('user_id') && $request->user_id !== 'all') {
                $baseQuery = \App\Models\KPI::whereHas('pics', function($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            } else if ($request->filled('divisi_id') && $request->divisi_id !== 'all') {
                $baseQuery = \App\Models\KPI::whereHas('pics', function($q) use ($request) {
                    $q->where('divisi', $request->divisi_id);
                });
            }else {
                $baseQuery = \App\Models\KPI::query();
            }
        } else {
            // Non-admin: 
            // 1. Tampilkan jika dia adalah PIC (kpis)
            // 2. ATAU Tampilkan jika KPI tersebut memiliki divisi yang sama dengan miliknya
            $userDivisions = Auth::user()->userDivisions()->pluck('division_id')->toArray();

            $baseQuery = \App\Models\KPI::where(function($q) use ($request, $userDivisions) {
                // Kondisi A: Jika ada filter user_id dari request, pakai itu
                if ($request->filled('user_id') && $request->user_id !== 'all') {
                    $q->whereHas('pics', function($sub) use ($request) {
                        $sub->where('user_id', $request->user_id);
                    });
                } else {
                    // Kondisi B: Default (jika tidak ada filter user_id)
                    // Tampilkan KPI miliknya sendiri ATAU KPI yang divisinya cocok
                    $q->whereHas('pics', function($sub) {
                        $sub->where('user_id', Auth::id());
                    })->orWhereIn('divisi', $userDivisions);
                }
            });
        }
        if ($request->filled('project_id') && $request->project_id !== 'all') {
            $baseQuery->where('project', $request->project_id);
        }

        // 2. Query untuk Statistik Berdasarkan Rentang Tanggal (Filter Start & End)
        $filteredQuery = (clone $baseQuery);
        // Ambil KPI yang masa berjalannya beririsan dengan periode filter.
        if ($request->filled('start_date')) {
            $filteredQuery->whereDate('tanggal_deadline', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $filteredQuery->whereDate('tanggal_mulai', '<=', $request->end_date);
        }

        $total = (clone $filteredQuery)->count();
        $selesai = (clone $filteredQuery)->where('status', 1)->count(); 
        $progress = (clone $filteredQuery)->where('status', 0)->whereDate('tanggal_deadline', '>=', now())->count();
        $overdue = (clone $filteredQuery)->where('status', '!=', 1)
                                        ->whereDate('tanggal_deadline', '<', now())
                                        ->count();

        // 3. Perhitungan untuk Hari Ini & Besok (MENGABAIKAN filter tanggal dari request)
        // Gunakan $baseQuery agar filter user/project tetap jalan, tapi tanggal tidak
        $Hariini = (clone $baseQuery)->whereDate('tanggal_deadline', now()->toDateString())->count();
        $Besok = (clone $baseQuery)->whereDate('tanggal_deadline', now()->addDay()->toDateString())->count();
        
        // List Deadline Hari Ini (juga abaikan filter tanggal request)
        $deadlinesHariIni = (clone $baseQuery)
            ->whereDate('tanggal_deadline', now()->toDateString())
            ->where('status', '!=', 2) 
            ->limit(5)
            ->get();

        // Point Bulan Ini (biasanya tetap ingin sesuai filter atau bulan berjalan, 
        // jika ingin tetap sesuai bulan berjalan saja tanpa terpengaruh filter date, pakai $baseQuery)
        $pointBulanIni = (clone $baseQuery)
            ->where('status', 1) 
            ->whereBetween('tanggal_deadline', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('point');
            
        $aktivitasMingguIni = (clone $filteredQuery)
            ->with(['pics', 'projectClient'])
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        return response()->json([
            'total' => $total,
            'selesai' => $selesai,
            'progress' => $progress,
            'overdue' => $overdue,
            'persenSelesai' => $total > 0 ? round($selesai / $total * 100, 1) : 0,
            'persenProgress' => $total > 0 ? round($progress / $total * 100, 1) : 0,
            'persenOverdue' => $total > 0 ? round($overdue / $total * 100, 1) : 0,
            'kpiHariIni' => $Hariini,
            'deadlineBesok' => $Besok,
            'totalPoint' => $pointBulanIni,
            'deadlines' => $deadlinesHariIni,
            'aktivitas' => $aktivitasMingguIni,
        ]);
    }

    public function getTaskStats(Request $request) 
    {
        $user = Auth::user();
        
        // 1. Logika penentuan akses user
        if ($user->isAdmin) {
            $allowedUserIds = \App\Models\User::unlocked()->where('isAdmin', false)->pluck('id')->toArray();
        } else {
            $allowedUserIds = \App\Models\userDivision::where('parent_id', $user->id)
                ->orWhere('user_id', $user->id)
                ->pluck('user_id')
                ->toArray();
        }

        // 2. Query Utama
        $query = \App\Models\Task::query()
            ->leftJoin('produk_client', 'task.project', '=', 'produk_client.id')
            ->leftJoin('users', 'task.pic', '=', 'users.id')
            ->leftJoin('kpi', 'task.id_kpi', '=', 'kpi.id')
            ->leftJoin('user_division', 'users.id', '=', 'user_division.user_id')
            ->whereIn('task.pic', $allowedUserIds);
        
        $clientFilter = $request->query('client');
        $picFilter = $request->query('pic');
        $activityPic = $request->query('activity_pic');
        $timerPic = $request->query('timer_pic');
        $divisiFilter = $request->query('divisi_id');
        if ($clientFilter && $clientFilter !== 'all') {
            $query->where('task.project', $clientFilter);
        } elseif ($picFilter && $picFilter !== 'all') {
            $query->where('task.pic', $picFilter);
        }

        if ($divisiFilter && $divisiFilter !== 'all') {
            $query->where('user_division.division_id', $divisiFilter);
        }

        // 3. Filter Tanggal
        $start = $request->query('start');
        $end = $request->query('end');
        if ($start && $end) {
            $query->whereBetween('task.tanggal_mulai', [$start, $end]);
        } elseif ($start) {
            $query->whereDate('task.tanggal_mulai', '>=', $start);
        } elseif ($end) {
            $query->whereDate('task.tanggal_mulai', '<=', $end);
        } else {
            $query->whereDate('task.tanggal_mulai', now());
        }

        $baseQuery = clone $query;

        // 4. Hitung Jumlah User untuk Rata-rata
        $jumlahUser = ($picFilter && $picFilter !== 'all') ? 1 : count($allowedUserIds);
        $jumlahUser = max($jumlahUser, 1);

        // 5. Statistik
        $stats = [
            'not_start' => (clone $baseQuery)->where('task.status', 0)->count(),
            'running'   => (clone $baseQuery)->where('task.status', 1)->count(),
            'waiting'   => (clone $baseQuery)->where('task.status', 3)->count(),
            'done'      => (clone $baseQuery)->where('task.status', 4)->count(),
            'overdue'   => (clone $baseQuery)->whereIn('task.status', [7, 9])->count(),
            'expired'   => (clone $baseQuery)->where('task.status', 8)->count(),
        ];

        // 6. Logika Produktivitas (Dibuat Rata-rata)
        $totalMenit = (clone $baseQuery)->sum('task.menit');
        $rataRataMenit = $totalMenit / $jumlahUser;
        
        $totalJam = floor($rataRataMenit / 60);
        $sisaMenit = round($rataRataMenit % 60);

        $totalTaskSelesai = (clone $baseQuery)->where('task.status', 4)->count();
        $rataRataTaskSelesai = $totalTaskSelesai / $jumlahUser;
        
        $jamKerjaDesimal = $totalJam + ($sisaMenit / 60);
        $taskPerJam = $jamKerjaDesimal > 0 ? round($rataRataTaskSelesai / $jamKerjaDesimal, 1) : 0;

        // Estimasi Sisa (rata-rata per user)
        $avgMenitPerTask = \App\Models\Task::whereIn('pic', $allowedUserIds)->where('status', 4)->avg('menit') ?? 0;
        $jumlahTaskSisa = (clone $baseQuery)->where('task.status', '!=', 4)->count();
        $estimasiJam = round((($avgMenitPerTask * $jumlahTaskSisa) / $jumlahUser) / 60, 1);

        // 7. Data Pendukung
        $taskHariIni = (clone $baseQuery)->select('task.*', 'produk_client.nama as nama_client', 'users.email as email_pic', 'kpi.nama_kpi as nama_kpi')->orderBy('task.jam_mulai', 'ASC')->get();
        $activeTask = (clone $baseQuery)->where('task.status', 1)->select('task.*', 'produk_client.nama as nama_client')->first();
        $antrian = (clone $baseQuery)->where('task.status', 0)->select('task.*', 'produk_client.nama as nama_client', 'users.email as email_pic')->orderBy('task.jam_mulai', 'asc')->limit(3)->get();
        
        $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        // 8. Timer
        

        $queryHariIni = \App\Models\Task::query()
            ->leftJoin('produk_client', 'task.project', '=', 'produk_client.id')
            ->leftJoin('users', 'task.pic', '=', 'users.id')
            ->whereIn('task.pic', $allowedUserIds)
            ->whereDate('task.tanggal_mulai', now());

        if ($clientFilter && $clientFilter !== 'all') {
            $queryHariIni->where('task.project', $clientFilter);
        } elseif ($activityPic && $activityPic !== 'all') {
            $queryHariIni->where('task.pic', $activityPic);
        }

        $totalMenit = (clone $queryHariIni)->sum('task.menit');
        $rataRataMenit = $totalMenit / $jumlahUser;

        $activities = (clone $queryHariIni)
            ->select('task.*', 'users.email as email_pic')
            ->orderBy('task.jam_mulai', 'ASC')
            // ->limit(5)
            ->get()
            ->map(function ($t) {
                $statusColors = [0 => 'secondary', 1 => 'primary', 3 => 'warning', 4 => 'success'];
                return [
                    'time'  => $t->jam_mulai, 
                    'color' => $statusColors[$t->status] ?? 'secondary', 
                    'desc'  => $t->nama_task,
                    'email' => $t->email_pic 
                ];
            });
        
        if($timerPic && $timerPic !== 'all'){
            $queryHariIni->where('task.pic', $timerPic);
        }else if($timerPic && $timerPic == 'all'){
            $queryHariIni->where('task.pic', Auth::id());
        }

        $activeNow = (clone $queryHariIni)->where('task.status', 1)->select('task.*', 'produk_client.nama as nama_client')->first();
        
        $timerData = null;
        if ($activeNow) {
            $mulai = \Carbon\Carbon::parse($activeNow->tanggal_mulai . ' ' . $activeNow->jam_mulai);
            $detikBerlalu = max(0, $mulai->diffInSeconds(now()));
            $timerData = [
                'is_active' => true,
                'detik_berlalu' => $detikBerlalu,
                'target_menit' => $activeNow->menit,
                'persentase' => min(round(($detikBerlalu / 60 / max($activeNow->menit, 1)) * 100), 100)
            ];
        }

        return response()->json([
            'stats' => $stats,
            'totalHariIni' => (clone $baseQuery)->count(),
            'pDone' => (clone $baseQuery)->count() > 0 ? round(($stats['done'] / (clone $baseQuery)->count()) * 100) : 0,
            'tasks' => $taskHariIni,
            'active_task' => $activeTask,
            'queue' => $antrian,
            'productivity' => [
                'jam_kerja' => $totalJam . 'j ' . $sisaMenit . 'm',
                'task_per_jam' => $taskPerJam,
                'total_estimasi' => $estimasiJam . ' jam'
            ],
            'activities' => $activities,
            'focus_timer' => $timerData,
            'time' => $currentTime
        ]);
    }

    public function getActiveTask(Request $request) 
    {
        $today = now()->toDateString();
        $divisiId = $request->input('divisi_id');
        $userId = $request->input('user_id');

        $query = \App\Models\Task::query()->with('user')->where('status', 1)
                                ->whereDate('tanggal_mulai', '<=', $today)
                                ->whereDate('tanggal_deadline', '>=', $today);

        // Filter Divisi
        if ($divisiId && $divisiId !== 'all') {
            $query->whereHas('user', function($q) use ($divisiId) {
                // Melakukan join atau whereHas ke tabel user_division melalui relasi userDivisions
                $q->whereHas('userDivisions', function($subQ) use ($divisiId) {
                    $subQ->where('division_id', $divisiId);
                });
            });
        }

        // Filter User
        if ($userId && $userId !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('id', $userId));
        }

        $allActive = $query->get(); // Ambil semua untuk dihitung jumlahnya

        return response()->json([
            'count' => $allActive->count(),
            'user' => $allActive,
            'tasks' => $allActive->map(function($task) {
                return [
                    'nama' => $task->nama_task,
                    'mulai' => $task->tanggal_mulai,
                    'project' => $task->projectClient->nama ?? 'Tanpa Project',
                    'pic_name' => $task->user->email,
                    'jam_mulai' => $task->jam_mulai
                ];
            })
        ]);
    }
}
