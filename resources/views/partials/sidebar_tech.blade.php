{{-- =========================================================
    SIDEBAR TECH
    Tailwind CSS / LMG Style
========================================================= --}}

@php
    $techProdukList = $techProduk->contains(
        'client_id',
        $currentProduct
    );

    $isPdf = Route::is('PDF');
    $isTechWorkspace = Route::is('division-client.workspace')
        && request()->route('division') === 'tech';

    $isTechCMS =
        Route::is('client.produk_divisi') &&
        request()->route('division') == 5;

    $techOpen =
        ($techProdukList && $isPdf) ||
        $isTechWorkspace ||
        $isTechCMS;

    $canManageTech =
        $isSuperAdmin ||
        ($myDivision->name == "Tech" && $myRole->level < 2);
@endphp


{{-- =========================================================
    TECH HEADER
========================================================= --}}

<div class="px-1">

    <button
        type="button"
        data-sidebar-collapse="collapseTech"
        data-sidebar-group="division"
        class="w-full group flex items-center justify-between
               px-3 py-2.5 rounded-lg
               text-left text-sm font-semibold
               text-zinc-300
               hover:bg-white/5 hover:text-white
               transition-colors duration-200
               {{ $techOpen ? 'bg-white/5 text-white' : '' }}"
        aria-expanded="{{ $techOpen ? 'true' : 'false' }}"
    >

        <span class="flex items-center min-w-0">

            {{-- Tech Icon --}}
            <svg
                class="w-4 h-4 mr-3 shrink-0
                       {{ $techOpen
                            ? 'text-white'
                            : 'text-zinc-500 group-hover:text-zinc-300' }}"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <rect
                    x="3"
                    y="4"
                    width="18"
                    height="13"
                    rx="2"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8 21h8M12 17v4"
                />
            </svg>

            <span class="truncate">
                Tech
            </span>

        </span>


        {{-- Chevron --}}
        <svg
            class="sidebar-chevron w-4 h-4 shrink-0
                   text-zinc-500
                   transition-transform duration-200
                   {{ $techOpen ? 'rotate-180' : '' }}"
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
        TECH CONTENT
    ========================================================== --}}

    <div
        id="collapseTech"
        class="{{ $techOpen ? '' : 'hidden' }}"
    >

        <div class="mt-1 ml-2 pl-3 border-l border-zinc-800 space-y-1">


            {{-- =================================================
                DASHBOARD CMS
            ================================================== --}}

            @if ($canManageTech)

                <a
                    href="{{ route('client.produk_divisi', 5) }}"
                    class="group flex items-center gap-2.5
                           px-3 py-2 rounded-md
                           text-sm
                           transition-colors duration-200
                           {{ $isTechCMS
                                ? 'bg-white text-black font-semibold'
                                : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                >

                    {{-- Settings Icon --}}
                    <svg
                        class="w-4 h-4 shrink-0
                               {{ $isTechCMS
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
                        Manajemen Client
                    </span>

                </a>


                {{-- =================================================
                    CLIENT TOGGLE
                ================================================== --}}

                <button
                    type="button"
                    data-sidebar-collapse="cat_tech"
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

                        {{-- Folder Icon --}}
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
                TECH CLIENT LIST
            ================================================== --}}

            <div
                id="cat_tech"
                class="{{ ($techProdukList && $isPdf) ? '' : 'hidden' }}"
            >

                <div class="ml-2 space-y-1 border-l border-zinc-800 pl-2">

                    @foreach ($techProduk as $client)

                        @if($myDivision->name == "Tech" || $isSuperAdmin)

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
                                // Akses client Tech harus mengecek divisi Tech (ID 5),
                                // bukan ID Digital Marketing. Sebelumnya kondisi ini
                                // membuat menu File Dokumen tidak dapat dibuka oleh PIC Tech.
                                $canOpen =
                                    $isPowerUser ||
                                    ($myDivision->name === 'Tech' && $hasClientAccess);

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
                                        href="{{ route('division-client.workspace', ['division' => 'tech', 'id_produk' => $client->id]) }}"
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
                                        id="client_tech_{{ $client->client_id }}"
                                        class="hidden"
                                    >

                                        <div class="ml-4 pl-3 py-1 border-l border-zinc-800">


                                            {{-- OVERVIEW --}}
                                            <a
                                                href="#"
                                                class="flex items-center
                                                       px-3 py-2 rounded-md
                                                       text-sm
                                                       transition-colors duration-200
                                                       {{ request()->is('tech_overview')
                                                            ? 'bg-white text-black font-medium'
                                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                                            >

                                                <svg
                                                    class="w-4 h-4 mr-2.5 shrink-0
                                                           {{ request()->is('tech_overview')
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
                                                        d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
                                                    />
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="2.5"
                                                    />
                                                </svg>

                                                <span>
                                                    Overview
                                                </span>

                                            </a>


                                            {{-- GITHUB --}}
                                            <a
                                                href="#"
                                                class="flex items-center
                                                       px-3 py-2 rounded-md
                                                       text-sm
                                                       transition-colors duration-200
                                                       {{ request()->is('tech_github')
                                                            ? 'bg-white text-black font-medium'
                                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                                            >

                                                <svg
                                                    class="w-4 h-4 mr-2.5 shrink-0
                                                           {{ request()->is('tech_github')
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
                                                        d="M15 22v-3.5c0-1-.35-1.5-1-2
                                                           3.25-.35 6.5-1.6 6.5-7.1
                                                           0-1.4-.5-2.55-1.3-3.45
                                                           .15-.35.55-1.75-.15-3.4
                                                           0 0-1.05-.35-3.5 1.3
                                                           A12 12 0 0012 4.4
                                                           c-1.05 0-2.1.15-3.1.45
                                                           C6.45 3.2 5.4 3.55 5.4 3.55
                                                           c-.7 1.65-.3 3.05-.15 3.4
                                                           C4.45 7.9 4 9.05 4 10.45
                                                           c0 5.5 3.25 6.75 6.5 7.1
                                                           -.65.55-1 1.2-1 2.5V22"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        d="M8 18c-3 .9-3-1.5-4-1.5"
                                                    />
                                                </svg>

                                                <span>
                                                    Github
                                                </span>

                                            </a>


                                            {{-- MONITORING --}}
                                            <a
                                                href="#"
                                                class="flex items-center
                                                       px-3 py-2 rounded-md
                                                       text-sm
                                                       transition-colors duration-200
                                                       {{ request()->is('tech_monitoring')
                                                            ? 'bg-white text-black font-medium'
                                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                                            >

                                                <svg
                                                    class="w-4 h-4 mr-2.5 shrink-0
                                                           {{ request()->is('tech_monitoring')
                                                                ? 'text-black'
                                                                : 'text-zinc-600' }}"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                >
                                                    <rect
                                                        x="3"
                                                        y="4"
                                                        width="18"
                                                        height="13"
                                                        rx="2"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        d="M8 21h8M12 17v4"
                                                    />
                                                </svg>

                                                <span>
                                                    Monitoring
                                                </span>

                                            </a>


                                            {{-- FILE --}}
                                            <a
                                                href="{{ route('PDF', ['id_produk' => $client->id, 'division_id' => 5]) }}"
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
                                                        d="M14 2H6a2 2 0 00-2 2v16
                                                           a2 2 0 002 2h12
                                                           a2 2 0 002-2V8z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M14 2v6h6M8 13h8M8 17h6"
                                                    />
                                                </svg>

                                                <span>
                                                    File
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

        </div>

    </div>

</div>
