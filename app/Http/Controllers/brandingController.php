<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\PlatformMirroringService;
class brandingController extends Controller
{
    public function index($id, $platform, PlatformMirroringService $mirroring){
        $title = 'Branding';
        $mirroredPlatforms = $mirroring->platforms((int) $id, $platform);
        $withoutMirrorCopies = fn ($query, string $table) => count($mirroredPlatforms) > 1
            ? $query->whereNotIn($table.'.id', $mirroring->copiedTargetIds((int) $id, $table))
            : $query;
        $produk = DB::table("produk_client")->where("id", $id)->first();
        $divisi = User::find(Auth::user()->id)->userDivisions;
        $role = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        
        $identify = DB::table('brand_identify')
                ->join('produk_client', 'produk_client.id', '=', 'brand_identify.produk_client_id')
                ->select("produk_client.nama", 'brand_identify.*')
                ->where("brand_identify.produk_client_id", $id)
                ->whereIn("brand_identify.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'brand_identify'))
                ->first();

        $image = DB::table('brand_image')
                ->join('produk_client', 'produk_client.id', '=', 'brand_image.produk_client_id')
                ->select("produk_client.nama", 'brand_image.*')
                ->where("brand_image.produk_client_id", $id)
                ->whereIn("brand_image.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'brand_image'))
                ->get();

        $value = DB::table('brand_value')
                ->join('produk_client', 'produk_client.id', '=', 'brand_value.produk_client_id')
                ->select("produk_client.nama", 'brand_value.*')
                ->where("brand_value.produk_client_id", $id)
                ->whereIn("brand_value.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'brand_value'))
                ->get();

        $tagline = DB::table('brand_tagline')
                ->join('produk_client', 'produk_client.id', '=', 'brand_tagline.produk_client_id')
                ->select("produk_client.nama", 'brand_tagline.*')
                ->where("brand_tagline.produk_client_id", $id)
                ->whereIn("brand_tagline.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'brand_tagline'))
                ->get();

        $komunikasi = DB::table('gaya_komunikasi')
                ->join('produk_client', 'produk_client.id', '=', 'gaya_komunikasi.produk_client_id')
                ->select("produk_client.nama", 'gaya_komunikasi.*')
                ->where("gaya_komunikasi.produk_client_id", $id)
                ->whereIn("gaya_komunikasi.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'gaya_komunikasi'))
                ->get();

        $komposisi = DB::table('komposisi')
                ->join('produk_client', 'produk_client.id', '=', 'komposisi.produk_client_id')
                ->select("produk_client.nama", 'komposisi.*')
                ->where("komposisi.produk_client_id", $id)
                ->whereIn("komposisi.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'komposisi'))
                ->get();
        
        $audio = DB::table('audio_brand')
                ->join('produk_client', 'produk_client.id', '=', 'audio_brand.produk_client_id')
                ->select("produk_client.nama", 'audio_brand.*')
                ->where("audio_brand.produk_client_id", $id)
                ->whereIn("audio_brand.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'audio_brand'))
                ->get();

        $moodboard = DB::table('moodboard')
                ->join('produk_client', 'produk_client.id', '=', 'moodboard.produk_client_id')
                ->select("produk_client.nama", 'moodboard.*')
                ->where("moodboard.produk_client_id", $id)
                ->whereIn("moodboard.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'moodboard'))
                ->first();
                
        $alat = DB::table('alat_branding')
                ->join('produk_client', 'produk_client.id', '=', 'alat_branding.produk_client_id')
                ->select("produk_client.nama", 'alat_branding.*')
                ->where("alat_branding.produk_client_id", $id)
                ->whereIn("alat_branding.platform", $mirroredPlatforms)
                ->tap(fn ($query) => $withoutMirrorCopies($query, 'alat_branding'))
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
