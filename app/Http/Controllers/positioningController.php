<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Positioning;
use Illuminate\Support\Facades\DB;
class positioningController extends Controller
{
    public function index(){
        $title = "Target Audience";
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table('produk_client')->whereIn('id', $visibleClientIds)->get();
        $data = Positioning::join('produk_client', 'produk_client.id', '=', 'positioning.produk_client_id')
        ->whereIn('produk_client.id', $visibleClientIds)
        ->select('positioning.*', 'produk_client.nama')
        ->get();
        
        return view('cms_market.positioning.index', compact(
            'title',
            'produk',
            'data'
        ));
    }

    public function store(Request $request){
        $request->validate([
            "indikator" => "nullable|string",
            "deskripsi" => "nullable|string"
        ]);

        Positioning::create([
            "produk_client_id" => $request->produk,
            "indikator" => $request->indikator,
            "deskripsi" => $request->deskripsi,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Berhasil Di Simpan');
    }

    public function update($id, Request $request){
        // dd($request->all());
        $request->validate([
            "indikator" => "nullable|string",
            "deskripsi" => "nullable|string"
        ]);
        Positioning::findOrFail($id)->update([
            "produk_client_id" => $request->produk,
            "indikator" => $request->indikator,
            "deskripsi" => $request->deskripsi,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Berhasil Di Simpan');
    }

    public function destroy($id){
        $id = (int)$id;
        Positioning::destroy($id);
        return redirect()->back()->with('success', 'Berhasil Di Hapus');
    }
}
