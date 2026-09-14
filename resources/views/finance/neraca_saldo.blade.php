@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    Finance / Trial Balance
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    {{ $title }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                    Kalkulasi real-time seluruh saldo berjalan akun per periode akuntansi.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                {{-- Periode --}}
                <form
                    action="{{ url()->current() }}"
                    method="GET"
                    class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white px-3 py-2.5 shadow-sm"
                >
                    <label
                        for="periode"
                        class="hidden text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400 sm:block"
                    >
                        Periode
                    </label>

                    <input
                        type="month"
                        name="periode"
                        id="periode"
                        value="{{ $periode }}"
                        onchange="this.form.submit()"
                        class="rounded-lg border-0 bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-900 outline-none transition focus:bg-zinc-200 focus:ring-2 focus:ring-zinc-300"
                    >
                </form>

                {{-- Export Excel --}}
                <a
                    href="{{ route('Ledger.export_neraca_saldo', ['periode' => $periode]) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-950 bg-zinc-950 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2"
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
                            d="M4 4.5A1.5 1.5 0 0 1 5.5 3h13A1.5 1.5 0 0 1 20 4.5v15a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 7h8M8 11h8M8 15h4"
                        />
                    </svg>

                    <span>Export Excel</span>
                </a>
            </div>
        </div>

        {{-- Balance Status --}}
        @if(round($grandTotalDebit, 2) === round($grandTotalKredit, 2))

            <div class="mb-6 flex items-start gap-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
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
                </div>

                <div>
                    <div class="text-sm font-bold text-emerald-900">
                        Status Pembukuan: SEIMBANG
                    </div>

                    <p class="mt-0.5 text-xs leading-5 text-emerald-700">
                        Total nilai aset, kewajiban, dan modal telah cocok sempurna.
                    </p>
                </div>
            </div>

        @else

            <div class="mb-6 flex items-start gap-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.3 3.7 2.9 17a2 2 0 0 0 1.75 3h14.7a2 2 0 0 0 1.75-3L13.7 3.7a2 2 0 0 0-3.4 0z"
                        />
                    </svg>
                </div>

                <div>
                    <div class="text-sm font-bold text-red-900">
                        Status Pembukuan: ADA SELISIH
                    </div>

                    <p class="mt-0.5 text-xs leading-5 text-red-700">
                        Terjadi ketidakcocokan antara posisi Debit dan Kredit.
                        Periksa kembali entri transaksi pada Jurnal Umum Anda.
                    </p>
                </div>
            </div>

        @endif

        {{-- Trial Balance Table --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            <div class="flex flex-col gap-1 border-b border-zinc-200 px-5 py-4 sm:px-6">
                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                    Trial Balance
                </div>

                <div class="text-sm font-semibold text-zinc-900">
                    Neraca Saldo — Periode {{ $periode }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] border-collapse">

                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50">

                            <th
                                class="w-[15%] px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Kode Akun
                            </th>

                            <th
                                class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Nama Akun / COA
                            </th>

                            <th
                                class="w-[20%] px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Kategori
                            </th>

                            <th
                                class="w-[20%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Saldo Akhir (Debit)
                            </th>

                            <th
                                class="w-[20%] px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Saldo Akhir (Kredit)
                            </th>

                        </tr>
                    </thead>

                    <tbody class="text-sm">

                        @forelse($reportData as $row)

                            <tr class="border-b border-zinc-100 transition hover:bg-zinc-50/70">

                                {{-- Account Code --}}
                                <td class="px-5 py-4 align-top">
                                    <span class="font-mono text-sm font-bold text-zinc-950">
                                        {{ $row['kode'] }}
                                    </span>
                                </td>

                                {{-- Account Name --}}
                                <td class="px-4 py-4 align-top">
                                    <span class="text-sm font-semibold text-zinc-800">
                                        {{ $row['nama_akun'] }}
                                    </span>
                                </td>

                                {{-- Category --}}
                                <td class="px-4 py-4 align-top">
                                    <span class="text-xs capitalize text-zinc-500">
                                        {{ $row['category'] }}
                                    </span>
                                </td>

                                {{-- Debit --}}
                                <td class="px-4 py-4 text-right align-top">
                                    @if($row['saldo_debit'] > 0)
                                        <span class="font-mono text-sm font-semibold text-zinc-900">
                                            Rp {{ number_format($row['saldo_debit'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>

                                {{-- Credit --}}
                                <td class="px-5 py-4 text-right align-top">
                                    @if($row['saldo_kredit'] > 0)
                                        <span class="font-mono text-sm font-semibold text-zinc-900">
                                            Rp {{ number_format($row['saldo_kredit'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="5"
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
                                        Tidak ada data akun
                                    </h3>

                                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-zinc-500">
                                        Tidak ada riwayat akun atau transaksi aktif di periode ini.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    {{-- Grand Total --}}
                    <tfoot>
                        <tr class="border-t-2 border-zinc-300 bg-zinc-950 text-white">

                            <td
                                colspan="3"
                                class="px-5 py-5 text-right"
                            >
                                <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">
                                    Total Neraca Keseluruhan
                                </div>

                                <div class="mt-1 text-xs text-zinc-500">
                                    Periode {{ $periode }}
                                </div>
                            </td>

                            <td class="px-4 py-5 text-right">
                                <span class="font-mono text-sm font-bold text-white">
                                    Rp {{ number_format($grandTotalDebit, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="px-5 py-5 text-right">
                                <span class="font-mono text-sm font-bold text-white">
                                    Rp {{ number_format($grandTotalKredit, 0, ',', '.') }}
                                </span>
                            </td>

                        </tr>
                    </tfoot>

                </table>
            </div>

        </div>

    </div>
</div>

@endsection