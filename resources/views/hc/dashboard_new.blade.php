@extends('layout.app')

@section('content')
@include('hc.partials.dashboard_tabs')
    <input type="hidden" id="divisi" value="{{ $divisiId }}">

    <div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1600px]">

            {{-- =========================
                HEADER
            ========================== --}}
            <div class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        Task Management
                    </div>

                    <h1 class="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                        Halo, {{ Auth::user()->name }} 👋
                    </h1>

                    <p class="mt-2 text-sm text-zinc-500">
                        Selamat bekerja hari ini
                    </p>
                </div>

                {{-- FILTER --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center xl:justify-end">

                    {{-- Filter Project --}}
                    <div class="relative">
                        <select
                            id="filter-client"
                            onchange="loadTaskStats()"
                            class="h-10 min-w-[170px] appearance-none rounded-xl border border-zinc-200 bg-white pl-4 pr-10 text-sm text-zinc-700 outline-none transition focus:border-zinc-950 focus:ring-0"
                        >
                            <option value="all">Semua Project</option>

                            @foreach($client as $p)
                                <option value="{{ $p->id_client }}">
                                    {{ $p->nama_client }}
                                </option>
                            @endforeach
                        </select>

                        <svg
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>

                    {{-- Filter PIC --}}
                    <div class="relative">
                        <select
                            id="filter-pic"
                            onchange="loadTaskStats()"
                            class="h-10 min-w-[170px] appearance-none rounded-xl border border-zinc-200 bg-white pl-4 pr-10 text-sm text-zinc-700 outline-none transition focus:border-zinc-950 focus:ring-0"
                        >
                            <option value="all">Semua Bawahan</option>

                            @foreach($allUser as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->email }}
                                </option>
                            @endforeach
                        </select>

                        <svg
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25-4.5a.75.75 0 01.02-1.06l4.25-4.5a.75.75 0 111.08 1.04L10 8.832 6.31 4.894a.75.75 0 01-1.08 1.04l4.25 4.5a.75.75 0 01-.02 1.06z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>

                    {{-- Filter Divisi --}}
                    <div class="relative">
                        <select
                            id="filter-divisi"
                            onchange="loadTaskStats()"
                            class="h-10 min-w-[170px] appearance-none rounded-xl border border-zinc-200 bg-white pl-4 pr-10 text-sm text-zinc-700 outline-none transition focus:border-zinc-950 focus:ring-0"
                        >
                            <option value="all">Semua Divisi</option>

                            @foreach($divisiList as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>

                        <svg
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25-4.5a.75.75 0 01-.02-1.06z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>

                    {{-- Date Range --}}
                    <div class="flex h-10 overflow-hidden rounded-xl border border-zinc-200 bg-white">
                        <input
                            type="date"
                            id="dateStart"
                            onchange="loadTaskStats()"
                            class="w-[145px] border-0 bg-transparent px-3 text-sm text-zinc-700 outline-none focus:ring-0"
                        >

                        <div class="flex items-center border-x border-zinc-200 px-3 text-xs font-medium text-zinc-400">
                            s/d
                        </div>

                        <input
                            type="date"
                            id="dateEnd"
                            onchange="loadTaskStats()"
                            class="w-[145px] border-0 bg-transparent px-3 text-sm text-zinc-700 outline-none focus:ring-0"
                        >
                    </div>

                    {{-- Reset --}}
                    <button
                        type="button"
                        onclick="resetFilters()"
                        title="Reset Filter"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 20v-5h-5"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 15a7 7 0 0011.9 2.1L20 14"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 9A7 7 0 006.6 6.9L4 10"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- =========================
                STAT CARDS
            ========================== --}}
            @php
                $statsData = [
                    [
                        'id' => 'val-total-task',
                        'label' => 'Total Task',
                        'sub' => 'Hari Ini',
                        'color' => 'zinc',
                        'status' => 'all'
                    ],
                    [
                        'id' => 'val-selesai',
                        'label' => 'Selesai',
                        'sub' => 'Done',
                        'color' => 'emerald',
                        'status' => 4
                    ],
                    [
                        'id' => 'val-progress',
                        'label' => 'Running',
                        'sub' => 'On Track',
                        'color' => 'amber',
                        'status' => 1
                    ],
                    [
                        'id' => 'val-belum-mulai',
                        'label' => 'Not Start',
                        'sub' => 'Pending',
                        'color' => 'slate',
                        'status' => 0
                    ],
                    [
                        'id' => 'val-overdue',
                        'label' => 'Overdue',
                        'sub' => 'Urgent',
                        'color' => 'rose',
                        'status' => 7
                    ],
                ];
            @endphp

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach($statsData as $item)
                    <div
                        class="stat-card group cursor-pointer rounded-2xl border-2 border-transparent bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(0,0,0,0.07)]"
                        onclick="filterByStatus('{{ $item['status'] }}', this)"
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <div class="
                                flex h-11 w-11 items-center justify-center rounded-xl
                                @if($item['color'] === 'emerald') bg-emerald-50 text-emerald-600
                                @elseif($item['color'] === 'amber') bg-amber-50 text-amber-600
                                @elseif($item['color'] === 'rose') bg-rose-50 text-rose-600
                                @elseif($item['color'] === 'slate') bg-slate-100 text-slate-600
                                @else bg-zinc-100 text-zinc-700
                                @endif
                            ">
                                @if($item['status'] === 'all')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h.01M3 12h.01M3 18h.01"/>
                                    </svg>
                                @elseif($item['status'] === 4)
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"/>
                                    </svg>
                                @elseif($item['status'] === 1)
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M4 19h16"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 4-4 3 2 5-6"/>
                                    </svg>
                                @elseif($item['status'] === 0)
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.3 4.2 2.8 17a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.2a2 2 0 00-3.4 0Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4M12 16h.01"/>
                                    </svg>
                                @endif
                            </div>

                            <span class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                {{ $item['sub'] }}
                            </span>
                        </div>

                        <div class="stat-title text-sm font-medium text-zinc-500">
                            {{ $item['label'] }}
                        </div>

                        <div
                            id="{{ $item['id'] }}"
                            class="
                                stat-number mt-1 text-4xl font-semibold tracking-tight
                                @if($item['color'] === 'emerald') text-emerald-600
                                @elseif($item['color'] === 'amber') text-amber-600
                                @elseif($item['color'] === 'rose') text-rose-600
                                @elseif($item['color'] === 'slate') text-slate-600
                                @else text-zinc-950
                                @endif
                            "
                        >
                            0
                        </div>

                        <div class="stat-desc mt-2 text-xs text-zinc-400">
                            {{ $item['label'] }} task
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- =========================
                MAIN CONTENT
            ========================== --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">

                {{-- LEFT --}}
                <div class="space-y-6 xl:col-span-8">

                    {{-- Progress --}}
                    <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                    Overview
                                </div>
                                <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                    Progress
                                </h2>
                            </div>

                            <div
                                id="progress-text"
                                class="text-lg font-semibold tracking-tight text-zinc-950"
                            >
                                0%
                            </div>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-zinc-100">
                            <div
                                id="progress-bar-fill"
                                class="h-full rounded-full bg-zinc-950 transition-all duration-500"
                                style="width: 0%"
                            ></div>
                        </div>

                        <div
                            id="progress-desc"
                            class="mt-3 text-xs text-zinc-500"
                        >
                            0 dari 0 task selesai
                        </div>
                    </div>

                    {{-- Task Table --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                        <div class="border-b border-zinc-100 px-6 py-5">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                Task Monitor
                            </div>

                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                Task Hari Ini
                            </h2>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] border-collapse text-left">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/70">
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            Task
                                        </th>
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            PIC
                                        </th>
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            Client
                                        </th>
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            Tanggal
                                        </th>
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            Jam Mulai
                                        </th>
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            Prioritas
                                        </th>
                                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-400">
                                            Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody
                                    id="task-tbody"
                                    class="divide-y divide-zinc-100"
                                ></tbody>
                            </table>
                        </div>

                        <div
                            id="pagination-controls"
                            class="flex justify-center border-t border-zinc-100 px-5 py-4"
                        ></div>
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="space-y-6 xl:col-span-4">

                    {{-- Active Tasks --}}
                    <div
                        id="card-active-task"
                        class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.03)]"
                    >
                        <div class="border-b border-zinc-100 px-5 py-5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                        Live
                                    </div>

                                    <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                        Sedang Dikerjakan
                                    </h2>
                                </div>

                                <div
                                    id="active-task-stats"
                                    class="text-xs font-medium text-zinc-400"
                                >
                                    Total: 0 Task Aktif
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="mb-5 grid grid-cols-2 gap-3">

                                {{-- Divisi --}}
                                <div>
                                    @if(Auth::user()->isAdmin !== true)
                                        <label class="flex h-10 cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3">
                                            <input
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950"
                                                id="divisi-filter"
                                                value="{{ $divisiId }}"
                                                onchange="updateUserDropdown()"
                                            >

                                            <span class="truncate text-xs font-medium text-zinc-600">
                                                Divisi Saya
                                            </span>
                                        </label>
                                    @else
                                        <div class="relative">
                                            <select
                                                id="divisi-filter"
                                                class="h-10 w-full appearance-none rounded-xl border border-zinc-200 bg-white px-3 pr-8 text-xs text-zinc-600 outline-none focus:border-zinc-950 focus:ring-0"
                                                onchange="updateUserDropdown()"
                                            >
                                                <option value="all">Semua Divisi</option>

                                                @foreach($divisiList as $d)
                                                    <option value="{{ $d->id }}">
                                                        {{ $d->name }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <svg
                                                class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25-4.5a.75.75 0 01-1.08 1.04l4.25 4.5a.75.75 0 01.02-1.06z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- PIC --}}
                                <div class="relative">
                                    <select
                                        id="filter-active-task-pic"
                                        class="h-10 w-full appearance-none rounded-xl border border-zinc-200 bg-white px-3 pr-8 text-xs text-zinc-600 outline-none focus:border-zinc-950 focus:ring-0"
                                        onchange="loadActiveTask()"
                                    >
                                        <option value="all">Semua User</option>

                                        @foreach($allUser as $u)
                                            <option value="{{ $u->id }}">
                                                {{ $u->email }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <svg
                                        class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25-4.5a.75.75 0 01-1.08 1.04l4.25-4.5a.75.75 0 01-1.08 1.04l4.25 4.5a.75.75 0 01-.02-1.06z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <input
                                type="hidden"
                                id="current-user-id"
                                value="{{ auth()->id() }}"
                            >

                            <input
                                type="hidden"
                                id="is-admin"
                                value="{{ Auth::user()->isAdmin == true ? '1' : '0' }}"
                            >

                            <div
                                id="active-task-list"
                                class="flex flex-col gap-2"
                            ></div>
                        </div>
                    </div>

                    {{-- Queue --}}
                    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                        <div class="border-b border-zinc-100 px-5 py-5">
                            <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                Queue
                            </div>

                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                Antrian Berikutnya
                            </h2>
                        </div>

                        <div
                            id="antrian-list"
                            class="p-5"
                        ></div>
                    </div>
                </div>
            </div>

            {{-- =========================
                BOTTOM WIDGETS
            ========================== --}}
            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

                {{-- Productivity --}}
                <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                Performance
                            </div>

                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                Produktivitas Hari Ini
                            </h2>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M4 19h16"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7 15 4-4 3 2 5-6"/>
                            </svg>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 divide-x divide-zinc-100">
                        <div class="pr-3 text-center">
                            <div class="text-[10px] font-medium uppercase tracking-wider text-zinc-400">
                                Jam Kerja
                            </div>

                            <div
                                id="prod-jam-kerja"
                                class="mt-2 text-lg font-semibold tracking-tight text-zinc-950"
                            >
                                0j 0m
                            </div>

                            <div
                                id="jamkerja"
                                class="mt-1 text-[10px] leading-4 text-zinc-400"
                            ></div>
                        </div>

                        <div class="px-3 text-center">
                            <div class="text-[10px] font-medium uppercase tracking-wider text-zinc-400">
                                Task / Jam
                            </div>

                            <div
                                id="prod-task-jam"
                                class="mt-2 text-lg font-semibold tracking-tight text-zinc-950"
                            >
                                0
                            </div>

                            <div class="mt-1 text-[10px] text-zinc-400">
                                Rata-rata
                            </div>
                        </div>

                        <div class="pl-3 text-center">
                            <div class="text-[10px] font-medium uppercase tracking-wider text-zinc-400">
                                Estimasi
                            </div>

                            <div
                                id="prod-estimasi"
                                class="mt-2 text-lg font-semibold tracking-tight text-emerald-600"
                            >
                                0j
                            </div>

                            <div class="mt-1 text-[10px] text-zinc-400">
                                Total estimasi
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Activity --}}
                <div class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <div class="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                Timeline
                            </div>

                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                Aktivitas Hari Ini
                            </h2>
                        </div>

                        <div class="relative">
                            <select
                                id="filter-user-activity"
                                class="h-9 w-[105px] appearance-none rounded-lg border border-zinc-200 bg-white px-3 pr-7 text-xs text-zinc-600 outline-none focus:border-zinc-950 focus:ring-0"
                                onchange="loadTaskStats()"
                            >
                                <option value="all">Semua</option>

                                @foreach($allUser as $u)
                                    <option value="{{ $u->id }}">
                                        {{ $u->email }}
                                    </option>
                                @endforeach
                            </select>

                            <svg
                                class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25-4.5a.75.75 0 01-1.08 1.04l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </div>

                    <div
                        id="activity-timeline"
                        class="min-h-[180px]"
                    >
                        <div class="text-xs text-zinc-400">
                            Memuat aktivitas...
                        </div>
                    </div>

                    <div
                        id="activity-pagination"
                        class="mt-5 flex items-center justify-center gap-3 border-t border-zinc-100 pt-4"
                    >
                        <button
                            id="prev-activity"
                            type="button"
                            class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:border-zinc-950 hover:text-zinc-950 disabled:cursor-not-allowed disabled:opacity-40"
                            onclick="changePageActivity(-1)"
                            disabled
                        >
                            Prev
                        </button>

                        <span
                            id="page-info"
                            class="text-xs font-semibold text-zinc-500"
                        >
                            Page 1
                        </span>

                        <button
                            id="next-activity"
                            type="button"
                            class="rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:border-zinc-950 hover:text-zinc-950 disabled:cursor-not-allowed disabled:opacity-40"
                            onclick="changePageActivity(1)"
                        >
                            Next
                        </button>
                    </div>
                </div>

                {{-- Focus Timer --}}
                <div
                    id="focus-timer-card"
                    class="rounded-2xl border border-zinc-200/80 bg-white p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)]"
                >
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                Deep Work
                            </div>

                            <h2 class="mt-1 text-lg font-semibold text-zinc-950">
                                Focus Timer
                            </h2>
                        </div>

                        <div class="relative">
                            <select
                                id="filter-user-timer"
                                class="h-9 w-[105px] appearance-none rounded-lg border border-zinc-200 bg-white px-3 pr-7 text-xs text-zinc-600 outline-none focus:border-zinc-950 focus:ring-0"
                                onchange="loadTaskStats()"
                            >
                                <option value="all">Semua</option>

                                @foreach($allUser as $u)
                                    <option value="{{ $u->id }}">
                                        {{ $u->email }}
                                    </option>
                                @endforeach
                            </select>

                            <svg
                                class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25-4.5a.75.75 0 01-.02-1.06z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-6">
                        <div>
                            <div
                                id="status-timer-text"
                                class="text-xs font-medium text-zinc-400"
                            >
                                Sedang Tidak Fokus
                            </div>

                            <div
                                id="timer-display"
                                class="my-1 text-4xl font-semibold tracking-tight text-zinc-950"
                            >
                                00:00
                            </div>

                            <div
                                id="timer-target"
                                class="text-xs text-zinc-400"
                            >
                                Target 0 menit
                            </div>
                        </div>

                        <div class="shrink-0 text-zinc-950">
                            <svg
                                width="76"
                                height="76"
                                viewBox="0 0 36 36"
                                class="-rotate-90"
                            >
                                <circle
                                    cx="18"
                                    cy="18"
                                    r="15.915"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-opacity="0.08"
                                    stroke-width="3"
                                />

                                <circle
                                    id="progress-circle"
                                    cx="18"
                                    cy="18"
                                    r="15.915"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-dasharray="0, 100"
                                    stroke-dashoffset="0"
                                    class="transition-all duration-500"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<script>
    window.onload = function () {
        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');

        const today = `${year}-${month}-${day}`;

        const dateStart = document.getElementById('dateStart');
        const dateEnd = document.getElementById('dateEnd');

        if (dateStart && !dateStart.value) {
            dateStart.value = today;
        }

        if (dateEnd && !dateEnd.value) {
            dateEnd.value = today;
        }

        if (typeof loadTaskStats === 'function') {
            loadTaskStats();
        }
    };


    /* ==========================================
       GLOBAL STATE
    ========================================== */

    let currentPage = 1;
    const rowsPerPage = 10;

    let currentData = [];
    let activityData = [];
    let currentActivityPage = 1;
    let currentFilter = 'all';


    /* ==========================================
       STATUS BADGE
    ========================================== */

    function getStatusBadge(status) {
        const map = {
            0: {
                label: 'Not Start',
                class: 'bg-zinc-100 text-zinc-600'
            },
            1: {
                label: 'Running',
                class: 'bg-amber-50 text-amber-700'
            },
            3: {
                label: 'Wait Approval',
                class: 'bg-sky-50 text-sky-700'
            },
            4: {
                label: 'Done',
                class: 'bg-emerald-50 text-emerald-700'
            },
            7: {
                label: 'Overdue',
                class: 'bg-rose-50 text-rose-700'
            },
            8: {
                label: 'Expired',
                class: 'bg-orange-50 text-orange-700'
            },
            9: {
                label: 'Expired',
                class: 'bg-orange-50 text-orange-700'
            }
        };

        const s = map[status] || {
            label: 'Unknown',
            class: 'bg-zinc-900 text-white'
        };

        return `
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold whitespace-nowrap ${s.class}">
                ${s.label}
            </span>
        `;
    }


    /* ==========================================
       PRIORITY BADGE
    ========================================== */

    function getPriorityBadge(priority) {
        const map = {
            1: {
                label: 'Rendah',
                class: 'bg-zinc-100 text-zinc-600'
            },
            2: {
                label: 'Sedang',
                class: 'bg-amber-50 text-amber-700'
            },
            3: {
                label: 'Tinggi',
                class: 'bg-rose-50 text-rose-700'
            }
        };

        const p = map[priority] || {
            label: '-',
            class: 'bg-zinc-100 text-zinc-500'
        };

        return `
            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold whitespace-nowrap ${p.class}">
                ${p.label}
            </span>
        `;
    }


    /* ==========================================
       UPDATE ACTIVE TASK USER DROPDOWN
    ========================================== */

    async function updateUserDropdown() {
        const filterEl = document.getElementById('divisi-filter');
        const userSelect = document.getElementById('filter-active-task-pic');

        if (!filterEl || !userSelect) return;

        let divisiId = 'all';

        if (filterEl.type === 'checkbox') {
            divisiId = filterEl.checked
                ? filterEl.value
                : 'all';
        } else {
            divisiId = filterEl.value;
        }

        try {
            const response = await fetch(
                `/api/get-user-divisions?divisi_id=${encodeURIComponent(divisiId)}`
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil user divisi.');
            }

            const users = await response.json();

            userSelect.innerHTML = `
                <option value="all">Semua User</option>
            `;

            users.forEach(u => {
                const option = document.createElement('option');

                option.value = u.id;
                option.textContent = u.email || u.name || 'Unknown';

                userSelect.appendChild(option);
            });

            loadActiveTask();

        } catch (error) {
            console.error('Error update user dropdown:', error);
        }
    }


    /* ==========================================
       LOAD TASK STATS
    ========================================== */

    async function loadTaskStats() {
        const picEl = document.getElementById('filter-pic');
        const divisiEl = document.getElementById('filter-divisi');
        const clientEl = document.getElementById('filter-client');
        const dateStartEl = document.getElementById('dateStart');
        const dateEndEl = document.getElementById('dateEnd');
        const activityEl = document.getElementById('filter-user-activity');
        const timerEl = document.getElementById('filter-user-timer');

        if (!picEl || !divisiEl || !clientEl || !dateStartEl || !dateEndEl) {
            return;
        }

        let pic = picEl.value;
        const selectDivisi = divisiEl.value;
        const client = clientEl.value;
        const dateStart = dateStartEl.value;
        const dateEnd = dateEndEl.value;

        const filterUserActivity = activityEl
            ? activityEl.value
            : 'all';

        const timerPic = timerEl
            ? timerEl.value
            : 'all';

        /*
         * Jika memilih client tertentu,
         * PIC diabaikan sesuai logic lama.
         */
        if (client !== 'all') {
            pic = 'all';
        }

        try {
            const params = new URLSearchParams({
                pic: pic,
                client: client,
                start: dateStart,
                end: dateEnd,
                activity_pic: filterUserActivity,
                timer_pic: timerPic,
                divisi_id: selectDivisi
            });

            const res = await fetch(`/task/stats?${params.toString()}`);

            if (!res.ok) {
                throw new Error('Gagal mengambil statistik task.');
            }

            const data = await res.json();

            const total = Number(data.totalHariIni || 0);
            const selesai = Number(data.stats?.done || 0);

            const percentage = total > 0
                ? Math.round((selesai / total) * 100)
                : 0;

            /* Progress */
            const progressText = document.getElementById('progress-text');
            const progressBar = document.getElementById('progress-bar-fill');
            const progressDesc = document.getElementById('progress-desc');

            if (progressText) {
                progressText.innerText = `${percentage}%`;
            }

            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
            }

            if (progressDesc) {
                progressDesc.innerText =
                    `${selesai} dari ${total} task selesai`;
            }

            /* Statistik */
            document.getElementById('val-total-task').innerText =
                data.totalHariIni || 0;

            document.getElementById('val-overdue').innerText =
                data.stats?.overdue || 0;

            document.getElementById('val-selesai').innerText =
                data.stats?.done || 0;

            document.getElementById('val-progress').innerText =
                data.stats?.running || 0;

            document.getElementById('val-belum-mulai').innerText =
                data.stats?.not_start || 0;


            /* Active task */
            loadActiveTask();


            /* ==========================================
               QUEUE
            ========================================== */

            const queueList = document.getElementById('antrian-list');

            if (queueList) {
                queueList.innerHTML = '';

                if (data.queue && data.queue.length > 0) {
                    data.queue.forEach(q => {
                        const picName = q.email_pic || 'Unassigned';

                        const jamMulai = q.jam_mulai
                            ? q.jam_mulai.substring(0, 5)
                            : '-';

                        queueList.innerHTML += `
                            <div class="group flex items-center gap-3 border-b border-zinc-100 py-3 last:border-0">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="8"/>
                                        <path stroke-linecap="round" d="M12 8v4l2.5 2"/>
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-sm font-semibold text-zinc-800">
                                        ${q.nama_task}
                                    </div>

                                    <div class="mt-0.5 truncate text-[11px] text-zinc-400">
                                        ${q.nama_client}
                                    </div>

                                    <div class="mt-1 truncate text-[10px] text-zinc-500">
                                        ${picName}
                                    </div>
                                </div>

                                <div class="shrink-0 rounded-lg bg-zinc-50 px-2.5 py-1.5 text-[10px] font-semibold text-zinc-600">
                                    ${jamMulai}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    queueList.innerHTML = `
                        <div class="py-6 text-center text-xs text-zinc-400">
                            Tidak ada antrian.
                        </div>
                    `;
                }
            }


            /* ==========================================
               PRODUCTIVITY
            ========================================== */

            document.getElementById('prod-jam-kerja').innerText =
                data.productivity?.jam_kerja || '0j 0m';

            document.getElementById('prod-task-jam').innerText =
                `${data.productivity?.task_per_jam || 0}/ Jam`;


            const divisi = document.getElementById('divisi').value;

            let labelJam = '';

            if (divisi == 5) {
                document.getElementById('prod-estimasi').innerText = '8 Jam';
                labelJam = '8 Jam Kerja';
            } else {
                const hariIni = new Date();

                const namaHari = hariIni.toLocaleDateString(
                    'id-ID',
                    {
                        weekday: 'long'
                    }
                );

                if (namaHari === 'Sabtu') {
                    document.getElementById('prod-estimasi').innerText = '5 Jam';
                    labelJam = '5 Jam Kerja';
                } else {
                    document.getElementById('prod-estimasi').innerText = '7 Jam';
                    labelJam = '7 Jam Kerja';
                }
            }


            const jamKerjaEl = document.getElementById('jamkerja');

            if (jamKerjaEl) {
                if (pic === 'all') {
                    jamKerjaEl.innerHTML =
                        `Rata-rata seluruh bawahan<br>(${labelJam})`;
                } else {
                    jamKerjaEl.innerText =
                        `Dari ${labelJam}`;
                }
            }


            /* ==========================================
               ACTIVITY
            ========================================== */

            activityData = data.activities || [];
            currentActivityPage = 1;

            renderActivityPage();


            /* ==========================================
               FOCUS TIMER
            ========================================== */

            const statusText =
                document.getElementById('status-timer-text');

            const timerDisplay =
                document.getElementById('timer-display');

            const timerTarget =
                document.getElementById('timer-target');

            const progressPath =
                document.getElementById('progress-circle');


            if (window.timerInterval) {
                clearInterval(window.timerInterval);
                window.timerInterval = null;
            }


            if (
                data.focus_timer &&
                data.focus_timer.is_active &&
                data.active_task
            ) {
                statusText.innerText = 'Sedang Fokus';

                const targetMenit =
                    Number(data.focus_timer.target_menit || 0);

                timerTarget.innerText =
                    `Target ${targetMenit} menit`;


                function updateDisplay() {
                    const jamMulai =
                        data.active_task.jam_mulai;

                    const menit =
                        Number(data.active_task.menit || targetMenit || 0);

                    let startTime;

                    try {
                        const p = String(jamMulai || '').split(/[^\d]/);

                        if (p.length >= 5) {
                            startTime = new Date(
                                parseInt(p[0]),
                                parseInt(p[1]) - 1,
                                parseInt(p[2]),
                                parseInt(p[3]),
                                parseInt(p[4]),
                                parseInt(p[5]) || 0
                            );
                        } else {
                            startTime = new Date();

                            startTime.setHours(
                                parseInt(p[0]) || 0,
                                parseInt(p[1]) || 0,
                                parseInt(p[2]) || 0,
                                0
                            );
                        }

                    } catch (e) {
                        timerDisplay.innerText = 'Err Parse';
                        return;
                    }


                    const now = new Date();

                    const diffMs =
                        now.getTime() -
                        startTime.getTime();


                    if (isNaN(diffMs)) {
                        timerDisplay.innerText =
                            'Waktu Invalid';

                        return;
                    }


                    const detikTerpakai =
                        Math.max(0, Math.floor(diffMs / 1000));

                    const m =
                        Math.floor(detikTerpakai / 60);

                    const s =
                        detikTerpakai % 60;


                    timerDisplay.innerText =
                        `${m}:${s.toString().padStart(2, '0')}`;


                    /* Progress circle */
                    if (progressPath && menit > 0) {
                        const totalDetikLimit =
                            menit * 60;

                        const persen =
                            Math.min(
                                (detikTerpakai / totalDetikLimit) * 100,
                                100
                            );

                        progressPath.setAttribute(
                            'stroke-dasharray',
                            `${persen}, 100`
                        );
                    }
                }


                updateDisplay();

                window.timerInterval =
                    setInterval(updateDisplay, 1000);

            } else {
                statusText.innerText =
                    'Sedang Tidak Fokus';

                timerDisplay.innerText =
                    '00:00';

                timerTarget.innerText =
                    'Target 0 menit';

                if (progressPath) {
                    progressPath.setAttribute(
                        'stroke-dasharray',
                        '0, 100'
                    );
                }
            }


            /* ==========================================
               TABLE
            ========================================== */

            currentData = data.tasks || [];
            currentPage = 1;

            renderTable();

        } catch (error) {
            console.error('Error loadTaskStats:', error);
        }
    }


    /* ==========================================
       ACTIVITY PAGINATION
    ========================================== */

    function renderActivityPage() {
        const timeline =
            document.getElementById('activity-timeline');

        if (!timeline) return;

        timeline.innerHTML = '';

        const start =
            (currentActivityPage - 1) * rowsPerPage;

        const end =
            start + rowsPerPage;

        const paginatedItems =
            activityData.slice(start, end);


        if (paginatedItems.length > 0) {

            paginatedItems.forEach(act => {

                const time =
                    act.time
                        ? act.time.substring(0, 5)
                        : '--:--';

                timeline.innerHTML += `
                    <div class="flex items-start gap-3 border-b border-zinc-100 py-3 last:border-0">
                        <span class="w-11 shrink-0 pt-0.5 text-[10px] font-medium text-zinc-400">
                            ${time}
                        </span>

                        <div class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-950"></div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-semibold text-zinc-700">
                                ${act.desc}
                            </div>

                            <div class="mt-0.5 truncate text-[10px] text-zinc-400">
                                ${act.email || ''}
                            </div>
                        </div>
                    </div>
                `;
            });

        } else {

            timeline.innerHTML = `
                <div class="py-8 text-center text-xs italic text-zinc-400">
                    Tidak ada aktivitas.
                </div>
            `;
        }


        const pageInfo =
            document.getElementById('page-info');

        const prev =
            document.getElementById('prev-activity');

        const next =
            document.getElementById('next-activity');


        if (pageInfo) {
            pageInfo.innerText =
                `Page ${currentActivityPage}`;
        }

        if (prev) {
            prev.disabled =
                currentActivityPage === 1;
        }

        if (next) {
            next.disabled =
                end >= activityData.length;
        }
    }


    /* ==========================================
       LOAD ACTIVE TASK
    ========================================== */

    async function loadActiveTask() {
        const isAdmin =
            document.getElementById('is-admin')?.value === '1';

        const currentUserId =
            document.getElementById('current-user-id')?.value;

        const userSelect =
            document.getElementById('filter-active-task-pic');

        const divisiFilter =
            document.getElementById('divisi-filter');

        const activeContent =
            document.getElementById('active-task-list');

        const activeStats =
            document.getElementById('active-task-stats');


        if (!userSelect || !divisiFilter || !activeContent) {
            return;
        }


        let userId =
            userSelect.value;

        let divisiId = 'all';


        /*
         * Non-admin:
         * jika tidak memilih "Divisi Saya"
         * dan user masih "all", tampilkan task user sendiri.
         */
        if (
            divisiFilter.type === 'checkbox' &&
            !divisiFilter.checked &&
            !isAdmin &&
            userId === 'all'
        ) {
            userId = currentUserId;
        }


        /*
         * Jika filter divisi aktif dan user tertentu dipilih,
         * reset user menjadi all.
         */
        if (
            divisiFilter.type === 'checkbox' &&
            divisiFilter.checked &&
            userId !== 'all'
        ) {
            userId = 'all';
            userSelect.value = 'all';
        }


        if (divisiFilter.type === 'checkbox') {
            divisiId =
                divisiFilter.checked
                    ? divisiFilter.value
                    : 'all';
        } else {
            divisiId =
                divisiFilter.value;
        }


        try {
            const params = new URLSearchParams({
                user_id: userId,
                divisi_id: divisiId
            });

            const response = await fetch(
                `/kpi/get-active-task?${params.toString()}`
            );

            if (!response.ok) {
                throw new Error('Gagal mengambil active task.');
            }

            const data =
                await response.json();


            console.log(data);


            if (activeStats) {
                activeStats.innerText =
                    `Total: ${data.count || 0} Task Aktif`;
            }


            activeContent.innerHTML = '';


            if (
                data.count > 0 &&
                data.tasks &&
                data.tasks.length > 0
            ) {

                data.tasks.forEach(task => {

                    const picName =
                        task.pic_name || 'No PIC';

                    const jamMulai =
                        task.jam_mulai || '-';


                    activeContent.innerHTML += `
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50/60 p-3 transition hover:bg-zinc-50">
                            <div class="flex items-start gap-3">

                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="4" y="5" width="16" height="14" rx="2"/>
                                        <path stroke-linecap="round" d="M8 9h8M8 13h5"/>
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">

                                    <div class="truncate text-xs font-semibold text-zinc-800">
                                        ${task.nama}
                                    </div>

                                    <div class="mt-0.5 truncate text-[10px] text-zinc-400">
                                        ${task.project}
                                    </div>

                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <div class="flex min-w-0 items-center gap-1 text-[10px] font-medium text-zinc-600">
                                            <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <circle cx="12" cy="8" r="3"/>
                                                <path stroke-linecap="round" d="M5 19a7 7 0 0114 0"/>
                                            </svg>

                                            <span class="truncate">
                                                ${picName}
                                            </span>
                                        </div>

                                        <div class="flex shrink-0 items-center gap-1 text-[10px] font-semibold text-zinc-600">
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="8"/>
                                                <path stroke-linecap="round" d="M12 8v4l2.5 2"/>
                                            </svg>

                                            ${jamMulai}
                                        </div>
                                    </div>

                                    <div class="mt-1 text-[10px] text-zinc-400">
                                        Mulai: ${task.mulai || '-'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

            } else {

                activeContent.innerHTML = `
                    <div class="rounded-xl border border-dashed border-zinc-200 py-8 text-center text-xs text-zinc-400">
                        Tidak ada task aktif.
                    </div>
                `;
            }

        } catch (error) {
            console.error('Error loadActiveTask:', error);

            activeContent.innerHTML = `
                <div class="rounded-xl border border-dashed border-rose-200 bg-rose-50/50 py-6 text-center text-xs text-rose-500">
                    Gagal memuat task aktif.
                </div>
            `;
        }
    }


    /* ==========================================
       FILTER BY STATUS
    ========================================== */

    function filterByStatus(status, element) {
        currentFilter = status;
        currentPage = 1;

        document
            .querySelectorAll('.stat-card')
            .forEach(card => {
                card.classList.remove(
                    'active',
                    'border-zinc-950',
                    'bg-zinc-50'
                );
            });


        if (status !== 'all' && element) {

            element.classList.add(
                'active',
                'border-zinc-950',
                'bg-zinc-50'
            );
        }


        renderTable();
    }


    /* ==========================================
       TABLE
    ========================================== */

    function renderTable() {
        const tbody =
            document.getElementById('task-tbody');

        if (!tbody) return;

        tbody.innerHTML = '';


        let filteredData =
            currentData;


        if (currentFilter !== 'all') {
            filteredData =
                currentData.filter(
                    t => t.status == currentFilter
                );
        }


        if (filteredData.length === 0) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                                    <path stroke-linecap="round" d="M8 9h8M8 13h5"/>
                                </svg>
                            </div>

                            <div class="text-xs text-zinc-400">
                                Tidak ada data task yang ditemukan.
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            renderPagination(0);
            return;
        }


        const start =
            (currentPage - 1) * rowsPerPage;

        const end =
            start + rowsPerPage;

        const paginatedItems =
            filteredData.slice(start, end);


        paginatedItems.forEach(t => {

            tbody.innerHTML += `
                <tr class="transition hover:bg-zinc-50/70">

                    <td class="px-5 py-4">
                        <div class="flex min-w-[220px] items-start gap-3">

                            <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-zinc-300"></div>

                            <div class="min-w-0">
                                <div class="truncate text-xs font-semibold text-zinc-800">
                                    ${t.nama_task}
                                </div>

                                <div class="mt-1 truncate text-[10px] text-zinc-400">
                                    ${t.id_kpi != null
                                        ? t.nama_kpi
                                        : 'Tidak Termasuk KPI'}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4">
                        <span class="text-xs text-zinc-600">
                            ${t.email_pic || '-'}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <span class="text-xs text-zinc-600">
                            ${t.nama_client || '-'}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <span class="whitespace-nowrap text-xs text-zinc-500">
                            ${t.tanggal_deadline || '-'}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        <span class="whitespace-nowrap text-xs font-medium text-zinc-600">
                            ${t.jam_mulai || '-'}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        ${getPriorityBadge(t.priority)}
                    </td>

                    <td class="px-5 py-4">
                        ${getStatusBadge(t.status)}
                    </td>

                </tr>
            `;
        });


        renderPagination(filteredData.length);
    }


    /* ==========================================
       TABLE PAGINATION
    ========================================== */

    function renderPagination(totalItems) {
        const nav =
            document.getElementById('pagination-controls');

        if (!nav) return;

        const pageCount =
            Math.ceil(totalItems / rowsPerPage);

        nav.innerHTML = '';


        if (pageCount <= 1) {
            return;
        }


        const createButton = (
            label,
            page,
            active = false,
            disabled = false
        ) => {

            return `
                <button
                    type="button"
                    class="mx-0.5 min-w-8 rounded-lg px-2.5 py-1.5 text-xs font-medium transition
                    ${
                        active
                            ? 'bg-zinc-950 text-white'
                            : 'border border-zinc-200 bg-white text-zinc-600 hover:border-zinc-950 hover:text-zinc-950'
                    }
                    ${
                        disabled
                            ? 'cursor-not-allowed opacity-40'
                            : ''
                    }"
                    ${disabled ? 'disabled' : ''}
                    onclick="changePage(${page})"
                >
                    ${label}
                </button>
            `;
        };


        nav.innerHTML += createButton(
            '«',
            currentPage - 1,
            false,
            currentPage === 1
        );


        for (let i = 1; i <= pageCount; i++) {

            nav.innerHTML += createButton(
                i,
                i,
                currentPage === i,
                false
            );
        }


        nav.innerHTML += createButton(
            '»',
            currentPage + 1,
            false,
            currentPage === pageCount
        );
    }


    function changePage(page) {
        currentPage = page;
        renderTable();
    }


    /* ==========================================
       ACTIVITY PAGE
    ========================================== */

    function changePageActivity(direction) {
        const maxPage =
            Math.max(
                1,
                Math.ceil(activityData.length / rowsPerPage)
            );

        currentActivityPage += direction;

        currentActivityPage =
            Math.max(
                1,
                Math.min(
                    currentActivityPage,
                    maxPage
                )
            );

        renderActivityPage();
    }


    /* ==========================================
       RESET FILTER
    ========================================== */

    function resetFilters() {

        const filterPic =
            document.getElementById('filter-pic');

        const filterClient =
            document.getElementById('filter-client');

        const filterDivisi =
            document.getElementById('filter-divisi');

        const filterActivity =
            document.getElementById('filter-user-activity');

        const filterTimer =
            document.getElementById('filter-user-timer');


        if (filterPic) {
            filterPic.value = 'all';
            filterPic.disabled = false;
        }

        if (filterClient) {
            filterClient.value = 'all';
        }

        if (filterDivisi) {
            filterDivisi.value = 'all';
        }

        if (filterActivity) {
            filterActivity.value = 'all';
        }

        if (filterTimer) {
            filterTimer.value = 'all';
        }


        const now = new Date();

        const today =
            `${now.getFullYear()}-${String(
                now.getMonth() + 1
            ).padStart(2, '0')}-${String(
                now.getDate()
            ).padStart(2, '0')}`;


        const dateStart =
            document.getElementById('dateStart');

        const dateEnd =
            document.getElementById('dateEnd');


        if (dateStart) {
            dateStart.value = today;
        }

        if (dateEnd) {
            dateEnd.value = today;
        }


        /* Reset status card */
        currentFilter = 'all';
        currentPage = 1;

        document
            .querySelectorAll('.stat-card')
            .forEach(card => {
                card.classList.remove(
                    'active',
                    'border-zinc-950',
                    'bg-zinc-50'
                );
            });


        /* Reset active-task filter */
        const activeDivisi =
            document.getElementById('divisi-filter');

        const activePic =
            document.getElementById('filter-active-task-pic');


        if (activeDivisi) {

            if (activeDivisi.type === 'checkbox') {
                activeDivisi.checked = false;
            } else {
                activeDivisi.value = 'all';
            }
        }


        if (activePic) {
            activePic.value = 'all';
        }


        if (typeof loadTaskStats === 'function') {
            loadTaskStats();
        }
    }


    /* ==========================================
       INITIAL LOAD
    ========================================== */

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            /*
             * window.onload juga memanggil loadTaskStats.
             * Tidak perlu memanggilnya dua kali.
             */
        }
    );
</script>
@endsection
