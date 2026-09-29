@php
    $paidItems = collect($paidPosts ?? []);
    $paidReach = $paidItems->sum('reach');
    $paidImpressions = $paidItems->sum('impressions');
    $paidClicks = $paidItems->sum('clicks');
    $paidEngagement = $paidItems->sum('engagement');
    $paidSpend = $paidItems->sum('spend');
@endphp

<section data-analysis-source-panel="paid" class="hidden space-y-4" hidden style="display: none;">
    @if($paidError)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <p class="text-[10px] font-bold uppercase tracking-[.16em] text-amber-700">Data Paid belum tersedia</p>
            <h2 class="mt-2 text-lg font-semibold text-amber-950">Periksa koneksi Meta Ads</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-amber-800">{{ $paidError }}</p>
        </div>
    @else
        <div class="rounded-2xl border border-zinc-200 bg-zinc-950 p-6 text-white shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-[.2em] text-lime-300">Paid {{ $platform }}</p>
            <h2 class="mt-2 text-xl font-semibold">Performa konten berbayar</h2>
            <p class="mt-3 max-w-4xl text-sm leading-7 text-zinc-300">
                Terdapat <strong class="text-white">{{ number_format($paidItems->count(), 0, ',', '.') }} iklan</strong>
                pada placement {{ $platform }} dengan total reach {{ number_format($paidReach, 0, ',', '.') }},
                {{ number_format($paidImpressions, 0, ',', '.') }} impresi, dan {{ number_format($paidClicks, 0, ',', '.') }} klik.
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Iklan', 'value' => number_format($paidItems->count(), 0, ',', '.')],
                ['label' => 'Reach', 'value' => number_format($paidReach, 0, ',', '.')],
                ['label' => 'Impresi', 'value' => number_format($paidImpressions, 0, ',', '.')],
                ['label' => 'Klik', 'value' => number_format($paidClicks, 0, ',', '.')],
                ['label' => 'Spend', 'value' => ($paidCurrency ?? 'IDR').' '.number_format($paidSpend, 0, ',', '.')],
            ] as $metric)
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $metric['label'] }}</p>
                    <p class="mt-2 break-words text-xl font-semibold text-zinc-950">{{ $metric['value'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h3 class="text-base font-semibold text-zinc-950">Daftar konten Paid {{ $platform }}</h3>
                <p class="mt-1 text-xs text-zinc-500">Data dikelompokkan berdasarkan iklan dan placement publisher platform.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[920px] w-full text-left text-xs">
                    <thead class="bg-zinc-50 text-[10px] uppercase tracking-wider text-zinc-500">
                        <tr><th class="px-5 py-3">Iklan</th><th class="px-4 py-3">Campaign</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3 text-right">Reach</th><th class="px-4 py-3 text-right">Impresi</th><th class="px-4 py-3 text-right">Klik</th><th class="px-4 py-3 text-right">Engagement</th><th class="px-5 py-3 text-right">Spend</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($paidItems as $item)
                            <tr class="text-zinc-700 hover:bg-zinc-50">
                                <td class="max-w-[240px] px-5 py-3 font-semibold text-zinc-950">{{ $item['judul'] }}</td>
                                <td class="max-w-[220px] px-4 py-3">{{ $item['campaign'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $item['tanggal_posting'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item['reach'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item['impressions'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item['clicks'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($item['engagement'], 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">{{ ($paidCurrency ?? 'IDR').' '.number_format($item['spend'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-14 text-center text-sm text-zinc-500">Belum ada iklan {{ $platform }} pada periode yang dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
