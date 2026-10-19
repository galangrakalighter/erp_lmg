<section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
    <div class="border-b border-zinc-200 px-5 py-4"><h2 class="font-semibold text-zinc-950">Rincian Invoice & Pembayaran</h2><p class="mt-1 text-xs text-zinc-500">Invoice utama dan pembayaran yang terhubung ditampilkan secara berurutan.</p></div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[950px] text-left text-sm">
            <thead class="bg-zinc-950 text-[10px] uppercase tracking-[.12em] text-white"><tr><th class="px-5 py-3.5">Transaksi</th><th class="px-4 py-3.5">Tanggal</th><th class="px-4 py-3.5">Metode</th><th class="px-4 py-3.5 text-right">Jumlah</th><th class="px-5 py-3.5 text-right">Sisa</th></tr></thead>
            <tbody class="divide-y divide-zinc-100">
            @forelse($clients as $cli)
                @if($grouped)<tr class="bg-zinc-100"><td colspan="5" class="px-5 py-3 text-xs font-bold uppercase tracking-wide text-zinc-800">{{ $cli->nama }}</td></tr>@endif
                @forelse($cli->invoices as $inv)
                    @php $paidInv = $inv->payments->sum('amount'); $remainingInv = $inv->total_amount - $paidInv; @endphp
                    <tr class="bg-white transition hover:bg-zinc-50"><td class="px-5 py-4"><p class="font-semibold text-zinc-950">Invoice {{ $inv->no_invoice ?? '#'.$inv->id }}</p><p class="mt-1 text-xs text-zinc-400">Piutang client</p></td><td class="whitespace-nowrap px-4 py-4 text-zinc-600">{{ optional($inv->created_at)->format('d M Y') ?? '-' }}</td><td class="px-4 py-4 text-zinc-400">—</td><td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-zinc-950">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td><td class="whitespace-nowrap px-5 py-4 text-right font-bold {{ $remainingInv > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($remainingInv, 0, ',', '.') }}</td></tr>
                    @forelse($inv->payments as $pay)
                        <tr class="bg-emerald-50/40"><td class="px-5 py-3 pl-9"><p class="text-xs font-semibold text-emerald-700">↳ Pembayaran masuk</p>@if($pay->file_path)<a href="{{ $pay->file_url }}" target="_blank" class="mt-1 inline-block text-[11px] font-medium text-blue-600 hover:underline">Lihat bukti pembayaran</a>@endif</td><td class="whitespace-nowrap px-4 py-3 text-xs text-zinc-600">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td><td class="px-4 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase text-emerald-700">{{ $pay->method ?: 'Lainnya' }}</span></td><td class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold text-emerald-700">+ Rp {{ number_format($pay->amount, 0, ',', '.') }}</td><td class="px-5 py-3 text-right text-zinc-400">—</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-9 py-3 text-xs italic text-zinc-400">Belum ada pembayaran untuk invoice ini.</td></tr>
                    @endforelse
                @empty
                    <tr><td colspan="5" class="px-5 py-4 text-sm text-zinc-400">Belum ada invoice.</td></tr>
                @endforelse
            @empty
                <tr><td colspan="5" class="px-5 py-14 text-center text-zinc-500">Belum ada data transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
