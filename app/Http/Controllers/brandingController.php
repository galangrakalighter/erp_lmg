<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
class brandingController extends Controller
{
    public function index($id, $platform){
        $title = 'Branding';
        $produk = DB::table("produk_client")->where("id", $id)->first();
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        
        $identify = DB::table('brand_identify')
                ->join('produk_client', 'produk_client.id', '=', 'brand_identify.produk_client_id')
                ->select("produk_client.nama", 'brand_identify.*')
                ->where("brand_identify.produk_client_id", $id)
                ->where("brand_identify.platform", $platform)
                ->first();

        $image = DB::table('brand_image')
                ->join('produk_client', 'produk_client.id', '=', 'brand_image.produk_client_id')
                ->select("produk_client.nama", 'brand_image.*')
                ->where("brand_image.produk_client_id", $id)
                ->where("brand_image.platform", $platform)
                ->get();

        $value = DB::table('brand_value')
                ->join('produk_client', 'produk_client.id', '=', 'brand_value.produk_client_id')
                ->select("produk_client.nama", 'brand_value.*')
                ->where("brand_value.produk_client_id", $id)
                ->where("brand_value.platform", $platform)
                ->get();

        $tagline = DB::table('brand_tagline')
                ->join('produk_client', 'produk_client.id', '=', 'brand_tagline.produk_client_id')
                ->select("produk_client.nama", 'brand_tagline.*')
                ->where("brand_tagline.produk_client_id", $id)
                ->where("brand_tagline.platform", $platform)
                ->get();

        $komunikasi = DB::table('gaya_komunikasi')
                ->join('produk_client', 'produk_client.id', '=', 'gaya_komunikasi.produk_client_id')
                ->select("produk_client.nama", 'gaya_komunikasi.*')
                ->where("gaya_komunikasi.produk_client_id", $id)
                ->where("gaya_komunikasi.platform", $platform)
                ->get();

        $komposisi = DB::table('komposisi')
                ->join('produk_client', 'produk_client.id', '=', 'komposisi.produk_client_id')
                ->select("produk_client.nama", 'komposisi.*')
                ->where("komposisi.produk_client_id", $id)
                ->where("komposisi.platform", $platform)
                ->get();
        
        $audio = DB::table('audio_brand')
                ->join('produk_client', 'produk_client.id', '=', 'audio_brand.produk_client_id')
                ->select("produk_client.nama", 'audio_brand.*')
                ->where("audio_brand.produk_client_id", $id)
                ->where("audio_brand.platform", $platform)
                ->get();

        $moodboard = DB::table('moodboard')
                ->join('produk_client', 'produk_client.id', '=', 'moodboard.produk_client_id')
                ->select("produk_client.nama", 'moodboard.*')
                ->where("moodboard.produk_client_id", $id)
                ->where("moodboard.platform", $platform)
                ->first();
                
        $alat = DB::table('alat_branding')
                ->join('produk_client', 'produk_client.id', '=', 'alat_branding.produk_client_id')
                ->select("produk_client.nama", 'alat_branding.*')
                ->where("alat_branding.produk_client_id", $id)
                ->where("alat_branding.platform", $platform)
                ->get();

        $halaman = "Branding";
        $readOnly = ! Auth::user()->isAdmin && $divisi->contains(
            fn ($row) => strtolower(trim((string) $row->division?->name)) === 'multimedia'
        );
        return view('branding.index', compact(
                "title", 
                'produk',
                'identify', 
                'image', 
                'value', 
                'tagline', 
                'komunikasi', 
                'komposisi', 
                'audio', 
                'moodboard',
                'alat',
                'platform',
                'halaman',
                'role',
                'readOnly'
        ));
    }
}
