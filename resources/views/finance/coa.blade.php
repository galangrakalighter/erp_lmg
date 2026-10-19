@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- =========================================================
             PAGE HEADER
        ========================================================== --}}
        <div class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-px w-8 bg-zinc-950"></span>
                    <span class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                        Finance / Accounting
                    </span>
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    Daftar Akun
                </h1>

                <p class="mt-2 text-sm leading-6 text-zinc-500">
                    Kelola Chart of Accounts dan saldo pembuka perusahaan.
                </p>
            </div>

            <button
                type="button"
                onclick="openTambahModal()"
                class="inline-flex w-fit items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>

                Tambah Akun Baru
            </button>
        </div>


        {{-- =========================================================
             MAIN CARD
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)]">

            {{-- CARD HEADER --}}
            <div class="border-b border-zinc-200 px-5 py-5 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v15H6.5A2.5 2.5 0 0 1 4 16.5v-10Z"/>
                            <path stroke-linecap="round" d="M4 8h16M8 12h7M8 15h5"/>
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950">
                            Chart of Accounts
                        </h2>

                        <p class="mt-0.5 text-xs text-zinc-500">
                            Daftar akun keuangan perusahaan
                        </p>
                    </div>
                </div>
            </div>


            {{-- =====================================================
                 FILTER AREA
            ====================================================== --}}
            <div class="border-b border-zinc-200 bg-zinc-50/70 px-5 py-5 sm:px-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                    {{-- Search --}}
                    <div class="xl:col-span-1">
                        <label
                            for="filterSearch"
                            class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-500"
                        >
                            Cari Akun
                        </label>

                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400">
                                <circle cx="11" cy="11" r="7"/>
                                <path stroke-linecap="round" d="m20 20-4-4"/>
                            </svg>

                            <input
                                type="text"
                                id="filterSearch"
                                placeholder="Kode atau nama akun..."
                                onkeyup="loadCoaData()"
                                class="block w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-9 pr-3 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>
                    </div>


                    {{-- Type --}}
                    <div>
                        <label
                            for="filterType"
                            class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-500"
                        >
                            Tipe Akun
                        </label>

                        <select
                            id="filterType"
                            onchange="loadCoaData()"
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="">- Semua Tipe -</option>
                            <option value="K">Kredit</option>
                            <option value="D">Debit</option>
                        </select>
                    </div>


                    {{-- Status --}}
                    <div>
                        <label
                            for="filterStatus"
                            class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-500"
                        >
                            Status
                        </label>

                        <select
                            id="filterStatus"
                            onchange="loadCoaData()"
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="">- Semua Status -</option>
                            <option value="aktif">Aktif</option>
                            <option value="non_aktif">Non-Aktif</option>
                        </select>
                    </div>


                    {{-- Category --}}
                    <div>
                        <label
                            for="filterCategory"
                            class="mb-1.5 block text-[11px] font-semibold uppercase tracking-[0.12em] text-zinc-500"
                        >
                            Kategori
                        </label>

                        <select
                            id="filterCategory"
                            onchange="loadCoaData()"
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="">- Semua Kategori -</option>
                            <option value="aset">Aset</option>
                            <option value="kewajiban">Kewajiban</option>
                            <option value="ekuitas">Ekuitas</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="beban">Beban</option>
                        </select>
                    </div>


                    {{-- Reset --}}
                    <div class="flex items-end">
                        <button
                            type="button"
                            onclick="resetFilter()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white focus:outline-none focus:ring-2 focus:ring-zinc-300"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 4v5h5"/>
                            </svg>

                            Reset Filter
                        </button>
                    </div>

                </div>
            </div>


            {{-- =====================================================
                 TABLE
            ====================================================== --}}
            <div class="p-5 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200">
                                <th class="w-[12%] px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Kode Akun
                                </th>

                                <th class="w-[28%] px-4 py-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Nama Akun
                                </th>

                                <th class="w-[12%] px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Tipe Akun
                                </th>

                                <th class="w-[15%] px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Saldo Awal
                                </th>

                                <th class="w-[10%] px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Status
                                </th>

                                <th class="w-[13%] px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Kategori
                                </th>

                                <th class="w-[10%] px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            id="coaTableBody"
                            class="divide-y divide-zinc-100"
                        >
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             class="h-5 w-5 animate-spin text-zinc-400">
                                            <path stroke-linecap="round"
                                                  d="M12 3a9 9 0 1 1-9 9"/>
                                        </svg>

                                        Memuat data...
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="coaPagination" class="mt-5 hidden flex-col gap-4 border-t border-zinc-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                        <span id="coaPaginationInfo">Menampilkan 0 data</span>
                        <label class="flex items-center gap-2">
                            <span>Baris</span>
                            <select id="coaPageSize" onchange="changeCoaPageSize(this.value)" class="rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-xs font-semibold text-zinc-700 focus:border-zinc-950 focus:ring-zinc-950">
                                <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
                            </select>
                        </label>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                        <button id="coaPreviousPage" type="button" onclick="changeCoaPage(-1)" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-600 transition hover:border-zinc-950 hover:text-zinc-950 disabled:cursor-not-allowed disabled:opacity-40">Sebelumnya</button>
                        <div id="coaPageNumbers" class="flex flex-wrap items-center gap-1.5"></div>
                        <button id="coaNextPage" type="button" onclick="changeCoaPage(1)" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-600 transition hover:border-zinc-950 hover:text-zinc-950 disabled:cursor-not-allowed disabled:opacity-40">Berikutnya</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- =========================================================
     MODAL TAMBAH
