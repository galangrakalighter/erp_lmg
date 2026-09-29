@php
    $metaItems = collect($organicItems ?? []);
    $metaReach = $metaItems->sum('reach'); $metaImpressions = $metaItems->sum('impressions');
    $metaEngagement = $metaItems->sum('engagement'); $metaSaves = $metaItems->sum('saved'); $metaShares = $metaItems->sum('shares');
    $metaLikes = $metaItems->sum('likes'); $metaComments = $metaItems->sum('comments');
    $metaEr = $metaReach > 0 ? ($metaEngagement / $metaReach) * 100 : 0;
    $metaFrequency = $metaReach > 0 ? $metaImpressions / $metaReach : 0;
    $topMetaItems = $metaItems->sortByDesc('engagement')->take(4)->values();
    $metaMetricDefinitions = [
        'posts' => ['Jumlah Konten', $metaItems->count()], 'reach' => ['Reach', $metaReach],
        'impressions' => ['Impresi', $metaImpressions], 'engagement' => ['Engagement', $metaEngagement],
        'engagement_rate' => ['Engagement Rate', number_format($metaEr,2,',','.').'%'],
        'likes' => ['Like / Reaksi', $metaLikes], 'comments' => ['Komentar', $metaComments],
        'saved' => ['Saves', $metaSaves], 'shares' => ['Shares', $metaShares],
        'followers' => ['Followers', $followerMetric ?? 0], 'frequency' => ['Frekuensi', number_format($metaFrequency,2,',','.').'x'],
    ];
