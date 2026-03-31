<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
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
            
            $mcnClient = DB::table('barang_client')->get();
            
            $view->with([
                'menus' => KategoriJasa::with('clients')->get(),
                'activeProductCode' => $currentProductCode,
                'mcnClient' => $mcnClient
            ]);
        });
    }
}
