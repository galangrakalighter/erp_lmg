<style>
    .collapse-container{
        padding: 0 .75rem;
    }

    .client-item-wrapper{
        border-radius: .35rem;
        overflow: hidden;
    }

    .client-dropdown-link{
        display: flex;
        align-items: center;
        gap: .75rem;
        width: 100%;
        padding: .7rem .9rem;
        color: rgba(255,255,255,.85);
        text-decoration: none;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: .35rem;
        transition: all .2s ease;
    }

    .client-dropdown-link{
        display:flex;
        justify-content:space-between;
        align-items:center;
    }

    .client-dropdown-link:hover{
        color: #fff;
        background: rgba(255,255,255,.14);
        border-color: rgba(255,255,255,.25);
        text-decoration: none;
    }

    .client-dropdown-link.active{
        color: #4e73df;
        background: #fff;
        font-weight: 600;
        border-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
    }

    .client-dropdown-link.active i{
        color: #4e73df;
    }

    /* Pemisah */

    .client-dropdown-link > div{
        display:flex;
        align-items:center;
    }

    .client-dropdown-link-out-div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 10px;
        color: rgba(255,255,255,0.85) !important;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none !important;
        cursor: pointer;
    }
    .client-dropdown-link-out-div:hover { color: #fff !important; }
    .client-dropdown-link-out-div.active{
        color:#ffffff;
        font-weight:700;
        border-left:4px solid #fdfdfd;
    }

    .active-menu{
        color:#ffffff;
        font-weight:700;
        border-left:4px solid #fdfdfd;
    }

    .platform-group-out-div {
        margin-bottom: 8px;
        background: rgba(0,0,0,0.1);
        padding: 5px;
        border-radius: 6px;
    }
    .platform-label-out-div {
        font-size: 0.65rem;
        font-weight: 800;
        color: #f6c23e;
        padding-left: 8px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }
    .platform-sub-links-out-div a {
        display: block;
        padding: 4px 10px 4px 20px;
        color: rgba(255,255,255,0.7) !important;
        font-size: 0.75rem;
        text-decoration: none !important;
    }
    .platform-sub-links-out-div a:hover { color: #fff !important; background: rgba(255,255,255,0.1); border-radius: 4px; }


    .dropdown-arrow{
        font-size:12px;
        transition:.25s;
    }

    .client-dropdown-link[aria-expanded="true"] .dropdown-arrow{
        transform:rotate(180deg);
    }

    /* submenu */

    .submenu-link{
        display:block;
        margin:6px 0 0 18px;
        padding:8px 12px;
        border-left:2px solid rgba(255,255,255,.2);
        color:rgba(255,255,255,.8);
        text-decoration:none;
        font-size:.88rem;
        transition:.2s;
    }

    .submenu-link:hover{
        color:#fff;
        border-left-color:#fff;
        padding-left:18px;
        text-decoration:none;
    }

    .submenu-link.active{
        color:#fff;
        font-weight:600;
        border-left-color:#fff;
    }

    .btn-cms-link {
        display: block;
        width: 100%;
        padding: 8px 12px;
        border-radius: 4px;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s ease-in-out;
    }

    .btn-cms-link-out-div {
        display: block;
        background: #f8f9fc;
        color: #4e73df !important;
        font-size: 0.65rem;
        font-weight: 800;
        text-align: center;
        padding: 6px;
        border-radius: 6px;
        text-transform: uppercase;
        text-decoration: none !important;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .btn-cms-link:hover {
        background-color: #e9ecef; /* Warna abu-abu standar Bootstrap */
        color: #000;
        text-decoration: none;
    }
    .category-dropdown-link {
        transition: background-color 0.2s ease-in-out;
        color: inherit; /* Menyesuaikan warna teks dengan tema sidebar */
    }

    .category-dropdown-link-out-div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 7px 10px;
        color: #fff !important;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-decoration: none !important;
        background: rgba(255,255,255,0.08);
        border-radius: 6px;
        cursor: pointer;
    }

    .category-dropdown-link-out-div:not(.collapsed) .arrow-icon-xs { transform: rotate(180deg); }

    .category-dropdown-link:hover {
        background-color: #e9ecef; /* Warna abu-abu saat di-hover */
        text-decoration: none;
    }

    .brand-sub-header {
        font-size: 0.7rem;
        font-weight: 900;
        background: white;
        padding: 4px;
        border-radius: 4px;
        text-align: center;
        letter-spacing: 1px;
    }
</style>
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Logo -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center py-3"
        href="{{ route('home') }}">

        <img src="{{ asset('img/logo.jpg') }}"
            class="rounded-circle shadow-sm mr-2"
            style="width:42px;height:42px;object-fit:cover;"
            alt="Logo">

        <div class="sidebar-brand-text">
            Lighter
        </div>

    </a>

    <hr class="sidebar-divider my-0">

    <!-- Dashboard -->
    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-fw fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    @php
        $currentProduct = request()->route('id_produk');
        $currentPlatform = request()->route('platform');
        
        // Identifikasi apakah produk saat ini ada di list Digital Marketing atau Business Dev
        $isDMProduct = $digitalMarketingProduk->contains('client_id', $currentProduct);
        $isBDProduct = $businessDevProduk->contains('client_id', $currentProduct);

        $isMarketingPage = Route::is(['market_research', 'branding', 'analisis', 'manajemen', 'PDF']);

        $divisiOpen = Route::is([
            'client.produk_divisi',
            'barang_client.index',
            'social_creator.index',
            'blacklist.index',
            'tools.index',
            'gform_affiliate.index',
            'selection.index',
        ])
    @endphp

    <!-- Nav Item: Divisi -->
    <li class="nav-item {{ $divisiOpen || ($isMarketingPage && $isDMProduct) ? 'active' : '' }}">
        <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseDivisi" aria-expanded="true">
            <span>Divisi</span>
        </a>

        <div id="collapseDivisi" class="collapse {{ $divisiOpen || ($isMarketingPage && $isDMProduct) ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                
                @include('partials.sidebar_digital_marketing')
                
                @include('partials.sidebar_mcn')
                
                @include('partials.sidebar_tech')
                
                @include('partials.sidebar_commerce')

            </div>
        </div>
    </li>

    @if(request()->routeIs('cms*'))
        <div class="sidebar-heading mt-3 text-warning">CMS MANAGEMENT</div>
        <div class="cms-nav-group mx-2 mb-3">
            @php
                $cmsMenus = [
                    ['route' => 'cms.produk', 'icon' => 'fa-box', 'label' => 'Produk'],
                    ['route' => 'cms.hook', 'icon' => 'fa-anchor', 'label' => 'Hook'],
                    ['route' => 'cms.strategy', 'icon' => 'fa-chess', 'label' => 'Strategy'],
                    ['route' => 'cms.status', 'icon' => 'fa-info-circle', 'label' => 'Status'],
                    ['route' => 'cms.type', 'icon' => 'fa-tags', 'label' => 'Type'],
                    ['route' => 'cms.pilar', 'icon' => 'fa-columns', 'label' => 'Pilar'],
                    ['route' => 'cms.body', 'icon' => 'fa-paragraph', 'label' => 'Body'],
                    ['route' => 'cms.cta', 'icon' => 'fa-mouse-pointer', 'label' => 'CTA'],
                ];
            @endphp
            @foreach($cmsMenus as $item)
            <li class="nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                <a class="nav-link py-2 px-3" href="{{ route($item['route'], $activeProductCode) }}">
                    <i class="fas fa-fw {{ $item['icon'] }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
            @endforeach
        </div>
        <hr class="sidebar-divider">
        <li class="nav-item">
            <a class="nav-link text-warning font-weight-bold" href="{{ route('home') }}">
                <i class="fas fa-fw fa-arrow-left"></i><span>Kembali</span>
            </a>
        </li>
    @endif

    <li class="nav-item {{ ($isMarketingPage && $isBDProduct) ? 'active' : '' }}">
        <a class="nav-link {{ ($isMarketingPage && $isBDProduct) ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseBD" aria-expanded="{{ ($isMarketingPage && $isBDProduct) ? 'true' : 'false' }}" aria-controls="collapseBD">
            <span>Business Dev</span>
        </a>
        
        <div id="collapseBD" class="collapse {{ ($isMarketingPage && $isBDProduct) ? 'show' : '' }}" 
            aria-labelledby="headingBD" data-parent="#accordionSidebar">
            <div class="collapse-container">

                <div class="brand-sub-header text-primary mb-3">
                    <i class="fas fa-store mr-2"></i> MR. CREPE
                </div>
                

                @if ($isSuperAdmin || $myDivision->name == "Business Dev" && $myRole->level < 4)
                    <div class="px-2 mb-3">
                        <a class="btn-cms-link-out-div" href="{{ route('cms.produk', 'BD') }}">
                            <i class="fas fa-cog mr-1"></i> Dashboard CMS
                        </a>
                    </div>
                @endif

                @php
                    // Persiapan data di atas loop agar tidak membebani performa
                    $isPowerUser = $isSuperAdmin || ($myRole->role_name ?? '') == "Performance & Reporting";
                    $divisionIds = $myDivision instanceof \Illuminate\Support\Collection ? $myDivision->pluck('id')->toArray() : [];
                    $isDigitalMarketing = ($myDivision->name ?? '') == "Business Dev";
                    if($myDivision->name == 'All Divisions'){
                        $isDigitalMarketing = true;
                    }
                    
                    // Kelompokkan data produk berdasarkan kategori untuk meminimalisir loop
                    $groupedProduk = $businessDevProduk->groupBy('kategori_jasa_id');
                    
                    // Fungsi bantuan untuk cek akses
                    $checkAccess = function($client) use ($isPowerUser, $divisionIds, $myClientIds) {
                        return $isPowerUser || (in_array(1, $divisionIds) && in_array($client->client_id, $myClientIds));
                    };
                @endphp

                @foreach([2 => 'Jasa', 3 => 'Barang'] as $catId => $catLabel)
                    <a class="category-dropdown-link-out-div collapsed mb-3 d-block" data-toggle="collapse" data-target="#cat_bd_{{ strtolower($catLabel) }}">
                        <span><i class="fas fa-folder-open mr-2 text-info"></i>Internal {{ $catLabel }}</span>
                    </a>

                    <div id="cat_bd_{{ strtolower($catLabel) }}" class="collapse {{ ($isMarketingPage) ? 'show' : '' }} ml-2 border-left-custom mb-2">
                        @php
                            // Filter data yang hanya boleh dilihat user ini
                            $filteredClients = $groupedProduk->get($catId, collect())->filter(function($client) use ($isDigitalMarketing, $isSuperAdmin) {
                                return $isDigitalMarketing || $isSuperAdmin;
                            });
                        @endphp

                        @forelse ($filteredClients as $client)
                            @php 
                                $canOpen = $checkAccess($client); 
                                $isActiveClient = $currentProduct == $client->client_id;
                            @endphp

                            <div class="client-item-wrapper" style="width: 7vw">
                                @if($canOpen)
                                    <a class="client-dropdown-link-out-div {{ $isActiveClient ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#client_bd_{{ $catLabel }}_{{ $client->client_id }}" aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                        <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                        <i class="fas fa-caret-right arrow-icon-xs"></i>
                                    </a>
                                    <div id="client_bd_{{ $catLabel }}_{{ $client->client_id }}" class="collapse ml-3 {{ $isActiveClient ? 'show' : '' }}">
                                        <div class="platform-sub-links-out-div">
                                            @php $platforms = collect(json_decode($client->access_token, true)['platform'] ?? [])->filter(); @endphp
                                            @forelse ($platforms as $plt)
                                                @php
                                                    $isPlatform = $currentPlatform == $plt;
                                                @endphp
                                                <div class="platform-group-out-div">
                                                    <div class="platform-label-out-div"><i class="fab fa-{{ strtolower($plt) }}"></i> {{ $plt }}</div>
                                                    <a href="{{ route('market_research', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="{{ Route::is('market_research') && $isPlatform && $isActiveClient ? 'active-menu' : '' }}">Market Research</a>
                                                    <a href="{{ route('branding', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="{{ Route::is('branding') && $isPlatform && $isActiveClient ? 'active-menu' : '' }}">Branding</a>
                                                    <a href="{{ route('analisis', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="{{ Route::is('analisis') && $isPlatform && $isActiveClient ? 'active-menu' : '' }}">Analisis</a>
                                                    <a href="{{ route('manajemen', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="{{ Route::is('manajemen') && $isPlatform && $isActiveClient ? 'active-menu' : '' }}">Content Plan</a>
                                                    <a href="{{ route('PDF', ['id_produk' => $client->id]) }}" class="{{ Route::is('PDF') && $isActiveClient ? 'active-menu' : '' }}">File</a>
                                                </div>
                                            @empty
                                                <span class="text-muted small p-2 d-block">Platform belum diset</span>
                                            @endforelse
                                        </div>
                                    </div>
                                @else
                                    <div class="p-1 text-muted" style="opacity: 0.6; cursor: not-allowed;">
                                        <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-2 text-muted small font-italic">
                                <i class="fas fa-info-circle mr-2"></i>Tidak ada data client tersedia.
                            </div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    </li>

    @php
        // Definisikan route yang termasuk dalam menu Human Capital
        $isHCPage = Route::is([
            'hc_daily.index',
            'hc_overview.list',
            'overview.dashboard_kpi',
            'overview.dashboard'
        ]);
    @endphp
    <li class="nav-item {{ $isHCPage ? 'active' : '' }}">
        <a class="nav-link {{ $isHCPage ? '' : 'collapsed' }}" href="#" 
        data-toggle="collapse" data-target="#collapseHC" 
        aria-expanded="{{ $isHCPage ? 'true' : 'false' }}" aria-controls="collapseHC">
            <span>Human Capital</span>
        </a>
        
        @php
            $isKPIActive = Route::is(['overview.dashboard_kpi', 'hc_overview.list']);
            $isHarianActive = Route::is(['overview.dashboard', 'hc_daily.index']);
        @endphp

        <div id="collapseHC" class="collapse {{ $isHCPage ? 'show' : '' }}" 
            aria-labelledby="headingHC" data-parent="#accordionSidebar">
            <div class="collapse-container-class-outdiv">
                
                {{-- Dropdown KPI (Berisi KPI & Project Overview) --}}
                <div class="client-item-wrapper-class-outdiv mb-2">
                     <a class="category-dropdown-link-out-div collapsed mb-3 d-block" data-toggle="collapse" data-target="#collapseSubKPI">
                        <span>KPI</span>
                    </a>
                    
                    <div id="collapseSubKPI" class="collapse ml-3 {{ $isKPIActive ? 'show' : '' }}">
                        <div class="platform-sub-links">
                            <div class="platform-group">
                                <a href="{{ route('overview.dashboard_kpi') }}" class="client-dropdown-link-out-div {{ Route::is('overview.dashboard_kpi') ? 'active-menu' : '' }}">Dashboard</a>
                                <a href="{{ route('hc_overview.list') }}" class="client-dropdown-link-out-div {{ Route::is('hc_overview.list') ? 'active-menu' : '' }}">Overview</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dropdown Harian (Berisi Dashboard & Daily Work) --}}
                <div class="client-item-wrapper-class-outdiv">
                    <a class="category-dropdown-link-out-div collapsed mb-3 d-block" data-toggle="collapse" data-target="#collapseSubHarian">
                        <span>Harian</span>
                    </a>
                    
                    <div id="collapseSubHarian" class="collapse ml-3 {{ $isHarianActive ? 'show' : '' }}">
                        <div class="platform-sub-links">
                            <div class="platform-group">
                                <a href="{{ route('overview.dashboard') }}" class="client-dropdown-link-out-div {{ Route::is('overview.dashboard') ? 'active-menu' : '' }}">Dashboard</a>
                                <a href="{{ route('hc_daily.index') }}" class="client-dropdown-link-out-div {{ Route::is('hc_daily.index') ? 'active-menu' : '' }}">Daily Work</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </li>

    @if(Auth::user()->isAdmin == true || $myDivision->name == 'Finance')
        @php
            $isFinancePage = Route::is([
                'finance_coa.index',
                'general_journal.index',
                'finance_ledger.list_account',
                'Ledger.neraca_saldo',
                'adjustment_journal.index',
                'Ledger.neraca_lajur',
                'Ledger.arus_kas',
                'Ledger.jurnal_penutup',
                'invoice.index',
                'profit.list',
                'payroll.index',
                'audit.index'
            ]);
        @endphp
        <li class="nav-item {{ $isFinancePage ? 'active' : '' }}">
            <a class="nav-link {{ $isFinancePage ? '' : 'collapsed' }}" href="#" 
            data-toggle="collapse" data-target="#collapseFinance" 
            aria-expanded="{{ $isFinancePage ? 'true' : 'false' }}" aria-controls="collapseFinance">
                <span>Finance</span>
            </a>
            
            <div id="collapseFinance" class="collapse {{ $isFinancePage ? 'show' : '' }}" 
                aria-labelledby="headingHC" data-parent="#accordionSidebar">
                <div class="collapse-container">
                    @if ($isSuperAdmin || ($myDivision->name == "Finance" && $myRole->level < 2))
                        <div class="mb-2">
                            <a href="{{ route('client.produk_divisi', 6) }}" class="btn-cms-link-out-div d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                                <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                            </a>
                        </div>
                    @endif
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('finance.dashboard') ? 'active' : '' }}" href="{{ route('finance.dashboard') }}">
                            <span>Dashboard</span>
                        </a>
                    </div>
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('finance_coa.index') ? 'active' : '' }}" href="{{ route('finance_coa.index') }}">
                            <span>Daftar Akun</span>
                        </a>
                    </div>
                    
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('general_journal.index') ? 'active' : '' }}" href="{{ route('general_journal.index') }}">
                            <span>Jurnal Umum</span>
                        </a>
                    </div>
                    
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('finance_ledger.list_account') ? 'active' : '' }}" href="{{ route('finance_ledger.list_account') }}">
                            <span>Buku Besar</span>
                        </a>
                    </div>
                    
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('Ledger.neraca_saldo') ? 'active' : '' }}" href="{{ route('Ledger.neraca_saldo') }}">
                            <span>Neraca Saldo</span>
                        </a>
                    </div>
                    
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('adjustment_journal.index') ? 'active' : '' }}" href="{{ route('adjustment_journal.index') }}">
                            <span>Jurnal Penyesuaian</span>
                        </a>
                    </div>

                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('Ledger.neraca_lajur') ? 'active' : '' }}" href="{{ route('Ledger.neraca_lajur') }}">
                            <span>Neraca Lajur</span>
                        </a>
                    </div>
                    
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('Ledger.arus_kas') ? 'active' : '' }}" href="{{ route('Ledger.arus_kas') }}">
                            <span>Laporan Arus Kas</span>
                        </a>
                    </div>
                    
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('Ledger.jurnal_penutup') ? 'active' : '' }}" href="{{ route('Ledger.jurnal_penutup') }}">
                            <span>Jurnal Penutup</span>
                        </a>
                    </div>
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('invoice.index') ? 'active' : '' }}" href="{{ route('invoice.index') }}">
                            <span>Pembayaran</span>
                        </a>
                    </div>
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('profit.list') ? 'active' : '' }}" href="{{ route('profit.list') }}">
                            <span>Profit</span>
                        </a>
                    </div>
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('payroll.index') ? 'active' : '' }}" href="{{ route('payroll.index') }}">
                            <span>Payroll</span>
                        </a>
                    </div>
                    @if(Auth::user()->isAdmin == true)
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link-out-div {{ Route::is('audit.index') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                            <span>Audit</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </li>
    @endif

    @if($isSuperAdmin)
        <li class="nav-item {{ Route::is('manajemen_user.index') ? 'active' : '' }}">
            <a class="nav-link collapsed" href="{{ route('manajemen_user.index') }}">
                <span>Manajemen User</span>
            </a>
        </li>
    @endif

    <li class="nav-item {{ Route::is('crm.index') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="{{ route('crm.index') }}">
            <span>Customer Relationship Management</span>
        </a>
    </li>

</ul>