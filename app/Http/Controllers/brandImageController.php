<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class brandImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Brand Image";
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table("produk_client")->whereIn('id', $visibleClientIds)->where('status', 1)->get();
        $data = DB::table('brand_image')
                ->join('produk_client', 'produk_client.id', '=', 'brand_image.produk_client_id')
                ->whereIn('produk_client.id', $visibleClientIds)
                ->select("produk_client.nama", 'brand_image.*')
                ->get();
        return view("cms_branding.image", compact('title', 'data', 'produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        DB::table('brand_image')->insert([
            "produk_client_id" => $request->produk,
            "nama_image" => $request->nama,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Berhasil Menyimpan Data');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::table("brand_image")->where("id", $id)->update([
            'produk_client_id' => $request->produk,
            'nama_image' => $request->nama,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Berhasil Mengubah Data');
    }
    
    /**
     * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        DB::table("brand_image")->delete($id);
        return redirect()->back()->with('success', 'Berhasil Menghapus Data');
    }
}
