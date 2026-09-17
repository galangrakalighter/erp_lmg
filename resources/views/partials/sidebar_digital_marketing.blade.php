{{-- =========================================================
    SIDEBAR DIGITAL MARKETING
    Tailwind CSS / LMG Style
========================================================= --}}

<div class="px-1">

    {{-- =====================================================
        DIGITAL MARKETING HEADER
    ====================================================== --}}
    @php
        $dmOpen = ($isMarketingPage && $isDMProduct);
    @endphp

    <button
        type="button"
        data-sidebar-collapse="collapseDM"
        data-sidebar-group="division"
        class="group flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-xs font-semibold transition
        {{ $dmOpen
            ? 'bg-white text-zinc-950'
            : 'text-zinc-400 hover:bg-zinc-800 hover:text-white'
        }}"
        aria-expanded="{{ $dmOpen ? 'true' : 'false' }}"
    >

        <span class="flex min-w-0 items-center gap-2.5">

            {{-- Digital Marketing Icon --}}
            <svg
                class="h-4 w-4 shrink-0 {{ $dmOpen ? 'text-zinc-950' : 'text-zinc-500' }}"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M3 11v2a2 2 0 002 2h2l3.5 4.5a1 1 0 001.8-.6V15l7-3V6L7 9H5a2 2 0 00-2 2z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M19 8.5a2.5 2.5 0 010 5"
                />
            </svg>

            <span class="truncate">
                Digital Marketing
            </span>

        </span>


        <svg
            class="sidebar-chevron h-3.5 w-3.5 shrink-0 transition-transform duration-200 {{ $dmOpen ? 'rotate-180' : '' }}"
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


    {{-- =====================================================
        DIGITAL MARKETING CONTENT
    ====================================================== --}}
    <div
        id="collapseDM"
        class="{{ $dmOpen ? '' : 'hidden' }} mt-1 ml-2 border-l border-zinc-700 pl-2"
    >

        {{-- =================================================
            BRAND / CMS
        ================================================== --}}
        @if (
            $isSuperAdmin ||
            (
                ($myDivision->name == "Digital Marketing" ||
                $myDivision->name == "Ecommerce") &&
                $myRole->level < 3
            )
        )

            <div class="mb-3">

                {{-- Brand --}}
                <div class="mb-2 flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-zinc-950">

                    <svg
                        class="h-3.5 w-3.5"
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

                    <span>
                        MR. CREPE
                    </span>

                </div>


                {{-- CMS --}}
                <a
                    href="{{ route('cms.produk', 'LGTR') }}"
                    class="flex items-center justify-center gap-2 rounded-lg border border-zinc-700 bg-zinc-900 px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-zinc-400 transition hover:border-zinc-500 hover:bg-zinc-800 hover:text-white"
                >

                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
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
                            d="M19.4 15a1.7 1.7 0 00.3 1.9l.1.1-1.8 1.8-.1-.1a1.7 1.7 0 00-1.9-.3 1.7 1.7 0 01-1 1.5v.1h-2.6v-.1a1.7 1.7 0 01-1-1.5 1.7 1.7 0 00-1.9.3l-.1.1-1.8-1.8.1-.1a1.7 1.7 0 00.3-1.9 1.7 1.7 0 00-1.5-1H6.4v-2.6h.1a1.7 1.7 0 001.5-1 1.7 1.7 0 00-.3-1.9l-.1-.1 1.8-1.8.1.1a1.7 1.7 0 001.9.3 1.7 1.7 0 001-1.5V5.4h2.6v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.9-.3l.1-.1 1.8 1.8-.1.1a1.7 1.7 0 00-.3 1.9 1.7 1.7 0 001.5 1h.1V14h-.1a1.7 1.7 0 00-1.5 1z"
                        />
                    </svg>

                    Dashboard CMS

                </a>

            </div>

        @endif


        {{-- =================================================
            CLIENT
        ================================================== --}}
        <button
            type="button"
            data-sidebar-collapse="cat_dm"
            class="mb-2 flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-[10px] font-bold uppercase tracking-[0.12em] transition
            {{ $isMarketingPage
                ? 'bg-zinc-800 text-white'
                : 'bg-zinc-900/50 text-zinc-500 hover:bg-zinc-800 hover:text-white'
            }}"
            aria-expanded="{{ $isMarketingPage ? 'true' : 'false' }}"
        >

            <span class="flex items-center gap-2">

                <svg
                    class="h-3.5 w-3.5 {{ $isMarketingPage ? 'text-white' : 'text-zinc-600' }}"
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

                <span>
                    Client
                </span>

            </span>


            <svg
                class="sidebar-chevron h-3.5 w-3.5 text-zinc-600 transition-transform duration-200 {{ $isMarketingPage ? 'rotate-180' : '' }}"
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
            id="cat_dm"
            class="{{ $isMarketingPage ? '' : 'hidden' }} ml-1 space-y-1 border-l border-zinc-800 pl-2"
        >

            {{-- =================================================
                CLIENT LOOP
            ================================================== --}}
            @foreach ($digitalMarketingProduk as $client)

                @if (
                    $myDivision->name == "Digital Marketing" ||
                    $myDivision->name == "Ecommerce" ||
                    $isSuperAdmin
                )

                    @php
                        $hasClientAccess =
                            in_array($client->client_id, $myClientIds);

                        $isPowerUser =
                            $isSuperAdmin ||
                            $myRole->role_name == "Performance & Reporting";

                        /*
                         * Jika Ecommerce:
                         * user tetap dapat membuka seluruh client.
                         */
                        $canOpen =
                            $isPowerUser ||
                            $myDivision->name == "Ecommerce" ||
                            (
                                in_array(
                                    1,
                                    $myDivision->pluck('id')->toArray()
                                ) &&
                                $hasClientAccess
                            );

                        $isActiveClient =
                            $isMarketingPage && $currentProduct == $client->client_id;
                    @endphp


                    <div class="mb-1">

                        @if($canOpen)

                            {{-- =================================
                                CLIENT BUTTON
                            ================================== --}}
                            <a
                                href="{{ route('digital-marketing.workspace', ['id_produk' => $client->client_id]) }}"
                                class="group flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-left text-xs transition
                                {{ $isActiveClient
                                    ? 'bg-white font-semibold text-zinc-950'
                                    : 'text-zinc-400 hover:bg-zinc-800 hover:text-white'
                                }}"
                            >

                                <span class="flex min-w-0 items-center gap-2">

                                    {{-- User Icon --}}
                                    <svg
                                        class="h-4 w-4 shrink-0 {{ $isActiveClient ? 'text-zinc-950' : 'text-zinc-600 group-hover:text-zinc-300' }}"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.7"
                                            d="M15 19a4 4 0 00-8 0M11 11a3 3 0 100-6 3 3 0 000 6zM19 19a4 4 0 00-3-3.87M16 5a3 3 0 012 5.24"
                                        />
                                    </svg>

                                    <span class="truncate font-semibold">
                                        {{ $client->nama }}
                                    </span>

                                </span>


                                <svg
                                    class="sidebar-chevron h-3.5 w-3.5 shrink-0 transition-transform duration-200 {{ $isActiveClient ? 'rotate-90' : '' }}"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M9 6l6 6-6 6"
                                    />
                                </svg>

                            </a>


                            {{-- =================================
                                CLIENT CONTENT
                            ================================== --}}
                            <div
                                id="client_lgtr_{{ $client->client_id }}"
                                class="hidden"
                            >

                                @php
                                    $accessToken =
                                        json_decode(
                                            $client->access_token,
                                            true
                                        );

                                    $platforms =
                                        collect(
                                            $accessToken['platform'] ?? []
                                        )->filter();
                                @endphp


                                {{-- =================================
                                    PLATFORM LOOP
                                ================================== --}}
                                @forelse ($platforms as $plt)

                                    @php
                                        $isPlatform =
                                            $currentPlatform == $plt;
                                    @endphp


                                    <div class="mb-2 rounded-lg bg-zinc-950/70 p-1.5">

                                        {{-- Platform --}}
                                        <div class="mb-1 flex items-center gap-2 px-2 py-1">

                                            <span class="h-1.5 w-1.5 rounded-full
                                                {{ $isPlatform
                                                    ? 'bg-white'
                                                    : 'bg-zinc-700'
                                                }}">
                                            </span>

                                            <span
                                                class="truncate text-[9px] font-bold uppercase tracking-[0.12em]
                                                {{ $isPlatform
                                                    ? 'text-white'
                                                    : 'text-zinc-600'
                                                }}"
                                            >
                                                {{ $plt }}
                                            </span>

                                        </div>


                                        {{-- Platform Links --}}
                                        <div class="space-y-0.5">

                                            {{-- Market Research --}}
                                            <a
                                                href="{{ route('market_research', [
                                                    'id_produk' => $client->id,
                                                    'platform' => $plt
                                                ]) }}"
                                                class="block rounded-md px-2 py-1.5 text-[11px] transition
                                                {{ Route::is('market_research') &&
                                                   $currentPlatform == $plt &&
                                                   $isActiveClient
                                                    ? 'bg-white font-semibold text-zinc-950'
                                                    : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                                }}"
                                            >
                                                Market Research
                                            </a>


                                            {{-- Branding --}}
                                            <a
                                                href="{{ route('branding', [
                                                    'id_produk' => $client->id,
                                                    'platform' => $plt
                                                ]) }}"
                                                class="block rounded-md px-2 py-1.5 text-[11px] transition
                                                {{ Route::is('branding') &&
                                                   $currentPlatform == $plt &&
                                                   $isActiveClient
                                                    ? 'bg-white font-semibold text-zinc-950'
                                                    : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                                }}"
                                            >
                                                Branding
                                            </a>


                                            {{-- Analisis --}}
                                            <a
                                                href="{{ route('analisis', [
                                                    'id_produk' => $client->id,
                                                    'platform' => $plt
                                                ]) }}"
                                                class="block rounded-md px-2 py-1.5 text-[11px] transition
                                                {{ Route::is('analisis') &&
                                                   $currentPlatform == $plt &&
                                                   $isActiveClient
                                                    ? 'bg-white font-semibold text-zinc-950'
                                                    : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                                }}"
                                            >
                                                Analisis
                                            </a>


                                            {{-- Content Plan --}}
                                            <a
                                                href="{{ route('manajemen', [
                                                    'id_produk' => $client->id,
                                                    'platform' => $plt
                                                ]) }}"
                                                class="block rounded-md px-2 py-1.5 text-[11px] transition
                                                {{ Route::is('manajemen') &&
                                                   $currentPlatform == $plt &&
                                                   $isActiveClient
                                                    ? 'bg-white font-semibold text-zinc-950'
                                                    : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                                }}"
                                            >
                                                Content Plan
                                            </a>

                                        </div>

                                    </div>

                                @empty

                                    <div class="rounded-lg px-2 py-2 text-[10px] italic text-zinc-600">
                                        Platform belum diset
                                    </div>

                                @endforelse


                                {{-- =================================
                                    FILE DOKUMEN
                                ================================== --}}
                                <a
                                    href="{{ route('PDF', [
                                        'id_produk' => $client->id
                                    ]) }}"
                                    class="mt-2 flex items-center gap-2 rounded-lg px-2.5 py-2 text-[11px] transition
                                    {{ Route::is('PDF') && $isActiveClient
                                        ? 'bg-white font-semibold text-zinc-950'
                                        : 'text-zinc-500 hover:bg-zinc-800 hover:text-white'
                                    }}"
                                >

                                    <svg
                                        class="h-3.5 w-3.5 shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M6 3h8l4 4v14H6V3z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M14 3v5h5"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M9 13h6M9 17h4"
                                        />
                                    </svg>

                                    <span>
                                        File Dokumen
                                    </span>

                                </a>

                            </div>

                        @else

                            {{-- =================================
                                NO ACCESS
                            ================================== --}}
                            <div
                                class="flex cursor-not-allowed items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-zinc-700"
                                title="Anda tidak memiliki akses ke client ini"
                            >

                                <svg
                                    class="h-4 w-4 shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.7"
                                        d="M15 19a4 4 0 00-8 0M11 11a3 3 0 100-6 3 3 0 000 6z"
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