@endphp
@include('partials.client_data_theme')
@include('partials.client_native_tables')
@include('partials.analysis_workspace_theme')
@include('partials.meta_analysis_dashboard_theme')
<style>
.meta-v2-shell{color:#27272a}.meta-v2-top{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:22px}.meta-v2-title h1{margin:0;font-size:26px;font-weight:760;letter-spacing:-.035em}.meta-v2-title p{margin:5px 0 0;color:#71717a;font-size:12px}.meta-v2-controls{display:flex;align-items:center;gap:9px}.meta-v2-control{display:flex;min-height:42px;align-items:center;gap:8px;border:1px solid #e4e4e7;border-radius:10px;background:#fff;padding:7px 11px;box-shadow:0 1px 2px rgba(24,24,27,.04)}.meta-v2-control input{width:92px;border:0;background:transparent;font-size:12px;outline:0}.meta-v2-btn{min-height:42px;border:1px solid #d4d4d8;border-radius:10px;background:#fff;padding:8px 14px;font-size:12px;font-weight:700}.meta-v2-btn--dark{border-color:#18181b;background:#18181b;color:#fff}.meta-v2-subnav{display:flex;gap:28px;margin:8px 0 20px;border-bottom:1px solid #e4e4e7}.meta-v2-subnav span{margin-bottom:-1px;padding:10px 2px;color:#a1a1aa;font-size:11px;font-weight:650}.meta-v2-subnav span.active{border-bottom:2px solid #18181b;color:#18181b}.meta-kpi-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}.meta-kpi{position:relative;min-height:122px;overflow:hidden;border:1px solid #e4e4e7;border-radius:12px;background:#fff;padding:17px;box-shadow:0 1px 3px rgba(24,24,27,.04)}.meta-kpi label{display:block;color:#52525b;font-size:10px;font-weight:750;text-transform:uppercase;letter-spacing:.06em}.meta-kpi strong{display:block;margin-top:10px;font-size:25px;letter-spacing:-.04em}.meta-kpi small{display:block;margin-top:7px;color:#16a34a;font-size:10px}.meta-kpi svg{position:absolute;right:13px;bottom:20px;width:62px;height:25px}.meta-board-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(260px,.72fr);gap:14px;margin-top:14px}.meta-panel{overflow:hidden;border:1px solid #e4e4e7;border-radius:13px;background:#fff;box-shadow:0 1px 3px rgba(24,24,27,.04)}.meta-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;border-bottom:1px solid #f4f4f5;padding:16px 18px}.meta-panel-head h3{margin:0;font-size:14px;font-weight:750}.meta-panel-head p{margin:4px 0 0;color:#a1a1aa;font-size:10px}.meta-chart-body{height:355px;padding:18px}.meta-insights{padding:13px}.meta-insight{display:flex;gap:10px;border:1px solid #f0f0f1;border-radius:10px;padding:12px}.meta-insight+.meta-insight{margin-top:9px}.meta-insight-dot{display:grid;width:29px;height:29px;flex:0 0 29px;place-items:center;border-radius:8px;background:#f4f4f5;font-size:11px}.meta-insight strong{display:block;font-size:11px}.meta-insight p{margin:3px 0 0;color:#71717a;font-size:10px;line-height:1.45}.meta-table-panel{margin-top:14px}.meta-table-wrap{overflow:auto}.meta-table{width:100%;min-width:900px;border-collapse:collapse;font-size:10px}.meta-table th{background:#fafafa;padding:11px 12px;color:#71717a;text-align:left;text-transform:uppercase;letter-spacing:.04em}.meta-table td{border-top:1px solid #f4f4f5;padding:11px 12px;color:#52525b}.meta-table td.title{max-width:260px;color:#18181b;font-weight:650}.meta-table a{color:#18181b;text-decoration:underline;text-underline-offset:3px}.meta-empty{padding:42px;text-align:center;color:#a1a1aa}@media(max-width:1200px){.meta-kpi-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:900px){.meta-v2-top{flex-direction:column}.meta-v2-controls{width:100%;flex-wrap:wrap}.meta-board-grid{grid-template-columns:1fr}.meta-kpi-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.meta-kpi-grid{grid-template-columns:1fr}.meta-v2-control{width:100%}.meta-v2-control input{flex:1}.meta-v2-subnav{gap:14px;overflow:auto}}
.meta-field-picker{position:relative}.meta-field-menu{position:absolute;z-index:50;top:48px;right:0;width:280px;border:1px solid #e4e4e7;border-radius:12px;background:#fff;padding:12px;box-shadow:0 18px 45px rgba(24,24,27,.16)}.meta-field-menu[hidden]{display:none!important}.meta-field-menu h4{margin:0 0 9px;font-size:12px}.meta-field-options{display:grid;grid-template-columns:1fr 1fr;gap:5px}.meta-field-option{display:flex;align-items:center;gap:7px;border-radius:7px;padding:6px;font-size:10px}.meta-field-option:hover{background:#f4f4f5}.meta-field-option input{accent-color:#18181b}
.meta-v2-btn.is-loading{display:inline-flex;align-items:center;gap:7px;cursor:wait;opacity:.82}.meta-v2-btn.is-loading::before{width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:999px;content:"";animation:meta-filter-spin .7s linear infinite}@keyframes meta-filter-spin{to{transform:rotate(360deg)}}
</style>
<div class="meta-v2-shell">
<header class="meta-v2-top"><div class="meta-v2-title"><h1 data-meta-analysis-title>Analisis Organik {{ $platform }}</h1><p>Pahami apa yang berhasil, mengapa berhasil, dan langkah berikutnya.</p></div><form id="metaAnalysisFilter" class="meta-v2-controls" action="{{ request()->url() }}" method="GET">@foreach(request()->except(['start_date', 'end_date']) as $queryKey => $queryValue)@if(is_scalar($queryValue))<input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">@endif @endforeach<div class="meta-v2-control"><strong style="font-size:11px">{{ $platform }}</strong><span style="color:#a1a1aa;font-size:11px">{{ $clientName ?? 'Client' }}</span></div><div class="meta-v2-control"><input type="date" name="start_date" id="startDate" data-filter-date placeholder="Mulai" value="{{ request('start_date', '') }}" aria-label="Tanggal mulai" required><span>–</span><input type="date" name="end_date" id="endDate" data-filter-date placeholder="Selesai" value="{{ request('end_date', '') }}" aria-label="Tanggal selesai" required></div><div class="meta-field-picker"><button type="button" class="meta-v2-btn" data-meta-field-toggle>Atur Field</button><div class="meta-field-menu" data-meta-field-menu hidden><h4>Field yang ditampilkan <span data-meta-field-count style="float:right;color:#71717a;font-weight:500"></span></h4><div class="meta-field-options">@foreach($metaMetricDefinitions as $fieldKey => $definition)<label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-meta-field-checkbox><span>{{ $definition[0] }}</span></label>@endforeach</div><p style="margin:9px 0 0;color:#a1a1aa;font-size:9px">Maksimal 9 field dapat ditampilkan.</p></div></div><button id="applyFilter" type="submit" class="meta-v2-btn meta-v2-btn--dark">Terapkan</button><button id="resetAll" type="button" class="meta-v2-btn">Reset</button></form></header>
@include('analisis.partials.period-tabs')
<div data-analysis-period-panel="realtime">@include('analisis.partials.source-tabs')
<section data-analysis-source-panel="organic">
<div class="meta-kpi-grid">@foreach($metaMetricDefinitions as $fieldKey => $definition)<article class="meta-kpi" data-meta-field="{{ $fieldKey }}"><label>{{ $definition[0] }}</label><strong>{{ is_numeric($definition[1]) ? number_format($definition[1],0,',','.') : $definition[1] }}</strong><small>Data realtime Meta</small><svg viewBox="0 0 62 25" fill="none"><path d="M1 20L10 16L18 18L28 9L38 13L49 6L61 2" stroke="#52525b" stroke-width="1.5"/><path d="M1 24H61" stroke="#e4e4e7"/></svg></article>@endforeach</div>
<div class="meta-board-grid"><div class="meta-panel"><div class="meta-panel-head"><div><h3>Performance Trend</h3><p>Pergerakan reach dan impresi setiap konten.</p></div></div><div class="meta-chart-body"><canvas id="metaOrganicTrendChart"></canvas></div></div><aside class="meta-panel"><div class="meta-panel-head"><div><h3>Insights &amp; Recommendation</h3><p>Temuan utama dari data saat ini.</p></div></div><div class="meta-insights"><div class="meta-insight"><span class="meta-insight-dot">01</span><div><strong>Konten terbaik</strong><p title="{{ data_get($topMetaItems->first(), 'judul', 'Belum ada konten') }}">{{ \Illuminate\Support\Str::limit(data_get($topMetaItems->first(), 'judul', 'Belum ada konten'), 80) }} menghasilkan engagement tertinggi.</p></div></div><div class="meta-insight"><span class="meta-insight-dot">02</span><div><strong>Engagement rate</strong><p>Rasio interaksi saat ini {{ number_format($metaEr,2,',','.') }}% dari total reach.</p></div></div><div class="meta-insight"><span class="meta-insight-dot">03</span><div><strong>Distribusi konten</strong><p>{{ $metaItems->count() }} konten terbaca pada periode ini.</p></div></div><div class="meta-insight"><span class="meta-insight-dot">04</span><div><strong>Langkah berikutnya</strong><p>Gunakan pola dari konten teratas untuk rencana konten berikutnya.</p></div></div></div></aside></div>
<div class="meta-panel meta-table-panel"><div class="meta-panel-head"><div><h3>Content Performance &amp; Analysis</h3><p>Data performa organik {{ $platform }}.</p></div></div><div class="meta-table-wrap"><table class="meta-table"><thead><tr><th>No</th><th>Tanggal</th><th>Judul</th><th>Jenis</th><th>Reach</th><th>Impresi</th><th>ER</th><th>Save</th><th>Share</th><th>Aksi</th></tr></thead><tbody>@forelse($metaItems as $item)@php($contentTitle = $item['judul'] ?: 'Konten tanpa judul')<tr><td>{{ $loop->iteration }}</td><td>{{ $item['tanggal_posting'] }}</td><td class="title" title="{{ $contentTitle }}">{{ \Illuminate\Support\Str::limit($contentTitle, 70) }}</td><td>{{ $item['jenis'] ?? '-' }}</td><td>{{ number_format($item['reach'],0,',','.') }}</td><td>{{ number_format($item['impressions'],0,',','.') }}</td><td>{{ $item['reach'] > 0 ? number_format(($item['engagement']/$item['reach'])*100,2,',','.') : '0,00' }}%</td><td>{{ number_format($item['saved'],0,',','.') }}</td><td>{{ number_format($item['shares'],0,',','.') }}</td><td><a href="{{ $item['link'] }}" target="_blank" rel="noopener">Lihat</a></td></tr>@empty<tr><td colspan="10" class="meta-empty">Belum ada data konten.</td></tr>@endforelse</tbody></table></div></div></section>
@include('analisis.partials.paid', ['platform' => $platform])</div>
@include('analisis.partials.trend-panels', ['platform' => $platform])</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rawItems = @json($metaItems->values());
    const dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const dailyMap = new Map();
    rawItems.forEach(item => {
        const date = new Date(item.tanggal_posting);
        if (Number.isNaN(date.getTime())) return;
        const key = date.toISOString().slice(0, 10);
        const current = dailyMap.get(key) || { date, reach: 0, impressions: 0 };
        current.reach += Number(item.reach || 0);
        current.impressions += Number(item.impressions || 0);
        dailyMap.set(key, current);
    });
    const dailyItems = [...dailyMap.values()].sort((a, b) => a.date - b.date);
    const items = [];
    const bucketSize = dailyItems.length > 10 ? 3 : 1;
    for (let index = 0; index < dailyItems.length; index += bucketSize) {
        const bucket = dailyItems.slice(index, index + bucketSize);
        const first = bucket[0];
        const last = bucket[bucket.length - 1];
        items.push({
            tanggal_posting: bucket.length === 1
                ? dateFormatter.format(first.date)
                : `${dateFormatter.format(first.date)} – ${dateFormatter.format(last.date)}`,
            reach: bucket.reduce((total, item) => total + item.reach, 0),
            impressions: bucket.reduce((total, item) => total + item.impressions, 0),
        });
    }

    let chart = null;
    const canvas = document.getElementById('metaOrganicTrendChart');
    function render() {
        if (!canvas || typeof Chart === 'undefined') return;
        if (chart) { chart.resize(); return; }
        chart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: items.map(item => item.tanggal_posting),
                datasets: [
                    { label: 'Reach', data: items.map(item => item.reach), borderColor: '#18181b', backgroundColor: 'rgba(24,24,27,.05)', fill: true, tension: .35, pointRadius: 3 },
                    { label: 'Impresi', data: items.map(item => item.impressions), borderColor: '#94a3b8', tension: .35, pointRadius: 2 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false }, ticks: { minRotation: 0, maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } }
                }
            }
        });
    }
    render();
    window.addEventListener('analysis:source-changed', event => { if (event.detail?.active === 'organic') requestAnimationFrame(render); });
    document.getElementById('metaAnalysisFilter')?.addEventListener('submit', event => {
        const startDate = document.getElementById('startDate')?.value;
        const endDate = document.getElementById('endDate')?.value;
        if (!startDate || !endDate) {
            event.preventDefault();
            return;
        }
        if (startDate > endDate) {
            event.preventDefault();
            window.alert('Tanggal mulai tidak boleh melewati tanggal selesai.');
            return;
        }

        const button = document.getElementById('applyFilter');
        button.disabled = true;
        button.classList.add('is-loading');
        button.textContent = 'Memfilter';
        window.showLoadingNow?.('Memfilter data analisis');
    });
    document.getElementById('resetAll')?.addEventListener('click', () => location.href = location.pathname);
});
</script>
<script>document.addEventListener('DOMContentLoaded',function(){const maxFields=9;const storageKey='meta-analysis-fields-{{ strtolower($platform) }}';const defaults=['reach','engagement_rate','impressions','saved','shares','followers'];const cards=[...document.querySelectorAll('[data-meta-field]')];const checks=[...document.querySelectorAll('[data-meta-field-checkbox]')];const menu=document.querySelector('[data-meta-field-menu]');const toggle=document.querySelector('[data-meta-field-toggle]');const count=document.querySelector('[data-meta-field-count]');let selected;try{selected=JSON.parse(localStorage.getItem(storageKey))}catch(e){selected=null}if(!Array.isArray(selected)||!selected.length)selected=defaults;selected=selected.slice(0,maxFields);function apply(){cards.forEach(card=>card.hidden=!selected.includes(card.dataset.metaField));checks.forEach(check=>{check.checked=selected.includes(check.value);check.disabled=!check.checked&&selected.length>=maxFields});if(count)count.textContent=`${selected.length}/${maxFields}`;}toggle?.addEventListener('click',e=>{e.stopPropagation();menu.hidden=!menu.hidden});checks.forEach(check=>check.addEventListener('change',()=>{const next=checks.filter(item=>item.checked).map(item=>item.value);if(!next.length||next.length>maxFields){check.checked=!check.checked;return}selected=next;localStorage.setItem(storageKey,JSON.stringify(selected));apply()}));document.addEventListener('click',e=>{if(menu&&!e.target.closest('.meta-field-picker'))menu.hidden=true});localStorage.setItem(storageKey,JSON.stringify(selected));apply()});</script>
