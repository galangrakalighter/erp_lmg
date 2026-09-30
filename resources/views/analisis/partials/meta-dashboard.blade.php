@php
    $metaItems = collect($organicItems ?? []);
    $metaReach = $metaItems->sum('reach'); $metaImpressions = $metaItems->sum('impressions');
    $metaEngagement = $metaItems->sum('engagement'); $metaSaves = $metaItems->sum('saved'); $metaShares = $metaItems->sum('shares');
    $metaLikes = $metaItems->sum('likes'); $metaComments = $metaItems->sum('comments');
    $metaEr = $metaReach > 0 ? ($metaEngagement / $metaReach) * 100 : 0;
    $metaFrequency = $metaReach > 0 ? $metaImpressions / $metaReach : 0;
    $topMetaItems = $metaItems->sortByDesc('engagement')->take(4)->values();
    $metaMetricDefinitions = [
        'reach' => ['Reach', $metaReach], 'views' => ['Views', $metaImpressions],
        'impressions' => ['Impressions', $metaImpressions], 'accounts_reached' => ['Accounts Reached', $metaReach],
        'engagement' => ['Total Engagement', $metaEngagement], 'likes' => ['Likes / Reactions', $metaLikes],
        'comments' => ['Comments', $metaComments], 'replies' => ['Replies', $metaItems->sum('replies')],
        'shares' => ['Shares', $metaShares], 'saved' => ['Saves', $metaSaves],
        'reposts' => ['Reposts', $metaItems->sum('reposts')], 'quotes' => ['Quotes', $metaItems->sum('quotes')],
        'follows' => ['Follows', $followerMetric ?? 0], 'unfollows' => ['Unfollows', 0],
        'net_followers' => ['Net Followers', $followerMetric ?? 0], 'profile_visits' => ['Profile Visits', 0],
        'link_clicks' => ['Link Clicks', 0], 'video_views' => ['Video Views', $metaItems->sum('video_views')],
        'video_3s_views' => ['3-sec Video Views', $metaItems->sum('video_3s_views')],
        'video_1m_views' => ['1-min Video Views', $metaItems->sum('video_1m_views')],
        'watch_time' => ['Watch Time', $metaItems->sum('watch_time')],
        'average_watch_time' => ['Average Watch Time', $metaItems->count() ? $metaItems->avg('average_watch_time') : 0],
        'engagement_rate' => ['Engagement Rate', number_format($metaEr,2,',','.').'%'],
        'like_rate' => ['Like Rate', number_format($metaReach > 0 ? ($metaLikes / $metaReach) * 100 : 0,2,',','.').'%'],
        'comment_rate' => ['Comment Rate', number_format($metaReach > 0 ? ($metaComments / $metaReach) * 100 : 0,2,',','.').'%'],
        'share_rate' => ['Share Rate', number_format($metaReach > 0 ? ($metaShares / $metaReach) * 100 : 0,2,',','.').'%'],
        'save_rate' => ['Save Rate', number_format($metaReach > 0 ? ($metaSaves / $metaReach) * 100 : 0,2,',','.').'%'],
        'profile_visit_rate' => ['Profile Visit Rate', '0,00%'], 'follow_rate' => ['Follow Rate', number_format($metaReach > 0 ? (($followerMetric ?? 0) / $metaReach) * 100 : 0,2,',','.').'%'],
    ];
    $metaMetricGroups = [
        'Reach & Visibility' => ['reach', 'views', 'impressions', 'accounts_reached'],
        'Engagement' => ['engagement', 'likes', 'comments', 'replies', 'shares', 'saved', 'reposts', 'quotes'],
        'Profile & Growth' => ['follows', 'unfollows', 'net_followers', 'profile_visits', 'link_clicks'],
        'Video' => ['video_views', 'video_3s_views', 'video_1m_views', 'watch_time', 'average_watch_time'],
        'Calculated Metrics' => ['engagement_rate', 'like_rate', 'comment_rate', 'share_rate', 'save_rate', 'profile_visit_rate', 'follow_rate'],
    ];
    $contentPlanRows = collect($contentPlanItems ?? []);
    $metaDonutDefinitions = $platform === 'Instagram'
        ? ['gender' => 'Gender', 'age' => 'Age', 'country' => 'Country', 'city' => 'City', 'engagement_type' => 'Engagement Type']
        : ($platform === 'Facebook'
            ? ['gender' => 'Gender', 'age' => 'Age', 'country' => 'Country', 'city' => 'City', 'reaction_type' => 'Reaction Type']
            : []);
    $metaDonutData = collect($metaDonutDefinitions)->mapWithKeys(fn ($label, $key) => [$key => (array) data_get($audienceBreakdowns ?? [], $key, [])]);
