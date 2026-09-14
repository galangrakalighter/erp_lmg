@extends('layout.app')
@section('content')
@php
    $cashVolume = abs($cashflowIn) + abs($cashflowOut);
    $cashInPercent = $cashVolume > 0 ? (abs($cashflowIn) / $cashVolume) * 100 : 0;
    $cashOutPercent = $cashVolume > 0 ? 100 - $cashInPercent : 0;
    $revenueVolume = abs($totalPendapatan) + abs($totalBeban);
    $revenuePercent = $revenueVolume > 0 ? (abs($totalPendapatan) / $revenueVolume) * 100 : 0;
    $expensePercent = $revenueVolume > 0 ? 100 - $revenuePercent : 0;
@endphp
<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
<div class="mx-auto max-w-[1600px] space-y-6">
    <section class="relative overflow-hidden rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div><p class="text-[11px] font-semibold uppercase tracking-[.22em] text-lime-300">Lighter Media Group · Finance</p><h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Dashboard Keuangan</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">Ringkasan kondisi finansial {{ $perusahaan_baru->nama_perusahaan ?? 'perusahaan' }} berdasarkan jurnal perusahaan.</p></div>
            <form action="{{ route('finance.dashboard', $id) }}" method="GET"><label class="block"><span class="mb-1.5 block text-[10px] font-bold uppercase tracking-[.14em] text-zinc-400">Periode Laporan</span><input type="month" name="periode" id="periode" value="{{ $periode }}" onchange="this.form.submit()" class="w-full rounded-xl border border-white/15 bg-white/10 px-3 py-2.5 text-sm font-semibold text-white outline-none transition [color-scheme:dark] focus:border-lime-300 focus:ring-2 focus:ring-lime-300/20 sm:w-48"></label></form>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[.14em] text-zinc-500">{{ $totalProfit >= 0 ? 'Profit Bersih' : 'Rugi Bersih' }}</p><p class="mt-2 text-2xl font-bold tracking-tight {{ $totalProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $totalProfit >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V9m6 10V5m6 14v-7m4 7H2"/></svg></span></div>
            <div class="mt-5 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-4"><div><p class="text-[10px] uppercase text-zinc-400">Pendapatan</p><p class="mt-1 text-xs font-semibold text-emerald-700">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p></div><div class="text-right"><p class="text-[10px] uppercase text-zinc-400">Beban</p><p class="mt-1 text-xs font-semibold text-rose-600">Rp {{ number_format($totalBeban, 0, ',', '.') }}</p></div></div>
        </article>
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[.14em] text-zinc-500">Arus Kas Bersih</p><p class="mt-2 text-2xl font-bold tracking-tight {{ $netCashflow >= 0 ? 'text-zinc-950' : 'text-rose-700' }}">Rp {{ number_format($netCashflow, 0, ',', '.') }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M16 13h3M7 6V4h10v2"/></svg></span></div>
            <div class="mt-5 grid grid-cols-2 gap-2 border-t border-zinc-100 pt-4"><div><p class="text-[10px] uppercase text-zinc-400">Kas masuk</p><p class="mt-1 text-xs font-semibold text-blue-700">Rp {{ number_format($cashflowIn, 0, ',', '.') }}</p></div><div class="text-right"><p class="text-[10px] uppercase text-zinc-400">Kas keluar</p><p class="mt-1 text-xs font-semibold text-amber-700">Rp {{ number_format($cashflowOut, 0, ',', '.') }}</p></div></div>
        </article>
        <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm md:col-span-2 xl:col-span-1">
            <div class="flex items-start justify-between"><div><p class="text-[10px] font-bold uppercase tracking-[.14em] text-zinc-500">Total Saldo Aset</p><p class="mt-2 text-2xl font-bold tracking-tight text-zinc-950">Rp {{ number_format($totalSaldoUtama, 0, ',', '.') }}</p></div><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-lime-50 text-lime-800"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h18M5 10v8m4-8v8m6-8v8m4-8v8M2 21h20M12 3 3 7h18Z"/></svg></span></div>
            <div class="mt-5 border-t border-zinc-100 pt-4"><p class="text-xs leading-5 text-zinc-500">Akumulasi {{ $mainBalances->count() }} akun aset perusahaan yang tercatat pada jurnal.</p></div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <article class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-5 py-4"><div><h2 class="font-semibold text-zinc-950">Rincian Saldo Akun Aset</h2><p class="mt-1 text-xs text-zinc-500">Kas, bank, dan akun aset lainnya.</p></div><span class="rounded-full bg-zinc-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-zinc-600">{{ $mainBalances->count() }} Akun</span></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[720px] text-left text-sm"><thead class="bg-zinc-950 text-[10px] font-semibold uppercase tracking-[.12em] text-white"><tr><th class="px-5 py-3.5">Kode</th><th class="px-4 py-3.5">Nama Akun</th><th class="px-4 py-3.5">Tipe</th><th class="px-5 py-3.5 text-right">Saldo Terkini</th></tr></thead><tbody class="divide-y divide-zinc-100">@forelse($mainBalances as $acc)<tr class="transition hover:bg-zinc-50"><td class="px-5 py-4 font-mono text-xs text-zinc-500">{{ $acc->kode }}</td><td class="px-4 py-4 font-semibold text-zinc-950">{{ $acc->nama_akun }}</td><td class="px-4 py-4"><span class="rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[10px] font-bold uppercase text-zinc-600">{{ $acc->type }}</span></td><td class="whitespace-nowrap px-5 py-4 text-right font-bold {{ $acc->balance >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">Rp {{ number_format($acc->balance, 0, ',', '.') }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-14 text-center text-sm text-zinc-500">Tidak ada data akun aset.</td></tr>@endforelse</tbody><tfoot><tr class="bg-zinc-100"><th colspan="3" class="px-5 py-4 text-right text-[10px] font-bold uppercase tracking-wide text-zinc-600">Total Keseluruhan Aset</th><th class="px-5 py-4 text-right text-sm font-bold text-zinc-950">Rp {{ number_format($totalSaldoUtama, 0, ',', '.') }}</th></tr></tfoot></table></div>
        </article>

        <aside class="space-y-4">
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-zinc-400">Komposisi Laba Rugi</p><div class="mt-4 flex items-end justify-between text-xs"><span class="font-semibold text-emerald-700">Pendapatan {{ number_format($revenuePercent, 0) }}%</span><span class="text-zinc-400">Beban {{ number_format($expensePercent, 0) }}%</span></div><div class="mt-2 flex h-2 overflow-hidden rounded-full bg-rose-200"><div class="h-full bg-emerald-500" style="width: {{ $revenuePercent }}%"></div></div></article>
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-zinc-400">Komposisi Arus Kas</p><div class="mt-4 flex items-end justify-between text-xs"><span class="font-semibold text-blue-700">Masuk {{ number_format($cashInPercent, 0) }}%</span><span class="text-zinc-400">Keluar {{ number_format($cashOutPercent, 0) }}%</span></div><div class="mt-2 flex h-2 overflow-hidden rounded-full bg-amber-200"><div class="h-full bg-blue-500" style="width: {{ $cashInPercent }}%"></div></div></article>
            <article class="rounded-2xl bg-zinc-950 p-5 text-white shadow-lg"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-lime-300">Akses Cepat</p><h3 class="mt-2 font-semibold">Penutupan Periode</h3><p class="mt-2 text-xs leading-5 text-zinc-400">Tinjau dan kelola jurnal penutup untuk periode laporan perusahaan.</p><a href="{{ route('Ledger.jurnal_penutup', $id) }}" class="mt-5 inline-flex w-full items-center justify-between rounded-xl bg-white px-4 py-3 text-xs font-bold text-zinc-950 transition hover:bg-lime-300"><span>Lihat Jurnal Penutup</span><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></a></article>
        </aside>
    </section>
</div>
</div>
@endsection
