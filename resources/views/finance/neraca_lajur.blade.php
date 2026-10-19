@extends('layout.app')

@section('content')

    <div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1600px]">

            {{-- HEADER --}}
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-px w-8 bg-zinc-900"></span>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.25em] text-zinc-500">
                            Financial Report
                        </span>
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                        Neraca Lajur
                    </h1>

                    <p class="mt-1 text-sm text-zinc-500">
                        Periode:
                        <span class="font-semibold text-zinc-800">
                            {{ \Carbon\Carbon::parse($periode)->translatedFormat('F Y') }}
                        </span>
                    </p>
                </div>

                {{-- FILTER PERIODE --}}
                <form action="{{ url()->current() }}" method="GET">
                    <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        Pilih Periode
                    </label>

                    <div class="relative">
                        <input
                            type="month"
                            name="periode"
                            value="{{ $periode }}"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-800 shadow-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10 lg:w-52"
                        >
                    </div>
                </form>
            </div>

            {{-- TABLE CARD --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)]">

                {{-- TABLE HEADER --}}
                <div class="flex flex-col gap-2 border-b border-zinc-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950">
                            Worksheet Akuntansi
                        </h2>
                        <p class="mt-0.5 text-xs text-zinc-500">
                            Ringkasan neraca saldo, penyesuaian, laba rugi, dan neraca.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500">
                            {{ \Carbon\Carbon::parse($periode)->translatedFormat('F Y') }}
                        </span>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1450px] border-collapse text-[12px]">

                        <thead>
                            {{-- GROUP HEADER --}}
                            <tr class="bg-zinc-950 text-white">
                                <th
                                    rowspan="2"
                                    class="w-[80px] border border-zinc-700 px-2 py-3 text-center align-middle text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Kode Akun
                                </th>

                                <th
                                    rowspan="2"
                                    class="w-[250px] border border-zinc-700 px-3 py-3 text-left align-middle text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Nama Akun
                                </th>

                                <th
                                    colspan="2"
                                    class="border border-zinc-700 px-2 py-3 text-center text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Neraca Saldo
                                </th>

                                <th
                                    colspan="2"
                                    class="border border-zinc-700 px-2 py-3 text-center text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Ayat Jurnal Penyesuaian
                                </th>

                                <th
                                    colspan="2"
                                    class="border border-zinc-700 px-2 py-3 text-center text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Neraca Saldo Disesuaikan
                                </th>

                                <th
                                    colspan="2"
                                    class="border border-zinc-700 px-2 py-3 text-center text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Laba Rugi
                                </th>

                                <th
                                    colspan="2"
                                    class="border border-zinc-700 px-2 py-3 text-center text-[10px] font-semibold uppercase tracking-wider"
                                >
                                    Neraca
                                </th>
                            </tr>

                            {{-- COLUMN HEADER --}}
                            <tr class="bg-zinc-100 text-zinc-700">
                                @for ($i = 0; $i < 5; $i++)
                                    <th class="border border-zinc-300 px-2 py-2.5 text-center text-[10px] font-bold uppercase tracking-wider">
                                        Debet
                                    </th>
                                    <th class="border border-zinc-300 px-2 py-2.5 text-center text-[10px] font-bold uppercase tracking-wider">
                                        Kredit
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($worksheetData as $row)

                                <tr class="transition-colors hover:bg-zinc-50">

                                    {{-- KODE --}}
                                    <td class="border border-zinc-200 px-2 py-2 text-center font-mono text-[11px] font-semibold text-zinc-600">
                                        {{ $row['kode'] }}
                                    </td>

                                    {{-- NAMA AKUN --}}
                                    <td class="border border-zinc-200 px-3 py-2 font-medium text-zinc-800">
                                        {{ $row['nama_akun'] }}
                                    </td>

                                    {{-- NERACA SALDO --}}
                                    <td class="border border-zinc-200 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['ns_debit'] > 0 ? number_format($row['ns_debit'], 0, ',', '.') : '' }}
                                    </td>

                                    <td class="border border-zinc-200 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['ns_kredit'] > 0 ? number_format($row['ns_kredit'], 0, ',', '.') : '' }}
                                    </td>

                                    {{-- AYAT JURNAL PENYESUAIAN --}}
                                    <td class="border border-zinc-200 bg-zinc-50/40 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['aj_debit'] > 0 ? number_format($row['aj_debit'], 0, ',', '.') : '' }}
                                    </td>

                                    <td class="border border-zinc-200 bg-zinc-50/40 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['aj_kredit'] > 0 ? number_format($row['aj_kredit'], 0, ',', '.') : '' }}
                                    </td>

                                    {{-- NERACA SALDO DISESUAIKAN --}}
                                    <td class="border border-zinc-200 px-3 py-2 text-right font-medium tabular-nums text-zinc-800">
                                        {{ $row['nsd_debit'] > 0 ? number_format($row['nsd_debit'], 0, ',', '.') : '' }}
                                    </td>

                                    <td class="border border-zinc-200 px-3 py-2 text-right font-medium tabular-nums text-zinc-800">
                                        {{ $row['nsd_kredit'] > 0 ? number_format($row['nsd_kredit'], 0, ',', '.') : '' }}
                                    </td>

                                    {{-- LABA RUGI --}}
                                    <td class="border border-zinc-200 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['lr_debit'] > 0 ? number_format($row['lr_debit'], 0, ',', '.') : '' }}
                                    </td>

                                    <td class="border border-zinc-200 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['lr_kredit'] > 0 ? number_format($row['lr_kredit'], 0, ',', '.') : '' }}
                                    </td>

                                    {{-- NERACA --}}
                                    <td class="border border-zinc-200 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['n_debit'] > 0 ? number_format($row['n_debit'], 0, ',', '.') : '' }}
                                    </td>

                                    <td class="border border-zinc-200 px-3 py-2 text-right tabular-nums text-zinc-700">
                                        {{ $row['n_kredit'] > 0 ? number_format($row['n_kredit'], 0, ',', '.') : '' }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td
                                        colspan="12"
                                        class="border border-zinc-200 px-4 py-12 text-center"
                                    >
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-zinc-100">
                                                <svg
                                                    class="h-5 w-5 text-zinc-400"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M3 7.5 12 3l9 4.5M3 7.5V16.5L12 21l9-4.5V7.5M3 7.5l9 4.5 9-4.5M12 12v9"
                                                    />
                                                </svg>
                                            </div>

                                            <p class="text-sm font-medium text-zinc-700">
                                                Tidak ada data transaksi
                                            </p>

                                            <p class="mt-1 text-xs text-zinc-400">
                                                Tidak ada data transaksi pada periode ini.
                                            </p>
                                        </div>
                                    </td>
                                </tr>

                            @endforelse

                            {{-- SUB TOTAL --}}
                            <tr class="bg-zinc-100 font-semibold text-zinc-900">

                                <td
                                    colspan="2"
                                    class="border border-zinc-300 px-3 py-3 text-center text-[10px] uppercase tracking-[0.12em]"
                                >
                                    Jumlah / Sub-Total
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['ns_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['ns_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['aj_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['aj_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['nsd_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['nsd_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['lr_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['lr_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['n_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['n_kredit'], 0, ',', '.') }}
                                </td>

                            </tr>

                            {{-- LABA / RUGI BERSIH --}}
                            <tr class="bg-zinc-50 font-semibold text-zinc-900">

                                <td
                                    colspan="2"
                                    class="border border-zinc-300 px-3 py-3 text-center text-[11px] italic"
                                >
                                    {{ $lr_balance >= 0 ? 'Laba Bersih Periode Berjalan' : 'Rugi Bersih Periode Berjalan' }}
                                </td>

                                <td
                                    colspan="6"
                                    class="border border-zinc-300 bg-white px-3 py-3"
                                ></td>

                                {{-- LABA RUGI --}}
                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ $lr_balance < 0 ? number_format(abs($lr_balance), 0, ',', '.') : '' }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right font-bold tabular-nums text-zinc-950">
                                    {{ $lr_balance >= 0 ? number_format($lr_balance, 0, ',', '.') : '' }}
                                </td>

                                {{-- NERACA --}}
                                <td class="border border-zinc-300 px-3 py-3 text-right font-bold tabular-nums text-zinc-950">
                                    {{ $lr_balance >= 0 ? number_format($lr_balance, 0, ',', '.') : '' }}
                                </td>

                                <td class="border border-zinc-300 px-3 py-3 text-right tabular-nums">
                                    {{ $lr_balance < 0 ? number_format(abs($lr_balance), 0, ',', '.') : '' }}
                                </td>

                            </tr>

                            {{-- GRAND TOTAL --}}
                            <tr class="bg-zinc-950 font-bold text-white">

                                <td
                                    colspan="2"
                                    class="border border-zinc-700 px-3 py-3 text-center text-[10px] uppercase tracking-[0.2em]"
                                >
                                    Total
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['ns_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['ns_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['aj_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['aj_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['nsd_debit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format($totals['nsd_kredit'], 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format(max($totals['lr_debit'], $totals['lr_kredit']), 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format(max($totals['lr_debit'], $totals['lr_kredit']), 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format(max($totals['n_debit'], $totals['n_kredit']), 0, ',', '.') }}
                                </td>

                                <td class="border border-zinc-700 px-3 py-3 text-right tabular-nums">
                                    {{ number_format(max($totals['n_debit'], $totals['n_kredit']), 0, ',', '.') }}
                                </td>

                            </tr>

                        </tbody>
                    </table>
                </div>

                {{-- FOOTER --}}
                <div class="border-t border-zinc-200 bg-zinc-50 px-5 py-3">
                    <div class="flex flex-col gap-1 text-[11px] text-zinc-400 sm:flex-row sm:items-center sm:justify-between">
                        <span>
                            Worksheet periode {{ \Carbon\Carbon::parse($periode)->translatedFormat('F Y') }}
                        </span>

                        <span class="font-medium text-zinc-500">
                            Debit & Kredit
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection