@extends('layout.app')

@section('content')

<div class="min-h-screen">
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
        @include('hc.partials.dashboard_tabs')

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">

            <div>
                <div class="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                    Performance Dashboard
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    Halo, {{ Auth::user()->name }} 👋
                </h1>

                <p class="mt-2 text-sm text-zinc-500">
                    Selamat bekerja hari ini.
                </p>
            </div>

            {{-- Filters --}}
            <div class="flex w-full flex-col gap-2 sm:flex-row sm:flex-wrap xl:w-auto xl:justify-end">

                {{-- Project --}}
                <select
                    id="filterProject"
                    onchange="loadStats()"
                    class="h-10 min-w-[170px] rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                >
                    <option value="">Semua Project</option>

                    @foreach($listProject as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->nama }}
                        </option>
                    @endforeach
                </select>

                {{-- User --}}
                <select
                    id="filterUser"
                    onchange="loadStats()"
                    class="h-10 min-w-[170px] rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                >
                    <option value="">Semua Bawahan</option>

                    @foreach($listBawahan as $bawahan)
                        <option value="{{ $bawahan->id }}">
                            {{ $bawahan->email }}
                        </option>
                    @endforeach
                </select>

                {{-- Division --}}
                <select
                    id="filterDivisi"
                    onchange="loadStats()"
                    class="h-10 min-w-[170px] rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                >
                    @if(Auth::user()->isAdmin == true)
                        <option value="">Semua Divisi</option>
                    @endif

                    @foreach($listDivisi as $divisi)
                        <option value="{{ $divisi->id }}">
                            {{ $divisi->name }}
                        </option>
                    @endforeach
                </select>

                {{-- Date Range --}}
                <div class="flex h-10 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                    <input
                        type="date"
                        id="dateStart"
                        onchange="loadStats()"
                        class="min-w-0 border-0 bg-transparent px-3 text-sm text-zinc-700 outline-none focus:ring-0"
                    >

                    <div class="flex items-center border-x border-zinc-200 px-2 text-xs text-zinc-400">
                        s/d
                    </div>

                    <input
                        type="date"
                        id="dateEnd"
                        onchange="loadStats()"
                        class="min-w-0 border-0 bg-transparent px-3 text-sm text-zinc-700 outline-none focus:ring-0"
                    >
                </div>

                {{-- Reset --}}
                <button
                    type="button"
                    onclick="resetFilters()"
                    title="Reset Filter"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 12a9 9 0 1 0 2.64-6.36"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 4v5h5"
                        />
                    </svg>

                    Reset
                </button>
            </div>
        </div>


        {{-- =========================================================
            KPI STAT CARDS
        ========================================================== --}}
        <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total --}}
            <div
                class="rounded-2xl border border-zinc-200 bg-white p-5"
            >
                <div class="mb-6 grid h-11 w-11 place-items-center rounded-xl bg-zinc-950 text-white">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <rect
                            x="5"
                            y="3"
                            width="14"
                            height="18"
                            rx="2"
                        />
                        <path
                            stroke-linecap="round"
                            d="M9 7h6M9 11h6M9 15h4"
                        />
                    </svg>
                </div>

                <div class="text-sm font-medium text-zinc-500">
                    Total KPI
                </div>

                <div
                    id="val-total"
                    class="mt-2 text-4xl font-semibold tracking-tight text-zinc-950"
                >
                    0
                </div>

                <div class="mt-2 text-xs text-zinc-400">
                    Semua KPI aktif
                </div>
            </div>


            {{-- Selesai --}}
            <div
                class="stat-card cursor-pointer rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-zinc-300"
                onclick="filterByStatus('selesai', this)"
            >
                <div class="mb-6 grid h-11 w-11 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8.5 12l2.2 2.2 4.8-5"
                        />
                    </svg>
                </div>

                <div class="text-sm font-medium text-zinc-500">
                    Selesai
                </div>

                <div
                    id="val-selesai"
                    class="mt-2 text-4xl font-semibold tracking-tight text-zinc-950"
                >
                    0
                </div>

                <div
                    id="desc-selesai"
                    class="mt-2 text-xs text-zinc-400"
                >
                    0% dari total KPI
                </div>
            </div>


            {{-- Progress --}}
            <div
                class="stat-card cursor-pointer rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-zinc-300"
                onclick="filterByStatus('progress', this)"
            >
                <div class="mb-6 grid h-11 w-11 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 19V5M4 19h16"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 16v-5M12 16V8M16 16V5"
                        />
                    </svg>
                </div>

                <div class="text-sm font-medium text-zinc-500">
                    Progress
                </div>

                <div
                    id="val-progress"
                    class="mt-2 text-4xl font-semibold tracking-tight text-zinc-950"
                >
                    0
                </div>

                <div
                    id="desc-progress"
                    class="mt-2 text-xs text-zinc-400"
                >
                    0% dari total KPI
                </div>
            </div>


            {{-- Overdue --}}
            <div
                class="stat-card cursor-pointer rounded-2xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-zinc-300"
                onclick="filterByStatus('overdue', this)"
            >
                <div class="mb-6 grid h-11 w-11 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3l9 17H3L12 3z"
                        />
                        <path
                            stroke-linecap="round"
                            d="M12 9v4"
                        />
                        <path
                            stroke-linecap="round"
                            d="M12 16h.01"
                        />
                    </svg>
                </div>

                <div class="text-sm font-medium text-zinc-500">
                    Overdue
                </div>

                <div
                    id="val-overdue"
                    class="mt-2 text-4xl font-semibold tracking-tight text-zinc-950"
                >
                    0
                </div>

                <div
                    id="desc-overdue"
                    class="mt-2 text-xs text-zinc-400"
                >
                    0% dari total KPI
                </div>
            </div>

        </div>


        {{-- =========================================================
            COMPLETION + KPI SUMMARY
        ========================================================== --}}
        <div class="mb-6 grid grid-cols-1 overflow-hidden rounded-2xl border border-zinc-200 bg-white lg:grid-cols-2">

            {{-- Completion --}}
            <div class="p-6 sm:p-8">

                <div class="mb-1 text-xs font-semibold uppercase tracking-[0.12em] text-zinc-400">
                    Performance
                </div>

                <h2 class="text-xl font-semibold tracking-tight text-zinc-950">
                    Completion Rate
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Target bulan ini
                </p>

                <div class="flex min-h-[280px] items-center justify-center">

                    <div
                        id="chart-donat"
                        class="relative grid h-56 w-56 place-items-center rounded-full"
                        style="
                            background:
                            radial-gradient(
                                closest-side,
                                white 70%,
                                transparent 71% 100%
                            ),
                            conic-gradient(
                                #18181b var(--percentage),
                                #e4e4e7 0
                            );
                            --percentage: 82%;
                        "
                    >
                        <div class="text-center">
                            <div
                                id="val-persen-chart"
                                class="text-4xl font-semibold tracking-tight text-zinc-950"
                            >
                                82%
                            </div>

                            <div class="mt-1 text-[10px] font-medium uppercase tracking-[0.12em] text-zinc-400">
                                Complete
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            {{-- Summary --}}
            <div class="border-t border-zinc-200 p-6 sm:p-8 lg:border-l lg:border-t-0">

                <div class="mb-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-zinc-400">
                        Overview
                    </div>

                    <h2 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950">
                        Ringkasan KPI
                    </h2>
                </div>

                <div class="space-y-3">

                    {{-- KPI Hari Ini --}}
                    <div class="flex items-center gap-4 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-zinc-950 text-white">
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="17"
                                    rx="2"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M16 2v4M8 2v4M3 10h18"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-zinc-900">
                                KPI Hari Ini
                            </div>

                            <div class="mt-0.5 text-xs text-zinc-500">
                                Deadline hari ini
                            </div>
                        </div>

                        <div
                            id="val-kpi-hari-ini"
                            class="text-right text-xl font-semibold text-zinc-950"
                        >
                            0
                        </div>
                    </div>


                    {{-- Deadline Besok --}}
                    <div class="flex items-center gap-4 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-zinc-100 text-zinc-950">
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3"
                                    y="4"
                                    width="18"
                                    height="17"
                                    rx="2"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M16 2v4M8 2v4M3 10h18"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 15l2 2 4-4"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-zinc-900">
                                Deadline Besok
                            </div>

                            <div class="mt-0.5 text-xs text-zinc-500">
                                Jangan sampai terlewat
                            </div>
                        </div>

                        <div
                            id="val-deadline-besok"
                            class="text-right text-xl font-semibold text-zinc-950"
                        >
                            0
                        </div>
                    </div>


                    {{-- Point Bulan Ini --}}
                    <div class="flex items-center gap-4 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-zinc-100 text-zinc-950">
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3z"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-zinc-900">
                                Point Bulan Ini
                            </div>

                            <div class="mt-0.5 text-xs text-zinc-500">
                                Total point
                            </div>
                        </div>

                        <div
                            id="val-point-bulan-ini"
                            class="text-right text-xl font-semibold text-zinc-950"
                        >
                            0
                        </div>
                    </div>

                </div>


                {{-- Deadline Today --}}
                <div class="mt-7 border-t border-zinc-200 pt-6">

                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900">
                                Deadline Hari Ini
                            </div>

                            <div class="mt-0.5 text-xs text-zinc-500">
                                KPI yang perlu diperhatikan
                            </div>
                        </div>

                        <svg
                            class="h-5 w-5 text-zinc-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />
                            <path
                                stroke-linecap="round"
                                d="M12 7v5l3 2"
                            />
                        </svg>
                    </div>

                    <div
                        id="list-deadline"
                        class="max-h-[250px] space-y-2 overflow-y-auto pr-1"
                    >
                        {{-- Data via JS --}}
                    </div>

                </div>

            </div>
        </div>


        {{-- =========================================================
            RECENT ACTIVITY
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">

            <div class="flex flex-col gap-1 border-b border-zinc-200 px-5 py-5 sm:px-6">
                <div class="text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-400">
                    Activity
                </div>

                <h2 class="text-lg font-semibold tracking-tight text-zinc-950">
                    Aktivitas Terbaru
                </h2>

                <p class="text-xs text-zinc-500">
                    Riwayat KPI terbaru berdasarkan filter yang dipilih.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50">
                            <th class="whitespace-nowrap px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Tanggal
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Client
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Nama KPI
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                PIC
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        id="list-aktivitas-body"
                        class="divide-y divide-zinc-100"
                    >
                        {{-- Data via JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="border-t border-zinc-200 px-5 py-4">
                <nav
                    id="pagination-controls"
                    class="flex justify-center"
                    aria-label="Pagination aktivitas"
                >
                    {{-- Tombol via JS --}}
                </nav>
            </div>

        </div>

    </div>
</div>


<script>
    let currentPage = 1;
    let data = null;
    const itemsPerPage = 5;

    let currentFilter = 'all';


    // ============================================================
    // FILTER STATUS
    // ============================================================

    function filterByStatus(status, card = null) {

        // Toggle:
        // klik status yang sama = kembali ke semua
        currentFilter =
            (currentFilter === status)
                ? 'all'
                : status;

        // Reset active state
        document.querySelectorAll('.stat-card').forEach(function (item) {
            item.classList.remove(
                'border-zinc-950',
                'bg-zinc-950',
                'text-white'
            );

            item.classList.add(
                'border-zinc-200',
                'bg-white'
            );

            const title = item.querySelector('.stat-title');
            const desc = item.querySelector('.stat-desc');
            const number = item.querySelector('.stat-number');

            if (title) {
                title.classList.remove('text-white');
                title.classList.add('text-zinc-500');
            }

            if (desc) {
                desc.classList.remove('text-zinc-400');
                desc.classList.add('text-zinc-500');
            }

            if (number) {
                number.classList.remove('text-white');
            }
        });

        // Active card
        if (currentFilter !== 'all' && card) {
            card.classList.remove(
                'border-zinc-200',
                'bg-white'
            );

            card.classList.add(
                'border-zinc-950',
                'bg-zinc-950'
            );

            const title = card.querySelector('.stat-title');
            const desc = card.querySelector('.stat-desc');
            const number = card.querySelector('.stat-number');

            if (title) {
                title.classList.remove('text-zinc-500');
                title.classList.add('text-white');
            }

            if (desc) {
                desc.classList.remove('text-zinc-400');
                desc.classList.add('text-zinc-400');
            }

            if (number) {
                number.classList.add('text-white');
            }
        }

        renderAktivitas(1);
    }


    // ============================================================
    // LOAD STATS
    // ============================================================

    async function loadStats() {

        const projectId =
            document.getElementById('filterProject').value;

        const userId =
            document.getElementById('filterUser')
                ? document.getElementById('filterUser').value
                : '';

        const divisiId =
            document.getElementById('filterDivisi')
                ? document.getElementById('filterDivisi').value
                : '';

        const dateStart =
            document.getElementById('dateStart').value;

        const dateEnd =
            document.getElementById('dateEnd').value;


        let url =
            `/kpi/stats?project_id=${projectId}`;

        if (userId) {
            url += `&user_id=${userId}`;
        }

        if (divisiId) {
            url += `&divisi_id=${divisiId}`;
        }

        if (dateStart) {
            url += `&start_date=${dateStart}`;
        }

        if (dateEnd) {
            url += `&end_date=${dateEnd}`;
        }


        try {

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error('Gagal mengambil statistik KPI.');
            }

            data = await response.json();

            console.log(data);


            // ====================================================
            // UPDATE KPI
            // ====================================================

            document.getElementById('val-total').innerText =
                data.total;

            document.getElementById('val-selesai').innerText =
                data.selesai;

            document.getElementById('val-progress').innerText =
                data.progress;

            document.getElementById('val-overdue').innerText =
                data.overdue;


            // ====================================================
            // UPDATE DESCRIPTION
            // ====================================================

            document.getElementById('desc-selesai').innerText =
                `${data.persenSelesai}% dari total KPI`;

            document.getElementById('desc-progress').innerText =
                `${data.persenProgress}% dari total KPI`;

            document.getElementById('desc-overdue').innerText =
                `${data.persenOverdue}% dari total KPI`;


            // ====================================================
            // DONUT
            // ====================================================

            const persen =
                data.persenSelesai || 0;

            const chart =
                document.getElementById('chart-donat');

            const text =
                document.getElementById('val-persen-chart');

            chart.style.setProperty(
                '--percentage',
                `${persen}%`
            );

            text.innerText =
                `${persen}%`;


            // ====================================================
            // MINI KPI
            // ====================================================

            document.getElementById(
                'val-kpi-hari-ini'
            ).innerHTML = `
                ${data.kpiHariIni}
                <span class="block text-[9px] font-normal uppercase tracking-wider text-zinc-400">
                    KPI
                </span>
            `;

            document.getElementById(
                'val-deadline-besok'
            ).innerHTML = `
                ${data.deadlineBesok}
                <span class="block text-[9px] font-normal uppercase tracking-wider text-zinc-400">
                    KPI
                </span>
            `;

            document.getElementById(
                'val-point-bulan-ini'
            ).innerHTML = `
                ${data.totalPoint}
                <span class="block text-[9px] font-normal uppercase tracking-wider text-zinc-400">
                    Point
                </span>
            `;


            // ====================================================
            // DEADLINE LIST
            // ====================================================

            const listDeadline =
                document.getElementById('list-deadline');

            listDeadline.innerHTML = '';

            if (
                data.deadlines &&
                data.deadlines.length > 0
            ) {

                data.deadlines.forEach(function (item) {

                    listDeadline.innerHTML += `
                        <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3">
                            <div class="shrink-0 rounded-lg bg-zinc-950 px-2 py-1.5 text-[10px] font-semibold text-white">
                                ${item.jam || '00:00'}
                            </div>

                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-zinc-900">
                                    ${item.nama}
                                </div>

                                <div class="mt-0.5 truncate text-xs text-zinc-500">
                                    ${item.project_name || 'Tanpa Project'}
                                </div>
                            </div>
                        </div>
                    `;
                });

            } else {

                listDeadline.innerHTML = `
                    <div class="rounded-xl border border-dashed border-zinc-200 px-4 py-8 text-center text-xs text-zinc-400">
                        Tidak ada deadline hari ini.
                    </div>
                `;
            }


            currentPage = 1;

            renderAktivitas(currentPage);

        } catch (error) {

            console.error(error);

            document.getElementById(
                'val-total'
            ).innerText = '-';

        }
    }


    // ============================================================
    // RENDER AKTIVITAS
    // ============================================================

    function renderAktivitas(page = 1) {

        const listBody =
            document.getElementById(
                'list-aktivitas-body'
            );

        const paginationControls =
            document.getElementById(
                'pagination-controls'
            );

        listBody.innerHTML = '';

        const today =
            new Date()
                .toISOString()
                .split('T')[0];


        if (!data || !data.aktivitas) {

            listBody.innerHTML = `
                <tr>
                    <td
                        colspan="5"
                        class="px-6 py-12 text-center text-sm text-zinc-400"
                    >
                        Tidak ada data aktivitas.
                    </td>
                </tr>
            `;

            paginationControls.innerHTML = '';

            return;
        }


        // ========================================================
        // FILTER DATA
        // ========================================================

        let filteredData =
            data.aktivitas.filter(function (item) {

                if (currentFilter === 'all') {
                    return true;
                }

                if (currentFilter === 'selesai') {
                    return item.status == 1;
                }

                if (currentFilter === 'progress') {
                    return (
                        item.status == 0 &&
                        item.tanggal_deadline >= today
                    );
                }

                if (currentFilter === 'overdue') {
                    return (
                        item.status == 0 &&
                        item.tanggal_deadline < today
                    );
                }

                return true;
            });


        // ========================================================
        // PAGINATION
        // ========================================================

        const start =
            (page - 1) * itemsPerPage;

        const paginatedItems =
            filteredData.slice(
                start,
                start + itemsPerPage
            );

        const totalPages =
            Math.ceil(
                filteredData.length / itemsPerPage
            );


        // ========================================================
        // TABLE ROW
        // ========================================================

        if (paginatedItems.length > 0) {

            paginatedItems.forEach(function (item) {

                const picEmails =
                    item.pics &&
                    item.pics.length > 0

                        ? item.pics
                            .map(function (pic) {
                                return `
                                    <span class="mr-1 mb-1 inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2 py-1 text-[10px] font-medium text-zinc-600">
                                        ${pic.email}
                                    </span>
                                `;
                            })
                            .join('')

                        : `
                            <span class="text-xs text-zinc-400">
                                Tanpa PIC
                            </span>
                        `;


                const btnSelesai =
                    item.status == 0

                        ? `
                            <button
                                class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-medium text-white transition hover:bg-zinc-800"
                                onclick="selesaikanKPI(${item.id})"
                            >
                                <svg
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12l4 4L19 6"
                                    />
                                </svg>

                                Selesai
                            </button>
                        `

                        : `
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                                <svg
                                    class="h-3 w-3"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12l4 4L19 6"
                                    />
                                </svg>

                                Selesai
                            </span>
                        `;


                listBody.innerHTML += `
                    <tr class="transition-colors hover:bg-zinc-50">

                        <td class="whitespace-nowrap px-5 py-4 align-middle text-xs text-zinc-500">
                            ${item.tanggal_mulai || '-'}
                        </td>

                        <td class="px-5 py-4 align-middle">
                            <div class="font-semibold text-zinc-900">
                                ${item.project_client.nama}
                            </div>
                        </td>

                        <td class="px-5 py-4 align-middle">
                            <div class="font-medium text-zinc-700">
                                ${item.nama_kpi}
                            </div>
                        </td>

                        <td class="px-5 py-4 align-middle">
                            <div class="flex flex-wrap max-w-[260px]">
                                ${picEmails}
                            </div>
                        </td>

                        <td class="px-5 py-4 align-middle">
                            ${btnSelesai}
                        </td>

                    </tr>
                `;
            });

        } else {

            listBody.innerHTML = `
                <tr>
                    <td
                        colspan="5"
                        class="px-6 py-14 text-center"
                    >
                        <div class="mx-auto max-w-sm">

                            <div class="mx-auto mb-3 grid h-11 w-11 place-items-center rounded-xl bg-zinc-100 text-zinc-400">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M9 5h6M9 9h6M9 13h4"
                                    />
                                    <rect
                                        x="5"
                                        y="3"
                                        width="14"
                                        height="18"
                                        rx="2"
                                    />
                                </svg>
                            </div>

                            <div class="text-sm font-medium text-zinc-700">
                                Tidak ada aktivitas ditemukan.
                            </div>

                            <div class="mt-1 text-xs text-zinc-400">
                                Coba ubah filter status atau periode.
                            </div>

                        </div>
                    </td>
                </tr>
            `;
        }


        // ========================================================
        // PAGINATION
        // ========================================================

        paginationControls.innerHTML = '';

        if (totalPages <= 1) {
            return;
        }

        const paginationWrapper =
            document.createElement('div');

        paginationWrapper.className =
            'flex items-center gap-1';


        // Previous
        const previousButton =
            document.createElement('button');

        previousButton.type = 'button';

        previousButton.className = `
            grid h-9 w-9 place-items-center rounded-lg
            text-zinc-500 transition
            hover:bg-zinc-100 hover:text-zinc-950
            disabled:pointer-events-none disabled:opacity-30
        `;

        previousButton.disabled =
            page <= 1;

        previousButton.innerHTML = `
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 18l-6-6 6-6"
                />
            </svg>
        `;

        previousButton.onclick = function () {
            changePage(page - 1);
        };

        paginationWrapper.appendChild(
            previousButton
        );


        // Number
        for (
            let i = 1;
            i <= totalPages;
            i++
        ) {

            const button =
                document.createElement('button');

            button.type = 'button';

            button.textContent = i;

            button.className = `
                grid h-9 min-w-9 place-items-center rounded-lg
                px-2 text-xs font-medium transition
                ${
                    i === page
                        ? 'bg-zinc-950 text-white'
                        : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950'
                }
            `;

            button.onclick = function () {
                changePage(i);
            };

            paginationWrapper.appendChild(button);
        }


        // Next
        const nextButton =
            document.createElement('button');

        nextButton.type = 'button';

        nextButton.className = `
            grid h-9 w-9 place-items-center rounded-lg
            text-zinc-500 transition
            hover:bg-zinc-100 hover:text-zinc-950
            disabled:pointer-events-none disabled:opacity-30
        `;

        nextButton.disabled =
            page >= totalPages;

        nextButton.innerHTML = `
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9 18l6-6-6-6"
                />
            </svg>
        `;

        nextButton.onclick = function () {
            changePage(page + 1);
        };

        paginationWrapper.appendChild(
            nextButton
        );


        paginationControls.appendChild(
            paginationWrapper
        );
    }


    // ============================================================
    // CHANGE PAGE
    // ============================================================

    function changePage(page) {
        currentPage = page;
        renderAktivitas(currentPage);
    }


    // ============================================================
    // INITIAL LOAD
    // ============================================================

    document.addEventListener(
        "DOMContentLoaded",
        function () {

            const now = new Date();

            const year =
                now.getFullYear();

            const month =
                (now.getMonth() + 1)
                    .toString()
                    .padStart(2, '0');

            const firstDay = '';

            const lastDay =
                new Date(
                    year,
                    now.getMonth() + 1,
                    0
                )
                .getDate()
                .toString()
                .padStart(2, '0');

            const lastDayFormatted = '';


            document.getElementById(
                'dateStart'
            ).value = firstDay;

            document.getElementById(
                'dateEnd'
            ).value = lastDayFormatted;


            loadStats();
        }
    );


    // ============================================================
    // SELESAIKAN KPI
    // ============================================================

    async function selesaikanKPI(id) {

        if (
            !confirm(
                'Yakin ingin menyelesaikan KPI ini?'
            )
        ) {
            return;
        }


        try {

            const response =
                await fetch(
                    `/kpi/${id}/selesai`,
                    {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    ?.getAttribute('content'),

                            'Accept':
                                'application/json'
                        }
                    }
                );


            const result =
                await response.json();


            if (result.success) {

                alert(
                    'KPI berhasil diselesaikan.'
                );

                loadStats();

            } else {

                alert(
                    result.message
                );
            }

        } catch (error) {

            console.error(error);

            alert(
                'Terjadi kesalahan.'
            );
        }
    }


    // ============================================================
    // RESET FILTER
    // ============================================================

    function resetFilters() {

        document.getElementById(
            'filterProject'
        ).value = '';


        if (
            document.getElementById(
                'filterUser'
            )
        ) {
            document.getElementById(
                'filterUser'
            ).value = '';
        }


        const filterDivisi =
            document.getElementById(
                'filterDivisi'
            );

        if (filterDivisi) {

            filterDivisi.value =
                filterDivisi.options[0]
                    ? filterDivisi.options[0].value
                    : '';
        }


        // Reset status
        currentFilter = 'all';

        document
            .querySelectorAll('.stat-card')
            .forEach(function (card) {

                card.classList.remove(
                    'border-zinc-950',
                    'bg-zinc-950'
                );

                card.classList.add(
                    'border-zinc-200',
                    'bg-white'
                );

                const title =
                    card.querySelector(
                        '.stat-title'
                    );

                const desc =
                    card.querySelector(
                        '.stat-desc'
                    );

                const number =
                    card.querySelector(
                        '.stat-number'
                    );

                if (title) {
                    title.classList.remove(
                        'text-white'
                    );

                    title.classList.add(
                        'text-zinc-500'
                    );
                }

                if (desc) {
                    desc.classList.remove(
                        'text-zinc-400'
                    );

                    desc.classList.add(
                        'text-zinc-500'
                    );
                }

                if (number) {
                    number.classList.remove(
                        'text-white'
                    );
                }
            });


        // Reset tanggal ke bulan berjalan
        const now =
            new Date();

        const year =
            now.getFullYear();

        const month =
            (now.getMonth() + 1)
                .toString()
                .padStart(2, '0');


        const firstDay = '';


        const lastDay =
            new Date(
                year,
                now.getMonth() + 1,
                0
            )
            .getDate()
            .toString()
            .padStart(2, '0');


        const lastDayFormatted = '';


        document.getElementById(
            'dateStart'
        ).value = firstDay;

        document.getElementById(
            'dateEnd'
        ).value = lastDayFormatted;


        loadStats();
    }
</script>

@endsection
