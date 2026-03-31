<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class BarangClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Project MCN";
        $barang = DB::table('barang_client')->orderBy('id', 'desc')->get();
        $data = $barang->map(function ($item) {
            $item->barang = json_decode($item->barang, true); 
            $item->shipping = json_decode($item->shipping, true);

            return $item;
        });
        return view('mcn.barang', compact('title', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $barang_result = [];
        $item_barang = [];
        $total_harga_produk = 0;
        $total_shipping_fee = 0;
        foreach ($request->barang as $index => $name) {
            $key = $index + 1;
            $cleanPrice = (int) preg_replace('/[^0-9]/', '', $request->harga_barang[$index]);
            $total_harga_produk += $cleanPrice;
            $item_barang["kategori_barang_{$key}"] = $name;
            $item_barang["harga_barang_{$key}"] = $cleanPrice;
        }
        $barang_result[] = $item_barang;

        // 2. Olah data shipping
        $shipping_result = [];
        $item_shipping = [];
        foreach ($request->shipping as $index => $name) {
            $key = $index + 1;
            $cleanPrice = (int) preg_replace('/[^0-9]/', '', $request->harga_shipping[$index]);
            $total_shipping_fee += $cleanPrice;
            $item_shipping["kategori_shipping_{$key}"] = $name;
            $item_shipping["harga_shipping_{$key}"] = $cleanPrice;
        }
        $shipping_result[] = $item_shipping;

        // 3. Insert menggunakan DB::table
        // WAJIB gunakan json_encode() karena DB Query Builder tidak otomatis mengonversi array
        DB::table('barang_client')->insert([
            'project'  => $request->project,
            'barang'   => json_encode($barang_result),  // <--- Diubah jadi String JSON
            'shipping' => json_encode($shipping_result), // <--- Diubah jadi String JSON
            'total_harga_produk' => $total_harga_produk,
            'total_shipping_fee' => $total_shipping_fee,
            'ongkir' => (int) preg_replace('/[^0-9]/', '', $request->ongkir),
            'biaya_layanan_pembeli' => (int) preg_replace('/[^0-9]/', '', $request->biaya_layanan),
            'biaya_penanganan' => (int) preg_replace('/[^0-9]/', '', $request->biaya_penanganan)
        ]);

        return redirect()->back()->with('success', 'Data berhasil masuk!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Olah data barang (Sama dengan logika store)
        $barang_result = [];
        $item_barang = [];
        $total_harga_produk = 0;
        $total_shipping_fee = 0;
        // Pastikan ada data barang yang dikirim
        if ($request->has('barang')) {
            foreach ($request->barang as $index => $name) {
                $key = $index + 1;
                $cleanPrice = (int) preg_replace('/[^0-9]/', '', $request->harga_barang[$index]);
                $total_harga_produk += $cleanPrice;
                $item_barang["kategori_barang_{$key}"] = $name;
                $item_barang["harga_barang_{$key}"] = $cleanPrice;
            }
        }
        $barang_result[] = $item_barang;

        // 2. Olah data shipping
        $shipping_result = [];
        $item_shipping = [];
        
        if ($request->has('shipping')) {
            foreach ($request->shipping as $index => $name) {
                $key = $index + 1;
                $cleanPrice = (int) preg_replace('/[^0-9]/', '', $request->harga_shipping[$index]);
                $total_shipping_fee += $cleanPrice;
                $item_shipping["kategori_shipping_{$key}"] = $name;
                $item_shipping["harga_shipping_{$key}"] = $cleanPrice;
            }
        }
        $shipping_result[] = $item_shipping;

        // 3. Update menggunakan DB::table berdasarkan ID
        DB::table('barang_client')
            ->where('id', $id)
            ->update([
                'project'  => $request->project,
                'barang'   => json_encode($barang_result),  // Simpan sebagai String JSON
                'shipping' => json_encode($shipping_result), // Simpan sebagai String JSON
                'total_harga_produk' => $total_harga_produk,
                'total_shipping_fee' => $total_shipping_fee,
                'ongkir' => (int) preg_replace('/[^0-9]/', '', $request->ongkir),
                'biaya_layanan_pembeli' => (int) preg_replace('/[^0-9]/', '', $request->biaya_layanan),
                'biaya_penanganan' => (int) preg_replace('/[^0-9]/', '', $request->biaya_penanganan)
            ]);

        return redirect()->back()->with('success', 'Project berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $project = DB::table('barang_client')->where('id', $id)->first();

        if (!$project) {
            return redirect()->back()->with('error', 'Data project tidak ditemukan!');
        }

        $eikyoo = DB::table('eikyoo')->where('project', strtoupper($project->project))->get();
        foreach($eikyoo as $d){
            DB::table('affiliator_gafi')->where('id_eikyoo', $d->id)->delete();
        }

        DB::table('eikyoo')->where('project', strtoupper($project->project))->delete();

        // 2. Eksekusi Penghapusan
        DB::table('barang_client')->where('id', $id)->delete();

        // 3. Redirect dengan pesan sukses
        return redirect()->back()->with('success', 'Project ' . $project->project . ' berhasil dihapus selamanya!');
    }
}
