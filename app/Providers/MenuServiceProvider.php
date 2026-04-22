<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\KategoriJasa;
use Illuminate\Support\Facades\Request;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $segment = Request::segment(1); 
            $currentProductCode = str_contains($segment, 'cms_') 
                                    ? str_replace('cms_', '', $segment) 
                                    : null;
            
            // Inisialisasi default agar View tidak error saat variabel dipanggil
            $isSuperAdmin = false;
            $myDivisions = collect(); // Koleksi kosong
            $user = Auth::user();
            $myRole = collect();
            $myClientIds = [];

            // Jalankan logika hanya jika user sudah terautentikasi
            // if ($user) {
            //     $isSuperAdmin = $user->isAdmin === 't';
                
            //     // Eager load relasi
            //     $user->load(['userDivisions.division', 'userDivisions.role']);
            //     $division = $user->userDivisions;
            //     $myDivisions = $division->first()->division;
            //     $myRole = $division->first()->role;
            //     $myClientIds = $user->userClients->pluck('client_id')->toArray();
            // }

            // Data yang bersifat umum (bisa diakses tanpa login)
            // $mcnClient = DB::table('barang_client')->get();
            $menus = KategoriJasa::with('clients')->get();

            $view->with([
                'menus' => $menus,
                'activeProductCode' => $currentProductCode,
                // 'mcnClient' => $mcnClient,
                // 'isSuperAdmin' => $isSuperAdmin,
                // 'myDivision' => $myDivisions,
                // 'myRoles' => $myRole,
                // 'myClientIds' => $myClientIds
            ]);
        });
    }
}
