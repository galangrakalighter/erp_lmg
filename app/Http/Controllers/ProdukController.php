<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use App\Models\KategoriJasa;
use App\Models\UserDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Arr;

class ProdukController extends Controller
{
    public function index(Request $request, $nama)
    {
        $divisiId = ($nama == "LGTR") ? 1 : 2;

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $RoleSekarang = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        if ($request->ajax()) {
            $data = ProdukClient::with(['teams.userDivisions.role'])
                ->join('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
                ->select('produk_client.*', 'kategori_jasa.category')
                ->when($divisiId == 1, function($q) { $q->where('produk_client.kategori_jasa_id', 1); })
                ->when($divisiId == 2, function($q) { $q->whereIn('produk_client.kategori_jasa_id', [2, 3]); })
                ->get()
                ->map(function($client) {
                    $teams = $client->teams;
                    
                    return [
                        'id'       => $client->id,
                        'nama'     => $client->nama,
                        'category' => $client->category,
                        'status'   => $client->status,
                        // Filter berdasarkan ID Role yang benar
                        'dmm'      => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 1)->pluck('email'),
                        'dm'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 3)->pluck('email'),
                        'scc'      => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 4)->pluck('email'),
                        'cc'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 5)->pluck('email'), 
                        'team_ids' => $teams->pluck('id')->toArray(),
                    ];
                });
            
            return response()->json(['data' => $data]);
        }

        // Ambil semua user yang masuk di divisi ini (Eager Load user_divisions)
        $usersInDivisi = User::with('userDivisions')->get();
        
        $kategori = KategoriJasa::where('id', $divisiId)->get();
        // dd($usersInDivisi->filter(fn($u) => $u->userDivisions->role_id == 1));
        // $divisions = $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 3)->flatMap->userDivisions;
        return view('cms.produk.index', [
            'title' => "Produk & Client",
            'dmm'   => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 1),
            'dm'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 3),
            'scc'   => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 4),
            'cc'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 5),
            'nama'  => $nama,
            'role'  => $RoleSekarang,
            'divisi' => $divisiId,
            'kategori' => $kategori
        ]);
    }


    public function getDetail($id)
    {
        try {
            $produk = ProdukClient::findOrFail($id);
            $currentTeamIds = $produk->teams()->pluck('users.id')->toArray();

            // Mengambil Role Sekarang sesuai logika Anda
            $user = Auth::user();
            $divisi = $user->userDivisions;
            
            // Cek apakah user punya divisi untuk menghindari error $divisi[0]
            $roleName = "Guest"; 
            if ($user->isAdmin) {
                $roleName = "Super Admin";
            } elseif ($divisi->isNotEmpty()) {
                $roleName = DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name');
            }

            // Tentukan hak akses (Bisa tambah role lain yang dianggap 'setara admin' di sini)
            $isUserAdmin = $user->isAdmin == true;

            $options = [
                'dmm' => User::whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Digital Marketing Manager'))->get(['id', 'name']),
                'dm'  => User::whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Digital Marketing'))->get(['id', 'name']),
                'scc' => User::whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Senior Content Creator'))->get(['id', 'name']),
                'cc'  => User::whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Content Creator'))->get(['id', 'name']),
            ];

            return response()->json([
                'status'        => 'success',
                'produk'        => $produk,
                'current_teams' => $currentTeamIds,
                'options'       => $options,
                'is_admin'      => $isUserAdmin,
                'my_role'       => $roleName // Tambahan untuk debugging jika perlu
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request){
        // dd($request->all());
        $accessTokenArray = [
            'platform' => $request->platform,
            'access'   => $request->access,
        ];

        try {
            DB::beginTransaction();

            // 1. Simpan Produk Baru
            $produk = ProdukClient::create([
                "kategori_jasa_id" => (int)$request->kategori_jasa_id,
                "nama"             => $request->client,
                "status"           => (int)$request->status,
                "access_token"     => $accessTokenArray 
            ]);

            // 2. Kumpulkan semu    a ID User dari input modal
            // Gunakan nama input sesuai yang ada di modal (dmm_ids, dm_ids, dll)
            $allUserIds = collect([
                $request->dmm_ids, 
                $request->dm_ids,
                $request->scc_ids,
                $request->cc_ids
            ])->flatten()->filter()->unique();

            // 3. Tambahkan Head of Division (HOD) secara otomatis berdasarkan divisi
            $currentUserDivision = Auth::user()->userDivisions->first();

            // Tentukan ID user yang akan dicari divisinya (prioritas user login, lalu dmm_ids)
            $targetUserId = ($currentUserDivision && $currentUserDivision->role_id != null) 
                            ? Auth::user()->id 
                            : ($request->dmm_ids[0] ?? null);

            $hod = null;
            if ($targetUserId) {
                $hod = UserDivision::join('roles', 'roles.id', '=', 'user_division.role_id')
                    ->where('user_id', $targetUserId)
                    ->first();
            }

            if ($hod) {
                // Pastikan yang di-push adalah ID USER, bukan ID dari tabel user_division
                $allUserIds->push($hod->user_id); 
            }
            
            if ($hod) {
                $allUserIds->push($hod->id);
            }

            // 4. Tambahkan Auth User jika bukan Super Admin (agar pembuat punya akses)
            if (Auth::user()->isAdmin == false) {
                $allUserIds->push(Auth::user()->id);
            }

            // 5. Simpan ke Tabel Pivot user_client menggunakan relasi
            // Ini akan otomatis mengisi tabel user_client dengan user_id dan client_id
            $uniqueUserIds = $allUserIds->unique()->toArray();
            $produk->teams()->sync($uniqueUserIds);

            DB::commit();
            return redirect()->back()->with('success', 'Produk dan Tim berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update($id, Request $request){
        $id = (int)$id;
        $kategori = (int)$request->kategori_jasa_id;
        $status = (int)$request->status;

        DB::transaction(function () use ($id, $kategori, $status, $request) {
            $array = [
                'platform' => $request->platform,
                'access' => $request->access,
            ];

            DB::table('produk_client')->where('id', $id)->update([
                "kategori_jasa_id" => $kategori,
                "nama"             => $request->client,
                "status"           => $status,
                "access_token"     => json_encode($array),
            ]);

            User::whereJsonContains('client', $id)
                ->get()
                ->each(function($user) use ($id) {
                    if($user->role != "super_admin"){
                        $currentClients = is_array($user->client) ? $user->client : json_decode($user->client, true) ?? [];
                        $cleaned = array_values(array_filter($currentClients, fn($val) => $val != $id));
                        $user->update(['client' => $cleaned]);
                    }
                });

            $allInvolvedIds = collect([$request->dmm])
                ->merge($request->dm)
                ->merge($request->cc)
                ->filter()
                ->unique()
                ->all();

            foreach($allInvolvedIds as $userId) {
                $user = User::find($userId);
                if($user) {
                    $currentClients = is_array($user->client) ? $user->client : json_decode($user->client, true) ?? [];
                    
                    if (!in_array($id, $currentClients)) {
                        $currentClients[] = $id;
                    }
                    
                    $user->update([
                        'client' => array_values(array_unique($currentClients))
                    ]);
                }
            }
            
            $user = User::whereJsonContains('divisi', (int)$request->divisi_edit)->where('role', 'head_of_division')->first();
            if($user) {
                $currentClients = is_array($user->client) ? $user->client : json_decode($user->client, true) ?? [];
                    
                if (!in_array($id, $currentClients)) {
                    $currentClients[] = $id;
                }
                
                $user->update([
                    'client' => array_values(array_unique($currentClients))
                ]);
            }
        });

        return redirect()->back();
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('produk_client')->where('id', $id)->delete($id);
        Task::where('project', $id)->delete($id);
        DB::transaction(function () use ($id) {

        User::whereIn('role', ['digital_marketing', 'content_creator', 'digital_marketing_manager', 'head_of_division'])
            ->whereJsonContains('client', $id)
            ->get()
            ->each(function($user) use ($id) {
                $cleaned = collect($user->client)->reject(fn($val) => $val == $id)->values()->all();
                $user->update(['client' => $cleaned]);
            });
        });
        return redirect()->back();
    }

    public function docs(){
        $title = "Access Token";
        return view('cms.produk.access_token', compact("title"));
    }

    public function getBawahanDM($id){
        $dm = User::where('role', 'digital_marketing')
              ->where('dmm', $id)
              ->get(['id', 'email']);
        return $dm;
    }

    public function getBawahanCC($id){
        $cc = User::where('role', 'content_creator')
              ->where('dm', $id)
              ->get(['id', 'email']);
        return $cc;
    }
}
