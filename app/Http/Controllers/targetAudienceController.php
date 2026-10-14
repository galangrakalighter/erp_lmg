<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TargetAudience;
use Illuminate\Support\Facades\DB;
class targetAudienceController extends Controller
{
    public function index() {
        $title = "Target Audience";
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table('produk_client')->whereIn('id', $visibleClientIds)->get();
        $data = TargetAudience::join('produk_client', 'produk_client.id', '=', 'target_audience.produk_client_id')
        ->whereIn('produk_client.id', $visibleClientIds)
        ->select('target_audience.*', 'produk_client.nama')
        ->get();
        // dd($data);
        return view('cms_market.target_audience.index', compact(
            'title',
            'produk',
            'data'
        ));
    }

    public function store(Request $request){
        // dd($request->all());
        $request->validate([
            'indikator' => 'nullable|string',
            'usia' => 'nullable|string',
            'gender' => 'nullable|string',
            'negara' => 'nullable|string',
            'wilayah' => 'nullable|string',
            'pekerjaan' => 'nullable|string',
            'gaya_hidup' => 'nullable|string',
            'status_sosial' => 'nullable|string',
            'penggunaan' => 'nullable|string',
            'manfaat' => 'nullable|string',
        ]);
        
        TargetAudience::create([
            'produk_client_id' => $request->produk,
            'indikator' => $request->indikator,
            'usia' => $request->usia, 
            'gender' => $request->gender,
            'negara' => $request->negara,
            'wilayah' => $request->wilayah,
            'pekerjaan' => $request->pekerjaan,
            'gaya_hidup' => $request->gaya_hidup,
            'status_sosial' => $request->status_sosial,
            'penggunaan' => $request->penggunaan,
            'manfaat' => $request->manfaat,
            "platform" => $request->platform
        ]);
        return redirect()->back()->with('success', 'Berhasil Di Simpan');
    }

    public function update($id, Request $request){
        // dd($request->all());
        $request->validate([
            'indikator' => 'nullable|string',
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
        
        TargetAudience::findOrFail($id)->update([
            'produk_client_id' => $request->produk,
            'indikator' => $request->indikator,
            'usia' => $request->usia, 
            'gender' => $request->gender,
            'negara' => $request->negara,
            'wilayah' => $request->wilayah,
            'pekerjaan' => $request->pekerjaan,
            'gaya_hidup' => $request->gaya_hidup,
            'status_sosial' => $request->status_sosial,
            'penggunaan' => $request->penggunaan,
            'manfaat' => $request->manfaat,
            "platform" => $request->platform
        ]);

        return redirect()->back()->with('success', 'Berhasil Di Simpan');
    }

    public function destroy($id){
        $id = (int)$id;
        TargetAudience::destroy($id);
        return redirect()->back()->with('success', 'Berhasil Di Hapus');
    }
}
