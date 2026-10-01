<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class HookController extends Controller
{
    public function index($name){
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        $datatable = DB::table('hooks');
        if($name == "LGTR"){
            $data = $datatable->where("kategori_id", 1);
        }else{
            $data = $datatable->where("kategori_id", 2);
        }
        $data = $datatable->get();
        $title = "Hooks";
        $activeProductCode = $name;
        return view('cms.hook.index', compact('data', 'title', 'role', 'activeProductCode'));
    }

    public function store(Request $request, $name){
        $request->validate(['hook' => 'required|string|max:255', 'fungsi_singkat' => 'nullable|string|max:1000', 'contoh' => 'nullable|string|max:3000', 'status' => 'required|integer']);
        if($name == "LGTR"){
            $divisi = 1;
        }else {
            $divisi = 2;
        }
        $status = (int)$request->status;
        DB::table("hooks")->insert([
            "hooks" => $request->hook,
            "fungsi_singkat" => $request->fungsi_singkat,
            "contoh" => $request->contoh,
            "status" => $status,
            "kategori_id" => $divisi
        ]);

        return redirect()->back();
    }

    public function update($id, Request $request){
        $request->validate(['hook' => 'required|string|max:255', 'fungsi_singkat' => 'nullable|string|max:1000', 'contoh' => 'nullable|string|max:3000', 'status' => 'required|integer']);
        $id = (int)$id;
        $status = (int)$request->status;
        
        DB::table('hooks')->where('id', $id)->update([
            "hooks" => $request->hook,
            "fungsi_singkat" => $request->fungsi_singkat,
            "contoh" => $request->contoh,
            "status" => $status,
        ]);

        return redirect()->back();
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('hooks')->where('id', $id)->delete($id);

        return redirect()->back();
    }
}
