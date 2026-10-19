@extends('layout.app')
@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">
                    <span class="h-px w-8 bg-zinc-900"></span>
                    Finance
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-zinc-950 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M6 3h12v18H6z"/>
                            <path d="M9 7h6M9 11h6M9 15h4"/>
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                            Jurnal Umum
                        </h1>
                        <p class="mt-1 text-sm text-zinc-500">
                            Kelola dan pantau seluruh transaksi jurnal perusahaan.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    onclick="exportJournalPdf()"
                    class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 2h9l4 4v16H6z"/>
                        <path d="M14 2v5h5M9 13h6M9 17h6"/>
                    </svg>
                    Export PDF
                </button>

                <button
                    type="button"
                    data-modal-open="modalTambahJurnal"
                    class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Buat Jurnal Baru
                </button>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="mb-5 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
            <div class="border-b border-zinc-100 bg-zinc-50/80 px-5 py-4">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-zinc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 5h16M7 12h10M10 19h4"/>
                    </svg>
                    <span class="text-sm font-semibold text-zinc-900">Filter Transaksi</span>
                </div>
            </div>

            <div class="p-5">
                <form id="formFilterJurnal">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">

                        <div>
                            <label for="filter_period" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                Periode Bulan
                            </label>
                            <input
                                type="month"
                                id="filter_period"
                                class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                value="{{ date('Y-m') }}"
                                onchange="updateDatesFromPeriod()"
                            >
                        </div>

                        <div>
                            <label for="filter_start_date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                Dari Tanggal
                            </label>
                            <input
                                type="date"
                                id="filter_start_date"
                                class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                value="{{ date('Y-m-01') }}"
                                onchange="loadJournalData()"
                            >
                        </div>

                        <div>
                            <label for="filter_end_date" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                Sampai Tanggal
                            </label>
                            <input
                                type="date"
                                id="filter_end_date"
                                class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                value="{{ date('Y-m-t') }}"
                                onchange="loadJournalData()"
                            >
                        </div>

                        <div>
                            <label for="filter_status" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                Status
                            </label>
                            <select
                                id="filter_status"
                                class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                onchange="loadJournalData()"
                            >
                                <option value="">-- Semua --</option>
                                <option value="draft">Draft</option>
                                <option value="posted">Posted</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="filter_search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                Cari No. Jurnal / Invoice / Keterangan
                            </label>

                            <div class="flex">
                                <input
                                    type="text"
                                    id="filter_search"
                                    class="min-w-0 flex-1 rounded-l-xl border border-r-0 border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    placeholder="Ketik keyword..."
                                >

                                <button
                                    class="inline-flex items-center justify-center rounded-r-xl bg-zinc-950 px-4 text-white transition hover:bg-zinc-800"
                                    type="button"
                                    onclick="loadJournalData()"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="11" cy="11" r="6"/>
                                        <path d="m16 16 4 4"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button
                            type="button"
                            onclick="resetJournalFilter()"
                            class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-600 transition hover:border-zinc-300 hover:bg-zinc-50 hover:text-zinc-900"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path d="M3 4v5h5"/>
                            </svg>
                            Reset Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-950">
                        Daftar Transaksi
                    </h2>
                    <p class="mt-0.5 text-xs text-zinc-500">
                        Master transaksi beserta rincian akun ledger.
                    </p>
                </div>

                <div class="hidden rounded-full bg-zinc-100 px-3 py-1.5 text-xs font-medium text-zinc-500 sm:block">
                    General Journal
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[1250px] w-full text-sm text-zinc-700" id="journalTable">
                    <thead class="bg-zinc-950 text-xs uppercase tracking-wide text-white">
                        <tr>
                            <th class="px-4 py-3.5 text-center font-semibold" style="width: 12%">
                                Tanggal / No. Jurnal
                            </th>
                            <th class="px-4 py-3.5 text-left font-semibold" style="width: 15%">
                                No. Invoice / Keterangan
                            </th>
                            <th class="px-4 py-3.5 text-center font-semibold" style="width: 15%">
                                Kode Akun
                            </th>
                            <th class="px-4 py-3.5 text-left font-semibold" style="width: 23%">
                                Nama Akun COA
                            </th>
                            <th class="px-4 py-3.5 text-right font-semibold" style="width: 15%">
                                Debit (Rp)
                            </th>
                            <th class="px-4 py-3.5 text-right font-semibold" style="width: 15%">
                                Kredit (Rp)
                            </th>
                            <th class="px-4 py-3.5 text-center font-semibold" style="width: 15%">
                                Status
                            </th>
                            <th class="px-4 py-3.5 text-center font-semibold" style="width: 5%">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody id="journalTableBody" class="divide-y divide-zinc-100">
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-500">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="9" class="stroke-zinc-200" stroke-width="3"/>
                                        <path d="M21 12a9 9 0 0 0-9-9" class="stroke-zinc-900" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    Sedang memuat data jurnal...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>


