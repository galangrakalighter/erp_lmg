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
            
            // 1. Inisialisasi default dengan objek kosong agar Blade tidak crash
            $isSuperAdmin = false;
            $myDivision = (object) ['name' => '-', 'id' => null];
            $myRole = (object) ['name' => '-', 'id' => null];
            $myClientIds = [];
            $user = Auth::user();

            if ($user) {
                $isSuperAdmin = $user->isAdmin == 'true';
                // Eager load relasi untuk menghindari N+1
                $user->load(['userDivisions.division', 'userDivisions.role', 'userClients']);
                
                $userDivisions = $user->userDivisions;

                // 2. Jika Super Admin, berikan identitas khusus
                if ($isSuperAdmin) {
                    $myDivision = (object) ['name' => 'All Divisions', 'id' => 0];
                    $myRole = (object) ['name' => 'Super Admin', 'id' => 0];
                    $myClientIds = $user->userClients->pluck('client_id')->toArray();
                } 
                // 3. Jika User biasa, cek apakah punya data di user_division
                elseif ($userDivisions->isNotEmpty()) {
                    $firstDivision = $userDivisions->first();
                    $myDivision = $firstDivision->division; // Mengambil object division
                    $myRole = $firstDivision->role;         // Mengambil object role
                    $myClientIds = $user->userClients->pluck('client_id')->toArray();
                }
            }

            // Data umum
            $mcnClient = DB::table('barang_client')->get();
            $menus = KategoriJasa::with('clients')->get();

            $view->with([
                'menus' => $menus,
                'activeProductCode' => $currentProductCode,
                'mcnClient' => $mcnClient,
                'isSuperAdmin' => $isSuperAdmin,
                'myDivision' => $myDivision, // Mengirim object (bukan collection)
                'myRole' => $myRole,         // Mengirim object (bukan collection)
                'myClientIds' => $myClientIds
            ]);
        });
    }
}