========================================================= --}}
<div
    id="tambahModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="tambahModal"
    >
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="tambahModalLabel"
        >

            {{-- Header --}}
            <div class="border-b border-zinc-800 bg-zinc-950 px-5 py-5 sm:px-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            Chart of Accounts
                        </div>

                        <h2
                            id="tambahModalLabel"
                            class="text-lg font-semibold tracking-tight text-white"
                        >
                            Tambah Akun Baru
                        </h2>
                    </div>

                    <button
                        type="button"
                        data-modal-close="tambahModal"
                        class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-white/10 hover:text-white focus:outline-none"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>


            {{-- Form --}}
            <form action="{{ route('finance_coa.store', $id) }}" method="POST">
                @csrf

                <input
                    type="hidden"
                    name="perusahaan"
                    value="{{ $id }}"
                >

                <div class="max-h-[70vh] space-y-5 overflow-y-auto px-5 py-6 sm:px-6">

                    {{-- Kode --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Kode Akun
                        </label>

                        <input
                            type="text"
                            name="kode"
                            placeholder="Contoh: 11001"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Nama Akun
                        </label>

                        <input
                            type="text"
                            name="nama_akun"
                            placeholder="Contoh: Kas Utama"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Tipe Akun
                        </label>

                        <select
                            name="type"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="">-- Pilih Tipe --</option>
                            <option value="K">Kredit</option>
                            <option value="D">Debit</option>
                        </select>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Kategori
                        </label>

                        <select
                            name="category"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            <option value="aset">Aset</option>
                            <option value="kewajiban">Kewajiban</option>
                            <option value="ekuitas">Ekuitas</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="beban">Beban</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Status Akun
                        </label>

                        <select
                            name="is_active"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>


                    {{-- Opening Balance --}}
                    <div class="border-t border-zinc-200 pt-5">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     class="h-4 w-4">
                                    <rect width="16" height="12" x="4" y="6" rx="2"/>
                                    <path stroke-linecap="round" d="M8 10h8M8 14h3"/>
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-zinc-950">
                                    Saldo Awal
                                </h3>

                                <p class="text-xs text-zinc-500">
                                    Opsional
                                </p>
                            </div>
                        </div>

                        {{-- Periode --}}
                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-zinc-900">
                                Periode Saldo Pembuka
                            </label>

                            <input
                                type="month"
                                name="opening_periode"
                                value="{{ date('Y-m') }}"
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >

                            <p class="mt-1.5 text-xs text-zinc-500">
                                Bulan pertama kali akun ini memiliki saldo murni.
                            </p>
                        </div>

                        {{-- Saldo --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-900">
                                Nominal Saldo Pembuka (Rp)
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                name="opening_saldo"
                                value="0"
                                placeholder="0.00"
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-right text-sm font-semibold text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>
                    </div>

                </div>


                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                    <button
                        type="button"
                        data-modal-close="tambahModal"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
                    >
                        Simpan Akun
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- =========================================================
     MODAL EDIT
========================================================= --}}
<div
    id="editModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="editModal"
    >
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="editModalLabel"
        >

            <div class="border-b border-zinc-800 bg-zinc-950 px-5 py-5 sm:px-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            Chart of Accounts
                        </div>

                        <h2
                            id="editModalLabel"
                            class="text-lg font-semibold tracking-tight text-white"
                        >
                            Ubah Data Akun
                        </h2>
                    </div>

                    <button
                        type="button"
                        data-modal-close="editModal"
                        class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-white/10 hover:text-white focus:outline-none"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>


            <form id="formEditCoa" method="POST">
                @csrf
                @method('PUT')

                <input
                    type="hidden"
                    name="perusahaan"
                    value="{{ $id }}"
                >

                <div class="max-h-[70vh] space-y-5 overflow-y-auto px-5 py-6 sm:px-6">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Kode Akun
                        </label>

                        <input
                            type="text"
                            id="edit_kode"
                            name="kode"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Nama Akun
                        </label>

                        <input
                            type="text"
                            id="edit_nama_akun"
                            name="nama_akun"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Tipe Akun
                        </label>

                        <select
                            id="edit_type"
                            name="type"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="K">Kredit</option>
                            <option value="D">Debit</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Kategori
                        </label>

                        <select
                            name="category"
                            id="edit_category"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="aset">Aset</option>
                            <option value="kewajiban">Kewajiban</option>
                            <option value="ekuitas">Ekuitas</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="beban">Beban</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-900">
                            Status Akun
                        </label>

                        <select
                            name="is_active"
                            id="edit_is_active"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="1">Aktif</option>
                            <option value="0">Non-Aktif</option>
                        </select>
                    </div>


                    <div class="border-t border-zinc-200 pt-5">

                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-900">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     class="h-4 w-4">
                                    <rect width="16" height="12" x="4" y="6" rx="2"/>
                                    <path stroke-linecap="round" d="M8 10h8M8 14h3"/>
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-zinc-950">
                                    Saldo Awal Pembuka
                                </h3>

                                <p class="text-xs text-zinc-500">
                                    Informasi saldo awal akun
                                </p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-medium text-zinc-900">
                                Periode Saldo Pembuka
                            </label>

                            <input
                                type="month"
                                id="edit_opening_periode"
                                name="opening_periode"
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-zinc-900">
                                Nominal Saldo Pembuka (Rp)
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                id="edit_opening_saldo"
                                name="opening_saldo"
                                placeholder="0.00"
                                class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-right text-sm font-semibold text-zinc-950 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>
                    </div>

                </div>


                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                    <button
                        type="button"
                        data-modal-close="editModal"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- =========================================================
     MODAL DELETE
========================================================= --}}
<div
    id="deleteModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="deleteModal"
    >
        <div
            class="relative w-full max-w-md overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteModalLabel"
        >

            <div class="border-b border-zinc-200 px-5 py-5 sm:px-6">
                <div class="flex items-start justify-between gap-4">

                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 9v4m0 4h.01"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10.3 3.9 2.7 17.2A2 2 0 0 0 4.4 20h15.2a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                            </svg>
                        </div>

                        <div>
                            <h2
                                id="deleteModalLabel"
                                class="text-base font-semibold text-zinc-950"
                            >
                                Konfirmasi Hapus Akun
                            </h2>

                            <p class="mt-0.5 text-xs text-zinc-500">
                                Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        data-modal-close="deleteModal"
                        class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-900"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>


            <form id="formDeleteCoa" method="POST">
                @csrf
                @method('DELETE')

                <div class="px-5 py-6 sm:px-6">
                    <p class="text-sm leading-6 text-zinc-600">
                        Apakah Anda yakin ingin menghapus akun
                        <strong
                            id="delete_nama_akun"
                            class="font-semibold text-red-600"
                        ></strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                    <button
                        type="button"
                        data-modal-close="deleteModal"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 6h18M8 6V4h8v2m-9 0 1 15h8l1-15M10 11v6m4-6v6"/>
                        </svg>

                        Ya, Hapus Akun
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>


