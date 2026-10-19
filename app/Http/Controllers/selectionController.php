<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Selection;
use App\Models\gformAffiliate;
use App\Models\Social_Creator;
class selectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request->all());
        $project = DB::table('barang_client')->where('id', $request->project_id)->first();
        $title = "Selection";

        $data = Selection::join('eikyoo', 'eikyoo.id', '=', 'affiliator_gafi.id_eikyoo')
        ->join('social_commerce_creator', 'social_commerce_creator.id', '=', 'eikyoo.id_affiliator')
        ->select(
            'affiliator_gafi.*', 
            'social_commerce_creator.user_tt', 
            'social_commerce_creator.user_ig',
            'social_commerce_creator.user_shopee',
            'social_commerce_creator.url_tt',
            'social_commerce_creator.url_ig',
            'social_commerce_creator.url_shopee',
            'social_commerce_creator.alamat',
            'social_commerce_creator.contact'
        )
        ->where('eikyoo.project', strtoupper($project->project))
        ->get();

        $getHeaderNames = function($collection, $columnName, $prefix) {
            $headers = [];
            dd($collection);
            foreach ($collection as $item) {
                $json = is_string($item->$columnName) ? json_decode($item->$columnName, true) : $item->$columnName;
                if (is_array($json)) {
                    foreach ($json as $obj) {
                        for ($i = 1; $i <= 10; $i++) {
                            $key = $prefix . $i;
                            if (isset($obj[$key]) && !empty($obj[$key])) {
                                $headers[$i] = $obj[$key];
                            }
                        }
                    }
                }
            }
            ksort($headers); // Urutkan berdasarkan nomor 1, 2, 3...
            return $headers;
        };

        $getHeaderNamesProject = function($first, $columnName, $prefix) {
            $headers = [];
            
            // Ambil datanya dulu
            $rawData = $first->$columnName;
            // Decode jika datanya string JSON, jika sudah array (Cast di Model) biarkan saja
            $data = is_string($rawData) ? json_decode($rawData, true) : $rawData;

            if (is_array($data)) {
                foreach ($data as $obj) {
                    for ($i = 1; $i <= 10; $i++) {
                        $key = $prefix . $i;
                        // Pastikan $obj adalah array dan key-nya ada
                        if (is_array($obj) && isset($obj[$key]) && !empty($obj[$key])) {
                            $headers[$i] = $obj[$key];
                        }
                    }
                }
            }

            ksort($headers);
            return $headers;
        };

        if($data){
            $productHeaders = $getHeaderNamesProject($project, 'barang', 'kategori_barang_');
            $shippingHeaders = $getHeaderNamesProject($project, 'shipping', 'kategori_shipping_');
            $hargaProduct = $getHeaderNamesProject($project, 'barang', 'harga_barang_');
            $hargaFee = $getHeaderNamesProject($project, 'shipping', 'harga_shipping_');
        }else{
            $productHeaders = $getHeaderNames($data, 'harga_produk', 'kategori_produk_');
            $shippingHeaders = $getHeaderNames($data, 'shipping_fee', 'kategori_shipping_');
            $hargaProduct = $getHeaderNames($data, 'harga_produk', 'harga_produk_');
            $hargaFee = $getHeaderNames($data, 'shipping_fee', 'harga_shipping_');
        }

        $creator = Selection::join('eikyoo', 'eikyoo.id', '=', 'affiliator_gafi.id_eikyoo')
        ->join('social_commerce_creator', 'social_commerce_creator.id', '=', 'eikyoo.id_affiliator')
        ->select(
            'affiliator_gafi.*', 
            'social_commerce_creator.user_tt', 
            'social_commerce_creator.user_ig',
            'social_commerce_creator.user_shopee',
            'social_commerce_creator.url_tt',
            'social_commerce_creator.url_ig',
            'social_commerce_creator.url_shopee',
            'social_commerce_creator.alamat',
            'social_commerce_creator.contact'
        )
        ->where('eikyoo.project', strtoupper($project->project))
        ->pluck('id_eikyoo')
        ->toArray();

        // Data Affiliator
        $affiliators = gformAffiliate::join('social_commerce_creator', 'social_commerce_creator.id', '=', 'eikyoo.id_affiliator')
                ->select('eikyoo.*', 'eikyoo.id as id_eikyoo', 'social_commerce_creator.*', 'social_commerce_creator.id as id_creator')
                ->where('eikyoo.project', strtoupper($project->project))
                ->whereNotIn('eikyoo.id', $creator)
                ->get();
        
                // dd($affiliators);
                // dd($data);

        return view('mcn.selection', compact('title', 'data', 'productHeaders', 'shippingHeaders', 'hargaProduct', 'hargaFee', 'affiliators', 'project'));
    }

    public function updateStatus(Request $request){
        try {
            $item = Selection::findOrFail($request->id);
            // Update kolom secara dinamis berdasarkan input
            $column = $request->column;
            $item->$column = $request->value; // Menyimpan "True" atau "False" sesuai format data Anda
            $item->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
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
        $request->validate([
            'affiliator_id' => 'required',
            'type'          => 'required|string|max:255',
            'harga_co_real' => 'required',
        ]);

        try {
            $hargaClean = preg_replace('/[^0-9]/', '', $request->harga_co_real);
            $eikyoo = gformAffiliate::find($request->affiliator_id);
            $affiliator = Social_Creator::find($eikyoo->id_affiliator);
            // 3. Simpan ke Database
            Selection::create([
                'id_eikyoo' => $request->affiliator_id,
                'done_4_konten'  => $request->type,
                'cb'          => $request->type,
                'td_pd'          => $request->type,
                'cek'          => $request->type,
                'harga_co_real' => $hargaClean,
                'type'          => $request->type,
                'gmv'           => (int)$affiliator->gmv_tt + (int)$affiliator->gmv_shopee
            ]);

            return redirect()->back()->with('success', 'Data Influencer berhasil ditambahkan!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $request->validate([
            'affiliator_id' => 'nullable',
            'type'          => 'required|string|max:255',
            'harga_co_real' => 'required',
        ]);

        try {
            // 2. Cari data selection yang akan diupdate
            $selection = Selection::findOrFail($id);
            // dd($selection);
            // 3. Bersihkan format Rupiah (buang titik/Rp)
            $hargaClean = preg_replace('/[^0-9]/', '', $request->harga_co_real);

            // 4. Cari data affiliator terbaru untuk update GMV (mengikuti logika store Anda)
            $eikyoo = gformAffiliate::find($selection->id_eikyoo);
            if (!$eikyoo) {
                return redirect()->back()->with('error', 'Data Project Affiliator tidak ditemukan.');
            }

            $affiliator = Social_Creator::find($eikyoo->id_affiliator);
            if (!$affiliator) {
                return redirect()->back()->with('error', 'Data Creator tidak ditemukan.');
            }

            // 5. Eksekusi Update
            $selection->update([
                'id_eikyoo'     => $selection->id_eikyoo,
                'done_4_konten' => $request->type, // Mengikuti pola store Anda
                'cb'            => $request->type,
                'td_pd'         => $request->type,
                'cek'           => $request->type,
                'harga_co_real' => $hargaClean,
                'type'          => $request->type,
                'gmv'           => (int)$affiliator->gmv_tt + (int)$affiliator->gmv_shopee
            ]);

            return redirect()->back()->with('success', 'Data Influencer berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // 1. Cari data berdasarkan ID
            $selection = Selection::findOrFail($id);

            // 2. Hapus data
            $selection->delete();

            // 3. Kembalikan respon sukses
            return redirect()->back()->with('success', 'Data Influencer berhasil dihapus!');

        } catch (\Exception $e) {
            // Jika terjadi error (misal data sudah tidak ada)
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}


