<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class taglineController extends Controller
{
    public function index()
    {
        $title = "Brand Tagline";
        $visibleClientIds = \App\Models\ProdukClient::query()->visibleTo(auth()->user())->pluck('produk_client.id');
        $produk = DB::table("produk_client")->whereIn('id', $visibleClientIds)->where('status', 1)->get();
        $data = DB::table('brand_tagline')
                ->join('produk_client', 'produk_client.id', '=', 'brand_tagline.produk_client_id')
                ->whereIn('produk_client.id', $visibleClientIds)
                ->select("produk_client.nama", 'brand_tagline.*')
                ->get();
        return view("cms_branding.tagline", compact('title', 'data', 'produk'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        DB::table('brand_tagline')->insert([
            "produk_client_id" => $request->produk,
            "nama_tagline" => $request->nama_tagline,
            "nama_hashtagline" => "#".$request->nama_hastagline,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Berhasil Menyimpan Data');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::table("brand_tagline")->where("id", $id)->update([
            'produk_client_id' => $request->produk,
            "nama_tagline" => $request->nama_tagline,
            "nama_hashtagline" => "#".$request->nama_hastagline,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Berhasil Mengubah Data');
    }
    
    /**
     * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        DB::table("brand_tagline")->delete($id);
        return redirect()->back()->with('success', 'Berhasil Menghapus Data');
    }
}