{{-- =========================================================
     SCRIPT
========================================================= --}}
<script>
    let coaCurrentPage = 1;
    let coaPageSize = 10;
    let coaTotalPages = 1;

    document.addEventListener("DOMContentLoaded", function () {
        loadCoaData();

        // Modal close
        document.addEventListener('click', function (event) {

            const closeButton = event.target.closest('[data-modal-close]');

            if (closeButton) {
                event.preventDefault();

                const modalId = closeButton.getAttribute('data-modal-close');

                closeModal(modalId);

                return;
            }

            const backdrop = event.target.closest('[data-modal-backdrop]');

            if (backdrop && event.target === backdrop) {
                closeModal(
                    backdrop.getAttribute('data-modal-backdrop')
                );
            }
        });

        // ESC close
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            const visibleModal = document.querySelector(
                '[id$="Modal"]:not(.hidden)'
            );

            if (visibleModal) {
                closeModal(visibleModal.id);
            }
        });
    });


    // =========================================================
    // MODAL HELPERS
    // =========================================================
    function openModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }


    function closeModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        const visibleModal = document.querySelector(
            '[id$="Modal"]:not(.hidden)'
        );

        if (!visibleModal) {
            document.body.classList.remove('overflow-hidden');
        }
    }


    function openTambahModal() {
        openModal('tambahModal');
    }


    // =========================================================
    // FORMAT RUPIAH
    // =========================================================
    function formatRupiah(val) {
        const num = parseFloat(val);

        if (isNaN(num)) return 'Rp. 0';

        return 'Rp. ' + num.toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }


    // =========================================================
    // ESCAPE HTML
    // =========================================================
    function escapeCoaHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }


    // =========================================================
    // LOAD COA DATA
    // =========================================================
    function loadCoaData(resetPage = true) {

        if (resetPage) coaCurrentPage = 1;

        const typeFilter =
            document.getElementById('filterType').value;

        const statusFilter =
            document.getElementById('filterStatus').value;

        const categoryFilter =
            document.getElementById('filterCategory').value;

        const searchFilter =
            document.getElementById('filterSearch').value;

        const tableBody =
            document.getElementById('coaTableBody');


        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500">
                    <div class="flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             class="h-4 w-4 animate-spin text-zinc-400">
                            <path stroke-linecap="round"
                                  d="M12 3a9 9 0 1 1-9 9"/>
                        </svg>

                        Memuat data...
                    </div>
                </td>
            </tr>
        `;


        let params = new URLSearchParams();

        if (typeFilter) {
            params.append('type', typeFilter);
        }

        if (statusFilter) {
            params.append('status', statusFilter);
        }

        if (categoryFilter) {
            params.append('category', categoryFilter);
        }

        if (searchFilter) {
            params.append('search', searchFilter);
        }


        let url =
            '{{ route("finance_coa.data", $id) }}?' +
            params.toString();


        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {

            if (!response.ok) {
                throw new Error(
                    'Network response was not ok'
                );
            }

            return response.json();
        })
        .then(res => {

            if (res.status === 'error') {
                throw new Error(res.message);
            }


            let accounts = [];

            if (Array.isArray(res)) {

                accounts = res;

            } else if (
                res &&
                Array.isArray(res.data)
            ) {

                accounts = res.data;

            } else if (
                res &&
                typeof res === 'object'
            ) {

                accounts = Object.values(res);
            }


            tableBody.innerHTML = '';


            if (accounts.length === 0) {

                document.getElementById('coaPagination').classList.add('hidden');

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="7"
                            class="px-4 py-12 text-center"
                        >
                            <div class="flex flex-col items-center">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-100 text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v15H6.5A2.5 2.5 0 0 1 4 16.5v-10Z"/>
                                        <path stroke-linecap="round" d="M8 12h7"/>
                                    </svg>
                                </div>

                                <p class="text-sm font-medium text-zinc-700">
                                    Belum ada data Chart of Account.
                                </p>

                                <p class="mt-1 text-xs text-zinc-400">
                                    Coba ubah filter atau tambahkan akun baru.
                                </p>
                            </div>
                        </td>
                    </tr>
                `;

                return;
            }

            coaTotalPages = Math.max(1, Math.ceil(accounts.length / coaPageSize));
            coaCurrentPage = Math.min(coaCurrentPage, coaTotalPages);
            const totalAccounts = accounts.length;
            const pageStart = (coaCurrentPage - 1) * coaPageSize;
            accounts = accounts.slice(pageStart, pageStart + coaPageSize);
            renderCoaPagination(totalAccounts, pageStart);


            const badges = {
                'D': {
                    label: 'Debit',
                    className: 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                },

                'K': {
                    label: 'Kredit',
                    className: 'bg-amber-50 text-amber-700 ring-amber-200'
                }
            };


            accounts.forEach(account => {

                if (!account || !account.kode) return;


                const badgeInfo =
                    badges[account.type] || {
                        label: account.type || '-',
                        className: 'bg-zinc-100 text-zinc-600 ring-zinc-200'
                    };


                const rawOpeningSaldo =
                    account.opening_saldo !== undefined
                        ? account.opening_saldo
                        : 0;


                const statusBadge = account.is_active
                    ? `
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    `
                    : `
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600 ring-1 ring-inset ring-zinc-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                            Non-Aktif
                        </span>
                    `;


                const safeKode =
                    escapeCoaHtml(account.kode);

                const safeNama =
                    escapeCoaHtml(account.nama_akun);

                const safeCategory =
                    escapeCoaHtml(account.category || '-');

                const safeType =
                    escapeCoaHtml(account.type || '');

                const safeOpeningPeriode =
                    escapeCoaHtml(account.opening_periode || '');

                const safeOpeningSaldo =
                    escapeCoaHtml(rawOpeningSaldo);


                const row = `
                    <tr class="transition-colors hover:bg-zinc-50">

                        {{-- Kode --}}
                        <td class="px-4 py-4 text-center align-middle">
                            <span class="font-mono text-xs font-semibold text-zinc-900">
                                ${safeKode}
                            </span>
                        </td>


                        {{-- Nama --}}
                        <td class="px-4 py-4 align-middle">
                            <div class="flex items-center gap-2.5">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-zinc-300"></span>

                                <span class="font-semibold text-zinc-900">
                                    ${safeNama}
                                </span>
                            </div>
                        </td>


                        {{-- Type --}}
                        <td class="px-4 py-4 text-center align-middle">
                            <span class="inline-flex min-w-[70px] items-center justify-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ${badgeInfo.className}">
                                ${badgeInfo.label}
                            </span>
                        </td>


                        {{-- Saldo --}}
                        <td class="px-4 py-4 text-right align-middle">
                            <span class="font-mono text-xs font-semibold text-zinc-900">
                                ${formatRupiah(rawOpeningSaldo)}
                            </span>
                        </td>


                        {{-- Status --}}
                        <td class="px-4 py-4 text-center align-middle">
                            ${statusBadge}
                        </td>


                        {{-- Category --}}
                        <td class="px-4 py-4 text-center align-middle">
                            <span class="text-xs font-medium capitalize text-zinc-500">
                                ${safeCategory}
                            </span>
                        </td>


                        {{-- Actions --}}
                        <td class="px-4 py-4 text-center align-middle">
                            <div class="flex items-center justify-center gap-2">

                                <button
                                    type="button"
                                    onclick="openEditModal(
                                        ${account.id},
                                        '${safeKode}',
                                        '${safeNama}',
                                        '${safeType}',
                                        ${account.is_active ? 'true' : 'false'},
                                        '${safeCategory === '-' ? '' : safeCategory}',
                                        '${safeOpeningPeriode}',
                                        '${safeOpeningSaldo}'
                                    )"
                                    title="Edit"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-300 bg-white text-zinc-600 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white focus:outline-none focus:ring-2 focus:ring-zinc-300"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         class="h-3.5 w-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m16.86 3.49 3.65 3.65M4 20l3.1-.62a2 2 0 0 0 1.04-.57L19.5 7.45a2.59 2.59 0 0 0-3.66-3.66L4.48 15.5a2 2 0 0 0-.57 1.04L3.3 19.64A.3.3 0 0 0 4 20Z"/>
                                    </svg>
                                </button>


                                <button
                                    type="button"
                                    onclick="openDeleteModal(
                                        ${account.id},
                                        '${safeNama}'
                                    )"
                                    title="Hapus"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-200"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         class="h-3.5 w-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 6h18M8 6V4h8v2m-9 0 1 15h8l1-15M10 11v6m4-6v6"/>
                                    </svg>
                                </button>

                            </div>
                        </td>

                    </tr>
                `;


                tableBody.insertAdjacentHTML(
                    'beforeend',
                    row
                );
            });

        })
        .catch(error => {

            console.error('Error:', error);

            document.getElementById('coaPagination').classList.add('hidden');

            tableBody.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="px-4 py-10 text-center"
                    >
                        <div class="flex flex-col items-center">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 9v4m0 4h.01"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M10.3 3.9 2.7 17.2A2 2 0 0 0 4.4 20h15.2a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                                </svg>
                            </div>

                            <p class="text-sm font-semibold text-red-600">
                                Gagal memuat data
                            </p>

                            <p class="mt-1 max-w-md text-xs text-zinc-500">
                                ${escapeCoaHtml(error.message)}
                            </p>
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    function renderCoaPagination(total, pageStart) {
        const container = document.getElementById('coaPagination');
        const numbers = document.getElementById('coaPageNumbers');
        const end = Math.min(pageStart + coaPageSize, total);
        container.classList.remove('hidden');
        container.classList.add('flex');
        document.getElementById('coaPaginationInfo').textContent = `Menampilkan ${pageStart + 1}–${end} dari ${total} akun`;
        document.getElementById('coaPreviousPage').disabled = coaCurrentPage <= 1;
        document.getElementById('coaNextPage').disabled = coaCurrentPage >= coaTotalPages;

        const pages = [];
        const from = Math.max(1, coaCurrentPage - 2);
        const to = Math.min(coaTotalPages, coaCurrentPage + 2);
        if (from > 1) pages.push(1);
        if (from > 2) pages.push('…');
        for (let page = from; page <= to; page++) pages.push(page);
        if (to < coaTotalPages - 1) pages.push('…');
        if (to < coaTotalPages) pages.push(coaTotalPages);

        numbers.innerHTML = pages.map(page => page === '…'
            ? '<span class="px-1 text-xs text-zinc-400">…</span>'
            : `<button type="button" onclick="goToCoaPage(${page})" class="h-8 min-w-8 rounded-lg px-2 text-xs font-bold transition ${page === coaCurrentPage ? 'bg-zinc-950 text-white' : 'border border-zinc-300 bg-white text-zinc-600 hover:border-zinc-950'}">${page}</button>`
        ).join('');
    }

    function goToCoaPage(page) {
        if (page < 1 || page > coaTotalPages || page === coaCurrentPage) return;
        coaCurrentPage = page;
        loadCoaData(false);
    }

    function changeCoaPage(direction) {
        goToCoaPage(coaCurrentPage + direction);
    }

    function changeCoaPageSize(value) {
        coaPageSize = Number(value) || 10;
        coaCurrentPage = 1;
        loadCoaData(false);
    }


    // =========================================================
    // RESET FILTER
    // =========================================================
    function resetFilter() {

        document.getElementById('filterType').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterSearch').value = '';

        loadCoaData();
    }


    // =========================================================
    // EDIT MODAL
    // =========================================================
    function openEditModal(
        id,
        kode,
        nama_akun,
        type,
        isActive,
        category,
        openingPeriode,
        openingSaldo
    ) {

        const formEdit =
            document.getElementById('formEditCoa');

        if (!formEdit) return;


        formEdit.action =
            `/finance_coa/${id}`;


        document.getElementById('edit_kode').value =
            kode;

        document.getElementById('edit_nama_akun').value =
            nama_akun;

        document.getElementById('edit_type').value =
            type;

        document.getElementById('edit_category').value =
            category;

        document.getElementById('edit_is_active').value =
            isActive ? '1' : '0';

        document.getElementById('edit_opening_periode').value =
            openingPeriode;

        document.getElementById('edit_opening_saldo').value =
            openingSaldo;


        openModal('editModal');
    }


    // =========================================================
    // DELETE MODAL
    // =========================================================
    function openDeleteModal(id, nama_akun) {

        const formDelete =
            document.getElementById('formDeleteCoa');

        if (!formDelete) return;


        formDelete.action =
            `/finance_coa/${id}`;


        document.getElementById('delete_nama_akun').textContent =
            nama_akun;


        openModal('deleteModal');
    }
</script>

@endsection
