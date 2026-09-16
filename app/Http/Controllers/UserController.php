<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Role;
use App\Models\userDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
                });
        })
        ->orderBy('name', 'ASC')
        ->get();

        $divisions = Division::all();
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
                    });
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
                'user_divisions' => $user->isAdmin && filled($user->jabatan)
                    ? [[
                        'division_id' => 'lmg',
                        'division' => ['name' => 'Lighter Media Group'],
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
        $isLmg = $request->input('divisi_id') === 'lmg';
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'divisi_id' => ['required'],
            'jabatan' => [$isLmg ? 'required' : 'nullable', 'in:ceo,coo,cmo,cfo'],
            'role_id' => [$isLmg ? 'nullable' : 'required', 'nullable', 'integer'],
        ]);
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
                    'parent_id' => $request->filled('parent_id') ? $request->parent_id : null
                ]);
            }

            if (!$isLmg && $request->has('subordinate_ids') && is_array($request->subordinate_ids)) {
                foreach ($request->subordinate_ids as $subordinateId) {
                    $subordinate = User::find($subordinateId);
                    if ($subordinate) {
                        $user_division = userDivision::where('user_id', $subordinateId)->update([
                            'parent_id' => $newUser->id
                        ]);
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
        $isLmg = $request->input('divisi_id') === 'lmg';
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'divisi_id' => ['required'],
            'jabatan' => [$isLmg ? 'required' : 'nullable', 'in:ceo,coo,cmo,cfo'],
            'role_id' => [$isLmg ? 'nullable' : 'required', 'nullable', 'integer'],
        ]);
        $level = Role::find((int)$request->role_id);

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

            // 3. Update User Division (Atasan)
            UserDivision::where('parent_id', $id)->update(['parent_id' => null]);
            UserDivision::where('user_id', $id)->delete();

            if (!$isLmg) {
                UserDivision::create([
                    'user_id' => $id,
                    'division_id' => (int)$request->divisi_id,
                    'role_id' => (int)$request->role_id,
                    'parent_id' => ($level && $level->level == 1) ? null : ($request->parent_id ?: null),
                ]);
            }

            // 4. Update Hierarki Bawahan
            // Langkah A: Lepas semua bawahan lama yang sebelumnya melapor ke user ini
            // Langkah B: Set bawahan baru berdasarkan pilihan di modal
            if (!$isLmg && $request->has('subordinate_ids') && is_array($request->subordinate_ids)) {
                UserDivision::whereIn('user_id', $request->subordinate_ids)
                    ->where('division_id', (int)$request->divisi_id)
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

    public function toggleLock(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'Akun yang sedang digunakan tidak dapat dikunci.');
        }

        $user->update(['is_locked' => !$user->is_locked]);

        return redirect()->back()->with(
            'success',
            $user->is_locked ? 'Akun berhasil dikunci.' : 'Kunci akun berhasil dibuka.'
        );
    }

    public function getUserDivision(Request $request){
        $divisiId = $request->input('divisi_id');
        if($divisiId != 'all'){
            $allUser = User::whereHas('userDivisions', function($user) use ($divisiId) {
                $user->where('division_id', $divisiId);
            })->get();
        }else{
            $allUser = User::where('isAdmin', false)->get();
        }

        return response()->json($allUser);
    }
}
