<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use App\Models\KategoriJasa;
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



    public function store(Request $request){
        dd($request->all());
        // $id = (int)$request->kategori_jasa_id;
        // $status = (int)$request->status;
        // $array = [
        //     'platform' => $request->platform,
        //     'access' => $request->access,
        // ];

        // $produk = ProdukClient::create([
        //     "kategori_jasa_id" => $id,
        //     "nama" => $request->client,
        //     "status" => $status,
        //     "access_token" => $array
        // ]);
            
        // foreach($request->dm as $dmId){
        //     $user = User::find($dmId);
        //     if($user) {
        //         $clientLama = $user->client ?? []; 
                
        //         $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
                
        //         $user->update([
        //             'client' => array_values($clientBaru)
        //         ]);
        //     }
        // }

        // if($request->dmm){
        //     $user = User::find($request->dmm);
        //     if($user){
        //         $clientLama = $user->client ?? []; 
                
        //         $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
                
        //         $user->update([
        //             'client' => array_values($clientBaru)
        //         ]);
        //     }
        // }

        
        // if(Auth::user()->role != "super_admin"){
        //     $client_baru = array_unique(array_merge(Auth::user()->client ?? [], [$produk->id]));
        //     Auth::user()->update([
        //         'client' => array_values($client_baru)
        //     ]);
        // }

        // foreach($request->cc as $ccId){
        //     $user = User::find($ccId);
        //     if($user) {
        //         $clientLama = $user->client ?? [];
        //         $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
                
        //         $user->update([
        //             'client' => array_values($clientBaru)
        //         ]);
        //     }
        // }

        // $user = User::whereJsonContains('divisi', (int)$request->divisi)->where('role', 'head_of_division')->first();
        // if($user) {
        //     $clientLama = $user->client ?? [];
        //     $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
            
        //     $user->update([
        //         'client' => array_values($clientBaru)
        //     ]);
        // }
        // return redirect()->back();
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
