@php
    $trendLabels = [
        'posts' => 'Jumlah konten', 'reach' => 'Reach', 'impressions' => 'Impresi',
        'engagement' => 'Engagement', 'likes' => 'Like/Reaksi', 'comments' => 'Komentar',
        'shares' => 'Share', 'clicks' => 'Klik', 'spend' => 'Biaya',
    ];
    $trendPeriods = ['three_days' => 3, 'seven_days' => 7];
@endphp

@foreach($trendPeriods as $periodKey => $dayCount)
    <section data-analysis-period-panel="{{ $periodKey }}" hidden style="display:none;" class="space-y-4">
        @foreach(['organic' => 'Organik', 'paid' => 'Paid'] as $sourceKey => $sourceLabel)
            @php
                $comparison = data_get($trendComparisons ?? [], $sourceKey.'.'.$periodKey, []);
                $metrics = collect($comparison['metrics'] ?? []);
                $positive = $metrics->filter(fn ($metric) => ($metric['delta'] ?? 0) > 0)->count();
                $negative = $metrics->filter(fn ($metric) => ($metric['delta'] ?? 0) < 0)->count();
            @endphp
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-zinc-400">{{ $sourceLabel }} · {{ $platform }}</p>
                        <h2 class="mt-1 text-lg font-semibold text-zinc-950">Perubahan dalam {{ $dayCount }} hari</h2>
                    </div>
                    @if($comparison['available'] ?? false)
                        <span class="rounded-full bg-zinc-100 px-3 py-1 text-[11px] font-semibold text-zinc-600">Dibandingkan {{ \Carbon\Carbon::parse($comparison['previous_date'])->format('d M Y') }}</span>
                    @endif
                </div>

                @if(!($comparison['available'] ?? false))
                    <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-sm text-zinc-600">
                        Snapshot {{ $dayCount }} hari sebelumnya belum tersedia. Sistem mulai menyimpan data internal hari ini dan perbandingan akan muncul otomatis setelah riwayat mencukupi.
                    </div>
                @else
                    <p class="mb-4 text-sm text-zinc-600">
                        @if($positive && !$negative)
                            Performa meningkat pada {{ $positive }} metrik dibandingkan {{ $dayCount }} hari sebelumnya.
                        @elseif($negative && !$positive)
                            Performa menurun pada {{ $negative }} metrik dibandingkan {{ $dayCount }} hari sebelumnya.
                        @else
                            Terdapat {{ $positive }} metrik meningkat dan {{ $negative }} metrik menurun dibandingkan {{ $dayCount }} hari sebelumnya.
                        @endif
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach($metrics as $key => $metric)
                            @php
                                $delta = (float) ($metric['delta'] ?? 0);
                                $percent = $metric['percent'] ?? null;
                                $isSpend = $key === 'spend';
                            @endphp
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $trendLabels[$key] ?? ucfirst($key) }}</p>
                                <p class="mt-2 text-xl font-semibold text-zinc-950">{{ $isSpend ? ($paidCurrency ?? 'IDR').' ' : '' }}{{ number_format($metric['current'] ?? 0, $isSpend ? 0 : 0, ',', '.') }}</p>
                                <p class="mt-1 text-xs font-semibold {{ $delta > 0 ? 'text-emerald-700' : ($delta < 0 ? 'text-rose-700' : 'text-zinc-500') }}">
                                    {{ $delta > 0 ? 'Naik' : ($delta < 0 ? 'Turun' : 'Tetap') }}
                                    {{ $isSpend ? ($paidCurrency ?? 'IDR').' ' : '' }}{{ number_format(abs($delta), 0, ',', '.') }}
                                    @if($percent !== null) ({{ number_format(abs($percent), 1, ',', '.') }}%) @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </section>
@endforeach
