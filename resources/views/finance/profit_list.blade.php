@extends('layout.app')
@section('content')
<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
<div class="mx-auto max-w-[1600px] space-y-6">
    <section class="relative overflow-hidden rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
        <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div><p class="text-[11px] font-semibold uppercase tracking-[.22em] text-lime-300">Lighter Media Group · Finance</p><h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Profit per Client</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">Pantau invoice, pembayaran masuk, dan sisa piutang setiap client.</p></div>
            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                <span class="text-right"><span class="block text-xs font-semibold">Tampilkan rincian</span><span class="block text-[11px] text-zinc-400">Seluruh invoice dan pembayaran</span></span>
                <input id="viewToggleSwitch" type="checkbox" class="peer sr-only" {{ ($viewMode ?? 'off') === 'on' ? 'checked' : '' }}>
                <span class="relative h-6 w-11 rounded-full bg-zinc-700 transition peer-checked:bg-lime-300 after:absolute after:left-1 after:top-1 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
            </label>
        </div>
    </section>

    @if(($viewMode ?? 'off') === 'off')
        <div><h2 class="text-lg font-semibold text-zinc-950">Ringkasan Client</h2><p class="mt-1 text-sm text-zinc-500">{{ $clients->count() }} client memiliki transaksi invoice.</p></div>
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($clients as $c)
            @php $paidPercentage = $c->total_invoice > 0 ? min(100, ($c->total_bayar / $c->total_invoice) * 100) : 0; @endphp
            <a href="{{ route('profit.detail', $c->id) }}" class="group rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-lg">
                <div class="flex items-start justify-between gap-4"><div class="min-w-0"><p class="text-[10px] font-semibold uppercase tracking-[.16em] text-zinc-400">Client</p><h3 class="mt-1 truncate text-base font-semibold text-zinc-950">{{ $c->nama }}</h3></div><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-100 transition group-hover:bg-zinc-950 group-hover:text-lime-300"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg></span></div>
                <div class="mt-5 grid grid-cols-2 gap-3"><div class="rounded-xl bg-zinc-50 p-3"><p class="text-[10px] uppercase tracking-wide text-zinc-500">Total invoice</p><p class="mt-1 text-sm font-semibold text-zinc-900">Rp {{ number_format($c->total_invoice, 0, ',', '.') }}</p></div><div class="rounded-xl bg-lime-50 p-3"><p class="text-[10px] uppercase tracking-wide text-lime-700">Dibayar</p><p class="mt-1 text-sm font-semibold text-lime-800">Rp {{ number_format($c->total_bayar, 0, ',', '.') }}</p></div></div>
                <div class="mt-4"><div class="mb-2 flex justify-between text-xs"><span class="text-zinc-500">Progres pembayaran</span><span class="font-semibold">{{ number_format($paidPercentage, 0) }}%</span></div><div class="h-2 overflow-hidden rounded-full bg-zinc-100"><div class="h-full rounded-full bg-lime-400" style="width: {{ $paidPercentage }}%"></div></div></div>
                <div class="mt-5 flex items-center justify-between border-t border-zinc-100 pt-4"><span class="text-xs font-medium text-zinc-500">Sisa piutang</span><span class="text-sm font-bold {{ $c->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($c->balance, 0, ',', '.') }}</span></div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center text-sm text-zinc-500">Belum ada data client yang memiliki invoice.</div>
        @endforelse
        </section>
    @else
        <section class="grid gap-4 md:grid-cols-3">
        @foreach([['Total Invoice', $totalInvoiceAll, 'text-zinc-950'], ['Pembayaran Masuk', $totalBayarAll, 'text-emerald-700'], ['Sisa Piutang', $balanceAll, $balanceAll > 0 ? 'text-rose-700' : 'text-emerald-700']] as [$label, $amount, $color])
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm"><p class="text-[11px] font-semibold uppercase tracking-[.14em] text-zinc-500">{{ $label }}</p><p class="mt-3 text-xl font-bold tracking-tight {{ $color }} sm:text-2xl">Rp {{ number_format($amount, 0, ',', '.') }}</p></article>
        @endforeach
        </section>
        @include('finance.partials.profit_transaction_table', ['clients' => $clients, 'grouped' => true])
    @endif
</div>
</div>
<script>document.getElementById('viewToggleSwitch')?.addEventListener('change',function(){const url=new URL(window.location.href);url.searchParams.set('view',this.checked?'on':'off');window.location.href=url.toString()});</script>
@endsection
