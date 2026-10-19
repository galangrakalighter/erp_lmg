<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Identify;

class brandIdentifyController extends Controller
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
        $path = $request->file('image')->store('identify', 'public');
        
        DB::table("brand_identify")->insert([
            'produk_client_id' => $request->produk,
            'identify'     => $path,
            'platform'     => $request->platform,
            'archived_at'  => null,
            'archived_by'  => null,
        ]);

        return redirect()->back()->with('success', 'Moodboard berhasil disimpan!');
    }

    public function update($id, Request $request){
        $identify = Identify::query()->whereNull('archived_at')->findOrFail($id);
        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'image' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Harus berupa gambar (JPG, PNG, JPEG, WEBP) dan maksimal 5MB!');
            }

            // Hapus foto lama & Simpan yang baru
            Storage::disk('public')->delete($identify->identify);
            $identify->identify = $request->file('image')->store('identify', 'public');
        }

        $identify->save();

        return redirect()->back()->with('success', 'Moodboard berhasil diperbarui!');
    }

    public function destroy($id){
        $updated = DB::table('brand_identify')->where('id', $id)->whereNull('archived_at')->update([
            'archived_at' => now(),
            'archived_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        abort_unless($updated > 0, 404, 'Branding aktif tidak ditemukan.');
        return redirect()->back()->with('success', 'Moodboard berhasil diarsipkan');
    }
}
