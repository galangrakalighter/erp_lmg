<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class StatusController extends Controller
{
    public function index($name){
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        $datatable = DB::table('status');
        if($name == "LGTR"){
            $data = $datatable->where("kategori_id", 1);
        }else{
            $data = $datatable->where("kategori_id", 2);
        }
        $data = $datatable->get();
        $title = "Status";
        return view('cms.status.index', compact('data', 'title', 'role'));
    }

    public function store(Request $request, $name){
        if($name == "LGTR"){
            $divisi = 1;
        }else {
            $divisi = 2;
        }
        DB::table("status")->insert([
            "nama_status" => $request->nama_status,
            "status" => $request->status,
            "kategori_id" => $divisi
        ]);

        return redirect()->back();
    }

    public function update($id, Request $request){
        DB::table('status')->where('id', $id)->update([
            "nama_status" => $request->nama_status,
            "status" => $request->status
        ]);

        return redirect()->back();
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('status')->where('id', $id)->delete($id);

        return redirect()->back();
    }
}
