{{-- =========================================================
    LMG SIDEBAR
    Tailwind CSS version
========================================================= --}}

<style>
    /* Keep the sidebar surface dark while making navigation labels readable. */
    #accordionSidebar a:not(.bg-white):not([data-sidebar-brand]),
    #accordionSidebar button[data-sidebar-collapse] {
        color: #e4e4e7 !important;
    }

    #accordionSidebar a:not(.bg-white):not([data-sidebar-brand]):hover,
    #accordionSidebar button[data-sidebar-collapse]:hover {
        background-color: #fff !important;
        color: #161616 !important;
    }

    #accordionSidebar a.bg-white,
    #accordionSidebar a[data-sidebar-active="true"] {
        background-color: #fff !important;
        color: #161616 !important;
        box-shadow: none;
        font-weight: 700;
    }

    /* Hanya tautan halaman yang aktif berwarna putih. Pastikan label dan
       ikon turunannya tetap gelap meski class bawaan memberi warna sendiri. */
    #accordionSidebar a.bg-white *,
    #accordionSidebar a[data-sidebar-active="true"] * {
        color: #161616 !important;
    }

    #accordionSidebar a:hover svg,
    #accordionSidebar button[data-sidebar-collapse]:hover svg,
    #accordionSidebar a.bg-white svg,
    #accordionSidebar a[data-sidebar-active="true"] svg {
        color: #161616 !important;
    }

    #accordionSidebar button[data-sidebar-parent-active="true"] {
        background-color: transparent !important;
        color: #fff !important;
    }

    #accordionSidebar button[data-sidebar-parent-active="true"] svg {
        color: #fff !important;
    }
</style>

<aside
    id="accordionSidebar"
    class="fixed inset-y-0 left-0 z-[1100] flex w-[280px] -translate-x-full flex-col border-r border-zinc-800 bg-zinc-950 text-white transition-transform duration-300 ease-out lg:translate-x-0"
