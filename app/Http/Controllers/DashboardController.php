<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserDivision;
use App\Models\ProdukClient;

class DashboardController extends Controller
{
    public function index(){
        $data = DB::table('kategori_jasa')->get();
        $title = "Dashbard";
        return view('index', compact('title'));
    }

    public function dashboard(){
        $title = 'Dashboard';
        $user = Auth::user();

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = $user->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]['role_id'])->value('role_name') : "Super Admin";
        $allUser = UserDivision::join('users', 'users.id', '=', 'user_division.user_id')->where('user_division.parent_id', $user->id)->orWhere('user_division.user_id', $user->id)->select('users.id', 'users.email')->get();
        if($user->isAdmin == true){
            $allUser = User::where('isAdmin', false)->get();
        }
        $divisiId = $user->isAdmin == false ? $divisi[0]->divisi_id : null;
        $produk =  DB::table('client_division')
            ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
            ->join('produk_client', 'produk_client.id', '=', 'client_division.client_id');
            if($role != "Super Admin"){
                $client = $produk->join('user_client', 'user_client.client_id', '=', 'produk_client.id')
                ->where('user_client.user_id', $user->id);
            }
            $client = $produk->where('produk_client.status', 1)->select('produk_client.id as id_client', 'produk_client.nama as nama_client')
            ->get();

        return view('hc.dashboard_new', compact('title', 'allUser', 'role', 'client', 'divisiId'));
    }

    public function kpi_dashboard(){
        $title = 'Dashboard KPI';
        // Kita hanya perlu daftar project untuk dropdown
        if(Auth::user()->isAdmin == true){
            $listProject = ProdukClient::all();
        }else{
            $listProject = ProdukClient::whereIn('id', Auth::user()->kpis()->pluck('project'))->get();
        }

        $user = Auth::user();

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = $user->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]['role_id'])->value('role_name') : "Super Admin";
        $listBawahan = UserDivision::join('users', 'users.id', '=', 'user_division.user_id')->where('user_division.parent_id', $user->id)->orWhere('user_division.user_id', $user->id)->select('users.id', 'users.email')->get();
        if($user->isAdmin == true){
            $listBawahan = User::where('isAdmin', false)->get();
        }

        // dd($listBawahan);
        
        return view('hc.kpi', compact('title', 'listProject', 'listBawahan'));
    }
}
