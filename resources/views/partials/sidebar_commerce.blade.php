{{-- =========================================================
    SIDEBAR ECOMMERCE
    Tailwind CSS / LMG Style
========================================================= --}}

@php
    $isEcommerceProduct = $ecommerceProduk->contains('client_id', $currentProduct);
    $isEcommerceCMS = Route::is('client.produk_divisi') && request()->route('division') == 7;
    $isPdf = Route::is('PDF');
    $isEcommerceWorkspace = Route::is('division-client.workspace')
        && request()->route('division') === 'ecommerce';

    $ecommerceOpen = ($isEcommerceProduct && $isPdf) || $isEcommerceCMS || $isEcommerceWorkspace;
    $canManageEcommerce =
        $isSuperAdmin ||
        ($myDivision->name == "Ecommerce" && $myRole->level < 4);
@endphp


{{-- =========================================================
    ECOMMERCE HEADER
========================================================= --}}

<div class="px-1">

    <button
        type="button"
        data-sidebar-collapse="collapseEcommerce"
        class="w-full group flex items-center justify-between
               px-3 py-2.5 rounded-lg
               text-left text-sm font-semibold
               text-zinc-300
               hover:bg-white/5 hover:text-white
               transition-colors duration-200
               {{ $ecommerceOpen ? 'bg-white/5 text-white' : '' }}"
        aria-expanded="{{ $ecommerceOpen ? 'true' : 'false' }}"
    >

        <span class="flex items-center min-w-0">

            {{-- Ecommerce Icon --}}
            <svg
                class="w-4 h-4 mr-3 shrink-0
                       {{ $ecommerceOpen ? 'text-white' : 'text-zinc-500 group-hover:text-zinc-300' }}"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.7"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 4h13m-8-4v4m5-4v4M9 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"
                />
            </svg>

            <span class="truncate">
                Ecommerce
            </span>

        </span>


        {{-- Chevron --}}
        <svg
            class="sidebar-chevron w-4 h-4 shrink-0 text-zinc-500
                   transition-transform duration-200
                   {{ $ecommerceOpen ? 'rotate-180' : '' }}"
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


    {{-- =====================================================
        ECOMMERCE CONTENT
    ====================================================== --}}

    <div
        id="collapseEcommerce"
        class="{{ $ecommerceOpen ? '' : 'hidden' }}"
    >

        <div class="mt-1 ml-2 pl-3 border-l border-zinc-800 space-y-1">


            {{-- =================================================
                DASHBOARD CMS
            ================================================== --}}

            @if ($canManageEcommerce)

                <a
                    href="{{ route('client.produk_divisi', 7) }}"
                    class="group flex items-center gap-2.5
                           px-3 py-2 rounded-md
                           text-sm
                           transition-colors duration-200
                           {{ $isEcommerceCMS
                                ? 'bg-white text-black font-semibold'
                                : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                >

                    {{-- Settings Icon --}}
                    <svg
                        class="w-4 h-4 shrink-0
                               {{ $isEcommerceCMS ? 'text-black' : 'text-zinc-500 group-hover:text-zinc-300' }}"
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
                               1.724 1.724 0 012.973 1.675
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
                               1.724 1.724 0 012.973-1.675
                               1.724 1.724 0 002.573-1.066z"
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
                    data-sidebar-collapse="cat_ecommerce"
                    class="w-full group flex items-center justify-between
                           px-3 py-2 rounded-md
                           text-sm font-medium
                           transition-colors duration-200
                           {{ $isPdf
                                ? 'text-white bg-white/5'
                                : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
                    aria-expanded="{{ $isPdf ? 'true' : 'false' }}"
                >

                    <span class="flex items-center">

                        {{-- Folder Icon --}}
                        <svg
                            class="w-4 h-4 mr-2.5 shrink-0
                                   {{ $isPdf ? 'text-white' : 'text-zinc-500 group-hover:text-zinc-300' }}"
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
                CLIENT LIST
            ================================================== --}}

            <div
                id="cat_ecommerce"
                class="{{ ($isEcommerceProduct && $isPdf) ? '' : 'hidden' }}"
            >

                <div class="ml-2 space-y-1 border-l border-zinc-800 pl-2">

                    @foreach ($ecommerceProduk as $client)

                        @if($myDivision->name == "Ecommerce" || $isSuperAdmin)

                            @php

                                // User memiliki akses eksplisit ke client
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


                                {{-- =================================================
                                    CLIENT HEADER
                                ================================================== --}}

                                @if($canOpen)

                                    <a
                                        href="{{ route('division-client.workspace', ['division' => 'ecommerce', 'id_produk' => $client->id]) }}"
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

                                            {{-- User / Client Icon --}}
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
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M15 19a4 4 0 00-8 0"
                                                />
                                                <circle
                                                    cx="11"
                                                    cy="7"
                                                    r="3"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M19 8v6m3-3h-6"
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
                                        id="client_ecommerce_{{ $client->client_id }}"
                                        class="hidden"
                                    >

                                        <div class="ml-4 pl-3 py-1 border-l border-zinc-800">

                                            <a
                                                href="{{ route('PDF', ['id_produk' => $client->id]) }}"
                                                class="flex items-center
                                                       px-3 py-2 rounded-md
                                                       text-sm
                                                       transition-colors duration-200
                                                       {{ Route::is('PDF') && $isActiveClient
                                                            ? 'bg-white text-black font-medium'
                                                            : 'text-zinc-500 hover:bg-white/5 hover:text-white' }}"
                                            >

                                                {{-- File Icon --}}
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
