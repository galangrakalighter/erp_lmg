@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1500px]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="mb-7 flex flex-col gap-5 border-b border-zinc-200 pb-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-px w-8 bg-zinc-950"></span>
                    <span class="text-[10px] font-semibold uppercase tracking-[0.25em] text-zinc-500">
                        Financial Report
                    </span>
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    {{ $title }}
                </h1>

                <p class="mt-1.5 text-xs text-zinc-500">
                    Periode Laporan:
                    <span class="font-semibold text-zinc-800">
                        {{ \Carbon\Carbon::parse($periode.'-01')->isoFormat('MMMM YYYY') }}
                    </span>
                </p>
            </div>

            {{-- FILTER + EXPORT --}}
            <form
                action="{{ url()->current() }}"
                method="GET"
                class="flex flex-col gap-2 sm:flex-row sm:items-center"
            >
                <div>
                    <label class="mb-1 block text-[9px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        Periode
                    </label>

                    <input
                        type="month"
                        name="periode"
                        value="{{ $periode }}"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-800 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10 sm:w-44"
                    >
                </div>

                <div class="sm:pt-[17px]">
                    <a
                        id="financial-pdf-export"
                        href="{{ url('finance/report/cash-flow/export/'. $id .'?periode=' . $periode) }}"
                        data-arus-kas-url="{{ url('finance/report/cash-flow/export/'. $id .'?periode=' . $periode) }}"
                        data-laba-rugi-url="{{ url('finance/report/worksheet/export-laba-rugi/'. $id .'?periode=' . $periode) }}"
                        data-neraca-url="{{ url('finance/report/worksheet/export-neraca/'. $id .'?periode=' . $periode) }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800 sm:w-auto"
                        target="_blank"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14a2 2 0 0 0 2-2v-5M3 14v5a2 2 0 0 0 2 2"
                            />
                        </svg>

                        <span class="financial-export-label">
                            PDF Arus Kas
                        </span>
                    </a>
                </div>
            </form>
        </div>


        {{-- =========================================================
             TABS
        ========================================================== --}}
        <div
            class="mb-6 flex w-full gap-1 rounded-xl border border-zinc-200 bg-zinc-100 p-1 sm:w-fit"
            role="tablist"
            aria-label="Jenis laporan keuangan"
        >
            <button
                type="button"
                class="financial-tab active flex-1 rounded-lg bg-zinc-950 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-white shadow-sm transition sm:flex-none"
                data-financial-tab="arus-kas"
                role="tab"
                aria-selected="true"
            >
                Arus Kas
            </button>

            <button
                type="button"
                class="financial-tab flex-1 rounded-lg px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 transition hover:bg-white hover:text-zinc-900 sm:flex-none"
                data-financial-tab="laba-rugi"
                role="tab"
                aria-selected="false"
            >
                Laba Rugi
            </button>

            <button
                type="button"
                class="financial-tab flex-1 rounded-lg px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 transition hover:bg-white hover:text-zinc-900 sm:flex-none"
                data-financial-tab="neraca"
                role="tab"
                aria-selected="false"
            >
                Neraca
            </button>

            <button
                type="button"
                class="financial-tab flex-1 rounded-lg px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 transition hover:bg-white hover:text-zinc-900 sm:flex-none"
                data-financial-tab="modal"
                role="tab"
                aria-selected="false"
            >
                Modal
            </button>
        </div>


        {{-- =========================================================
             ARUS KAS
        ========================================================== --}}
        <div
            id="financial-arus-kas"
            class="financial-pane active"
        >

            {{-- SUMMARY CARDS --}}
            <div class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-3">

                {{-- SALDO AWAL --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <span class="block text-[9px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        Saldo Awal Kas & Bank
                    </span>

                    <span class="mt-2 block text-lg font-semibold tracking-tight text-zinc-900">
                        Rp {{ number_format($totalSaldoAwal, 2, ',', '.') }}
                    </span>
                </div>

                {{-- KENAIKAN --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                    <span class="block text-[9px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        Kenaikan / (Penurunan) Bersih
                    </span>

                    <span class="mt-2 block text-lg font-semibold tracking-tight {{ $kenaikanPenurunanBersih >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $kenaikanPenurunanBersih < 0 ? '-' : '' }}Rp
                        {{ number_format(abs($kenaikanPenurunanBersih), 2, ',', '.') }}
                    </span>
                </div>

                {{-- SALDO AKHIR --}}
                <div class="rounded-2xl border border-zinc-900 bg-zinc-950 p-5 shadow-[0_4px_20px_rgba(0,0,0,0.08)]">
                    <span class="block text-[9px] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        Saldo Akhir Kas & Bank
                    </span>

                    <span class="mt-2 block text-lg font-semibold tracking-tight text-white">
                        Rp {{ number_format($totalSaldoAkhir, 2, ',', '.') }}
                    </span>
                </div>

            </div>


            {{-- CASH FLOW TABLE --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_6px_25px_rgba(0,0,0,0.04)]">

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] border-collapse text-xs">

                        <thead>
                            <tr class="bg-zinc-950 text-white">
                                <th class="w-[15%] border border-zinc-800 px-4 py-3 text-left font-mono text-[10px] font-semibold uppercase tracking-wider">
                                    Kode Akun
                                </th>

                                <th class="w-[55%] border border-zinc-800 px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                    Deskripsi Aliran Arus Kas
                                </th>

                                <th class="w-[15%] border border-zinc-800 px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                    Kas Masuk (Dr)
                                </th>

                                <th class="w-[15%] border border-zinc-800 px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                    Kas Keluar (Cr)
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            {{-- =====================================================
                                 OPERASIONAL
                            ====================================================== --}}
                            <tr class="bg-zinc-100">
                                <td
                                    colspan="4"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-[11px] font-bold uppercase tracking-wide text-zinc-900"
                                >
                                    1. Arus Kas dari Aktivitas Operasional
                                </td>
                            </tr>

                            @forelse($operasional as $item)

                                <tr class="transition-colors hover:bg-zinc-50">
                                    <td class="border-b border-zinc-100 px-4 py-2 font-mono text-[10px] text-zinc-400">
                                        {{ $item['kode'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-zinc-700">
                                        {{ $item['nama_akun'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-right font-mono font-medium tabular-nums text-emerald-600">
                                        {{ $item['jumlah'] >= 0 ? number_format($item['jumlah'], 2, ',', '.') : '-' }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-right font-mono font-medium tabular-nums text-red-600">
                                        {{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']), 2, ',', '.') : '-' }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="4"
                                        class="border-b border-zinc-200 px-4 py-8 text-center text-xs italic text-zinc-400"
                                    >
                                        Tidak ada transaksi kas operasional
                                    </td>
                                </tr>

                            @endforelse

                            <tr class="bg-zinc-50">
                                <td
                                    colspan="2"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-right text-[9px] font-semibold uppercase tracking-[0.15em] text-zinc-500"
                                >
                                    Total Kas Netto Operasional
                                </td>

                                <td
                                    colspan="2"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-right font-mono text-xs font-bold tabular-nums text-zinc-900"
                                >
                                    {{ $totalOperasional < 0 ? '(' . number_format(abs($totalOperasional), 2, ',', '.') . ')' : number_format($totalOperasional, 2, ',', '.') }}
                                </td>
                            </tr>


                            {{-- =====================================================
                                 INVESTASI
                            ====================================================== --}}
                            <tr class="border-t border-zinc-300 bg-zinc-100">
                                <td
                                    colspan="4"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-[11px] font-bold uppercase tracking-wide text-zinc-900"
                                >
                                    2. Arus Kas dari Aktivitas Investasi
                                </td>
                            </tr>

                            @forelse($investasi as $item)

                                <tr class="transition-colors hover:bg-zinc-50">
                                    <td class="border-b border-zinc-100 px-4 py-2 font-mono text-[10px] text-zinc-400">
                                        {{ $item['kode'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-zinc-700">
                                        {{ $item['nama_akun'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-right font-mono font-medium tabular-nums text-emerald-600">
                                        {{ $item['jumlah'] >= 0 ? number_format($item['jumlah'], 2, ',', '.') : '-' }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-right font-mono font-medium tabular-nums text-red-600">
                                        {{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']), 2, ',', '.') : '-' }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="4"
                                        class="border-b border-zinc-200 px-4 py-8 text-center text-xs italic text-zinc-400"
                                    >
                                        Tidak ada transaksi kas investasi
                                    </td>
                                </tr>

                            @endforelse

                            <tr class="bg-zinc-50">
                                <td
                                    colspan="2"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-right text-[9px] font-semibold uppercase tracking-[0.15em] text-zinc-500"
                                >
                                    Total Kas Netto Investasi
                                </td>

                                <td
                                    colspan="2"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-right font-mono text-xs font-bold tabular-nums text-zinc-900"
                                >
                                    {{ $totalInvestasi < 0 ? '(' . number_format(abs($totalInvestasi), 2, ',', '.') . ')' : number_format($totalInvestasi, 2, ',', '.') }}
                                </td>
                            </tr>


                            {{-- =====================================================
                                 PENDANAAN
                            ====================================================== --}}
                            <tr class="border-t border-zinc-300 bg-zinc-100">
                                <td
                                    colspan="4"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-[11px] font-bold uppercase tracking-wide text-zinc-900"
                                >
                                    3. Arus Kas dari Aktivitas Pendanaan
                                </td>
                            </tr>

                            @forelse($pendanaan as $item)

                                <tr class="transition-colors hover:bg-zinc-50">
                                    <td class="border-b border-zinc-100 px-4 py-2 font-mono text-[10px] text-zinc-400">
                                        {{ $item['kode'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-zinc-700">
                                        {{ $item['nama_akun'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-right font-mono font-medium tabular-nums text-emerald-600">
                                        {{ $item['jumlah'] >= 0 ? number_format($item['jumlah'], 2, ',', '.') : '-' }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2 text-right font-mono font-medium tabular-nums text-red-600">
                                        {{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']), 2, ',', '.') : '-' }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="4"
                                        class="border-b border-zinc-200 px-4 py-8 text-center text-xs italic text-zinc-400"
                                    >
                                        Tidak ada transaksi kas pendanaan
                                    </td>
                                </tr>

                            @endforelse

                            <tr class="bg-zinc-50">
                                <td
                                    colspan="2"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-right text-[9px] font-semibold uppercase tracking-[0.15em] text-zinc-500"
                                >
                                    Total Kas Netto Pendanaan
                                </td>

                                <td
                                    colspan="2"
                                    class="border-b border-zinc-200 px-4 py-2.5 text-right font-mono text-xs font-bold tabular-nums text-zinc-900"
                                >
                                    {{ $totalPendanaan < 0 ? '(' . number_format(abs($totalPendanaan), 2, ',', '.') . ')' : number_format($totalPendanaan, 2, ',', '.') }}
                                </td>
                            </tr>


                            {{-- =====================================================
                                 REKONSILIASI
                            ====================================================== --}}
                            <tr class="bg-zinc-800 text-white">
                                <td
                                    colspan="2"
                                    class="px-4 py-3 text-right text-[9px] font-semibold uppercase tracking-[0.15em] text-zinc-300"
                                >
                                    Kenaikan / (Penurunan) Bersih Kas Periode Ini
                                </td>

                                <td
                                    colspan="2"
                                    class="px-4 py-3 text-right font-mono text-sm font-bold tabular-nums"
                                >
                                    {{ $kenaikanPenurunanBersih < 0 ? '(' . number_format(abs($kenaikanPenurunanBersih), 2, ',', '.') . ')' : number_format($kenaikanPenurunanBersih, 2, ',', '.') }}
                                </td>
                            </tr>

                            <tr class="bg-zinc-900 text-zinc-300">
                                <td
                                    colspan="2"
                                    class="px-4 py-2.5 text-right text-[9px] font-semibold uppercase tracking-[0.15em]"
                                >
                                    Saldo Awal Kas & Bank Bersih
                                </td>

                                <td
                                    colspan="2"
                                    class="px-4 py-2.5 text-right font-mono text-xs font-semibold tabular-nums"
                                >
                                    {{ number_format($totalSaldoAwal, 2, ',', '.') }}
                                </td>
                            </tr>

                            <tr class="border-t border-zinc-700 bg-black text-emerald-400">
                                <td
                                    colspan="2"
                                    class="px-4 py-3 text-right text-[9px] font-bold uppercase tracking-[0.15em]"
                                >
                                    Saldo Akhir Kas & Bank Riil
                                </td>

                                <td
                                    colspan="2"
                                    class="px-4 py-3 text-right font-mono text-sm font-bold tracking-wide tabular-nums"
                                >
                                    {{ number_format($kenaikanPenurunanBersih + $totalSaldoAwal, 2, ',', '.') }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        {{-- =========================================================
             LABA RUGI
        ========================================================== --}}
        <div
            id="financial-laba-rugi"
            class="financial-pane hidden"
        >

            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_6px_25px_rgba(0,0,0,0.04)]">

                <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <span class="h-5 w-1 rounded-full bg-zinc-950"></span>

                        <div>
                            <h2 class="text-sm font-semibold text-zinc-950">
                                Laporan Laba Rugi
                            </h2>

                            <p class="mt-0.5 text-[11px] text-zinc-500">
                                Pendapatan dan beban berdasarkan saldo disesuaikan pada neraca lajur.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] border-collapse text-xs">

                        <thead>
                            <tr class="bg-zinc-950 text-white">
                                <th class="w-[18%] border border-zinc-800 px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                    Kode Akun
                                </th>

                                <th class="border border-zinc-800 px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                    Nama Akun
                                </th>

                                <th class="w-[18%] border border-zinc-800 px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                    Debit
                                </th>

                                <th class="w-[18%] border border-zinc-800 px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                    Kredit
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($labaRugi as $row)

                                <tr class="transition-colors hover:bg-zinc-50">

                                    <td class="border-b border-zinc-100 px-4 py-2.5 font-mono text-[10px] text-zinc-400">
                                        {{ $row['kode'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2.5 text-zinc-700">
                                        {{ $row['nama_akun'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2.5 text-right font-mono tabular-nums text-zinc-700">
                                        {{ $row['lr_debit'] != 0 ? number_format($row['lr_debit'], 2, ',', '.') : '-' }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2.5 text-right font-mono tabular-nums text-zinc-700">
                                        {{ $row['lr_kredit'] != 0 ? number_format($row['lr_kredit'], 2, ',', '.') : '-' }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-xs italic text-zinc-400"
                                    >
                                        Tidak ada data laba rugi pada periode ini.
                                    </td>
                                </tr>

                            @endforelse

                            {{-- JUMLAH --}}
                            <tr class="bg-zinc-100 font-semibold text-zinc-900">

                                <td
                                    colspan="2"
                                    class="border-t border-zinc-300 px-4 py-3 text-right text-[10px] uppercase tracking-wider"
                                >
                                    Jumlah
                                </td>

                                <td class="border-t border-zinc-300 px-4 py-3 text-right font-mono tabular-nums">
                                    {{ number_format($worksheetTotals['lr_debit'], 2, ',', '.') }}
                                </td>

                                <td class="border-t border-zinc-300 px-4 py-3 text-right font-mono tabular-nums">
                                    {{ number_format($worksheetTotals['lr_kredit'], 2, ',', '.') }}
                                </td>

                            </tr>

                            {{-- HASIL --}}
                            <tr class="bg-zinc-950 text-white">

                                <td
                                    colspan="3"
                                    class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    {{ $labaRugiBersih >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}
                                </td>

                                <td class="px-4 py-3 text-right font-mono text-sm font-bold tabular-nums">
                                    {{ number_format(abs($labaRugiBersih), 2, ',', '.') }}
                                </td>

                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>


        {{-- =========================================================
             PERUBAHAN MODAL
        ========================================================== --}}
        <div id="financial-modal" class="financial-pane hidden">
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_6px_25px_rgba(0,0,0,0.04)]">
                <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4">
                    <div class="flex items-center gap-2"><span class="h-5 w-1 rounded-full bg-lime-400"></span><div><h2 class="text-sm font-semibold text-zinc-950">Laporan Perubahan Modal</h2><p class="mt-0.5 text-[11px] text-zinc-500">Posisi akun modal 301 setelah membawa laba atau rugi bersih periode sebelumnya.</p></div></div>
                </div>
                <div class="px-4 py-8 sm:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-zinc-950">{{ $namaPerusahaan }}</h3>
                        <p class="mt-1 text-xs font-semibold uppercase text-zinc-700">Laporan Perubahan Modal</p>
                        <p class="mt-1 text-xs uppercase text-zinc-500">{{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }} (dalam Rp)</p>
                    </div>
                    <div class="mx-auto mt-7 max-w-3xl overflow-hidden rounded-xl border border-zinc-300">
                        <div class="grid grid-cols-[1fr_auto] border-b border-zinc-200 px-4 py-3 text-sm"><span class="text-zinc-700">Modal Awal, 1 {{ \Carbon\Carbon::parse($periode . '-01')->translatedFormat('F Y') }}</span><span class="min-w-44 text-right font-mono font-semibold tabular-nums text-zinc-950">{{ number_format($modalAwal, 0, ',', '.') }}</span></div>
                        <div class="grid grid-cols-[1fr_auto] border-b border-zinc-300 px-4 py-3 text-sm"><span class="text-zinc-700">{{ $modalLabaRugi >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }} {{ \Carbon\Carbon::parse($periodeModal . '-01')->translatedFormat('F Y') }}</span><span class="min-w-44 text-right font-mono font-semibold tabular-nums {{ $modalLabaRugi < 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ number_format($modalLabaRugi, 0, ',', '.') }}</span></div>
                        <div class="grid grid-cols-[1fr_auto] bg-zinc-950 px-4 py-4 text-sm text-white"><span class="font-bold">Modal Akhir, {{ \Carbon\Carbon::parse($periode . '-01')->endOfMonth()->translatedFormat('d F Y') }}</span><span class="min-w-44 text-right font-mono font-bold tabular-nums">{{ number_format($modalAkhir, 0, ',', '.') }}</span></div>
                    </div>
                    <p class="mx-auto mt-3 max-w-3xl text-right text-[10px] text-zinc-400">Modal akhir = saldo akhir akun 301 {{ \Carbon\Carbon::parse($periodeModal . '-01')->translatedFormat('F Y') }} + laba/rugi bersih pada periode yang sama.</p>
                </div>
            </div>
        </div>

        {{-- =========================================================
             NERACA
        ========================================================== --}}
        <div
            id="financial-neraca"
            class="financial-pane hidden"
        >

            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_6px_25px_rgba(0,0,0,0.04)]">

                <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <span class="h-5 w-1 rounded-full bg-zinc-950"></span>

                        <div>
                            <h2 class="text-sm font-semibold text-zinc-950">
                                Laporan Neraca
                            </h2>

                            <p class="mt-0.5 text-[11px] text-zinc-500">
                                Aset, liabilitas, dan ekuitas berdasarkan saldo disesuaikan pada neraca lajur.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] border-collapse text-xs">

                        <thead>
                            <tr class="bg-zinc-950 text-white">

                                <th class="w-[18%] border border-zinc-800 px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                    Kode Akun
                                </th>

                                <th class="border border-zinc-800 px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                    Nama Akun
                                </th>

                                <th class="w-[18%] border border-zinc-800 px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                    Debit
                                </th>

                                <th class="w-[18%] border border-zinc-800 px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                    Kredit
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            @forelse($neraca as $row)

                                <tr class="transition-colors hover:bg-zinc-50">

                                    <td class="border-b border-zinc-100 px-4 py-2.5 font-mono text-[10px] text-zinc-400">
                                        {{ $row['kode'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2.5 text-zinc-700">
                                        {{ $row['nama_akun'] }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2.5 text-right font-mono tabular-nums text-zinc-700">
                                        {{ $row['neraca_debit'] != 0 ? number_format($row['neraca_debit'], 2, ',', '.') : '-' }}
                                    </td>

                                    <td class="border-b border-zinc-100 px-4 py-2.5 text-right font-mono tabular-nums text-zinc-700">
                                        {{ $row['neraca_kredit'] != 0 ? number_format($row['neraca_kredit'], 2, ',', '.') : '-' }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="4"
                                        class="px-4 py-10 text-center text-xs italic text-zinc-400"
                                    >
                                        Tidak ada data neraca pada periode ini.
                                    </td>
                                </tr>

                            @endforelse


                            {{-- LABA / RUGI --}}
                            <tr class="bg-zinc-950 text-white">

                                <td
                                    colspan="2"
                                    class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    {{ $labaRugiBersih >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}
                                </td>

                                <td class="px-4 py-3 text-right font-mono text-sm font-bold tabular-nums">
                                    {{ $labaRugiBersih >= 0 ? number_format($labaRugiBersih, 2, ',', '.') : '-' }}
                                </td>

                                <td class="px-4 py-3 text-right font-mono text-sm font-bold tabular-nums">
                                    {{ $labaRugiBersih < 0 ? number_format(abs($labaRugiBersih), 2, ',', '.') : '-' }}
                                </td>

                            </tr>


                            {{-- TOTAL --}}
                            <tr class="bg-zinc-100 font-bold text-zinc-900">

                                <td
                                    colspan="2"
                                    class="border-t-2 border-zinc-300 px-4 py-3 text-right text-[10px] uppercase tracking-wider"
                                >
                                    Total Neraca
                                </td>

                                <td class="border-t-2 border-zinc-300 px-4 py-3 text-right font-mono tabular-nums">
                                    {{ number_format($totalNeracaDebit, 2, ',', '.') }}
                                </td>

                                <td class="border-t-2 border-zinc-300 px-4 py-3 text-right font-mono tabular-nums">
                                    {{ number_format($totalNeracaKredit, 2, ',', '.') }}
                                </td>

                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>


{{-- =============================================================
     TAB SCRIPT
============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const buttons = document.querySelectorAll('[data-financial-tab]');
    const panes = document.querySelectorAll('.financial-pane');
    const exportButton = document.getElementById('financial-pdf-export');
    const exportLabel = exportButton.querySelector('.financial-export-label');

    const exportLabels = {
        'arus-kas': 'PDF Arus Kas',
        'laba-rugi': 'PDF Laba Rugi',
        'neraca': 'PDF Neraca'
    };

    const exportUrlKeys = {
        'arus-kas': 'arusKasUrl',
        'laba-rugi': 'labaRugiUrl',
        'neraca': 'neracaUrl'
    };

    buttons.forEach(function (button) {

        button.addEventListener('click', function () {

            const selectedTab = button.dataset.financialTab;

            // Reset semua button
            buttons.forEach(function (item) {

                item.classList.remove(
                    'active',
                    'bg-zinc-950',
                    'text-white',
                    'shadow-sm'
                );

                item.classList.add(
                    'text-zinc-500'
                );

                item.setAttribute(
                    'aria-selected',
                    'false'
                );
            });

            // Reset semua pane
            panes.forEach(function (pane) {
                pane.classList.remove('active');
                pane.classList.add('hidden');
            });

            // Active button
            button.classList.add(
                'active',
                'bg-zinc-950',
                'text-white',
                'shadow-sm'
            );

            button.classList.remove(
                'text-zinc-500'
            );

            button.setAttribute(
                'aria-selected',
                'true'
            );

            // Active pane
            const activePane = document.getElementById(
                'financial-' + selectedTab
            );

            if (activePane) {
                activePane.classList.remove('hidden');
                activePane.classList.add('active');
            }

            // Update export URL
            if (selectedTab === 'modal') {
                exportButton.classList.add('hidden');
            } else {
                exportButton.classList.remove('hidden');
                exportButton.href = exportButton.dataset[exportUrlKeys[selectedTab]];
                exportLabel.textContent = exportLabels[selectedTab];
            }
        });
    });

});
</script>

@endsection