>

    {{-- =====================================================
        BRAND
    ====================================================== --}}
    <div class="flex h-[76px] shrink-0 items-center border-b border-zinc-800 px-5">
        <a
            href="{{ route('home') }}"
            data-sidebar-brand
            class="group flex min-w-0 items-center gap-3"
        >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white">
                <img
                    src="{{ asset('img/logo.jpg') }}"
                    class="h-full w-full object-cover"
                    alt="Lighter Media Group"
                >
            </div>

            <div class="min-w-0">
                <div class="truncate text-sm font-bold tracking-tight text-white">
                    Lighter
                </div>

                <div class="truncate text-[9px] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                    Media Group
                </div>
            </div>
        </a>

        {{-- Mobile close --}}
        <button
            id="sidebarClose"
            type="button"
            class="ml-auto flex h-8 w-8 items-center justify-center rounded-lg text-zinc-500 transition hover:bg-zinc-800 hover:text-white"
            aria-label="Close sidebar"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M6 6l12 12M18 6L6 18"
                />
            </svg>
        </button>
    </div>


    {{-- =====================================================
        SIDEBAR CONTENT
    ====================================================== --}}
    <div id="sidebarNavigationContent" class="min-h-0 flex-1 overflow-y-auto px-3 py-4 scrollbar-thin">

        {{-- Nilai awal agar state dropdown selalu tersedia pada seluruh halaman. --}}
        @php
            $divisiActive = false;
        @endphp

        {{-- =================================================
            PRIMARY NAVIGATION
        ================================================== --}}
        <div class="mb-6">

            <div class="mb-2 px-3 text-[9px] font-bold uppercase tracking-[0.18em] text-zinc-600">
                Workspace
            </div>


            {{-- Dashboard --}}
            <a
                href="{{ route('home') }}"
                class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                {{ request()->routeIs('home')
                    ? 'bg-white text-zinc-950'
                    : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                }}"
            >
                <svg class="h-[18px] w-[18px] shrink-0 {{ request()->routeIs('home') ? 'text-zinc-950' : 'text-zinc-500 group-hover:text-white' }}"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"
                    />
                </svg>

                <span class="font-medium">
                    Dashboard
                </span>
            </a>

            {{-- @unless($isSuperAdmin)
                <a
                    href="{{ route('monitoring.index') }}"
                    class="group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                    {{ Route::is('monitoring.*')
                        ? 'bg-white text-zinc-950'
                        : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    }}"
                >
                    <svg class="h-[18px] w-[18px] shrink-0 text-zinc-500 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7a3 3 0 013-3h6a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7zM16 10l4-2v8l-4-2v-4z" />
                    </svg>
                    <span class="font-medium">Monitoring</span>
                </a>
            @endunless --}}

            @php
                $isPerformanceWorkspace = Route::is(
                    'overview.dashboard_kpi',
                    'overview.dashboard',
                    'hc_overview.*',
                    'hc_daily.index'
                );
            @endphp
            <div class="mb-1">
                <button type="button" data-sidebar-collapse="collapsePerformanceWorkspace" data-sidebar-group="primary"
                    class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition {{ $isPerformanceWorkspace ? 'bg-zinc-900 text-white' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}"
                    aria-expanded="{{ $isPerformanceWorkspace ? 'true' : 'false' }}">
                    <span class="flex items-center gap-3">
                        <svg class="h-[18px] w-[18px] text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M6 17V9M12 17V5M18 17v-7" /></svg>
                        <span class="font-medium">KPI &amp; Daily Work</span>
                    </span>
                    <svg class="sidebar-chevron h-4 w-4 text-zinc-600 transition-transform duration-200 {{ $isPerformanceWorkspace ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6" /></svg>
                </button>
                <div id="collapsePerformanceWorkspace" class="{{ $isPerformanceWorkspace ? '' : 'hidden' }} mt-1 space-y-1 pl-3">
                    <a href="{{ route('overview.dashboard_kpi') }}" class="block rounded-lg px-3 py-2 text-xs transition {{ Route::is('overview.dashboard_kpi', 'overview.dashboard') ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-500 hover:bg-zinc-900 hover:text-white' }}">Dashboard</a>
                    <a href="{{ route('hc_overview.list') }}" class="block rounded-lg px-3 py-2 text-xs transition {{ Route::is('hc_overview.*') && !request()->boolean('readonly') ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-500 hover:bg-zinc-900 hover:text-white' }}">Overview</a>
                    <a href="{{ route('hc_daily.index') }}" class="block rounded-lg px-3 py-2 text-xs transition {{ Route::is('hc_daily.index') ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-500 hover:bg-zinc-900 hover:text-white' }}">Daily Work</a>
                </div>
            </div>

            {{-- Storage --}}
            @php
                $isStoragePage = request()->routeIs('master-folder.*', 'file-master.*');
            @endphp
            <div class="mb-4 border-b border-zinc-800 pb-4">
                <button
                    type="button"
                    data-sidebar-collapse="collapseStorage"
                    data-sidebar-group="primary"
                    class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition
                    {{ $isStoragePage
                        ? 'bg-zinc-900 text-white'
                        : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    }}"
                    aria-expanded="{{ $isStoragePage ? 'true' : 'false' }}"
                >
                    <span class="flex items-center gap-3">
                        <svg class="h-[18px] w-[18px] text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                        </svg>
                        <span class="font-medium">Storage</span>
                    </span>

                    <svg class="sidebar-chevron h-4 w-4 text-zinc-600 transition-transform duration-200 {{ $isStoragePage ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div id="collapseStorage" class="{{ $isStoragePage ? '' : 'hidden' }} mt-1 space-y-1 pl-3">
                    <a
                        href="{{ route('master-folder.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2 text-xs transition
                        {{ request()->routeIs('master-folder.*') ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                        </svg>
                        <span>Folder Master</span>
                    </a>

                    <a
                        href="{{ route('file-master.index') }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2 text-xs transition
                        {{ request()->routeIs('file-master.*') ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-400 hover:bg-zinc-900 hover:text-white' }}"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 3h8l4 4v14H6V3zM14 3v5h5M9 13h6M9 17h6" />
                        </svg>
                        <span>File Master</span>
                    </a>
                </div>
            </div>

        </div>


        {{-- =================================================
            ROUTE STATE
        ================================================== --}}
        @php
            $currentProduct = request()->route('id_produk');
            $currentPlatform = request()->route('platform');

            $isDMProduct = $digitalMarketingProduk->contains('client_id', $currentProduct);
            $isBDProduct = $businessDevProduk->contains('client_id', $currentProduct);
            $isMCNProduct = $mcnProduk->contains('client_id', $currentProduct);
            $techProdukList = $techProduk->contains('client_id', $currentProduct);
            $isEcommerceList = $ecommerceProduk->contains('client_id', $currentProduct);
            $isMultimediaProduct = $multimediaProduk->contains('client_id', $currentProduct);

            $isBusinessDevWorkspace = Route::is('business-dev.workspace');
            $isDMClientManagement = Route::is('cms.produk') && request()->route('nama') === 'LGTR';

            $isMarketingPage = Route::is([
                'market_research',
                'branding',
                'analisis',
                'manajemen',
                'PDF',
                'digital-marketing.workspace'
            ]);

            $divisiOpen = Route::is([
                'client.produk_divisi',
                'barang_client.index',
                'social_creator.index',
                'blacklist.index',
                'tools.index',
                'gform_affiliate.index',
                'selection.index',
            ]);

            $isPdf = Route::is('PDF');

            $divisiActive =
                $divisiOpen ||
                ($isMarketingPage && $isDMProduct) ||
                $isDMClientManagement ||
                Route::is('multimedia.workspace') ||
                Route::is('idme.workspace') ||
                ($isMCNProduct && $isPdf) ||
                ($techProdukList && $isPdf) ||
                ($isEcommerceList && $isPdf);
        @endphp


        {{-- =================================================
            DIVISI
        ================================================== --}}
        @php
            $visibleDivisionMenuIds = $isSuperAdmin
                ? [1, 3, 5, 7, (int) $multimediaDivisionId]
                : $myDivisionIds;
            $hasVisibleDivisionMenu = count(array_intersect(
                [1, 3, 5, 7, (int) $multimediaDivisionId],
                $visibleDivisionMenuIds
            )) > 0;
        @endphp
        @if($isSuperAdmin && $hasVisibleDivisionMenu)
        <div class="mb-2">

            <button
                type="button"
                data-sidebar-collapse="collapseDivisi"
                data-sidebar-group="primary"
                class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition
                {{ $divisiActive
                    ? 'bg-zinc-900 text-white'
                    : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                }}"
                aria-expanded="{{ $divisiActive ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-3">

                    <svg
                        class="h-[18px] w-[18px] shrink-0 {{ $divisiActive ? 'text-white' : 'text-zinc-500' }}"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M4 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"
                        />
                    </svg>

                    <span class="font-semibold">
                        Divisi
                    </span>
                </span>

                <svg
                    class="sidebar-chevron h-4 w-4 shrink-0 text-zinc-600 transition-transform duration-200 {{ $divisiActive ? 'rotate-180' : '' }}"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M6 9l6 6 6-6"
                    />
                </svg>
            </button>


            <div
                id="collapseDivisi"
                class="{{ $divisiActive ? '' : 'hidden' }} mt-1 space-y-1 pl-3"
            >

                <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-2">
                    @include('partials.sidebar_division_items')
                </div>

            </div>
        </div>
        @elseif($hasVisibleDivisionMenu)
            <div class="mb-2 space-y-1">
                @include('partials.sidebar_division_items')
            </div>
        @endif


        {{-- =================================================
            BUSINESS DEVELOPMENT
        ================================================== --}}
        <div class="hidden" aria-hidden="true">

            @php
                $isBDOpen = $isBusinessDevWorkspace && $isBDProduct;
            @endphp

            <button
                type="button"
                data-sidebar-collapse="collapseBD"
                data-sidebar-group="primary"
                class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition
                {{ $isBDOpen
                    ? 'bg-zinc-900 text-white'
                    : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                }}"
                aria-expanded="{{ $isBDOpen ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-3">

                    <svg
                        class="h-[18px] w-[18px] text-zinc-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M3 10h18M5 10v9M9 10v9M15 10v9M19 10v9M3 20h18M4 7l8-4 8 4v3H4V7z"
                        />
                    </svg>

                    <span class="font-semibold">
                        Business Dev
                    </span>

                </span>

                <svg
                    class="sidebar-chevron h-4 w-4 text-zinc-600 transition-transform duration-200 {{ $isBDOpen ? 'rotate-180' : '' }}"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M6 9l6 6 6-6"
                    />
                </svg>

            </button>


            <div
                id="collapseBD"
                class="{{ $isBDOpen ? '' : 'hidden' }} mt-2 space-y-2 pl-3"
            >

                <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-2">

                    {{-- Brand --}}
                    <div class="mb-3 rounded-lg bg-white px-3 py-2 text-center text-[10px] font-black uppercase tracking-[0.15em] text-zinc-950">
                        MR. CREPE
                    </div>


                    {{-- CMS --}}
                    @if ($isSuperAdmin || ($myDivision->name == "Business Dev" && $myRole->level < 4))

                        <a
                            href="{{ route('cms.produk', 'BD') }}"
                            class="mb-3 flex items-center justify-center gap-2 rounded-lg bg-white px-3 py-2 text-[10px] font-bold uppercase tracking-wide text-zinc-950 transition hover:bg-zinc-200"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1-1.8 1.8-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 00-1 1.5v.1h-2.6v-.1a1.7 1.7 0 00-1-1.5 1.7 1.7 0 00-1.9.3l-.1.1-1.8-1.8.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H6.4v-2.6h.1a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9l-.1-.1 1.8-1.8.1.1a1.7 1.7 0 001.9.3 1.7 1.7 0 001-1.5V5.4h2.6v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1 1.8 1.8-.1.1a1.7 1.7 0 00-.3 1.9 1.7 1.7 0 001.5 1h.1V14h-.1a1.7 1.7 0 00-1.5 1z"
                                />
                            </svg>

                            Manajemen Client
                        </a>

                    @endif


                    @php
                        $isPowerUser =
                            $isSuperAdmin ||
                            ($myRole->role_name ?? '') == "Performance & Reporting";

                        $divisionIds =
                            $myDivision instanceof \Illuminate\Support\Collection
                                ? $myDivision->pluck('id')->toArray()
                                : [];

                        $isDigitalMarketing =
                            ($myDivision->name ?? '') == "Business Dev";

                        if($myDivision->name == 'All Divisions'){
                            $isDigitalMarketing = true;
                        }

                        $groupedProduk =
                            $businessDevProduk->groupBy('kategori_jasa_id');

                        $checkAccess = function($client) use (
                            $isPowerUser,
                            $divisionIds,
                            $myClientIds
                        ) {
                            return
                                $isPowerUser ||
                                (
                                    in_array(2, $divisionIds) &&
                                    in_array($client->client_id, $myClientIds)
                                );
                        };
                    @endphp


                    @php
                        $businessDevClients = $groupedProduk->flatten(1)->unique('client_id')->values();
                    @endphp

                    <div class="ml-1 space-y-1 border-l border-zinc-800 pl-2">
                        @forelse ($businessDevClients as $client)
                            @php
                                $canOpen = $checkAccess($client);
                                $isActiveClient = $isBusinessDevWorkspace && $currentProduct == $client->client_id;
                            @endphp

                            @if($canOpen)
                                <a
                                    href="{{ route('business-dev.workspace', ['id_produk' => $client->client_id]) }}"
                                    class="group flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-xs transition
                                    {{ $isActiveClient
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-400 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    <svg class="h-4 w-4 shrink-0 {{ $isActiveClient ? 'text-zinc-950' : 'text-zinc-600 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 19a4 4 0 00-8 0M11 11a3 3 0 100-6 3 3 0 000 6zM19 19a4 4 0 00-3-3.87M16 5a3 3 0 012 5.24" />
                                    </svg>
                                    <span class="truncate">{{ $client->nama }}</span>
                                </a>
                            @else
                                <div class="flex cursor-not-allowed items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-zinc-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 19a4 4 0 00-8 0M11 11a3 3 0 100-6 3 3 0 000 6z" />
                                    </svg>
                                    <span class="truncate">{{ $client->nama }}</span>
                                </div>
                            @endif
                        @empty
                            <div class="rounded-lg px-2 py-2 text-[10px] italic text-zinc-600">
                                Tidak ada data client tersedia.
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>

        </div>


        {{-- =================================================
            HUMAN CAPITAL
        ================================================== --}}
        @if($isSuperAdmin || in_array(4, $myDivisionIds, true))
        @php
            $isHCPage = Route::is([
                'hc_daily.index',
                'hc_overview.list',
                'overview.dashboard_kpi',
                'overview.dashboard'
            ]);

            $isKPIActive = Route::is([
                'overview.dashboard_kpi',
                'hc_overview.list'
            ]);

            $isHarianActive = Route::is([
                'overview.dashboard',
                'hc_daily.index'
            ]);
        @endphp


        <div class="mt-5 border-t border-zinc-800 pt-4">

            <button
                type="button"
                data-sidebar-collapse="collapseHC"
                data-sidebar-group="primary"
                class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition
                {{ $isHCPage
                    ? 'bg-zinc-900 text-white'
                    : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                }}"
                aria-expanded="{{ $isHCPage ? 'true' : 'false' }}"
            >

                <span class="flex items-center gap-3">

                    <svg
                        class="h-[18px] w-[18px] text-zinc-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M16 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M9.5 11a4 4 0 100-8 4 4 0 000 8zM17 8h4M19 6v4"
                        />
                    </svg>

                    <span class="font-semibold">
                        Human Capital
                    </span>

                </span>

                <svg
                    class="sidebar-chevron h-4 w-4 text-zinc-600 transition-transform duration-200 {{ $isHCPage ? 'rotate-180' : '' }}"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M6 9l6 6 6-6"
                    />
                </svg>

            </button>


            <div
                id="collapseHC"
                class="{{ $isHCPage ? '' : 'hidden' }} mt-1 space-y-2 pl-3"
            >

                {{-- KPI --}}
                <div>

                    <button
                        type="button"
                        data-sidebar-collapse="collapseSubKPI"
                        class="flex w-full items-center justify-between rounded-lg bg-zinc-900/50 px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                        aria-expanded="{{ $isKPIActive ? 'true' : 'false' }}"
                    >
                        <span>KPI</span>

                        <svg
                            class="sidebar-chevron h-3.5 w-3.5 text-zinc-600 transition-transform duration-200 {{ $isKPIActive ? 'rotate-180' : '' }}"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M6 9l6 6 6-6"
                            />
                        </svg>
                    </button>


                    <div
                        id="collapseSubKPI"
                        class="{{ $isKPIActive ? '' : 'hidden' }} ml-2 mt-1 space-y-1 border-l border-zinc-800 pl-2"
                    >

                        <a
                            href="{{ route('overview.dashboard_kpi') }}"
                            class="block rounded-lg px-3 py-2 text-xs transition
                            {{ Route::is('overview.dashboard_kpi')
                                ? 'bg-white font-semibold text-zinc-950'
                                : 'text-zinc-500 hover:bg-zinc-900 hover:text-white'
                            }}"
                        >
                            Dashboard
                        </a>

                        <a
                            href="{{ route('hc_overview.list', ['readonly' => 1]) }}"
                            class="block rounded-lg px-3 py-2 text-xs transition
                            {{ Route::is('hc_overview.list')
                                ? 'bg-white font-semibold text-zinc-950'
                                : 'text-zinc-500 hover:bg-zinc-900 hover:text-white'
                            }}"
                        >
                            Overview
                        </a>

                    </div>

                </div>


                {{-- Harian --}}
                <div>

                    <button
                        type="button"
                        data-sidebar-collapse="collapseSubHarian"
                        class="flex w-full items-center justify-between rounded-lg bg-zinc-900/50 px-3 py-2 text-left text-[10px] font-bold uppercase tracking-wide text-zinc-400 transition hover:bg-zinc-900 hover:text-white"
                        aria-expanded="{{ $isHarianActive ? 'true' : 'false' }}"
                    >
                        <span>Harian</span>

                        <svg
                            class="sidebar-chevron h-3.5 w-3.5 text-zinc-600 transition-transform duration-200 {{ $isHarianActive ? 'rotate-180' : '' }}"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M6 9l6 6 6-6"
                            />
                        </svg>
                    </button>


                    <div
                        id="collapseSubHarian"
                        class="{{ $isHarianActive ? '' : 'hidden' }} ml-2 mt-1 space-y-1 border-l border-zinc-800 pl-2"
                    >

                        <a
                            href="{{ route('overview.dashboard') }}"
                            class="block rounded-lg px-3 py-2 text-xs transition
                            {{ Route::is('overview.dashboard')
                                ? 'bg-white font-semibold text-zinc-950'
                                : 'text-zinc-500 hover:bg-zinc-900 hover:text-white'
                            }}"
                        >
                            Dashboard
                        </a>

                        <a
                            href="{{ route('hc_daily.index') }}"
                            class="block rounded-lg px-3 py-2 text-xs transition
                            {{ Route::is('hc_daily.index')
                                ? 'bg-white font-semibold text-zinc-950'
                                : 'text-zinc-500 hover:bg-zinc-900 hover:text-white'
                            }}"
                        >
                            Daily Work
                        </a>

                    </div>

                </div>

            </div>

        </div>
        @endif


        {{-- =================================================
            FINANCE
        ================================================== --}}
        @if($isSuperAdmin || in_array(6, $myDivisionIds, true))

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
                    'audit.index',
                    'perusahaan.index'
                ]);
            @endphp


            <div class="mt-5 border-t border-zinc-800 pt-4">

                <button
                    type="button"
                    data-sidebar-collapse="collapseFinance"
                    data-sidebar-group="primary"
                    class="group flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left text-sm transition
                    {{ $isFinancePage
                        ? 'bg-zinc-900 text-white'
                        : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    }}"
                    aria-expanded="{{ $isFinancePage ? 'true' : 'false' }}"
                >

                    <span class="flex items-center gap-3">

                        <svg
                            class="h-[18px] w-[18px] text-zinc-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 3v18M17 7.5c0-1.7-2.2-3-5-3s-5 1.3-5 3 2.2 3 5 3 5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"
                            />
                        </svg>

                        <span class="font-semibold">
                            Finance
                        </span>

                    </span>

                    <svg
                        class="sidebar-chevron h-4 w-4 text-zinc-600 transition-transform duration-200 {{ $isFinancePage ? 'rotate-180' : '' }}"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M6 9l6 6 6-6"
                        />
                    </svg>

                </button>


                <div
                    id="collapseFinance"
                    class="{{ $isFinancePage ? '' : 'hidden' }} mt-1 space-y-2 pl-3"
                >

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-2">

                        {{-- Master perusahaan khusus Finance, bukan produk client. --}}
                        @if($canManageFinanceCompanies)
                            <a
                                href="{{ route('perusahaan.index') }}"
                                class="mb-2 flex items-center rounded-lg px-3 py-2 text-xs font-semibold transition
                                {{ Route::is('perusahaan.index')
                                    ? 'bg-white text-zinc-950'
                                    : 'text-zinc-400 hover:bg-zinc-800 hover:text-white'
                                }}"
                            >
                                Master Perusahaan
                            </a>
                        @endif


                        @foreach ($perusahaan as $pe)

                            <button
                                type="button"
                                data-sidebar-collapse="cat_perusahaan_{{ $pe->id }}"
                                class="mb-1 flex w-full items-center justify-between rounded-lg bg-zinc-800/70 px-3 py-2 text-left text-xs font-semibold text-zinc-300 transition hover:bg-zinc-800 hover:text-white"
                                aria-expanded="false"
                            >
                                <span class="truncate">
                                    {{ $pe->nama_perusahaan }}
                                </span>

                                <svg
                                    class="sidebar-chevron h-3.5 w-3.5 shrink-0 text-zinc-600 transition-transform duration-200"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M6 9l6 6 6-6"
                                    />
                                </svg>
                            </button>


                            <div
                                id="cat_perusahaan_{{ $pe->id }}"
                                class="hidden mb-3 ml-2 space-y-1 border-l border-zinc-700 pl-2"
                            >

                                <a
                                    href="{{ route('finance.dashboard', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('finance.dashboard')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Dashboard
                                </a>


                                <a
                                    href="{{ route('finance_coa.index', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('finance_coa.index')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Daftar Akun
                                </a>


                                <a
                                    href="{{ route('general_journal.index', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('general_journal.index')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Jurnal Umum
                                </a>


                                <a
                                    href="{{ route('finance_ledger.list_account', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('finance_ledger.list_account')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Buku Besar
                                </a>


                                <a
                                    href="{{ route('Ledger.neraca_saldo', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('Ledger.neraca_saldo')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Neraca Saldo
                                </a>


                                <a
                                    href="{{ route('adjustment_journal.index', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('adjustment_journal.index')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Jurnal Penyesuaian
                                </a>


                                <a
                                    href="{{ route('Ledger.neraca_lajur', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('Ledger.neraca_lajur')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Neraca Lajur
                                </a>


                                <a
                                    href="{{ route('Ledger.arus_kas', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('Ledger.arus_kas')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Laporan Keuangan
                                </a>


                                <a
                                    href="{{ route('Ledger.jurnal_penutup', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('Ledger.jurnal_penutup')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Jurnal Tutup Akun
                                </a>


                                <a
                                    href="{{ route('invoice.index', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('invoice.index')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Pembayaran
                                </a>


                                <a
                                    href="{{ route('profit.list', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('profit.list')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Profit
                                </a>


                                <a
                                    href="{{ route('payroll.index', $pe->id) }}"
                                    class="block rounded-lg px-3 py-2 text-xs transition
                                    {{ Route::is('payroll.index')
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >
                                    Payroll
                                </a>


                                @if(Auth::user()->isAdmin == true)

                                    <a
                                        href="{{ route('audit.index', $pe->id) }}"
                                        class="block rounded-lg px-3 py-2 text-xs transition
                                        {{ Route::is('audit.index')
                                            ? 'bg-white font-semibold text-zinc-950'
                                            : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                        }}"
                                    >
                                        Audit
                                    </a>

                                @endif

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        @endif


        {{-- =================================================
            SUPER ADMIN
        ================================================== --}}
            <div class="mt-5 border-t border-zinc-800 pt-4">
                @if($isSuperAdmin)
                <div class="mb-2 px-3 text-[9px] font-bold uppercase tracking-[0.18em] text-zinc-600">
                    Administration
                </div>
                @endif


                <a
                    href="{{ route('monitoring.index') }}"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                    {{ Route::is('monitoring.*')
                        ? 'bg-white font-semibold text-zinc-950'
                        : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    }}"
                >
                    <svg
                        class="h-[18px] w-[18px]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M4 7a3 3 0 013-3h6a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M16 10l4-2v8l-4-2v-4zM8 8v4m0 4h.01"
                        />
                    </svg>

                    Monitoring
                </a>

                @if($isSuperAdmin)
                <a
                    href="{{ route('activity-logs.index') }}"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                    {{ Route::is('activity-logs.*')
                        ? 'bg-white font-semibold text-zinc-950'
                        : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    }}"
                >
                    <svg
                        class="h-[18px] w-[18px]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M12 8v4l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    Activity Log
                </a>


                <a
                    href="{{ route('manajemen_user.index') }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                    {{ Route::is('manajemen_user.index')
                        ? 'bg-white font-semibold text-zinc-950'
                        : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                    }}"
                >
                    <svg
                        class="h-[18px] w-[18px]"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.8"
                            d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM17 11a3 3 0 100-6M22 21v-2a4 4 0 00-3-3.87"
                        />
                    </svg>

                    Manajemen User
                </a>
                @endif

            </div>


        {{-- =================================================
            CRM
        ================================================== --}}
        <div class="mt-5 border-t border-zinc-800 pt-4">

            <a
                href="{{ route('crm.index') }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition
                {{ Route::is('crm.index')
                    ? 'bg-white font-semibold text-zinc-950'
                    : 'text-zinc-400 hover:bg-zinc-900 hover:text-white'
                }}"
            >

                <svg
                    class="h-[18px] w-[18px] shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M10 10a4 4 0 100-8 4 4 0 000 8zM21 20v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                    />
                </svg>

                <span class="font-medium">
                    Customer Relationship Management
                </span>

            </a>

        </div>

    </div>

</aside>


{{-- =========================================================
    MOBILE SIDEBAR BACKDROP
========================================================= --}}
<div
    id="sidebarBackdrop"
    class="fixed inset-0 z-[1090] hidden bg-black/55 lg:hidden"
></div>


{{-- =========================================================
    SIDEBAR JAVASCRIPT
========================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | COLLAPSIBLE MENUS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('[data-sidebar-collapse]').forEach(function (button) {

        const targetId = button.getAttribute('data-sidebar-collapse');
        const target = document.getElementById(targetId);

        if (!target) {
            return;
        }

        const initiallyOpen = button.getAttribute('aria-expanded') === 'true';

        target.classList.add('overflow-hidden');
        target.style.transition = 'height 280ms ease, opacity 220ms ease, transform 280ms ease';

        if (initiallyOpen) {
            target.classList.remove('hidden');
            target.style.height = 'auto';
            target.style.opacity = '1';
            target.style.transform = 'translateY(0)';
        } else {
            target.classList.add('hidden');
            target.style.height = '0px';
            target.style.opacity = '0';
            target.style.transform = 'translateY(-6px)';
        }

        button.addEventListener('click', function () {

            const willOpen = this.getAttribute('aria-expanded') !== 'true';
            const group = this.getAttribute('data-sidebar-group');

            if (willOpen && group) {
                document.querySelectorAll(`[data-sidebar-group="${CSS.escape(group)}"]`).forEach(function (otherButton) {
                    if (otherButton === button || otherButton.getAttribute('aria-expanded') !== 'true') {
                        return;
                    }

                    const otherTarget = document.getElementById(otherButton.getAttribute('data-sidebar-collapse'));
                    otherButton.setAttribute('aria-expanded', 'false');

                    if (otherTarget) {
                        otherTarget.classList.remove('hidden');
                        otherTarget.style.height = otherTarget.scrollHeight + 'px';

                        requestAnimationFrame(function () {
                            otherTarget.style.height = '0px';
                            otherTarget.style.opacity = '0';
                            otherTarget.style.transform = 'translateY(-6px)';
                        });
                    }

                    const otherChevron = otherButton.querySelector('.sidebar-chevron');
                    otherChevron?.classList.remove('rotate-180', 'rotate-90');
                });
            }

            this.setAttribute(
                'aria-expanded',
                willOpen ? 'true' : 'false'
            );

            if (willOpen) {
                target.classList.remove('hidden');
                target.style.height = '0px';
                target.style.opacity = '0';
                target.style.transform = 'translateY(-6px)';

                requestAnimationFrame(function () {
                    target.style.height = target.scrollHeight + 'px';
                    target.style.opacity = '1';
                    target.style.transform = 'translateY(0)';
                });
            } else {
                target.style.height = target.scrollHeight + 'px';

                requestAnimationFrame(function () {
                    target.style.height = '0px';
                    target.style.opacity = '0';
                    target.style.transform = 'translateY(-6px)';
                });
            }

            const chevron =
                this.querySelector('.sidebar-chevron');

            if (chevron) {
                chevron.classList.toggle(
                    'rotate-180',
                    willOpen
                );

                /*
                 * Client arrows are slightly different.
                 */
                if (targetId.startsWith('client_bd_')) {
                    chevron.classList.toggle(
                        'rotate-90',
                        willOpen
                    );
                    chevron.classList.remove(
                        'rotate-180'
                    );
                }
            }

        });

        target.addEventListener('transitionend', function (event) {
            if (event.propertyName !== 'height') {
                return;
            }

            const isOpen = button.getAttribute('aria-expanded') === 'true';

            if (isOpen) {
                target.style.height = 'auto';
            } else {
                target.classList.add('hidden');
            }
        });

    });


    /*
    |--------------------------------------------------------------------------
    | SIDEBAR RESPONSIVE
    |--------------------------------------------------------------------------
    */

    const sidebar = document.getElementById('accordionSidebar');
    const sidebarToggle = document.getElementById('sidebarToggleTop');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const desktopQuery = window.matchMedia('(min-width: 1025px)');

    function isDesktopSidebar() {
        return desktopQuery.matches;
    }

    function activateCurrentSidebarMenu() {
        if (!sidebar) return;

        const currentUrl = new URL(window.location.href);
        const links = Array.from(sidebar.querySelectorAll('a[href]:not([data-sidebar-brand])'));
        const activeLink = links.find(function (link) {
            const href = link.getAttribute('href');
            if (!href || href === '#') return false;

            try {
                const linkUrl = new URL(href, window.location.origin);
                return linkUrl.pathname.replace(/\/$/, '') === currentUrl.pathname.replace(/\/$/, '')
                    && (!linkUrl.search || linkUrl.search === currentUrl.search);
            } catch (error) {
                return false;
            }
        });

        if (!activeLink) return;

        activeLink.dataset.sidebarActive = 'true';
        activeLink.setAttribute('aria-current', 'page');

        let ancestor = activeLink.parentElement;
        while (ancestor && ancestor !== sidebar) {
            if (ancestor.id) {
                const controller = sidebar.querySelector(
                    `[data-sidebar-collapse="${CSS.escape(ancestor.id)}"]`
                );

                if (controller) {
                    ancestor.classList.remove('hidden');
                    ancestor.style.height = 'auto';
                    ancestor.style.opacity = '1';
                    ancestor.style.transform = 'translateY(0)';
                    controller.setAttribute('aria-expanded', 'true');
                    controller.dataset.sidebarParentActive = 'true';

                    const chevron = controller.querySelector('.sidebar-chevron');
                    if (chevron) {
                        if (ancestor.id.startsWith('client_bd_')) {
                            chevron.classList.add('rotate-90');
                            chevron.classList.remove('rotate-180');
                        } else {
                            chevron.classList.add('rotate-180');
                        }
                    }
                }
            }

            ancestor = ancestor.parentElement;
        }

        window.requestAnimationFrame(function () {
            activeLink.scrollIntoView({ block: 'nearest' });
        });
    }

    function setToggleState(isOpen) {
        sidebarToggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        sidebarToggle?.setAttribute('aria-label', isOpen ? 'Tutup sidebar' : 'Buka sidebar');
    }

    function openSidebar(options = {}) {
        if (!sidebar) return;

        if (isDesktopSidebar()) {
            document.body.classList.remove('sidebar-collapsed');
            if (options.persist !== false) localStorage.setItem('lmg-sidebar-collapsed', 'false');
        } else {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            sidebarBackdrop?.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        setToggleState(true);
    }

    function closeSidebar(options = {}) {
        if (!sidebar) return;

        if (isDesktopSidebar()) {
            document.body.classList.add('sidebar-collapsed');
            if (options.persist !== false) localStorage.setItem('lmg-sidebar-collapsed', 'true');
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            sidebarBackdrop?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        setToggleState(false);
    }

    function syncSidebarToViewport() {
        document.body.classList.remove('overflow-hidden');
        sidebarBackdrop?.classList.add('hidden');

        if (isDesktopSidebar()) {
            sidebar.classList.remove('-translate-x-full', 'translate-x-0');
            document.body.classList.remove('sidebar-collapsed');
            localStorage.setItem('lmg-sidebar-collapsed', 'false');
            setToggleState(true);
        } else {
            document.body.classList.remove('sidebar-collapsed');
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            setToggleState(false);
        }
    }

    sidebarToggle?.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const isOpen = isDesktopSidebar()
            ? !document.body.classList.contains('sidebar-collapsed')
            : sidebar.classList.contains('translate-x-0');

        isOpen ? closeSidebar() : openSidebar();
    });

    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarBackdrop?.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });

    sidebar?.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!isDesktopSidebar()) closeSidebar();
        });
    });

    desktopQuery.addEventListener('change', syncSidebarToViewport);
    activateCurrentSidebarMenu();
    syncSidebarToViewport();

});
</script>
