<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class analize4PController extends Controller
{
    public function index(){
        $title = 'Analisa 4P';
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table('produk_client')->whereIn('id', $visibleClientIds)->get();
        $data = DB::table('analize_4p')->join('produk_client', 'produk_client.id', '=', 'analize_4p.produk_client_id')->whereIn('produk_client.id', $visibleClientIds)->select('analize_4p.*', 'produk_client.nama')->get();
        // dd($data);
        return view('cms_market.4p.index', compact(
            'title',
            'produk',
            'data'
        ));
    }

    public function store(Request $request){
        DB::table("analize_4p")->insert([
            'produk_client_id' => $request->produk,
            "nama_analisis" => $request->nama,
            "type" => $request->type,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Data Berhasil Disimpan');
    }

    public function update($id, Request $request){
        DB::table('analize_4p')->where('id', $id)->update([
            "produk_client_id" => $request->produk,
            "nama_analisis" => $request->nama,
            "type" => $request->type,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Data Berhasil Disimpan');
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('analize_4p')->where('id', $id)->delete($id);

        return redirect()->back()->with('success', 'Data Berhasil DiHapus');
    }
}
