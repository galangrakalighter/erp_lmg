@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- Page Header --}}
        <div class="relative mb-6 flex flex-col gap-4 overflow-hidden rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:flex-row sm:items-end sm:justify-between sm:px-9">
            <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>
            <div>
                <p class="relative mb-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-lime-300">
                    Lighter Media Group · Finance
                </p>

                <h1 class="relative text-2xl font-semibold tracking-tight text-white sm:text-3xl">
                    Daftar Invoice & Piutang
                </h1>

                <p class="relative mt-2 text-sm text-zinc-400">
                    Kelola invoice, piutang, pembayaran, dan status tagihan client.
                </p>
            </div>

            <button
                type="button"
                data-modal-open="invoiceModal"
                onclick="resetForm()"
                class="relative inline-flex items-center justify-center gap-2 rounded-xl bg-lime-300 px-4 py-2.5 text-sm font-bold text-zinc-950 shadow-sm transition hover:bg-lime-200 focus:outline-none focus:ring-2 focus:ring-lime-300 focus:ring-offset-2 focus:ring-offset-zinc-950"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                </svg>
                Tambah Invoice
            </button>
        </div>


        {{-- Invoice Table Card --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            <div class="flex flex-col gap-2 border-b border-zinc-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.12em] text-zinc-950">
                        Tabel Data Piutang
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        Daftar seluruh invoice dan status pembayaran.
                    </p>
                </div>

                <span class="inline-flex w-fit items-center rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1 text-xs font-medium text-zinc-600">
                    Invoice & Receivables
                </span>
            </div>

            <div class="flex flex-col gap-3 border-b border-zinc-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <label class="relative block w-full sm:max-w-sm">
                    <span class="sr-only">Cari invoice</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input id="invoiceSearch" type="search" placeholder="Cari client, invoice, atau status..." class="w-full rounded-xl border border-zinc-200 bg-zinc-50 py-2.5 pl-10 pr-4 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-400 focus:bg-white focus:ring-2 focus:ring-zinc-100">
                </label>
                <p id="invoiceSummary" class="text-xs font-medium text-zinc-500">Memuat data...</p>
            </div>

            <div class="p-0">
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[1000px] text-left text-sm text-zinc-700"
                        id="invoiceTable"
                        width="100%"
                    >
                        <thead class="bg-zinc-950 text-[11px] font-semibold uppercase tracking-wider text-white">
                            <tr>
                                <th class="px-4 py-3.5">Nama Client</th>
                                <th class="px-4 py-3.5">No Invoice</th>
                                <th class="px-4 py-3.5">Jatuh Tempo</th>
                                <th class="px-4 py-3.5 text-right">Total Tagihan</th>
                                <th class="px-4 py-3.5 text-right">Sisa Piutang</th>
                                <th class="px-4 py-3.5">Status</th>
                                <th class="px-4 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody id="invoiceTableBody" class="divide-y divide-zinc-100">
                            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-zinc-500">Memuat data invoice...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="flex items-center justify-between gap-3 border-t border-zinc-200 px-5 py-4">
                <button id="invoicePrev" type="button" class="rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40">Sebelumnya</button>
                <span id="invoicePageInfo" class="text-xs font-medium text-zinc-500">Halaman 1</span>
                <button id="invoiceNext" type="button" class="rounded-lg border border-zinc-200 px-3 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 disabled:cursor-not-allowed disabled:opacity-40">Berikutnya</button>
            </div>
        </div>

    </div>
</div>


{{-- ============================================================
     MODAL : TAMBAH / EDIT INVOICE
============================================================ --}}
<div
    id="invoiceModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="invoiceModalTitle"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <form
                id="invoiceForm"
                action="/finance/invoice/store"
                method="POST"
            >
                @csrf

                <div id="methodField"></div>

                {{-- JSON layanan --}}
                <input
                    type="hidden"
                    name="items_json"
                    id="items_json"
                >

                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-950 px-5 py-4 text-white">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-400">
                            Finance
                        </p>

                        <h2
                            id="invoiceModalTitle"
                            class="mt-1 text-lg font-semibold"
                        >
                            Form Invoice
                        </h2>
                    </div>

                    <button
                        type="button"
                        data-modal-close
                        aria-label="Tutup"
                        class="rounded-lg p-2 text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>


                {{-- Modal Body --}}
                <div class="max-h-[75vh] overflow-y-auto px-5 py-6 sm:px-6">

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        {{-- Customer --}}
                        <div>
                            <label
                                for="customer_id"
                                class="mb-1.5 block text-sm font-semibold text-zinc-800"
                            >
                                Customer
                            </label>

                            <select
                                name="customer_id"
                                id="customer_id"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                required
                            >
                                <option value="">Pilih Customer</option>

                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        {{-- Akun --}}
                        <div>
                            <label
                                for="coa_id"
                                class="mb-1.5 block text-sm font-semibold text-zinc-800"
                            >
                                Akun
                            </label>

                            <select
                                name="coa"
                                id="coa_id"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                required
                            >
                                <option value="">Pilih Akun</option>

                                @foreach($accountPiutang as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        {{-- No Invoice --}}
                        <div class="sm:col-span-2">
                            <label
                                for="no_invoice"
                                class="mb-1.5 block text-sm font-semibold text-zinc-800"
                            >
                                No Invoice
                            </label>

                            <input
                                type="text"
                                name="no_invoice"
                                id="no_invoice"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-100 px-3.5 py-2.5 text-sm text-zinc-500 outline-none"
                                placeholder="Akan digenerate otomatis oleh sistem"
                                readonly
                            >
                        </div>

                    </div>


                    {{-- Dynamic Items --}}
                    <div class="mt-6">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-950">
                                    Rincian Layanan & Harga
                                </h3>

                                <p class="mt-1 text-xs text-zinc-500">
                                    Tambahkan layanan yang masuk ke invoice.
                                </p>
                            </div>

                            <button
                                type="button"
                                id="addRowBtn"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-zinc-800"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
                                </svg>
                                Tambah
                            </button>
                        </div>


                        <div
                            id="itemRowsContainer"
                            class="space-y-3"
                        >
                            <div class="item-row grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 sm:grid-cols-[1fr_220px_44px]">

                                <div>
                                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                                        Nama Layanan
                                    </label>

                                    <input
                                        type="text"
                                        class="item-desc w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                        placeholder="Nama Layanan"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                                        Harga
                                    </label>

                                    <input
                                        type="text"
                                        class="item-price w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                        placeholder="Harga (Rp)"
                                        required
                                    >

                                    <input
                                        type="hidden"
                                        class="item-price-raw"
                                        value="0"
                                    >
                                </div>

                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        class="remove-row inline-flex h-[42px] w-full items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 sm:w-[42px]"
                                        disabled
                                        title="Hapus"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- Total --}}
                    <div class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <label
                            for="total_amount_display"
                            class="mb-1.5 block text-sm font-semibold text-zinc-800"
                        >
                            Total Tagihan
                        </label>

                        <input
                            type="text"
                            id="total_amount_display"
                            class="w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-3 text-lg font-semibold text-zinc-950 outline-none"
                            readonly
                            placeholder="Rp 0"
                        >

                        <input
                            type="hidden"
                            name="total_amount"
                            id="total_amount"
                            required
                        >
                    </div>


                    {{-- Due Date + Keterangan --}}
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label
                                for="due_date"
                                class="mb-1.5 block text-sm font-semibold text-zinc-800"
                            >
                                Jatuh Tempo
                            </label>

                            <input
                                type="date"
                                name="due_date"
                                id="due_date"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                required
                            >
                        </div>

                        <div>
                            <label
                                for="keterangan"
                                class="mb-1.5 block text-sm font-semibold text-zinc-800"
                            >
                                Keterangan
                            </label>

                            <textarea
                                id="keterangan"
                                name="keterangan"
                                rows="2"
                                class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            ></textarea>
                        </div>

                    </div>

                </div>


                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">

                    <button
                        type="button"
                        data-modal-close
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12.5 9.5 17 19 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Simpan Data
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL : PEMBAYARAN
============================================================ --}}
<div
    id="paymentModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="paymentModalTitle"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <form
                id="paymentForm"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 bg-zinc-950 px-5 py-4 text-white">

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-400">
                            Receivable
                        </p>

                        <h2
                            id="paymentModalTitle"
                            class="mt-1 text-lg font-semibold"
                        >
                            Pembayaran:
                            <span id="modalNoInvoice" class="text-zinc-300"></span>
                        </h2>
                    </div>

                    <button
                        type="button"
                        data-modal-close
                        aria-label="Tutup"
                        class="rounded-lg p-2 text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
                        </svg>
                    </button>

                </div>


                {{-- Body --}}
                <div class="space-y-5 px-5 py-6 sm:px-6">

                    {{-- Akun --}}
                    <div>
                        <label
                            for="payment_coa_id"
                            class="mb-1.5 block text-sm font-semibold text-zinc-800"
                        >
                            Akun
                        </label>

                        <select
                            name="coa"
                            id="payment_coa_id"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            required
                        >
                            <option value="">Pilih Akun</option>

                            @foreach($accountPendapatan as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Amount --}}
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-zinc-800"
                        >
                            Jumlah Bayar
                        </label>

                        <input
                            type="number"
                            name="amount"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            required
                        >
                    </div>


                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        {{-- Payment Date --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-zinc-800">
                                Tanggal Bayar
                            </label>

                            <input
                                type="date"
                                name="payment_date"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                                value="{{ date('Y-m-d') }}"
                                required
                            >
                        </div>


                        {{-- Method --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-zinc-800">
                                Metode Pembayaran
                            </label>

                            <select
                                name="method"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="Transfer">Transfer</option>
                                <option value="Tunai">Tunai</option>
                            </select>
                        </div>

                    </div>


                    {{-- Keterangan --}}
                    <div>
                        <label
                            for="payment_keterangan"
                            class="mb-1.5 block text-sm font-semibold text-zinc-800"
                        >
                            Keterangan
                        </label>

                        <textarea
                            id="payment_keterangan"
                            name="keterangan"
                            rows="4"
                            class="w-full resize-none rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        ></textarea>
                    </div>


                    {{-- File --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-zinc-800">
                            Bukti Pembayaran
                        </label>

                        <input
                            type="file"
                            name="file_path"
                            accept="image/*,application/pdf"
                            class="block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-700 file:mr-4 file:border-0 file:border-r file:border-zinc-300 file:bg-zinc-50 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-zinc-700"
                        >

                        <p class="mt-1.5 text-xs text-zinc-500">
                            Format: JPG, PNG, atau PDF (Max 2MB)
                        </p>
                    </div>

                </div>


                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">

                    <button
                        type="button"
                        data-modal-close
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12.5 9.5 17 19 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Konfirmasi Bayar
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


{{-- ============================================================
     PAGE SCRIPT
============================================================ --}}
<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Modal Helper
    |--------------------------------------------------------------------------
    */

    function openModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        const openModals = document.querySelectorAll('[id$="Modal"]:not(.hidden)');

        if (!openModals.length) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    document.querySelectorAll('[data-modal-open]').forEach(button => {
        button.addEventListener('click', function () {
            openModal(this.dataset.modalOpen);
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', function () {
            closeModal(this.closest('[id$="Modal"]'));
        });
    });

    document.querySelectorAll('[data-modal-backdrop]').forEach(backdrop => {
        backdrop.addEventListener('click', function () {
            const modal = this.closest('[id$="Modal"]');

            if (!modal) return;

            if (modal.dataset.modalStatic === 'true') {
                return;
            }

            closeModal(modal);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('[id$="Modal"]:not(.hidden)').forEach(modal => {

            if (modal.dataset.modalStatic === 'true') {
                return;
            }

            closeModal(modal);
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Reset Invoice Form
    |--------------------------------------------------------------------------
    */

    window.resetForm = function () {

        $('#invoiceForm').attr(
            'action',
            '{{ route("invoice.store", $id) }}'
        );

        $('#methodField').html('');

        $('#no_invoice').val('');
        $('#total_amount').val('');
        $('#total_amount_display').val('');

        /*
         * Reset dynamic rows
         */
        const container = document.getElementById('itemRowsContainer');

        if (container) {

            const rows = container.querySelectorAll('.item-row');

            rows.forEach((row, index) => {
                if (index > 0) {
                    row.remove();
                }
            });

            const firstRow = container.querySelector('.item-row');

            if (firstRow) {
                const desc = firstRow.querySelector('.item-desc');
                const price = firstRow.querySelector('.item-price');
                const raw = firstRow.querySelector('.item-price-raw');

                if (desc) desc.value = '';
                if (price) price.value = '';
                if (raw) raw.value = '0';
            }
        }

        updateRemoveButtons();
        calculateTotal();
    };


    /*
    |--------------------------------------------------------------------------
    | Tabel Invoice (tanpa plugin DataTables)
    |--------------------------------------------------------------------------
    */

    const invoiceState = {
        rows: [],
        page: 1,
        perPage: 10,
        query: ''
    };

    function escapeInvoiceHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function invoiceStatusBadge(status) {
        const normalized = String(status || '').toLowerCase();
        const classes = normalized === 'paid'
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
            : normalized === 'partial'
                ? 'bg-amber-50 text-amber-700 border-amber-200'
                : 'bg-red-50 text-red-700 border-red-200';

        return `<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide ${classes}">${escapeInvoiceHtml(normalized || 'unpaid')}</span>`;
    }

    function invoiceActions(row) {
        const id = Number(row.id);
        const invoiceNumber = escapeInvoiceHtml(row.no_invoice);
        const payButton = row.status !== 'paid' ? `
            <button type="button" data-invoice-pay="${id}" data-invoice-number="${invoiceNumber}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white" title="Bayar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7h18M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M16 14h2"/></svg>
            </button>` : '';

        return `<div class="flex items-center justify-center gap-1.5">
            ${payButton}
            <a href="/invoice/pdf/${id}" target="_blank" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600" title="PDF">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5"/><path d="M9 15h6M9 18h4"/></svg>
            </a>
            <button type="button" data-invoice-edit="${id}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600" title="Edit">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
            </button>
            <form action="/invoice/${id}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 transition hover:bg-red-50" title="Hapus">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg>
                </button>
            </form>
        </div>`;
    }

    function renderInvoiceTable() {
        const body = document.getElementById('invoiceTableBody');
        const query = invoiceState.query.toLowerCase();
        const filtered = invoiceState.rows.filter(row =>
            [row.customer_name, row.no_invoice, row.due_date, row.status]
                .some(value => String(value ?? '').toLowerCase().includes(query))
        );
        const totalPages = Math.max(1, Math.ceil(filtered.length / invoiceState.perPage));
        invoiceState.page = Math.min(invoiceState.page, totalPages);
        const start = (invoiceState.page - 1) * invoiceState.perPage;
        const visibleRows = filtered.slice(start, start + invoiceState.perPage);

        body.innerHTML = visibleRows.length ? visibleRows.map(row => `
            <tr class="transition hover:bg-zinc-50/80">
                <td class="px-4 py-4 font-semibold text-zinc-900">${escapeInvoiceHtml(row.customer_name)}</td>
                <td class="px-4 py-4 font-mono text-xs text-zinc-700">${escapeInvoiceHtml(row.no_invoice)}</td>
                <td class="px-4 py-4 whitespace-nowrap">${escapeInvoiceHtml(row.due_date)}</td>
                <td class="px-4 py-4 text-right font-medium">Rp ${escapeInvoiceHtml(row.total_amount)}</td>
                <td class="px-4 py-4 text-right font-semibold text-zinc-950">Rp ${escapeInvoiceHtml(row.remaining)}</td>
                <td class="px-4 py-4">${invoiceStatusBadge(row.status)}</td>
                <td class="px-4 py-4 text-center">${invoiceActions(row)}</td>
            </tr>
        `).join('') : '<tr><td colspan="7" class="px-5 py-12 text-center text-sm text-zinc-500">Data invoice tidak ditemukan.</td></tr>';

        document.getElementById('invoiceSummary').textContent = `${filtered.length} dari ${invoiceState.rows.length} invoice`;
        document.getElementById('invoicePageInfo').textContent = `Halaman ${invoiceState.page} dari ${totalPages}`;
        document.getElementById('invoicePrev').disabled = invoiceState.page <= 1;
        document.getElementById('invoiceNext').disabled = invoiceState.page >= totalPages;
    }

    async function loadInvoiceTable() {
        try {
            const response = await fetch('{{ route('api.invoices.data', $id) }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Gagal mengambil data invoice.');
            const payload = await response.json();
            invoiceState.rows = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);
            renderInvoiceTable();
        } catch (error) {
            document.getElementById('invoiceTableBody').innerHTML = `<tr><td colspan="7" class="px-5 py-12 text-center text-sm text-red-600">${escapeInvoiceHtml(error.message)}</td></tr>`;
            document.getElementById('invoiceSummary').textContent = 'Data gagal dimuat';
        }
    }

    document.getElementById('invoiceSearch')?.addEventListener('input', event => {
        invoiceState.query = event.target.value.trim();
        invoiceState.page = 1;
        renderInvoiceTable();
    });
    document.getElementById('invoicePrev')?.addEventListener('click', () => {
        if (invoiceState.page > 1) invoiceState.page--;
        renderInvoiceTable();
    });
    document.getElementById('invoiceNext')?.addEventListener('click', () => {
        invoiceState.page++;
        renderInvoiceTable();
    });
    document.getElementById('invoiceTableBody')?.addEventListener('click', event => {
        const payButton = event.target.closest('[data-invoice-pay]');
        if (payButton) {
            openPaymentModal(Number(payButton.dataset.invoicePay), payButton.dataset.invoiceNumber);
            return;
        }
        const editButton = event.target.closest('[data-invoice-edit]');
        if (editButton) {
            const row = invoiceState.rows.find(item => Number(item.id) === Number(editButton.dataset.invoiceEdit));
            if (row) editInvoice(row);
        }
    });

    loadInvoiceTable();

    /*
    |--------------------------------------------------------------------------
    | Edit Invoice
    |--------------------------------------------------------------------------
    */

    window.editInvoice = function(data) {

        openModal('invoiceModal');

        $('#invoiceForm').attr(
            'action',
            '/invoice/' + data.id
        );

        $('#methodField').html(
            '@method("PUT")'
        );

        $('#customer_id').val(data.customer_id);

        $('#no_invoice').val(data.no_invoice);

        $('#total_amount').val(
            String(data.total_amount || '').replace(/\D/g, '')
        );

        $('#total_amount_display').val(
            formatRupiah(
                parseFloat(
                    String(data.total_amount || '').replace(/\D/g, '')
                ) || 0
            )
        );

        $('#due_date').val(data.due_date_raw);

        /*
         * Reset keterangan if returned from API
         */
        if (data.keterangan !== undefined) {
            $('#keterangan').val(data.keterangan);
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Payment Modal
    |--------------------------------------------------------------------------
    */

    window.openPaymentModal = function(invoiceId, noInvoice) {

        $('#modalNoInvoice').text(noInvoice);

        $('#paymentForm').attr(
            'action',
            '/invoice/payment/' + invoiceId
        );

        openModal('paymentModal');
    };


    /*
    |--------------------------------------------------------------------------
    | Dynamic Invoice Items
    |--------------------------------------------------------------------------
    */

    const container = document.getElementById('itemRowsContainer');

    const totalAmountInput =
        document.getElementById('total_amount');

    const totalDisplay =
        document.getElementById('total_amount_display');

    const invoiceForm =
        document.getElementById('invoiceForm');


    const formatRupiah = (angka) => {

        return new Intl.NumberFormat(
            'id-ID',
            {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }
        ).format(angka || 0);

    };


    const parseRupiah = (string) => {

        let clean = String(string)
            .replace(/[^0-9]/g, '');

        return parseFloat(clean) || 0;
    };


    window.calculateTotal = function() {

        let total = 0;

        document
            .querySelectorAll('.item-price-raw')
            .forEach(input => {

                let val =
                    parseFloat(input.value) || 0;

                total += val;
            });

        totalDisplay.value =
            formatRupiah(total);

        totalAmountInput.value =
            total;
    };


    /*
    |--------------------------------------------------------------------------
    | Add Item Row
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('addRowBtn')
        .addEventListener('click', function() {

            const newRow =
                document.createElement('div');

            newRow.className =
                'item-row grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 sm:grid-cols-[1fr_220px_44px]';

            newRow.innerHTML = `
                <div>
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                        Nama Layanan
                    </label>

                    <input
                        type="text"
                        class="item-desc w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        placeholder="Nama Layanan"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1 block text-[11px] font-medium uppercase tracking-wide text-zinc-500">
                        Harga
                    </label>

                    <input
                        type="text"
                        class="item-price w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        placeholder="Harga (Rp)"
                        required
                    >

                    <input
                        type="hidden"
                        class="item-price-raw"
                        value="0"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="button"
                        class="remove-row inline-flex h-[42px] w-full items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 sm:w-[42px]"
                        title="Hapus"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            `;

            container.appendChild(newRow);

            updateRemoveButtons();
        });


    /*
    |--------------------------------------------------------------------------
    | Remove Item Row
    |--------------------------------------------------------------------------
    */

    container.addEventListener(
        'click',
        function(e) {

            const button =
                e.target.closest('.remove-row');

            if (!button) return;

            const row =
                button.closest('.item-row');

            if (!row) return;

            row.remove();

            calculateTotal();

            updateRemoveButtons();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Format Harga
    |--------------------------------------------------------------------------
    */

    container.addEventListener(
        'input',
        function(e) {

            if (!e.target.classList.contains('item-price')) {
                return;
            }

            const row =
                e.target.closest('.item-row');

            const rawInput =
                row.querySelector('.item-price-raw');

            const numericValue =
                parseRupiah(e.target.value);

            rawInput.value =
                numericValue;

            e.target.value =
                numericValue > 0
                    ? formatRupiah(numericValue)
                        .replace('Rp', '')
                        .trim()
                    : '';

            calculateTotal();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Remove Button State
    |--------------------------------------------------------------------------
    */

    window.updateRemoveButtons = function() {

        const rows =
            container.querySelectorAll('.item-row');

        rows.forEach(row => {

            const button =
                row.querySelector('.remove-row');

            button.disabled =
                rows.length === 1;
        });
    };


    /*
    |--------------------------------------------------------------------------
    | Submit Invoice
    |--------------------------------------------------------------------------
    */

    invoiceForm.addEventListener(
        'submit',
        function() {

            let itemsArray = [];

            document
                .querySelectorAll('.item-row')
                .forEach(row => {

                    let desc =
                        row.querySelector('.item-desc').value;

                    let price =
                        row.querySelector('.item-price-raw').value;

                    itemsArray.push({
                        description: desc,
                        price: parseFloat(price) || 0
                    });
                });

            document.getElementById(
                'items_json'
            ).value =
                JSON.stringify(itemsArray);
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial State
    |--------------------------------------------------------------------------
    */

    updateRemoveButtons();
    calculateTotal();

});
</script>

@endsection
