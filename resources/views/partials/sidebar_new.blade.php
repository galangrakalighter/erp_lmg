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

    .btn-cms-link:hover {
        background-color: #e9ecef; /* Warna abu-abu standar Bootstrap */
        color: #000;
        text-decoration: none;
    }
    .category-dropdown-link {
        transition: background-color 0.2s ease-in-out;
        color: inherit; /* Menyesuaikan warna teks dengan tema sidebar */
    }

    .category-dropdown-link:hover {
        background-color: #e9ecef; /* Warna abu-abu saat di-hover */
        text-decoration: none;
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

    <!-- Nav Item: Divisi -->
    <li class="nav-item {{ !Route::is('home') && !Route::is('manajemen_user.index') && !Route::is('crm.index') ? 'active' : '' }}">
        <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseDivisi" aria-expanded="true">
            <span>Divisi</span>
        </a>

        @php
            $currentProduct = request()->route('id_produk');
            $currentPlatform = request()->route('platform');
            
            // Identifikasi apakah produk saat ini ada di list Digital Marketing atau Business Dev
            $isDMProduct = $digitalMarketingProduk->contains('client_id', $currentProduct);
            $isBDProduct = $businessDevProduk->contains('client_id', $currentProduct);

            $isMarketingPage = Route::is(['market_research', 'branding', 'analisis', 'manajemen', 'PDF']);
        @endphp

        <div id="collapseDivisi" class="collapse {{ !Route::is('home') && !Route::is('manajemen_user.index') && !Route::is('crm.index') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                
                <!-- 2. Sub-Menu: DIGITAL MARKETING (Dropdown) -->
                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseDM">
                    Digital Marketing
                </a>
                
                <div id="collapseDM" class="collapse {{ ($isMarketingPage && $isDMProduct) ? 'show' : '' }}" 
                    aria-labelledby="headingDM" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2">

                        
                        @if ($isSuperAdmin || ($myDivision->name == "Digital Marketing" && $myRole->level < 3))
                            <div class="brand-sub-header text-danger mb-2 px-2">
                                <i class="fas fa-store mr-2"></i> MR. CREPE
                            </div>
                            <div class="mb-2">
                                <a href="{{ route('cms.produk', 'LGTR') }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                                </a>
                            </div>
                        
                            
                            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover {{ !$isMarketingPage ? 'collapsed' : '' }}" 
                                data-toggle="collapse" 
                                data-target="#cat_dm" 
                            aria-expanded="{{ $isMarketingPage ? 'true' : 'false' }}">
                                <span><i class="fas fa-folder-open mr-2 text-warning"></i>Client</span>
                                <i class="fas fa-caret-down arrow-icon-xs"></i>
                            </a>
                        @endif

                        <div id="cat_dm" class="collapse {{ $isMarketingPage ? 'show' : '' }} ml-2 border-left-custom">
                            @foreach ($digitalMarketingProduk as $client)
                                @if($myDivision->name == "Digital Marketing" || $isSuperAdmin)
                                    @php
                                        $hasClientAccess = in_array($client->client_id, $myClientIds);
                                        $isPowerUser = $isSuperAdmin || $myRole->role_name == "Performance & Reporting";
                                        $canOpen = $isPowerUser || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                                        $isActiveClient = $currentProduct == $client->client_id;
                                    @endphp

                                    <div class="client-item-wrapper mb-1">
                                        @if($canOpen)
                                            <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }}" 
                                            data-toggle="collapse" 
                                            data-target="#client_lgtr_{{ $client->client_id }}" 
                                            aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                                <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                                <i class="fas fa-caret-right arrow-icon-xs"></i>
                                            </a>

                                            <div id="client_lgtr_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                                <div class="platform-sub-links pl-3 pt-1 pb-2">
                                                    @php
                                                        $accessToken = json_decode($client->access_token, true);
                                                        $platforms = collect($accessToken['platform'] ?? [])->filter();
                                                    @endphp

                                                    @forelse ($platforms as $plt)
                                                        <div class="platform-group mb-2">
                                                            <div class="platform-label small font-weight-bold text-dark px-2 py-1">
                                                                <i class="fab fa-{{ strtolower($plt) }} mr-1"></i> {{ $plt }}
                                                            </div>
                                                            <div class="d-flex flex-column pl-2">
                                                                <a href="{{ route('market_research', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('market_research') && $currentPlatform == $plt && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Market Research</a>
                                                                <a href="{{ route('branding', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('branding') && $currentPlatform == $plt && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Branding</a>
                                                                <a href="{{ route('analisis', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('analisis') && $currentPlatform == $plt && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Analisis</a>
                                                                <a href="{{ route('manajemen', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('manajemen') && $currentPlatform == $plt && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Content Plan</a>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <span class="text-muted small p-2 d-block font-italic">Platform belum diset</span>
                                                    @endforelse
                                                    
                                                    <a href="{{ route('PDF', ['id_produk'=>$client->id]) }}" class="mt-2 d-flex align-items-center text-dark p-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">
                                                        <i class="fas fa-file-pdf mr-2 text-danger"></i> <span>File Dokumen</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
                                                <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <style>
                            .transition-hover {
                                transition: background-color 0.2s ease-in-out;
                            }
                            .transition-hover:hover {
                                background-color: #f8f9fc !important;
                                color: #4e73df !important;
                            }
                        </style>
                    </div>
                </div>

                <!-- 3. Sub-Menu: BUSINESS DEV (Dropdown) -->
                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseBD">
                    Business Dev
                </a>
                
                <div id="collapseBD" class="collapse {{ ($isMarketingPage && $isBDProduct) ? 'show' : '' }}" aria-labelledby="headingBD" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2">
                        
                        @if ($isSuperAdmin || ($myDivision->name == "Business Dev" && $myRole->level < 3))
                            <div class="brand-sub-header text-danger mb-2 px-2">
                                <i class="fas fa-store mr-2"></i> MR. CREPE
                            </div>
                            <div class="mb-2">
                                <a href="{{ route('cms.produk', 'BD') }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                                </a>
                            </div>
                        @endif

                        @php
                            // Persiapan data di atas loop agar tidak membebani performa
                            $isPowerUser = $isSuperAdmin || ($myRole->role_name ?? '') == "Performance & Reporting";
                            $divisionIds = $myDivision instanceof \Illuminate\Support\Collection ? $myDivision->pluck('id')->toArray() : [];
                            $isBusinessDev = ($myDivision->name ?? '') == "Business Dev";
                            if($myDivision->name == 'All Divisions'){
                                $isBusinessDev = true;
                            }
                            
                            // Kelompokkan data produk berdasarkan kategori untuk meminimalisir loop
                            $groupedProduk = $businessDevProduk->groupBy('kategori_jasa_id');
                            
                            // Fungsi bantuan untuk cek akses
                            $checkAccess = function($client) use ($isPowerUser, $divisionIds, $myClientIds) {
                                return $isPowerUser || (in_array(1, $divisionIds) && in_array($client->client_id, $myClientIds));
                            };
                        @endphp

                        @foreach([2 => 'Jasa', 3 => 'Barang'] as $catId => $catLabel)
                            @if ($isSuperAdmin || $myDivision->name == "Business Dev")
                            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2 {{ !$isMarketingPage ? 'collapsed' : '' }}" 
                            data-toggle="collapse" 
                            data-target="#cat_bd_{{ strtolower($catLabel) }}">
                                <span><i class="fas fa-folder-open mr-2 text-info"></i>Internal {{ $catLabel }}</span>
                                <i class="fas fa-caret-down arrow-icon-xs"></i>
                            </a>
                            @endif

                            <div id="cat_bd_{{ strtolower($catLabel) }}" class="collapse {{ ($isMarketingPage) ? 'show' : '' }} ml-2 border-left-custom mb-2">
                                @php
                                    // Filter data yang hanya boleh dilihat user ini
                                    $filteredClients = $groupedProduk->get($catId, collect())->filter(function($client) use ($isBusinessDev, $isSuperAdmin) {
                                        return $isBusinessDev || $isSuperAdmin;
                                    });
                                @endphp

                                @forelse ($filteredClients as $client)
                                    @php 
                                        $canOpen = $checkAccess($client); 
                                        $isActiveClient = $currentProduct == $client->client_id;
                                    @endphp

                                    <div class="client-item-wrapper mb-1">
                                        @if($canOpen)
                                            <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }}" 
                                            data-toggle="collapse" 
                                            data-target="#client_bd_{{ $catLabel }}_{{ $client->client_id }}" 
                                            aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                                <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                                <i class="fas fa-caret-right arrow-icon-xs"></i>
                                            </a>

                                            <div id="client_bd_{{ $catLabel }}_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                                <div class="platform-sub-links pl-3 pt-1 pb-2">
                                                    @php 
                                                        $accessToken = json_decode($client->access_token, true);
                                                        $platforms = collect($accessToken['platform'] ?? [])->filter(); 
                                                    @endphp

                                                    @forelse ($platforms as $plt)
                                                        @php
                                                            $isPlatform = $currentPlatform == $plt;
                                                        @endphp
                                                        <div class="platform-group mb-2">
                                                            <div class="platform-label small font-weight-bold text-dark px-2 py-1">
                                                                <i class="fab fa-{{ strtolower($plt) }} mr-1"></i> {{ $plt }}
                                                            </div>
                                                            <div class="d-flex flex-column pl-2">
                                                                <a href="{{ route('market_research', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('market_research') && $isPlatform && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Market Research</a>
                                                                <a href="{{ route('branding', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('branding') && $isPlatform && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Branding</a>
                                                                <a href="{{ route('analisis', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('analisis') && $isPlatform && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Analisis</a>
                                                                <a href="{{ route('manajemen', ['id_produk' => $client->id, 'platform' => $plt]) }}" class="text-dark d-block py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('manajemen') && $isPlatform && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">Content Plan</a>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <span class="text-muted small p-2 d-block font-italic">Platform belum diset</span>
                                                    @endforelse

                                                    <a href="{{ route('PDF', ['id_produk' => $client->id]) }}" class="mt-2 d-flex align-items-center text-dark p-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">
                                                        <i class="fas fa-file-pdf mr-2 text-danger"></i> <span>File Dokumen</span>
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
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

                @php
                    // Definisikan route yang termasuk dalam menu Human Capital
                    $isHCPage = Route::is([
                        'hc_daily.index',
                        'hc_overview.list',
                        'overview.dashboard_kpi',
                        'overview.dashboard'
                    ]);
                @endphp

                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseHC">
                    Human Capital
                </a>

                <div id="collapseHC" class="collapse {{ $isHCPage ? 'show' : '' }}" 
                    aria-labelledby="headingHC" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2 py-2">
                        {{-- Menu Daily Work --}}
                        <div class="client-item-wrapper mb-2">
                            <a href="{{ route('hc_daily.index') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('hc_daily.index') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                <i class="fas fa-calendar-day mr-2 text-warning"></i> <span>Daily Work</span>
                            </a>
                        </div>

                        <div class="client-item-wrapper mb-2">
                            <a href="{{ route('hc_overview.list') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('hc_overview.list') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                <i class="fas fa-project-diagram mr-2 text-info"></i> <span>Project Overview</span>
                            </a>
                        </div>

                        <div class="client-item-wrapper mb-2">
                            <a href="{{ route('overview.dashboard_kpi') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('overview.dashboard_kpi') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                <i class="fas fa-chart-line mr-2 text-success"></i> <span>KPI</span>
                            </a>
                        </div>

                        <div class="client-item-wrapper">
                            <a href="{{ route('overview.dashboard') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('overview.dashboard') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                <i class="fas fa-tachometer-alt mr-2 text-primary"></i> <span>Dashboard</span>
                            </a>
                        </div>

                        <style>
                            .transition-hover {
                                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
                            }
                            .transition-hover:hover {
                                background-color: #f8f9fc !important;
                                color: #4e73df !important;
                            }
                        </style>
                    </div>
                </div>

                @php
                    // Daftar semua route yang termasuk di bawah menu MCN
                    $isMCNPage = Route::is([
                        'client.produk_divisi',
                        'barang_client.index',
                        'social_creator.index',
                        'blacklist.index',
                        'tools.index',
                        'gform_affiliate.index',
                        'selection.index',
                    ]);

                    $isPdf = Route::is('PDF');
                @endphp

                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseMCN">
                    MCN
                </a>

                <div id="collapseMCN" class="collapse {{ $isMCNPage ? 'show' : '' }}" 
                    aria-labelledby="headingMCN" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2 py-2">
                        
                        @if ($isSuperAdmin || ($myDivision->name == "MCN" && $myRole->level < 4))
                            <div class="mb-2">
                                <a href="{{ route('client.produk_divisi', 3) }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                                </a>
                            </div>
                            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2 {{ !$isPdf ? 'collapsed' : '' }}" 
                            data-toggle="collapse" 
                            data-target="#cat_mcn">
                                <span><i class="fas fa-folder-open mr-2 text-info"></i>Client</span>
                                <i class="fas fa-caret-down arrow-icon-xs"></i>
                            </a>
                        @endif


                        <div class="group-wrapper mb-2">
                            <div id="cat_mcn" class="collapse {{ ($isPdf) ? 'show' : '' }} ml-2 border-left-custom">
                                @foreach ($mcnProduk as $client)
                                    @if($myDivision->name == "MCN" || $isSuperAdmin)
                                        @php
                                            // 1. Cek apakah user punya akses eksplisit ke client ini (dari tabel user_client)
                                            $hasClientAccess = in_array($client->client_id, $myClientIds);

                                            // 2. Tentukan Power User: Super Admin, Role Admin, atau Head of Division
                                            $isPowerUser = $isSuperAdmin || $myRole->role_name == "Busines Unit Head";

                                            // 3. Syarat Utama Membuka Dropdown Client
                                            $canOpen = $isPowerUser || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                                            $isActiveClient = $currentProduct == $client->client_id;
                                        @endphp

                                        <div class="client-item-wrapper mb-1">
                                            {{-- Bagian Header Client --}}
                                            @if($canOpen)
                                                <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }}" 
                                                data-toggle="collapse" 
                                                data-target="#client_mcn_{{ $client->client_id }}" 
                                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                                    <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                                </a>

                                                {{-- Bagian Isi Platform --}}
                                                <div id="client_mcn_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                                    <div class="platform-sub-links pl-3 pt-1 pb-2">
                                                        <div class="client-item-wrapper mb-1">
                                                            <a href="{{ route('PDF', ['id_produk' => $client->id]) }}" class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}">
                                                                <i class="fas fa-file-pdf mr-2 text-danger"></i> <span>File</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Tampilan untuk yang tidak punya akses --}}
                                                <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
                                                    <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if($myDivision->name == "MCN" || $isSuperAdmin)
                            <div class="client-item-wrapper mb-2">
                                <a href="{{ route('barang_client.index') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('barang_client.index') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                    <i class="fas fa-boxes mr-2 text-warning"></i> <span>Project MCN</span>
                                </a>
                            </div>

                            <div class="client-item-wrapper mb-2">
                                <a href="{{ route('social_creator.index') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('social_creator.index') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                    <i class="fas fa-users mr-2 text-primary"></i> <span>Affiliator</span>
                                </a>
                            </div>

                            <div class="client-item-wrapper mb-2">
                                <a href="{{ route('blacklist.index') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('blacklist.index') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                    <i class="fas fa-ban mr-2 text-danger"></i> <span>Blacklist</span>
                                </a>
                            </div>
                            
                            <div class="client-item-wrapper mb-2">
                                <a href="{{ route('tools.index') }}" class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('tools.index') ? 'active-menu bg-light font-weight-bold' : '' }}">
                                    <i class="fas administrative-tools mr-2 text-success"></i> <span>Tools</span>
                                </a>
                            </div>
                        @endif

                        @foreach ($mcnClient as $item)
                            @php
                                $isItemActive = request()->is('project/' . $item->id . '*');
                            @endphp
                            <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover mb-1 {{ !$isItemActive ? 'collapsed' : '' }}" 
                            data-toggle="collapse" 
                            data-target="#client_mcn_project_{{ $item->id }}" 
                            aria-expanded="{{ $isItemActive ? 'true' : 'false' }}">
                                <span class="text-uppercase font-weight-bold text-truncate">
                                    <i class="far fa-user-circle mr-2 text-secondary"></i>{{ strtoupper($item->project) }}
                                </span>
                                <i class="fas fa-caret-down arrow-icon-xs"></i>
                            </a>

                            {{-- Konten Sub-Menu Project --}}
                            <div id="client_mcn_project_{{ $item->id }}" class="collapse {{ request()->segment(2) == $item->id ? 'show' : '' }}">
                                <div class="platform-sub-links pl-3 pt-1 pb-2"> 
                                    
                                    {{-- Link Gform --}}
                                    <div class="client-item-wrapper mb-1">
                                        <a href="{{ route('gform_affiliate.index', ['project_id' => $item->id]) }}" class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('gform_affiliate/' . $item->id) ? 'active-menu bg-light font-weight-bold' : '' }}">
                                            <i class="fas fa-file-alt mr-2 text-info"></i><span>Gform</span>
                                        </a>
                                    </div>

                                    {{-- Link Hasil Seleksi --}}
                                    <div class="client-item-wrapper mb-1">
                                        <a href="{{ route('selection.index', ['project_id' => $item->id]) }}" class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('selection/' . $item->id) ? 'active-menu bg-light font-weight-bold' : '' }}">
                                            <i class="fas fa-clipboard-check mr-2 text-success"></i><span>Hasil Seleksi</span>
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>
                        @endforeach

                        <style>
                            .transition-hover {
                                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
                            }
                            .transition-hover:hover {
                                background-color: #f8f9fc !important;
                                color: #4e73df !important;
                            }
                        </style>
                    </div>
                </div>

                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseTech">
                    Tech
                </a>

                <div id="collapseTech" class="collapse {{ request()->is('*tech*') ? 'show' : '' }}" 
                    aria-labelledby="headingTech" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2 py-2">
                        
                        @if ($isSuperAdmin || ($myDivision->name == "Tech" && $myRole->level < 2))
                            <div class="mb-2">
                                <a href="{{ route('client.produk_divisi', 5) }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                                </a>
                            </div>
                            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2" 
                            data-toggle="collapse" 
                            data-target="#cat_tech">
                                <span><i class="fas fa-folder-open mr-2 text-info"></i>Client</span>
                                <i class="fas fa-caret-down arrow-icon-xs"></i>
                            </a>
                        @endif


                        <div class="group-wrapper mb-2">
                            <div id="cat_tech" class="collapse ml-2 border-left-custom">
                                @foreach ($techProduk as $client)
                                    @if($myDivision->name == "Tech" || $isSuperAdmin)
                                        @php
                                            // 1. Cek apakah user punya akses eksplisit ke client ini (dari tabel user_client)
                                            $hasClientAccess = in_array($client->client_id, $myClientIds);

                                            // 2. Tentukan Power User: Super Admin, Role Admin, atau Head of Division
                                            $isPowerUser = $isSuperAdmin || $myRole->role_name == "Busines Unit Head";

                                            // 3. Syarat Utama Membuka Dropdown Client
                                            $canOpen = $isPowerUser || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                                            $isActiveClient = $currentProduct == $client->client_id;
                                        @endphp

                                        <div class="client-item-wrapper mb-1">
                                            {{-- Bagian Header Client --}}
                                            @if($canOpen)
                                                <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }}" 
                                                data-toggle="collapse" 
                                                data-target="#client_tech_{{ $client->client_id }}" 
                                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                                    <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                                </a>

                                                {{-- Bagian Isi Sub-Menu (Overview, Github, Monitoring, File) --}}
                                                <div id="client_tech_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                                    <div class="platform-sub-links pl-3 pt-1 pb-2">
                                                        <div class="client-item-wrapper mb-1">
                                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('tech_overview') ? 'active-menu bg-light font-weight-bold' : '' }}" href="#">
                                                                <i class="fas fa-eye mr-2 text-primary"></i> <span>Overview</span>
                                                            </a>
                                                        </div>
                                                        <div class="client-item-wrapper mb-1">
                                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('tech_github') ? 'active-menu bg-light font-weight-bold' : '' }}" href="#">
                                                                <i class="fab fa-github mr-2 text-dark"></i> <span>Github</span>
                                                            </a>
                                                        </div>
                                                        <div class="client-item-wrapper mb-1">
                                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('tech_monitoring') ? 'active-menu bg-light font-weight-bold' : '' }}" href="#">
                                                                <i class="fas fa-desktop mr-2 text-success"></i> <span>Monitoring</span>
                                                            </a>
                                                        </div>
                                                        <div class="client-item-wrapper mb-1">
                                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('PDF', ['id_produk' => $client->id]) }}">
                                                                <i class="fas fa-file-pdf mr-2 text-danger"></i> <span>File</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Tampilan untuk yang tidak punya akses --}}
                                                <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
                                                    <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <style>
                            .transition-hover {
                                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
                            }
                            .transition-hover:hover {
                                background-color: #f8f9fc !important;
                                color: #4e73df !important;
                            }
                        </style>
                    </div>
                </div>

                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseEcommerce">
                    Ecommerce
                </a>

                <div id="collapseEcommerce" class="collapse {{ request()->is('*ecommerce*') ? 'show' : '' }}" 
                    aria-labelledby="headingEcommerce" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2 py-2">
                        
                        @if ($isSuperAdmin || ($myDivision->name == "Ecommerce" && $myRole->level < 4))
                            <div class="mb-2">
                                <a href="{{ route('client.produk_divisi', 7) }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                                </a>
                            </div>
                            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2" 
                            data-toggle="collapse" 
                            data-target="#cat_ecommerce">
                                <span><i class="fas fa-folder-open mr-2 text-info"></i>Client</span>
                                <i class="fas fa-caret-down arrow-icon-xs"></i>
                            </a>
                        @endif


                        <div class="group-wrapper mb-2">
                            <div id="cat_ecommerce" class="collapse ml-2 border-left-custom">
                                @foreach ($ecommerceProduk as $client)
                                    @if($myDivision->name == "Ecommerce" || $isSuperAdmin)
                                        @php
                                            // 1. Cek apakah user punya akses eksplisit ke client ini (dari tabel user_client)
                                            $hasClientAccess = in_array($client->client_id, $myClientIds);

                                            // 2. Tentukan Power User: Super Admin, Role Admin, atau Head of Division
                                            $isPowerUser = $isSuperAdmin || $myRole->role_name == "Busines Unit Head";

                                            // 3. Syarat Utama Membuka Dropdown Client
                                            $canOpen = $isPowerUser || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                                            $isActiveClient = $currentProduct == $client->client_id;
                                        @endphp

                                        <div class="client-item-wrapper mb-1">
                                            {{-- Bagian Header Client --}}
                                            @if($canOpen)
                                                <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }}" 
                                                data-toggle="collapse" 
                                                data-target="#client_ecommerce_{{ $client->client_id }}" 
                                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                                    <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                                </a>

                                                {{-- Bagian Isi Sub-Menu (File / Submenu opsional) --}}
                                                <div id="client_ecommerce_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                                    <div class="platform-sub-links pl-3 pt-1 pb-2">
                                                        <div class="client-item-wrapper mb-1">
                                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('PDF', ['id_produk' => $client->id]) }}">
                                                                <i class="fas fa-file-pdf mr-2 text-danger"></i> <span>File</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Tampilan untuk yang tidak punya akses --}}
                                                <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
                                                    <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <style>
                            .transition-hover {
                                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
                            }
                            .transition-hover:hover {
                                background-color: #f8f9fc !important;
                                color: #4e73df !important;
                            }
                        </style>
                    </div>
                </div>

                <a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseFinance">
                    Finance
                </a>

                @php
                    $isFinancePage = Route::is([
                        'finance_coa.index',
                        'general_journal.index',
                        'finance_ledger.list_account',
                        'Ledger.neraca_saldo',
                        'adjustment_journal.index',
                        'Ledger.neraca_lajur',
                        'Ledger.arus_kas',
                        'Ledger.jurnal_penutup'
                    ]);
                @endphp

                <div id="collapseFinance" class="collapse {{ $isFinancePage ? 'show' : '' }}" 
                    aria-labelledby="headingFinance" data-parent="#collapseDivisi">
                    <div class="collapse-container px-2 py-2">
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('finance_coa.index') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('finance_coa.index') }}">
                                <i class="fas fa-list-alt mr-2 text-primary"></i> <span>Daftar Akun</span>
                            </a>
                        </div>
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('general_journal.index') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('general_journal.index') }}">
                                <i class="fas fa-book mr-2 text-info"></i> <span>Jurnal Umum</span>
                            </a>
                        </div>
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('finance_ledger.list_account') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('finance_ledger.list_account') }}">
                                <i class="fas fa-file-invoice-dollar mr-2 text-success"></i> <span>Buku Besar</span>
                            </a>
                        </div>
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('Ledger.neraca_saldo') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('Ledger.neraca_saldo') }}">
                                <i class="fas fa-balance-scale mr-2 text-warning"></i> <span>Neraca Saldo</span>
                            </a>
                        </div>
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('adjustment_journal.index') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('adjustment_journal.index') }}">
                                <i class="fas fa-edit mr-2 text-secondary"></i> <span>Jurnal Penyesuaian</span>
                            </a>
                        </div>

                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('Ledger.neraca_lajur') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('Ledger.neraca_lajur') }}">
                                <i class="fas fa-table mr-2 text-primary"></i> <span>Neraca Lajur</span>
                            </a>
                        </div>
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('Ledger.arus_kas') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('Ledger.arus_kas') }}">
                                <i class="fas fa-wallet mr-2 text-success"></i> <span>Laporan Arus Kas</span>
                            </a>
                        </div>
                        
                        <div class="client-item-wrapper mb-2">
                            <a class="d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('Ledger.jurnal_penutup') ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('Ledger.jurnal_penutup') }}">
                                <i class="fas fa-lock mr-2 text-danger"></i> <span>Jurnal Penutup</span>
                            </a>
                        </div>

                        <style>
                            .transition-hover {
                                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
                            }
                            .transition-hover:hover {
                                background-color: #f8f9fc !important;
                                color: #4e73df !important;
                            }
                        </style>
                    </div>
                </div>
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