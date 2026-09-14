@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <div class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    Finance / Adjustment Journal
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    {{ $title }}
                </h1>

                <p class="mt-2 text-sm leading-6 text-zinc-500">
                    Kelola jurnal penyesuaian dan rincian transaksi akun untuk periode akuntansi.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">

                {{-- Export PDF --}}
                <a
                    href="{{ route('adjustment_journal.export-pdf', [
                        'id' => $id,
                        'periode' => request('periode', date('Y-m'))
                    ]) }}"
                    target="_blank"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-800 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 hover:text-zinc-950"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3v12m0 0 4-4m-4 4-4-4"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 17v1.5A2.5 2.5 0 0 0 6.5 21h11a2.5 2.5 0 0 0 2.5-2.5V17"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 3h5l3 3v4H8z"
                        />
                    </svg>

                    Export PDF
                </a>

                {{-- New Journal --}}
                @php
                    $currentPeriod = date('Y-m');
                @endphp

                <button
                    type="button"
                    onclick="resetJournalForm(); openAdjustmentModal('tambahJournalModal')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 5v14M5 12h14"
                        />
                    </svg>

                    Buat Jurnal Baru
                </button>

            </div>
        </div>


        {{-- =========================================================
             JOURNAL TABLE
        ========================================================== --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            <div class="border-b border-zinc-200 px-5 py-4 sm:px-6">
                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                    Adjustment Journal
                </div>

                <div class="mt-1 text-sm font-semibold text-zinc-900">
                    Daftar Jurnal Penyesuaian
                </div>
            </div>

            <div class="overflow-x-auto">

                <table
                    class="w-full min-w-[1100px] border-collapse"
                >

                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50">

                            <th
                                class="w-[12%] px-5 py-3 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Tanggal
                            </th>

                            <th
                                class="w-[35%] px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Keterangan
                            </th>

                            <th
                                class="w-[12%] px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Kode Akun
                            </th>

                            <th
                                class="w-[14%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Debit
                            </th>

                            <th
                                class="w-[14%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Kredit
                            </th>

                            <th
                                class="w-[13%] px-5 py-3 text-center text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Aksi
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        @php
                            $grandTotalDebet = 0;
                            $grandTotalKredit = 0;
                        @endphp

                        @forelse($journals as $journal)

                            @foreach($journal->details as $index => $detail)

                                @php
                                    $grandTotalDebet += (float) $detail->debit;
                                    $grandTotalKredit += (float) $detail->kredit;
                                @endphp

                                <tr class="border-b border-zinc-100 transition hover:bg-zinc-50/70">

                                    {{-- Date --}}
                                    <td class="px-5 py-4 text-center align-top">

                                        @if($index === 0)

                                            <div class="font-mono text-xs font-semibold text-zinc-900">
                                                {{ \Carbon\Carbon::parse($journal->tgl_transaksi)->format('Y') }}
                                            </div>

                                            <div class="mt-1 text-xs font-medium text-zinc-500">
                                                {{ \Carbon\Carbon::parse($journal->tgl_transaksi)->translatedFormat('d M') }}
                                            </div>

                                        @endif

                                    </td>

                                    {{-- Description --}}
                                    <td class="px-4 py-4 align-middle">

                                        @if((float) $detail->debit > 0)

                                            <div class="text-sm font-semibold text-zinc-900">
                                                {{ $detail->account->nama_akun ?? 'Nama Akun N/A' }}
                                            </div>

                                        @else

                                            <div class="pl-5 text-sm italic text-zinc-500">
                                                {{ $detail->account->nama_akun ?? 'Nama Akun N/A' }}
                                            </div>

                                        @endif

                                        @if($index === count($journal->details) - 1 && $journal->keterangan)

                                            <div class="mt-2 flex items-start gap-2 pl-3 text-xs italic text-zinc-400">

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path
                                                        stroke-linecap="round"
                                                        d="M12 10v6m0-9h.01"
                                                    />
                                                </svg>

                                                <span>
                                                    ({{ $journal->keterangan }})
                                                </span>

                                            </div>

                                        @endif

                                    </td>

                                    {{-- Account Code --}}
                                    <td class="px-4 py-4 text-center align-middle">

                                        <span class="inline-flex rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1 font-mono text-xs font-semibold text-zinc-700">
                                            {{ $detail->account->kode ?? '-' }}
                                        </span>

                                    </td>

                                    {{-- Debit --}}
                                    <td class="px-4 py-4 text-right align-middle">

                                        @if((float) $detail->debit > 0)

                                            <span class="font-mono text-sm font-semibold text-zinc-900">
                                                {{ number_format($detail->debit, 0, ',', '.') }}
                                            </span>

                                        @else

                                            <span class="text-zinc-300">—</span>

                                        @endif

                                    </td>

                                    {{-- Credit --}}
                                    <td class="px-4 py-4 text-right align-middle">

                                        @if((float) $detail->kredit > 0)

                                            <span class="font-mono text-sm font-semibold text-zinc-900">
                                                {{ number_format($detail->kredit, 0, ',', '.') }}
                                            </span>

                                        @else

                                            <span class="text-zinc-300">—</span>

                                        @endif

                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-center align-top">

                                        @if($index === 0)

                                            @if($journal->is_locked)

                                                <span
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400"
                                                    title="Data terkunci karena periode sudah ditutup"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-4 w-4"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                    >
                                                        <rect
                                                            x="5"
                                                            y="10"
                                                            width="14"
                                                            height="10"
                                                            rx="2"
                                                        />
                                                        <path
                                                            stroke-linecap="round"
                                                            d="M8 10V7a4 4 0 0 1 8 0v3"
                                                        />
                                                    </svg>
                                                </span>

                                            @else

                                                <div class="flex items-center justify-center gap-1.5">

                                                    {{-- Edit --}}
                                                    <button
                                                        type="button"
                                                        title="Edit Jurnal"
                                                        onclick='editJournal(
                                                            @json($journal),
                                                            @json(route("adjustment_journal.update", $journal->id))
                                                        )'
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition hover:border-zinc-300 hover:bg-zinc-950 hover:text-white"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="h-3.5 w-3.5"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M12 20h9"
                                                            />
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"
                                                            />
                                                        </svg>
                                                    </button>

                                                    {{-- Delete --}}
                                                    <button
                                                        type="button"
                                                        title="Hapus Jurnal"
                                                        onclick='deleteJournal(
                                                            @json(route("adjustment_journal.destroy", $journal->id)),
                                                            @json($journal->journal_number ?? "")
                                                        )'
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="h-3.5 w-3.5"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M4 7h16"
                                                            />
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M10 11v6m4-6v6"
                                                            />
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M6 7l1 14h10l1-14M9 7V4h6v3"
                                                            />
                                                        </svg>
                                                    </button>

                                                </div>

                                            @endif

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                            {{-- Journal separator --}}
                            <tr>
                                <td
                                    colspan="6"
                                    class="h-2 border-b border-zinc-200 bg-zinc-50 p-0"
                                ></td>
                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center"
                                >

                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-6 w-6"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 18.5z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M8 8h8M8 12h8M8 16h5"
                                            />
                                        </svg>

                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-zinc-900">
                                        Belum ada jurnal penyesuaian
                                    </h3>

                                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-zinc-500">
                                        Belum ada data ayat jurnal penyesuaian untuk periode ini.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    {{-- Total --}}
                    @if(count($journals) > 0)

                        <tfoot>

                            <tr class="border-t-2 border-zinc-300 bg-zinc-950 text-white">

                                <td
                                    colspan="3"
                                    class="px-5 py-5 text-center"
                                >
                                    <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                                        Total
                                    </span>
                                </td>

                                <td class="px-4 py-5 text-right">
                                    <span class="font-mono text-sm font-bold text-white">
                                        {{ number_format($grandTotalDebet, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td class="px-4 py-5 text-right">
                                    <span class="font-mono text-sm font-bold text-white">
                                        {{ number_format($grandTotalKredit, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td></td>

                            </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>
</div>


{{-- ================================================================
     MODAL INPUT
================================================================ --}}

<div
    id="tambahJournalModal"
    data-adjustment-modal
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-adjustment-backdrop="tambahJournalModal"
    >

        <div
            class="relative w-full max-w-6xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
        >

            {{-- Header --}}
            <div class="flex items-center justify-between bg-zinc-950 px-5 py-4 text-white sm:px-6">

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-500">
                        Adjustment Journal
                    </div>

                    <h2
                        id="tambahJournalModalLabel"
                        class="mt-1 text-base font-semibold"
                    >
                        Input Jurnal Penyesuaian
                    </h2>
                </div>

                <button
                    type="button"
                    onclick="closeAdjustmentModal('tambahJournalModal')"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>

            </div>

            <form
                action="{{ route('adjustment_journal.store', $id) }}"
                method="POST"
                id="formModalJournal"
            >

                @csrf

                <div class="max-h-[75vh] overflow-y-auto bg-[#f7f7f5] p-4 sm:p-6">

                    {{-- Journal Header --}}
                    <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">

                            <div class="md:col-span-3">
                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                    No. Jurnal
                                </label>

                                <input
                                    type="text"
                                    name="journal_number"
                                    value="AJ-{{ date('Ymd') }}-{{ str_pad($jumlah + 1, 4, '0', STR_PAD_LEFT) }}"
                                    readonly
                                    class="w-full rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-sm font-mono font-semibold text-zinc-700 outline-none"
                                >
                            </div>

                            <div class="md:col-span-3">
                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                    Tanggal Jurnal
                                </label>

                                <input
                                    type="date"
                                    name="date"
                                    value="{{ date('Y-m-d') }}"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                >
                            </div>

                            <div class="md:col-span-6">
                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                    Keterangan / Deskripsi
                                </label>

                                <input
                                    type="text"
                                    name="description"
                                    placeholder="Contoh: Penyesuaian sewa dibayar dimuka..."
                                    required
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                >
                            </div>

                        </div>

                    </div>

                    <input
                        type="hidden"
                        name="perusahaan"
                        value="{{ $id }}"
                    >

                    {{-- Detail --}}
                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">

                        <div class="flex flex-col gap-3 border-b border-zinc-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">
                                    Journal Entries
                                </div>

                                <div class="mt-0.5 text-xs font-semibold text-zinc-800">
                                    Rincian Akun Debit & Kredit
                                </div>
                            </div>

                            <button
                                type="button"
                                onclick="addModalRow('tambah')"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-zinc-800"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M12 5v14M5 12h14"
                                    />
                                </svg>

                                Tambah Baris
                            </button>

                        </div>

                        <div class="overflow-x-auto">

                            <table
                                class="w-full min-w-[850px] border-collapse"
                                id="modalJournalTable"
                            >

                                <thead>
                                    <tr class="border-b border-zinc-200 bg-zinc-50">

                                        <th class="w-[45%] px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Akun (COA)
                                        </th>

                                        <th class="w-[22%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Debit (Rp)
                                        </th>

                                        <th class="w-[22%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Kredit (Rp)
                                        </th>

                                        <th class="w-[11%] px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Aksi
                                        </th>

                                    </tr>
                                </thead>

                                <tbody id="modalJournalRows">

                                    <tr class="tambah-modal-journal-row border-b border-zinc-100">

                                        <td class="px-4 py-3">

                                            <select
                                                name="items[0][chart_of_account_id]"
                                                class="select-coa w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                                required
                                            >
                                                <option value="">
                                                    -- Pilih Akun COA --
                                                </option>

                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">
                                                        {{ $account->kode }} - {{ $account->nama_akun }}
                                                    </option>
                                                @endforeach

                                            </select>

                                        </td>

                                        <td class="px-4 py-3">

                                            <input
                                                type="number"
                                                step="0.01"
                                                name="items[0][debit]"
                                                class="input-debit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                                value="0"
                                                oninput="calculateModalTotal('tambah')"
                                            >

                                        </td>

                                        <td class="px-4 py-3">

                                            <input
                                                type="number"
                                                step="0.01"
                                                name="items[0][kredit]"
                                                class="input-kredit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                                value="0"
                                                oninput="calculateModalTotal('tambah')"
                                            >

                                        </td>

                                        <td class="px-4 py-3 text-center">

                                            <button
                                                type="button"
                                                onclick="removeModalRow(this, 'tambah')"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        d="M4 7h16"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        d="M10 11v6m4-6v6"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M6 7l1 14h10l1-14M9 7V4h6v3"
                                                    />
                                                </svg>
                                            </button>

                                        </td>

                                    </tr>

                                </tbody>

                                <tfoot>

                                    <tr class="border-t border-zinc-200 bg-zinc-50">

                                        <td class="px-4 py-3 text-right text-xs font-bold text-zinc-500">
                                            Total :
                                        </td>

                                        <td
                                            id="modalTotalDebitDisplay"
                                            class="px-4 py-3 text-right font-mono text-sm font-bold text-zinc-900"
                                        >
                                            Rp 0,00
                                        </td>

                                        <td
                                            id="modalTotalKreditDisplay"
                                            class="px-4 py-3 text-right font-mono text-sm font-bold text-zinc-900"
                                        >
                                            Rp 0,00
                                        </td>

                                        <td
                                            id="modalBalanceStatus"
                                            class="px-4 py-3 text-center text-xs font-bold text-red-600"
                                        >
                                            <span class="inline-flex items-center gap-1">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path
                                                        stroke-linecap="round"
                                                        d="m9 9 6 6m0-6-6 6"
                                                    />
                                                </svg>
                                                Unbalanced
                                            </span>
                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        onclick="closeAdjustmentModal('tambahJournalModal')"
                        class="rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        id="btnModalSubmit"
                        disabled
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 4h11l3 3v13H5z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 4v5h6V4M9 20v-7h6v7"
                            />
                        </svg>

                        Simpan Jurnal
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


{{-- ================================================================
     MODAL EDIT
================================================================ --}}

<div
    id="editJournalModal"
    data-adjustment-modal
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-adjustment-backdrop="editJournalModal"
    >

        <div
            class="relative w-full max-w-6xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
        >

            {{-- Header --}}
            <div class="flex items-center justify-between bg-zinc-950 px-5 py-4 text-white sm:px-6">

                <div>
                    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-500">
                        Adjustment Journal
                    </div>

                    <h2
                        id="editJournalModalLabel"
                        class="mt-1 text-base font-semibold"
                    >
                        Edit Jurnal Penyesuaian
                    </h2>
                </div>

                <button
                    type="button"
                    onclick="closeAdjustmentModal('editJournalModal')"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Close"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>

            </div>

            <form
                action=""
                method="POST"
                id="formEditJournal"
            >

                @csrf
                @method('PUT')

                <div class="max-h-[75vh] overflow-y-auto bg-[#f7f7f5] p-4 sm:p-6">

                    {{-- Journal Header --}}
                    <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">

                            <div class="md:col-span-3">

                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                    No. Jurnal
                                </label>

                                <input
                                    type="text"
                                    id="edit_journal_number"
                                    name="journal_number"
                                    readonly
                                    class="w-full rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 font-mono text-sm font-semibold text-zinc-700 outline-none"
                                >

                            </div>

                            <div class="md:col-span-3">

                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                    Tanggal Jurnal
                                </label>

                                <input
                                    type="date"
                                    id="edit_date"
                                    name="date"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                >

                            </div>

                            <div class="md:col-span-6">

                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                    Keterangan / Deskripsi
                                </label>

                                <input
                                    type="text"
                                    id="edit_description"
                                    name="description"
                                    required
                                    class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                                >

                            </div>

                        </div>

                    </div>

                    {{-- Detail --}}
                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">

                        <div class="flex flex-col gap-3 border-b border-zinc-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">
                                    Journal Entries
                                </div>

                                <div class="mt-0.5 text-xs font-semibold text-zinc-800">
                                    Rincian Akun Debit & Kredit
                                </div>
                            </div>

                            <button
                                type="button"
                                onclick="addModalRow('edit')"
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-zinc-950 px-3 py-2 text-xs font-semibold text-white transition hover:bg-zinc-800"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M12 5v14M5 12h14"
                                    />
                                </svg>

                                Tambah Baris
                            </button>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="w-full min-w-[850px] border-collapse">

                                <thead>
                                    <tr class="border-b border-zinc-200 bg-zinc-50">

                                        <th class="w-[45%] px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Akun (COA)
                                        </th>

                                        <th class="w-[22%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Debit (Rp)
                                        </th>

                                        <th class="w-[22%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Kredit (Rp)
                                        </th>

                                        <th class="w-[11%] px-4 py-3 text-center text-[10px] font-bold uppercase tracking-wide text-zinc-500">
                                            Aksi
                                        </th>

                                    </tr>
                                </thead>

                                <tbody id="editModalJournalRows">
                                    {{-- Inject via JavaScript --}}
                                </tbody>

                                <tfoot>

                                    <tr class="border-t border-zinc-200 bg-zinc-50">

                                        <td class="px-4 py-3 text-right text-xs font-bold text-zinc-500">
                                            Total :
                                        </td>

                                        <td
                                            id="editModalTotalDebitDisplay"
                                            class="px-4 py-3 text-right font-mono text-sm font-bold text-zinc-900"
                                        >
                                            Rp 0,00
                                        </td>

                                        <td
                                            id="editModalTotalKreditDisplay"
                                            class="px-4 py-3 text-right font-mono text-sm font-bold text-zinc-900"
                                        >
                                            Rp 0,00
                                        </td>

                                        <td
                                            id="editModalBalanceStatus"
                                            class="px-4 py-3 text-center text-xs font-bold text-red-600"
                                        >
                                            <span class="inline-flex items-center gap-1">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-3.5 w-3.5"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path
                                                        stroke-linecap="round"
                                                        d="m9 9 6 6m0-6-6 6"
                                                    />
                                                </svg>
                                                Unbalanced
                                            </span>
                                        </td>

                                    </tr>

                                </tfoot>

                            </table>

                        </div>

                    </div>

                </div>

                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        onclick="closeAdjustmentModal('editJournalModal')"
                        class="rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        id="btnEditModalSubmit"
                        disabled
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 4h11l3 3v13H5z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 4v5h6V4M9 20v-7h6v7"
                            />
                        </svg>

                        Perbarui Jurnal
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


{{-- ================================================================
     MODAL DELETE
================================================================ --}}

<div
    id="deleteJournalModal"
    data-adjustment-modal
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4"
        data-adjustment-backdrop="deleteJournalModal"
    >

        <div class="relative w-full max-w-md overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between bg-red-700 px-5 py-4 text-white">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10.3 3.7 2.9 17a2 2 0 0 0 1.75 3h14.7a2 2 0 0 0 1.75-3L13.7 3.7a2 2 0 0 0-3.4 0z"
                            />
                        </svg>

                    </div>

                    <div>

                        <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-red-200">
                            Adjustment Journal
                        </div>

                        <h2
                            id="deleteJournalModalLabel"
                            class="mt-0.5 text-base font-semibold"
                        >
                            Konfirmasi Hapus
                        </h2>

                    </div>

                </div>

                <button
                    type="button"
                    onclick="closeAdjustmentModal('deleteJournalModal')"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-100 transition hover:bg-white/10 hover:text-white"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>
                </button>

            </div>

            <form
                action=""
                method="POST"
                id="formDeleteJournal"
            >

                @csrf
                @method('DELETE')

                <div class="px-5 py-6">

                    <p class="text-sm leading-6 text-zinc-700">
                        Apakah Anda yakin ingin menghapus Jurnal Penyesuaian
                        <strong
                            id="delete_journal_code"
                            class="font-semibold text-red-600"
                        >
                            Terpilih
                        </strong>?
                    </p>

                    <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3">

                        <div class="flex gap-3">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="mt-0.5 h-4 w-4 shrink-0 text-red-500"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v4m0 4h.01"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.3 3.7 2.9 17a2 2 0 0 0 1.75 3h14.7a2 2 0 0 0 1.75-3L13.7 3.7a2 2 0 0 0-3.4 0z"
                                />
                            </svg>

                            <p class="text-xs leading-5 text-red-700">
                                Tindakan ini tidak dapat dibatalkan dan akan menghapus
                                semua rincian pos debit/kredit yang terkait.
                            </p>

                        </div>

                    </div>

                </div>

                <div class="flex justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4">

                    <button
                        type="button"
                        onclick="closeAdjustmentModal('deleteJournalModal')"
                        class="rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                d="M4 7h16"
                            />
                            <path
                                stroke-linecap="round"
                                d="M10 11v6m4-6v6"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 7l1 14h10l1-14M9 7V4h6v3"
                            />
                        </svg>

                        Ya, Hapus Jurnal
                    </button>

                </div>

            </form>

        </div>

    </div>
</div>


{{-- ================================================================
     JAVASCRIPT
================================================================ --}}

<script>
    let modalRowIndex = 1;
    let editModalRowIndex = 0;

    /*
     * Kumpulan option COA.
     * Tetap menggunakan data dari $accounts seperti versi sebelumnya.
     */
    const coaOptions = `
        <option value="">-- Pilih Akun COA --</option>
        @foreach($accounts as $account)
            <option value="{{ $account->id }}">
                {{ $account->kode }} - {{ $account->nama_akun }}
            </option>
        @endforeach
    `;


    /* =============================================================
       MODAL HELPERS
    ============================================================= */

    function openAdjustmentModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }


    function closeAdjustmentModal(id) {
        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        const visibleModal = document.querySelector(
            '[data-adjustment-modal]:not(.hidden)'
        );

        if (!visibleModal) {
            document.body.classList.remove('overflow-hidden');
        }
    }


    /*
     * Klik backdrop.
     * Modal input/edit dibuat tidak tertutup hanya karena user
     * tidak sengaja klik area luar panel.
     */
    document.addEventListener('click', function (event) {

        const backdrop = event.target.closest(
            '[data-adjustment-backdrop]'
        );

        if (!backdrop || event.target !== backdrop) {
            return;
        }

        const modalId = backdrop.getAttribute(
            'data-adjustment-backdrop'
        );

        /*
         * Delete boleh ditutup dengan klik backdrop.
         * Form input/edit tidak.
         */
        if (modalId === 'deleteJournalModal') {
            closeAdjustmentModal(modalId);
        }

    });


    /*
     * Escape hanya menutup modal delete.
     * Input/edit tetap aman dari accidental close.
     */
    document.addEventListener('keydown', function (event) {

        if (event.key !== 'Escape') {
            return;
        }

        const visibleModal = document.querySelector(
            '[data-adjustment-modal]:not(.hidden)'
        );

        if (!visibleModal) {
            return;
        }

        if (visibleModal.id === 'deleteJournalModal') {
            closeAdjustmentModal(visibleModal.id);
        }

    });


    /* =============================================================
       ADD / REMOVE ROW
    ============================================================= */

    function addModalRow(type = 'tambah') {

        const tbodyId =
            type === 'tambah'
                ? 'modalJournalRows'
                : 'editModalJournalRows';

        const index =
            type === 'tambah'
                ? modalRowIndex
                : editModalRowIndex;

        const tbody = document.getElementById(tbodyId);

        if (!tbody) return;

        const rowHTML = `
            <tr class="${type}-modal-journal-row border-b border-zinc-100">

                <td class="px-4 py-3">

                    <select
                        name="items[${index}][chart_of_account_id]"
                        class="select-coa w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                        required
                    >
                        ${coaOptions}
                    </select>

                </td>

                <td class="px-4 py-3">

                    <input
                        type="number"
                        step="0.01"
                        name="items[${index}][debit]"
                        class="input-debit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                        value="0"
                        oninput="calculateModalTotal('${type}')"
                    >

                </td>

                <td class="px-4 py-3">

                    <input
                        type="number"
                        step="0.01"
                        name="items[${index}][kredit]"
                        class="input-kredit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                        value="0"
                        oninput="calculateModalTotal('${type}')"
                    >

                </td>

                <td class="px-4 py-3 text-center">

                    <button
                        type="button"
                        onclick="removeModalRow(this, '${type}')"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                d="M4 7h16"
                            />
                            <path
                                stroke-linecap="round"
                                d="M10 11v6m4-6v6"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 7l1 14h10l1-14M9 7V4h6v3"
                            />
                        </svg>
                    </button>

                </td>

            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', rowHTML);

        if (type === 'tambah') {
            modalRowIndex++;
        } else {
            editModalRowIndex++;
        }

        calculateModalTotal(type);
    }


    function removeModalRow(button, type = 'tambah') {

        const rowClass =
            type === 'tambah'
                ? '.tambah-modal-journal-row'
                : '.edit-modal-journal-row';

        const rows = document.querySelectorAll(rowClass);

        if (rows.length <= 1) {
            alert('Minimal harus mengisi 1 baris transaksi!');
            return;
        }

        const row = button.closest('tr');

        if (row) {
            row.remove();
        }

        calculateModalTotal(type);
    }


    /* =============================================================
       CALCULATE TOTAL
    ============================================================= */

    function calculateModalTotal(type = 'tambah') {

        const prefix =
            type === 'tambah'
                ? 'modal'
                : 'editModal';

        const rowClass =
            type === 'tambah'
                ? '.tambah-modal-journal-row'
                : '.edit-modal-journal-row';

        let totalDebit = 0;
        let totalKredit = 0;

        document
            .querySelectorAll(`${rowClass} .input-debit`)
            .forEach(input => {
                totalDebit += parseFloat(input.value) || 0;
            });

        document
            .querySelectorAll(`${rowClass} .input-kredit`)
            .forEach(input => {
                totalKredit += parseFloat(input.value) || 0;
            });


        const debitDisplay =
            document.getElementById(
                `${prefix}TotalDebitDisplay`
            );

        const kreditDisplay =
            document.getElementById(
                `${prefix}TotalKreditDisplay`
            );

        const balanceStatus =
            document.getElementById(
                `${prefix}BalanceStatus`
            );

        const btnSubmit =
            type === 'tambah'
                ? document.getElementById('btnModalSubmit')
                : document.getElementById('btnEditModalSubmit');


        if (debitDisplay) {
            debitDisplay.textContent =
                'Rp ' +
                totalDebit.toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                });
        }

        if (kreditDisplay) {
            kreditDisplay.textContent =
                'Rp ' +
                totalKredit.toLocaleString('id-ID', {
                    minimumFractionDigits: 2
                });
        }


        const isBalanced =
            totalDebit === totalKredit &&
            totalDebit > 0;


        if (!balanceStatus) {
            return;
        }


        if (isBalanced) {

            balanceStatus.className =
                'px-4 py-3 text-center text-xs font-bold text-emerald-600';

            balanceStatus.innerHTML = `
                <span class="inline-flex items-center gap-1">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m5 12 4 4L19 6"
                        />
                    </svg>
                    Balanced
                </span>
            `;

            if (btnSubmit) {
                btnSubmit.disabled = false;
            }

        } else {

            balanceStatus.className =
                'px-4 py-3 text-center text-xs font-bold text-red-600';

            balanceStatus.innerHTML = `
                <span class="inline-flex items-center gap-1">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                        />
                        <path
                            stroke-linecap="round"
                            d="m9 9 6 6m0-6-6 6"
                        />
                    </svg>
                    Unbalanced
                </span>
            `;

            if (btnSubmit) {
                btnSubmit.disabled = true;
            }

        }
    }


    /* =============================================================
       RESET FORM
    ============================================================= */

    function resetJournalForm() {

        const form =
            document.getElementById('formModalJournal');

        if (!form) return;

        form.reset();

        const tbody =
            document.getElementById('modalJournalRows');

        if (!tbody) return;


        tbody.innerHTML = `
            <tr class="tambah-modal-journal-row border-b border-zinc-100">

                <td class="px-4 py-3">

                    <select
                        name="items[0][chart_of_account_id]"
                        class="select-coa w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                        required
                    >
                        ${coaOptions}
                    </select>

                </td>

                <td class="px-4 py-3">

                    <input
                        type="number"
                        step="0.01"
                        name="items[0][debit]"
                        class="input-debit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                        value="0"
                        oninput="calculateModalTotal('tambah')"
                    >

                </td>

                <td class="px-4 py-3">

                    <input
                        type="number"
                        step="0.01"
                        name="items[0][kredit]"
                        class="input-kredit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                        value="0"
                        oninput="calculateModalTotal('tambah')"
                    >

                </td>

                <td class="px-4 py-3 text-center">

                    <button
                        type="button"
                        onclick="removeModalRow(this, 'tambah')"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                d="M4 7h16"
                            />
                            <path
                                stroke-linecap="round"
                                d="M10 11v6m4-6v6"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 7l1 14h10l1-14M9 7V4h6v3"
                            />
                        </svg>
                    </button>

                </td>

            </tr>
        `;

        modalRowIndex = 1;

        calculateModalTotal('tambah');
    }


    /* =============================================================
       EDIT JOURNAL
    ============================================================= */

    function editJournal(journal, actionUrl) {

        const form =
            document.getElementById('formEditJournal');

        const journalNumber =
            document.getElementById('edit_journal_number');

        const date =
            document.getElementById('edit_date');

        const description =
            document.getElementById('edit_description');

        const tbody =
            document.getElementById('editModalJournalRows');


        if (!form || !tbody) {
            return;
        }


        form.action = actionUrl;

        journalNumber.value =
            journal.journal_number ||
            `AJ-${journal.id}`;

        date.value =
            journal.tgl_transaksi || '';

        description.value =
            journal.keterangan || '';


        tbody.innerHTML = '';

        editModalRowIndex = 0;


        if (!Array.isArray(journal.details)) {
            calculateModalTotal('edit');
            openAdjustmentModal('editJournalModal');
            return;
        }


        journal.details.forEach((detail, index) => {

            const debit =
                parseFloat(detail.debit) || 0;

            const kredit =
                parseFloat(detail.kredit) || 0;


            const rowHTML = `
                <tr class="edit-modal-journal-row border-b border-zinc-100">

                    <td class="px-4 py-3">

                        <select
                            name="items[${index}][chart_of_account_id]"
                            class="select-coa w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                            required
                        >
                            ${coaOptions}
                        </select>

                    </td>

                    <td class="px-4 py-3">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][debit]"
                            class="input-debit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                            value="${debit}"
                            oninput="calculateModalTotal('edit')"
                        >

                    </td>

                    <td class="px-4 py-3">

                        <input
                            type="number"
                            step="0.01"
                            name="items[${index}][kredit]"
                            class="input-kredit w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-right font-mono text-sm text-zinc-900 outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-200"
                            value="${kredit}"
                            oninput="calculateModalTotal('edit')"
                        >

                    </td>

                    <td class="px-4 py-3 text-center">

                        <button
                            type="button"
                            onclick="removeModalRow(this, 'edit')"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M4 7h16"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M10 11v6m4-6v6"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 7l1 14h10l1-14M9 7V4h6v3"
                                />
                            </svg>
                        </button>

                    </td>

                </tr>
            `;


            tbody.insertAdjacentHTML(
                'beforeend',
                rowHTML
            );


            const row =
                tbody.children[index];

            const selectElement =
                row.querySelector('.select-coa');


            if (selectElement) {
                selectElement.value =
                    detail.chart_of_account_id;
            }


            editModalRowIndex++;

        });


        calculateModalTotal('edit');

        openAdjustmentModal('editJournalModal');
    }


    /* =============================================================
       DELETE JOURNAL
    ============================================================= */

    function deleteJournal(actionUrl, journalCode) {

        const form =
            document.getElementById('formDeleteJournal');

        const code =
            document.getElementById('delete_journal_code');


        if (!form) return;


        form.action = actionUrl;

        if (code) {
            code.textContent =
                journalCode || 'Terpilih';
        }


        openAdjustmentModal('deleteJournalModal');
    }


    /* =============================================================
       INITIALIZE
    ============================================================= */

    document.addEventListener('DOMContentLoaded', function () {

        calculateModalTotal('tambah');

    });
</script>

@endsection