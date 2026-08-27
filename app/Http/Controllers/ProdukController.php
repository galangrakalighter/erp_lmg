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
                        'buh'      => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 1)->pluck('email'),
                        'ads'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 4)->pluck('email'),
                        'dm'      => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 3)->pluck('email'),
                        'ccm'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 5)->pluck('email'), 
                        'cc'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 9)->pluck('email'), 
                        'team_ids' => $teams->pluck('id')->toArray(),
                    ];
                });
            
            return response()->json(['data' => $data]);
        }

        // Ambil semua user yang masuk di divisi ini (Eager Load user_divisions)
        $usersInDivisi = User::with('userDivisions')->get();
        
        $kategori = KategoriJasa::where('id', $divisiId)->get();
        // dd($usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 1));
        // dd($kategori);
        // dd($usersInDivisi->filter(fn($u) => $u->userDivisions->role_id == 1));
        // $divisions = $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 3)->flatMap->userDivisions;
        return view('cms.produk.index', [
            'title' => "Produk & Client",
            'buh'   => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 1),
            'dm'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 3),
            'ads'   => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 4),
            'ccm'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 5),
            'cc'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 9),
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
                    $kategori = KategoriJasa::where('id', 1)->get();
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
                'buh' => User::whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Busines Unit Head'))->get(['id', 'name']),
                'dm'  => User::with('userDivisions')->whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Digital Marketer'))->get()->map(function($u) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->first()->parent_id ?? 0];
                }),
                'ads' => User::with('userDivisions')->whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Advertising'))->get()->map(function($u) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->first()->parent_id ?? 0];
                }),
                'ccm'  => User::with('userDivisions')->whereHas('userDivisions.role', fn($q) => $q->where('role_name', 'Content Creator Manager'))->get()->map(function($u) {
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


    public function store(Request $request)
    {
        $accessTokenArray = [
            'platform' => $request->platform,
            'access'   => $request->access,
        ];

        try {
            DB::beginTransaction();

            // 1. Simpan produk
            $produk = ProdukClient::create([
                'kategori_jasa_id' => (int) $request->kategori_jasa_id,
                'nama'              => $request->client,
                'status'            => (int) $request->status,
                'access_token'      => $accessTokenArray,
            ]);

            // 2. Kumpulkan semua ID user dari form
            $allUserIds = collect()
                ->merge(collect($request->input('buh_ids', [])))
                ->merge(collect($request->input('dm_ids', [])))
                ->merge(collect($request->input('ads_ids', [])))
                ->merge(collect($request->input('ccm_ids', [])))
                ->merge(collect($request->input('cc_ids', [])));

            // 3. Tentukan user acuan untuk mencari HOD
            $currentUserDivision = Auth::user()
                ->userDivisions
                ->first();

            $targetUserId = (
                $currentUserDivision &&
                $currentUserDivision->role_id !== null
            )
                ? Auth::id()
                : collect($request->input('buh_ids', []))->first();

            // 4. Ambil hanya kolom user_id agar tidak ambigu
            if ($targetUserId) {
                $hod = UserDivision::query()
                    ->where('user_division.user_id', $targetUserId)
                    ->select('user_division.user_id')
                    ->first();

                if ($hod) {
                    // Tambahkan hanya user_id.
                    // Jangan menambahkan $hod->id.
                    $allUserIds->push((int) $hod->user_id);
                }
            }

            // 5. Tambahkan user login jika bukan admin
            if (!Auth::user()->isAdmin) {
                $allUserIds->push((int) Auth::id());
            }

            // 6. unique harus dijalankan setelah seluruh ID ditambahkan
            $allUserIds = $allUserIds
                ->filter(fn ($userId) => $userId !== null && $userId !== '')
                ->map(fn ($userId) => (int) $userId)
                ->filter(fn ($userId) => $userId > 0)
                ->unique()
                ->values();

            // 7. Cegah pasangan user_id dan client_id dimasukkan dua kali
            foreach ($allUserIds as $userId) {
                UserClient::firstOrCreate(
                    [
                        'user_id'   => $userId,
                        'client_id' => (int) $produk->id,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            }

            DB::table('client_division')->insertOrIgnore([
                'client_id'   => (int) $produk->id,
                'division_id' => (int) $request->divisi,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Produk dan Tim berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $id = (int) $id;

        $accessTokenArray = [
            'platform' => $request->platform,
            'access'   => $request->access,
        ];

        try {
            DB::beginTransaction();

            // 1. Update data produk
            $produk = ProdukClient::findOrFail($id);

            $produk->update([
                'kategori_jasa_id' => (int) $request->kategori_jasa_id,
                'nama'              => $request->nama,
                'status'            => (int) $request->status,
                'access_token'      => $accessTokenArray,
            ]);

            // 2. Kumpulkan semua ID user dari form edit
            $allUserIds = collect()
                ->merge(collect($request->input('buh_ids', [])))
                ->merge(collect($request->input('dm_ids', [])))
                ->merge(collect($request->input('ads_ids', [])))
                ->merge(collect($request->input('ccm_ids', [])))
                ->merge(collect($request->input('cc_ids', [])));

            // 3. Tentukan user acuan
            $targetUserId = !Auth::user()->isAdmin
                ? Auth::id()
                : collect($request->input('buh_ids', []))->first();

            // 4. Tambahkan HOD
            if ($targetUserId) {
                $hod = UserDivision::query()
                    ->where('user_division.user_id', $targetUserId)
                    ->select('user_division.user_id')
                    ->first();

                if ($hod) {
                    // Masukkan user_id, bukan id tabel user_division.
                    $allUserIds->push((int) $hod->user_id);
                }
            }

            // 5. Pastikan user login tetap memiliki akses jika bukan admin
            if (!Auth::user()->isAdmin) {
                $allUserIds->push((int) Auth::id());
            }

            // 6. Bersihkan dan hilangkan ID duplikat
            $allUserIds = $allUserIds
                ->filter(function ($userId) {
                    return $userId !== null && $userId !== '';
                })
                ->map(function ($userId) {
                    return (int) $userId;
                })
                ->filter(function ($userId) {
                    return $userId > 0;
                })
                ->unique()
                ->values();

            /*
            * 7. Ambil ID user yang sudah tersimpan.
            * Dipakai untuk membandingkan data lama dengan data baru.
            */
            $existingUserIds = UserClient::query()
                ->where('client_id', $produk->id)
                ->pluck('user_id')
                ->map(function ($userId) {
                    return (int) $userId;
                });

            /*
            * 8. Cari perbedaannya.
            *
            * $userIdsToAdd:
            * Ada di form baru, tetapi belum ada di database.
            *
            * $userIdsToDelete:
            * Ada di database, tetapi sudah tidak dipilih di form.
            *
            * ID yang ada di keduanya tidak akan di-update.
            */
            $userIdsToAdd = $allUserIds
                ->diff($existingUserIds)
                ->values();

            $userIdsToDelete = $existingUserIds
                ->diff($allUserIds)
                ->values();

            // 9. Hapus hanya user yang sudah tidak dipilih
            if ($userIdsToDelete->isNotEmpty()) {
                UserClient::query()
                    ->where('client_id', $produk->id)
                    ->whereIn('user_id', $userIdsToDelete->all())
                    ->delete();
            }

            // 10. Tambahkan hanya user yang belum tersimpan
            foreach ($userIdsToAdd as $userId) {
                UserClient::firstOrCreate(
                    [
                        'user_id'   => (int) $userId,
                        'client_id' => (int) $produk->id,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Perubahan produk dan tim berhasil diperbarui!'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Terjadi kesalahan: '.$e->getMessage()
                );
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

    // public function getBawahanDM($id){
    //     $dm = User::where('role', 'digital_marketing')
    //           ->where('dmm', $id)
    //           ->get(['id', 'email']);
    //     return $dm;
    // }

    // public function getBawahanCC($id){
    //     $cc = User::where('role', 'content_creator')
    //           ->where('dm', $id)
    //           ->get(['id', 'email']);
    //     return $cc;
    // }
    public function indexDivision($divisi){
        $title = "CMS Produk";
        $user = Auth::user()->id;
        $divisiName = User::find(Auth::user()->id)->userDivisions;
        $RoleSekarang = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisiName[0]->role_id)->value('role_name') : "Super Admin";
        $bawahanSemua = User::join('user_division', 'user_division.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->where('user_division.division_id', $divisi)
            ->whereIn('roles.level', [1, 2, 3])
            ->get();

        // Lalu pisahkan di controller atau langsung kirim semua ke view
        $buh = $bawahanSemua->where('level', 1);
        $bawahan2 = $bawahanSemua->where('level', 2);
        $bawahan3 = $bawahanSemua->where('level', 3);

        $return = [
            'title' => $title,
            'role' => $RoleSekarang,
            'divisi' => $divisi,
            'user' => $user,
            'buh' => $buh,
            'bawahan2' => $bawahan2,
            'bawahan3' => $bawahan3
        ];
        switch($divisi){
            case 3:
                return view('cms.produk.division.mcn', $return);
                break;
            case 5:
                return view('cms.produk.division.tech', $return);
                break;
            case 6:
                return view('cms.produk.division.finance', $return);
                break;
            case 7:
                return view('cms.produk.division.ecommerce', $return);
                break;
            default:
        }
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
                    ->filter(fn($u) => optional($u->userDivisions->first())->role->level == 1)
                    ->pluck('email')
                    ->values(),

                // MCN Specialist Manager
                'bawahan2' => $teams
                    ->filter(fn($u) => optional($u->userDivisions->first())->role->level == 2)
                    ->pluck('email')
                    ->values(),

                // MCN Specialist
                'bawahan3' => $teams
                    ->filter(fn($u) => optional($u->userDivisions->first())->role->level == 3)
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
        $produk = ProdukClient::with(['teams.userDivisions.role'])
            ->findOrFail($id);

        $teams = $produk->teams;

        return response()->json([
            'id'        => $produk->id,
            'client_id' => $produk->nama,
            'status'    => $produk->status,

            'buh' => $teams
                ->filter(fn($u) => $u->userDivisions->first()?->role?->level == 1)
                ->pluck('id')
                ->values(),

            'bawahan2' => $teams
                ->filter(fn($u) => $u->userDivisions->first()?->role?->level == 2)
                ->pluck('id')
                ->values(),

            'bawahan3' => $teams
                ->filter(fn($u) => $u->userDivisions->first()?->role?->level == 3)
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
