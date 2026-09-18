{{-- =========================================================
    SIDEBAR MCN
    Tailwind CSS / LMG Style
========================================================= --}}

@php
    // Daftar semua route yang termasuk di bawah menu MCN
    $isMCNPage = Route::is([
        'barang_client.index',
        'social_creator.index',
        'blacklist.index',
        'tools.index',
        'gform_affiliate.index',
        'selection.index',
    ]);

    $isMCNCMS = Route::is('client.produk_divisi')
        && request()->route('division') == 3;

    $isMCNProduct = $mcnProduk->contains(
        'client_id',
        $currentProduct
    );

    $isPdf = Route::is('PDF');
    $isMCNWorkspace = Route::is('division-client.workspace')
        && request()->route('division') === 'mcn';

    $mcnOpen =
        $isMCNPage ||
        $isMCNWorkspace ||
        ($isMCNProduct && $isPdf) ||
        $isMCNCMS;

    $canManageMCN =
        $isSuperAdmin ||
        ($myDivision->name == "MCN" && $myRole->level < 4);
@endphp


{{-- =========================================================
    MCN HEADER
========================================================= --}}

<div class="px-1">

    <button
        type="button"
        data-sidebar-collapse="collapseMCN"
        data-sidebar-group="division"
        class="w-full group flex items-center justify-between
               px-3 py-2.5 rounded-lg
               text-left text-sm font-semibold
               text-zinc-300
               hover:bg-white/5 hover:text-white
               transition-colors duration-200
               {{ $mcnOpen ? 'bg-white/5 text-white' : '' }}"
        aria-expanded="{{ $mcnOpen ? 'true' : 'false' }}"
    >

        <span class="flex items-center min-w-0">

            {{-- MCN Icon --}}
            <svg
                class="w-4 h-4 mr-3 shrink-0
                       {{ $mcnOpen
                            ? 'text-white'
                            : 'text-zinc-500 group-hover:text-zinc-300' }}"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1"
                />
                <circle cx="12" cy="7" r="4" />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16 11a4 4 0 000-8M8 11a4 4 0 010-8"
                />
            </svg>

            <span class="truncate">
                MCN
            </span>

        </span>


        {{-- Chevron --}}
        <svg
            class="sidebar-chevron w-4 h-4 shrink-0
                   text-zinc-500
                   transition-transform duration-200
                   {{ $mcnOpen ? 'rotate-180' : '' }}"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.7"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m19 9-7 7-7-7"
            />
        </svg>

    </button>


    {{-- =========================================================
        MCN CONTENT
    ========================================================== --}}

    <div
        id="collapseMCN"
        class="{{ $mcnOpen ? '' : 'hidden' }}"
    >

        <div class="mt-1 ml-2 pl-3 border-l border-zinc-800 space-y-1">


            {{-- =================================================
                DASHBOARD CMS
            ================================================== --}}

            @if ($canManageMCN)

                <a
                    href="{{ route('client.produk_divisi', 3) }}"
                    class="group flex items-center gap-2.5
                           px-3 py-2 rounded-md
                           text-sm
                           transition-colors duration-200
                           {{ $isMCNCMS
                                ? 'bg-white text-black font-semibold'
                                : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                >

                    {{-- Settings --}}
                    <svg
                        class="w-4 h-4 shrink-0
                               {{ $isMCNCMS
                                    ? 'text-black'
                                    : 'text-zinc-500 group-hover:text-zinc-300' }}"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M10.325 4.317a1.724 1.724 0 013.35 0
                               1.724 1.724 0 002.573 1.066
                               1.724 1.724 0 002.973 1.675
                               1.724 1.724 0 001.066 2.573
                               1.724 1.724 0 010 3.35
                               1.724 1.724 0 00-1.066 2.573
                               1.724 1.724 0 01-2.973 1.675
                               1.724 1.724 0 00-2.573 1.066
                               1.724 1.724 0 01-3.35 0
                               1.724 1.724 0 00-2.573-1.066
                               1.724 1.724 0 01-2.973-1.675
                               1.724 1.724 0 00-1.066-2.573
                               1.724 1.724 0 010-3.35
                               1.724 1.724 0 001.066-2.573
                               1.724 1.724 0 01-2.973-1.675
                               1.724 1.724 0 00-2.573-1.066z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>

                    <span>
                        Dashboard CMS
                    </span>

                </a>


                {{-- =================================================
                    CLIENT TOGGLE
                ================================================== --}}

                <button
                    type="button"
                    data-sidebar-collapse="cat_mcn"
                    class="w-full group flex items-center justify-between
                           px-3 py-2 rounded-md
                           text-sm font-medium
                           transition-colors duration-200
                           {{ $isPdf
                                ? 'bg-white/5 text-white'
                                : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                    aria-expanded="{{ $isPdf ? 'true' : 'false' }}"
                >

                    <span class="flex items-center">

                        {{-- Folder --}}
                        <svg
                            class="w-4 h-4 mr-2.5 shrink-0
                                   {{ $isPdf
                                        ? 'text-white'
                                        : 'text-zinc-500 group-hover:text-zinc-300' }}"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
                            />
                        </svg>

                        <span>
                            Client
                        </span>

                    </span>

                    <svg
                        class="sidebar-chevron w-3.5 h-3.5 shrink-0
                               text-zinc-600
                               transition-transform duration-200
                               {{ $isPdf ? 'rotate-180' : '' }}"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19 9-7 7-7-7"
                        />
                    </svg>

                </button>

            @endif


            {{-- =================================================
                MCN CLIENT LIST
            ================================================== --}}

            <div
                id="cat_mcn"
                class="{{ $isPdf ? '' : 'hidden' }}"
            >

                <div class="ml-2 space-y-1 border-l border-zinc-800 pl-2">

                    @foreach ($mcnProduk as $client)

                        @if($myDivision->name == "MCN" || $isSuperAdmin)

                            @php

                                // User memiliki akses eksplisit
                                $hasClientAccess = in_array(
                                    $client->client_id,
                                    $myClientIds
                                );

                                // Power User
                                $isPowerUser =
                                    $isSuperAdmin ||
                                    $myRole->role_name == "Busines Unit Head";

                                // Permission membuka client
                                $canOpen =
                                    $isPowerUser ||
                                    (
                                        in_array(
                                            1,
                                            $myDivision->pluck('id')->toArray()
                                        )
                                        && $hasClientAccess
                                    );

                                // Active client
                                $isActiveClient =
                                    $currentProduct == $client->client_id;

                            @endphp


                            <div class="mb-1">

                                @if($canOpen)

                                    {{-- =================================================
                                        CLIENT HEADER
                                    ================================================== --}}

                                    <a
                                        href="{{ route('division-client.workspace', ['division' => 'mcn', 'id_produk' => $client->id]) }}"
                                        class="w-full group flex items-center justify-between
                                               px-3 py-2 rounded-md
                                               text-left text-sm
                                               transition-colors duration-200
                                               {{ $isActiveClient
                                                    ? 'bg-white/5 text-white font-medium'
                                                    : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                                        aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}"
                                    >

                                        <span class="flex items-center min-w-0">

                                            {{-- Client Icon --}}
                                            <svg
                                                class="w-4 h-4 mr-2.5 shrink-0
                                                       {{ $isActiveClient
                                                            ? 'text-white'
                                                            : 'text-zinc-600 group-hover:text-zinc-400' }}"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="8"
                                                    r="3"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M5 20a7 7 0 0114 0"
                                                />
                                            </svg>

                                            <span class="truncate">
                                                {{ $client->nama }}
                                            </span>

                                        </span>


                                        <svg
                                            class="sidebar-chevron w-3.5 h-3.5 shrink-0
                                                   text-zinc-600
                                                   transition-transform duration-200
                                                   {{ $isActiveClient ? 'rotate-180' : '' }}"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m19 9-7 7-7-7"
                                            />
                                        </svg>

                                    </a>


                                    {{-- =================================================
                                        CLIENT SUB MENU
                                    ================================================== --}}

                                    <div
                                        id="client_mcn_{{ $client->client_id }}"
                                        class="hidden"
                                    >

                                        <div class="ml-4 pl-3 py-1 border-l border-zinc-800">

                                            <a
                                                href="{{ route('PDF', ['id_produk' => $client->id, 'division_id' => 3]) }}"
                                                class="flex items-center
                                                       px-3 py-2 rounded-md
                                                       text-sm
                                                       transition-colors duration-200
                                                       {{ Route::is('PDF') && $isActiveClient
                                                            ? 'bg-white text-black font-medium'
                                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                                            >

                                                <svg
                                                    class="w-4 h-4 mr-2.5 shrink-0
                                                           {{ Route::is('PDF') && $isActiveClient
                                                                ? 'text-black'
                                                                : 'text-zinc-600' }}"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M14 2v6h6M8 13h8M8 17h6"
                                                    />
                                                </svg>

                                                <span>
                                                    File Dokumen
                                                </span>

                                            </a>

                                        </div>

                                    </div>

                                @else

                                    {{-- =================================================
                                        NO ACCESS
                                    ================================================== --}}

                                    <div
                                        class="flex items-center
                                               px-3 py-2 rounded-md
                                               text-sm text-zinc-700
                                               cursor-not-allowed"
                                        title="Anda tidak memiliki akses ke client ini"
                                    >

                                        <svg
                                            class="w-4 h-4 mr-2.5 shrink-0"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="9"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                d="M9 9l6 6m0-6l-6 6"
                                            />
                                        </svg>

                                        <span class="truncate">
                                            {{ $client->nama }}
                                        </span>

                                    </div>

                                @endif

                            </div>

                        @endif

                    @endforeach

                </div>

            </div>


            {{-- =================================================
                MCN TOOLS / MANAGEMENT
            ================================================== --}}

            @if($myDivision->name == "MCN" || $isSuperAdmin)

                <div class="pt-2 mt-2 border-t border-zinc-800">


                    {{-- Project MCN --}}
                    <a
                        href="{{ route('barang_client.index') }}"
                        class="group flex items-center
                               px-3 py-2 rounded-md
                               text-sm
                               transition-colors duration-200
                               {{ Route::is('barang_client.index')
                                    ? 'bg-white text-black font-medium'
                                    : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                    >

                        <svg
                            class="w-4 h-4 mr-2.5 shrink-0
                                   {{ Route::is('barang_client.index')
                                        ? 'text-black'
                                        : 'text-zinc-600 group-hover:text-zinc-400' }}"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 7h16M4 7a2 2 0 01-2-2v12a2 2 0 002 2h16a2 2 0 002-2V5a2 2 0 00-2-2H4"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 11h8M8 15h5"
                            />
                        </svg>

                        <span>
                            Project MCN
                        </span>

                    </a>


                    {{-- Affiliator --}}
                    <a
                        href="{{ route('social_creator.index') }}"
                        class="group flex items-center
                               px-3 py-2 rounded-md
                               text-sm
                               transition-colors duration-200
                               {{ Route::is('social_creator.index')
                                    ? 'bg-white text-black font-medium'
                                    : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                    >

                        <svg
                            class="w-4 h-4 mr-2.5 shrink-0
                                   {{ Route::is('social_creator.index')
                                        ? 'text-black'
                                        : 'text-zinc-600 group-hover:text-zinc-400' }}"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <circle cx="9" cy="8" r="3" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 20a6 6 0 0112 0"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M17 11a3 3 0 100-6M21 20a4 4 0 00-4-4"
                            />
                        </svg>

                        <span>
                            Affiliator
                        </span>

                    </a>


                    {{-- Blacklist --}}
                    <a
                        href="{{ route('blacklist.index') }}"
                        class="group flex items-center
                               px-3 py-2 rounded-md
                               text-sm
                               transition-colors duration-200
                               {{ Route::is('blacklist.index')
                                    ? 'bg-white text-black font-medium'
                                    : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                    >

                        <svg
                            class="w-4 h-4 mr-2.5 shrink-0
                                   {{ Route::is('blacklist.index')
                                        ? 'text-black'
                                        : 'text-zinc-600 group-hover:text-zinc-400' }}"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <circle cx="12" cy="12" r="9" />
                            <path
                                stroke-linecap="round"
                                d="M7 7l10 10"
                            />
                        </svg>

                        <span>
                            Blacklist
                        </span>

                    </a>


                    {{-- Tools --}}
                    <a
                        href="{{ route('tools.index') }}"
                        class="group flex items-center
                               px-3 py-2 rounded-md
                               text-sm
                               transition-colors duration-200
                               {{ Route::is('tools.index')
                                    ? 'bg-white text-black font-medium'
                                    : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                    >

                        <svg
                            class="w-4 h-4 mr-2.5 shrink-0
                                   {{ Route::is('tools.index')
                                        ? 'text-black'
                                        : 'text-zinc-600 group-hover:text-zinc-400' }}"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14.7 6.3a4 4 0 01-5.66 5.66L4 17v3h3l5.04-5.04a4 4 0 005.66-5.66L14 12"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16 4l4 4"
                            />
                        </svg>

                        <span>
                            Tools
                        </span>

                    </a>

                </div>

            @endif


            {{-- =================================================
                MCN PROJECT LIST
            ================================================== --}}

            @foreach ($mcnClient as $item)

                @php
                    $isItemActive = request()->is(
                        'project/' . $item->id . '*'
                    );

                    $isProjectOpen =
                        request()->segment(2) == $item->id;
                @endphp


                <div class="mt-1">


                    {{-- =================================================
                        PROJECT HEADER
                    ================================================== --}}

                    <button
                        type="button"
                        data-sidebar-collapse="client_mcn_project_{{ $item->id }}"
                        class="w-full group flex items-center justify-between
                               px-3 py-2 rounded-md
                               text-left text-sm
                               transition-colors duration-200
                               {{ $isItemActive
                                    ? 'bg-white/5 text-white font-medium'
                                    : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                        aria-expanded="{{ $isItemActive ? 'true' : 'false' }}"
                    >

                        <span class="flex items-center min-w-0">

                            {{-- Project Icon --}}
                            <svg
                                class="w-4 h-4 mr-2.5 shrink-0
                                       {{ $isItemActive
                                            ? 'text-white'
                                            : 'text-zinc-600 group-hover:text-zinc-400' }}"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <circle
                                    cx="12"
                                    cy="8"
                                    r="3"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 20a7 7 0 0114 0"
                                />
                            </svg>

                            <span class="uppercase truncate font-medium">
                                {{ strtoupper($item->project) }}
                            </span>

                        </span>


                        <svg
                            class="sidebar-chevron w-3.5 h-3.5 shrink-0
                                   text-zinc-600
                                   transition-transform duration-200
                                   {{ $isProjectOpen ? 'rotate-180' : '' }}"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m19 9-7 7-7-7"
                            />
                        </svg>

                    </button>


                    {{-- =================================================
                        PROJECT SUB MENU
                    ================================================== --}}

                    <div
                        id="client_mcn_project_{{ $item->id }}"
                        class="{{ $isProjectOpen ? '' : 'hidden' }}"
                    >

                        <div class="ml-4 pl-3 py-1 border-l border-zinc-800">


                            {{-- GFORM --}}
                            <a
                                href="{{ route('gform_affiliate.index', ['project_id' => $item->id]) }}"
                                class="flex items-center
                                       px-3 py-2 rounded-md
                                       text-sm
                                       transition-colors duration-200
                                       {{ request()->is('gform_affiliate/' . $item->id)
                                            ? 'bg-white text-black font-medium'
                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                            >

                                <svg
                                    class="w-4 h-4 mr-2.5 shrink-0
                                           {{ request()->is('gform_affiliate/' . $item->id)
                                                ? 'text-black'
                                                : 'text-zinc-600' }}"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 3h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5a2 2 0 012-2z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        d="M8 8h8M8 12h8M8 16h5"
                                    />
                                </svg>

                                <span>
                                    Gform
                                </span>

                            </a>


                            {{-- HASIL SELEKSI --}}
                            <a
                                href="{{ route('selection.index', ['project_id' => $item->id]) }}"
                                class="flex items-center
                                       px-3 py-2 rounded-md
                                       text-sm
                                       transition-colors duration-200
                                       {{ request()->is('selection/' . $item->id)
                                            ? 'bg-white text-black font-medium'
                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                            >

                                <svg
                                    class="w-4 h-4 mr-2.5 shrink-0
                                           {{ request()->is('selection/' . $item->id)
                                                ? 'text-black'
                                                : 'text-zinc-600' }}"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 11l3 3L22 4"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"
                                    />
                                </svg>

                                <span>
                                    Hasil Seleksi
                                </span>

                            </a>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>
