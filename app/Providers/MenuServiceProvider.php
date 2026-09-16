<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        // Data menu hanya dibutuhkan layout aplikasi. Halaman login dan view
        // mandiri tidak perlu membayar biaya seluruh query sidebar.
        View::composer('layout.app', function ($view) {
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
                $isSuperAdmin = $user->isAdmin == true;
                // Eager load relasi untuk menghindari N+1
                $user->load(['userDivisions.division', 'userDivisions.role', 'userClients']);
                
                $userDivisions = $user->userDivisions;

                // 2. Jika Super Admin, berikan identitas khusus
                if ($isSuperAdmin) {
                    $myDivision = (object) ['name' => 'All Divisions', 'id' => 0];
                    $myRole = (object) ['role_name' => 'Super Admin', 'id' => 0];
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
            
            // Satu query untuk seluruh client sidebar, lalu kelompokkan di memori.
            $sidebarProducts = DB::table('client_division')
                ->join('produk_client', 'produk_client.id', '=', 'client_division.client_id')
                ->whereIn('client_division.division_id', [1, 2, 3, 5, 7])
                ->where('produk_client.status', 1)
                ->select('client_division.division_id', 'client_division.client_id', 'produk_client.*')
                ->orderBy('produk_client.nama')
                ->get()
                ->groupBy('division_id');

            $digitalMarketingProduk = $sidebarProducts->get(1, collect());
            $businessDevProduk = $sidebarProducts->get(2, collect());
            $mcnProduk = $sidebarProducts->get(3, collect());
            $techProduk = $sidebarProducts->get(5, collect());
            $ecommerceProduk = $sidebarProducts->get(7, collect());
            $idmeClients = DB::table('produk_client')
                ->where('status', 1)
                ->orderBy('nama')
                ->get();

            $perusahaanFinance = DB::table('perusahaan')->where('status', true)->get();

            $view->with([
                'businessDevProduk' => $businessDevProduk,
                'digitalMarketingProduk' => $digitalMarketingProduk,
                'activeProductCode' => $currentProductCode,
                'mcnClient' => $mcnClient,
                'isSuperAdmin' => $isSuperAdmin,
                'myDivision' => $myDivision, // Mengirim object (bukan collection)
                'myRole' => $myRole,         // Mengirim object (bukan collection)
                'myClientIds' => $myClientIds,
                'mcnProduk' => $mcnProduk,
                'techProduk' => $techProduk,
                'ecommerceProduk' => $ecommerceProduk,
                'idmeClients' => $idmeClients,
                'perusahaan' => $perusahaanFinance,
                'auth' => $user
            ]);
        });
    }
}
