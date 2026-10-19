<nav
    class="lmg-navbar flex items-center justify-between px-4 sm:px-6 lg:px-8"
>
    <div class="flex min-w-0 items-center gap-3">

        {{-- =====================================================
            MOBILE SIDEBAR TOGGLE
        ====================================================== --}}
        <button
            id="sidebarToggleTop"
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition-colors hover:border-zinc-300 hover:bg-zinc-100 hover:text-zinc-950"
            aria-label="Buka atau tutup sidebar"
            aria-controls="accordionSidebar"
            aria-expanded="true"
        >
            <svg
                class="h-5 w-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.8"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>

    </div>


    {{-- =========================================================
        RIGHT SIDE
    ========================================================== --}}
    <div class="flex items-center gap-2 sm:gap-3">


        {{-- =====================================================
            DOWNLOAD PDF
        ====================================================== --}}
        @if(
            isset($produk) &&
            (
                request()->routeIs('manajemen*') ||
                request()->routeIs('branding*') ||
                request()->routeIs('market_research*')
            )
        )

            <a
                href="{{ route(
                    'invoice.download',
                    array_merge(
                        [
                            'id' => $produk->id,
                            'halaman' => $halaman
                        ],
                        request()->only([
                            'start_date',
                            'end_date'
                        ])
                    )
                ) }}"
                class="hidden items-center gap-2 rounded-lg bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800 sm:inline-flex"
            >

                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 3v12"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M8 11l4 4 4-4"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M5 21h14"
                    />
                </svg>

                <span>
                    Download PDF
                </span>

            </a>


            {{-- Mobile PDF --}}
            <a
                href="{{ route(
                    'invoice.download',
                    array_merge(
                        [
                            'id' => $produk->id,
                            'halaman' => $halaman
                        ],
                        request()->only([
                            'start_date',
                            'end_date'
                        ])
                    )
                ) }}"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950 sm:hidden"
                title="Download PDF"
                aria-label="Download PDF"
            >

                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M12 3v12"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M8 11l4 4 4-4"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M5 21h14"
                    />
                </svg>

            </a>

        @endif


        {{-- =====================================================
            USER MENU
        ====================================================== --}}
        <div class="relative">

            <button
                id="userDropdownButton"
                type="button"
                class="flex items-center gap-2 rounded-lg border border-transparent px-2 py-1.5 transition-colors hover:border-zinc-200 hover:bg-white"
                aria-expanded="false"
                aria-haspopup="true"
            >

                {{-- User Avatar --}}
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-zinc-950 text-xs font-bold text-white"
                >
                    @if(Auth::user()->photo_profile)
                        <img src="{{ asset('storage/' . Auth::user()->photo_profile) }}" alt="Foto {{ Auth::user()->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->email, 0, 1)) }}
                    @endif
                </div>


                {{-- Email --}}
                <div class="hidden text-left lg:block">

                    <div
                        class="max-w-[180px] truncate text-xs font-semibold text-zinc-800"
                    >
                        {{ Auth::user()->email }}
                    </div>

                    <div
                        class="text-[10px] uppercase tracking-[0.12em] text-zinc-400"
                    >
                        Account
                    </div>

                </div>


                {{-- Chevron --}}
                <svg
                    id="userDropdownChevron"
                    class="hidden h-4 w-4 text-zinc-400 transition-transform lg:block"
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


            {{-- =================================================
                USER DROPDOWN
            ================================================== --}}
            <div
                id="userDropdownMenu"
                class="absolute right-0 top-full z-[1200] mt-2 hidden w-64 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-[0_2px_8px_rgba(0,0,0,0.08)]"
            >

                {{-- User Info --}}
                <div class="border-b border-zinc-100 px-4 py-4">

                    <p
                        class="mb-1 text-[10px] font-bold uppercase tracking-[0.15em] text-zinc-400"
                    >
                        Signed in as
                    </p>

                    <p
                        class="truncate text-sm font-semibold text-zinc-900"
                    >
                        {{ Auth::user()->email }}
                    </p>

                </div>


                {{-- Logout --}}
                <div class="p-2">

                    <a
                        href="{{ route('profile.show') }}"
                        class="mb-1 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                    >
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4" stroke-width="1.8"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 21a8 8 0 0 1 16 0"></path>
                            </svg>
                        </span>
                        <span>Profil Saya</span>
                    </a>

                    <a
                        href="{{ route('ai-connections.index') }}"
                        class="mb-1 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                    >
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9h8M8 13h5m-8 8 3.5-3H18a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v9a3 3 0 0 0 2 2.83V21Z"/></svg>
                        </span>
                        <span>Koneksi AI</span>
                    </a>

                    <button
                        type="button"
                        id="logoutButton"
                        data-logout
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                    >

                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600"
                        >

                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M10 17l5-5-5-5"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M15 12H3"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M14 5h4a2 2 0 012 2v10a2 2 0 01-2 2h-4"
                                />
                            </svg>

                        </span>

                        <span>
                            Logout
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </div>

</nav>


{{-- =============================================================
    NAVBAR JAVASCRIPT
============================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       USER DROPDOWN
    ========================================================== */

    const dropdownButton =
        document.getElementById(
            'userDropdownButton'
        );

    const dropdownMenu =
        document.getElementById(
            'userDropdownMenu'
        );

    const dropdownChevron =
        document.getElementById(
            'userDropdownChevron'
        );


    if (
        dropdownButton &&
        dropdownMenu
    ) {

        dropdownButton.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

                const isOpen =
                    !dropdownMenu.classList.contains(
                        'hidden'
                    );


                if (isOpen) {

                    dropdownMenu.classList.add(
                        'hidden'
                    );

                    dropdownButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                    dropdownChevron?.classList.remove(
                        'rotate-180'
                    );

                } else {

                    dropdownMenu.classList.remove(
                        'hidden'
                    );

                    dropdownButton.setAttribute(
                        'aria-expanded',
                        'true'
                    );

                    dropdownChevron?.classList.add(
                        'rotate-180'
                    );

                }

            }
        );


        /* Close when clicking outside */

        document.addEventListener(
            'click',
            function (event) {

                if (
                    !dropdownButton.contains(event.target) &&
                    !dropdownMenu.contains(event.target)
                ) {

                    dropdownMenu.classList.add(
                        'hidden'
                    );

                    dropdownButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                    dropdownChevron?.classList.remove(
                        'rotate-180'
                    );

                }

            }
        );

    }


    /*
     * Sidebar dikendalikan dari sidebar_new agar perilaku desktop dan mobile
     * menggunakan satu sumber state yang sama.
     */

});
</script>
