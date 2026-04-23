<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Division;
use App\Models\Role;
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
                        'role' => ['name' => $ud->role->name],
                        'parent' => $ud->parent ? [
                            'name' => $ud->parent->name,
                            'email' => $ud->parent->email,
                            // Kita ambil nama role atasan dari relasi role si parent
                            'role_name' => $ud->parent->userDivisions->where('division_id', $ud->division_id)->first()->role->name ?? '-'
                        ] : null
                    ];
                }),
                'user_clients' => $user->userClients->map(function($uc) {
                    return ['client' => ['nama' => $uc->client->nama]];
                })
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request){
        $dmm_id = $request->dmm;
        $dm_id  = $request->dm;
        switch ($request->role) {
            case 'head_of_division':
                $hod_id = null;
                $dmm_id = null;
                $dm_id = null;
                break;
            case 'digital_marketing_manager':
                $hod_id = Auth::user()->role == "head_of_division" ? Auth::user()->id : (int)$request->hod_id;
                $dmm_id = null;
                $dm_id = null;
                break;
            case 'digital_marketing':
                $hod_id = User::find($request->dmm_id)->hod ?? null;
                $dmm_id = $request->dmm_id ?? null;
                $dm_id = null;
                break;
            case 'content_creator':
                $hod_id = User::find($request->dm_id)->hod ?? null;
                $dmm_id = User::find($request->dm_id)->dmm ?? null;
                $dm_id =  (int)$request->dm_id ?? null;
                break;
            default:
                break;
        }

        $divisi = Auth::user()->role == "super_admin" ? array_map('intval', (array)$request->divisi) : [(int)Auth::user()->divisi[0]];
        
        // dd(array_map('intval', (array)$request->divisi)[0]);
        // 3. Simpan ke Database
        $newUser = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => $request->role,
            'divisi'   => $divisi,
            'hod'      => $hod_id,
            'dmm'      => $dmm_id,
            'dm'       => $dm_id,
            'cc'       => null,
            'client'   => [],
        ]);

        if ($request->has('bawahan_ids') && is_array($request->bawahan_ids)) {
            foreach ($request->bawahan_ids as $subordinateId) {
                $subordinate = User::find($subordinateId);
                if ($subordinate) {
                    // Jika user baru adalah DMM, maka dia jadi 'dmm' bagi bawahannya
                    if ($request->role === 'digital_marketing_manager') {
                        $subordinate->update(['dmm' => $newUser->id]);
                    }
                    // Jika user baru adalah DM, maka dia jadi 'dm' bagi bawahannya
                    elseif ($request->role === 'digital_marketing') {
                        $subordinate->update(['dm' => $newUser->id]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Berhasil Membuat Akun');
    }

    public function update($id, Request $request){
        $dmm_id = $request->dmm;
        $dm_id = $request->dm;
        switch ($request->role) {
            case 'head_of_division':
                $hod_id = null;
                $dmm_id = null;
                $dm_id = null;
                break;
            case 'digital_marketing_manager':
                $hod_id = Auth::user()->role == "head_of_division" ? Auth::user()->id : (int)$request->hod_id;
                $dmm_id = null;
                $dm_id = null;
                break;
            case 'digital_marketing':
                $hod_id = User::find($request->dmm_id)->hod ?? null;
                $dmm_id = (int)$request->dmm ?? null;
                $dm_id = null;
                break;
            case 'content_creator':
                $hod_id = User::find($request->dm_id)->hod ?? null;
                $dmm_id = User::find($request->dm)->dmm ?? null;
                $dm_id =  (int)$request->dm ?? null;
                break;
            default:
                break;
        }
        
        $divisi = Auth::user()->role == "super_admin" ? array_map('intval', (array)$request->divisi) : [(int)Auth::user()->divisi[0]];
        
        // 3. Simpan ke Database
        User::where('id', $id)->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password ? bcrypt($request->password) : User::where('id', $id)->first()->password,
            'role'     => $request->role,
            'divisi'   => $divisi,
            'hod'      => $hod_id,
            'dmm'      => $dmm_id,
            'dm'       => $dm_id,
            'cc'       => null,
            'client'   => [],
        ]);

        if ($request->has('bawahan_ids') && is_array($request->bawahan_ids)) {
            foreach ($request->bawahan_ids as $subordinateId) {
                $subordinate = User::find($subordinateId);
                if ($subordinate) {
                    if ($request->role === 'digital_marketing_manager') {
                        $cc = User::where('cc', $subordinate->cc)->first();
                        $ccData = User::find($cc->id);
                        User::where('id', $subordinate->dmm)->update(['dm' => null]);
                        
                        $subordinate->update(['dmm' => $id]);
                        
                        $subordinate->update(['hod' => $hod_id]);

                        $ccData->update(['dmm' => $id]);

                        $ccData->update(['hod' => $hod_id]);

                    }elseif ($request->role === 'digital_marketing') {
                        User::where('id', $subordinate->dm)->update(['cc' => null]);
                        $subordinate->update(['dm' => $id]);
                        $subordinate->update(['hod' => $hod_id]);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'berhasil ubah data user');
    }

    public function destroy($id){
        $id = (int)$id;
        User::destroy($id);
        return redirect()->back()->with('success', 'User Berhasil di Hapus');
    }
}
