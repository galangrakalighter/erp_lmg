<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Segmentasi;
class segmentasiController extends Controller
{
    public function index() {
        $title = "Segmentasi";
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table('produk_client')->whereIn('id', $visibleClientIds)->get();
        $data = Segmentasi::join('produk_client', 'produk_client.id', '=', 'segmentasi.produk_client_id')
        ->whereIn('produk_client.id', $visibleClientIds)
        ->whereNull('segmentasi.archived_at')
        ->select('segmentasi.*', 'produk_client.nama')
        ->get();
        return view('cms_market.segmentasi.index', compact(
            'title',
            'produk',
            'data'
        ));
    }

    public function store(Request $request){
        // dd($request->all());
        $request->validate([
            'usia' => 'nullable|string',
            'gender' => 'nullable|string',
            'negara' => 'nullable|string',
            'wilayah' => 'nullable|string',
            'gaya_hidup' => 'nullable|string',
            'status_sosial' => 'nullable|string',
            'minat' => 'nullable|string',
            'penggunaan' => 'nullable|string',
            'loyalitas' => 'nullable|string',
            'manfaat' => 'nullable|string',
        ]);

        Segmentasi::create([
            'produk_client_id' => $request->produk,
            'usia' => $request->usia, 
            'gender' => $request->gender,
            'wilayah' => $request->wilayah,
            'gaya_hidup' => $request->gaya_hidup,
            'status_sosial' => $request->status_sosial,
            'minat' => $request->minat,
            'penggunaan' => $request->penggunaan,
            'loyalitas' => $request->loyalitas,
            'negara' => $request->negara,
            'manfaat' => $request->manfaat,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Berhasil Di Simpan');
    }

    public function update($id, Request $request){
        // dd($request->all());
        $request->validate([
            'usia' => 'nullable|string',
            'gender' => 'nullable|string',
            'negara' => 'nullable|string',
            'wilayah' => 'nullable|string',
            'gaya_hidup' => 'nullable|string',
            'status_sosial' => 'nullable|string',
            'minat' => 'nullable|string',
            'penggunaan' => 'nullable|string',
            'loyalitas' => 'nullable|string',
            'manfaat' => 'nullable|string',
        ]);
        
        Segmentasi::query()->whereNull('archived_at')->findOrFail($id)->update([
            'produk_client_id' => $request->produk,
            'usia' => $request->usia, 
            'gender' => $request->gender,
            'wilayah' => $request->wilayah,
            'gaya_hidup' => $request->gaya_hidup,
            'status_sosial' => $request->status_sosial,
            'minat' => $request->minat,
            'penggunaan' => $request->penggunaan,
            'loyalitas' => $request->loyalitas,
            'negara' => $request->negara,
            'manfaat' => $request->manfaat,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Berhasil Di Simpan');
    }

    public function destroy($id){
        $id = (int)$id;
        DB::table('segmentasi')->where('id', $id)->whereNull('archived_at')->update(['archived_at' => now(), 'archived_by' => auth()->id(), 'updated_at' => now()]);
        return redirect()->back()->with('success', 'Berhasil Di Hapus');
    }
}
