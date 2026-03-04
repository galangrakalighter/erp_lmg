<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Social_Creator;
use App\Imports\AffiliatorImport;
class SosialCreatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = "Creator";
        $data = Social_Creator::all();
        return view('mcn.creator', compact('title', 'data'));
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
        if ($request->has('categories_array')) {
            $data['category'] = implode(', ', $request->categories_array);
        } else {
            $data['category'] = null;
        }

        Social_Creator::create($data);
        return redirect()->back()->with('success', 'Data Creator ' . $request->user_tt . ' berhasil disimpan!');
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
        $creator = Social_Creator::findOrFail($id);
        $data = $request->all();
        
        if ($request->has('categories_array')) {
            $data['category'] = implode(', ', $request->categories_array);
        } else {
            $data['category'] = null;
        }

        $creator->update($data);
        return redirect()->back()->with('success', 'Data @' . $creator->user_tt . ' berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $creator = Social_Creator::findOrFail($id);
            $username = $creator->user_tt;
            $creator->delete();

            return redirect()->back()->with('success', "Data Creator @$username berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Gagal menghapus data: " . $e->getMessage());
        }
    }

    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new AffiliatorImport, $request->file('file'));
            return back()->with('success', 'Data berhasil diimport!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
