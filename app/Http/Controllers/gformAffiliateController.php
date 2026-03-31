<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\gformAffiliate;
use Illuminate\Support\Facades\DB;
use App\Models\Social_Creator;
class gformAffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $project = DB::table('barang_client')->where('id', $request->project_id)->first();
        $title = "Gform MCN Eikyoo";
        $data = gformAffiliate::join('social_commerce_creator', 'social_commerce_creator.id', '=', 'eikyoo.id_affiliator')
                ->select('eikyoo.*', 'eikyoo.id as id_eikyoo', 'social_commerce_creator.*', 'social_commerce_creator.id as id_creator')
                ->where('eikyoo.project', strtoupper($project->project))
                ->get();
        $creator = Social_Creator::select('id', 'user_ig', 'user_tt', 'user_shopee')->orderBy('id', 'ASC')->get();
        return view('mcn.gform', compact('title', 'data', 'creator', 'project'));
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
        $data = $request->all();
        dd($data);
        if($request->has('social_media')){
            $data['social_media'] = implode(', ', $request->input('social_media'));
        }

        $result = [
            "id_affiliator" => $request->creator_id,
            "social_media" => $data['social_media'],
            "wajib_follow" => $request->wajib_follow,
            "surat_pernyataan" => $request->surat_pernyataan,
            "project" => strtoupper($request->project),
            "status" => $request->status
        ];

        $affiliator = gformAffiliate::create($result);

        return redirect()->back()->with('success', 'Data Berhasil Di Tambahkan');
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
    public function update(int $id, Request $request)
    {
        $affiliate = gformAffiliate::find($id);
        $data = $request->all();
        if ($request->has('social_media')) {
            $data['social_media'] = implode(', ', $request->input('social_media'));
        } else {
            $data['social_media'] = null;
        }

        $result = [
            "id_affiliator" => $request->creator_id,
            "social_media" => $data['social_media'],
            "wajib_follow" => $request->wajib_follow,
            "surat_pernyataan" => $request->surat_pernyataan,
            "project" => strtoupper($request->project),
            "status" => $request->status
        ];

        $affiliate->update($result);
        return redirect()->back()->with('success', 'Data Affiliator berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        gformAffiliate::destroy($id);
        return redirect()->back()->with('error', "Berhasil Dihapus");
    }
}
