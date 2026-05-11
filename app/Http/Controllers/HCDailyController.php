<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\ProdukClient;
use App\Models\Division;
use Illuminate\Support\Facades\DB;
// use Midtrans\Config;
// use Midtrans\Snap;
use Carbon\CarbonPeriod;
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
        $hierarchy = [];

        if ($user->isAdmin) {
            // LOGIKA SUPER ADMIN
            $RoleSekarang = "Super Admin";
            
            // Super Admin bisa melihat SEMUA role yang ada di database
            $hierarchy = DB::table('roles')->pluck('role_name')->toArray();
            
        } else {
            // LOGIKA USER BIASA (Berdasarkan Divisi & Level)
            $userDivisi = $user->userDivisions->first(); 

            if ($userDivisi) {
                $currentRole = DB::table('roles')->where('id', $userDivisi->role_id)->first();
                
                if ($currentRole) {
                    $RoleSekarang = $currentRole->role_name;
                    
                    // Ambil role yang satu divisi DAN levelnya di bawah
                    $hierarchy = DB::table('roles')
                        ->where('division_id', $currentRole->division_id)
                        ->where('level', '>', $currentRole->level)
                        ->pluck('role_name')
                        ->toArray();
                }
            }
        }

        $level = Role::where('role_name', $RoleSekarang)->first();
        if($level == null){
            $CurrentLevel = 0;
            $role = "Super Admin";
        }else{
            $CurrentLevel = $level->level;
            $role = $level->role_name;
        }

        $prioritas = [1 => 'Rendah', 2 => 'Sedang', 3 => 'Tinggi'];
        $atasan = $role;

        $query = Task::leftJoin('users', 'users.id', '=', 'task.pic')
            ->join('produk_client', 'produk_client.id', '=', 'task.project')
            ->join('user_division', 'user_division.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->select('task.*', 'users.email as vic_email', 'roles.role_name as vic_role', 'produk_client.nama');
        
        $query->whereMonth('task.tanggal_mulai', $today->month)
              ->whereYear('task.tanggal_mulai', $today->year);

        $query->where(function($q) use ($user, $today, $hierarchy, $atasan) {
            // 1. DATA MILIK USER LOGIN
            $q->where('task.pic', $user->id)
                ->where(function($sub) use ($today) {
                    
                    // JALUR GRID
                    $sub->where(function($grid) use ($today) {
                        $grid->whereNotNull('task.jam_mulai')
                            // TAMBAHKAN INI: Memastikan task tersebut dijadwalkan untuk HARI INI
                            ->whereDate('task.tanggal_mulai', $today) 
                            ->where(function($statusQ) use ($today) {
                                // Status Aktif: 0, 1, 3, 7
                                $statusQ->whereIn('task.status', [0, 1, 2, 3, 7, 8, 9])
                                    // Status 5 (Split): Hanya jika tanggal_split hari ini
                                    ->orWhere(function($splitToday) use ($today) {
                                        $splitToday->where('task.status', 5)
                                                ->whereDate('task.tanggal_split', $today);
                                    })
                                    // Status 4 (Done): Hanya jika tanggal_approve hari ini
                                    ->orWhere(function($doneToday) use ($today) {
                                        $doneToday->where('task.status', 4)
                                                ->whereDate('task.tanggal_approve', $today);
                                    });
                            });
                    })
                    
                    // JALUR POOL & OVERDUE (Tetap sama)
                    ->orWhere(function($pool) use ($today) {
                        $pool->whereNull('task.jam_mulai')
                            ->whereIn('task.status', [0, 7])
                            ->whereDate('task.tanggal_deadline', '>=', $today);
                    })
                    ->orWhere(function($overdue) use ($today) {
                        $overdue->whereDate('task.tanggal_deadline', '<', $today)
                                ->whereNotIn('task.status', [4, 5]);
                    });
                });

            // 2. DATA BAWAHAN (Approval)
           if ($user->isAdmin == true) {
                $q->orWhereIn('task.status', [2, 3, 7, 9]); 
            } 
            elseif ($atasan != '' && isset($hierarchy[$user->role])) {
                $q->orWhere(function($bawahan) use ($atasan, $user) {
                    $bawahan->where('users.'.$atasan, $user->id)
                            ->where('task.status', 3);
                });
            }
        });

        $all_tasks = $query->get();
        $user = Auth::user();
        $tugas_bawahan = Task::leftjoin('users', 'users.id', '=', 'task.pic')
        ->leftjoin('user_division', 'user_division.user_id', '=', 'users.id')
        ->select('task.*', 'users.id as user_id', 'user_division.parent_id','users.email');
        if($CurrentLevel == 0){
            $tugas_bawahan->where(function($q) use ($user) {
                $q->whereNot('users.isAdmin', true);
            });
        }else{
            $tugas_bawahan->where(function($q) use ($user) {
                $q->where('user_division.parent_id', $user->id)->orWhere('task.pic', $user->id);
            });
        }

        $startHour = (Auth::id() == 39) ? 8 : 9;
        $totalHours = 17 - $startHour;

        $subordinateTasks = $tugas_bawahan->whereNotNull('jam_mulai')->whereDate('task.tanggal_mulai', $today)->get();

        if($CurrentLevel == 0){
            $bawahanIds = User::whereNot('users.isAdmin', true)->pluck('id');
        }else{
            $bawahanIds = User::join('user_division', 'user_division.user_id', '=', 'users.id')->where('user_division.parent_id', Auth::id())->pluck('id');
        }

        $unscheduled_tasks = $all_tasks->filter(function ($task) use ($today, $bawahanIds) {
            $validStatus = in_array($task->status, [2, 3, 7, 9]);

            $tglMulai = \Carbon\Carbon::parse($task->tanggal_mulai)->startOfDay();
            $tglDeadline = \Carbon\Carbon::parse($task->tanggal_deadline)->endOfDay();
            $currentDate = \Carbon\Carbon::parse($today)->startOfDay();

            $inRange = $currentDate->between($tglMulai, $tglDeadline);

            $checkApprove = true;
            if ($task->status == 7) {
                $checkApprove = is_null($task->tanggal_approve);
            }

            if ($task->status == 3 || $task->status == 9) {
                $isBawahanTask = $bawahanIds->contains($task->pic);
                if ($task->status == 9) {
                    $dateCheck = $isBawahanTask; 
                } else {
                    $dateCheck = $isBawahanTask && $currentDate->gt($tglDeadline);
                }
            } else {
                $dateCheck = ($task->status == 7) || $inRange;
            }

            return $validStatus && $dateCheck && $checkApprove;
        });

        $scheduled_tasks = $all_tasks->filter(function ($task) use ($user, $today) {
            // Syarat Mutlak: Harus punya jam_mulai dan bukan revisi (2)
            if (is_null($task->jam_mulai)) return false;

            if ($task->status == 5) {
                return $task->tanggal_split && \Carbon\Carbon::parse($task->tanggal_split)->isToday();
            }

            if ($task->status == 4) {
                return $task->tanggal_approve && \Carbon\Carbon::parse($task->tanggal_approve)->isToday();
            }

            if($task->status == 7){
                return \Carbon\Carbon::parse($task->tanggal_mulai)->isToday() && $task->jam_mulai != null;
            }

            // Tampilkan status aktif (0, 1, 3)
            return in_array($task->status, [0, 1, 2, 3, 7, 8]);
        });

        $scheduled_tasks = $scheduled_tasks->concat($subordinateTasks)->unique('id');
        $totalMenitPool = $unscheduled_tasks->sum('menit');
        $jam = floor($totalMenitPool / 60);
        $menitSisa = $totalMenitPool % 60;
        $formatTotalPool = ($jam > 0 ? $jam . 'j ' : '') . $menitSisa . 'm';

        $priority_colors = [1 => 'info', 2 => 'warning', 3 => 'danger'];
            
        $divisi_list = Division::pluck('name', 'id');
        $divisi = User::find(Auth::user()->id)->userDivisions;

        $result = [];
        if($RoleSekarang == "Super Admin"){
            $result = $divisi_list;
        }else{
            foreach($divisi_list as $divId => $nama) {
                if($divId == $divisi[0]->division_id) {
                    $result[$divId] = $nama;
                }
            }
        }

        if(Auth::user()->isAdmin == true){
            $produk = DB::table('produk_client')->get();
        }else{
            $handleClient = Auth::user()->clients->pluck('id')->toArray();
            $produk = ProdukClient::select('id', 'nama')->whereIn('id', $handleClient)->get();
        }

        return view('hc.dailywork', compact('title', 'unscheduled_tasks', 'scheduled_tasks', 'priority_colors', 'startHour','totalHours','formatTotalPool', 'subordinateTasks', 'result', 'prioritas', 'produk'));
    }

    public function updateSchedule(Request $request)
    {   
        $task = Task::find($request->id);
        if (!$task) return response()->json(['success' => false], 404);

        if ($request->has('reset') && $request->reset == true) {
            if($task->status == 7){
                $task->update(['jam_mulai' => null]);
            }else{
                $task->update(['jam_mulai' => null, 'status' => 0]);
            }
            return response()->json(['success' => true]);
        }

        if ($request->has('top')) {
            $newTime = Carbon::createFromTime(8, 0, 0)->addMinutes($request->top);
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
            if($task->status == 7){
                $task->update(['status' => 9, 'jam_mulai' => $task->jam_mulai]);
            }else{
                $task->update(['status' => 3]); // Menunggu Approval
            }
        } elseif ($action == 'revisi') {
            $task->update(['status' => 2, 'tanggal_revisi' => now()]); // Balik ke Pool Jadi Revisi
        } elseif ($action == 'approve') {
            if($task->status == 9){
                $task->update(['status' => 7,'tanggal_approve' => now()]); // Selesai
            }else{
                $task->update(['status' => 4, 'tanggal_approve' => now()]); // Selesai
            }
        } elseif ($action == 'set_overdue'){
            $task->update(['status' => 7]);
        } elseif ($action == 'habis'){
            $task->update(['status' => 8]);
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

    public function getTaskJSON(Request $request){
        $userId = $request->query('user_id');
        $userId = (int)$userId;
        $today = \Carbon\Carbon::today();

        $tasks = Task::leftJoin('users', 'users.id', '=', 'task.pic')
            ->join('produk_client', 'produk_client.id', '=', 'task.project')
            ->join('user_division', 'user_division.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->select('task.*', 'users.email as vic_email', 'produk_client.nama as project_name', 'roles.role_name as vic_role', 'user_division.parent_id')
            ->where('task.pic', $userId)
            ->whereNotNull('task.jam_mulai')
            ->where(function($query) use ($today) {
                // 1. TUGAS BARU / BERJALAN (Status 0, 1, 2, 8)
                // Hanya yang tanggal mulainya HARI INI
                $query->where(function($q) use ($today) {
                    $q->whereIn('task.status', [0, 1, 2, 8, 3])
                    ->whereDate('task.tanggal_mulai', $today);
                })

                // 2. TUGAS SPLIT (Status 5)
                // Hanya jika proses split dilakukan HARI INI
                ->orWhere(function($q) use ($today) {
                    $q->where('task.status', 5)
                    ->whereDate('task.tanggal_mulai', $today)
                    ->whereDate('task.tanggal_split', $today);
                })

                // 3. TUGAS SELESAI (Status 4)
                // Hanya jika disetujui/selesai HARI INI
                ->orWhere(function($q) use ($today) {
                    $q->where('task.status', 4)
                    ->whereDate('task.tanggal_mulai', $today)
                    ->whereDate('task.tanggal_approve', $today);
                });
            })
            ->get();

        return response()->json($tasks);
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

    public function getPic($id, $userId){
        $user = User::find($userId);
        $userQuery = $user->join('user_division', 'user_division.user_id', '=', 'users.id')
        ->join('user_client', 'user_client.user_id', '=', 'users.id')
        ->join('roles', 'roles.id', '=', 'user_division.role_id')
        ->select('users.id', 'users.email', 'roles.role_name')
        ->distinct();
        $RoleSekarang = $user->isAdmin == false ? DB::table('roles')->where('id', $user->userDivisions[0]->role_id)->value('role_name') : "Super Admin";
        $level = Role::where('role_name', $RoleSekarang)->first();
        if($level == null){
            $CurrentLevel = 0;
            $role = "Super Admin";
        }else{
            $CurrentLevel = $level->level;
            $role = $level->role_name;
        }
        $arrRole = [];
        if ($CurrentLevel == 0) {
            $vic_list = $userQuery->get();
        }else{
            $vic_list = $userQuery->where(function($q) use ($user) {
                $q->where('users.id', $user->id)->orWhere('user_division.parent_id', $user->id);
            })->get();
        }
        // } else if ($user->role == "head_of_division") {
        //     // Melihat diri sendiri + orang yang HOD-nya adalah dia
        //     $vic_list = $userQuery->where(function($q) use ($user) {
        //         $q->where('id', $user->id)->orWhere('hod', $user->id);
        //     })->get();

        // } else if ($user->role == "digital_marketing_manager") {
        //     // Melihat diri sendiri + orang yang DMM-nya adalah dia
        //     $vic_list = $userQuery->where(function($q) use ($user) {
        //         $q->where('id', $user->id)->orWhere('dmm', $user->id);
        //     })->get();

        // } else if ($user->role == "digital_marketing") {
        //     // Melihat diri sendiri + orang yang DM-nya adalah dia
        //     $vic_list = $userQuery->where(function($q) use ($user) {
        //         $q->where('id', $user->id)->orWhere('dm', $user->id);
        //     })->get();

        // } else {
        //     // Role lainnya (misal Content Creator) hanya melihat dirinya sendiri
        //     $vic_list = $userQuery->where('id', $user->id)->get();
        // }

        // 3. Mapping data untuk response JSON yang ringan dan aman
        return response()->json($vic_list->map(function($u) {
            return [
                'id' => $u->id,
                'email' => $u->email,
                'role' => $u->role_name// Opsional: tambahkan role agar di JS bisa ditrack jika perlu
            ];
        }));
        return $data;
    }

    public function storeDaily(Request $request){
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
                'project'          => (int)$request->client_id,
                'nama_task'        => $request->nama_task,
                'divisi'           => (int)$request->divisi,
                'pic'              => (int)$request->pic,
                'priority'         => (int)$request->priority,
                'menit'            => (int)$request->menit,
                'tanggal_mulai'    => $date->format('Y-m-d'),
                'tanggal_deadline' => $date->format('Y-m-d'),
                'jam_mulai'        => $request->jam_mulai,
                'note'             => $request->note,
                'status'           => 0,
                'jira'             => $request->jira_input,
            ]);
        }

        return redirect()->back()->with('success', 'Task berhasil ditambahkan ke project!');
    }

    public function getTasksById($id){
        $task = Task::find($id);
        $handleClient = User::find($task->pic);
        $client = [];
        if ($handleClient && $handleClient->client) {
            $clientIds = is_array($handleClient->client) 
                        ? $handleClient->client 
                        : json_decode($handleClient->cliepnt, true);

            $client = ProdukClient::select('id', 'nama')
                        ->whereIn('id', $clientIds)
                        ->get();
        }
        return response()->json([
            'task'   => $task,
            'client' => $client
        ]);
    }

    // public function getPaymentToken(Request $request)
    // {
    //     // 1. Konfigurasi Midtrans (Ambil dari .env)
    //     Config::$serverKey = env('MIDTRANS_SERVER_KEY');
    //     Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
    //     Config::$isSanitized = true;
    //     Config::$is3ds = true;

    //     // 2. Buat Unique Order ID (Sangat Penting)
    //     // Gabungkan ID User atau Timestamp agar tidak pernah duplikat
    //     $orderId = 'LMG-' . Auth::id() . '-' . time();

    //     // 3. Siapkan Parameter Transaksi
    //     $params = [
    //         'transaction_details' => [
    //             'order_id' => $orderId,
    //             'gross_amount' => 1000, 
    //         ],
    //         'item_details' => [
    //             [
    //                 'id' => 'PROD-001',
    //                 'price' => 1000,
    //                 'quantity' => 1,
    //                 'name' => 'Langganan CRM - 1 Bulan', // Ini yang akan muncul di rincian
    //                 'brand' => 'Lighter Media Group',
    //                 'category' => 'Software Service'
    //             ]
    //         ],
    //         'customer_details' => [
    //             'first_name' => Auth::user()->name,
    //             'email' => Auth::user()->email,
    //             'phone' => '08123456789', // Tambahkan HP jika perlu
    //         ],
    //     ];

    //     try {
    //         // 4. Minta Snap Token dari Midtrans
    //         $snapToken = Snap::getSnapToken($params);

    //         // 5. Kirim Token ke Frontend (Blade)
    //         return response()->json([
    //             'snap_token' => $snapToken,
    //             'order_id' => $orderId
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json(['message' => $e->getMessage()], 500);
    //     }
    // }
}
