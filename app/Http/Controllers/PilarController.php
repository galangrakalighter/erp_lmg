<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PilarController extends Controller
{
    public function index($name){
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        $datatable = DB::table("pilar");
        if($name == "LGTR"){
            $data = $datatable->where("kategori_id", 1);
        }else{
            $data = $datatable->where("kategori_id", 2);
        }
        $data = $datatable->get();
        $title = "Pilar";
        return view('cms.pilar.index', compact('data', 'title', 'role'));
    }

    public function store(Request $request, $name){
        if($name == "LGTR"){
            $divisi = 1;
        }else {
            $divisi = 2;
        }
        $status = (int)$request->status;
        DB::table("pilar")->insert([
            "pilar" => $request->pilar,
            "status" => $status,
            "kategori_id" => $divisi
        ]);

        return redirect()->back();
    }

    public function update($id, Request $request){
        $id = (int)$id;
        $status = (int)$request->status;
        DB::table('pilar')->where('id', $id)->update([
            "pilar" => $request->pilar,
            "status" => $status
        ]);

        return redirect()->back();
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('pilar')->where('id', $id)->delete($id);

        return redirect()->back();
    }
}
