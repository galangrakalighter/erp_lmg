<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Arr;

class ProdukController extends Controller
{
    public function index($nama){
        $kategoriQuery = DB::table('kategori_jasa');
        if($nama == "LGTR"){
            $kategoriQuery = $kategoriQuery->where('id', 1);
        }else {
            $kategoriQuery = $kategoriQuery->whereIn('id', [2,3]);
        }
        $kategori = $kategoriQuery->get();

        $userClients = is_array(Auth::user()->client) ? Auth::user()->client : json_decode(Auth::user()->client, true) ?? [];
        $query = DB::table('produk_client')
        ->join('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
        ->select('produk_client.*', 'kategori_jasa.category');
        if ($nama == "LGTR") {
            $query->where('produk_client.kategori_jasa_id', 1);
        } else {
            $query->whereIn('produk_client.kategori_jasa_id', [2, 3]);
        }

        if(Auth::user()->role != "head_of_division" && Auth::user()->role != "super_admin"){
            $query->whereIn('produk_client.id', $userClients);
        }
        
        $dataClient = $query->when(!in_array(Auth::user()->role, ['head_of_division', 'super_admin']), function($q) use ($userClients) {
            return $q->whereIn('produk_client.id', $userClients);
        })
        ->get()->map(function($client) {
            $client->teams = User::whereJsonContains('client', $client->id)->get();
            return $client;
        });

        $divisi = 0;
        switch ($nama) {
            case "LGTR":
                $divisi = 1;
                break;
            case "BD":
                $divisi = 2;
                break;
            default:
                //code block
        }

        $dmm = User::whereJsonContains('divisi', $divisi)->where('role', 'digital_marketing_manager')->get();
        $dm = User::whereJsonContains('divisi', $divisi)->where('role', 'digital_marketing')->get();
        $cc = User::whereJsonContains('divisi', $divisi)->where('role', 'content_creator')->get();

        $title = "Produk";
        return view('cms.produk.index', compact('kategori', 'dataClient', 'title', 'dm', 'cc', 'dmm', 'divisi'));
    }

    public function store(Request $request){
        $id = (int)$request->kategori_jasa_id;
        $status = (int)$request->status;
        $array = [
            'platform' => $request->platform,
            'access' => $request->access,
        ];

        $produk = ProdukClient::create([
            "kategori_jasa_id" => $id,
            "nama" => $request->client,
            "status" => $status,
            "access_token" => $array
        ]);
            
        foreach($request->dm as $dmId){
            $user = User::find($dmId);
            if($user) {
                $clientLama = $user->client ?? []; 
                
                $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
                
                $user->update([
                    'client' => array_values($clientBaru)
                ]);
            }
        }

        if($request->dmm){
            $user = User::find($request->dmm);
            if($user){
                $clientLama = $user->client ?? []; 
                
                $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
                
                $user->update([
                    'client' => array_values($clientBaru)
                ]);
            }
        }

        
        if(Auth::user()->role != "super_admin"){
            $client_baru = array_unique(array_merge(Auth::user()->client ?? [], [$produk->id]));
            Auth::user()->update([
                'client' => array_values($client_baru)
            ]);
        }

        foreach($request->cc as $ccId){
            $user = User::find($ccId);
            if($user) {
                $clientLama = $user->client ?? [];
                $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
                
                $user->update([
                    'client' => array_values($clientBaru)
                ]);
            }
        }

        $user = User::whereJsonContains('divisi', (int)$request->divisi)->where('role', 'head_of_division')->first();
        if($user) {
            $clientLama = $user->client ?? [];
            $clientBaru = array_unique(array_merge($clientLama, [$produk->id]));
            
            $user->update([
                'client' => array_values($clientBaru)
            ]);
        }
        return redirect()->back();
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
