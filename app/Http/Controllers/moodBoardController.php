<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Moodboard;
use Illuminate\Support\Facades\Storage;
class moodBoardController extends Controller
{
    public function store(Request $request){
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'produk' => 'required',
            'image'  => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // 2. Jika Validasi Gagal, Redirect Back dengan Session Error
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'MoodBoard Harus berupa gambar (JPG, PNG, JPEG) dan maksimal 5MB!');
        }

        // 3. Jika Lolos, Simpan Data
        $path = $request->file('image')->store('moodboards', 'public');
        
        DB::table("moodboard")->insert([
            'produk_client_id' => $request->produk,
            'moodboard'     => $path,
            'platform'     => $request->platform,
        ]);

        return redirect()->back()->with('success', 'Moodboard berhasil disimpan!');
    }

    public function update($id, Request $request){
        $moodboard = Moodboard::findOrFail($id);
        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'image' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Harus berupa gambar (JPG, PNG, JPEG, WEBP) dan maksimal 5MB!');
            }

            // Hapus foto lama & Simpan yang baru
            Storage::disk('public')->delete($moodboard->moodboard);
            $moodboard->moodboard = $request->file('image')->store('moodboards', 'public');
        }

        $moodboard->save();

        return redirect()->back()->with('success', 'Moodboard berhasil diperbarui!');
    }

    public function destroy($id){
        $moodboard = Moodboard::findOrFail($id);

        // 2. Cek apakah file gambar ada di storage, jika ada hapus permanen
        if ($moodboard->image && Storage::disk('public')->exists($moodboard->image)) {
            Storage::disk('public')->delete($moodboard->image);
        }

        // 3. Hapus data dari database
        $moodboard->delete();
        return redirect()->back()->with('success', 'Berhasil Menghapus Data');
    }
}
