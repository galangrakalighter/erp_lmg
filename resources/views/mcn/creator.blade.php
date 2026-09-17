@extends('layout.app')

@section('content')
@include('mcn.partials.theme')

<div class="mcn-workspace">
    <div class="mx-auto max-w-[1900px]">

        @include('mcn.partials.header', ['heading' => 'Database Affiliator', 'description' => 'Kelola profil affiliator, performa setiap platform, GMV, dan rate card.', 'actionLabel' => 'Tambah Affiliator', 'actionAttributes' => 'onclick="document.getElementById(\'tambahModal\').checked=true"'])


        {{-- =========================================================
            FILTER
        ========================================================== --}}
        <div class="mcn-filter-panel relative z-20 mb-6 overflow-visible rounded-2xl border border-zinc-200 bg-white shadow-sm">

            <div class="border-b border-zinc-200 bg-zinc-50/70 px-5 py-4 sm:px-6">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#161616] text-white">
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-zinc-950">
                            Filter Data
                        </p>

                        <p class="text-xs text-zinc-500">
                            Gunakan filter untuk menyaring database affiliator.
                        </p>
                    </div>

                </div>

            </div>

            <div class="p-5 sm:p-6">

                <form
                    id="filterForm"
                    class="mcn-filter-grid grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4"
                >

                    {{-- KATEGORI --}}
                    <div>
                        <label
                            for="filterKategori"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Kategori
                        </label>

                        <select
                            id="filterKategori"
                            onchange="filterTable()"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                            <option value="">Semua Kategori</option>

                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- PROVINSI --}}
                    <div>
                        <label
                            for="filterLokasi"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Provinsi
                        </label>

                        <select
                            id="filterLokasi"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                            <option value="">Semua Provinsi</option>

                            <option value="Banten">Banten</option>
                            <option value="DKI Jakarta">DKI Jakarta</option>
                            <option value="Jawa Barat">Jawa Barat</option>
                            <option value="Jawa Tengah">Jawa Tengah</option>
                            <option value="DI Yogyakarta">DI Yogyakarta</option>
                            <option value="Jawa Timur">Jawa Timur</option>

                            <option value="Aceh">Aceh</option>
                            <option value="Sumatera Utara">Sumatera Utara</option>
                            <option value="Sumatera Barat">Sumatera Barat</option>
                            <option value="Riau">Riau</option>
                            <option value="Kepulauan Riau">Kepulauan Riau</option>
                            <option value="Jambi">Jambi</option>
                            <option value="Sumatera Selatan">Sumatera Selatan</option>
                            <option value="Bengkulu">Bengkulu</option>
                            <option value="Lampung">Lampung</option>
                            <option value="Kepulauan Bangka Belitung">
                                Bangka Belitung
                            </option>

                            <option value="Bali">Bali</option>
                            <option value="Nusa Tenggara Barat">
                                Nusa Tenggara Barat
                            </option>
                            <option value="Nusa Tenggara Timur">
                                Nusa Tenggara Timur
                            </option>

                            <option value="Kalimantan Barat">
                                Kalimantan Barat
                            </option>
                            <option value="Kalimantan Tengah">
                                Kalimantan Tengah
                            </option>
                            <option value="Kalimantan Selatan">
                                Kalimantan Selatan
                            </option>
                            <option value="Kalimantan Timur">
                                Kalimantan Timur
                            </option>
                            <option value="Kalimantan Utara">
                                Kalimantan Utara
                            </option>

                            <option value="Sulawesi Utara">
                                Sulawesi Utara
                            </option>
                            <option value="Gorontalo">Gorontalo</option>
                            <option value="Sulawesi Tengah">
                                Sulawesi Tengah
                            </option>
                            <option value="Sulawesi Barat">
                                Sulawesi Barat
                            </option>
                            <option value="Sulawesi Selatan">
                                Sulawesi Selatan
                            </option>
                            <option value="Sulawesi Tenggara">
                                Sulawesi Tenggara
                            </option>

                            <option value="Maluku">Maluku</option>
                            <option value="Maluku Utara">
                                Maluku Utara
                            </option>

                            <option value="Papua">Papua</option>
                            <option value="Papua Barat">
                                Papua Barat
                            </option>
                            <option value="Papua Selatan">
                                Papua Selatan
                            </option>
                            <option value="Papua Tengah">
                                Papua Tengah
                            </option>
                            <option value="Papua Pegunungan">
                                Papua Pegunungan
                            </option>
                            <option value="Papua Barat Daya">
                                Papua Barat Daya
                            </option>
                        </select>
                    </div>


                    {{-- PLATFORM FOLLOWERS --}}
                    <div>
                        <label
                            for="filterPlatform"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Platform Followers
                        </label>

                        <select
                            id="filterPlatform"
                            onchange="filterTable()"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                            <option value="all">
                                Gabungan (Semua)
                            </option>

                            <option value="tiktok">
                                TikTok
                            </option>

                            <option value="instagram">
                                Instagram
                            </option>

                            <option value="shopee">
                                Shopee
                            </option>
                        </select>
                    </div>


                    {{-- MIN FOLLOWERS --}}
                    <div>
                        <label
                            for="filterFollowers"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Min. Followers
                        </label>

                        <input
                            type="number"
                            id="filterFollowers"
                            oninput="filterTable()"
                            placeholder="0"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                    </div>


                    {{-- PLATFORM GMV --}}
                    <div>
                        <label
                            for="filterPlatformGMV"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Platform GMV
                        </label>

                        <select
                            id="filterPlatformGMV"
                            onchange="filterTable()"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                            <option value="all">
                                Gabungan (Semua)
                            </option>

                            <option value="tiktok">
                                TikTok
                            </option>

                            <option value="shopee">
                                Shopee
                            </option>
                        </select>
                    </div>


                    {{-- MIN GMV --}}
                    <div>
                        <label
                            for="filterGMV"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Min. GMV
                        </label>

                        <input
                            type="number"
                            id="filterGMV"
                            oninput="filterTable()"
                            placeholder="0"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                    </div>


                    {{-- GENDER --}}
                    <div>
                        <label
                            for="filterGender"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                        >
                            Gender
                        </label>

                        <select
                            id="filterGender"
                            onchange="filterTable()"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        >
                            <option value="">Semua</option>
                            <option value="Pria">Pria</option>
                            <option value="Wanita">Wanita</option>
                        </select>
                    </div>


                    {{-- RESET --}}
                    <div class="flex items-end">
                        <button
                            type="button"
                            onclick="resetFilter()"
                            class="inline-flex min-h-[42px] w-full items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 text-sm font-semibold text-zinc-700 transition hover:border-zinc-400 hover:bg-zinc-50 hover:text-zinc-950"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path d="M3 4v6h6"/>
                            </svg>

                            Reset Filter
                        </button>
                    </div>

                </form>

            </div>
        </div>


        {{-- =========================================================
            TABLE
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            <div class="flex flex-col gap-1 border-b border-zinc-200 bg-zinc-50/70 px-5 py-4 sm:px-6">

                <p class="text-xs font-bold uppercase tracking-wider text-zinc-950">
                    Database Affiliator
                </p>

                <p class="text-xs text-zinc-500">
                    Data lengkap affiliator dan rate card.
                </p>

            </div>

            <div class="overflow-x-auto">

                <table
                    id="creatorMcnTable"
                    data-native-table="creator"
                    data-api="{{ route('api.mcn.affiliators') }}"
                    class="min-w-[1800px] w-full border-collapse text-left text-xs"
                >

                    <thead>

                        <tr class="bg-[#161616] text-white">

                            <th
                                rowspan="2"
                                class="sticky left-0 z-20 border-r border-zinc-700 bg-[#161616] px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider"
                            >
                                No
                            </th>

                            <th
                                colspan="15"
                                class="border-r border-zinc-700 px-4 py-3 text-center text-[11px] font-bold uppercase tracking-[0.15em]"
                            >
                                Data Affiliator
                            </th>

                            <th
                                colspan="6"
                                class="border-r border-zinc-700 px-4 py-3 text-center text-[11px] font-bold uppercase tracking-[0.15em]"
                            >
                                Rate Card
                            </th>

                            <th
                                rowspan="2"
                                class="px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wider"
                            >
                                Action
                            </th>

                        </tr>

                        <tr class="bg-zinc-100 text-zinc-700">

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                TikTok
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                URL TT
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Followers TT
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Shopee
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                URL SHP
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Followers SHP
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Instagram
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                URL IG
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Followers IG
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Kategori
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                GMV TT
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                GMV Shopee
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Provinsi
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Gender
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Contact
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Detail
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Reels
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                VT
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Mirroring
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Kerkun
                            </th>

                            <th class="px-3 py-2 text-center text-[10px] font-bold uppercase">
                                Story
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-zinc-100">

                        @foreach ($data as $d)

                            <tr class="transition hover:bg-zinc-50">

                                <td class="sticky left-0 z-10 border-r border-zinc-200 bg-white px-4 py-3 text-center font-semibold text-zinc-700">
                                    {{ $loop->iteration }}
                                </td>


                                {{-- TIKTOK --}}
                                <td class="px-3 py-3 font-semibold text-zinc-900">
                                    {{ $d->user_tt }}
                                </td>

                                <td class="px-3 py-3">

                                    <a
                                        href="{{ $d->url_tt }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 font-semibold text-zinc-700 hover:text-black hover:underline"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                        >
                                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.56 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.18-3.47-3.39-3.65-5.73-.02-.5-.03-1-.01-1.49.15-1.95 1.12-3.83 2.65-5.05 1.73-1.41 4.18-2.04 6.35-1.46.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.27 1.79-.14.55-.1 1.12.04 1.67.25.96 1.05 1.78 2.01 2.05.93.26 1.96.08 2.68-.55.75-.65 1.11-1.65 1.08-2.64.05-4.71-.03-9.4.02-14.11z"/>
                                        </svg>

                                        Link
                                    </a>

                                </td>

                                <td
                                    class="px-3 py-3 text-right tabular-nums"
                                    data-fol="{{ $d->fol_tt }}"
                                >
                                    {{ number_format($d->fol_tt) }}
                                </td>


                                {{-- SHOPEE --}}
                                <td class="px-3 py-3 font-semibold text-zinc-900">
                                    {{ $d->user_shopee }}
                                </td>

                                <td class="px-3 py-3">

                                    <a
                                        href="{{ $d->url_shp }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 font-semibold text-zinc-700 hover:text-black hover:underline"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M6 2l1.5 4h9L18 2"/>
                                            <path d="M3 6h18l-1.5 15h-15z"/>
                                            <path d="M9 10v7"/>
                                            <path d="M15 10v7"/>
                                        </svg>

                                        Link
                                    </a>

                                </td>

                                <td
                                    class="px-3 py-3 text-right tabular-nums"
                                    data-fol="{{ $d->fol_shopee }}"
                                >
                                    {{ number_format($d->fol_shopee) }}
                                </td>


                                {{-- INSTAGRAM --}}
                                <td class="px-3 py-3 font-semibold text-zinc-900">
                                    {{ $d->user_ig }}
                                </td>

                                <td class="px-3 py-3">

                                    <a
                                        href="{{ $d->url_ig }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 font-semibold text-zinc-700 hover:text-black hover:underline"
                                    >
                                        <svg
                                            class="h-3.5 w-3.5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <rect
                                                x="3"
                                                y="3"
                                                width="18"
                                                height="18"
                                                rx="5"
                                            />

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="4"
                                            />

                                            <circle
                                                cx="17.5"
                                                cy="6.5"
                                                r=".75"
                                                fill="currentColor"
                                                stroke="none"
                                            />
                                        </svg>

                                        Link
                                    </a>

                                </td>

                                <td
                                    class="px-3 py-3 text-right tabular-nums"
                                    data-fol="{{ $d->fol_ig }}"
                                >
                                    {{ number_format($d->fol_ig) }}
                                </td>


                                {{-- CATEGORY --}}
                                <td class="max-w-[220px] px-3 py-3">

                                    @if($d->category)

                                        <div class="flex flex-wrap gap-1">

                                            @foreach(explode(',', $d->category) as $cat)

                                                <span class="inline-flex rounded-md bg-zinc-100 px-2 py-1 text-[10px] font-semibold text-zinc-700">
                                                    {{ trim($cat) }}
                                                </span>

                                            @endforeach

                                        </div>

                                    @else

                                        <span class="text-zinc-400">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- GMV --}}
                                <td
                                    class="px-3 py-3 text-right font-medium tabular-nums text-zinc-800"
                                    data-gmv="{{ $d->gmv_tt }}"
                                >
                                    Rp {{ number_format($d->gmv_tt, 0, ',', '.') }}
                                </td>

                                <td
                                    class="px-3 py-3 text-right font-medium tabular-nums text-zinc-800"
                                    data-gmv="{{ $d->gmv_shopee }}"
                                >
                                    Rp {{ number_format($d->gmv_shopee, 0, ',', '.') }}
                                </td>


                                {{-- LOCATION --}}
                                <td class="px-3 py-3">
                                    {{ $d->provinsi }}
                                </td>


                                {{-- GENDER --}}
                                <td class="px-3 py-3">

                                    <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-semibold text-zinc-700">
                                        {{ $d->gender }}
                                    </span>

                                </td>


                                {{-- CONTACT --}}
                                <td class="px-3 py-3">

                                    @php
                                        $waNumber = preg_replace(
                                            ['/-/', '/^0/'],
                                            ['', '62'],
                                            $d->contact
                                        );
                                    @endphp

                                    <a
                                        href="https://wa.me/{{ $waNumber }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="font-semibold text-zinc-800 hover:text-black hover:underline"
                                    >
                                        {{ $d->contact }}
                                    </a>

                                </td>


                                {{-- RATE CARD --}}
                                <td class="px-3 py-3 text-center font-medium">
                                    {{ $d->detail }}
                                </td>

                                <td class="px-3 py-3 text-center font-medium">
                                    {{ $d->reels }}
                                </td>

                                <td class="px-3 py-3 text-center font-medium">
                                    {{ $d->vt }}
                                </td>

                                <td class="px-3 py-3 text-center font-medium">
                                    {{ $d->mirroring }}
                                </td>

                                <td class="px-3 py-3 text-center font-medium">
                                    {{ $d->kerkun }}
                                </td>

                                <td class="px-3 py-3 text-center font-medium">
                                    {{ $d->story }}
                                </td>


                                {{-- ACTION --}}
                                <td class="px-3 py-3">

                                    <div class="flex items-center justify-center gap-2">

                                        <label
                                            for="editModal{{ $d->id }}"
                                            title="Edit"
                                            class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-zinc-300 bg-white text-zinc-700 transition hover:border-zinc-500 hover:bg-zinc-50 hover:text-zinc-950"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </label>


                                        <label
                                            for="deleteModal{{ $d->id }}"
                                            title="Hapus"
                                            class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50 text-zinc-500 transition hover:border-zinc-400 hover:bg-zinc-100 hover:text-zinc-950"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="M19 6l-1 14H6L5 6"/>
                                                <path d="M10 11v5"/>
                                                <path d="M14 11v5"/>
                                            </svg>
                                        </label>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div id="creatorMcnTablePagination" class="native-table-pagination"></div>

        </div>

    </div>
