<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class HCDailyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Daily Schedule";
        $today = Carbon::today();
        $user = Auth::user();

        $hierarchy = [
            'digital_marketing'         => ['content_creator'],
            'digital_marketing_manager' => ['digital_marketing'],
            'head_of_division'          => ['digital_marketing_manager'],
        ];

        $atasan = '';
        switch($user->role){
            case 'head_of_division': $atasan = 'hod'; break;
            case 'digital_marketing_manager': $atasan = 'dmm'; break;
            case 'digital_marketing': $atasan = 'dm'; break;
        }

        $query = Task::leftJoin('users', 'users.id', '=', 'task.pic')->join('produk_client', 'produk_client.id', '=', 'task.project')
            ->select('task.*', 'users.email as vic_email', 'users.role as vic_role', 'produk_client.nama');

        $query->where(function($q) use ($user, $today, $hierarchy, $atasan) {
            // 1. DATA MILIK USER LOGIN
            $q->where('task.pic', $user->id)
                ->where(function($sub) use ($today) {
                    
                    // JALUR GRID
                    $sub->where(function($grid) use ($today) {
                        $grid->whereNotNull('task.jam_mulai')
                            ->where(function($statusQ) use ($today) {
                                // Status Aktif: 0 (Pending), 1 (In Progress), 3 (Waiting)
                                $statusQ->whereIn('task.status', [0, 1, 3])
                                    // Status 5 (Split): Hanya tarik jika tanggal_split adalah HARI INI
                                    ->orWhere(function($splitToday) use ($today) {
                                        $splitToday->where('task.status', 5)
                                                ->whereDate('task.tanggal_split', $today);
                                    })
                                    // Status 4 (Done): Hanya tarik jika tanggal_approve adalah HARI INI
                                    ->orWhere(function($doneToday) use ($today) {
                                        $doneToday->where('task.status', 4)
                                                ->whereDate('task.tanggal_approve', $today);
                                    });
                            });
                    })
                    
                    // JALUR POOL & OVERDUE (Tetap sama)
                    ->orWhere(function($pool) use ($today) {
                        $pool->whereNull('task.jam_mulai')
                            ->whereIn('task.status', [0, 2])
                            ->whereDate('task.tanggal_deadline', '>=', $today);
                    })
                    ->orWhere(function($overdue) use ($today) {
                        $overdue->whereDate('task.tanggal_deadline', '<', $today)
                                ->whereNotIn('task.status', [4, 5]);
                    });
                });

            // 2. DATA BAWAHAN (Approval)
            if ($atasan != '' && isset($hierarchy[$user->role])) {
                $q->orWhere(function($bawahan) use ($atasan, $user) {
                    $bawahan->where('users.'.$atasan, $user->id)
                            ->where('task.status', 3);
                });
            }
        });

        $all_tasks = $query->get();

        // --- FILTERING UNTUK VIEW ---

        $unscheduled_tasks = $all_tasks->filter(function ($task) {
            return is_null($task->jam_mulai);
        });

        $scheduled_tasks = $all_tasks->filter(function ($task) use ($user, $today) {
            // Syarat Mutlak: Harus punya jam_mulai dan bukan revisi (2)
            if (is_null($task->jam_mulai)) return false;

            // Logika Bawahan
            if ($task->pic != $user->id) return $task->status == 3;

            // Logika Milik Sendiri
            // TAMPILKAN STATUS 5 JIKA TANGGAL_SPLIT HARI INI
            if ($task->status == 5) {
                return $task->tanggal_split && \Carbon\Carbon::parse($task->tanggal_split)->isToday();
            }

            // Tampilkan Status 4 jika Approve hari ini
            if ($task->status == 4) {
                return $task->tanggal_approve && \Carbon\Carbon::parse($task->tanggal_approve)->isToday();
            }

            // Tampilkan status aktif (0, 1, 3)
            return in_array($task->status, [0, 1, 2, 3]);
        });

        $totalMenitPool = $unscheduled_tasks->sum('menit');
        $jam = floor($totalMenitPool / 60);
        $menitSisa = $totalMenitPool % 60;
        $formatTotalPool = ($jam > 0 ? $jam . 'j ' : '') . $menitSisa . 'm';

        $priority_colors = [1 => 'info', 2 => 'warning', 3 => 'danger'];

        return view('hc.dailywork', compact('title', 'unscheduled_tasks', 'scheduled_tasks', 'priority_colors', 'formatTotalPool'));
    }

    public function updateSchedule(Request $request)
    {   
        $task = Task::find($request->id);
        if (!$task) return response()->json(['success' => false], 404);

        if ($request->has('reset') && $request->reset == true) {
            $task->update(['jam_mulai' => null, 'status' => 0]);
            return response()->json(['success' => true]);
        }

        if ($request->has('top')) {
            $newTime = Carbon::createFromTime(9, 0, 0)->addMinutes($request->top);
            $task->update([
                'jam_mulai' => $newTime->format('H:i:s'),
                'tanggal_mulai' => Carbon::today(),
                'status' => ($task->status == 2) ? 0 : $task->status 
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request)
    {
        $task = Task::find($request->id);
        if (!$task) return response()->json(['success' => false], 404);

        $action = $request->action;
        if ($request->action == 'play') {
            $updateData = ['status' => 1];
            if ($task->jam_mulai) {
                $updateData['jam_mulai'] = $task->jam_mulai;
            } else {
                $updateData['jam_mulai'] = Carbon::now('Asia/Jakarta')->format('H:i');
            }

            $task->update($updateData);
        } elseif ($action == 'stop') {
            $task->update(['status' => 3]); // Menunggu Approval
        } elseif ($action == 'revisi') {
            $task->update(['status' => 2, 'jam_mulai' => null, 'tanggal_revisi' => now()]); // Balik ke Pool Jadi Revisi
        } elseif ($action == 'approve') {
            $task->update(['status' => 4, 'tanggal_approve' => now()]); // Selesai
        }

        return response()->json(['success' => true]);
    }

    public function cancelRemaining(Request $request)
    {
        $task = Task::find($request->id);
        if (!$task) return response()->json(['error' => 'Task tidak ditemukan'], 404);
        // Ambil jam saja dari kolom jam_mulai (misal: 15:45:02)
        $jamSaja = $task->jam_mulai; 
        
        // Paksa gabungkan dengan TANGGAL HARI INI, bukan dari database
        // Ini akan menghasilkan: "2026-02-12 15:45:02" (Asumsi hari ini tgl 12)
        $startTime = Carbon::parse(Carbon::now()->format('Y-m-d') . ' ' . $jamSaja);
        
        $now = Carbon::now();
        
        // Sekarang selisihnya pasti kecil karena tanggalnya sama-sama hari ini
        $usedMinutes = $startTime->diffInMinutes($now);
        if ($usedMinutes <= 0) $usedMinutes = 1;
        if ($usedMinutes < $task->menit) {
            $remainingMinutes = $task->menit - $usedMinutes;

            // Proses Duplikasi Manual
            $data = $task->toArray();
            unset($data['id']);
            
            // 3. Modifikasi isi data untuk Task Pool yang baru
            $data['nama_task']             = $task->nama_task;
            $data['divisi']                = $task->divisi;
            $data['pic']                   = $task->pic;
            $data['priority']              = $task->priority;
            $data['menit']                 = $remainingMinutes;
            $data['id_manajement_konten']  = $task->id_manajement_kontent;
            $data['tanggal_mulai']         = $task->tanggal_mulai;
            $data['tanggal_deadline']      = $task->tanggal_deadline;
            $data['note']                  = $task->note;
            $data['status']                = 0;
            $data['project']                = $task->project;
            $data['jam_mulai']             = null;
            $data['tanggal_approve']             = null;
            $data['tanggal_revisi']             = null;
            $data['tanggal_split']             = null;

            // 4. Masukkan ke database sebagai baris baru
            $newTask = Task::create($data);

            // 5. Update task lama yang nempel di Grid
            $task->update([
                'menit'  => $usedMinutes,
                'status' => 5, // Langsung DONE
                'tanggal_split' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task baru "' . $newTask->nama_task . '" telah dibuat di pool.'
            ]);

        } else {
            $task->update(['status' => 5, 'tanggal_split' => now()]);
            return response()->json(['success' => true]);
        }
    }

    private function getStatusLabel($status)
    {
        $labels = [0 => 'Todo', 1 => 'In Progress', 2 => 'Need Revision', 3 => 'Waiting Approval', 4 => 'Done'];
        return $labels[$status] ?? 'Unknown';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);
        if ($task) {
            // Opsi A: Hapus permanen
            // $task->delete();
            
            // Opsi B: Reset status agar kembali ke list bulatan (Source)
            $task->status = 0; // Atau null, tergantung struktur DB Anda
            $task->jam_mulai = null;
            $task->save();
            
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
