<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blacklist;
use App\Models\Social_Creator;
class BlacklistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Blacklist";
        $data = Blacklist::leftjoin('social_commerce_creator', 'social_commerce_creator.id', '=', 'black_list.id_creator')
        ->select('black_list.*', 'social_commerce_creator.user_tt', 'social_commerce_creator.url_tt', 'social_commerce_creator.user_ig', 'social_commerce_creator.url_ig', 'social_commerce_creator.user_shopee', 'social_commerce_creator.url_shopee', 'social_commerce_creator.alamat', 'social_commerce_creator.contact')
        ->orderBy('black_list.bulan', 'asc')
        ->get();
        $blacklistIds = Blacklist::pluck('id_creator')->toArray();

        // 3. Cari kreator yang BELUM ada di blacklist (yang muncul hanya yang bersih)
        $creators = Social_Creator::whereNotIn('id', $blacklistIds)->get();
        $creators_blacklist = Social_Creator::all();
        return view('mcn.blacklist', compact('title', 'data', 'creators', 'creators_blacklist'));
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
        try {
            Blacklist::create($request->all());
            return redirect()->back()->with('success', 'Data Blacklist berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
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
    public function update(Request $request, string $id)
    {
        try {
            $data = Blacklist::findOrFail($id);

            // 3. Update data
            $data->update([
                'id_creator'  => (int)$request->id_creator,
                'bulan'       => strtoupper($request->bulan), // Paksa ke huruf kapital agar rapi
                'job'         => $request->job,
                'keterangan'  => (int)$request->keterangan,
                'link_report' => $request->link_report,
                'note' => $request->note
            ]);

            // 4. Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Data affiliator berhasil diperbarui!');
        } catch (\Exception $e) {
            // Jika ada error database
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Blacklist::find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan atau sudah dihapus sebelumnya.');
        }

        try {
            $data->delete();

            return redirect()->back()->with('success', "Data berhasil dihapus");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
