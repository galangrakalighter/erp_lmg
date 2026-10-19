@extends('layout.app')

@section('content')
<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                    KPI Management
                </p>

                <h1 class="text-3xl font-semibold tracking-[-0.03em] text-zinc-950 sm:text-4xl">
                    Project Overview
                </h1>

                <p class="mt-2 text-sm text-zinc-500">
                    Kelola KPI dan task untuk
                    <span class="font-medium text-zinc-800">{{ $client->nama }}</span>
                </p>
            </div>

            @if($canCreateTask)
                <button
                    type="button"
                    data-modal-open="tambahModal"
                    class="inline-flex w-fit items-center gap-2 rounded-xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>

                    Tambah KPI
                </button>
            @endif
        </div>

        {{-- =========================================================
            STAT CARDS
        ========================================================== --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Belum selesai --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                            Point Belum Selesai
                        </p>

                        <p
                            id="pointBelum"
                            class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-zinc-950"
                        >
                            0
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </div>
                </div>
            </div>


            {{-- Selesai --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                            Point Selesai
                        </p>

                        <p
                            id="pointSelesai"
                            class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-zinc-950"
                        >
                            0
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path d="M5 12l4 4L19 6"/>
                        </svg>
                    </div>
                </div>
            </div>


            {{-- Total point --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                            Total Point
                        </p>

                        <p
                            id="totalPoint"
                            class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-zinc-950"
                        >
                            0
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path d="M4 19h16M6 17V9M12 17V5M18 17v-7"/>
                        </svg>
                    </div>
                </div>
            </div>


            {{-- Total KPI --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-500">
                            Total KPI
                        </p>

                        <p
                            id="totalKPI"
                            class="mt-3 text-3xl font-semibold tracking-[-0.03em] text-zinc-950"
                        >
                            0
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <rect x="4" y="4" width="16" height="16" rx="2"/>
                            <path d="M8 9h8M8 13h8M8 17h5"/>
                        </svg>
                    </div>
                </div>
            </div>

        </div>


        {{-- =========================================================
            MAIN CARD
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)]">

            {{-- Card header --}}
            <div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        Project
                    </p>

                    <h2 class="mt-1 text-lg font-semibold tracking-[-0.02em] text-zinc-950">
                        {{ $client->nama }}
                    </h2>
                </div>

                {{-- @if(Auth::user()->role != "content_creator")
                    <button
                        type="button"
                        data-modal-open="tambahModal"
                        class="inline-flex w-fit items-center gap-2 rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <path d="M12 5v14M5 12h14"/>
                        </svg>

                        Tambah Task
                    </button>
                @endif --}}
            </div>


            {{-- =====================================================
                FILTER
            ====================================================== --}}
            <div class="border-b border-zinc-200 bg-zinc-50/70 px-5 py-5 sm:px-6">
                <form
                    id="filterForm"
                    action="{{ route('hc_overview.index', $id) }}"
                    method="GET"
                >
                    @if($readOnly)<input type="hidden" name="readonly" value="1">@endif
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

                        {{-- Mulai --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                                Mulai
                            </label>

                            <input
                                type="date"
                                name="start_date"
                                value="{{ request('start_date') }}"
                                class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                        </div>


                        {{-- Sampai --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                                Sampai
                            </label>

                            <input
                                type="date"
                                name="end_date"
                                value="{{ request('end_date') }}"
                                class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                        </div>


                        {{-- Divisi --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                                Divisi
                            </label>

                            <select
                                name="divisi"
                                class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="">- Semua -</option>

                                @foreach($result as $divId => $nama)
                                    <option
                                        value="{{ $divId }}"
                                        {{ request('divisi') == $divId ? 'selected' : '' }}
                                    >
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        {{-- Point --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                                Point
                            </label>

                            <select
                                name="priority"
                                class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="">- Semua -</option>

                                @foreach($difficultyLabels as $key => $label)
                                    <option
                                        value="{{ $key }}"
                                        {{ request('priority') == $key ? 'selected' : '' }}
                                    >
                                        {{ $key }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        {{-- PIC --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                                PIC
                            </label>

                            <select
                                name="pic"
                                class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="">- Semua PIC -</option>

                                @foreach($vic_list as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        {{ request('pic') == $user->id ? 'selected' : '' }}
                                    >
                                        {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        {{-- Status --}}
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                                Status
                            </label>

                            <select
                                name="status"
                                class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="">- Semua -</option>

                                <option
                                    value="0"
                                    {{ request('status') === '0' ? 'selected' : '' }}
                                >
                                    Belum Selesai
                                </option>

                                <option
                                    value="1"
                                    {{ request('status') === '1' ? 'selected' : '' }}
                                >
                                    Sudah Selesai
                                </option>
                            </select>
                        </div>

                    </div>


                    {{-- Filter buttons --}}
                    <div class="mt-5 flex flex-wrap items-center gap-2">

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                            </svg>

                            Filter
                        </button>

                        <a
                            href="#"
                            id="resetFilter"
                            class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:border-zinc-400 hover:bg-zinc-50"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path d="M3 4v5h5"/>
                            </svg>

                            Reset
                        </a>

                    </div>
                </form>
            </div>


            {{-- =====================================================
                TABLE
            ====================================================== --}}
            <div class="p-5 sm:p-6">

                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label class="relative block w-full sm:max-w-sm">
                        <span class="sr-only">Cari data KPI</span>
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input id="kpiTableSearch" type="search" placeholder="Cari KPI, divisi, pembuat, atau penerima..." class="w-full rounded-xl border border-zinc-200 bg-white py-2.5 pl-10 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                    </label>

                    <label class="flex items-center gap-2 text-xs font-semibold text-zinc-500">
                        Tampilkan
                        <select id="kpiTablePageSize" class="rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm font-semibold text-zinc-800 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        data
                    </label>
                </div>

                <div class="overflow-x-auto rounded-xl border border-zinc-200">
                    <table
                        id="dataTable"
                        class="min-w-[1200px] w-full border-collapse text-left"
                    >
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50">
                                <th class="whitespace-nowrap px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    No
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    KPI
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Divisi
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Pembuat
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Penerima
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Point
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Mulai
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Deadline
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Tanggal Approve
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Status
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Note
                                </th>

                                <th class="whitespace-nowrap px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-100">
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p id="kpiTableInfo" class="text-xs font-medium text-zinc-500">Menampilkan 0 data</p>
                    <div id="kpiTablePagination" class="flex flex-wrap items-center gap-1" aria-label="Paginasi data KPI"></div>
                </div>

            </div>
        </div>

    </div>
</div>


{{-- =============================================================
    TAMBAH KPI MODAL
============================================================= --}}
<div
    id="tambahModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="tambahModal"
    >
        <div class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-950 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        KPI Management
                    </p>

                    <h2 class="mt-1 text-lg font-semibold text-white">
                        Tambah KPI Baru
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="tambahModal"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>


            <form
                action="{{ route('kpi.store') }}"
                method="POST"
            >
                @csrf

                <input
                    type="hidden"
                    name="project"
                    value="{{ $id }}"
                >

                <div class="max-h-[75vh] overflow-y-auto bg-[#f7f7f5] px-5 py-6 sm:px-6">

                    {{-- Section --}}
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-950 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-4 w-4"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v5M12 8h.01"/>
                            </svg>
                        </div>

                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                Information
                            </p>

                            <h3 class="text-sm font-semibold text-zinc-950">
                                Informasi KPI
                            </h3>
                        </div>
                    </div>


                    {{-- Nama KPI --}}
                    <div class="mb-5">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Nama KPI
                        </label>

                        <input
                            type="text"
                            name="nama_kpi"
                            placeholder="Masukkan nama KPI..."
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                    </div>


                    {{-- Client + Divisi --}}
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                                Client
                            </label>

                            <select
                                name="project"
                                required
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="" selected disabled>
                                    -- Pilih Client --
                                </option>

                                <option
                                    value="{{ $client->id }}"
                                    {{ $client->id == $id ? 'selected' : '' }}
                                >
                                    {{ $client->nama }}
                                </option>
                            </select>
                        </div>


                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                                Divisi
                            </label>

                            <select
                                name="divisi"
                                onchange="HandleChangeDivsion(this, 'pic_select')"
                                required
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="" selected disabled>
                                    -- Pilih Divisi --
                                </option>

                                @foreach($result as $divId => $nama)
                                    <option value="{{ $divId }}">
                                        {{ $nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>


                    {{-- PIC + Point --}}
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                                PIC (Person In Charge)
                            </label>

                            <select
                                name="pic[]"
                                id="pic_select"
                                multiple
                                required
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                            </select>

                            <p class="mt-1.5 text-xs text-zinc-500">
                                Gunakan Ctrl + Klik untuk memilih banyak PIC
                            </p>
                        </div>


                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                                Tingkat Kesulitan (Point)
                            </label>

                            <select
                                name="point"
                                required
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="" selected disabled>
                                    -- Pilih Tingkat Kesulitan --
                                </option>

                                @foreach($difficultyLabels as $key => $label)
                                    <option value="{{ $key }}">
                                        {{ $key }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>


                    {{-- Dates --}}
                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                                Tanggal Mulai
                            </label>

                            <input
                                type="date"
                                name="tanggal_mulai"
                                required
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                        </div>


                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                                Deadline
                            </label>

                            <input
                                type="date"
                                name="tanggal_deadline"
                                required
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                        </div>

                    </div>


                    {{-- Note --}}
                    <div class="mt-5">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Catatan
                        </label>

                        <textarea
                            name="note"
                            rows="3"
                            placeholder="Tambahkan rincian KPI..."
                            class="block w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        ></textarea>
                    </div>

                </div>


                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">

                    <button
                        type="button"
                        data-modal-close="tambahModal"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-950 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <path d="M17 21v-8H7v8M7 3v5h8"/>
                        </svg>

                        Simpan KPI
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


{{-- =============================================================
    EDIT KPI MODAL
============================================================= --}}
<div
    id="editModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="editModal"
    >
        <div class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-zinc-800 bg-zinc-950 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        KPI Management
                    </p>

                    <h2 class="mt-1 text-lg font-semibold text-white">
                        Edit KPI
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="editModal"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-5 w-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>

            <form
                id="editForm"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div
                    id="editModalBody"
                    class="max-h-[75vh] overflow-y-auto bg-[#f7f7f5] px-5 py-6 sm:px-6"
                >
                </div>

                <div class="flex justify-end gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:px-6">

                    <button
                        type="button"
                        data-modal-close="editModal"
                        class="rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        Update KPI
                    </button>

                </div>
            </form>
        </div>
    </div>
</div>


{{-- =============================================================
    HAPUS MODAL
============================================================= --}}
<div
    id="hapusModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4"
        data-modal-backdrop="hapusModal"
    >
        <div class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"></div>

        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="border-b border-zinc-200 px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                    Confirmation
                </p>

                <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                    Konfirmasi Hapus
                </h2>
            </div>

            <div class="px-5 py-5">
                <div class="flex gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2">
                            <path d="M12 9v4M12 17h.01"/>
                            <path d="M10.3 3.7L2.9 16.5A2 2 0 0 0 4.6 19.5h14.8a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0z"/>
                        </svg>
                    </div>

                    <p class="pt-1 text-sm leading-6 text-zinc-600">
                        Yakin ingin menghapus KPI ini?
                    </p>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-zinc-200 px-5 py-4">

                <form
                    id="hapusForm"
                    method="POST"
                    class="flex gap-2"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="button"
                        data-modal-close="hapusModal"
                        class="rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                    >
                        Hapus
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>


@push('script')
<script>
    const baseUrl = window.location.origin;

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function openModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        const hasOpenModal = document.querySelector(
            '[id$="Modal"]:not(.hidden)'
        );

        if (!hasOpenModal) {
            document.body.classList.remove('overflow-hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Modal triggers
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        const openButton = event.target.closest('[data-modal-open]');

        if (openButton) {
            event.preventDefault();

            openModal(
                openButton.getAttribute('data-modal-open')
            );

            return;
        }


        const closeButton = event.target.closest('[data-modal-close]');

        if (closeButton) {
            event.preventDefault();

            closeModal(
                closeButton.getAttribute('data-modal-close')
            );

            return;
        }


        const backdrop = event.target.closest('[data-modal-backdrop]');

        if (
            backdrop &&
            event.target === backdrop
        ) {
            closeModal(
                backdrop.getAttribute('data-modal-backdrop')
            );
        }
    });


    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }

        const visibleModal = document.querySelector(
            '[id$="Modal"]:not(.hidden)'
        );

        if (visibleModal) {
            closeModal(visibleModal.id);
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Load Table
    |--------------------------------------------------------------------------
    */

    let kpiTableRows = [];
    let kpiTablePage = 1;

    function renderKpiTable() {
        const search = (document.getElementById('kpiTableSearch')?.value || '').trim().toLowerCase();
        const pageSize = Number(document.getElementById('kpiTablePageSize')?.value || 10);
        const filteredRows = kpiTableRows.filter(row => !search || row.textContent.toLowerCase().includes(search));
        const tbody = document.querySelector('#dataTable tbody');
        document.getElementById('kpiNoResultsRow')?.remove();

        if (!kpiTableRows.length) {
            tbody.innerHTML = '<tr><td colspan="12" class="px-6 py-14 text-center text-sm text-zinc-500">Belum ada data KPI.</td></tr>';
            document.getElementById('kpiTableInfo').textContent = 'Menampilkan 0 data';
            document.getElementById('kpiTablePagination').innerHTML = '';
            return;
        }

        if (!filteredRows.length) {
            kpiTableRows.forEach(row => row.classList.add('hidden'));
            tbody.insertAdjacentHTML('beforeend', '<tr id="kpiNoResultsRow"><td colspan="12" class="px-6 py-14 text-center text-sm text-zinc-500">Data yang dicari tidak ditemukan.</td></tr>');
            document.getElementById('kpiTableInfo').textContent = 'Menampilkan 0 data';
            document.getElementById('kpiTablePagination').innerHTML = '';
            return;
        }

        const totalPages = Math.max(1, Math.ceil(filteredRows.length / pageSize));
        kpiTablePage = Math.min(Math.max(1, kpiTablePage), totalPages);

        const start = (kpiTablePage - 1) * pageSize;
        const visibleRows = new Set(filteredRows.slice(start, start + pageSize));

        kpiTableRows.forEach(row => row.classList.toggle('hidden', !visibleRows.has(row)));
        filteredRows.slice(start, start + pageSize).forEach((row, index) => {
            if (row.cells[0]) row.cells[0].textContent = start + index + 1;
        });

        const info = document.getElementById('kpiTableInfo');
        if (info) {
            const from = filteredRows.length ? start + 1 : 0;
            const to = Math.min(start + pageSize, filteredRows.length);
            info.textContent = `Menampilkan ${from}–${to} dari ${filteredRows.length} data`;
        }

        const pagination = document.getElementById('kpiTablePagination');
        if (!pagination) return;

        const button = (label, page, active = false, disabled = false) => `
            <button type="button" data-kpi-page="${page}" ${disabled ? 'disabled' : ''}
                class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2.5 text-xs font-bold transition ${active ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-zinc-400 hover:text-zinc-950'} disabled:cursor-not-allowed disabled:opacity-35">
                ${label}
            </button>`;

        const pages = [];
        for (let page = 1; page <= totalPages; page++) {
            if (page === 1 || page === totalPages || Math.abs(page - kpiTablePage) <= 1) pages.push(page);
        }

        let previous = 0;
        const pageButtons = pages.map(page => {
            const separator = previous && page - previous > 1
                ? '<span class="px-1 text-xs text-zinc-400">…</span>'
                : '';
            previous = page;
            return separator + button(page, page, page === kpiTablePage);
        }).join('');

        pagination.innerHTML = button('‹', kpiTablePage - 1, false, kpiTablePage === 1)
            + pageButtons
            + button('›', kpiTablePage + 1, false, kpiTablePage === totalPages);
    }

    document.getElementById('kpiTableSearch')?.addEventListener('input', () => {
        kpiTablePage = 1;
        renderKpiTable();
    });

    document.getElementById('kpiTablePageSize')?.addEventListener('change', () => {
        kpiTablePage = 1;
        renderKpiTable();
    });

    document.getElementById('kpiTablePagination')?.addEventListener('click', event => {
        const target = event.target.closest('[data-kpi-page]');
        if (!target || target.disabled) return;
        kpiTablePage = Number(target.dataset.kpiPage);
        renderKpiTable();
    });

    function loadTable() {

        if (window.jQuery && $.fn.DataTable && $.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }

        const form = document.getElementById('filterForm');

        const params = new URLSearchParams({
            start_date: form.querySelector('[name="start_date"]').value,
            end_date: form.querySelector('[name="end_date"]').value,
            divisi: form.querySelector('[name="divisi"]').value,
            priority: form.querySelector('[name="priority"]').value,
            pic: form.querySelector('[name="pic"]').value,
            status: form.querySelector('[name="status"]').value,
        });


        const apiUrl =
            `/api/kpi-data/{{ $client->id }}/{{ Auth::id() }}?${params.toString()}`;


        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {

            console.log(response);

            if (!response.ok) {
                throw new Error('Gagal mengambil data KPI.');
            }

            return response.json();
        })
        .then(response => {

            const tbody = document.querySelector('#dataTable tbody');

            tbody.innerHTML = '';


            let pointBelum = 0;
            let pointSelesai = 0;
            let totalPoint = 0;


            console.log(response.data);


            response.data.forEach(function(item, index) {

                const isCompleted = Number(item.status) === 1;


                if (isCompleted) {

                    pointSelesai += Number(item.point);

                } else {

                    pointBelum += Number(item.point);

                }


                totalPoint += Number(item.point);


                /*
                |--------------------------------------------------------------------------
                | Encode item
                |--------------------------------------------------------------------------
                */

                const itemEncoded = JSON.stringify(item)
                    .replace(/"/g, '&quot;');


                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                const status = isCompleted

                    ? `
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Sudah Selesai
                        </span>
                    `

                    : `
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-600"></span>
                            Belum Selesai
                        </span>
                    `;


                /*
                |--------------------------------------------------------------------------
                | Action
                |--------------------------------------------------------------------------
                */

                const action = @json($readOnly) ? '<span class="text-xs font-semibold text-zinc-400">Hanya lihat</span>' : (!isCompleted

                    ? `
                        <div class="flex flex-wrap items-center justify-center gap-1.5">

                            <button
                                type="button"
                                onclick="openEditModal(${itemEncoded})"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 transition hover:border-zinc-950 hover:text-zinc-950"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-3.5 w-3.5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>

                                Edit
                            </button>


                            <button
                                type="button"
                                onclick="selesaikanKPI(${item.id})"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-3.5 w-3.5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M5 12l4 4L19 6"/>
                                </svg>

                                Selesai
                            </button>


                            <button
                                type="button"
                                onclick="openDeleteModal(${item.id})"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-3.5 w-3.5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                                    <path d="M10 11v5M14 11v5"/>
                                </svg>

                                Hapus
                            </button>

                        </div>
                    `

                    : `
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-semibold text-zinc-700">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-3.5 w-3.5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M8 12l2.5 2.5L16 9"/>
                            </svg>

                            KPI Selesai
                        </span>
                    `);


                /*
                |--------------------------------------------------------------------------
                | Row
                |--------------------------------------------------------------------------
                */

                const row = `
                    <tr class="transition hover:bg-zinc-50">

                        <td class="whitespace-nowrap px-4 py-4 text-center text-sm text-zinc-500">
                            ${index + 1}
                        </td>

                        <td class="min-w-[220px] px-4 py-4">
                            <div class="font-medium text-zinc-950">
                                ${item.nama_kpi ?? ''}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600">
                            ${item.nama_divisi ?? ''}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600">
                            ${item.pembuat ?? ''}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600">
                            ${item.vic_email ?? ''}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            <span class="inline-flex min-w-8 items-center justify-center rounded-lg bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-800">
                                ${item.point}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600">
                            ${item.tanggal_mulai ?? ''}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600">
                            ${item.tanggal_deadline ?? ''}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-sm text-zinc-600">
                            ${item.tanggal_approve ?? '-'}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-center">
                            ${status}
                        </td>

                        <td class="min-w-[200px] px-4 py-4 text-sm leading-6 text-zinc-500">
                            ${item.note ?? ''}
                        </td>

                        <td class="min-w-[260px] px-4 py-4 text-center">
                            ${action}
                        </td>

                    </tr>
                `;


                tbody.insertAdjacentHTML('beforeend', row);
            });


            /*
            |--------------------------------------------------------------------------
            | Update statistics
            |--------------------------------------------------------------------------
            */

            document.getElementById('pointBelum').textContent = pointBelum;
            document.getElementById('pointSelesai').textContent = pointSelesai;
            document.getElementById('totalPoint').textContent = totalPoint;
            document.getElementById('totalKPI').textContent = response.data.length;
            kpiTableRows = Array.from(tbody.querySelectorAll('tr'));
            kpiTablePage = 1;
            renderKpiTable();

        })
        .catch(error => {

            console.error(error);

            kpiTableRows = [];
            document.getElementById('kpiTableInfo').textContent = 'Data gagal dimuat';
            document.getElementById('kpiTablePagination').innerHTML = '';
            document.querySelector('#dataTable tbody').innerHTML = `
                <tr>
                    <td
                        colspan="12"
                        class="px-4 py-10 text-center"
                    >
                        <div class="flex flex-col items-center justify-center">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path d="M12 9v4M12 17h.01"/>
                                    <path d="M10.3 3.7L2.9 16.5A2 2 0 0 0 4.6 19.5h14.8a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0z"/>
                                </svg>
                            </div>

                            <p class="text-sm font-medium text-zinc-700">
                                Gagal memuat data KPI
                            </p>

                            <p class="mt-1 text-xs text-zinc-500">
                                Silakan coba lagi.
                            </p>
                        </div>
                    </td>
                </tr>
            `;
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Document Ready
    |--------------------------------------------------------------------------
    */

    document.addEventListener('DOMContentLoaded', function () {

        const filterForm = document.getElementById('filterForm');
        const resetFilter = document.getElementById('resetFilter');

        filterForm.addEventListener('submit', function (event) {

            event.preventDefault();

            loadTable();
        });


        resetFilter.addEventListener('click', function (event) {

            event.preventDefault();

            filterForm.reset();

            loadTable();
        });


        /*
        |--------------------------------------------------------------------------
        | Select2 PIC
        |--------------------------------------------------------------------------
        */

        if (
            window.jQuery &&
            $.fn.select2
        ) {

            $('#pic_select').select2({
                placeholder: '-- Pilih PIC --',
                width: '100%',
                dropdownParent: $('#tambahModal')
            });

        }


        loadTable();
    });


    /*
    |--------------------------------------------------------------------------
    | Selesaikan KPI
    |--------------------------------------------------------------------------
    */

    async function selesaikanKPI(id) {

        if (!confirm('Yakin ingin menyelesaikan KPI ini?')) {
            return;
        }


        try {

            const response = await fetch(`/kpi/${id}/selesai`, {
                method: 'PUT',

                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });


            const result = await response.json();


            if (result.success) {

                alert('KPI berhasil diselesaikan.');

                loadTable();

            } else {

                alert(
                    result.message || 'Gagal menyelesaikan KPI.'
                );
            }

        } catch (error) {

            console.error(error);

            alert('Terjadi kesalahan.');

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Modal
    |--------------------------------------------------------------------------
    */

    function openEditModal(item) {

        document.getElementById('editForm').setAttribute(
            'action',
            '/kpi/' + item.id
        );


        const selectedPoint = item.point;


        document.getElementById('editModalBody').innerHTML = `

            <div class="space-y-5">

                {{-- Nama KPI --}}
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                        Nama KPI
                    </label>

                    <input
                        type="text"
                        name="nama_kpi"
                        value="${item.nama_kpi ?? ''}"
                        required
                        class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >
                </div>


                {{-- Divisi + PIC --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Divisi
                        </label>

                        <select
                            name="divisi"
                            onchange="HandleChangeDivsion(this, 'edit_pic_select')"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                            <option
                                value="${item.divisi}"
                                selected
                            >
                                ${item.nama_divisi ?? ''}
                            </option>
                        </select>
                    </div>


                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            PIC
                        </label>

                        <select
                            name="pic[]"
                            id="edit_pic_select"
                            multiple
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                        </select>
                    </div>

                </div>


                {{-- Point + Status --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Point (Kesulitan)
                        </label>

                        <select
                            name="point"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                            ${generateDifficultyOptions(selectedPoint)}
                        </select>
                    </div>


                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Status
                        </label>

                        <select
                            name="status"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                            <option
                                value="0"
                                ${item.status == 0 ? 'selected' : ''}
                            >
                                Belum Selesai
                            </option>

                            <option
                                value="1"
                                ${item.status == 1 ? 'selected' : ''}
                            >
                                Sudah Selesai
                            </option>
                        </select>
                    </div>

                </div>


                {{-- Dates --}}
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Mulai
                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            value="${item.tanggal_mulai ?? ''}"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                    </div>


                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                            Deadline
                        </label>

                        <input
                            type="date"
                            name="tanggal_deadline"
                            value="${item.tanggal_deadline ?? ''}"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                    </div>

                </div>


                {{-- Note --}}
                <div>
                    <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-700">
                        Catatan
                    </label>

                    <textarea
                        name="note"
                        rows="3"
                        class="block w-full resize-none rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >${item.note ?? ''}</textarea>
                </div>

            </div>
        `;


        /*
        |--------------------------------------------------------------------------
        | Select2 Edit PIC
        |--------------------------------------------------------------------------
        */

        if (
            window.jQuery &&
            $.fn.select2
        ) {

            $('#edit_pic_select').select2({
                width: '100%',
                placeholder: 'Pilih PIC',
                dropdownParent: $('#editModal')
            });

        }


        openModal('editModal');


        /*
        |--------------------------------------------------------------------------
        | Selected PIC
        |--------------------------------------------------------------------------
        */

        const selectedPics = (item.pics || [])
            .map(pic => pic.id.toString());


        HandleChangeDivsion(
            { value: item.divisi },
            'edit_pic_select',
            selectedPics
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Difficulty Options
    |--------------------------------------------------------------------------
    */

    function generateDifficultyOptions(selectedPoint) {

        let options = '';


        for (let i = 1; i <= 5; i++) {

            options += `
                <option
                    value="${i}"
                    ${i == selectedPoint ? 'selected' : ''}
                >
                    ${i}
                </option>
            `;
        }


        return options;
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Modal
    |--------------------------------------------------------------------------
    */

    function openDeleteModal(id) {

        document.getElementById('hapusForm').setAttribute(
            'action',
            '/kpi/' + id
        );


        openModal('hapusModal');
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Division Change
    |--------------------------------------------------------------------------
    */

    async function HandleChangeDivsion(
        el,
        targetId,
        selectedPic = []
    ) {

        const picSelect = document.getElementById(targetId);

        const userId = {{ Auth::id() }};


        if (!picSelect) {
            return;
        }

        // Gunakan sumber pengguna global agar pilihan PIC selalu mengikuti divisi
        // dan hak akses akun yang sedang masuk.
        if (window.lmgPopulateUserSelect) {
            await window.lmgPopulateUserSelect(picSelect, el.value);
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Clear Select2
        |--------------------------------------------------------------------------
        */

        if (
            window.jQuery &&
            $.fn.select2
        ) {

            $(`#${targetId}`)
                .empty()
                .trigger('change');

        } else {

            picSelect.innerHTML = '';

        }


        try {

            const response = await fetch(
                `${baseUrl}/api/get-users/${el.value}/${userId}`
            );


            if (!response.ok) {
                throw new Error();
            }


            const data = await response.json();


            data.forEach(pic => {

                const selected =
                    selectedPic.includes(
                        pic.id.toString()
                    );


                const option = new Option(
                    pic.email,
                    pic.id,
                    selected,
                    selected
                );


                picSelect.appendChild(option);

            });


            if (
                window.jQuery &&
                $.fn.select2
            ) {

                $(`#${targetId}`).trigger('change');

            }

        } catch (error) {

            console.error(error);

        }
    }
</script>
@endpush

@endsection
