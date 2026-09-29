@php
    $paidItems = collect($paidPosts ?? []);
    $paidReach = $paidItems->sum('reach');
    $paidImpressions = $paidItems->sum('impressions');
    $paidClicks = $paidItems->sum('clicks');
    $paidEngagement = $paidItems->sum('engagement');
    $paidSpend = $paidItems->sum('spend');
    $paidCtr = $paidImpressions > 0 ? ($paidClicks / $paidImpressions) * 100 : 0;
    $paidCpm = $paidImpressions > 0 ? ($paidSpend / $paidImpressions) * 1000 : 0;
    $paidCpc = $paidClicks > 0 ? $paidSpend / $paidClicks : 0;
    $paidFrequency = $paidReach > 0 ? $paidImpressions / $paidReach : 0;
    $paidMetricDefinitions = [
        'ads' => ['Jumlah Iklan', number_format($paidItems->count(), 0, ',', '.')],
        'reach' => ['Reach', number_format($paidReach, 0, ',', '.')],
        'impressions' => ['Impresi', number_format($paidImpressions, 0, ',', '.')],
        'clicks' => ['Klik', number_format($paidClicks, 0, ',', '.')],
        'engagement' => ['Engagement', number_format($paidEngagement, 0, ',', '.')],
        'spend' => ['Spend', ($paidCurrency ?? 'IDR').' '.number_format($paidSpend, 0, ',', '.')],
        'ctr' => ['CTR', number_format($paidCtr, 2, ',', '.').'%'],
        'cpm' => ['CPM', ($paidCurrency ?? 'IDR').' '.number_format($paidCpm, 0, ',', '.')],
        'cpc' => ['CPC', ($paidCurrency ?? 'IDR').' '.number_format($paidCpc, 0, ',', '.')],
        'frequency' => ['Frekuensi', number_format($paidFrequency, 2, ',', '.').'x'],
    ];
@endphp

