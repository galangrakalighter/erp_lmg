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
            $myDivisionIds = [];
            $user = Auth::user();
            $canManageFinanceCompanies = $user
                && $user->isAdmin
                && strtolower(trim((string) $user->jabatan)) === 'cfo';

            if ($user) {
                $isSuperAdmin = $user->isAdmin == true;
                // Eager load relasi untuk menghindari N+1
                $user->load(['userDivisions.division', 'userDivisions.role', 'userClients']);
                
                $userDivisions = $user->userDivisions;
                $myDivisionIds = $userDivisions
                    ->pluck('division_id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

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
            $multimediaDivisionId = DB::table('divisions')
                ->whereRaw('LOWER(TRIM(name)) = ?', ['multimedia'])
                ->value('id');
            $sidebarDivisionIds = array_values(array_filter([1, 2, 3, 5, 7, $multimediaDivisionId]));
            $visibleSidebarDivisionIds = $isSuperAdmin
                ? $sidebarDivisionIds
                : array_values(array_intersect($sidebarDivisionIds, $myDivisionIds));
            
            // Satu query untuk seluruh client sidebar, lalu kelompokkan di memori.
            $sidebarProducts = DB::table('client_division')
                ->join('produk_client', 'produk_client.id', '=', 'client_division.client_id')
                ->leftJoin('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
                ->whereIn('client_division.division_id', $visibleSidebarDivisionIds)
                ->where('client_division.status', 1)
                // Sidebar dan IDME menggunakan relasi client_division yang sama.
                // Keberadaan PIC tidak menentukan apakah client tercatat pada
                // sebuah divisi; PIC hanya menentukan hak buka untuk user biasa.
                ->select(
                    'client_division.division_id',
                    'client_division.client_id',
                    'produk_client.*',
                    'kategori_jasa.category as kategori_jasa_category'
                )
                ->orderBy('produk_client.nama')
                ->get();

            $internalClientIds = $sidebarProducts
                ->filter(function ($client) {
                    $name = strtolower(trim((string) $client->nama));
                    $category = strtolower(trim((string) ($client->kategori_jasa_category ?? '')));

                    return $name === 'internal' || str_starts_with($category, 'internal');
                })
                ->pluck('client_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (! $isSuperAdmin) {
                // User biasa hanya melihat client yang ditangani. INTERNAL selalu
                // tersedia sebagai workspace bersama walaupun tidak menjadi PIC.
                $myClientIds = array_values(array_unique(array_merge($myClientIds, $internalClientIds)));
                $sidebarProducts = $sidebarProducts
                    ->whereIn('client_id', $myClientIds)
                    ->values();
            }

            $sidebarProducts = $sidebarProducts->groupBy('division_id');

            $digitalMarketingProduk = $sidebarProducts->get(1, collect());
            if ($user && ! $isSuperAdmin) {
                $digitalMarketingAssignment = $user->userDivisions->firstWhere('division_id', 1);
                if ($digitalMarketingAssignment && $digitalMarketingAssignment->role?->role_name !== 'Busines Unit Head') {
                    $digitalMarketingProduk = $digitalMarketingProduk
                        ->whereIn('client_id', $myClientIds)->values();
                }
            }
            $businessDevProduk = $sidebarProducts->get(2, collect());
            $mcnProduk = $sidebarProducts->get(3, collect());
            $techProduk = $sidebarProducts->get(5, collect());
            $ecommerceProduk = $sidebarProducts->get(7, collect());
            $multimediaProduk = $sidebarProducts->get($multimediaDivisionId, collect());
            $idmeClients = DB::table('produk_client')
                ->join('client_division', 'client_division.client_id', '=', 'produk_client.id')
                ->join('divisions', 'divisions.id', '=', 'client_division.division_id')
                ->leftJoin('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
                ->whereRaw("LOWER(TRIM(divisions.name)) <> 'finance'")
                ->select('produk_client.*')
                ->groupBy('produk_client.id')
                ->havingRaw('COUNT(DISTINCT client_division.division_id) > 1')
                ->orderBy('produk_client.nama')
                ->get();

            $perusahaanFinance = DB::table('perusahaan as p')
                ->where('p.status', true)
                ->when($user && ! $isSuperAdmin, function ($query) use ($user) {
                    $query->whereExists(function ($subQuery) use ($user) {
                        $subQuery->selectRaw('1')
                            ->from('perusahaan_user as pu')
                            ->whereColumn('pu.perusahaan_id', 'p.id')
                            ->where('pu.user_id', $user->id);
                    });
                })
                ->orderBy('p.nama_perusahaan')
                ->get();

            $view->with([
                'businessDevProduk' => $businessDevProduk,
                'digitalMarketingProduk' => $digitalMarketingProduk,
                'activeProductCode' => $currentProductCode,
                'mcnClient' => $mcnClient,
                'isSuperAdmin' => $isSuperAdmin,
                'myDivision' => $myDivision, // Mengirim object (bukan collection)
                'myRole' => $myRole,         // Mengirim object (bukan collection)
                'myClientIds' => $myClientIds,
                'myDivisionIds' => $myDivisionIds,
                'mcnProduk' => $mcnProduk,
                'techProduk' => $techProduk,
                'ecommerceProduk' => $ecommerceProduk,
                'multimediaProduk' => $multimediaProduk,
                'multimediaDivisionId' => $multimediaDivisionId,
                'idmeClients' => $idmeClients,
                'perusahaan' => $perusahaanFinance,
                'canManageFinanceCompanies' => $canManageFinanceCompanies,
                'auth' => $user
            ]);
        });
    }
}