{{-- =========================================================
     MODAL TAMBAH JURNAL
========================================================= --}}
<div
    id="modalTambahJurnal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop="modalTambahJurnal"
    ></div>

    <div class="relative flex min-h-full items-start justify-center p-4 sm:p-6 lg:p-8">
        <div class="relative my-4 w-full max-w-7xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between bg-zinc-950 px-5 py-4 text-white">
                <div>
                    <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                        Finance / Journal
                    </div>

                    <h2 class="text-lg font-semibold" id="modalTambahJurnalLabel">
                        Input Transaksi Jurnal Umum
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="modalTambahJurnal"
                    class="rounded-xl p-2 text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>

            <form id="formTambahJurnal" action="{{ route('general_journal.store', $id) }}" method="POST">
                @csrf
                <input type="hidden" name="perusahaan" value="{{ $id }}">

                <div class="max-h-[75vh] overflow-y-auto p-5 sm:p-6">

                    {{-- HEADER TRANSAKSI --}}
                    <div class="mb-6 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-5">
                        <div class="mb-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">
                                Header Jurnal
                            </p>
                            <h3 class="mt-1 text-base font-semibold text-zinc-950">
                                Informasi Transaksi
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

                            <div class="xl:col-span-1">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Tanggal Transaksi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="tgl_transaksi"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    value="{{ date('Y-m-d') }}"
                                    required
                                >
                            </div>

                            <div class="xl:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    No. Invoice Referensi
                                </label>
                                <input
                                    type="text"
                                    name="no_invoice"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    placeholder="Contoh: INV/2026/0012"
                                >
                            </div>

                            <div class="md:col-span-2 xl:col-span-3">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Keterangan Umum <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    name="keterangan"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    rows="1"
                                    placeholder="Contoh: Pembayaran sewa gedung kantor"
                                    required
                                ></textarea>
                            </div>

                            <div class="xl:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Status Awal Jurnal <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="status"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    required
                                >
                                    <option value="draft" selected>Draft</option>
                                    <option value="posted">Posted</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 xl:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Aktivitas Arus Kas (Metode Langsung) <span class="text-red-500">*</span>
                                </label>

                                <select
                                    name="cash_flow_activity"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    required
                                >
                                    <option value="non_cash" selected>Bukan Transaksi Kas (Non-Kas)</option>
                                    <option value="operasional">Operasional</option>
                                    <option value="investasi">Investasi</option>
                                    <option value="pendanaan">Pendanaan</option>
                                </select>

                                <p class="mt-1.5 text-xs text-zinc-400">
                                    Pilih jenis aktivitas jika transaksi ini mengalirkan uang tunai/bank.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- DETAIL --}}
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M8 6h13M8 12h13M8 18h13"/>
                                <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-zinc-950">
                                Rincian Akun Transaksi
                            </h3>
                            <p class="text-xs text-zinc-500">
                                Masukkan akun ledger dan nominal debit/kredit.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-zinc-200">
                        <table class="min-w-[950px] w-full text-sm" id="tableJurnalEntries">
                            <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold" style="width:40%">
                                        Pilih Akun Chart Of Account <span class="text-red-500">*</span>
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold" style="width:25%">
                                        Keterangan Item Baris
                                    </th>
                                    <th class="px-4 py-3 text-right font-semibold" style="width:15%">
                                        Debit (Rp)
                                    </th>
                                    <th class="px-4 py-3 text-right font-semibold" style="width:15%">
                                        Kredit (Rp)
                                    </th>
                                    <th class="px-4 py-3 text-center font-semibold" style="width:5%">
                                        #
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="entriesRowContainer" class="divide-y divide-zinc-100">

                                {{-- ROW 1 --}}
                                <tr class="entry-row">
                                    <td class="p-3">
                                        <select
                                            name="items[0][chart_of_account_id]"
                                            class="select-coa-target block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            required
                                        >
                                            <option value="">- Cari / Pilih Akun COA -</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ strtoupper($acc->type) }}">
                                                    {{ $acc->kode }} - {{ $acc->nama_akun }} ({{ $acc->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="text"
                                            name="items[0][keterangan_item]"
                                            class="block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            placeholder="Keterangan baris..."
                                        >
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="number"
                                            name="items[0][debit]"
                                            class="input-nominal input-debit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            value="0"
                                            min="0"
                                            step="0.01"
                                            required
                                        >
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="number"
                                            name="items[0][kredit]"
                                            class="input-nominal input-kredit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            value="0"
                                            min="0"
                                            step="0.01"
                                            required
                                        >
                                    </td>

                                    <td class="p-3 text-center">
                                        <button
                                            type="button"
                                            class="disabled-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-300"
                                            disabled
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>

                                {{-- ROW 2 --}}
                                <tr class="entry-row">
                                    <td class="p-3">
                                        <select
                                            name="items[1][chart_of_account_id]"
                                            class="select-coa-target block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            required
                                        >
                                            <option value="">- Cari / Pilih Akun COA -</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}" data-type="{{ strtoupper($acc->type) }}">
                                                    {{ $acc->kode }} - {{ $acc->nama_akun }} ({{ $acc->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="text"
                                            name="items[1][keterangan_item]"
                                            class="block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            placeholder="Keterangan baris..."
                                        >
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="number"
                                            name="items[1][debit]"
                                            class="input-nominal input-debit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            value="0"
                                            min="0"
                                            step="0.01"
                                            required
                                        >
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="number"
                                            name="items[1][kredit]"
                                            class="input-nominal input-kredit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                            value="0"
                                            min="0"
                                            step="0.01"
                                            required
                                        >
                                    </td>

                                    <td class="p-3 text-center">
                                        <button
                                            type="button"
                                            class="disabled-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-300"
                                            disabled
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="border-t border-zinc-200 bg-zinc-50">
                                <tr>
                                    <td class="p-3">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-zinc-800"
                                            onclick="addNewEntryRow()"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M12 8v8M8 12h8"/>
                                            </svg>
                                            Tambah Baris Akun
                                        </button>
                                    </td>

                                    <td class="px-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                        Total Balance
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="text"
                                            id="total_debit_label"
                                            class="w-full bg-transparent text-right text-sm font-bold text-emerald-700 outline-none"
                                            value="Rp 0"
                                            readonly
                                        >
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="text"
                                            id="total_kredit_label"
                                            class="w-full bg-transparent text-right text-sm font-bold text-red-700 outline-none"
                                            value="Rp 0"
                                            readonly
                                        >
                                    </td>

                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div
                        id="balance_alert"
                        class="mt-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                    >
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 9v4M12 17h.01"/>
                                <path d="m10.3 3.8-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.2l-8-14a2 2 0 0 0-3.4 0Z"/>
                            </svg>
                            <span>
                                Jumlah Debit dan Kredit tidak seimbang!
                                Selisih:
                                <strong id="balance_diff_text">Rp 0</strong>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                    <button
                        type="button"
                        data-modal-close="modalTambahJurnal"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        id="btnSimpanJurnal"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40"
                        disabled
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 4h11l3 3v13H5z"/>
                            <path d="M8 4v5h8V4M8 20v-6h8v6"/>
                        </svg>
                        Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- =========================================================
     MODAL EDIT JURNAL
========================================================= --}}
<div
    id="modalEditJurnal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop="modalEditJurnal"
    ></div>

    <div class="relative flex min-h-full items-start justify-center p-4 sm:p-6 lg:p-8">
        <div class="relative my-4 w-full max-w-7xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between bg-zinc-950 px-5 py-4 text-white">
                <div>
                    <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                        Finance / Journal
                    </div>

                    <h2 class="text-lg font-semibold" id="modalEditJurnalLabel">
                        Edit Transaksi Jurnal Umum
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="modalEditJurnal"
                    class="rounded-xl p-2 text-zinc-400 transition hover:bg-white/10 hover:text-white"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>

            <form id="formEditJurnal" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="perusahaan" value="{{ $id }}">

                <div class="max-h-[75vh] overflow-y-auto p-5 sm:p-6">

                    <div class="mb-6 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-5">
                        <div class="mb-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-zinc-500">
                                Header Jurnal
                            </p>
                            <h3 class="mt-1 text-base font-semibold text-zinc-950">
                                Informasi Transaksi
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

                            <div class="xl:col-span-1">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Tanggal Transaksi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    name="tgl_transaksi"
                                    id="edit_tgl_transaksi"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    required
                                >
                            </div>

                            <div class="xl:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    No. Invoice Referensi
                                </label>
                                <input
                                    type="text"
                                    name="no_invoice"
                                    id="edit_no_invoice"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    placeholder="Contoh: INV/2026/0012"
                                >
                            </div>

                            <div class="md:col-span-2 xl:col-span-3">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Keterangan Umum <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    name="keterangan"
                                    id="edit_keterangan"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    rows="1"
                                    required
                                ></textarea>
                            </div>

                            <div class="xl:col-span-2">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Status Jurnal <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="status"
                                    id="edit_status"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    required
                                >
                                    <option value="draft">Draft (Simpan Sementara)</option>
                                    <option value="posted">Posted (Masuk Buku Besar)</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 xl:col-span-4">
                                <label class="mb-1.5 block text-xs font-semibold text-zinc-600">
                                    Aktivitas Arus Kas (Metode Langsung) <span class="text-red-500">*</span>
                                </label>

                                <select
                                    name="cash_flow_activity"
                                    id="edit_cash_flow"
                                    class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    required
                                >
                                    <option value="non_cash" selected>Bukan Transaksi Kas (Non-Kas)</option>
                                    <option value="operasional">Operasional</option>
                                    <option value="investasi">Investasi</option>
                                    <option value="pendanaan">Pendanaan</option>
                                </select>

                                <p class="mt-1.5 text-xs text-zinc-400">
                                    Pilih jenis aktivitas jika transaksi ini mengalirkan uang tunai/bank.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M8 6h13M8 12h13M8 18h13"/>
                                <path d="M3 6h.01M3 12h.01M3 18h.01"/>
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-zinc-950">
                                Rincian Akun Transaksi
                            </h3>
                            <p class="text-xs text-zinc-500">
                                Perbarui rincian akun dan nominal transaksi.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-zinc-200">
                        <table class="min-w-[950px] w-full text-sm" id="tableJurnalEditEntries">
                            <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold" style="width:40%">
                                        Pilih Akun Chart Of Account <span class="text-red-500">*</span>
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold" style="width:25%">
                                        Keterangan Item Baris
                                    </th>
                                    <th class="px-4 py-3 text-right font-semibold" style="width:15%">
                                        Debit (Rp)
                                    </th>
                                    <th class="px-4 py-3 text-right font-semibold" style="width:15%">
                                        Kredit (Rp)
                                    </th>
                                    <th class="px-4 py-3 text-center font-semibold" style="width:5%">
                                        #
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="editEntriesRowContainer" class="divide-y divide-zinc-100"></tbody>

                            <tfoot class="border-t border-zinc-200 bg-zinc-50">
                                <tr>
                                    <td class="p-3">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-zinc-800"
                                            onclick="addNewEditEntryRow()"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="M12 8v8M8 12h8"/>
                                            </svg>
                                            Tambah Baris Akun
                                        </button>
                                    </td>

                                    <td class="px-3 text-right text-xs font-semibold uppercase tracking-wide text-zinc-500">
                                        Total Balance
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="text"
                                            id="edit_total_debit_label"
                                            class="w-full bg-transparent text-right text-sm font-bold text-emerald-700 outline-none"
                                            value="Rp 0"
                                            readonly
                                        >
                                    </td>

                                    <td class="p-3">
                                        <input
                                            type="text"
                                            id="edit_total_kredit_label"
                                            class="w-full bg-transparent text-right text-sm font-bold text-red-700 outline-none"
                                            value="Rp 0"
                                            readonly
                                        >
                                    </td>

                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div
                        id="edit_balance_alert"
                        class="mt-3 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                    >
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 9v4M12 17h.01"/>
                                <path d="m10.3 3.8-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.2l-8-14a2 2 0 0 0-3.4 0Z"/>
                            </svg>
                            <span>
                                Jumlah Debit dan Kredit tidak seimbang!
                                Selisih:
                                <strong id="edit_balance_diff_text">Rp 0</strong>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                    <button
                        type="button"
                        data-modal-close="modalEditJurnal"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        id="btnUpdateJurnal"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40"
                        disabled
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 4h11l3 3v13H5z"/>
                            <path d="M8 4v5h8V4M8 20v-6h8v6"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- =========================================================
     MODAL HAPUS JURNAL
========================================================= --}}
<div
    id="modalHapusJurnal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop="modalHapusJurnal"
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between bg-red-600 px-5 py-4 text-white">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 9v4M12 17h.01"/>
                            <path d="m10.3 3.8-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.2l-8-14a2 2 0 0 0-3.4 0Z"/>
                        </svg>
                    </div>

                    <h2 class="text-base font-semibold" id="modalHapusJurnalLabel">
                        Konfirmasi Hapus Transaksi
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="modalHapusJurnal"
                    class="rounded-xl p-2 text-white/70 transition hover:bg-white/10 hover:text-white"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>

            <form id="formHapusJurnal" method="POST">
                @csrf
                @method('DELETE')

                <div class="p-5 sm:p-6">
                    <p class="text-sm text-zinc-600">
                        Apakah Anda yakin ingin menghapus/membatalkan transaksi jurnal ini?
                    </p>

                    <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <span class="block text-xs font-semibold uppercase tracking-wide text-zinc-400">
                            Nomor Jurnal
                        </span>

                        <h3 id="hapus_no_jurnal" class="mt-1 text-base font-bold text-red-600">
                            -
                        </h3>

                        <span class="mt-4 block text-xs font-semibold uppercase tracking-wide text-zinc-400">
                            Keterangan
                        </span>

                        <p id="hapus_keterangan" class="mt-1 text-sm text-zinc-700">
                            -
                        </p>
                    </div>

                    <div class="mt-4 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 9v4M12 17h.01"/>
                            <path d="m10.3 3.8-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.2l-8-14a2 2 0 0 0-3.4 0Z"/>
                        </svg>

                        <span>
                            Tindakan ini akan menghapus data header beserta seluruh rincian akun ledger yang terikat didalamnya.
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4">
                    <button
                        type="button"
                        data-modal-close="modalHapusJurnal"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-600 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                        </svg>
                        Ya, Hapus Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    let entryRowIndex = 2;
    let editEntryRowIndex = 2;

    /*
    |--------------------------------------------------------------------------
    | Modal Helper
    |--------------------------------------------------------------------------
    */
    function openJournalModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function closeJournalModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('[id^="modal"]:not(.hidden)')) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    document.addEventListener('click', function (e) {
        const openButton = e.target.closest('[data-modal-open]');

        if (openButton) {
            e.preventDefault();
            openJournalModal(openButton.getAttribute('data-modal-open'));
            return;
        }

        const closeButton = e.target.closest('[data-modal-close]');

        if (closeButton) {
            e.preventDefault();
            closeJournalModal(closeButton.getAttribute('data-modal-close'));
            return;
        }

        const backdrop = e.target.closest('[data-modal-backdrop]');

        if (backdrop && e.target === backdrop) {
            closeJournalModal(backdrop.getAttribute('data-modal-backdrop'));
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;

        const activeModal = document.querySelector('[id^="modal"]:not(.hidden)');

        if (activeModal) {
            closeJournalModal(activeModal.id);
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Format Rupiah
    |--------------------------------------------------------------------------
    */
    function formatJournalRupiah(value) {
        const number = parseFloat(value) || 0;

        return 'Rp ' + number.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Add Entry Row
    |--------------------------------------------------------------------------
    */
    function addNewEntryRow() {
        const container = document.getElementById('entriesRowContainer');
        const masterSelect = document.querySelector('.select-coa-target');

        const optionsHtml = masterSelect
            ? masterSelect.innerHTML
            : '<option value="">- Cari / Pilih Akun COA -</option>';

        const newRowHtml = `
            <tr class="entry-row">
                <td class="p-3">
                    <select
                        name="items[${entryRowIndex}][chart_of_account_id]"
                        class="select-coa-target block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        required
                    >
                        ${optionsHtml}
                    </select>
                </td>

                <td class="p-3">
                    <input
                        type="text"
                        name="items[${entryRowIndex}][keterangan_item]"
                        class="block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        placeholder="Keterangan baris..."
                    >
                </td>

                <td class="p-3">
                    <input
                        type="number"
                        name="items[${entryRowIndex}][debit]"
                        class="input-nominal input-debit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        value="0"
                        min="0"
                        step="0.01"
                        required
                    >
                </td>

                <td class="p-3">
                    <input
                        type="number"
                        name="items[${entryRowIndex}][kredit]"
                        class="input-nominal input-kredit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        value="0"
                        min="0"
                        step="0.01"
                        required
                    >
                </td>

                <td class="p-3 text-center">
                    <button
                        type="button"
                        class="row-delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100"
                        onclick="removeEntryRow(this)"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `;

        container.insertAdjacentHTML('beforeend', newRowHtml);

        entryRowIndex++;

        calculateJournalTotals();
    }


    /*
    |--------------------------------------------------------------------------
    | Add Edit Entry Row
    |--------------------------------------------------------------------------
    */
    function addNewEditEntryRow() {
        const container = document.getElementById('editEntriesRowContainer');

        const masterSelect =
            container.querySelector('.select-edit-coa-target') ||
            document.querySelector('.select-coa-target');

        const optionsHtml = masterSelect
            ? masterSelect.innerHTML
            : '<option value="">- Cari / Pilih Akun COA -</option>';

        const newRowHtml = `
            <tr class="edit-entry-row">
                <td class="p-3">
                    <select
                        name="items[${editEntryRowIndex}][chart_of_account_id]"
                        class="select-edit-coa-target block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        required
                    >
                        ${optionsHtml}
                    </select>
                </td>

                <td class="p-3">
                    <input
                        type="text"
                        name="items[${editEntryRowIndex}][keterangan_item]"
                        class="block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        placeholder="Keterangan baris..."
                    >
                </td>

                <td class="p-3">
                    <input
                        type="number"
                        name="items[${editEntryRowIndex}][debit]"
                        class="input-edit-nominal input-edit-debit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        value="0"
                        min="0"
                        step="0.01"
                        required
                    >
                </td>

                <td class="p-3">
                    <input
                        type="number"
                        name="items[${editEntryRowIndex}][kredit]"
                        class="input-edit-nominal input-edit-kredit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        value="0"
                        min="0"
                        step="0.01"
                        required
                    >
                </td>

                <td class="p-3 text-center">
                    <button
                        type="button"
                        class="row-delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100"
                        onclick="this.closest('tr').remove(); calculateEditTotals();"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `;

        container.insertAdjacentHTML('beforeend', newRowHtml);

        editEntryRowIndex++;

        calculateEditTotals();
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Entry
    |--------------------------------------------------------------------------
    */
    function removeEntryRow(button) {
        const row = button.closest('tr');

        if (row) {
            row.remove();
        }

        calculateJournalTotals();
    }


    /*
    |--------------------------------------------------------------------------
    | Export PDF
    |--------------------------------------------------------------------------
    */
    function exportJournalPdf() {
        const startDate = document.getElementById('filter_start_date').value;
        const endDate = document.getElementById('filter_end_date').value;
        const status = document.getElementById('filter_status').value;
        const search = document.getElementById('filter_search').value;

        const params = new URLSearchParams({
            start_date: startDate,
            end_date: endDate,
            status: status,
            search: search
        });

        window.open(
            `{{ route('journals.export-pdf') }}?${params.toString()}`,
            '_blank'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COA Selection
    |--------------------------------------------------------------------------
    */
    document.addEventListener('change', function (e) {

        if (e.target.classList.contains('select-coa-target')) {
            const currentRow = e.target.closest('tr');

            if (!currentRow) return;

            const selectedOption =
                e.target.options[e.target.selectedIndex];

            const debitInput =
                currentRow.querySelector('.input-debit');

            const kreditInput =
                currentRow.querySelector('.input-kredit');

            if (debitInput) {
                debitInput.removeAttribute('readonly');
                debitInput.classList.remove('bg-zinc-100');
            }

            if (kreditInput) {
                kreditInput.removeAttribute('readonly');
                kreditInput.classList.remove('bg-zinc-100');
            }

            calculateJournalTotals();
        }


        if (e.target.classList.contains('select-edit-coa-target')) {
            const currentRow = e.target.closest('tr');

            if (!currentRow) return;

            const debitInput =
                currentRow.querySelector('.input-edit-debit');

            const kreditInput =
                currentRow.querySelector('.input-edit-kredit');

            if (debitInput) {
                debitInput.removeAttribute('readonly');
                debitInput.classList.remove('bg-zinc-100');
            }

            if (kreditInput) {
                kreditInput.removeAttribute('readonly');
                kreditInput.classList.remove('bg-zinc-100');
            }

            calculateEditTotals();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Calculate Add Journal
    |--------------------------------------------------------------------------
    */
    function calculateJournalTotals() {
        let totalDebit = 0;
        let totalKredit = 0;

        document.querySelectorAll('.input-debit').forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.input-kredit').forEach(input => {
            totalKredit += parseFloat(input.value) || 0;
        });

        const debitLabel =
            document.getElementById('total_debit_label');

        const kreditLabel =
            document.getElementById('total_kredit_label');

        if (debitLabel) {
            debitLabel.value = formatJournalRupiah(totalDebit);
        }

        if (kreditLabel) {
            kreditLabel.value = formatJournalRupiah(totalKredit);
        }

        const alertBox =
            document.getElementById('balance_alert');

        const btnSimpan =
            document.getElementById('btnSimpanJurnal');

        const diffText =
            document.getElementById('balance_diff_text');

        if (!alertBox || !btnSimpan || !diffText) return;

        const diff =
            Math.abs(totalDebit - totalKredit);

        if (
            totalDebit.toFixed(2) === totalKredit.toFixed(2) &&
            totalDebit > 0
        ) {
            alertBox.classList.add('hidden');
            btnSimpan.disabled = false;
        } else {
            alertBox.classList.remove('hidden');
            btnSimpan.disabled = true;

            if (totalDebit === 0 && totalKredit === 0) {
                diffText.innerText =
                    'Nominal transaksi tidak boleh kosong.';
            } else {
                diffText.innerText =
                    formatJournalRupiah(diff);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Edit Journal
    |--------------------------------------------------------------------------
    */
    function calculateEditTotals() {
        let totalDebit = 0;
        let totalKredit = 0;

        document.querySelectorAll('.input-edit-debit').forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.input-edit-kredit').forEach(input => {
            totalKredit += parseFloat(input.value) || 0;
        });

        const debitLabel =
            document.getElementById('edit_total_debit_label');

        const kreditLabel =
            document.getElementById('edit_total_kredit_label');

        if (debitLabel) {
            debitLabel.value =
                formatJournalRupiah(totalDebit);
        }

        if (kreditLabel) {
            kreditLabel.value =
                formatJournalRupiah(totalKredit);
        }

        const alertBox =
            document.getElementById('edit_balance_alert');

        const btnUpdate =
            document.getElementById('btnUpdateJurnal');

        const diffText =
            document.getElementById('edit_balance_diff_text');

        if (!alertBox || !btnUpdate || !diffText) return;

        const diff =
            Math.abs(totalDebit - totalKredit);

        if (
            totalDebit.toFixed(2) === totalKredit.toFixed(2) &&
            totalDebit > 0
        ) {
            alertBox.classList.add('hidden');
            btnUpdate.disabled = false;
        } else {
            alertBox.classList.remove('hidden');
            btnUpdate.disabled = true;
            diffText.innerText =
                formatJournalRupiah(diff);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Load Journal
    |--------------------------------------------------------------------------
    */
    document.addEventListener('DOMContentLoaded', function () {
        loadJournalData();
        calculateJournalTotals();

        const searchInput =
            document.getElementById('filter_search');

        if (searchInput) {
            let searchTimeout = null;

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(() => {
                    loadJournalData();
                }, 500);
            });
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Nominal Input Listener
    |--------------------------------------------------------------------------
    */
    document.addEventListener('input', function (e) {

        if (e.target.classList.contains('input-nominal')) {
            calculateJournalTotals();
        }

        if (e.target.classList.contains('input-edit-nominal')) {
            calculateEditTotals();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Load Journal Data
    |--------------------------------------------------------------------------
    */
    function loadJournalData() {
        const startDate =
            document.getElementById('filter_start_date').value;

        const endDate =
            document.getElementById('filter_end_date').value;

        const search =
            document.getElementById('filter_search').value;

        const status =
            document.getElementById('filter_status').value;

        const tbody =
            document.getElementById('journalTableBody');

        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-500">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" class="stroke-zinc-200" stroke-width="3"/>
                            <path d="M21 12a9 9 0 0 0-9-9" class="stroke-zinc-900" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        Memuat data transaksi...
                    </div>
                </td>
            </tr>
        `;

        const params =
            new URLSearchParams({
                start_date: startDate,
                end_date: endDate
            });

        if (search) {
            params.append('search', search);
        }

        if (status) {
            params.append('status', status);
        }

        const url =
            '{{ route("general_journal.data", $id) }}?' +
            params.toString();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Gagal mengambil data jurnal.');
            }

            return res.json();
        })
        .then(response => {

            const data = response.journals || [];
            const lockedPeriods = response.isLocked || [];

            tbody.innerHTML = '';

            if (!data.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M6 3h12v18H6z"/>
                                        <path d="M9 8h6M9 12h6M9 16h4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-zinc-700">
                                    Tidak ditemukan transaksi jurnal
                                </p>
                                <p class="mt-1 text-xs text-zinc-400">
                                    Coba ubah periode atau filter pencarian.
                                </p>
                            </div>
                        </td>
                    </tr>
                `;

                return;
            }


            function isLockedPeriod(dateString) {
                if (!dateString) return false;

                const txDate = new Date(dateString);

                if (isNaN(txDate.getTime())) {
                    return false;
                }

                const now = new Date();

                if (
                    txDate.getFullYear() === now.getFullYear() &&
                    txDate.getMonth() === now.getMonth()
                ) {
                    return false;
                }

                const period =
                    `${txDate.getFullYear()}-${String(
                        txDate.getMonth() + 1
                    ).padStart(2, '0')}`;

                const periods =
                    Array.isArray(lockedPeriods)
                        ? lockedPeriods.map(p => String(p).trim())
                        : [];

                return periods.includes(period);
            }


            let currentJournalId = null;

            data.forEach(row => {

                const debitVal =
                    parseFloat(row.debit) || 0;

                const kreditVal =
                    parseFloat(row.kredit) || 0;

                const debitFormatted =
                    debitVal > 0
                        ? formatJournalRupiah(debitVal)
                        : '';

                const kreditFormatted =
                    kreditVal > 0
                        ? formatJournalRupiah(kreditVal)
                        : '';

                let dateCell = '';
                let descCell = '';
                let statusCell = '';
                let actionCell = '';

                const isFirstRow =
                    currentJournalId !== row.journal_id;

                if (isFirstRow) {

                    const isLocked =
                        row.status === 'posted' ||
                        isLockedPeriod(row.tgl_transaksi);

                    let statusBadge = '';

                    if (row.status === 'posted') {
                        statusBadge = `
                            <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>
                                Posted
                            </span>
                        `;
                    } else if (isLocked) {
                        statusBadge = `
                            <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-semibold text-zinc-600 ring-1 ring-inset ring-zinc-200">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="5" y="10" width="14" height="10" rx="2"/>
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                                </svg>
                                Locked
                            </span>
                        `;
                    } else {
                        statusBadge = `
                            <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M6 4h12v16H6z"/>
                                    <path d="M9 8h6M9 12h6"/>
                                </svg>
                                Draft
                            </span>
                        `;
                    }

                    dateCell = `
                        <div class="font-semibold text-zinc-900">
                            ${row.tgl_transaksi}
                        </div>
                        <div class="mt-1 text-xs font-medium text-zinc-500">
                            ${row.no_jurnal || '-'}
                        </div>
                    `;

                    descCell = `
                        <div class="font-semibold text-zinc-900">
                            ${row.no_invoice || '-'}
                        </div>
                        <div class="mt-1 text-xs leading-relaxed text-zinc-500">
                            ${row.keterangan_header || '-'}
                        </div>
                    `;

                    statusCell = statusBadge;

                    actionCell = `
                        <div class="flex items-center justify-center gap-1.5">
                            <button
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition hover:border-zinc-300 hover:bg-zinc-50 hover:text-zinc-900"
                                onclick="openEditJournalModal(${row.journal_id})"
                                title="Edit Jurnal"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                </svg>
                            </button>

                            <button
                                type="button"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:bg-red-100"
                                onclick="voidJournal(${row.journal_id})"
                                title="Void Jurnal"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="m8 8 8 8M16 8l-8 8"/>
                                </svg>
                            </button>
                        </div>
                    `;
                }

                const isCredit =
                    kreditVal > 0;

                const trHtml = `
                    <tr class="transition hover:bg-zinc-50">
                        <td class="px-4 py-4 align-top text-center">
                            ${dateCell}
                        </td>

                        <td class="px-4 py-4 align-top">
                            ${descCell}
                        </td>

                        <td class="px-4 py-4 align-top text-center">
                            <span class="font-mono text-xs font-semibold text-zinc-700">
                                ${row.kode_akun || '-'}
                            </span>
                        </td>

                        <td class="px-4 py-4 align-top">
                            <div class="${isCredit ? 'pl-5 text-zinc-600' : 'font-semibold text-zinc-900'}">
                                ${row.nama_akun || '-'}
                            </div>

                            ${
                                row.keterangan_item
                                    ? `
                                        <div class="mt-1 pl-5 text-xs italic text-zinc-400">
                                            (${row.keterangan_item})
                                        </div>
                                    `
                                    : ''
                            }
                        </td>

                        <td class="px-4 py-4 text-right align-top font-mono text-xs font-semibold text-emerald-700">
                            ${debitFormatted}
                        </td>

                        <td class="px-4 py-4 text-right align-top font-mono text-xs font-semibold text-red-700">
                            ${kreditFormatted}
                        </td>

                        <td class="px-4 py-4 text-center align-middle">
                            ${statusCell}
                        </td>

                        <td class="px-4 py-4 text-center align-middle">
                            ${actionCell}
                        </td>
                    </tr>
                `;

                tbody.insertAdjacentHTML(
                    'beforeend',
                    trHtml
                );

                currentJournalId =
                    row.journal_id;
            });
        })
        .catch(err => {

            console.error(
                'Gagal memuat jurnal:',
                err
            );

            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center">
                        <div class="flex items-center justify-center gap-2 text-sm text-red-600">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M12 9v4M12 17h.01"/>
                                <path d="m10.3 3.8-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3.2l-8-14a2 2 0 0 0-3.4 0Z"/>
                            </svg>
                            Gagal memuat data transaksi dari server.
                        </div>
                    </td>
                </tr>
            `;
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Journal
    |--------------------------------------------------------------------------
    */
    function openEditJournalModal(id) {

        const form =
            document.getElementById('formEditJurnal');

        if (!form) return;

        form.action =
            `/general_journal/${id}`;

        const container =
            document.getElementById('editEntriesRowContainer');

        container.innerHTML = `
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" class="stroke-zinc-200" stroke-width="3"/>
                            <path d="M21 12a9 9 0 0 0-9-9" class="stroke-zinc-900" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        Mengambil rincian data...
                    </div>
                </td>
            </tr>
        `;

        openJournalModal('modalEditJurnal');

        fetch(`/general_journal/detail/${id}`)
            .then(res => {

                if (!res.ok) {
                    throw new Error(
                        'Gagal memuat data dari server'
                    );
                }

                return res.json();
            })
            .then(data => {

                document.getElementById(
                    'edit_tgl_transaksi'
                ).value =
                    data.header.tgl_transaksi;

                document.getElementById(
                    'edit_no_invoice'
                ).value =
                    data.header.no_invoice || '';

                document.getElementById(
                    'edit_keterangan'
                ).value =
                    data.header.keterangan || '';

                document.getElementById(
                    'edit_status'
                ).value =
                    data.header.status;

                document.getElementById(
                    'edit_cash_flow'
                ).value =
                    data.header.arus;

                container.innerHTML = '';

                editEntryRowIndex = 0;

                const masterSelect =
                    document.querySelector('.select-coa-target');

                const optionsHtml =
                    masterSelect
                        ? masterSelect.innerHTML
                        : '';

                (data.items || []).forEach(detail => {

                    const debit =
                        parseFloat(detail.debit) || 0;

                    const kredit =
                        parseFloat(detail.kredit) || 0;

                    const trHtml = `
                        <tr class="edit-entry-row">
                            <td class="p-3">
                                <select
                                    name="items[${editEntryRowIndex}][chart_of_account_id]"
                                    class="select-edit-coa-target block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    required
                                >
                                    ${optionsHtml}
                                </select>
                            </td>

                            <td class="p-3">
                                <input
                                    type="text"
                                    name="items[${editEntryRowIndex}][keterangan_item]"
                                    class="block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    value="${detail.keterangan_item || ''}"
                                    placeholder="Keterangan baris..."
                                >
                            </td>

                            <td class="p-3">
                                <input
                                    type="number"
                                    name="items[${editEntryRowIndex}][debit]"
                                    class="input-edit-nominal input-edit-debit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    value="${debit}"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                            </td>

                            <td class="p-3">
                                <input
                                    type="number"
                                    name="items[${editEntryRowIndex}][kredit]"
                                    class="input-edit-nominal input-edit-kredit block w-full rounded-lg border border-zinc-200 px-3 py-2 text-right text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                    value="${kredit}"
                                    min="0"
                                    step="0.01"
                                    required
                                >
                            </td>

                            <td class="p-3 text-center">
                                <button
                                    type="button"
                                    class="row-delete-btn inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 transition hover:bg-red-100"
                                    onclick="this.closest('tr').remove(); calculateEditTotals();"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    `;

                    container.insertAdjacentHTML(
                        'beforeend',
                        trHtml
                    );

                    const currentSelect =
                        container.querySelector(
                            `select[name="items[${editEntryRowIndex}][chart_of_account_id]"]`
                        );

                    if (currentSelect) {
                        currentSelect.value =
                            detail.chart_of_account_id;
                    }

                    editEntryRowIndex++;
                });

                calculateEditTotals();
            })
            .catch(err => {

                container.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-red-600">
                            ${err.message}
                        </td>
                    </tr>
                `;
            });
    }


    /*
    |--------------------------------------------------------------------------
    | Void Journal
    |--------------------------------------------------------------------------
    */
    function voidJournal(id) {

        const form =
            document.getElementById('formHapusJurnal');

        if (!form) return;

        form.action =
            `/general_journal/${id}`;

        document.getElementById(
            'hapus_no_jurnal'
        ).innerText = 'Memuat...';

        document.getElementById(
            'hapus_keterangan'
        ).innerText =
            'Memuat data transaksi...';

        openJournalModal('modalHapusJurnal');

        fetch(`/general_journal/${id}`)
            .then(res => {

                if (!res.ok) {
                    throw new Error();
                }

                return res.json();
            })
            .then(data => {

                document.getElementById(
                    'hapus_no_jurnal'
                ).innerText =
                    data.header.no_jurnal;

                document.getElementById(
                    'hapus_keterangan'
                ).innerText =
                    data.header.keterangan;
            })
            .catch(() => {

                document.getElementById(
                    'hapus_no_jurnal'
                ).innerText =
                    'Error';

                document.getElementById(
                    'hapus_keterangan'
                ).innerText =
                    'Gagal memuat detail ringkasan transaksi.';
            });
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Submit
    |--------------------------------------------------------------------------
    */
    document
        .getElementById('formHapusJurnal')
        .addEventListener('submit', function (e) {

            e.preventDefault();

            const form =
                this;

            const actionUrl =
                form.action;

            const submitButton =
                form.querySelector(
                    'button[type="submit"]'
                );

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50');
            }

            fetch(actionUrl, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest',
                    'Accept':
                        'application/json'
                }
            })
            .then(res => {

                if (!res.ok) {
                    throw new Error(
                        'Gagal memproses penghapusan.'
                    );
                }

                return res.json();
            })
            .then(data => {

                closeJournalModal(
                    'modalHapusJurnal'
                );

                if (data.status === 'success') {

                    alert(data.message);

                    loadJournalData();

                } else {

                    alert(
                        'Gagal: ' +
                        data.message
                    );
                }
            })
            .catch(err => {

                console.error(err);

                alert(
                    'Terjadi kesalahan sistem saat memproses penghapusan.'
                );
            })
            .finally(() => {

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50');
                }
            });
        });


    /*
    |--------------------------------------------------------------------------
    | Period
    |--------------------------------------------------------------------------
    */
    function isPeriodLocked(dateString) {

        const txDate =
            new Date(dateString);

        const nowDate =
            new Date();

        const currentYear =
            nowDate.getFullYear();

        const currentMonth =
            nowDate.getMonth();

        const txYear =
            txDate.getFullYear();

        const txMonth =
            txDate.getMonth();

        if (txYear < currentYear) {
            return true;
        }

        if (
            txYear === currentYear &&
            txMonth < currentMonth
        ) {
            return true;
        }

        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Update Date From Period
    |--------------------------------------------------------------------------
    */
    function updateDatesFromPeriod() {

        const periodVal =
            document.getElementById(
                'filter_period'
            ).value;

        if (periodVal) {

            const [year, month] =
                periodVal.split('-');

            const startDate =
                `${year}-${month}-01`;

            const lastDay =
                new Date(
                    year,
                    month,
                    0
                ).getDate();

            const endDate =
                `${year}-${month}-${String(lastDay).padStart(2, '0')}`;

            document.getElementById(
                'filter_start_date'
            ).value =
                startDate;

            document.getElementById(
                'filter_end_date'
            ).value =
                endDate;
        }

        loadJournalData();
    }


    /*
    |--------------------------------------------------------------------------
    | Reset Filter
    |--------------------------------------------------------------------------
    */
    function resetJournalFilter() {

        const now =
            new Date();

        const currentYear =
            now.getFullYear();

        const currentMonth =
            String(
                now.getMonth() + 1
            ).padStart(2, '0');

        const lastDay =
            new Date(
                currentYear,
                currentMonth,
                0
            ).getDate();

        document.getElementById(
            'filter_period'
        ).value =
            `${currentYear}-${currentMonth}`;

        document.getElementById(
            'filter_start_date'
        ).value =
            `${currentYear}-${currentMonth}-01`;

        document.getElementById(
            'filter_end_date'
        ).value =
            `${currentYear}-${currentMonth}-${lastDay}`;

        document.getElementById(
            'filter_search'
        ).value =
            '';

        document.getElementById(
            'filter_status'
        ).value =
            '';

        loadJournalData();
    }
</script>

@endsection