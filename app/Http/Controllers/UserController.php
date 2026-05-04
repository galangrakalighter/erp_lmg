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
        ->where('isAdmin', 'f') // Tetap sembunyikan Super Admin utama jika perlu
        ->orderBy('name', 'ASC')
        ->get();

        $divisions = Division::all();
        $roles = Role::all();

        return view('users.index', compact('title', 'data', 'divisions', 'roles'));
    }

    public function getUsersJson() 
    {
        $users = User::with(['userDivisions.division', 'userDivisions.role', 'userDivisions.parent', 'userClients.client'])
            ->where('isAdmin', 'f')
            ->get();

        $result = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_divisions' => $user->userDivisions->map(function($ud) {
                    return [
                        'division_id' => $ud->division_id,
                        'division' => ['name' => $ud->division->name],
                        'role_id' => $this->getRole(User::find($ud->user_id), $ud->division_id)->id,
                        'role' => $this->getRole(User::find($ud->user_id), $ud->division_id)->role_name,
                        'level' => $this->getRole(User::find($ud->user_id), $ud->division_id)->level,
                        'parent_id' => $ud->parent_id,
                        'parent' => $ud->parent ? [
                            'name' => $ud->parent->name,
                            'email' => $ud->parent->email,
                            // Kita ambil nama role atasan dari relasi role si parent
                            'role_name' => $this->getRole($ud->parent, $ud->division_id)->role_name,
                            'role_level' => $this->getRole($ud->parent, $ud->division_id)->level
                        ] : null
                    ];
                }),
                'user_clients' => $user->userClients->map(function($uc) {
                    $client = $uc->client;
                    $name = $client?->nama ?? 'Nama tidak ditemukan';
                    return ['client' => ['nama' => $name]];
                })
            ];
        });

        return response()->json($result);
    }

    private function getRole($user, $divisionId)
    {
        $divInfo = $user->userDivisions->where('division_id', $divisionId)->first();
        $roleName = DB::table('roles')->where('id', $divInfo->role_id)->first();
        return $roleName;
    }

    public function store(Request $request){
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
                'isAdmin' => 'f'
            ]);

            // Tambah Ke Table User Division
            UserDivision::insert([
                'user_id' => $newUser->id,
                'division_id' => (int)$request->divisi_id,
                'role_id' => (int)$request->role_id,
                'parent_id' => (int)$request->parent_id
            ]);

            if ($request->has('subordinate_ids') && is_array($request->subordinate_ids)) {
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
            ];
            
            // Hanya update password jika diisi
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            $user->update($userData);

            // 3. Update User Division (Atasan)
            UserDivision::where('user_id', $id)->update([
                'division_id' => (int)$request->divisi_id,
                'role_id'     => (int)$request->role_id,
                'parent_id'   => ($level && $level->level == 1) ? null : ($request->parent_id ?: null)
            ]);

            // 4. Update Hierarki Bawahan
            // Langkah A: Lepas semua bawahan lama yang sebelumnya melapor ke user ini
            UserDivision::where('parent_id', $id)
                ->where('division_id', (int)$request->divisi_id)
                ->update(['parent_id' => null]);

            // Langkah B: Set bawahan baru berdasarkan pilihan di modal
            if ($request->has('subordinate_ids') && is_array($request->subordinate_ids)) {
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
        DB::beginTransaction();

        try {
            // 1. Ambil data user
            $user = User::findOrFail($id);

            // 2. Bersihkan Hierarki: Jika user ini adalah atasan, 
            // lepaskan bawahannya (set parent_id jadi null)
            UserDivision::where('parent_id', $id)->update([
                'parent_id' => null
            ]);

            // 3. Hapus data di tabel relasi (UserDivision)
            UserDivision::where('user_id', $id)->delete();
            
            // 4. Hapus data di tabel User Utama
            $user->delete();

            DB::commit();
            return redirect()->back()->with('success', 'User dan hubungan hierarki berhasil dihapus');

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus user: ' . $th->getMessage());
        }
    }
}