@endphp
@include('partials.client_data_theme')
@include('partials.client_native_tables')
@include('partials.analysis_workspace_theme')
@include('partials.meta_analysis_dashboard_theme')
<style>
.meta-v2-shell{color:#27272a}.meta-v2-top{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;margin-bottom:22px}.meta-v2-title h1{margin:0;font-size:26px;font-weight:760;letter-spacing:-.035em}.meta-v2-title p{margin:5px 0 0;color:#71717a;font-size:12px}.meta-v2-controls{display:flex;align-items:center;gap:9px}.meta-v2-control{display:flex;min-height:42px;align-items:center;gap:8px;border:1px solid #e4e4e7;border-radius:10px;background:#fff;padding:7px 11px;box-shadow:0 1px 2px rgba(24,24,27,.04)}.meta-v2-control input{width:92px;border:0;background:transparent;font-size:12px;outline:0}.meta-v2-btn{min-height:42px;border:1px solid #d4d4d8;border-radius:10px;background:#fff;padding:8px 14px;font-size:12px;font-weight:700}.meta-v2-btn--dark{border-color:#18181b;background:#18181b;color:#fff}.meta-v2-subnav{display:flex;gap:28px;margin:8px 0 20px;border-bottom:1px solid #e4e4e7}.meta-v2-subnav span{margin-bottom:-1px;padding:10px 2px;color:#a1a1aa;font-size:11px;font-weight:650}.meta-v2-subnav span.active{border-bottom:2px solid #18181b;color:#18181b}.meta-kpi-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}.meta-kpi{position:relative;min-height:122px;overflow:hidden;border:1px solid #e4e4e7;border-radius:12px;background:#fff;padding:17px;box-shadow:0 1px 3px rgba(24,24,27,.04)}.meta-kpi label{display:block;color:#52525b;font-size:10px;font-weight:750;text-transform:uppercase;letter-spacing:.06em}.meta-kpi strong{display:block;margin-top:10px;font-size:25px;letter-spacing:-.04em}.meta-kpi small{display:block;margin-top:7px;color:#16a34a;font-size:10px}.meta-kpi svg{position:absolute;right:13px;bottom:20px;width:62px;height:25px}.meta-board-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(260px,.72fr);gap:14px;margin-top:14px}.meta-panel{overflow:hidden;border:1px solid #e4e4e7;border-radius:13px;background:#fff;box-shadow:0 1px 3px rgba(24,24,27,.04)}.meta-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:15px;border-bottom:1px solid #f4f4f5;padding:16px 18px}.meta-panel-head h3{margin:0;font-size:14px;font-weight:750}.meta-panel-head p{margin:4px 0 0;color:#a1a1aa;font-size:10px}.meta-chart-body{height:355px;padding:18px}.meta-insights{padding:13px}.meta-insight{display:flex;gap:10px;border:1px solid #f0f0f1;border-radius:10px;padding:12px}.meta-insight+.meta-insight{margin-top:9px}.meta-insight-dot{display:grid;width:29px;height:29px;flex:0 0 29px;place-items:center;border-radius:8px;background:#f4f4f5;font-size:11px}.meta-insight strong{display:block;font-size:11px}.meta-insight p{margin:3px 0 0;color:#71717a;font-size:10px;line-height:1.45}.meta-table-panel{margin-top:14px}.meta-table-wrap{overflow:auto}.meta-table{width:100%;min-width:900px;border-collapse:collapse;font-size:10px}.meta-table th{background:#fafafa;padding:11px 12px;color:#71717a;text-align:left;text-transform:uppercase;letter-spacing:.04em}.meta-table td{border-top:1px solid #f4f4f5;padding:11px 12px;color:#52525b}.meta-table td.title{max-width:260px;color:#18181b;font-weight:650}.meta-table a{color:#18181b;text-decoration:underline;text-underline-offset:3px}.meta-empty{padding:42px;text-align:center;color:#a1a1aa}@media(max-width:1200px){.meta-kpi-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:900px){.meta-v2-top{flex-direction:column}.meta-v2-controls{width:100%;flex-wrap:wrap}.meta-board-grid{grid-template-columns:1fr}.meta-kpi-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:560px){.meta-kpi-grid{grid-template-columns:1fr}.meta-v2-control{width:100%}.meta-v2-control input{flex:1}.meta-v2-subnav{gap:14px;overflow:auto}}
.meta-chart-panel{overflow:visible}.meta-field-picker{position:relative}.meta-field-menu{position:absolute;z-index:50;top:48px;right:0;width:340px;max-height:min(620px,70vh);overflow:auto;border:1px solid #e4e4e7;border-radius:12px;background:#fff;padding:12px;box-shadow:0 18px 45px rgba(24,24,27,.16)}.meta-field-menu[hidden]{display:none!important}.meta-field-menu h4{margin:0 0 9px;font-size:12px}.meta-field-group{overflow:hidden;border:1px solid #eeeeef;border-radius:9px}.meta-field-group+.meta-field-group{margin-top:7px}.meta-field-group summary{display:flex;align-items:center;justify-content:space-between;cursor:pointer;list-style:none;background:#fafafa;padding:9px 10px;font-size:11px;font-weight:750}.meta-field-group summary::-webkit-details-marker{display:none}.meta-field-group summary::after{content:'⌄';color:#71717a}.meta-field-group[open] summary::after{transform:rotate(180deg)}.meta-field-options{display:grid;grid-template-columns:1fr 1fr;gap:3px;padding:6px}.meta-field-option{display:flex;align-items:center;gap:7px;border-radius:7px;padding:6px;font-size:10px}.meta-field-option:hover{background:#f4f4f5}.meta-field-option input{accent-color:#18181b}
.meta-v2-btn.is-loading{display:inline-flex;align-items:center;gap:7px;cursor:wait;opacity:.82}.meta-v2-btn.is-loading::before{width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:999px;content:"";animation:meta-filter-spin .7s linear infinite}@keyframes meta-filter-spin{to{transform:rotate(360deg)}}
</style>
<style>.meta-field-toolbar{display:flex;justify-content:flex-end;margin-bottom:10px}</style>
<style>.meta-donut-section{margin-top:14px}.meta-donut-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;padding:14px}.meta-donut-card{min-width:0;border:1px solid #f0f0f1;border-radius:11px;padding:13px}.meta-donut-card h4{margin:0 0 10px;font-size:11px}.meta-donut-canvas{height:190px}.meta-donut-empty{display:grid;height:190px;place-items:center;color:#a1a1aa;font-size:10px;text-align:center}@media(max-width:1250px){.meta-donut-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:760px){.meta-donut-grid{grid-template-columns:1fr 1fr}}@media(max-width:480px){.meta-donut-grid{grid-template-columns:1fr}}</style>
<div class="meta-v2-shell">
<header class="meta-v2-top"><div class="meta-v2-title"><h1 data-meta-analysis-title>Analisis Organik {{ $platform }}</h1><p>Pahami apa yang berhasil, mengapa berhasil, dan langkah berikutnya.</p></div><form id="metaAnalysisFilter" class="meta-v2-controls" action="{{ request()->url() }}" method="GET">@foreach(request()->except(['start_date', 'end_date']) as $queryKey => $queryValue)@if(is_scalar($queryValue))<input type="hidden" name="{{ $queryKey }}" value="{{ $queryValue }}">@endif @endforeach<div class="meta-v2-control"><strong style="font-size:11px">{{ $platform }}</strong><span style="color:#a1a1aa;font-size:11px">{{ $clientName ?? 'Client' }}</span></div><div class="meta-v2-control"><input type="date" name="start_date" id="startDate" data-filter-date placeholder="Mulai" value="{{ request('start_date', '') }}" aria-label="Tanggal mulai" required><span>–</span><input type="date" name="end_date" id="endDate" data-filter-date placeholder="Selesai" value="{{ request('end_date', '') }}" aria-label="Tanggal selesai" required></div><button id="applyFilter" type="submit" class="meta-v2-btn meta-v2-btn--dark">Terapkan</button><button id="resetAll" type="button" class="meta-v2-btn">Reset</button></form></header>
@include('analisis.partials.period-tabs')
<div data-analysis-period-panel="realtime">@include('analisis.partials.source-tabs')
<section data-analysis-source-panel="organic">
<div class="meta-field-toolbar"><div class="meta-field-picker"><button type="button" class="meta-v2-btn" data-meta-card-field-toggle>Atur Field Ringkasan</button><div class="meta-field-menu" data-meta-card-field-menu hidden><h4>Field ringkasan <span data-meta-card-field-count style="float:right;color:#71717a;font-weight:500"></span></h4>@foreach($metaMetricGroups as $groupLabel => $groupFields)<details class="meta-field-group" @if($loop->first) open @endif><summary><span>{{ $groupLabel }}</span></summary><div class="meta-field-options">@foreach($groupFields as $fieldKey)@php($definition = $metaMetricDefinitions[$fieldKey])<label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-meta-card-field-checkbox><span>{{ $definition[0] }}</span></label>@endforeach</div></details>@endforeach<p style="margin:9px 0 0;color:#a1a1aa;font-size:9px">Pilih maksimal 9 metrik untuk kartu ringkasan.</p></div></div></div>
<div class="meta-kpi-grid">@foreach($metaMetricDefinitions as $fieldKey => $definition)<article class="meta-kpi" data-meta-field="{{ $fieldKey }}"><label>{{ $definition[0] }}</label><strong>{{ is_numeric($definition[1]) ? number_format($definition[1],0,',','.') : $definition[1] }}</strong><small>Data realtime Meta</small><svg viewBox="0 0 62 25" fill="none"><path d="M1 20L10 16L18 18L28 9L38 13L49 6L61 2" stroke="#52525b" stroke-width="1.5"/><path d="M1 24H61" stroke="#e4e4e7"/></svg></article>@endforeach</div>
<div class="meta-board-grid"><div class="meta-panel meta-chart-panel"><div class="meta-panel-head"><div><h3>Performance Trend</h3><p>Pergerakan metrik konten organik yang dipilih.</p></div><div class="meta-field-picker" data-organic-field-picker><button type="button" class="meta-v2-btn" data-meta-field-toggle>Atur Field Chart</button><div class="meta-field-menu" data-meta-field-menu hidden><h4>Metrik chart <span data-meta-field-count style="float:right;color:#71717a;font-weight:500"></span></h4>@foreach($metaMetricGroups as $groupLabel => $groupFields)<details class="meta-field-group" @if($loop->first) open @endif><summary><span>{{ $groupLabel }}</span><small data-meta-group-count="{{ $groupLabel }}"></small></summary><div class="meta-field-options">@foreach($groupFields as $fieldKey)@php($definition = $metaMetricDefinitions[$fieldKey])<label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-meta-field-checkbox data-meta-field-group="{{ $groupLabel }}"><span>{{ $definition[0] }}</span></label>@endforeach</div></details>@endforeach<p style="margin:9px 0 0;color:#a1a1aa;font-size:9px">Pilih maksimal 9 metrik untuk kartu dan line chart.</p></div></div></div><div class="meta-chart-body"><canvas id="metaOrganicTrendChart"></canvas></div></div><aside class="meta-panel"><div class="meta-panel-head"><div><h3>Insights &amp; Recommendation</h3><p>Temuan utama dari data saat ini.</p></div></div><div class="meta-insights"><div class="meta-insight"><span class="meta-insight-dot">01</span><div><strong>Konten terbaik</strong><p title="{{ data_get($topMetaItems->first(), 'judul', 'Belum ada konten') }}">{{ \Illuminate\Support\Str::limit(data_get($topMetaItems->first(), 'judul', 'Belum ada konten'), 80) }} menghasilkan engagement tertinggi.</p></div></div><div class="meta-insight"><span class="meta-insight-dot">02</span><div><strong>Engagement rate</strong><p>Rasio interaksi saat ini {{ number_format($metaEr,2,',','.') }}% dari total reach.</p></div></div><div class="meta-insight"><span class="meta-insight-dot">03</span><div><strong>Distribusi konten</strong><p>{{ $metaItems->count() }} konten terbaca pada periode ini.</p></div></div><div class="meta-insight"><span class="meta-insight-dot">04</span><div><strong>Langkah berikutnya</strong><p>Gunakan pola dari konten teratas untuk rencana konten berikutnya.</p></div></div></div></aside></div>
@if($metaDonutDefinitions !== [])<div class="meta-panel meta-donut-section"><div class="meta-panel-head"><div><h3>Audience &amp; Engagement Breakdown</h3><p>Distribusi audiens dan interaksi yang tersedia dari Meta.</p></div></div><div class="meta-donut-grid">@foreach($metaDonutDefinitions as $dimensionKey => $dimensionLabel)@php($dimensionData = $metaDonutData->get($dimensionKey, []))<article class="meta-donut-card"><h4>{{ $dimensionLabel }}</h4>@if(array_sum($dimensionData) > 0)<div class="meta-donut-canvas"><canvas data-meta-donut="{{ $dimensionKey }}"></canvas></div>@else<div class="meta-donut-empty">Data {{ strtolower($dimensionLabel) }} belum tersedia dari Meta.</div>@endif</article>@endforeach</div></div>@endif
<div class="meta-panel meta-table-panel"><div class="meta-panel-head"><div><h3>Content Plan {{ $platform }}</h3><p>Rencana konten yang dibuat untuk platform {{ $platform }} pada periode terpilih.</p></div></div><div class="meta-table-wrap"><table class="meta-table"><thead><tr><th>No</th><th>Tanggal</th><th>Judul</th><th>Type</th><th>Strategy</th><th>Pilar</th><th>Hook</th><th>Body</th><th>CTA</th><th>Status</th><th>Output</th></tr></thead><tbody>@forelse($contentPlanRows as $item)@php($contentTitle = $item->judul ?: 'Konten tanpa judul')<tr><td>{{ $loop->iteration }}</td><td>{{ $item->tanggal_posting ? \Carbon\Carbon::parse($item->tanggal_posting)->translatedFormat('d M Y') : '-' }}</td><td class="title" title="{{ $contentTitle }}">{{ \Illuminate\Support\Str::limit($contentTitle, 70) }}</td><td>{{ $item->type ?? '-' }}</td><td>{{ $item->strategy ?? '-' }}</td><td>{{ $item->pilar ?? '-' }}</td><td title="{{ $item->hooks ?? '' }}">{{ \Illuminate\Support\Str::limit($item->hooks ?? '-', 42) }}</td><td title="{{ $item->body ?? '' }}">{{ \Illuminate\Support\Str::limit($item->body ?? '-', 42) }}</td><td title="{{ $item->cta ?? '' }}">{{ \Illuminate\Support\Str::limit($item->cta ?? '-', 42) }}</td><td>{{ $item->nama_status ?? '-' }}</td><td>@if($item->link)<a href="{{ $item->link }}" target="_blank" rel="noopener">Lihat</a>@else - @endif</td></tr>@empty<tr><td colspan="11" class="meta-empty">Belum ada Content Plan untuk {{ $platform }} pada periode ini.</td></tr>@endforelse</tbody></table></div></div></section>
@include('analisis.partials.paid', ['platform' => $platform])</div>
@include('analisis.partials.trend-panels', ['platform' => $platform])</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rawItems = @json($metaItems->values());
    const dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const metricLabels = @json(collect($metaMetricDefinitions)->map(fn ($definition) => $definition[0]));
    const accountMetrics = { follows: Number(@json($followerMetric ?? 0)), unfollows: 0, net_followers: Number(@json($followerMetric ?? 0)), profile_visits: 0, link_clicks: 0 };
    const additiveKeys = ['reach','views','impressions','accounts_reached','engagement','likes','comments','replies','shares','saved','reposts','quotes','video_views','video_3s_views','video_1m_views','watch_time'];
    const dailyMap = new Map();
    rawItems.forEach(item => {
        const date = new Date(item.tanggal_posting);
        if (Number.isNaN(date.getTime())) return;
        const key = date.toISOString().slice(0, 10);
        const current = dailyMap.get(key) || { date, item_count: 0 };
        const normalized = {
            reach: Number(item.reach || 0), views: Number(item.views ?? item.impressions ?? 0),
            impressions: Number(item.impressions || 0), accounts_reached: Number(item.accounts_reached ?? item.reach ?? 0),
            engagement: Number(item.engagement || 0), likes: Number(item.likes || 0), comments: Number(item.comments || 0),
            replies: Number(item.replies || 0), shares: Number(item.shares || 0), saved: Number(item.saved || 0),
            reposts: Number(item.reposts || 0), quotes: Number(item.quotes || 0), video_views: Number(item.video_views || 0),
            video_3s_views: Number(item.video_3s_views || 0), video_1m_views: Number(item.video_1m_views || 0),
            watch_time: Number(item.watch_time || 0), average_watch_time: Number(item.average_watch_time || 0)
        };
        additiveKeys.forEach(metric => { current[metric] = Number(current[metric] || 0) + normalized[metric]; });
        current.average_watch_time = Number(current.average_watch_time || 0) + normalized.average_watch_time;
        current.item_count += 1;
        dailyMap.set(key, current);
    });
    const dailyItems = [...dailyMap.values()].sort((a, b) => a.date - b.date);
    const items = [];
    const bucketSize = dailyItems.length > 10 ? 3 : 1;
    for (let index = 0; index < dailyItems.length; index += bucketSize) {
        const bucket = dailyItems.slice(index, index + bucketSize);
        const first = bucket[0];
        const last = bucket[bucket.length - 1];
        const aggregate = {
            tanggal_posting: bucket.length === 1
                ? dateFormatter.format(first.date)
                : `${dateFormatter.format(first.date)} – ${dateFormatter.format(last.date)}`,
            item_count: bucket.reduce((total, item) => total + item.item_count, 0),
        };
        additiveKeys.forEach(metric => { aggregate[metric] = bucket.reduce((total, item) => total + Number(item[metric] || 0), 0); });
        aggregate.average_watch_time = aggregate.item_count ? bucket.reduce((total, item) => total + Number(item.average_watch_time || 0), 0) / aggregate.item_count : 0;
        items.push(aggregate);
    }

    const rate = (numerator, denominator) => denominator > 0 ? (numerator / denominator) * 100 : 0;
    function metricValue(item, metric) {
        if (Object.prototype.hasOwnProperty.call(accountMetrics, metric)) return accountMetrics[metric];
        const rates = {
            engagement_rate: rate(item.engagement, item.reach), like_rate: rate(item.likes, item.reach),
            comment_rate: rate(item.comments, item.reach), share_rate: rate(item.shares, item.reach),
            save_rate: rate(item.saved, item.reach), profile_visit_rate: rate(accountMetrics.profile_visits, item.reach),
            follow_rate: rate(accountMetrics.follows, item.reach)
        };
        return Object.prototype.hasOwnProperty.call(rates, metric) ? rates[metric] : Number(item[metric] || 0);
    }
    const palette = ['#18181b','#64748b','#2563eb','#059669','#d97706','#dc2626','#7c3aed','#0891b2','#db2777'];

    let chart = null;
    const canvas = document.getElementById('metaOrganicTrendChart');
    let activeMetrics = ['reach', 'views', 'impressions', 'engagement'];
    function render(force = false) {
        if (!canvas || typeof Chart === 'undefined') return;
        if (chart && !force) { chart.resize(); return; }
        if (chart) chart.destroy();
        chart = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: items.map(item => item.tanggal_posting),
                datasets: activeMetrics.map((metric, index) => ({ label: metricLabels[metric] || metric, data: items.map(item => metricValue(item, metric)), borderColor: palette[index], backgroundColor: index === 0 ? 'rgba(24,24,27,.05)' : 'transparent', fill: index === 0, tension: .35, pointRadius: 2 }))
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
    window.metaOrganicSetMetrics = metrics => { activeMetrics = metrics.length ? metrics : ['reach']; render(true); };
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
@if($metaDonutDefinitions !== [])
<script>
document.addEventListener('DOMContentLoaded',function(){
    const dimensions=@json($metaDonutData),charts=[],colors=['#18181b','#64748b','#2563eb','#059669','#d97706','#dc2626','#7c3aed','#0891b2','#db2777','#84cc16'];
    document.querySelectorAll('[data-meta-donut]').forEach(canvas=>{
        const values=dimensions[canvas.dataset.metaDonut]||{},labels=Object.keys(values),data=Object.values(values).map(Number);
        if(!labels.length||typeof Chart==='undefined')return;
        charts.push(new Chart(canvas.getContext('2d'),{type:'doughnut',data:{labels,datasets:[{data,backgroundColor:labels.map((_,index)=>colors[index%colors.length]),borderColor:'#fff',borderWidth:2,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,cutout:'62%',plugins:{legend:{position:'bottom',labels:{boxWidth:8,boxHeight:8,font:{size:9},usePointStyle:true}},tooltip:{callbacks:{label(context){const total=context.dataset.data.reduce((sum,value)=>sum+Number(value||0),0),value=Number(context.raw||0),percent=total>0?value/total*100:0;return ` ${context.label}: ${value.toLocaleString('id-ID')} (${percent.toFixed(1)}%)`}}}}}}));
    });
    window.addEventListener('analysis:source-changed',event=>{if(event.detail?.active==='organic')requestAnimationFrame(()=>charts.forEach(chart=>chart.resize()))});
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded',function(){
    const max=9,key='meta-analysis-chart-fields-v2-{{ strtolower($platform) }}',defaults=['reach','views','impressions','accounts_reached'];
    const checks=[...document.querySelectorAll('[data-meta-field-checkbox]')],menu=document.querySelector('[data-meta-field-menu]'),toggle=document.querySelector('[data-meta-field-toggle]'),count=document.querySelector('[data-meta-field-count]');
    let selected;try{selected=JSON.parse(localStorage.getItem(key))}catch(e){selected=null}if(!Array.isArray(selected)||!selected.length)selected=defaults;selected=selected.filter(value=>checks.some(check=>check.value===value)).slice(0,max);if(!selected.length)selected=defaults;
    function apply(){checks.forEach(check=>{check.checked=selected.includes(check.value);check.disabled=!check.checked&&selected.length>=max});if(count)count.textContent=`${selected.length}/${max}`;document.querySelectorAll('[data-meta-group-count]').forEach(label=>{const group=label.dataset.metaGroupCount,total=checks.filter(check=>check.dataset.metaFieldGroup===group).length,active=checks.filter(check=>check.dataset.metaFieldGroup===group&&selected.includes(check.value)).length;label.textContent=`${active}/${total}`});window.metaOrganicSetMetrics?.(selected)}
    toggle?.addEventListener('click',event=>{event.stopPropagation();menu.hidden=!menu.hidden});checks.forEach(check=>check.addEventListener('change',()=>{const next=checks.filter(item=>item.checked).map(item=>item.value);if(!next.length||next.length>max){check.checked=!check.checked;return}selected=next;localStorage.setItem(key,JSON.stringify(selected));apply()}));document.addEventListener('click',event=>{if(menu&&!event.target.closest('[data-meta-field-menu]')&&!event.target.closest('[data-meta-field-toggle]'))menu.hidden=true});localStorage.setItem(key,JSON.stringify(selected));apply();
});
document.addEventListener('DOMContentLoaded',function(){
    const max=9,key='meta-analysis-card-fields-v2-{{ strtolower($platform) }}',defaults=['reach','views','impressions','engagement'];
    const cards=[...document.querySelectorAll('[data-meta-field]')],checks=[...document.querySelectorAll('[data-meta-card-field-checkbox]')],menu=document.querySelector('[data-meta-card-field-menu]'),toggle=document.querySelector('[data-meta-card-field-toggle]'),count=document.querySelector('[data-meta-card-field-count]');
    let selected;try{selected=JSON.parse(localStorage.getItem(key))}catch(e){selected=null}if(!Array.isArray(selected)||!selected.length)selected=defaults;selected=selected.filter(value=>checks.some(check=>check.value===value)).slice(0,max);if(!selected.length)selected=defaults;
    function apply(){cards.forEach(card=>card.hidden=!selected.includes(card.dataset.metaField));checks.forEach(check=>{check.checked=selected.includes(check.value);check.disabled=!check.checked&&selected.length>=max});if(count)count.textContent=`${selected.length}/${max}`}
    toggle?.addEventListener('click',event=>{event.stopPropagation();menu.hidden=!menu.hidden});checks.forEach(check=>check.addEventListener('change',()=>{const next=checks.filter(item=>item.checked).map(item=>item.value);if(!next.length||next.length>max){check.checked=!check.checked;return}selected=next;localStorage.setItem(key,JSON.stringify(selected));apply()}));document.addEventListener('click',event=>{if(menu&&!event.target.closest('[data-meta-card-field-menu]')&&!event.target.closest('[data-meta-card-field-toggle]'))menu.hidden=true});localStorage.setItem(key,JSON.stringify(selected));apply();
});
</script>
