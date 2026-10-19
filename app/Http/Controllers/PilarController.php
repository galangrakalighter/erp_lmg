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
        $activeProductCode = $name;
        return view('cms.pilar.index', compact('data', 'title', 'role', 'activeProductCode'));
    }

    public function store(Request $request, $name){
        $request->validate(['pilar' => 'required|string|max:255', 'fungsi_singkat' => 'nullable|string|max:1000', 'contoh' => 'nullable|string|max:3000', 'status' => 'required|integer']);
        if($name == "LGTR"){
            $divisi = 1;
        }else {
            $divisi = 2;
        }
        $status = (int)$request->status;
        DB::table("pilar")->insert([
            "pilar" => $request->pilar,
            "fungsi_singkat" => $request->fungsi_singkat,
            "contoh" => $request->contoh,
            "status" => $status,
            "kategori_id" => $divisi
        ]);

        return redirect()->back();
    }

    public function update($id, Request $request){
        $request->validate(['pilar' => 'required|string|max:255', 'fungsi_singkat' => 'nullable|string|max:1000', 'contoh' => 'nullable|string|max:3000', 'status' => 'required|integer']);
        $id = (int)$id;
        $status = (int)$request->status;
        DB::table('pilar')->where('id', $id)->update([
            "pilar" => $request->pilar,
            "fungsi_singkat" => $request->fungsi_singkat,
            "contoh" => $request->contoh,
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
