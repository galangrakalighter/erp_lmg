<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\ProdukClient;
use App\Models\KPI;
use App\Models\Division;
use Illuminate\Support\Facades\DB;
// use Midtrans\Config;
// use Midtrans\Snap;
use Carbon\CarbonPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
class HCDailyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selfOnly = $request->routeIs('daily-work.self');

        return view('hc.dailywork', [
            'title' => $selfOnly ? 'Daily Work Saya' : 'Daily Work Tim',
            'selfOnly' => $selfOnly,
        ]);

        $title = "Daily Schedule";
        $today = Carbon::today('Asia/Jakarta');
        $user = Auth::user();
        $hierarchy = [];
        $RoleSekarang = '';

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
            $bawahanIds = User::unlocked()->whereNot('users.isAdmin', true)->pluck('id');
        }else{
            $bawahanIds = User::unlocked()->join('user_division', 'user_division.user_id', '=', 'users.id')->where('user_division.parent_id', Auth::id())->pluck('users.id');
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
        $myRole = 1;
        if(Auth::user()->isAdmin == false){
            $myRole = $divisi->first()->role->role_name;
        }

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

        return view('hc.dailywork', compact('title', 'unscheduled_tasks', 'scheduled_tasks', 'myRole','priority_colors', 'startHour','totalHours','formatTotalPool', 'subordinateTasks', 'result', 'prioritas', 'produk'));
    }

    public function dailyWorkData(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Sesi login sudah berakhir. Silakan login kembali.'], 401);
        }

        $today = Carbon::today('Asia/Jakarta');
        $formContext = $this->dailyWorkFormContext($currentUser);
        $selfOnly = str_starts_with((string) $request->query('scope'), 'self');

        // Daily Work yang dibuka dari Dashboard adalah ruang kerja pribadi.
        // Walaupun user memiliki bawahan, PIC form tetap hanya dirinya sendiri.
        if ($selfOnly) {
            $formContext['people'] = collect([[
                'id' => (int) $currentUser->id,
                'email' => $currentUser->email,
            ]]);
        }

        $visibleUserIds = collect([$currentUser->id]);

        if (! $selfOnly && $currentUser->isAdmin) {
            $visibleUserIds = User::pluck('id');
        } elseif (! $selfOnly) {
            $visibleUserIds = $visibleUserIds->merge(
                DB::table('user_division')->where('parent_id', $currentUser->id)->pluck('user_id')
            );
        }

        $visibleUserIds = $visibleUserIds->map(fn ($id) => (int) $id)->unique()->values();
        $selectedUserId = (int) $request->query('user_id', $currentUser->id);
        if (!$visibleUserIds->contains($selectedUserId)) {
            $selectedUserId = (int) $currentUser->id;
        }

        // Task overdue hanya kembali ke Task Pool untuk pemiliknya sendiri.
        // Saat atasan memilih anggota tim, task overdue anggota tersebut
        // ditampilkan melalui Approval Task agar tidak tercampur dengan
        // backlog pribadi atasan.
        $isViewingOwnWorkspace = $selectedUserId === (int) $currentUser->id;

        $selectedDivisionName = DB::table('user_division')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->join('divisions', 'divisions.id', '=', 'roles.division_id')
            ->where('user_division.user_id', $selectedUserId)
            ->orderBy('roles.level')
            ->value('divisions.name');
        $workdayStart = strcasecmp((string) $selectedDivisionName, 'Tech') === 0 ? 8 : 9;

        $tasks = Task::query()
            ->leftJoin('users', 'users.id', '=', 'task.pic')
            ->leftJoin('produk_client', 'produk_client.id', '=', 'task.project')
            ->leftJoin('kpi', 'kpi.id', '=', 'task.id_kpi')
            ->where('task.pic', $selectedUserId)
            ->where(function ($query) use ($today, $isViewingOwnWorkspace) {
                $query->where(function ($todayTask) use ($today) {
                    $todayTask->whereDate('task.tanggal_mulai', $today)
                        ->whereDate('task.tanggal_deadline', $today);
                });

                // Task revisi kembali menjadi backlog pemilik: tidak memiliki
                // deadline dan tetap tampil di pool sampai dijadwalkan ulang.
                // Status ini sengaja dipisahkan dari overdue.
                if ($isViewingOwnWorkspace) {
                    $query->orWhere(function ($revision) {
                        $revision->where('task.status', 2)
                            ->whereNull('task.tanggal_deadline');
                    });

                    // Status 7 = overdue yang belum dikirim. Pemilik tetap
                    // melihatnya di Task Pool, termasuk jika sebelumnya memiliki
                    // jam kerja.
                    $query->orWhere(function ($overdue) use ($today) {
                        $overdue->whereDate('task.tanggal_deadline', '<', $today)
                            ->where('task.status', 7);
                    });
                }
            })
            ->where(function ($query) use ($today) {
                $query->where(function ($scheduled) use ($today) {
                    $scheduled->whereNotNull('task.jam_mulai')
                        ->where(function ($scheduledToday) use ($today) {
                            $scheduledToday->where(function ($activeToday) {
                                $activeToday->whereNotIn('task.status', [4, 5]);
                            })->orWhere(function ($approvedToday) use ($today) {
                                $approvedToday->where('task.status', 4)
                                    ->whereDate('task.tanggal_approve', $today);
                            })->orWhere(function ($splitToday) use ($today) {
                                $splitToday->where('task.status', 5)
                                    ->whereDate('task.tanggal_split', $today);
                            });
                        });
                })->orWhere(function ($pool) use ($today) {
                    $pool->whereNull('task.jam_mulai')
                        ->whereNotIn('task.status', [4, 5]);
                });
            })
            ->select('task.*', 'users.email as pic_email',
                'produk_client.nama as project_name', 'kpi.nama_kpi as kpi_name')
            ->orderByRaw('task.jam_mulai IS NULL DESC')
            ->orderBy('task.jam_mulai')
            ->orderByDesc('task.priority')
            ->get()
            ->map(fn ($task) => $this->formatDailyTask($task, $today));

        $approvalUserIds = $selfOnly
            ? collect()
            : ($currentUser->isAdmin
                ? User::pluck('id')
                : DB::table('user_division')
                    ->where('parent_id', $currentUser->id)
                    ->pluck('user_id'));

        // Task sendiri tidak pernah menjadi item Approval, termasuk untuk
        // admin. Task overdue pribadi tetap hanya berada di Task Pool.
        $approvalUserIds = $approvalUserIds
            ->reject(fn ($userId) => (int) $userId === (int) $currentUser->id)
            ->values();

        $approvalTasks = Task::query()
            ->leftJoin('users', 'users.id', '=', 'task.pic')
            ->leftJoin('produk_client', 'produk_client.id', '=', 'task.project')
            ->leftJoin('kpi', 'kpi.id', '=', 'task.id_kpi')
            ->whereIn('task.pic', $approvalUserIds)
            // Filter anggota tim yang dipilih juga berlaku untuk task approval.
            // Sebelumnya filter ini hanya dipakai query Kanban di atas.
            ->when($request->filled('user_id'), function ($query) use ($selectedUserId) {
                $query->where('task.pic', $selectedUserId);
            })
            // Status 9 = overdue yang sudah dikirim dan menunggu approval.
            // Status 7 tidak masuk ke sini karena bukti belum dikirim.
            ->whereIn('task.status', [3, 9])
            ->select('task.*', 'users.email as pic_email',
                'produk_client.nama as project_name', 'kpi.nama_kpi as kpi_name')
            ->orderBy('task.tanggal_mulai')
            ->orderByDesc('task.priority')
            ->get()
            ->map(function ($task) use ($today) {
                $formatted = $this->formatDailyTask($task, $today);
                $formatted['can_review'] = in_array((int) $task->status, [3, 9], true);
                return $formatted;
            });

        $people = User::unlocked()->whereIn('id', $visibleUserIds)->select('id', 'email')
            ->orderBy('email')->get()->map(fn ($user) => [
                'id' => $user->id,
                'email' => $user->email,
            ]);

        return response()->json([
            'date' => $today->toDateString(),
            'current_user' => ['id' => $currentUser->id, 'is_admin' => (bool) $currentUser->isAdmin],
            'selected_user_id' => $selectedUserId,
            'workday_start' => $workdayStart,
            'workday_end' => 17,
            'people' => $people,
            'assignable_people' => $formContext['people'],
            'form_division_id' => $formContext['division_id'],
            'division_locked' => $formContext['division_locked'],
            'projects' => ProdukClient::query()
                ->leftJoin('client_division', 'client_division.client_id', '=', 'produk_client.id')
                ->leftJoin('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
                ->select('produk_client.id', 'produk_client.nama', 'client_division.division_id')
                ->selectRaw("CASE WHEN LOWER(TRIM(produk_client.nama)) = 'internal' OR LOWER(TRIM(COALESCE(kategori_jasa.category, ''))) LIKE 'internal%' THEN TRUE ELSE FALSE END AS is_internal")
                ->orderBy('produk_client.nama')
                ->get(),
            'divisions' => Division::select('id', 'name')->orderBy('name')->get(),
            'kpis' => KPI::select('id', 'nama_kpi', 'divisi as division_id', 'project as project_id')
                ->orderBy('nama_kpi')->get(),
            'tasks' => $tasks,
            'approval_tasks' => $approvalTasks,
            'summary' => [
                'scheduled' => $tasks->filter(fn (array $task) => $task['start_time'] && (int) $task['status'] !== 7)->count(),
                'pool' => $tasks->filter(fn (array $task) => ! $task['start_time'] || (int) $task['status'] === 7)->count(),
                'running' => $tasks->where('status', 1)->count(),
                'approval' => $approvalTasks->count(),
                'pool_minutes' => $tasks->filter(fn (array $task) => ! $task['start_time'] || (int) $task['status'] === 7)->sum('minutes'),
            ],
        ]);
    }

    private function formatDailyTask(object $task, Carbon $today): array
    {
        $labels = [
            0 => 'Belum dimulai', 1 => 'Sedang dikerjakan', 2 => 'Perlu revisi',
            3 => 'Menunggu persetujuan', 4 => 'Selesai', 5 => 'Dipecah',
            7 => 'Terlambat', 8 => 'Waktu habis', 9 => 'Menunggu persetujuan',
        ];

        return [
            'id' => $task->id,
            'name' => $task->nama_task,
            'project_id' => (int) $task->project,
            'division_id' => (int) $task->divisi,
            'kpi_id' => $task->id_kpi ? (int) $task->id_kpi : null,
            'pic_id' => (int) $task->pic,
            'project' => $task->project_name ?: 'Tanpa project',
            'pic' => $task->pic_email ?: 'Belum ditentukan',
            'priority' => (int) $task->priority,
            'minutes' => (int) $task->menit,
            'start_time' => $task->jam_mulai ? substr($task->jam_mulai, 0, 5) : null,
            'start_date' => $task->tanggal_mulai,
            'deadline' => $task->tanggal_deadline,
            'status' => (int) $task->status,
            'status_label' => $labels[(int) $task->status] ?? 'Tidak diketahui',
            'note' => $task->note,
            'kpi' => $task->kpi_name,
            'proof' => $task->completion_proof_path ? [
                'name' => $task->completion_proof_name ?: 'Bukti penyelesaian',
                'mime' => $task->completion_proof_mime,
                'size' => (int) ($task->completion_proof_size ?: 0),
                'url' => url('/api/hc/daily-work/tasks/'.$task->id.'/proof'),
            ] : null,
            'is_overdue' => $task->tanggal_deadline
                && Carbon::parse($task->tanggal_deadline)->lt($today)
                && !in_array((int) $task->status, [4, 5]),
        ];
    }

    public function storeTaskApi(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', 'project_id' => 'required|integer|exists:produk_client,id',
            'division_id' => 'required|integer|exists:divisions,id', 'pic_id' => 'required|integer|exists:users,id',
            'priority' => 'required|integer|between:1,3', 'minutes' => 'required|integer|min:1|max:1440',
            'start_date' => 'required|date', 'deadline' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i', 'kpi_id' => 'nullable|integer|exists:kpi,id',
            'note' => 'nullable|string',
        ]);

        $data = $this->validateTaskAssignment($data);
        $this->validateWorkingHours($data);
        $this->validateScheduleConflict($data);

        $task = Task::create($this->mapApiTask($data));
        return response()->json(['message' => 'Task berhasil ditambahkan.', 'id' => $task->id], 201);
    }

    public function updateTaskApi(Request $request, Task $task)
    {
        if ((int) $task->status !== 0) {
            return response()->json([
                'message' => 'Task yang sudah dimulai atau diproses tidak dapat diedit.',
            ], 422);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255', 'project_id' => 'required|integer|exists:produk_client,id',
            'division_id' => 'required|integer|exists:divisions,id', 'pic_id' => 'required|integer|exists:users,id',
            'priority' => 'required|integer|between:1,3', 'minutes' => 'required|integer|min:1|max:1440',
            'start_date' => 'required|date', 'deadline' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i', 'kpi_id' => 'nullable|integer|exists:kpi,id',
            'note' => 'nullable|string',
        ]);
        $data = $this->validateTaskAssignment($data);
        $this->validateWorkingHours($data);
        $this->validateScheduleConflict($data, $task->id);
        $task->update($this->mapApiTask($data, false));
        return response()->json(['message' => 'Task berhasil diperbarui.']);
    }

    public function deleteTaskApi(Task $task)
    {
        if ((int) $task->status !== 0) {
            return response()->json([
                'message' => 'Task yang sudah dimulai atau diproses tidak dapat dihapus.',
            ], 422);
        }

        $task->delete();
        return response()->json(['message' => 'Task berhasil dihapus.']);
    }

    private function mapApiTask(array $data, bool $includeStatus = true): array
    {
        $mapped = [
            'nama_task' => $data['name'], 'project' => $data['project_id'], 'divisi' => $data['division_id'],
            'pic' => $data['pic_id'], 'priority' => $data['priority'], 'menit' => $data['minutes'],
            'tanggal_mulai' => $data['start_date'], 'tanggal_deadline' => $data['deadline'],
            'jam_mulai' => $data['start_time'], 'id_kpi' => $data['kpi_id'] ?? null,
            'note' => $data['note'] ?? null,
        ];
        if ($includeStatus) $mapped['status'] = 0;
        return $mapped;
    }

    private function dailyWorkFormContext(User $user): array
    {
        $assignment = DB::table('user_division')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->where('user_division.user_id', $user->id)
            ->select('user_division.division_id', 'roles.level')
            ->orderBy('roles.level')
            ->first();

        if (! $assignment) {
            $people = $user->isAdmin
                ? User::unlocked()->select('id', 'email')->orderBy('email')->get()
                : User::whereKey($user->id)->select('id', 'email')->get();

            return [
                'division_id' => null,
                'division_locked' => false,
                'people' => $people->map(fn ($person) => ['id' => $person->id, 'email' => $person->email]),
            ];
        }

        $rows = DB::table('user_division')
            ->join('users', 'users.id', '=', 'user_division.user_id')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->where('user_division.division_id', $assignment->division_id)
            ->whereNull('users.deleted_at')
            ->select('users.id', 'users.email', 'user_division.parent_id', 'roles.level')
            ->orderBy('roles.level')
            ->orderBy('users.email')
            ->get();

        $allowedIds = collect([(int) $user->id]);
        $frontier = collect([(int) $user->id]);
        while ($frontier->isNotEmpty()) {
            $children = $rows
                ->whereIn('parent_id', $frontier)
                ->where('level', '>', (int) $assignment->level)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->diff($allowedIds)
                ->values();
            if ($children->isEmpty()) break;
            $allowedIds = $allowedIds->merge($children)->unique()->values();
            $frontier = $children;
        }

        return [
            'division_id' => (int) $assignment->division_id,
            'division_locked' => true,
            'people' => $rows->whereIn('id', $allowedIds)->unique('id')->values()->map(fn ($person) => [
                'id' => (int) $person->id,
                'email' => $person->email,
                'level' => (int) $person->level,
            ]),
        ];
    }

    private function validateTaskAssignment(array $data): array
    {
        $context = $this->dailyWorkFormContext(Auth::user());
        if ($context['division_locked']) {
            $data['division_id'] = $context['division_id'];
        }

        abort_unless(
            $context['people']->pluck('id')->map(fn ($id) => (int) $id)->contains((int) $data['pic_id']),
            403,
            'PIC hanya dapat dipilih dari diri sendiri atau bawahan Anda.'
        );

        $project = ProdukClient::query()->findOrFail($data['project_id']);
        $projectMatchesDivision = $project->is_internal || DB::table('client_division')
            ->where('client_id', $data['project_id'])
            ->where('division_id', $data['division_id'])
            ->exists();
        if (! $projectMatchesDivision) {
            throw ValidationException::withMessages([
                'project_id' => 'Project tidak terdaftar pada divisi yang dipilih.',
            ]);
        }

        if (! empty($data['kpi_id'])) {
            $kpiMatches = KPI::whereKey($data['kpi_id'])
                ->where('divisi', $data['division_id'])
                ->where('project', $data['project_id'])
                ->exists();
            if (! $kpiMatches) {
                throw ValidationException::withMessages([
                    'kpi_id' => 'KPI tidak sesuai dengan divisi dan project yang dipilih.',
                ]);
            }
        }

        return $data;
    }

    private function validateWorkingHours(array $data): void
    {
        $divisionName = Division::whereKey($data['division_id'])->value('name');
        $startHour = strcasecmp((string) $divisionName, 'Tech') === 0 ? 8 : 9;
        [$hour, $minute] = array_map('intval', explode(':', $data['start_time']));
        $startMinutes = ($hour * 60) + $minute;
        $endMinutes = $startMinutes + (int) $data['minutes'];

        if ($startMinutes < ($startHour * 60) || $startMinutes >= (17 * 60) || $endMinutes > (17 * 60)) {
            $schedule = sprintf('%02d.00–17.00 WIB', $startHour);
            throw ValidationException::withMessages([
                'start_time' => "Jam kerja divisi {$divisionName} adalah {$schedule}. Task harus selesai paling lambat pukul 17.00 WIB.",
            ]);
        }
    }

    private function validateScheduleConflict(array $data, ?int $ignoreTaskId = null): void
    {
        $newStart = Carbon::createFromFormat('Y-m-d H:i', $data['start_date'].' '.$data['start_time']);
        $newEnd = $newStart->copy()->addMinutes((int) $data['minutes']);

        $conflict = Task::query()
            ->where('pic', $data['pic_id'])
            ->whereDate('tanggal_mulai', $data['start_date'])
            ->whereNotNull('jam_mulai')
            ->when($ignoreTaskId, fn ($query) => $query->where('id', '!=', $ignoreTaskId))
            ->get(['id', 'nama_task', 'jam_mulai', 'menit'])
            ->first(function (Task $task) use ($data, $newStart, $newEnd): bool {
                $existingStart = Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $data['start_date'].' '.Carbon::parse($task->jam_mulai)->format('H:i:s')
                );
                $existingEnd = $existingStart->copy()->addMinutes((int) $task->menit);

                return $newStart->lt($existingEnd) && $newEnd->gt($existingStart);
            });

        if ($conflict) {
            $conflictStart = Carbon::parse($conflict->jam_mulai);
            $conflictEnd = $conflictStart->copy()->addMinutes((int) $conflict->menit);
            throw ValidationException::withMessages([
                'start_time' => sprintf(
                    'Jadwal bentrok dengan task "%s" pada pukul %s–%s WIB.',
                    $conflict->nama_task,
                    $conflictStart->format('H:i'),
                    $conflictEnd->format('H:i'),
                ),
            ]);
        }
    }

    public function schedulePreview(Request $request)
    {
        $validated = $request->validate([
            'pic_id' => ['required', 'integer', 'exists:users,id'],
            'date' => ['required', 'date'],
        ]);
        $context = $this->dailyWorkFormContext($request->user());
        abort_unless(
            $context['people']->pluck('id')->map(fn ($id) => (int) $id)->contains((int) $validated['pic_id']),
            403,
            'Anda tidak berhak melihat jadwal pengguna ini.'
        );

        $tasks = Task::query()
            ->leftJoin('produk_client', 'produk_client.id', '=', 'task.project')
            ->leftJoin('kpi', 'kpi.id', '=', 'task.id_kpi')
            ->where('task.pic', $validated['pic_id'])
            ->whereDate('task.tanggal_mulai', $validated['date'])
            ->whereNotNull('task.jam_mulai')
            ->orderBy('task.jam_mulai')
            ->get([
                'task.id', 'task.nama_task', 'task.jam_mulai', 'task.menit', 'task.status',
                'produk_client.nama as project_name', 'kpi.nama_kpi as kpi_name',
            ])
            ->map(function ($task): array {
                $start = Carbon::parse($task->jam_mulai);
                return [
                    'id' => $task->id,
                    'name' => $task->nama_task,
                    'start' => $start->format('H:i'),
                    'end' => $start->copy()->addMinutes((int) $task->menit)->format('H:i'),
                    'minutes' => (int) $task->menit,
                    'status' => (int) $task->status,
                    'project' => $task->project_name ?: 'Tanpa project',
                    'kpi' => $task->kpi_name,
                ];
            });

        return response()->json(['tasks' => $tasks]);
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
            $divisionName = Division::whereKey($task->divisi)->value('name');
            $startHour = strcasecmp((string) $divisionName, 'Tech') === 0 ? 8 : 9;
            $top = max(0, (int) $request->top);
            $newTime = Carbon::createFromTime($startHour, 0, 0)->addMinutes($top);
            $endTime = $newTime->copy()->addMinutes((int) $task->menit);

            if ($newTime->hour >= 17 || $endTime->gt(Carbon::createFromTime(17, 0, 0))) {
                return response()->json([
                    'message' => 'Task tidak dapat dijadwalkan karena akan selesai melewati pukul 17.00 WIB.',
                ], 422);
            }

            $this->validateScheduleConflict([
                'pic_id' => $task->pic,
                'start_date' => Carbon::today('Asia/Jakarta')->toDateString(),
                'start_time' => $newTime->format('H:i'),
                'minutes' => (int) $task->menit,
            ], $task->id);

            $scheduleDate = Carbon::today('Asia/Jakarta');
            $wasRevision = (int) $task->status === 2;
            $task->update([
                'jam_mulai' => $newTime->format('H:i:s'),
                'tanggal_mulai' => $scheduleDate,
                // Deadline revisi hanya dibuat lagi ketika task dijadwalkan.
                'tanggal_deadline' => $wasRevision ? $scheduleDate : $task->tanggal_deadline,
                'status' => $wasRevision ? 0 : $task->status,
            ]);
        }
        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],
            'action' => ['required', 'in:play,stop,revisi,approve,set_overdue,habis'],
            'proof' => [
                $request->input('action') === 'stop' ? 'required' : 'nullable',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip',
            ],
        ], [
            'proof.required' => 'Bukti penyelesaian wajib diunggah sebelum task dikirim untuk approval.',
            'proof.max' => 'Ukuran bukti maksimal 10 MB.',
            'proof.mimes' => 'Format bukti harus berupa gambar, PDF, dokumen Office, atau ZIP.',
        ]);

        $task = Task::find($request->id);
        if (!$task) return response()->json(['success' => false], 404);

        $action = $request->action;
        if (in_array($action, ['play', 'stop'], true)) {
            abort_unless((int) $task->pic === (int) Auth::id() || Auth::user()->isAdmin, 403, 'Anda tidak berhak mengubah task ini.');
        }

        if (in_array($action, ['approve', 'revisi'], true)) {
            abort_if(
                (int) $task->pic === (int) Auth::id(),
                403,
                'Anda tidak dapat menyetujui atau merevisi pekerjaan milik sendiri.'
            );

            $taskOwnerLevel = DB::table('user_division')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->where('user_division.user_id', $task->pic)
                ->where('user_division.division_id', $task->divisi)
                ->value('roles.level');

            if ((int) $taskOwnerLevel === 1) {
                abort_unless(Auth::user()->isAdmin, 403, 'Task PIC level 1 hanya dapat disetujui oleh admin.');
            } elseif (! Auth::user()->isAdmin) {
                $isDirectSubordinate = DB::table('user_division')
                    ->where('user_id', $task->pic)
                    ->where('division_id', $task->divisi)
                    ->where('parent_id', Auth::id())
                    ->exists();

                abort_unless($isDirectSubordinate, 403, 'Task ini bukan milik bawahan langsung Anda.');
            }
        }

        if ($request->action == 'play') {
            $updateData = ['status' => 1];
            if ($task->jam_mulai) {
                $updateData['jam_mulai'] = $task->jam_mulai;
            } else {
                $updateData['jam_mulai'] = Carbon::now('Asia/Jakarta')->format('H:i');
            }

            $task->update($updateData);
        } elseif ($action == 'stop') {
            $proof = $request->file('proof');
            $oldProofPath = $task->completion_proof_path;
            $storedPath = $proof->store('daily-work-proofs/'.$task->id, 'local');
            $task->forceFill([
                'completion_proof_path' => $storedPath,
                'completion_proof_name' => $proof->getClientOriginalName(),
                'completion_proof_mime' => $proof->getMimeType(),
                'completion_proof_size' => $proof->getSize(),
                'completion_proof_uploaded_at' => now(),
            ]);
            if($task->status == 7){
                $task->status = 9;
            }else{
                $task->status = 3; // Menunggu Approval
            }
            $task->save();
            if ($oldProofPath && $oldProofPath !== $storedPath) {
                Storage::disk('local')->delete($oldProofPath);
            }
        } elseif ($action == 'revisi') {
            $revisionAt = Carbon::now('Asia/Jakarta');
            $task->update([
                'status' => 2,
                'tanggal_revisi' => $revisionAt,
                'tanggal_mulai' => $revisionAt->toDateString(),
                'tanggal_deadline' => null,
                'jam_mulai' => null,
                'tanggal_approve' => null,
            ]); // Kembali ke pool sebagai revisi, bukan overdue.
        } elseif ($action == 'approve') {
            if (! $task->completion_proof_path || ! Storage::disk('local')->exists($task->completion_proof_path)) {
                return response()->json(['message' => 'Task belum memiliki bukti penyelesaian.'], 422);
            }
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

    public function viewCompletionProof(Task $task)
    {
        $user = Auth::user();
        $isDirectSupervisor = DB::table('user_division')
            ->where('user_id', $task->pic)
            ->where('parent_id', $user->id)
            ->exists();

        abort_unless(
            (int) $task->pic === (int) $user->id || $user->isAdmin || $isDirectSupervisor,
            403,
            'Anda tidak berhak melihat bukti task ini.'
        );
        abort_unless(
            $task->completion_proof_path && Storage::disk('local')->exists($task->completion_proof_path),
            404,
            'File bukti tidak ditemukan.'
        );

        return Storage::disk('local')->response(
            $task->completion_proof_path,
            $task->completion_proof_name ?: basename($task->completion_proof_path),
            ['Content-Type' => $task->completion_proof_mime ?: 'application/octet-stream']
        );
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
            ->leftJoin('kpi', 'kpi.id', '=', 'task.id_kpi')
            ->select('task.*', 'users.email as vic_email', 'produk_client.nama as project_name', 'roles.role_name as vic_role', 'user_division.parent_id', 'roles.level', DB::raw("COALESCE(kpi.nama_kpi, '-') as nama_kpi"), 'kpi.note as catatan_kpi')
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
    }

    public function storeDaily(Request $request){
        $data = Task::where('pic', (int)$request->pic)->get();

        $user = User::find($request->pic)->userDivisions[0];

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
        if($user->division_id == 5){
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
                'id_kpi'           => !empty($request->kpi_id) ? (int) $request->kpi_id : null,
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
        $kpi = $handleClient->kpis()->select('kpi.id', 'kpi.nama_kpi')->get();
        
        $client = [];
        if ($handleClient) {
            $client = $handleClient->clients()->select('produk_client.id', 'nama')->get();
        }

        return response()->json([
            'task'   => $task,
            'client' => $client,
            'kpi'    => $kpi
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
