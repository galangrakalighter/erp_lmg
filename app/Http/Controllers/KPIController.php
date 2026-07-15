<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class KPIController extends Controller
{
    public function getKpiStats(Request $request) 
    {
        $isAdmin = Auth::user()->isAdmin == true;

        // 1. Base Query (Filter User/Project saja, TANPA filter tanggal)
       if ($isAdmin) {
            if ($request->filled('user_id')) {
                $baseQuery = \App\Models\KPI::whereHas('pics', function($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            } else {
                $baseQuery = \App\Models\KPI::query();
            }
        } else {
            // Non-admin: 
            // 1. Tampilkan jika dia adalah PIC (kpis)
            // 2. ATAU Tampilkan jika KPI tersebut memiliki divisi yang sama dengan miliknya
            $userDivisions = Auth::user()->userDivisions()->pluck('division_id')->toArray();

            $baseQuery = \App\Models\KPI::where(function($q) use ($request, $userDivisions) {
                // Kondisi A: Jika ada filter user_id dari request, pakai itu
                if ($request->filled('user_id')) {
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
        if ($request->filled('project_id')) {
            $baseQuery->where('project', $request->project_id);
        }

        // 2. Query untuk Statistik Berdasarkan Rentang Tanggal (Filter Start & End)
        $filteredQuery = (clone $baseQuery);
        if ($request->filled('start_date')) $filteredQuery->where('tanggal_mulai', '>=', $request->start_date);
        if ($request->filled('end_date')) $filteredQuery->where('tanggal_deadline', '<=', $request->end_date);

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
    
        // Logika menentukan user mana saja yang datanya boleh dilihat
        if ($user->isAdmin) {
            // Admin melihat semua (hanya non-admin)
            $allowedUserIds = \App\Models\User::where('isAdmin', false)->pluck('id')->toArray();
        } else {
            // Bukan Admin: ambil dari user_division (diri sendiri + bawahan)
            $allowedUserIds = \App\Models\UserDivision::where('parent_id', $user->id)
                ->orWhere('user_id', $user->id)
                ->pluck('user_id')
                ->toArray();
        }

        $query = \App\Models\Task::query()
            ->leftJoin('produk_client', 'task.project', '=', 'produk_client.id')
            ->leftJoin('users', 'task.pic', '=', 'users.id')
            ->leftJoin('kpi', 'task.id_kpi', '=', 'kpi.id')
            // Terapkan pembatasan di sini agar semua clone nantinya otomatis terfilter
            ->whereIn('task.pic', $allowedUserIds);
        
        // Terapkan Filter Client (Project)
        $clientFilter = $request->query('client');
        $picFilter = $request->query('pic');
        // 2. Terapkan Filter Client (Paling Prioritas)
        if ($clientFilter && $clientFilter !== 'all') {
            $query->where('task.project', $clientFilter);
        } else {
            if ($picFilter && $picFilter !== 'all') {
                $query->where('task.pic', $picFilter);
            }
        }

        $start = $request->query('start');
        $end = $request->query('end');

        if ($start && $end) {
            $query->whereBetween('task.tanggal_mulai', [$start, $end]);
        } elseif ($start) {
            $query->whereDate('task.tanggal_mulai', '>=', $start);
        } elseif ($end) {
            $query->whereDate('task.tanggal_mulai', '<=', $end);
        } else {
            // Jika tidak ada filter tanggal, default ke hari ini
            $query->whereDate('task.tanggal_mulai', now());
        }

        $baseQuery = clone $query;

        $stats = [
            'not_start' => (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', 0)->count(),
            'running'   => (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', 1)->count(),
            'waiting'   => (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', 3)->count(),
            'done'      => (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', 4)->count(),
            'overdue'   => (clone $baseQuery)->whereIn('task.status', [7, 9])->count(),
            'expired'   => (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', 8)->count(),
        ];

        $totalMenit = (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->sum('task.menit');

        // Konversi ke format Jam dan Menit
        $totalJam = floor($totalMenit / 60);
        $sisaMenit = $totalMenit % 60;

        // Menghitung Task per Jam (Jumlah task selesai hari ini / Total jam kerja)
        $totalTaskSelesai = (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', 4)->count();
        $jamKerjaDesimal = $totalJam + ($sisaMenit / 60);
        $taskPerJam = $jamKerjaDesimal > 0 ? round($totalTaskSelesai / $jamKerjaDesimal, 1) : 0;

        // Hitung Total Estimasi (jika ada kolom estimasi_menit)
        $avgMenitPerTask = \App\Models\Task::where('status', 4)->avg('menit') ?? 0;

        // Jumlah task yang belum selesai hari ini
        $jumlahTaskSisa = (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->where('task.status', '!=', 4)->count();

        $estimasiSisaMenit = $avgMenitPerTask * $jumlahTaskSisa;
        $estimasiJam = round($estimasiSisaMenit / 60, 1);

        $totalHariIni = (clone $baseQuery)->whereDate('task.tanggal_mulai', now())->count();
        
        // Pastikan select nama_client agar tidak null
        $taskHariIni = (clone $baseQuery)->select('task.*', 'produk_client.nama as nama_client', 'users.email as email_pic', 'kpi.nama_kpi as nama_kpi')
                                        ->get();
        
        // REVISI: Tambahkan select agar nama_client terbawa
       if (!Auth::user()->isAdmin) {
            // Jika BUKAN Admin: Hanya boleh melihat Active Task MILIKNYA SENDIRI
            $activeTask = (clone $baseQuery)
                ->where('task.status', 1)
                ->select('task.*', 'produk_client.nama as nama_client')
                ->first();
        } else {
            // Jika ADMIN: Tampilkan Active Task milik PIC yang difilter, 
            // atau prioritaskan milik Admin sendiri jika melihat "Semua Bawahan"
            $activeTask = (clone $baseQuery)
                ->where('task.status', 1)
                ->select('task.*', 'produk_client.nama as nama_client')
                ->first();
        }

        // 2. Ambil Antrian (Diprioritaskan untuk milik sendiri, baru berdasarkan priority)
        $antrian = (clone $baseQuery)
            ->where('task.status', 0)
            ->select('task.*', 'produk_client.nama as nama_client')
            // Prioritaskan milik sendiri dulu, baru diikuti sorting priority
            ->orderBy('task.jam_mulai', 'asc')
            ->limit(3)
            ->get();

        $activities = (clone $baseQuery)
            ->whereDate('task.tanggal_mulai', now())
            ->orderBy('task.jam_mulai', 'ASC')
            ->limit(5)
            ->get()
            ->map(function ($t) {
                // Tentukan label dan warna berdasarkan status
                $statusLabels = [0 => 'Antrian', 1 => 'Sedang Dikerjakan', 3 => 'Waiting', 4 => 'Selesai'];
                $statusColors = [0 => 'secondary', 1 => 'primary', 3 => 'warning', 4 => 'success'];
                
                return [
                    'time'  => $t->jam_mulai,
                    'color' => $statusColors[$t->status] ?? 'secondary',
                    'desc'  => $t->nama_task
                ];
            });
        
        $timerData = null;
        if ($activeTask) {
            // Gabungkan tanggal dan jam mulai dari database
            $mulai = \Carbon\Carbon::parse($activeTask->tanggal_mulai . ' ' . $activeTask->jam_mulai);
            $sekarang = \Carbon\Carbon::now();
            
            // Hitung total detik berlalu (Gunakan max agar tidak mungkin minus)
            $detikBerlalu = max(0, $mulai->diffInSeconds($sekarang));
            
            // return response()->json([
            $timerData = [
                    'is_active' => true,
                    'detik_berlalu' => $detikBerlalu, // Kirim ke JS dalam detik
                    'target_menit' => $activeTask->menit,
                    'persentase' => min(round(($detikBerlalu / 60 / max($activeTask->menit, 1)) * 100), 100)
                ];
        }
        $namaHari = date('l');

        if($picFilter == 'all'){
            if(Auth::user()->isAdmin == true){
                if($namaHari == 'Saturday'){
                    $totalJam = 5;
                    $sisaMenit = 0;
                }else{
                    $totalJam = 7;
                    $sisaMenit = 0;
                }
            }
        }

        return response()->json([
            'stats' => $stats,
            'totalHariIni' => $totalHariIni,
            // Gunakan $stats['done'] untuk persentase agar sinkron
            'pDone' => $totalHariIni > 0 ? round(($stats['done'] / $totalHariIni) * 100) : 0,
            'tasks' => $taskHariIni,
            'active_task' => $activeTask,
            'queue' => $antrian,
            'productivity' => [
                'jam_kerja' => $totalJam . 'j ' . $sisaMenit . 'm',
                'task_per_jam' => $taskPerJam,
                'total_estimasi' => $estimasiJam . ' jam'
            ],
            'activities' => $activities,
            'focus_timer' => $timerData
        ]);
    }
}