</div>


{{-- ================================================================
    MODAL TAMBAH
================================================================ --}}
<input
    type="checkbox"
    id="tambahModal"
    class="peer sr-only"
>

<div
    class="pointer-events-none fixed inset-0 z-[100000] flex items-center justify-center p-4 opacity-0 transition duration-200 peer-checked:pointer-events-auto peer-checked:opacity-100 sm:p-6"
>

    <label
        for="tambahModal"
        class="absolute inset-0 cursor-pointer bg-zinc-950/60 backdrop-blur-sm"
    ></label>

    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="tambahModalLabel"
        class="relative z-10 flex max-h-[calc(100dvh-2rem)] w-full max-w-6xl scale-95 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition duration-200 peer-checked:scale-100 sm:max-h-[calc(100dvh-4rem)]"
    >

        <form
            action="{{ route('social_creator.store') }}"
            method="POST"
            class="flex min-h-0 flex-1 flex-col overflow-hidden"
        >

            @csrf


            {{-- HEADER --}}
            <div class="flex shrink-0 items-center justify-between bg-[#161616] px-5 py-4 text-white sm:px-6">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M19 8v6"/>
                            <path d="M22 11h-6"/>
                        </svg>

                    </div>

                    <div>

                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">
                            Affiliator
                        </p>

                        <h2
                            id="tambahModalLabel"
                            class="text-base font-bold sm:text-lg"
                        >
                            Tambah Data Affiliator Baru
                        </h2>

                    </div>

                </div>


                <label
                    for="tambahModal"
                    class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <path d="M6 6l12 12"/>
                        <path d="M18 6 6 18"/>
                    </svg>
                </label>

            </div>


            {{-- BODY --}}
            <div class="min-h-0 flex-1 overflow-y-auto bg-zinc-50 p-4 sm:p-6">

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">


                    {{-- =================================================
                        IDENTITAS & SOSMED
                    ================================================== --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

                        <div class="border-b border-zinc-200 px-5 py-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <circle cx="12" cy="8" r="4"/>
                                        <path d="M4 21a8 8 0 0 1 16 0"/>
                                    </svg>
                                </div>

                                <div>
                                    <h3 class="text-sm font-bold text-zinc-950">
                                        Data Identitas & Sosmed
                                    </h3>

                                    <p class="text-[11px] text-zinc-500">
                                        Informasi utama affiliator
                                    </p>
                                </div>

                            </div>

                        </div>


                        <div class="space-y-5 p-5">

                            {{-- USERNAME TIKTOK --}}
                            <div>

                                <label
                                    for="user_tt"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                >
                                    Username TikTok
                                </label>

                                <div class="flex">

                                    <span class="inline-flex items-center rounded-l-xl border border-r-0 border-zinc-300 bg-zinc-50 px-3 text-sm font-semibold text-zinc-500">
                                        @
                                    </span>

                                    <input
                                        type="text"
                                        name="user_tt"
                                        id="user_tt"
                                        class="min-w-0 flex-1 rounded-r-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="username"
                                        required
                                    >

                                </div>

                            </div>


                            {{-- URL TIKTOK --}}
                            <div>

                                <label
                                    for="url_tt"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                >
                                    URL TikTok
                                </label>

                                <input
                                    type="text"
                                    name="url_tt"
                                    id="url_tt"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    placeholder="https://tiktok.com/@..."
                                >

                            </div>


                            {{-- FOLLOWERS + GMV --}}
                            <div class="grid grid-cols-2 gap-4">

                                <div>

                                    <label
                                        for="fol_tt"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Followers TikTok
                                    </label>

                                    <input
                                        type="number"
                                        name="fol_tt"
                                        id="fol_tt"
                                        value="0"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>

                                <div>

                                    <label
                                        for="gmv_tt"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        GMV TikTok
                                    </label>

                                    <input
                                        type="number"
                                        name="gmv_tt"
                                        id="gmv_tt"
                                        value="0"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>

                            </div>


                            {{-- PROVINSI + GENDER --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                <div>

                                    <label
                                        for="choices-provinsi"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Provinsi
                                    </label>

                                    <select
                                        name="provinsi"
                                        id="choices-provinsi"
                                        class="w-full"
                                        required
                                    >

                                        <optgroup label="Sumatra">
                                            <option value="Aceh">Aceh</option>
                                            <option value="Sumatera Utara">Sumatera Utara</option>
                                            <option value="Sumatera Barat">Sumatera Barat</option>
                                            <option value="Riau">Riau</option>
                                            <option value="Kepulauan Riau">Kepulauan Riau</option>
                                            <option value="Jambi">Jambi</option>
                                            <option value="Bengkulu">Bengkulu</option>
                                            <option value="Sumatera Selatan">Sumatera Selatan</option>
                                            <option value="Kepulauan Bangka Belitung">
                                                Kepulauan Bangka Belitung
                                            </option>
                                            <option value="Lampung">Lampung</option>
                                        </optgroup>

                                        <optgroup label="Jawa">
                                            <option value="DKI Jakarta">DKI Jakarta</option>
                                            <option value="Banten">Banten</option>
                                            <option value="Jawa Barat">Jawa Barat</option>
                                            <option value="Jawa Tengah">Jawa Tengah</option>
                                            <option value="DI Yogyakarta">DI Yogyakarta</option>
                                            <option value="Jawa Timur">Jawa Timur</option>
                                        </optgroup>

                                        <optgroup label="Bali & Nusa Tenggara">
                                            <option value="Bali">Bali</option>
                                            <option value="Nusa Tenggara Barat">
                                                Nusa Tenggara Barat
                                            </option>
                                            <option value="Nusa Tenggara Timur">
                                                Nusa Tenggara Timur
                                            </option>
                                        </optgroup>

                                        <optgroup label="Kalimantan">
                                            <option value="Kalimantan Barat">
                                                Kalimantan Barat
                                            </option>
                                            <option value="Kalimantan Tengah">
                                                Kalimantan Tengah
                                            </option>
                                            <option value="Kalimantan Selatan">
                                                Kalimantan Selatan
                                            </option>
                                            <option value="Kalimantan Timur">
                                                Kalimantan Timur
                                            </option>
                                            <option value="Kalimantan Utara">
                                                Kalimantan Utara
                                            </option>
                                        </optgroup>

                                        <optgroup label="Sulawesi">
                                            <option value="Sulawesi Utara">
                                                Sulawesi Utara
                                            </option>
                                            <option value="Gorontalo">Gorontalo</option>
                                            <option value="Sulawesi Tengah">
                                                Sulawesi Tengah
                                            </option>
                                            <option value="Sulawesi Barat">
                                                Sulawesi Barat
                                            </option>
                                            <option value="Sulawesi Selatan">
                                                Sulawesi Selatan
                                            </option>
                                            <option value="Sulawesi Tenggara">
                                                Sulawesi Tenggara
                                            </option>
                                        </optgroup>

                                        <optgroup label="Maluku & Papua">
                                            <option value="Maluku">Maluku</option>
                                            <option value="Maluku Utara">
                                                Maluku Utara
                                            </option>
                                            <option value="Papua">Papua</option>
                                            <option value="Papua Barat">
                                                Papua Barat
                                            </option>
                                            <option value="Papua Selatan">
                                                Papua Selatan
                                            </option>
                                            <option value="Papua Tengah">
                                                Papua Tengah
                                            </option>
                                            <option value="Papua Pegunungan">
                                                Papua Pegunungan
                                            </option>
                                            <option value="Papua Barat Daya">
                                                Papua Barat Daya
                                            </option>
                                        </optgroup>

                                    </select>

                                </div>


                                <div>

                                    <label
                                        for="gender"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Gender
                                    </label>

                                    <select
                                        name="gender"
                                        id="gender"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        required
                                    >
                                        <option value="" disabled selected>
                                            Pilih...
                                        </option>

                                        <option value="Pria">
                                            Pria
                                        </option>

                                        <option value="Wanita">
                                            Wanita
                                        </option>
                                    </select>

                                </div>

                            </div>


                            {{-- CATEGORY --}}
                            <div>

                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700">
                                    Kategori
                                    <span class="text-zinc-400">
                                        (Bisa pilih lebih dari 1)
                                    </span>
                                </label>

                                <div class="max-h-[140px] overflow-y-auto rounded-xl border border-zinc-300 bg-white p-3">

                                    <div class="space-y-1">

                                        @foreach($categories as $cat)

                                            <div class="flex items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-zinc-50">

                                                <input
                                                    type="checkbox"
                                                    name="categories_array[]"
                                                    value="{{ $cat }}"
                                                    class="h-4 w-4 rounded border-zinc-300 text-[#161616] focus:ring-zinc-900"
                                                    id="cat_{{ $loop->index }}"
                                                >

                                                <label
                                                    for="cat_{{ $loop->index }}"
                                                    class="cursor-pointer text-xs font-medium text-zinc-700"
                                                >
                                                    {{ $cat }}
                                                </label>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                                <p class="mt-2 text-[11px] italic text-zinc-400">
                                    Centang kategori yang sesuai.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        RATE CARD + ECOMMERCE
                    ================================================== --}}
                    <div class="space-y-5">


                        {{-- RATE CARD --}}
                        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

                            <div class="border-b border-zinc-200 px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">

                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M20 12V8H6a2 2 0 0 1 0-4h14v4"/>
                                            <path d="M4 6v14a2 2 0 0 0 2 2h14V8"/>
                                            <path d="M18 12h2"/>
                                        </svg>

                                    </div>

                                    <div>
                                        <h3 class="text-sm font-bold text-zinc-950">
                                            Rate Card
                                        </h3>

                                        <p class="text-[11px] text-zinc-500">
                                            Harga layanan affiliator
                                        </p>
                                    </div>

                                </div>

                            </div>


                            <div class="grid grid-cols-2 gap-x-4 gap-y-5 p-5">

                                <div>

                                    <label
                                        for="detail"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Detail
                                    </label>

                                    <input
                                        type="text"
                                        name="detail"
                                        id="detail"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Rp"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="mirroring"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Mirroring
                                    </label>

                                    <input
                                        type="text"
                                        name="mirroring"
                                        id="mirroring"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Rp"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="reels"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Reels IG
                                    </label>

                                    <input
                                        type="text"
                                        name="reels"
                                        id="reels"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Rp"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="kerkun"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Kerkun
                                    </label>

                                    <input
                                        type="text"
                                        name="kerkun"
                                        id="kerkun"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Rp"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="vt"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Video TikTok (VT)
                                    </label>

                                    <input
                                        type="text"
                                        name="vt"
                                        id="vt"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Rp"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="story"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        IG Story
                                    </label>

                                    <input
                                        type="text"
                                        name="story"
                                        id="story"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Rp"
                                    >

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            ECOMMERCE & SOCIAL CONNECTION
                        ================================================== --}}
                        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

                            <div class="border-b border-zinc-200 px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">

                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M3 3h18v18H3z"/>
                                            <path d="M8 12h8"/>
                                            <path d="M12 8v8"/>
                                        </svg>

                                    </div>

                                    <div>
                                        <h3 class="text-sm font-bold text-zinc-950">
                                            Koneksi E-Commerce & Media Sosial
                                        </h3>

                                        <p class="text-[11px] text-zinc-500">
                                            Informasi akun Shopee dan Instagram
                                        </p>
                                    </div>

                                </div>

                            </div>


                            <div class="space-y-4 p-5">


                                {{-- SHOPEE --}}
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">

                                    <div class="mb-4 flex items-center gap-2">

                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-zinc-700 shadow-sm">

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M6 2l1.5 4h9L18 2"/>
                                                <path d="M3 6h18l-1.5 15h-15z"/>
                                                <path d="M9 10v7"/>
                                                <path d="M15 10v7"/>
                                            </svg>

                                        </div>

                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                                            Shopee Data
                                        </span>

                                    </div>


                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                                        <div>

                                            <label
                                                for="user_shopee"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                Username
                                            </label>

                                            <input
                                                type="text"
                                                name="user_shopee"
                                                id="user_shopee"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                placeholder="user_shp"
                                            >

                                        </div>


                                        <div>

                                            <label
                                                for="url_shopee"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                URL Toko
                                            </label>

                                            <input
                                                type="text"
                                                name="url_shopee"
                                                id="url_shopee"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                placeholder="shopee.co.id/..."
                                            >

                                        </div>


                                        <div>

                                            <label
                                                for="fol_shopee"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                Followers
                                            </label>

                                            <input
                                                type="number"
                                                name="fol_shopee"
                                                id="fol_shopee"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                placeholder="0"
                                            >

                                        </div>


                                        <div>

                                            <label
                                                for="gmv_shopee"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                GMV Shopee
                                            </label>

                                            <input
                                                type="number"
                                                name="gmv_shopee"
                                                id="gmv_shopee"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                placeholder="Rp"
                                            >

                                        </div>

                                    </div>

                                </div>


                                {{-- INSTAGRAM --}}
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">

                                    <div class="mb-4 flex items-center gap-2">

                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-zinc-700 shadow-sm">

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <rect
                                                    x="3"
                                                    y="3"
                                                    width="18"
                                                    height="18"
                                                    rx="5"
                                                />

                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="4"
                                                />

                                                <circle
                                                    cx="17.5"
                                                    cy="6.5"
                                                    r=".75"
                                                    fill="currentColor"
                                                    stroke="none"
                                                />
                                            </svg>

                                        </div>

                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                                            Instagram Data
                                        </span>

                                    </div>


                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                                        <div>

                                            <label
                                                for="user_ig"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                Username IG
                                            </label>

                                            <div class="flex">

                                                <span class="inline-flex items-center rounded-l-xl border border-r-0 border-zinc-300 bg-white px-3 text-sm font-semibold text-zinc-400">
                                                    @
                                                </span>

                                                <input
                                                    type="text"
                                                    name="user_ig"
                                                    id="user_ig"
                                                    class="min-w-0 flex-1 rounded-r-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                    placeholder="username"
                                                >

                                            </div>

                                        </div>


                                        <div>

                                            <label
                                                for="url_ig"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                URL Profile
                                            </label>

                                            <input
                                                type="text"
                                                name="url_ig"
                                                id="url_ig"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                placeholder="instagram.com/..."
                                            >

                                        </div>


                                        <div>

                                            <label
                                                for="fol_ig"
                                                class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-zinc-600"
                                            >
                                                Follower IG
                                            </label>

                                            <input
                                                type="number"
                                                name="fol_ig"
                                                id="fol_ig"
                                                class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                placeholder="0"
                                            >

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">

                <label
                    for="tambahModal"
                    class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                    Batal
                </label>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#161616] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/>
                        <path d="M17 21v-8H7v8"/>
                        <path d="M7 3v5h8"/>
                    </svg>

                    Simpan Data Affiliator
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ================================================================
    MODAL EDIT
