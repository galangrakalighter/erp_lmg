<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PerusahaanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = 'Perusahaan';
        $user = Auth::user();

        return view('finance.perusahaan', compact('title', 'user'));
    }

    public function getDataPerusahaan(){
        $data = DB::table('perusahaan')->get();

        return response()->json(['data' => $data]);
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
            // Validasi input
            $request->validate([
                'nama_perusahaan' => 'required|string|max:255|unique:perusahaan,nama_perusahaan',
                'status' => 'required|in:0,1'
            ]);

            // Simpan data menggunakan Query Builder
            DB::table('perusahaan')->insert([
                'nama_perusahaan' => $request->nama_perusahaan,
                'status' => $request->status,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data perusahaan berhasil ditambahkan!'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
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
            // Cek apakah data ada
            $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
            if (!$perusahaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data perusahaan tidak ditemukan.'
                ], 404);
            }

            // Validasi input (unique dengan mengecualikan ID saat ini)
            $request->validate([
                'nama_perusahaan' => 'required|string|max:255|unique:perusahaan,nama_perusahaan,' . $id,
                'status' => 'required|in:0,1'
            ]);

            // Update data
            DB::table('perusahaan')->where('id', $id)->update([
                'nama_perusahaan' => $request->nama_perusahaan,
                'status' => $request->status,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data perusahaan berhasil diperbarui!'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $perusahaan = DB::table('perusahaan')->where('id', $id)->first();
            if (!$perusahaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data perusahaan tidak ditemukan.'
                ], 404);
            }

            DB::table('perusahaan')->where('id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data perusahaan berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
}