<section data-analysis-source-panel="paid" class="hidden space-y-4" hidden style="display: none;">
    @if($paidError)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <p class="text-[10px] font-bold uppercase tracking-[.16em] text-amber-700">Data Paid belum tersedia</p>
            <h2 class="mt-2 text-lg font-semibold text-amber-950">Periksa koneksi Meta Ads</h2>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-amber-800">{{ $paidError }}</p>
        </div>
    @else
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm" role="tablist" aria-label="Jenis tampilan analisis Paid">
            <div class="flex flex-wrap gap-1">
                <button type="button" data-paid-analysis-tab="descriptive" aria-selected="true" class="rounded-lg bg-zinc-950 px-4 py-2.5 text-xs font-bold text-white">Deskriptif</button>
                <button type="button" data-paid-analysis-tab="metrics" aria-selected="false" class="rounded-lg px-4 py-2.5 text-xs font-bold text-zinc-500">Metrik &amp; Tabel</button>
            </div>
            <div class="meta-field-picker mr-1">
                <button type="button" class="meta-v2-btn" data-paid-field-toggle>Atur Field Paid</button>
                <div class="meta-field-menu" data-paid-field-menu hidden>
                    <h4>Field Paid <span data-paid-field-count style="float:right;color:#71717a;font-weight:500"></span></h4>
                    <div class="meta-field-options">
                        @foreach($paidMetricDefinitions as $fieldKey => $definition)
                            <label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-paid-field-checkbox><span>{{ $definition[0] }}</span></label>
                        @endforeach
                    </div>
                    <p style="margin:9px 0 0;color:#a1a1aa;font-size:9px">Maksimal 9 field dapat ditampilkan.</p>
                </div>
            </div>
        </div>

        <div data-paid-analysis-panel="descriptive">
            <div class="rounded-2xl border border-zinc-200 bg-zinc-950 p-6 text-white shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-[.2em] text-lime-300">Paid {{ $platform }}</p>
                <h2 class="mt-2 text-xl font-semibold">Performa konten berbayar</h2>
                <p class="mt-3 max-w-4xl text-sm leading-7 text-zinc-300">
                    Terdapat <strong class="text-white">{{ number_format($paidItems->count(), 0, ',', '.') }} iklan</strong>
                    pada placement {{ $platform }} dengan total reach {{ number_format($paidReach, 0, ',', '.') }},
                    {{ number_format($paidImpressions, 0, ',', '.') }} impresi, dan {{ number_format($paidClicks, 0, ',', '.') }} klik.
                </p>
            </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
            @foreach($paidMetricDefinitions as $fieldKey => $metric)
                <article data-paid-field="{{ $fieldKey }}" class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $metric[0] }}</p>
                    <p class="mt-2 break-words text-xl font-semibold text-zinc-950">{{ $metric[1] }}</p>
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
                                <td class="max-w-[240px] px-5 py-3 font-semibold text-zinc-950" title="{{ $item['judul'] }}">{{ \Illuminate\Support\Str::limit($item['judul'], 55) }}</td>
                                <td class="max-w-[220px] px-4 py-3" title="{{ $item['campaign'] }}">{{ \Illuminate\Support\Str::limit($item['campaign'], 45) }}</td>
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
        </div>

        <div data-paid-analysis-panel="metrics" hidden style="display:none;" class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($paidMetricDefinitions as $fieldKey => $metric)
                    <article data-paid-field="{{ $fieldKey }}" class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $metric[0] }}</p>
                        <p class="mt-2 break-words text-2xl font-semibold text-zinc-950">{{ $metric[1] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-zinc-950">Daftar iklan</h3>
                        <p class="mt-1 text-xs text-zinc-500">Data iklan Paid {{ $platform }} pada periode terpilih.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-[620px] w-full text-left text-xs">
                            <thead class="bg-zinc-50 text-[10px] uppercase tracking-wider text-zinc-500">
                                <tr><th class="px-5 py-3">Iklan</th><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3 text-right">Reach</th><th class="px-5 py-3 text-right">Impresi</th></tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @forelse($paidItems as $item)
                                    <tr class="text-zinc-700 hover:bg-zinc-50">
                                        <td class="max-w-[250px] px-5 py-3 font-semibold text-zinc-950" title="{{ $item['judul'] }}">{{ \Illuminate\Support\Str::limit($item['judul'], 55) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">{{ $item['tanggal_posting'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($item['reach'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-3 text-right">{{ number_format($item['impressions'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-14 text-center text-sm text-zinc-500">Belum ada data iklan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm">
                    <div class="border-b border-zinc-200 px-5 py-4">
                        <h3 class="text-base font-semibold text-zinc-950">Reach vs Impresi</h3>
                        <p class="mt-1 text-xs text-zinc-500">Perbandingan performa setiap iklan Paid {{ $platform }}.</p>
                    </div>
                    <div class="relative p-5" style="height: 390px;">
                        <canvas data-paid-metrics-chart></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>

<style>[data-paid-analysis-panel][hidden], [data-paid-field][hidden] { display: none !important; }</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paidChartData = @json($paidItems->values());
    const paidChartCanvas = document.querySelector('[data-paid-metrics-chart]');
    let paidMetricsChart = null;

    function renderPaidMetricsChart() {
        if (!paidChartCanvas || typeof Chart === 'undefined') return;
        if (paidMetricsChart) {
            paidMetricsChart.resize();
            return;
        }
        paidMetricsChart = new Chart(paidChartCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: paidChartData.map(item => {
                    const title = item.judul || item.tanggal_posting || 'Iklan';
                    return title.length > 28 ? `${title.slice(0, 28)}…` : title;
                }),
                datasets: [
                    { label: 'Reach', data: paidChartData.map(item => Number(item.reach || 0)), backgroundColor: '#4f46e5', borderRadius: 5, order: 2 },
                    { label: 'Impresi', data: paidChartData.map(item => Number(item.impressions || 0)), type: 'line', borderColor: '#fb7185', backgroundColor: 'transparent', tension: .35, order: 1 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    function activatePaidAnalysis(active) {
        document.querySelectorAll('[data-paid-analysis-panel]').forEach(function (panel) {
            const selected = panel.dataset.paidAnalysisPanel === active;
            panel.hidden = !selected;
            panel.style.display = selected ? '' : 'none';
        });
        document.querySelectorAll('[data-paid-analysis-tab]').forEach(function (tab) {
            const selected = tab.dataset.paidAnalysisTab === active;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.classList.toggle('bg-zinc-950', selected);
            tab.classList.toggle('text-white', selected);
            tab.classList.toggle('text-zinc-500', !selected);
        });
        if (active === 'metrics') {
            requestAnimationFrame(renderPaidMetricsChart);
        }
    }

    activatePaidAnalysis('descriptive');
    document.querySelectorAll('[data-paid-analysis-tab]').forEach(function (button) {
        button.addEventListener('click', function () {
            activatePaidAnalysis(button.dataset.paidAnalysisTab);
            window.dispatchEvent(new Event('resize'));
        });
    });

    const maxPaidFields = 9;
    const paidStorageKey = 'meta-analysis-paid-fields-{{ strtolower($platform) }}';
    const paidDefaults = ['ads', 'reach', 'impressions', 'clicks', 'engagement', 'spend'];
    const paidCards = [...document.querySelectorAll('[data-paid-field]')];
    const paidChecks = [...document.querySelectorAll('[data-paid-field-checkbox]')];
    const paidMenu = document.querySelector('[data-paid-field-menu]');
    const paidToggle = document.querySelector('[data-paid-field-toggle]');
    const paidCount = document.querySelector('[data-paid-field-count]');
    let selectedPaidFields;
    try { selectedPaidFields = JSON.parse(localStorage.getItem(paidStorageKey)); } catch (error) { selectedPaidFields = null; }
    if (!Array.isArray(selectedPaidFields) || !selectedPaidFields.length) selectedPaidFields = paidDefaults;
    selectedPaidFields = selectedPaidFields.slice(0, maxPaidFields);

    function applyPaidFields() {
        paidCards.forEach(card => card.hidden = !selectedPaidFields.includes(card.dataset.paidField));
        paidChecks.forEach(check => {
            check.checked = selectedPaidFields.includes(check.value);
            check.disabled = !check.checked && selectedPaidFields.length >= maxPaidFields;
        });
        if (paidCount) paidCount.textContent = `${selectedPaidFields.length}/${maxPaidFields}`;
    }

    paidToggle?.addEventListener('click', function (event) {
        event.stopPropagation();
        paidMenu.hidden = !paidMenu.hidden;
    });
    paidChecks.forEach(check => check.addEventListener('change', function () {
        const next = paidChecks.filter(item => item.checked).map(item => item.value);
        if (!next.length || next.length > maxPaidFields) {
            check.checked = !check.checked;
            return;
        }
        selectedPaidFields = next;
        localStorage.setItem(paidStorageKey, JSON.stringify(selectedPaidFields));
        applyPaidFields();
    }));
    document.addEventListener('click', function (event) {
        if (paidMenu && !event.target.closest('[data-paid-field-toggle]') && !event.target.closest('[data-paid-field-menu]')) paidMenu.hidden = true;
    });
    localStorage.setItem(paidStorageKey, JSON.stringify(selectedPaidFields));
    applyPaidFields();
});
</script>
