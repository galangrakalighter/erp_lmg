@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                    Finance / General Ledger
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    {{ $title }}
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                    Silahkan pilih salah satu akun di bawah untuk melihat mutasi Buku Besar secara real-time.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                {{-- Form Periode --}}
                <form
                    action="{{ url()->current() }}"
                    method="GET"
                    class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white px-3 py-2.5 shadow-sm"
                >
                    {{-- Pertahankan akun aktif saat periode diganti --}}
                    @if(request('chart_of_account_id'))
                        <input
                            type="hidden"
                            name="chart_of_account_id"
                            value="{{ request('chart_of_account_id') }}"
                        >
                    @endif

                    <label
                        for="periode"
                        class="hidden text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400 sm:block"
                    >
                        Periode Buku
                    </label>

                    <input
                        type="month"
                        name="periode"
                        id="periode"
                        value="{{ $periode }}"
                        onchange="this.form.submit()"
                        class="rounded-lg border-0 bg-zinc-100 px-3 py-2 text-sm font-semibold text-zinc-900 outline-none ring-0 transition focus:bg-zinc-200 focus:ring-2 focus:ring-zinc-300"
                    >
                </form>

                {{-- Export PDF --}}
                <button
                    type="button"
                    onclick="exportLedgerPdf()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2"
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

                    <span>Export PDF</span>
                </button>
            </div>
        </div>

        {{-- Account Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @foreach($accounts as $account)

                @php
                    $isDebit = strtoupper($account->type) === 'D';
                @endphp

                <a
                    href="{{ route('finance_ledger.show', [
                        'perusahaan' => $id,
                        'id' => $account->id,
                        'periode' => $periode
                    ]) }}"
                    class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-xl"
                >

                    {{-- Accent --}}
                    <div
                        class="absolute inset-x-0 top-0 h-1 {{ $isDebit ? 'bg-zinc-950' : 'bg-zinc-400' }}"
                    ></div>

                    <div class="p-5">

                        {{-- Top --}}
                        <div class="mb-4 flex items-start justify-between gap-3">

                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                                    Kode Akun
                                </div>

                                <div class="mt-1 font-mono text-sm font-semibold text-zinc-950">
                                    {{ $account->kode }}
                                </div>
                            </div>

                            <span
                                class="inline-flex min-w-[68px] items-center justify-center rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide
                                {{ $isDebit
                                    ? 'border-zinc-200 bg-zinc-100 text-zinc-800'
                                    : 'border-zinc-300 bg-white text-zinc-600'
                                }}"
                            >
                                {{ $isDebit ? 'Debit' : 'Kredit' }}
                            </span>
                        </div>

                        {{-- Account Name --}}
                        <h2
                            class="min-h-[48px] text-base font-semibold leading-6 text-zinc-900 transition group-hover:text-zinc-600"
                            title="{{ $account->nama_akun }}"
                        >
                            {{ $account->nama_akun }}
                        </h2>

                        {{-- Divider --}}
                        <div class="my-4 border-t border-dashed border-zinc-200"></div>

                        {{-- Balance --}}
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <div class="text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                    Saldo Saat Ini
                                </div>

                                <div class="mt-1 text-xs text-zinc-500">
                                    Periode {{ $periode }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-mono text-sm font-bold tracking-tight text-zinc-950">
                                    Rp {{ number_format($account->saldo_saat_ini, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        {{-- View indicator --}}
                        <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4">
                            <span class="text-xs font-medium text-zinc-400">
                                Lihat mutasi akun
                            </span>

                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 transition group-hover:bg-zinc-950 group-hover:text-white"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12h14m-6-6 6 6-6 6"
                                    />
                                </svg>
                            </span>
                        </div>

                    </div>
                </a>

            @endforeach

        </div>

        {{-- Empty State --}}
        @if($accounts->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 7.5 12 3l9 4.5M3 7.5 12 12m0 0 9-4.5M12 12v9"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 7.5V17l9 4 9-4V7.5"
                        />
                    </svg>
                </div>

                <h3 class="mt-4 text-sm font-semibold text-zinc-900">
                    Belum ada akun
                </h3>

                <p class="mt-1 text-sm text-zinc-500">
                    Tidak terdapat akun Buku Besar yang tersedia untuk periode ini.
                </p>
            </div>
        @endif

    </div>
</div>

<script>
    function exportLedgerPdf() {
        const periodeElement = document.getElementById('periode');

        const periode = periodeElement
            ? periodeElement.value
            : '';

        const accountElement = document.getElementById('chart_of_account_id');

        const accountId = accountElement
            ? accountElement.value
            : '';

        const params = new URLSearchParams({
            periode: periode,
            chart_of_account_id: accountId
        });

        window.open(
            `{{ route('ledger.export-pdf') }}?${params.toString()}`,
            '_blank'
        );
    }
</script>

@endsection