================================================================ --}}
@foreach ($data as $d)

<input
    type="checkbox"
    id="editModal{{ $d->id }}"
    class="peer sr-only"
>

<div
    class="pointer-events-none fixed inset-0 z-[100000] flex items-center justify-center p-4 opacity-0 transition duration-200 peer-checked:pointer-events-auto peer-checked:opacity-100 sm:p-6"
>

    <label
        for="editModal{{ $d->id }}"
        class="absolute inset-0 cursor-pointer bg-zinc-950/60 backdrop-blur-sm"
    ></label>


    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="editModalLabel{{ $d->id }}"
        class="relative z-10 flex max-h-[calc(100dvh-2rem)] w-full max-w-6xl scale-95 flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition duration-200 peer-checked:scale-100 sm:max-h-[calc(100dvh-4rem)]"
    >

        <form
            action="{{ route('social_creator.update', $d->id) }}"
            method="POST"
            class="flex min-h-0 flex-1 flex-col overflow-hidden"
        >

            @csrf
            @method('PUT')


            {{-- HEADER --}}
            <div class="flex shrink-0 items-center justify-between bg-[#161616] px-5 py-4 text-white sm:px-6">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10">

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 20h9"/>
                            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                        </svg>

                    </div>

                    <div class="min-w-0">

                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-400">
                            Edit Affiliator
                        </p>

                        <h2
                            id="editModalLabel{{ $d->id }}"
                            class="truncate text-base font-bold sm:text-lg"
                        >
                            {{ $d->user_tt }}
                        </h2>

                    </div>

                </div>


                <label
                    for="editModal{{ $d->id }}"
                    class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    >
                        <path d="M6 6l12 12"/>
                        <path d="M18 6 6 18"/>
                    </svg>
                </label>

            </div>


            {{-- BODY --}}
            <div class="min-h-0 flex-1 overflow-y-auto bg-zinc-50 p-4 text-left sm:p-6">

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">


                    {{-- =================================================
                        IDENTITAS
                    ================================================== --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

                        <div class="border-b border-zinc-200 px-5 py-4">

                            <div class="flex items-center gap-3">

                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">

                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <circle cx="12" cy="8" r="4"/>
                                        <path d="M4 21a8 8 0 0 1 16 0"/>
                                    </svg>

                                </div>

                                <div>
                                    <h3 class="text-sm font-bold text-zinc-950">
                                        Data Identitas & Sosmed
                                    </h3>

                                    <p class="text-[11px] text-zinc-500">
                                        Informasi utama affiliator
                                    </p>
                                </div>

                            </div>

                        </div>


                        <div class="space-y-5 p-5">

                            {{-- USERNAME --}}
                            <div>

                                <label
                                    for="edit_user_tt_{{ $d->id }}"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                >
                                    Username TikTok
                                </label>

                                <div class="flex">

                                    <span class="inline-flex items-center rounded-l-xl border border-r-0 border-zinc-300 bg-zinc-50 px-3 text-sm font-semibold text-zinc-500">
                                        @
                                    </span>

                                    <input
                                        type="text"
                                        name="user_tt"
                                        id="edit_user_tt_{{ $d->id }}"
                                        value="{{ $d->user_tt }}"
                                        class="min-w-0 flex-1 rounded-r-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        required
                                    >

                                </div>

                            </div>


                            {{-- URL --}}
                            <div>

                                <label
                                    for="edit_url_tt_{{ $d->id }}"
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                >
                                    URL TikTok
                                </label>

                                <input
                                    type="text"
                                    name="url_tt"
                                    id="edit_url_tt_{{ $d->id }}"
                                    value="{{ $d->url_tt }}"
                                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                >

                            </div>


                            {{-- FOLLOWERS + GMV --}}
                            <div class="grid grid-cols-2 gap-4">

                                <div>

                                    <label
                                        for="edit_fol_tt_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Followers TikTok
                                    </label>

                                    <input
                                        type="number"
                                        name="fol_tt"
                                        id="edit_fol_tt_{{ $d->id }}"
                                        value="{{ $d->fol_tt }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="edit_gmv_tt_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        GMV TikTok
                                    </label>

                                    <input
                                        type="number"
                                        name="gmv_tt"
                                        id="edit_gmv_tt_{{ $d->id }}"
                                        value="{{ $d->gmv_tt }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>

                            </div>


                            {{-- PROVINSI + GENDER --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                <div>

                                    <label
                                        for="select_provinsi_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Provinsi
                                    </label>

                                    <select
                                        name="provinsi"
                                        id="select_provinsi_{{ $d->id }}"
                                        class="select-choices-edit w-full"
                                        required
                                    >

                                        <optgroup label="Jawa">

                                            <option
                                                value="DKI Jakarta"
                                                {{ $d->provinsi == 'DKI Jakarta' ? 'selected' : '' }}
                                            >
                                                DKI Jakarta
                                            </option>

                                            <option
                                                value="Banten"
                                                {{ $d->provinsi == 'Banten' ? 'selected' : '' }}
                                            >
                                                Banten
                                            </option>

                                            <option
                                                value="Jawa Barat"
                                                {{ $d->provinsi == 'Jawa Barat' ? 'selected' : '' }}
                                            >
                                                Jawa Barat
                                            </option>

                                            <option
                                                value="Jawa Tengah"
                                                {{ $d->provinsi == 'Jawa Tengah' ? 'selected' : '' }}
                                            >
                                                Jawa Tengah
                                            </option>

                                            <option
                                                value="DI Yogyakarta"
                                                {{ $d->provinsi == 'DI Yogyakarta' ? 'selected' : '' }}
                                            >
                                                DI Yogyakarta
                                            </option>

                                            <option
                                                value="Jawa Timur"
                                                {{ $d->provinsi == 'Jawa Timur' ? 'selected' : '' }}
                                            >
                                                Jawa Timur
                                            </option>

                                        </optgroup>


                                        <optgroup label="Sumatra">

                                            <option
                                                value="Aceh"
                                                {{ $d->provinsi == 'Aceh' ? 'selected' : '' }}
                                            >
                                                Aceh
                                            </option>

                                            <option
                                                value="Sumatera Utara"
                                                {{ $d->provinsi == 'Sumatera Utara' ? 'selected' : '' }}
                                            >
                                                Sumatera Utara
                                            </option>

                                            <option
                                                value="Sumatera Barat"
                                                {{ $d->provinsi == 'Sumatera Barat' ? 'selected' : '' }}
                                            >
                                                Sumatera Barat
                                            </option>

                                            <option
                                                value="Riau"
                                                {{ $d->provinsi == 'Riau' ? 'selected' : '' }}
                                            >
                                                Riau
                                            </option>

                                            <option
                                                value="Kepulauan Riau"
                                                {{ $d->provinsi == 'Kepulauan Riau' ? 'selected' : '' }}
                                            >
                                                Kepulauan Riau
                                            </option>

                                            <option
                                                value="Jambi"
                                                {{ $d->provinsi == 'Jambi' ? 'selected' : '' }}
                                            >
                                                Jambi
                                            </option>

                                            <option
                                                value="Bengkulu"
                                                {{ $d->provinsi == 'Bengkulu' ? 'selected' : '' }}
                                            >
                                                Bengkulu
                                            </option>

                                            <option
                                                value="Sumatera Selatan"
                                                {{ $d->provinsi == 'Sumatera Selatan' ? 'selected' : '' }}
                                            >
                                                Sumatera Selatan
                                            </option>

                                            <option
                                                value="Kepulauan Bangka Belitung"
                                                {{ $d->provinsi == 'Kepulauan Bangka Belitung' ? 'selected' : '' }}
                                            >
                                                Kepulauan Bangka Belitung
                                            </option>

                                            <option
                                                value="Lampung"
                                                {{ $d->provinsi == 'Lampung' ? 'selected' : '' }}
                                            >
                                                Lampung
                                            </option>

                                        </optgroup>


                                        <optgroup label="Kalimantan">

                                            <option
                                                value="Kalimantan Barat"
                                                {{ $d->provinsi == 'Kalimantan Barat' ? 'selected' : '' }}
                                            >
                                                Kalimantan Barat
                                            </option>

                                            <option
                                                value="Kalimantan Tengah"
                                                {{ $d->provinsi == 'Kalimantan Tengah' ? 'selected' : '' }}
                                            >
                                                Kalimantan Tengah
                                            </option>

                                            <option
                                                value="Kalimantan Selatan"
                                                {{ $d->provinsi == 'Kalimantan Selatan' ? 'selected' : '' }}
                                            >
                                                Kalimantan Selatan
                                            </option>

                                            <option
                                                value="Kalimantan Timur"
                                                {{ $d->provinsi == 'Kalimantan Timur' ? 'selected' : '' }}
                                            >
                                                Kalimantan Timur
                                            </option>

                                            <option
                                                value="Kalimantan Utara"
                                                {{ $d->provinsi == 'Kalimantan Utara' ? 'selected' : '' }}
                                            >
                                                Kalimantan Utara
                                            </option>

                                        </optgroup>


                                        <optgroup label="Sulawesi">

                                            <option
                                                value="Sulawesi Utara"
                                                {{ $d->provinsi == 'Sulawesi Utara' ? 'selected' : '' }}
                                            >
                                                Sulawesi Utara
                                            </option>

                                            <option
                                                value="Gorontalo"
                                                {{ $d->provinsi == 'Gorontalo' ? 'selected' : '' }}
                                            >
                                                Gorontalo
                                            </option>

                                            <option
                                                value="Sulawesi Tengah"
                                                {{ $d->provinsi == 'Sulawesi Tengah' ? 'selected' : '' }}
                                            >
                                                Sulawesi Tengah
                                            </option>

                                            <option
                                                value="Sulawesi Barat"
                                                {{ $d->provinsi == 'Sulawesi Barat' ? 'selected' : '' }}
                                            >
                                                Sulawesi Barat
                                            </option>

                                            <option
                                                value="Sulawesi Selatan"
                                                {{ $d->provinsi == 'Sulawesi Selatan' ? 'selected' : '' }}
                                            >
                                                Sulawesi Selatan
                                            </option>

                                            <option
                                                value="Sulawesi Tenggara"
                                                {{ $d->provinsi == 'Sulawesi Tenggara' ? 'selected' : '' }}
                                            >
                                                Sulawesi Tenggara
                                            </option>

                                        </optgroup>


                                        <optgroup label="Bali & Nusa Tenggara">

                                            <option
                                                value="Bali"
                                                {{ $d->provinsi == 'Bali' ? 'selected' : '' }}
                                            >
                                                Bali
                                            </option>

                                            <option
                                                value="Nusa Tenggara Barat"
                                                {{ $d->provinsi == 'Nusa Tenggara Barat' ? 'selected' : '' }}
                                            >
                                                Nusa Tenggara Barat
                                            </option>

                                            <option
                                                value="Nusa Tenggara Timur"
                                                {{ $d->provinsi == 'Nusa Tenggara Timur' ? 'selected' : '' }}
                                            >
                                                Nusa Tenggara Timur
                                            </option>

                                        </optgroup>


                                        <optgroup label="Maluku & Papua">

                                            <option
                                                value="Maluku"
                                                {{ $d->provinsi == 'Maluku' ? 'selected' : '' }}
                                            >
                                                Maluku
                                            </option>

                                            <option
                                                value="Maluku Utara"
                                                {{ $d->provinsi == 'Maluku Utara' ? 'selected' : '' }}
                                            >
                                                Maluku Utara
                                            </option>

                                            <option
                                                value="Papua"
                                                {{ $d->provinsi == 'Papua' ? 'selected' : '' }}
                                            >
                                                Papua
                                            </option>

                                            <option
                                                value="Papua Barat"
                                                {{ $d->provinsi == 'Papua Barat' ? 'selected' : '' }}
                                            >
                                                Papua Barat
                                            </option>

                                            <option
                                                value="Papua Selatan"
                                                {{ $d->provinsi == 'Papua Selatan' ? 'selected' : '' }}
                                            >
                                                Papua Selatan
                                            </option>

                                            <option
                                                value="Papua Tengah"
                                                {{ $d->provinsi == 'Papua Tengah' ? 'selected' : '' }}
                                            >
                                                Papua Tengah
                                            </option>

                                            <option
                                                value="Papua Pegunungan"
                                                {{ $d->provinsi == 'Papua Pegunungan' ? 'selected' : '' }}
                                            >
                                                Papua Pegunungan
                                            </option>

                                            <option
                                                value="Papua Barat Daya"
                                                {{ $d->provinsi == 'Papua Barat Daya' ? 'selected' : '' }}
                                            >
                                                Papua Barat Daya
                                            </option>

                                        </optgroup>

                                    </select>

                                </div>


                                <div>

                                    <label
                                        for="edit_gender_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Gender
                                    </label>

                                    <select
                                        name="gender"
                                        id="edit_gender_{{ $d->id }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        required
                                    >

                                        <option
                                            value="Pria"
                                            {{ $d->gender == 'Pria' ? 'selected' : '' }}
                                        >
                                            Pria
                                        </option>

                                        <option
                                            value="Wanita"
                                            {{ $d->gender == 'Wanita' ? 'selected' : '' }}
                                        >
                                            Wanita
                                        </option>

                                    </select>

                                </div>

                            </div>


                            {{-- CATEGORY --}}
                            <div>

                                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700">
                                    Kategori
                                </label>

                                @php
                                    $selectedCats = explode(', ', $d->category);
                                @endphp

                                <div class="max-h-[140px] overflow-y-auto rounded-xl border border-zinc-300 bg-white p-3">

                                    <div class="space-y-1">

                                        @foreach($categories as $cat)

                                            <div class="flex items-center gap-3 rounded-lg px-2 py-1.5 transition hover:bg-zinc-50">

                                                <input
                                                    type="checkbox"
                                                    name="categories_array[]"
                                                    value="{{ $cat }}"
                                                    class="h-4 w-4 rounded border-zinc-300 text-[#161616] focus:ring-zinc-900"
                                                    id="edit_cat_{{ $d->id }}_{{ $loop->index }}"
                                                    {{ in_array($cat, $selectedCats) ? 'checked' : '' }}
                                                >

                                                <label
                                                    for="edit_cat_{{ $d->id }}_{{ $loop->index }}"
                                                    class="cursor-pointer text-xs font-medium text-zinc-700"
                                                >
                                                    {{ $cat }}
                                                </label>

                                            </div>

                                        @endforeach

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                        RATE CARD + SOCIAL
                    ================================================== --}}
                    <div class="space-y-5">


                        {{-- RATE CARD --}}
                        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

                            <div class="border-b border-zinc-200 px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">

                                        <svg
                                            class="h-4 w-4"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path d="M20 12V8H6a2 2 0 0 1 0-4h14v4"/>
                                            <path d="M4 6v14a2 2 0 0 0 2 2h14V8"/>
                                            <path d="M18 12h2"/>
                                        </svg>

                                    </div>

                                    <div>
                                        <h3 class="text-sm font-bold text-zinc-950">
                                            Rate Card
                                        </h3>

                                        <p class="text-[11px] text-zinc-500">
                                            Harga layanan affiliator
                                        </p>
                                    </div>

                                </div>

                            </div>


                            <div class="grid grid-cols-2 gap-x-4 gap-y-5 p-5">

                                <div>

                                    <label
                                        for="edit_detail_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Detail
                                    </label>

                                    <input
                                        type="text"
                                        name="detail"
                                        id="edit_detail_{{ $d->id }}"
                                        value="{{ $d->detail }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="edit_mirroring_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Mirroring
                                    </label>

                                    <input
                                        type="text"
                                        name="mirroring"
                                        id="edit_mirroring_{{ $d->id }}"
                                        value="{{ $d->mirroring }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="edit_reels_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Reels IG
                                    </label>

                                    <input
                                        type="text"
                                        name="reels"
                                        id="edit_reels_{{ $d->id }}"
                                        value="{{ $d->reels }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="edit_kerkun_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        Kerkun
                                    </label>

                                    <input
                                        type="text"
                                        name="kerkun"
                                        id="edit_kerkun_{{ $d->id }}"
                                        value="{{ $d->kerkun }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="edit_vt_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        VT
                                    </label>

                                    <input
                                        type="text"
                                        name="vt"
                                        id="edit_vt_{{ $d->id }}"
                                        value="{{ $d->vt }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>


                                <div>

                                    <label
                                        for="edit_story_{{ $d->id }}"
                                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-700"
                                    >
                                        IG Story
                                    </label>

                                    <input
                                        type="text"
                                        name="story"
                                        id="edit_story_{{ $d->id }}"
                                        value="{{ $d->story }}"
                                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                    >

                                </div>

                            </div>

                        </div>


                        {{-- SOCIAL --}}
                        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

                            <div class="space-y-4 p-5">


                                {{-- SHOPEE --}}
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">

                                    <div class="mb-3 flex items-center gap-2">

                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-zinc-700 shadow-sm">

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <path d="M6 2l1.5 4h9L18 2"/>
                                                <path d="M3 6h18l-1.5 15h-15z"/>
                                                <path d="M9 10v7"/>
                                                <path d="M15 10v7"/>
                                            </svg>

                                        </div>

                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                                            Shopee
                                        </span>

                                    </div>


                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                        <input
                                            type="text"
                                            name="user_shopee"
                                            value="{{ $d->user_shopee }}"
                                            placeholder="User"
                                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        >

                                        <input
                                            type="text"
                                            name="url_shopee"
                                            value="{{ $d->url_shopee }}"
                                            placeholder="URL"
                                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        >

                                        <input
                                            type="number"
                                            name="fol_shopee"
                                            value="{{ $d->fol_shopee }}"
                                            placeholder="Fol"
                                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        >

                                    </div>

                                </div>


                                {{-- INSTAGRAM --}}
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">

                                    <div class="mb-3 flex items-center gap-2">

                                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-zinc-700 shadow-sm">

                                            <svg
                                                class="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <rect
                                                    x="3"
                                                    y="3"
                                                    width="18"
                                                    height="18"
                                                    rx="5"
                                                />

                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="4"
                                                />

                                                <circle
                                                    cx="17.5"
                                                    cy="6.5"
                                                    r=".75"
                                                    fill="currentColor"
                                                    stroke="none"
                                                />
                                            </svg>

                                        </div>

                                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-700">
                                            Instagram
                                        </span>

                                    </div>


                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                        <input
                                            type="text"
                                            name="user_ig"
                                            value="{{ $d->user_ig }}"
                                            placeholder="User"
                                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        >

                                        <input
                                            type="text"
                                            name="url_ig"
                                            value="{{ $d->url_ig }}"
                                            placeholder="URL"
                                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        >

                                        <input
                                            type="number"
                                            name="fol_ig"
                                            value="{{ $d->fol_ig }}"
                                            placeholder="Fol"
                                            class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">

                <label
                    for="editModal{{ $d->id }}"
                    class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                >
                    Batal
                </label>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#161616] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M20 11a8.1 8.1 0 0 0-15.5-2"/>
                        <path d="M4 4v5h5"/>
                        <path d="M4 13a8.1 8.1 0 0 0 15.5 2"/>
                        <path d="M20 20v-5h-5"/>
                    </svg>

                    Update Data
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ================================================================
    MODAL DELETE
================================================================ --}}
<input
    type="checkbox"
    id="deleteModal{{ $d->id }}"
    class="peer sr-only"
