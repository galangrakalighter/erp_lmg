<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center py-4" href="{{ route('home') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="{{ asset('img/logo.jpg') }}" alt="Logo" class="rounded-circle shadow-lg" 
                 style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #fff;">
        </div>
        <div class="sidebar-brand-text mx-3 uppercase tracking-wider">Lighter</div>
    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('home') }}">
            <i class="fas fa-fw fa-th-large"></i>
            <span>Dashboard</span>
        </a>
    </li>

    @if(!request()->routeIs('cms*'))
        <div class="sidebar-heading mt-3">Main Services</div>

        {{-- ==========================================
             1. DIGITAL MARKETING (ID 1)
        ========================================== --}}
        <li class="nav-item {{ (request()->is('*manajemen*') || request()->is('*analisis*')) && !request()->is('*BD*') ? 'active' : '' }}">
            <a class="nav-link {{ (request()->is('*manajemen*') || request()->is('*analisis*')) ? '' : 'collapsed' }}" 
               href="#" data-toggle="collapse" data-target="#collapseLGTR">
                <i class="fas fa-fw fa-bullhorn"></i>
                <span>Digital Marketing</span>
            </a>

            <div id="collapseLGTR" class="collapse {{ (request()->is('*manajemen*') || request()->is('*analisis*')) ? 'show' : '' }}" data-parent="#accordionSidebar">
                <div class="collapse-container">
                    <div class="brand-sub-header text-danger mb-3">
                        <i class="fas fa-store mr-2"></i> MR. CREPE
                    </div>

                    @if ($isSuperAdmin || $myDivision->name == "Digital Marketing" && $myRole->level < 4)
                        <div class="px-2 mb-3">
                            <a class="btn-cms-link" href="{{ route('cms.produk', 'LGTR') }}">
                                <i class="fas fa-cog mr-1"></i> Dashboard CMS
                            </a>
                        </div>
                    @endif

                    @foreach ($menus as $group)
                        @if($myDivision->name == "Digital Marketing" || $isSuperAdmin)
                            @if($group->id == 1)
                            <div class="group-wrapper mb-2">
                                <a class="category-dropdown-link collapsed" data-toggle="collapse" data-target="#cat_lgtr_{{ $group->id }}">
                                    <span><i class="fas fa-folder-open mr-2 text-warning"></i>{{ $group->category }}</span>
                                    <i class="fas fa-chevron-down arrow-icon-xs"></i>
                                </a>

                                <div id="cat_lgtr_{{ $group->id }}" class="collapse ml-2 border-left-custom">
                                    @foreach ($group->clients as $client)
                                        @php
                                            // 1. Cek apakah user punya akses eksplisit ke client ini (dari tabel user_client)
                                            $hasClientAccess = in_array($client->id, $myClientIds);

                                            // 2. Tentukan Power User: Super Admin, Role Admin, atau Head of Division
                                            // Mereka bisa melihat semua client tanpa harus terdaftar di tabel user_client
                                            $isPowerUser = $isSuperAdmin || $myRole->role_name == "Digital Marketing Manager";

                                            // 3. Syarat Utama Membuka Dropdown Client:
                                            // Dia Power User ATAU (Berada di Divisi 1 DAN terdaftar di user_client)
                                            $canOpen = $isPowerUser || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                                        @endphp

                                        <div class="client-item-wrapper" style="width: 7vw">
                                            {{-- Bagian Header Client --}}
                                            @if($canOpen)
                                                <a class="client-dropdown-link collapsed" data-toggle="collapse" data-target="#client_lgtr_{{ $client->id }}">
                                                    <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                                </a>
                                            @else
                                                {{-- Tampilan untuk yang tidak punya akses: Nama muncul tapi tidak bisa diklik --}}
                                                <div class="p-1 text-muted" style="opacity: 0.6; cursor: not-allowed;">
                                                    <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                                </div>
                                            @endif

                                            {{-- Bagian Isi Platform (Hanya dirender jika punya akses) --}}
                                            @if($canOpen)
                                                <div id="client_lgtr_{{ $client->id }}" class="collapse ml-3">
                                                    <div class="platform-sub-links">
                                                        @php
                                                            $platforms = collect(data_get($client->access_token, 'platform'))->filter();
                                                        @endphp

                                                        @if($platforms->isNotEmpty())
                                                            @foreach ($platforms as $plt)
                                                                <div class="platform-group">
                                                                    <div class="platform-label">
                                                                        <i class="fab fa-{{ strtolower($plt) }}"></i> {{ $plt }}
                                                                    </div>
                                                                    <a href="{{ route('manajemen', ['id_produk' => $client->id, 'platform' => $plt]) }}">
                                                                        Manajemen Konten
                                                                    </a>
                                                                    <a href="{{ route('analisis', ['id_produk' => $client->id, 'platform' => $plt]) }}">
                                                                        Analisis
                                                                    </a>
                                                                    <a href="{{ route('market_research', ['id_produk' => $client->id, 'platform' => $plt]) }}">
                                                                        Market Research
                                                                    </a>
                                                                    <a href="{{ route('branding', ['id_produk' => $client->id, 'platform' => $plt]) }}">
                                                                        Branding
                                                                    </a>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted small p-2 d-block">Platform belum diset</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        </li>

        {{-- ==========================================
             2. BUSINESS DEVELOPMENT (ID NOT 1)
        ========================================== --}}
        <li class="nav-item {{ request()->is('*BD*') ? 'active' : '' }}">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBD">
                <i class="fas fa-fw fa-briefcase"></i>
                <span>Business Dev</span>
            </a>
            <div id="collapseBD" class="collapse" data-parent="#accordionSidebar">
                <div class="collapse-container">
                    <div class="brand-sub-header text-primary mb-3">
                        <i class="fas fa-store mr-2"></i> MR. CREPE
                    </div>

                    @if ($isSuperAdmin || $myDivision->name == "Digital Marketing" && $myRole->level < 4)
                        <div class="px-2 mb-3">
                            <a class="btn-cms-link" href="{{ route('cms.produk', 'BD') }}">
                                <i class="fas fa-cog mr-1"></i> Dashboard CMS
                            </a>
                        </div>
                    @endif

                    @foreach ($menus as $group)
                        @if($group->id == 2 || $group->id == 3)
                        <div class="group-wrapper mb-2">
                            <a class="category-dropdown-link collapsed" data-toggle="collapse" data-target="#cat_bd_{{ $group->id }}">
                                <span><i class="fas fa-folder-open mr-2 text-info"></i>{{ $group->category }}</span>
                                <i class="fas fa-chevron-down arrow-icon-xs"></i>
                            </a>

                            <div id="cat_bd_{{ $group->id }}" class="collapse ml-2 border-left-custom">
                                @foreach ($group->clients as $client)
                                    @php
                                        $hasClientAccess = in_array($client->id, $myClientIds);
                                        
                                        $canOpen = $isSuperAdmin || ($myDivision->name == "Digital Marketing" && $hasClientAccess);
                                    @endphp
                                    <div class="client-item-wrapper" style="width:7vw">
                                        
                                        @if( $isSuperAdmin || $myRole->level == 1 && $myDivision->name == "Digital Marketing" || $hasClientAccess)
                                        <a class="client-dropdown-link collapsed" data-toggle="collapse" data-target="#client_bd_{{ $client->id }}">
                                            <span>{{ $client->nama }}</span>
                                            <i class="fas fa-caret-right arrow-icon-xs"></i>
                                        </a>
                                        @else
                                            <span>{{ $client->nama }}</span>
                                            <i class="fas fa-caret-right arrow-icon-xs"></i>
                                        @endif
                                        <div class="client-item-wrapper" style="width:7vw">
                                        @if($canOpen)
                                            <a class="client-dropdown-link collapsed" data-toggle="collapse" data-target="#client_bd_{{ $client->id }}">
                                                <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                <i class="fas fa-caret-right arrow-icon-xs"></i>
                                            </a>

                                            <div id="client_bd_{{ $client->id }}" class="collapse ml-3">
                                                <div class="platform-sub-links">
                                                    @php
                                                        $platforms = collect(data_get($client->access_token, 'platform'))->filter();
                                                    @endphp

                                                    @if($platforms->isNotEmpty())
                                                        @foreach ($platforms as $plt)
                                                            <div class="platform-group">
                                                                <div class="platform-label">
                                                                    <i class="fab fa-{{ strtolower($plt) }}"></i> {{ $plt }}
                                                                </div>
                                                                <a href="{{ route('manajemen', ['id_produk' => $client->id, 'platform' => $plt]) }}">Manajemen {{ $plt }}</a>
                                                                <a href="{{ route('analisis', ['id_produk' => $client->id, 'platform' => $plt]) }}">Analisis Konten {{ $plt }}</a>
                                                                <a href="{{ route('market_research', ['id_produk' => $client->id, 'platform' => $plt]) }}">Market Research {{ $plt }}</a>
                                                                <a href="{{ route('branding', ['id_produk' => $client->id, 'platform' => $plt]) }}">Branding {{ $plt }}</a>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted small p-2 d-block">Platform belum diset</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-1 text-muted" style="opacity: 0.6; cursor: not-allowed;">
                                                <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                                <i class="fas fa-caret-right arrow-icon-xs"></i>
                                            </div>
                                        @endif
                                    </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </li>

        <li class="nav-item {{ request()->is('*HC*') ? 'active' : '' }}">
            <a class="nav-link {{ request()->is('*HC*') ? '' : 'collapsed' }}" href="#" 
            data-toggle="collapse" data-target="#collapseHC" 
            aria-expanded="{{ request()->is('*HC*') ? 'true' : 'false' }}" aria-controls="collapseHC">
                <i class="fas fa-fw fa-users"></i>
                <span>Human Capital</span>
            </a>
            
            <div id="collapseHC" class="collapse {{ request()->is('*HC*') ? 'show' : '' }}" 
                aria-labelledby="headingHC" data-parent="#accordionSidebar">
                <div class="collapse-container">
                    {{-- Menu Daily Work --}}
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link {{ request()->is('hc_dailywork') ? 'active' : '' }}" href="{{ route('hc_daily.index') }}">
                            <span>Daily Work</span>
                        </a>
                    </div>

                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link {{ request()->is('hc_overview.list') ? 'active' : '' }}" href="{{ route('hc_overview.list') }}">
                            <span>Project Overview</span>
                        </a>
                    </div>

                    <div class="client-item-wrapper">
                        <a class="client-dropdown-link {{ request()->is('hc_overview.list') ? 'active' : '' }}" href="{{ route('overview.dashboard') }}">
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </li>

        <li class="nav-item {{ request()->is('*HC*') ? 'active' : '' }}">
            <a class="nav-link {{ request()->is('*HC*') ? '' : 'collapsed' }}" href="#" 
            data-toggle="collapse" data-target="#collapseMCN" 
            aria-expanded="{{ request()->is('*HC*') ? 'true' : 'false' }}" aria-controls="collapseMCN">
                <i class="fas fa-fw fa-users"></i>
                <span>MCN</span>
            </a>
            
            <div id="collapseMCN" class="collapse {{ request()->is('*HC*') ? 'show' : '' }}" 
                aria-labelledby="headingHC" data-parent="#accordionSidebar">
                <div class="collapse-container">
                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link {{ request()->is('barang_client') ? 'active' : '' }}" href="{{ route('barang_client.index') }}">
                            <span>Project MCN</span>
                        </a>
                    </div>

                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link {{ request()->is('social_creator') ? 'active' : '' }}" href="{{ route('social_creator.index') }}">
                            <span>Affiliator</span>
                        </a>
                    </div>

                    <div class="client-item-wrapper mb-2">
                        <a class="client-dropdown-link {{ request()->is('blacklist') ? 'active' : '' }}" href="{{ route('blacklist.index') }}">
                            <span>Blacklist</span>
                        </a>
                    </div>

                    @foreach ($mcnClient as $item)
                    <a class="client-dropdown-link {{ request()->is('project/' . $item->id . '*') ? '' : 'collapsed' }}" 
                        data-toggle="collapse" 
                        data-target="#client_mcn_{{ $item->id }}" 
                        aria-expanded="{{ request()->is('project/' . $item->id . '*') ? 'true' : 'false' }}"
                        style="cursor: pointer;">
                            <span class="text-uppercase font-weight-bold">
                                <i class="far fa-user-circle mr-2"></i>{{ strtoupper($item->project) }}
                            </span>
                            <i class="fas fa-chevron-down arrow-icon-xs"></i>
                        </a>

                        {{-- Konten Sub-Menu --}}
                        <div id="client_mcn_{{ $item->id }}" class="collapse {{ request()->segment(2) == $item->id ? 'show' : '' }}">
                            <div class="platform-sub-links pl-4"> {{-- Tambah padding kiri agar terlihat bertingkat --}}
                                
                                {{-- Link Gform --}}
                                <div class="client-item-wrapper mb-1">
                                    <a class="client-dropdown-link {{ request()->is('gform_affiliate/' . $item->id) ? 'active text-primary' : '' }}" 
                                    href="{{ route('gform_affiliate.index', ['project_id' => $item->id]) }}">
                                        <i class="fas fa-file-alt mr-2"></i><span>Gform</span>
                                    </a>
                                </div>

                                {{-- Link Hasil Seleksi --}}
                                <div class="client-item-wrapper mb-1">
                                    <a class="client-dropdown-link {{ request()->is('selection/' . $item->id) ? 'active text-primary' : '' }}" 
                                    href="{{ route('selection.index', ['project_id' => $item->id]) }}">
                                        <i class="fas fa-clipboard-check mr-2"></i><span>Hasil Seleksi</span>
                                    </a>
                                </div>
                                
                            </div>
                        </div>
                    @endforeach


                </div>
            </div>
        </li>
    @endif

    @if($isSuperAdmin)
        <li class="nav-item">
            <a class="nav-link collapsed" href="{{ route('manajemen_user.index') }}">
                <i class="fas fa-fw fa-users"></i>
                <span>Manajemen User</span>
            </a>
        </li>
    @endif

    <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('crm.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Customer Relationship Management</span>
        </a>
    </li>

    {{-- <li class="nav-item">
        <a class="nav-link collapsed" href="{{ route('payment.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Payment</span>
        </a>
    </li> --}}

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

    <div class="text-center d-none d-md-inline mt-4">
        <button class="rounded-circle border-0 shadow" id="sidebarToggle"></button>
    </div>
</ul>

{{-- MODERNIZE CSS --}}
<style>
    /* 1. Reset Scrollbar */
    #accordionSidebar {
        overflow-y: auto !important;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    #accordionSidebar::-webkit-scrollbar { display: none; }

    /* 2. Container Styling */
    .collapse-container {
        background: rgba(255, 255, 255, 0.05);
        margin: 0 0.8rem 1rem 0.8rem;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* 3. Category Level Styling (Internal Jasa/Barang) */
    .category-dropdown-link {
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

    /* 4. Client Level Styling */
    .client-dropdown-link {
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
    .client-dropdown-link:hover { color: #fff !important; }

    /* 5. Platform Links (Level Terakhir) */
    .platform-sub-links {
        padding: 5px 0;
    }
    .platform-group {
        margin-bottom: 8px;
        background: rgba(0,0,0,0.1);
        padding: 5px;
        border-radius: 6px;
    }
    .platform-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: #f6c23e;
        padding-left: 8px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }
    .platform-sub-links a {
        display: block;
        padding: 4px 10px 4px 20px;
        color: rgba(255,255,255,0.7) !important;
        font-size: 0.75rem;
        text-decoration: none !important;
    }
    .platform-sub-links a:hover { color: #fff !important; background: rgba(255,255,255,0.1); border-radius: 4px; }

    /* 6. Decoration & Arrows */
    .border-left-custom {
        border-left: 1px solid rgba(255,255,255,0.2);
        margin-top: 5px;
    }
    .arrow-icon-xs { font-size: 0.5rem; transition: transform 0.3s; }
    .category-dropdown-link:not(.collapsed) .arrow-icon-xs { transform: rotate(180deg); }
    .client-dropdown-link:not(.collapsed) .arrow-icon-xs { transform: rotate(90deg); }

    .brand-sub-header {
        font-size: 0.7rem;
        font-weight: 900;
        background: white;
        padding: 4px;
        border-radius: 4px;
        text-align: center;
        letter-spacing: 1px;
    }

    .btn-cms-link {
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
</style>