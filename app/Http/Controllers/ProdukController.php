<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use App\Models\KategoriJasa;
use App\Models\UserClient;
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
        // dd($kategori);
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


    public function getDetail($id, $id_user, $folderName)
    {
        try {
            $produk = ProdukClient::findOrFail($id);
            $currentTeamIds = $produk->teams()->pluck('users.id')->toArray();

            // Mengambil Role Sekarang sesuai logika Anda
            $user = User::find($id_user);
            $divisi = $user->userDivisions;

            if ($user->isAdmin) {
                $kategori = KategoriJasa::where('id', 1)->get();
                if($folderName == "cms_BD"){
                    $kategori = KategoriJasa::where('id', 2)->get();
                }
            } else {
                $kategori = KategoriJasa::where('id', $divisi[0]->division_id)->get();
                if($folderName == "cms_LGTR"){
                    $kategori = KategoriJasa::where('id', 2)->get();
                }
            }
            
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
                'dm'  => User::with('userDivisions')->whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Digital Marketing'))->get()->map(function($u) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->first()->parent_id ?? 0];
                }),
                'scc' => User::with('userDivisions')->whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Senior Content Creator'))->get()->map(function($u) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->first()->parent_id ?? 0];
                }),
                'cc'  => User::with('userDivisions')->whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Content Creator'))->get()->map(function($u) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->first()->parent_id ?? 0];
                }),
            ];

            return response()->json([
                'status'        => 'success',
                'produk'        => $produk,
                'current_teams' => $currentTeamIds,
                'options'       => $options,
                'is_admin'      => $isUserAdmin,
                'kategori'      => $kategori,
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

    public function update($id, Request $request)
    {
        $id = (int)$id;
        
        // Siapkan array access_token
        $accessTokenArray = [
            'platform' => $request->platform,
            'access'   => $request->access,
        ];

        try {
            DB::beginTransaction();

            // 1. Update Data Produk
            $produk = ProdukClient::findOrFail($id);
            $produk->update([
                "kategori_jasa_id" => (int)$request->kategori_jasa_id,
                "nama"             => $request->client,
                "status"           => (int)$request->status,
                "access_token"     => $accessTokenArray 
            ]);

            // 2. Kumpulkan semua ID User dari input modal edit
            $allUserIds = collect([
                $request->dmm_ids, 
                $request->dm_ids,
                $request->scc_ids,
                $request->cc_ids
            ])->flatten()->filter()->unique();

            // 3. Tambahkan Head of Division (HOD) secara otomatis
            // Kita ambil DMM pertama sebagai patokan divisi jika user login adalah Admin
            $targetUserId = (Auth::user()->isAdmin == false) 
                            ? Auth::user()->id 
                            : ($request->dmm_ids[0] ?? null);

            if ($targetUserId) {
                $hod = UserDivision::where('user_id', $targetUserId)->first();
                if ($hod) {
                    // Tambahkan ID user dari HOD tersebut (asumsi HOD adalah user di divisi yang sama)
                    // Jika logic HOD Anda adalah user tertentu, sesuaikan query-nya di sini
                    $allUserIds->push($hod->user_id);
                }
            }

            // 4. Pastikan pembuat/user login tetap punya akses jika bukan Admin
            if (Auth::user()->isAdmin == false) {
                $allUserIds->push(Auth::user()->id);
            }

            // 5. Sinkronisasi Tabel Pivot (User Client)
            // sync() akan menghapus yang lama dan mengganti dengan daftar ID yang baru
            $produk->teams()->sync($allUserIds->unique()->toArray());

            DB::commit();
            return redirect()->back()->with('success', 'Perubahan produk dan tim berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $produk = ProdukClient::findOrFail($id);

            // Putus semua hubungan user dengan produk ini di tabel pivot
            // (Opsional jika di migration sudah pakai onDelete('cascade'))
            $produk->teams()->detach(); 

            // Hapus produk
            $produk->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
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

    public function indexDivision($divisi){
        $title = "CMS Produk";
        $user = Auth::user()->id;
        $divisiName = User::find(Auth::user()->id)->userDivisions;
        $RoleSekarang = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisiName[0]->role_id)->value('role_name') : "Super Admin";
        $buh = User::join('user_division', 'user_division.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->where('roles.level', 1)
                ->where('user_division.division_id', $divisi)
                ->get();

        $bawahan2 = User::join('user_division', 'user_division.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->where('roles.level', 2)
                ->where('user_division.division_id', $divisi)
                ->get();

        $bawahan3 = User::join('user_division', 'user_division.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->where('roles.level', 3)
                ->where('user_division.division_id', $divisi)
                ->get();
        
        return view('cms.produk.division.index', [
            'title' => $title,
            'role' => $RoleSekarang,
            'divisi' => $divisi,
            'user' => $user,
            'buh' => $buh,
            'mcn' => $bawahan2,
            'ms' => $bawahan3
        ]);
    }

    public function getDataDivision($user, $divisi)
    {
        $user = User::findOrFail($user);

        $query = ProdukClient::with(['teams.userDivisions.role'])
            ->join('client_division', 'client_division.client_id', '=', 'produk_client.id')
            ->where('client_division.division_id', $divisi)
            ->select('produk_client.*');

        // Jika bukan admin, hanya tampilkan client yang dimiliki user
        if (!$user->isAdmin) {
            $query->whereHas('teams', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $data = $query->get()->map(function ($client) {

            $teams = $client->teams;

            return [
                'id'       => $client->id,
                'nama'     => $client->nama,
                'category' => $client->category,
                'status'   => $client->status,

                // Business Unit Head
                'buh' => $teams
                    ->filter(fn($u) => optional($u->userDivisions->first())->role_id == 6)
                    ->pluck('email')
                    ->values(),

                // MCN Specialist Manager
                'mcn' => $teams
                    ->filter(fn($u) => optional($u->userDivisions->first())->role_id == 7)
                    ->pluck('email')
                    ->values(),

                // MCN Specialist
                'ms' => $teams
                    ->filter(fn($u) => optional($u->userDivisions->first())->role_id == 8)
                    ->pluck('email')
                    ->values(),

                'team_ids' => $teams->pluck('id')->toArray(),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }



    public function tambahDataClient(Request $request, $divisi){
        $produk = ProdukClient::create([
            'kategori_jasa_id' => null,
            'nama' => $request->client,
            'status' => $request->status,
            'access_token' => null
        ]);

        $allId = [];

        foreach($request->buh as $buh){
            array_push($allId, $buh);
        }

        foreach($request->mcn as $mcn){
            array_push($allId, $mcn);
        }
        foreach($request->ms as $ms){
            array_push($allId, $ms);
        }

        foreach($allId as $id){
            UserClient::insert([
                'user_id' => $id,
                'client_id' => $produk->id,
                'assigned_at' => now()
            ]);
        }

        DB::table('client_division')->insert([
            'client_id' => $produk->id,
            'division_id' => $divisi
        ]);

        return response()->json(['success' => 'Berhasil Dibuat']);
    }

    public function updateDataClient(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $produk = ProdukClient::findOrFail($id);

            // Update data client
            $produk->update([
                'nama'   => $request->client,
                'status' => $request->status,
            ]);

            // Hapus relasi user lama
            UserClient::where('client_id', $produk->id)->delete();

            // Gabungkan seluruh user yang dipilih
            $allId = array_merge(
                $request->buh ?? [],
                $request->mcn ?? [],
                $request->ms ?? []
            );

            // Hilangkan ID yang sama jika ada
            $allId = array_unique($allId);

            // Insert relasi baru
            foreach ($allId as $userId) {

                UserClient::create([
                    'user_id'     => $userId,
                    'client_id'   => $produk->id,
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => 'Data berhasil diupdate'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProduk($id)
    {
        $produk = ProdukClient::with(['teams.userDivisions'])
            ->findOrFail($id);

        $teams = $produk->teams;

        return response()->json([
            'id'        => $produk->id,
            'client_id' => $produk->nama,
            'status'    => $produk->status,

            'buh' => $teams
                ->filter(fn($u) => $u->userDivisions->first()?->role_id == 6)
                ->pluck('id')
                ->values(),

            'mcn' => $teams
                ->filter(fn($u) => $u->userDivisions->first()?->role_id == 7)
                ->pluck('id')
                ->values(),

            'ms' => $teams
                ->filter(fn($u) => $u->userDivisions->first()?->role_id == 8)
                ->pluck('id')
                ->values(),
        ]);
    }

    public function hapusDataClient($id)
    {
        DB::beginTransaction();

        try {

            $produk = ProdukClient::findOrFail($id);

            // Hapus relasi user
            UserClient::where('client_id', $id)->delete();

            // Hapus relasi divisi
            DB::table('client_division')
                ->where('client_id', $id)
                ->delete();

            // Hapus client
            $produk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client berhasil dihapus.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
}
