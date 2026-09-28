<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Role;
use App\Models\userDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(){
        $title = "Manajemen User";
        $currentUser = Auth::user();

        // Ambil ID divisi milik user yang sedang login
        // $myDivisionIds = $currentUser->userDivisions->pluck('division_id')->toArray();

        $data = User::with([
            'userDivisions.division', 
            'userDivisions.role',    
            'userDivisions.parent'
        ])
        ->where(function ($query) {
            $query->where('isAdmin', false)
                ->orWhere(function ($adminQuery) {
                    $adminQuery->where('isAdmin', true)
                        ->whereNotNull('jabatan')
                        ->where('jabatan', '!=', '');
                })
                ->orWhereHas('userDivisions');
        })
        ->orderBy('name', 'ASC')
        ->get();

        // IDME merupakan workspace khusus admin, bukan divisi penempatan user.
        $divisions = Division::query()
            ->whereRaw("LOWER(TRIM(name)) <> 'idme'")
            ->orderBy('name')
            ->get();
        $roles = Role::all();

        return view('users.index', compact('title', 'data', 'divisions', 'roles'));
    }

    public function getUsersJson() 
    {
        $users = User::with(['userDivisions.division', 'userDivisions.role', 'userDivisions.parent', 'userClients.client'])
            ->where(function ($query) {
                $query->where('isAdmin', false)
                ->orWhere(function ($adminQuery) {
                    $adminQuery->where('isAdmin', true)
                        ->whereNotNull('jabatan')
                        ->where('jabatan', '!=', '');
                    })
                    ->orWhereHas('userDivisions');
            })
            ->orderBy('name')
            ->get();

        $result = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool) $user->isAdmin,
                'jabatan' => $user->jabatan,
                'photo_profile_url' => $user->photo_profile ? asset('storage/' . $user->photo_profile) : null,
                'is_locked' => (bool) $user->is_locked,
                'is_current_user' => Auth::id() === $user->id,
                // Akun pimpinan selalu dikelompokkan pada IDME di manajemen user.
                // Penempatan operasionalnya (jika pernah ada) tidak mengubah tampilan ini.
                'user_divisions' => $user->isAdmin && filled($user->jabatan)
                    ? [[
                        'division_id' => 'idme',
                        'division' => ['name' => 'IDME'],
                        'role_id' => null,
                        'role' => strtoupper($user->jabatan),
                        'level' => $this->lmgPositionLevel($user->jabatan),
                        'parent_id' => null,
                        'parent' => null,
                    ]]
                    : $user->userDivisions->map(function($ud) {
                    $role = $this->getRole(User::find($ud->user_id), $ud->division_id);
                    return [
                        'division_id' => $ud->division_id,
                        'division' => ['name' => $ud->division->name],
                        'role_id' => $role->id,
                        'role' => $role->role_name,
                        'level' => $role->level,
                        'parent_id' => $ud->parent_id,
                        'parent' => $ud->parent ? [
                            'name' => $ud->parent->name,
                            'email' => $ud->parent->email,
                            // Kita ambil nama role atasan dari relasi role si parent
                            'role_name' => $role->role_name,
                            'role_level' => $role->level
                        ] : null
                    ];
                })->values(),
                'user_clients' => $user->userClients->map(function($uc) {
                    $client = $uc->client;
                    $name = $client?->nama ?? 'Nama tidak ditemukan';
                    return ['client' => ['nama' => $name]];
                })
            ];
        });

        return response()->json($result);
    }

    /**
     * Daftar pengguna yang boleh dipilih oleh akun yang sedang masuk.
     * Digunakan oleh filter dan modal agar pilihan pengguna selalu sesuai divisi.
     */
    public function selectableUsers(Request $request)
    {
        $currentUser = $request->user();
        $divisionId = $request->integer('division_id') ?: null;

        $query = User::query()->unlocked()->orderBy('email');

        if (! $currentUser->isAdmin) {
            $query->whereIn('id', $this->visibleUserIds($currentUser->id));
        }

        if ($divisionId) {
            $query->whereHas('userDivisions', function ($userDivisions) use ($divisionId) {
                $userDivisions->where('division_id', $divisionId);
            });
        }

        return response()->json(
            $query->get(['id', 'name', 'email'])->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->values(),
        );
    }

    private function visibleUserIds(int $userId): array
    {
        $visibleIds = [$userId];
        $parents = [$userId];

        while ($parents !== []) {
            $children = userDivision::query()
                ->whereIn('parent_id', $parents)
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->reject(fn (int $id) => in_array($id, $visibleIds, true))
                ->values()
                ->all();

            if ($children === []) {
                break;
            }

            $visibleIds = array_values(array_unique([...$visibleIds, ...$children]));
            $parents = $children;
        }

        return $visibleIds;
    }

    private function lmgPositionLevel(?string $position): int
    {
        return match (strtolower((string) $position)) {
            'ceo' => 1,
            'coo', 'cmo', 'cfo' => 2,
            default => 3,
        };
    }

    private function getRole($user, $divisionId)
    {
        $divInfo = $user->userDivisions->where('division_id', $divisionId)->first();
    
        if (!$divInfo || !$divInfo->role) {
            return (object) ['id' => null, 'role_name' => '-', 'level' => 0];
        }
        
        $roleName = DB::table('roles')->where('id', $divInfo->role_id)->first();
        return $roleName;
    }

    public function store(Request $request){
        $isLmg = in_array((string) $request->input('divisi_id'), ['lmg', 'idme'], true);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'divisi_id' => ['required'],
            'jabatan' => [$isLmg ? 'required' : 'nullable', 'in:ceo,coo,cmo,cfo'],
            'role_id' => [$isLmg ? 'nullable' : 'required', 'nullable', 'integer'],
        ]);
        if (!$isLmg && Division::whereKey($request->divisi_id)->whereRaw("LOWER(TRIM(name)) = 'idme'")->exists()) {
            return redirect()->back()->withInput()->with('error', 'IDME bukan divisi penempatan user.');
        }
        $role = $isLmg ? null : Role::whereKey($request->role_id)
            ->where('division_id', $request->divisi_id)
            ->firstOrFail();
        $parentId = $this->validatedParentId($request, $role);
        $exists = User::where('email', $request->email)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Email Sudah Terdaftar Di Sistem');
        }

        DB::beginTransaction();

        try {
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'isAdmin' => $isLmg,
                'jabatan' => $isLmg ? strtolower($request->jabatan) : null,
            ]);

            // Tambah Ke Table User Division
            if (!$isLmg) {
                UserDivision::insert([
                    'user_id' => $newUser->id,
                    'division_id' => (int)$request->divisi_id,
                    'role_id' => (int)$request->role_id,
                    'parent_id' => $parentId,
                ]);
            }

            if (!$isLmg && ! $this->isFinanceDivision((int) $request->divisi_id) && $request->has('subordinate_ids') && is_array($request->subordinate_ids)) {
                foreach ($request->subordinate_ids as $subordinateId) {
                    $subordinate = User::unlocked()->find($subordinateId);
                    if ($subordinate) {
                        userDivision::where('user_id', $subordinateId)
                            ->where('division_id', (int) $request->divisi_id)
                            ->whereHas('role', fn ($query) => $query->where('level', '>', $role->level))
                            ->update(['parent_id' => $newUser->id]);
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Berhasil Membuat Akun');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi Kesalahan: ' . $th->getMessage());
        }

    }

    public function update($id, Request $request){
        $user = User::findOrFail($id);
        $isLmg = in_array((string) $request->input('divisi_id'), ['lmg', 'idme'], true);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'divisi_id' => ['required'],
            'jabatan' => [$isLmg ? 'required' : 'nullable', 'in:ceo,coo,cmo,cfo'],
            'role_id' => [$isLmg ? 'nullable' : 'required', 'nullable', 'integer'],
        ]);
        if (!$isLmg && Division::whereKey($request->divisi_id)->whereRaw("LOWER(TRIM(name)) = 'idme'")->exists()) {
            return redirect()->back()->withInput()->with('error', 'IDME bukan divisi penempatan user.');
        }
        $level = $isLmg ? null : Role::whereKey($request->role_id)
            ->where('division_id', $request->divisi_id)
            ->firstOrFail();
        $parentId = $this->validatedParentId($request, $level);

        // 1. Cek duplikasi email (kecuali email milik user ini sendiri)
        $exists = User::where('email', $request->email)->where('id', '!=', $id)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Email Sudah Terdaftar Di Sistem');
        }

        DB::beginTransaction();

        try {
            // 2. Update Data User
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
                'isAdmin' => $isLmg,
                'jabatan' => $isLmg ? strtolower($request->jabatan) : null,
            ];
            
            // Hanya update password jika diisi
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            $user->update($userData);

            // 3. Update User Division (Atasan). Mengubah data pimpinan di IDME
            // tidak boleh menghapus penempatan Finance atau relasi bawahannya.
            if (! $isLmg) {
                UserDivision::where('parent_id', $id)->update(['parent_id' => null]);
                UserDivision::where('user_id', $id)->delete();

                UserDivision::create([
                    'user_id' => $id,
                    'division_id' => (int)$request->divisi_id,
                    'role_id' => (int)$request->role_id,
                    'parent_id' => $parentId,
                ]);
            }

            // 4. Update Hierarki Bawahan
            // Langkah A: Lepas semua bawahan lama yang sebelumnya melapor ke user ini
            // Langkah B: Set bawahan baru berdasarkan pilihan di modal
            if (!$isLmg && ! $this->isFinanceDivision((int) $request->divisi_id) && $request->has('subordinate_ids') && is_array($request->subordinate_ids)) {
                $unlockedSubordinateIds = User::unlocked()
                    ->whereIn('id', $request->subordinate_ids)
                    ->pluck('id');
                UserDivision::whereIn('user_id', $unlockedSubordinateIds)
                    ->where('division_id', (int)$request->divisi_id)
                    ->whereHas('role', fn ($query) => $query->where('level', '>', $level->level))
                    ->update(['parent_id' => $id]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Berhasil mengubah data user');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi Kesalahan: ' . $th->getMessage());
        }
    }

    public function destroy($id){
        try {
            $user = User::findOrFail($id);

            if (Auth::id() === $user->id) {
                return redirect()->back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
            }

            $user->delete();

            return redirect()->back()->with('success', 'Akun disembunyikan. Data akun tetap tersimpan di database.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal menghapus user: ' . $th->getMessage());
        }
    }

    private function validatedParentId(Request $request, ?Role $role): ?int
    {
        if ($role === null) {
            return null;
        }

        // Struktur Finance tidak mengikuti level role biasa: seluruh pengguna
        // Finance, termasuk Business Unit Head, melapor langsung kepada CFO.
        if ($this->isFinanceDivision($request->integer('divisi_id'))) {
            $request->validate([
                'parent_id' => ['required', 'integer', 'exists:users,id'],
            ], [
                'parent_id.required' => 'Atasan Finance wajib dipilih dari akun CFO.',
            ]);

            $isCfo = User::query()
                ->unlocked()
                ->whereKey($request->integer('parent_id'))
                ->where('isAdmin', true)
                ->whereRaw("LOWER(TRIM(COALESCE(jabatan, ''))) = 'cfo'")
                ->exists();

            if (! $isCfo) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Seluruh pengguna Finance harus memiliki atasan dengan jabatan CFO.',
                ]);
            }

            return $request->integer('parent_id');
        }

        if (empty($role->parent_id)) {
            return null;
        }

        $request->validate([
            'parent_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            'parent_id.required' => 'Atasan wajib dipilih untuk role ini.',
        ]);

        if (! User::unlocked()->whereKey($request->integer('parent_id'))->exists()) {
            throw ValidationException::withMessages([
                'parent_id' => 'Akun atasan sedang terkunci dan tidak dapat dipilih.',
            ]);
        }

        $isValidParent = userDivision::query()
            ->where('user_id', $request->integer('parent_id'))
            ->where('division_id', $request->integer('divisi_id'))
            ->whereHas('role', fn ($query) => $query->where('level', '<', $role->level))
            ->exists();

        if (! $isValidParent) {
            throw ValidationException::withMessages([
                'parent_id' => 'Atasan harus berasal dari divisi yang sama dan memiliki level yang lebih tinggi.',
            ]);
        }

        return $request->integer('parent_id');
    }

    private function isFinanceDivision(?int $divisionId): bool
    {
        return $divisionId && Division::query()
            ->whereKey($divisionId)
            ->whereRaw("LOWER(TRIM(name)) = 'finance'")
            ->exists();
    }

    public function toggleLock(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'Akun yang sedang digunakan tidak dapat dikunci.');
        }

        $willBeLocked = ! (bool) $user->is_locked;
        $user->forceFill([
            'is_locked' => $willBeLocked,
            'remember_token' => $willBeLocked ? \Illuminate\Support\Str::random(60) : $user->getRememberToken(),
        ])->save();
        $user->refresh();

        return redirect()->back()->with(
            'success',
            $user->is_locked ? 'Akun berhasil dikunci.' : 'Kunci akun berhasil dibuka.'
        );
    }

    public function getUserDivision(Request $request){
        $divisiId = $request->input('divisi_id');
        if($divisiId != 'all'){
            $allUser = User::unlocked()->whereHas('userDivisions', function($user) use ($divisiId) {
                $user->where('division_id', $divisiId);
            })->get();
        }else{
            $allUser = User::unlocked()->where('isAdmin', false)->get();
        }

        return response()->json($allUser);
    }
}
