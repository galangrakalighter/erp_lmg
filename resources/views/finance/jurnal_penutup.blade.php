@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1500px]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="mb-6 flex flex-col gap-4 border-b border-zinc-200 pb-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-px w-8 bg-zinc-950"></span>

                    <span class="text-[10px] font-semibold uppercase tracking-[0.25em] text-zinc-500">
                        Accounting
                    </span>
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    Jurnal Tutup Akun
                </h1>

                <p class="mt-1 text-xs text-zinc-500">
                    Kelola penutupan akun pendapatan dan beban berdasarkan periode.
                </p>
            </div>

            <div>
                @if(!$isPeriodLocked)

                    <button
                        type="button"
                        data-modal-open="modalTutupAkun"
                        {{ $accounts->isEmpty() ? 'disabled' : '' }}
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40 sm:w-auto"
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
                                d="M12 5v14M5 12h14"
                            />
                        </svg>

                        Tutup Satu Akun
                    </button>

                @else

                    <span class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-semibold text-red-700">
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                            <path
                                stroke-linecap="round"
                                d="M8 10V7a4 4 0 018 0v3"
                            />
                        </svg>

                        Periode Dikunci
                    </span>

                @endif
            </div>

        </div>


        {{-- =========================================================
             PERIOD FILTER
        ========================================================== --}}
        <div class="mb-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">

            <form
                action="{{ route('Ledger.jurnal_penutup', $id) }}"
                method="GET"
                class="flex flex-col gap-4 lg:flex-row lg:items-end"
            >

                <div class="w-full lg:w-56">
                    <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-500">
                        Periode Bulan
                    </label>

                    <input
                        type="month"
                        name="periode"
                        value="{{ $periode }}"
                        onchange="this.form.submit()"
                        class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm font-medium text-zinc-800 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >
                </div>

                <div class="pb-1 lg:ml-2">
                    <p class="max-w-2xl text-xs leading-5 text-zinc-500">
                        Setiap akun dapat ditutup, diubah keterangannya, atau dibuka kembali secara terpisah.
                    </p>
                </div>

            </form>
        </div>


        {{-- =========================================================
             TABLE
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_6px_25px_rgba(0,0,0,0.04)]">

            <div class="border-b border-zinc-200 bg-zinc-50 px-5 py-4">
                <div class="flex items-center justify-between gap-4">

                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950">
                            Daftar Jurnal Penutupan
                        </h2>

                        <p class="mt-0.5 text-[11px] text-zinc-500">
                            Akun yang telah ditutup pada periode terpilih.
                        </p>
                    </div>

                    <span class="hidden rounded-full border border-zinc-200 bg-white px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-500 sm:inline-flex">
                        {{ $periode }}
                    </span>

                </div>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1100px] border-collapse text-xs">

                    <thead>
                        <tr class="bg-zinc-950 text-white">

                            <th class="w-[13%] border border-zinc-800 px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-wider">
                                Tanggal / No. Jurnal
                            </th>

                            <th class="w-[10%] border border-zinc-800 px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-wider">
                                Kode Akun
                            </th>

                            <th class="w-[18%] border border-zinc-800 px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                Nama Akun COA
                            </th>

                            <th class="border border-zinc-800 px-3 py-3 text-left text-[10px] font-semibold uppercase tracking-wider">
                                Keterangan
                            </th>

                            <th class="w-[12%] border border-zinc-800 px-3 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                Debit (Rp)
                            </th>

                            <th class="w-[12%] border border-zinc-800 px-3 py-3 text-right text-[10px] font-semibold uppercase tracking-wider">
                                Kredit (Rp)
                            </th>

                            <th class="w-[9%] border border-zinc-800 px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-wider">
                                Status
                            </th>

                            <th class="w-[9%] border border-zinc-800 px-3 py-3 text-center text-[10px] font-semibold uppercase tracking-wider">
                                Aksi
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        @forelse($closings as $closing)

                            {{-- =================================================
                                 MAIN CLOSING ROW
                            ================================================== --}}
                            <tr class="transition-colors hover:bg-zinc-50">

                                <td
                                    rowspan="2"
                                    class="border-b border-zinc-200 px-3 py-3 text-center align-middle"
                                >
                                    <div class="font-semibold text-zinc-800">
                                        {{ \Carbon\Carbon::parse($closing->closing_date)->format('d/m/Y') }}
                                    </div>

                                    <span class="mt-1.5 inline-flex rounded-md border border-zinc-200 bg-zinc-50 px-2 py-1 font-mono text-[9px] font-semibold text-zinc-500">
                                        JT-{{ str_replace('-', '', $closing->periode) }}-{{ str_pad($closing->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-3 text-center align-middle font-mono font-semibold text-zinc-600">
                                    {{ $closing->kode }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-3 align-middle font-semibold text-zinc-800">
                                    {{ $closing->nama_akun }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-3 align-middle text-zinc-500">
                                    {{ $closing->keterangan }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-3 text-right align-middle font-mono font-semibold tabular-nums text-emerald-600">
                                    {{ $closing->debit > 0 ? number_format($closing->debit, 2, ',', '.') : '-' }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-3 text-right align-middle font-mono font-semibold tabular-nums text-red-600">
                                    {{ $closing->kredit > 0 ? number_format($closing->kredit, 2, ',', '.') : '-' }}
                                </td>

                                <td
                                    rowspan="2"
                                    class="border-b border-zinc-200 px-3 py-3 text-center align-middle"
                                >
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">

                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        Ditutup
                                    </span>
                                </td>

                                <td
                                    rowspan="2"
                                    class="border-b border-zinc-200 px-3 py-3 text-center align-middle"
                                >

                                    @if(!$isPeriodLocked)

                                        <div class="flex items-center justify-center gap-1.5">

                                            {{-- EDIT --}}
                                            <button
                                                type="button"
                                                data-modal-open="modalEdit{{ $closing->id }}"
                                                title="Ubah keterangan"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white"
                                            >
                                                <svg
                                                    class="h-3.5 w-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m16.5 3.5 4 4L8 20H4v-4L16.5 3.5Z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        d="m14 6 4 4"
                                                    />
                                                </svg>
                                            </button>

                                            {{-- DELETE / OPEN AGAIN --}}
                                            <form
                                                action="{{ route('Ledger.delete_account_closing', [$id, $closing->id]) }}"
                                                method="POST"
                                                onsubmit="return confirm('Buka kembali akun {{ addslashes($closing->nama_akun) }}?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    title="Buka kembali"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-white text-red-500 transition hover:bg-red-600 hover:text-white"
                                                >
                                                    <svg
                                                        class="h-3.5 w-3.5"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"
                                                        />
                                                    </svg>
                                                </button>
                                            </form>

                                        </div>

                                    @endif

                                </td>

                            </tr>


                            {{-- =================================================
                                 EQUITY / COUNTER ENTRY
                            ================================================== --}}
                            <tr class="bg-zinc-50">

                                <td class="border-b border-zinc-200 px-3 py-2.5 text-center font-mono font-semibold text-zinc-500">
                                    {{ $closing->equity_kode }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-2.5 font-semibold text-zinc-700">
                                    {{ $closing->equity_nama }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-2.5 text-zinc-400">
                                    Lawan jurnal penutupan {{ $closing->nama_akun }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-2.5 text-right font-mono font-semibold tabular-nums text-emerald-600">
                                    {{ $closing->kredit > 0 ? number_format($closing->kredit, 2, ',', '.') : '-' }}
                                </td>

                                <td class="border-b border-zinc-200 px-3 py-2.5 text-right font-mono font-semibold tabular-nums text-red-600">
                                    {{ $closing->debit > 0 ? number_format($closing->debit, 2, ',', '.') : '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="8"
                                    class="px-4 py-14 text-center"
                                >
                                    <div class="flex flex-col items-center">

                                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
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
                                                    d="M4 5.5A2.5 2.5 0 0 1 6.5 3H19v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    d="M4 18.5A2.5 2.5 0 0 1 6.5 16H19"
                                                />
                                            </svg>
                                        </div>

                                        <p class="text-sm font-medium text-zinc-700">
                                            Belum ada akun yang ditutup
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-400">
                                            Belum ada akun yang ditutup pada periode ini.
                                        </p>

                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>


{{-- =============================================================
     MODAL: TUTUP SATU AKUN
============================================================= --}}
<div
    id="modalTutupAkun"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">

        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <form
                action="{{ route('Ledger.close_account', $id) }}"
                method="POST"
            >
                @csrf

                {{-- HEADER --}}
                <div class="border-b border-zinc-800 bg-zinc-950 px-5 py-4 text-white">

                    <div class="flex items-center justify-between gap-4">

                        <div class="flex items-center gap-3">

                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                                    <path
                                        stroke-linecap="round"
                                        d="M8 10V7a4 4 0 018 0v3"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-sm font-semibold">
                                    Tutup Satu Akun
                                </h2>

                                <p class="mt-0.5 text-[10px] text-zinc-400">
                                    Buat jurnal penutupan akun.
                                </p>
                            </div>

                        </div>

                        <button
                            type="button"
                            data-modal-close="modalTutupAkun"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                            aria-label="Close"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </button>

                    </div>

                </div>


                {{-- BODY --}}
                <div class="space-y-5 p-5">

                    {{-- PERIODE --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-600">
                            Periode yang Akan Ditutup
                        </label>

                        <input
                            type="month"
                            name="periode"
                            value="{{ $periode }}"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >

                        <p class="mt-1.5 text-[11px] leading-4 text-zinc-400">
                            Bisa memilih periode sebelum atau sesudah bulan yang sedang ditampilkan.
                        </p>
                    </div>


                    {{-- ACCOUNT --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-600">
                            Akun Pendapatan / Beban
                        </label>

                        <select
                            name="chart_of_account_id"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                            <option value="">
                                -- Pilih akun yang akan ditutup --
                            </option>

                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->kode }} - {{ $account->nama_akun }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- KETERANGAN --}}
                    <div>
                        <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-600">
                            Keterangan
                        </label>

                        <input
                            type="text"
                            name="keterangan"
                            placeholder="Opsional, dibuat otomatis jika kosong"
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 placeholder:text-zinc-400 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                        >
                    </div>


                    {{-- WARNING --}}
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3.5">

                        <div class="flex gap-3">

                            <svg
                                class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3 2.8 19a1.5 1.5 0 0 0 1.3 2.25h15.8A1.5 1.5 0 0 0 21.2 19L12 3Z"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M12 9v4M12 17h.01"
                                />
                            </svg>

                            <p class="text-[11px] leading-5 text-amber-800">
                                Setelah ditutup, akun tidak dapat digunakan pada transaksi di periode yang dipilih sampai penutupannya dibatalkan.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4">

                    <button
                        type="button"
                        data-modal-close="modalTutupAkun"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-zinc-800"
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
                                d="m5 12 4 4L19 6"
                            />
                        </svg>

                        Tutup Akun
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>


{{-- =============================================================
     MODAL: EDIT KETERANGAN
============================================================= --}}
@foreach($closings as $closing)

<div
    id="modalEdit{{ $closing->id }}"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
        data-modal-backdrop
    ></div>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">

        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <form
                action="{{ route('Ledger.update_account_closing', [$id, $closing->id]) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="border-b border-zinc-800 bg-zinc-950 px-5 py-4 text-white">

                    <div class="flex items-center justify-between gap-4">

                        <div>
                            <h2 class="text-sm font-semibold">
                                Ubah Keterangan Jurnal
                            </h2>

                            <p class="mt-1 text-[10px] text-zinc-400">
                                {{ $closing->kode }} — {{ $closing->nama_akun }}
                            </p>
                        </div>

                        <button
                            type="button"
                            data-modal-close="modalEdit{{ $closing->id }}"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                            aria-label="Close"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </button>

                    </div>

                </div>


                {{-- BODY --}}
                <div class="p-5">

                    <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.15em] text-zinc-600">
                        Keterangan
                    </label>

                    <input
                        type="text"
                        name="keterangan"
                        value="{{ $closing->keterangan }}"
                        required
                        maxlength="255"
                        class="block w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                    >

                </div>


                {{-- FOOTER --}}
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4">

                    <button
                        type="button"
                        data-modal-close="modalEdit{{ $closing->id }}"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-100"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-zinc-800"
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
                                d="M5 12.5 9.5 17 19 7"
                            />
                        </svg>

                        Simpan
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endforeach


{{-- =============================================================
     MODAL SCRIPT
============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

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

        /*
         * Hanya remove overflow jika tidak ada modal
         * lain yang sedang terbuka.
         */
        const openModals = document.querySelectorAll(
            '[id^="modalTutupAkun"]:not(.hidden), [id^="modalEdit"]:not(.hidden)'
        );

        if (openModals.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    /*
     * OPEN MODAL
     */
    document.querySelectorAll('[data-modal-open]').forEach(function (button) {

        button.addEventListener('click', function () {

            const modalId = button.dataset.modalOpen;

            openModal(modalId);
        });

    });


    /*
     * CLOSE MODAL
     */
    document.querySelectorAll('[data-modal-close]').forEach(function (button) {

        button.addEventListener('click', function () {

            const modalId = button.dataset.modalClose;

            closeModal(modalId);
        });

    });


    /*
     * CLOSE VIA BACKDROP
     */
    document.querySelectorAll('[data-modal-backdrop]').forEach(function (backdrop) {

        backdrop.addEventListener('click', function () {

            const modal = backdrop.closest('[id]');

            if (modal) {
                closeModal(modal.id);
            }

        });

    });


    /*
     * ESCAPE KEY
     */
    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') return;

        const visibleModals = document.querySelectorAll(
            '[id^="modalTutupAkun"]:not(.hidden), [id^="modalEdit"]:not(.hidden)'
        );

        if (visibleModals.length) {

            const modal = visibleModals[visibleModals.length - 1];

            closeModal(modal.id);
        }

    });

});
</script>

@endsection