>

<div
    class="pointer-events-none fixed inset-0 z-[100000] flex items-center justify-center p-4 opacity-0 transition duration-200 peer-checked:pointer-events-auto peer-checked:opacity-100"
>

    <label
        for="deleteModal{{ $d->id }}"
        class="absolute inset-0 cursor-pointer bg-zinc-950/60 backdrop-blur-sm"
    ></label>


    <div
        role="dialog"
        aria-modal="true"
        class="relative z-10 w-full max-w-md scale-95 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition duration-200 peer-checked:scale-100"
    >

        {{-- HEADER --}}
        <div class="flex items-center justify-between bg-[#161616] px-5 py-4 text-white">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M12 9v4"/>
                        <path d="M12 17h.01"/>
                        <path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/>
                    </svg>

                </div>

                <h2 class="text-sm font-bold uppercase tracking-wider">
                    Konfirmasi Hapus
                </h2>

            </div>


            <label
                for="deleteModal{{ $d->id }}"
                class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                >
                    <path d="M6 6l12 12"/>
                    <path d="M18 6 6 18"/>
                </svg>
            </label>

        </div>


        {{-- BODY --}}
        <div class="px-6 py-7 text-center">

            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-700">

                <svg
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M3 6h18"/>
                    <path d="M8 6V4h8v2"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v5"/>
                    <path d="M14 11v5"/>
                </svg>

            </div>

            <p class="text-sm text-zinc-600">
                Apakah Anda yakin ingin menghapus data affiliator:
            </p>

            <h3 class="mt-2 text-lg font-bold text-zinc-950">
                {{ $d->user_tt }}
            </h3>

            <p class="mt-2 text-xs text-zinc-400">
                *Data yang dihapus tidak dapat dikembalikan.
            </p>

        </div>


        {{-- FOOTER --}}
        <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:flex-row sm:justify-center">

            <label
                for="deleteModal{{ $d->id }}"
                class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
            >
                Batal
            </label>

            <form
                action="{{ route('social_creator.destroy', $d->id) }}"
                method="POST"
            >

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#161616] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 sm:w-auto"
                >

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M3 6h18"/>
                        <path d="M8 6V4h8v2"/>
                        <path d="M19 6l-1 14H6L5 6"/>
                        <path d="M10 11v5"/>
                        <path d="M14 11v5"/>
                    </svg>

                    Ya, Hapus Data

                </button>

            </form>

        </div>

    </div>

