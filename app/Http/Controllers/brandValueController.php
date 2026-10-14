<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class brandValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Brand Value";
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table("produk_client")->whereIn('id', $visibleClientIds)->where('status', 1)->get();
        $data = DB::table('brand_value')
                ->join('produk_client', 'produk_client.id', '=', 'brand_value.produk_client_id')
                ->whereIn('produk_client.id', $visibleClientIds)
                ->select("produk_client.nama", 'brand_value.*')
                ->get();
        return view("cms_branding.value", compact('title', 'data', 'produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        DB::table('brand_value')->insert([
            "produk_client_id" => $request->produk,
            "nama_value" => $request->nama,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Berhasil Menyimpan Data');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::table("brand_value")->where("id", $id)->update([
            'produk_client_id' => $request->produk,
            'nama_value' => $request->nama,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Berhasil Mengubah Data');
    }
    
    /**
     * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        DB::table("brand_value")->delete($id);
        return redirect()->back()->with('success', 'Berhasil Menghapus Data');
    }
}
