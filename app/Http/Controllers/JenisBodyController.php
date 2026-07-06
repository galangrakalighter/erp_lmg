<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class JenisBodyController extends Controller
{
    public function index($name){
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        $datatable = DB::table('jenis_body');
        if($name == "LGTR"){
            $data = $datatable->where("kategori_id", 1);
        }else{
            $data = $datatable->where("kategori_id", 2);
        }
        $data = $datatable->get();
        $title = "Jenis Body";
        return view('cms.body.index', compact('data', 'title', 'role'));
    }

    public function store(Request $request, $name){
        if($name == "LGTR"){
            $divisi = 1;
        }else {
            $divisi = 2;
        }
        DB::table("jenis_body")->insert([
            "nama_body" => $request->nama,
            "status" => $request->status,
            "kategori_id" => $divisi
        ]);

        return redirect()->back();
    }

    public function update($id, Request $request){
        DB::table('jenis_body')->where('id', $id)->update([
            "nama_body" => $request->nama,
            "status" => $request->status
        ]);

        return redirect()->back();
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('jenis_body')->where('id', $id)->delete($id);

        return redirect()->back();
    }
}