</div>

@endforeach


{{-- ================================================================
    JAVASCRIPT
================================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | CHOICES.JS
    |--------------------------------------------------------------------------
    */

    const config = {
        searchEnabled: true,
        itemSelectText: '',
        shouldSort: false,
        placeholder: true,
        position: 'bottom',

        classNames: {
            containerInner: 'choices__inner',
        }
    };


    // Modal Tambah
    const elementTambah = document.getElementById('choices-provinsi');

    if (elementTambah && typeof Choices !== 'undefined') {
        new Choices(elementTambah, config);
    }


    // Modal Edit
    const elementsEdit = document.querySelectorAll('.select-choices-edit');

    if (typeof Choices !== 'undefined') {

        elementsEdit.forEach(function (el) {
            new Choices(el, config);
        });

    }


    // Filter lokasi
    const elementsFilter = document.getElementById('filterLokasi');

    if (elementsFilter && typeof Choices !== 'undefined') {
        new Choices(elementsFilter, config);
    }


    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    @if(false)
    const table = $('#dataTables1').DataTable({

        responsive: {
            details: {
                type: 'column',
                target: -1
            }
        },

        autoWidth: false,

        scrollX: true,

        pageLength: 10,

        columnDefs: [

            {
                responsivePriority: 1,
                targets: [0, 1, -1]
            },

            {
                responsivePriority: 2,
                targets: [13, 14]
            }

        ]

    });


    /*
    |--------------------------------------------------------------------------
    | GLOBAL FILTER DATATABLE
    |--------------------------------------------------------------------------
    */

    $.fn.dataTable.ext.search.push(function (
        settings,
        data,
        dataIndex
    ) {

        const row = table.row(dataIndex).node();

        const cells = row
            ? row.querySelectorAll('td')
            : [];


        /*
        |--------------------------------------------------------------------------
        | FILTER ANGKA
        |--------------------------------------------------------------------------
        */

        const platform =
            $('#filterPlatform').val();

        const platformGMV =
            $('#filterPlatformGMV').val();


        const minFolInput =
            parseInt(
                document.getElementById('filterFollowers').value
            ) || 0;


        const minGMVInput =
            parseInt(
                document.getElementById('filterGMV').value
            ) || 0;


        if (!cells || cells.length === 0) {
            return true;
        }


        const folTT =
            parseInt(cells[3].dataset.fol) || 0;


        const folSHP =
            parseInt(cells[6].dataset.fol) || 0;


        const folIG =
            parseInt(cells[9].dataset.fol) || 0;


        const gmvTT =
            parseInt(cells[11].dataset.gmv) || 0;


        const gmvSHP =
            parseInt(cells[12].dataset.gmv) || 0;


        /*
        |--------------------------------------------------------------------------
        | FILTER TEKS
        |--------------------------------------------------------------------------
        */

        const fKat =
            document
                .getElementById('filterKategori')
                .value
                .toLowerCase();


        const fLok =
            document
                .getElementById('filterLokasi')
                .value
                .toLowerCase();


        const fGen =
            document
                .getElementById('filterGender')
                .value
                .toLowerCase()
                .trim();


        const txtKat =
            (data[10] || '').toLowerCase();


        const txtLok =
            (data[13] || '').toLowerCase();


        const txtGen =
            (data[14] || '').toLowerCase().trim();


        /*
        |--------------------------------------------------------------------------
        | FOLLOWERS
        |--------------------------------------------------------------------------
        */

        let matchFol = false;


        if (platform === 'tiktok') {

            matchFol =
                folTT >= minFolInput;

        } else if (platform === 'instagram') {

            matchFol =
                folIG >= minFolInput;

        } else if (platform === 'shopee') {

            matchFol =
                folSHP >= minFolInput;

        } else {

            matchFol =
                folTT >= minFolInput ||
                folSHP >= minFolInput ||
                folIG >= minFolInput;

        }


        /*
        |--------------------------------------------------------------------------
        | GMV
        |--------------------------------------------------------------------------
        */

        let matchGMV = false;


        if (platformGMV === 'tiktok') {

            matchGMV =
                gmvTT >= minGMVInput;

        } else if (platformGMV === 'shopee') {

            matchGMV =
                gmvSHP >= minGMVInput;

        } else {

            matchGMV =
                gmvTT >= minGMVInput ||
                gmvSHP >= minGMVInput;

        }


        /*
        |--------------------------------------------------------------------------
        | FILTER LAINNYA
        |--------------------------------------------------------------------------
        */

        const matchKat =
            fKat === '' ||
            txtKat.includes(fKat);


        const matchLok =
            fLok === '' ||
            txtLok.includes(fLok);


        const matchGen =
            fGen === '' ||
            txtGen === fGen;


        return (
            matchFol &&
            matchGMV &&
            matchKat &&
            matchLok &&
            matchGen
        );

    });


    /*
    |--------------------------------------------------------------------------
    | FILTER EVENTS
    |--------------------------------------------------------------------------
    */

    const filterInputs = [
        'filterKategori',
        'filterLokasi',
        'filterFollowers',
        'filterGMV',
        'filterGender'
    ];


    filterInputs.forEach(function (id) {

        const el =
            document.getElementById(id);


        if (!el) {
            return;
        }


        el.addEventListener(
            'input',
            function () {
                table.draw();
            }
        );


        el.addEventListener(
            'change',
            function () {
                table.draw();
            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | PLATFORM FILTER
    |--------------------------------------------------------------------------
    */

    const platformFilter =
        document.getElementById('filterPlatform');


    if (platformFilter) {

        platformFilter.addEventListener(
            'change',
            function () {
                table.draw();
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | GMV PLATFORM FILTER
    |--------------------------------------------------------------------------
    */

    const platformGMVFilter =
        document.getElementById('filterPlatformGMV');


    if (platformGMVFilter) {

        platformGMVFilter.addEventListener(
            'change',
            function () {
                table.draw();
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RESET FILTER
    |--------------------------------------------------------------------------
    */

    window.resetFilter = function () {

        const form =
            document.getElementById('filterForm');


        if (form) {
            form.reset();
        }


        /*
        |--------------------------------------------------------------------------
        | Reset Choices.js Provinsi
        |--------------------------------------------------------------------------
        */

        const lokasi =
            document.getElementById('filterLokasi');


        if (
            lokasi &&
            lokasi.choices
        ) {
            lokasi.choices.setChoiceByValue('');
        }


        table.draw();

    };


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL GLOBAL FILTER FUNCTION
    |--------------------------------------------------------------------------
    |
    | Dipertahankan karena HTML existing menggunakan:
    | onchange="filterTable()"
    |
    */

    window.filterTable = function () {

        if (typeof table !== 'undefined') {
            table.draw();
        }

    };
    @endif

});
</script>

@include('mcn.partials.native-table-script')

@endsection
