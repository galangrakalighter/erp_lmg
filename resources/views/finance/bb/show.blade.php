@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- Back --}}
        <div class="mb-5">
            <a
                href="{{ route('finance_ledger.list_account', [
                    'id' => $id_perusahaan,
                    'periode' => $periode
                ]) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 hover:text-zinc-950"
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
                        d="M19 12H5m6 6-6-6 6-6"
                    />
                </svg>

                <span>Kembali ke Pilih Akun</span>
            </a>
        </div>

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    Finance / General Ledger
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    [{{ $ledgerData['account']->kode }}]
                    <span class="font-normal text-zinc-400">-</span>
                    {{ $ledgerData['account']->nama_akun }}
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-zinc-500">
                    <span>
                        Tipe Akun:
                    </span>

                    <span
                        class="inline-flex items-center rounded-full border border-zinc-200 bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-zinc-700"
                    >
                        {{ $ledgerData['account']->type === 'D' ? 'Debit' : 'Kredit' }}
                    </span>

                    <span class="hidden text-zinc-300 sm:inline">|</span>

                    <span>
                        Kategori:
                        <span class="font-semibold capitalize text-zinc-800">
                            {{ $ledgerData['account']->category }}
                        </span>
                    </span>
                </div>
            </div>

            {{-- Period --}}
            <form
                action="{{ url()->current() }}"
                method="GET"
                class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white px-3 py-2.5 shadow-sm"
            >
                <label
                    for="periode"
                    class="hidden text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400 sm:block"
                >
                    Periode Laporan
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
        </div>

        {{-- Ledger Table --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            {{-- Table Header --}}
            <div class="flex flex-col gap-1 border-b border-zinc-200 px-5 py-4 sm:px-6">
                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                    Mutasi Buku Besar
                </div>

                <div class="text-sm font-semibold text-zinc-900">
                    Periode {{ $periode }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] border-collapse">

                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50">
                            <th
                                class="w-[12%] px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Tanggal
                            </th>

                            <th
                                class="w-[15%] px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                No. Jurnal
                            </th>

                            <th
                                class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Keterangan Mutasi
                            </th>

                            <th
                                class="w-[15%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Debit
                            </th>

                            <th
                                class="w-[15%] px-4 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Kredit
                            </th>

                            <th
                                class="w-[18%] px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500"
                            >
                                Saldo Akhir Berjalan
                            </th>
                        </tr>
                    </thead>

                    <tbody class="text-sm">

                        @php
                            $totalDebit = 0;
                            $totalKredit = 0;
                        @endphp

                        {{-- Opening Balance --}}
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <td
                                colspan="3"
                                class="px-5 py-4 text-left"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-zinc-200 text-zinc-600">
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
                                                d="M4 19V9m0 0 3 3m-3-3L1 12M20 5v10m0 0-3-3m3 3 3-3"
                                            />
                                        </svg>
                                    </span>

                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-[0.12em] text-zinc-500">
                                            Saldo Awal
                                        </div>

                                        <div class="mt-0.5 text-xs font-semibold text-zinc-800">
                                            Saldo pembuka periode
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4"></td>

                            <td class="px-4 py-4"></td>

                            <td class="px-5 py-4 text-right">
                                <span class="font-mono text-sm font-bold text-zinc-950">
                                    Rp {{ number_format($ledgerData['opening_balance'], 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>

                        {{-- Mutations --}}
                        @forelse($ledgerData['mutasi'] as $row)

                            @php
                                $totalDebit += $row['debit'];
                                $totalKredit += $row['kredit'];
                            @endphp

                            <tr class="border-b border-zinc-100 transition hover:bg-zinc-50/70">

                                {{-- Date --}}
                                <td class="px-5 py-4 align-top">
                                    <span class="whitespace-nowrap text-xs font-medium text-zinc-500">
                                        {{ date('d/m/Y', strtotime($row['tanggal'])) }}
                                    </span>
                                </td>

                                {{-- Journal Number --}}
                                <td class="px-4 py-4 align-top">
                                    <span class="inline-flex rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 py-1 font-mono text-xs font-semibold text-zinc-800">
                                        {{ $row['nomor_jurnal'] }}
                                    </span>
                                </td>

                                {{-- Description --}}
                                <td class="px-4 py-4 align-top">
                                    <span class="text-sm leading-6 text-zinc-700">
                                        {{ $row['keterangan'] }}
                                    </span>
                                </td>

                                {{-- Debit --}}
                                <td class="px-4 py-4 text-right align-top">
                                    @if($row['debit'] > 0)
                                        <span class="font-mono text-sm font-semibold text-emerald-700">
                                            Rp {{ number_format($row['debit'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>

                                {{-- Credit --}}
                                <td class="px-4 py-4 text-right align-top">
                                    @if($row['kredit'] > 0)
                                        <span class="font-mono text-sm font-semibold text-red-600">
                                            Rp {{ number_format($row['kredit'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>

                                {{-- Running Balance --}}
                                <td class="px-5 py-4 text-right align-top">
                                    <span class="font-mono text-sm font-bold text-zinc-950">
                                        Rp {{ number_format($row['saldo_berjalan'], 0, ',', '.') }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            {{-- Empty State --}}
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">

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
                                                d="M6 3h9l3 3v15H6z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M14 3v4h4M9 12h6M9 16h4"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-zinc-900">
                                        Tidak ada mutasi
                                    </h3>

                                    <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-zinc-500">
                                        Tidak ada mutasi transaksi jurnal umum untuk akun ini pada periode terpilih.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                    {{-- Totals --}}
                    @if(count($ledgerData['mutasi']) > 0)

                        <tfoot>

                            {{-- Movement Total --}}
                            <tr class="border-t border-zinc-200 bg-zinc-50">
                                <td
                                    colspan="3"
                                    class="px-5 py-4 text-right"
                                >
                                    <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                                        Total Pergerakan Mutasi Bulan Ini
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <span class="font-mono text-sm font-bold text-emerald-700">
                                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <span class="font-mono text-sm font-bold text-red-600">
                                        Rp {{ number_format($totalKredit, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td class="px-5 py-4"></td>
                            </tr>

                            {{-- Final Balance --}}
                            <tr class="border-t-2 border-zinc-300 bg-zinc-950 text-white">
                                <td
                                    colspan="5"
                                    class="px-5 py-5 text-right"
                                >
                                    <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">
                                        Saldo Akhir Buku Besar
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        Saldo penutupan periode {{ $periode }}
                                    </div>
                                </td>

                                <td class="px-5 py-5 text-right">
                                    <span class="font-mono text-base font-bold tracking-tight text-white">
                                        Rp {{ number_format($ledgerData['final_balance'], 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>

                        </tfoot>

                    @endif

                </table>
            </div>

        </div>

    </div>
</div>

@endsection