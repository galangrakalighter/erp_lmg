@php
    $paidItems = collect($paidPosts ?? []);
    $sum = fn (string $key) => (float) $paidItems->sum($key);
    $paidReach = $sum('reach'); $paidImpressions = $sum('impressions'); $paidClicks = $sum('clicks');
    $paidUniqueClicks = $sum('unique_clicks'); $paidSpend = $sum('spend'); $paidCurrencyLabel = $paidCurrency ?? 'IDR';
    $number = fn ($value, int $decimals = 0) => number_format($value, $decimals, ',', '.');
    $money = fn ($value) => $paidCurrencyLabel.' '.$number($value, 0);
    $percent = fn ($value) => $number($value, 2).'%';
    $metaLabel = fn ($value) => filled($value) ? \Illuminate\Support\Str::of((string) $value)->replace('_', ' ')->lower()->title()->toString() : '-';
    $ratio = fn ($top, $bottom, $multiplier = 1) => $bottom > 0 ? ($top / $bottom) * $multiplier : 0;
    $paidMetricDefinitions = [
        'ads' => ['Jumlah Iklan', $number($paidItems->count()), 'number'], 'reach' => ['Reach', $number($paidReach), 'number'],
        'impressions' => ['Impresi', $number($paidImpressions), 'number'], 'frequency' => ['Frekuensi', $number($ratio($paidImpressions,$paidReach),2).'x', 'number'],
        'clicks' => ['Klik', $number($paidClicks), 'number'], 'unique_clicks' => ['Klik Unik', $number($paidUniqueClicks), 'number'],
        'ctr' => ['CTR', $percent($ratio($paidClicks,$paidImpressions,100)), 'percent'], 'unique_ctr' => ['Unique CTR', $percent($ratio($paidUniqueClicks,$paidReach,100)), 'percent'],
        'cpc' => ['CPC', $money($ratio($paidSpend,$paidClicks)), 'currency'], 'cpm' => ['CPM', $money($ratio($paidSpend,$paidImpressions,1000)), 'currency'],
        'cpp' => ['CPP', $money($ratio($paidSpend,$paidReach,1000)), 'currency'], 'spend' => ['Spend', $money($paidSpend), 'currency'],
        'social_spend' => ['Social Spend', $money($sum('social_spend')), 'currency'], 'engagement' => ['Engagement', $number($sum('engagement')), 'number'],
        'inline_post_engagement' => ['Post Engagement', $number($sum('inline_post_engagement')), 'number'], 'inline_link_clicks' => ['Link Click', $number($sum('inline_link_clicks')), 'number'],
        'inline_link_click_ctr' => ['Link CTR', $percent($ratio($sum('inline_link_clicks'),$paidImpressions,100)), 'percent'], 'outbound_clicks' => ['Outbound Click', $number($sum('outbound_clicks')), 'number'],
        'conversions' => ['Conversion', $number($sum('conversions'),2), 'number'], 'conversion_values' => ['Conversion Value', $money($sum('conversion_values')), 'currency'],
        'video_plays' => ['Video Play', $number($sum('video_plays')), 'number'], 'video_thruplays' => ['ThruPlay', $number($sum('video_thruplays')), 'number'],
        'video_p25' => ['Video 25%', $number($sum('video_p25')), 'number'], 'video_p50' => ['Video 50%', $number($sum('video_p50')), 'number'],
        'video_p75' => ['Video 75%', $number($sum('video_p75')), 'number'], 'video_p100' => ['Video 100%', $number($sum('video_p100')), 'number'],
    ];
    $topPaidItem = $paidItems->sortByDesc('engagement')->first();
    $treeMetrics = function ($items) {
        $items = collect($items);

        return [
            'reach' => (int) $items->sum('reach'),
            'impressions' => (int) $items->sum('impressions'),
            'clicks' => (int) $items->sum('clicks'),
            'engagement' => (int) $items->sum('engagement'),
            'spend' => (float) $items->sum('spend'),
        ];
    };
    $treeDates = function ($items) {
        $items = collect($items);
        $firstStart = $items->filter(fn ($item) => filled($item['start_timestamp'] ?? null))->sortBy('start_timestamp')->first();
        $lastEnd = $items->filter(fn ($item) => filled($item['end_timestamp'] ?? null))->sortByDesc('end_timestamp')->first();

        return [
            'start' => $firstStart['start_date'] ?? '-',
            'end' => $lastEnd['end_date'] ?? '-',
        ];
    };
    $paidHierarchy = $paidItems
        ->groupBy(fn ($item) => filled($item['campaign_id'] ?? null) ? 'id:'.$item['campaign_id'] : 'name:'.($item['campaign'] ?? '-'))
        ->map(function ($campaignItems, $campaignKey) use ($treeMetrics, $treeDates) {
            $firstCampaign = $campaignItems->first();
            $adsets = $campaignItems
                ->groupBy(fn ($item) => filled($item['adset_id'] ?? null) ? 'id:'.$item['adset_id'] : 'name:'.($item['adset'] ?? 'Set iklan tanpa nama'))
                ->map(function ($adItems, $adsetKey) use ($treeMetrics, $treeDates) {
                    $firstAd = $adItems->first();

                    return [
                        'key' => 'adset-'.md5((string) $adsetKey),
                        'name' => $firstAd['adset'] ?? 'Set iklan tanpa nama',
                        'metrics' => $treeMetrics($adItems),
                        'dates' => $treeDates($adItems),
                        'details' => $firstAd['adset_details'] ?? [],
                        'ads' => $adItems->values(),
                    ];
                })->values();

            return [
                'key' => 'campaign-'.md5((string) $campaignKey),
                'name' => $firstCampaign['campaign'] ?? 'Campaign tanpa nama',
                'metrics' => $treeMetrics($campaignItems),
                'dates' => $treeDates($campaignItems),
                'adsets' => $adsets,
                'ads_count' => $campaignItems->count(),
            ];
        })->values();
@endphp

<section data-analysis-source-panel="paid" class="hidden" hidden style="display:none">
<div class="meta-panel meta-table-panel" style="margin-top:0">
    <div class="meta-panel-head"><div><h3>Content Plan Paid {{ $platform }}</h3><p>Rencana konten berbayar untuk platform {{ $platform }} pada periode terpilih.</p></div></div>
    <div class="meta-table-wrap"><table class="meta-table">
        <thead><tr><th>No</th><th>Tanggal</th><th>Judul</th><th>Type</th><th>Strategy</th><th>Pilar</th><th>Hook</th><th>Body</th><th>CTA</th><th>Status</th><th>Output</th></tr></thead>
        <tbody>
        @forelse($paidContentPlanRows ?? [] as $item)
            @php
                $contentTitle = $item->judul ?: 'Konten tanpa judul';
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->tanggal_posting ? \Carbon\Carbon::parse($item->tanggal_posting)->translatedFormat('d M Y') : '-' }}</td>
                <td class="title" title="{{ $contentTitle }}">{{ \Illuminate\Support\Str::limit($contentTitle, 70) }}</td>
                <td>{{ $item->type ?? '-' }}</td><td>{{ $item->strategy ?? '-' }}</td><td>{{ $item->pilar ?? '-' }}</td>
                <td title="{{ $item->hooks ?? '' }}">{{ \Illuminate\Support\Str::limit($item->hooks ?? '-', 42) }}</td>
                <td title="{{ $item->body ?? '' }}">{{ \Illuminate\Support\Str::limit($item->body ?? '-', 42) }}</td>
                <td title="{{ $item->cta ?? '' }}">{{ \Illuminate\Support\Str::limit($item->cta ?? '-', 42) }}</td>
                <td>{{ $item->nama_status ?? '-' }}</td>
                <td>@if($item->link)<a href="{{ $item->link }}" target="_blank" rel="noopener">Lihat</a>@else - @endif</td>
            </tr>
        @empty
            <tr><td colspan="11" class="meta-empty">Belum ada Content Plan Paid untuk {{ $platform }} pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@if($paidError)
    <div class="meta-panel"><div class="meta-empty"><strong>Data Paid belum tersedia</strong><br>{{ $paidError }}</div></div>
@else
    <div class="meta-field-toolbar"><div class="meta-field-picker"><button type="button" class="meta-v2-btn" data-paid-card-field-toggle>Atur Field Ringkasan</button><div class="meta-field-menu paid-field-menu" data-paid-card-field-menu hidden><h4>Field ringkasan Paid <span data-paid-card-field-count style="float:right;color:#71717a;font-weight:500"></span></h4><div class="meta-field-options paid-field-options">@foreach($paidMetricDefinitions as $fieldKey => $definition)<label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-paid-card-field-checkbox><span>{{ $definition[0] }}</span></label>@endforeach</div><p style="margin:9px 0 0;color:#71717a;font-size:9px">Pilih maksimal 9 metrik untuk kartu ringkasan.</p></div></div></div>
    <div class="meta-kpi-grid">@foreach($paidMetricDefinitions as $fieldKey => $metric)<article class="meta-kpi" data-paid-field="{{ $fieldKey }}"><label>{{ $metric[0] }}</label><strong>{{ $metric[1] }}</strong><small>Data Paid Meta</small><svg viewBox="0 0 62 25" fill="none"><path d="M1 20L10 16L18 18L28 9L38 13L49 6L61 2" stroke="#52525b" stroke-width="1.5"/><path d="M1 24H61" stroke="#e4e4e7"/></svg></article>@endforeach</div>

    <div class="meta-board-grid">
        <div class="meta-panel meta-chart-panel"><div class="meta-panel-head"><div><h3>Performance Trend Paid</h3><p>Pergerakan gabungan metrik paid per tanggal.</p></div><div class="meta-field-picker"><button type="button" class="meta-v2-btn" data-paid-field-toggle>Atur Field Chart</button><div class="meta-field-menu paid-field-menu" data-paid-field-menu hidden><h4>Seluruh field Meta <span data-paid-field-count style="float:right;color:#71717a;font-weight:500"></span></h4><div class="meta-field-options paid-field-options">@foreach($paidMetricDefinitions as $fieldKey => $definition)<label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-paid-field-checkbox><span>{{ $definition[0] }}</span></label>@endforeach</div><p style="margin:9px 0 0;color:#71717a;font-size:9px">Pilih minimal 1 dan maksimal 9 metrik untuk kartu dan line chart.</p></div></div></div><div class="meta-chart-body"><canvas data-paid-metrics-chart></canvas></div></div>
        <aside class="meta-panel"><div class="meta-panel-head"><div><h3>Insights &amp; Recommendation</h3><p>Temuan utama performa iklan.</p></div></div><div class="meta-insights">
            <div class="meta-insight"><span class="meta-insight-dot">01</span><div><strong>Iklan terbaik</strong><p title="{{ data_get($topPaidItem,'judul','Belum ada iklan') }}">{{ \Illuminate\Support\Str::limit(data_get($topPaidItem,'judul','Belum ada iklan'),70) }} memiliki engagement tertinggi.</p></div></div>
            <div class="meta-insight"><span class="meta-insight-dot">02</span><div><strong>Efisiensi klik</strong><p>CTR {{ $percent($ratio($paidClicks,$paidImpressions,100)) }} dengan CPC {{ $money($ratio($paidSpend,$paidClicks)) }}.</p></div></div>
            <div class="meta-insight"><span class="meta-insight-dot">03</span><div><strong>Distribusi</strong><p>{{ $number($paidItems->count()) }} iklan menjangkau {{ $number($paidReach) }} akun.</p></div></div>
            <div class="meta-insight"><span class="meta-insight-dot">04</span><div><strong>Belanja iklan</strong><p>Total spend {{ $money($paidSpend) }} pada periode terpilih.</p></div></div>
        </div></aside>
    </div>

    <div class="meta-panel meta-table-panel"><div class="meta-panel-head"><div><h3>Content Performance &amp; Analysis</h3><p>Klik Campaign, lalu Set Iklan untuk melihat struktur dan performa Paid {{ $platform }}.</p></div><button type="button" class="meta-v2-btn paid-tree-collapse-all" data-paid-tree-collapse>Ciutkan Semua</button></div><div class="meta-table-wrap"><table class="meta-table paid-tree-table"><thead><tr><th>No</th><th>Campaign</th><th>Set Iklan</th><th>Iklan</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th><th>Reach</th><th>Impresi</th><th>Klik</th><th>Engagement</th><th>Spend</th></tr></thead><tbody>
    @forelse($paidHierarchy as $campaign)
        <tr class="paid-tree-row paid-tree-campaign" data-paid-tree-row="{{ $campaign['key'] }}" data-paid-tree-level="campaign">
            <td>{{ $loop->iteration }}</td>
            <td class="title"><button type="button" class="paid-tree-toggle" data-paid-tree-toggle="{{ $campaign['key'] }}" aria-expanded="false"><span class="paid-tree-chevron" aria-hidden="true">›</span><span title="{{ $campaign['name'] }}">{{ \Illuminate\Support\Str::limit($campaign['name'],45) }}</span><small>{{ $campaign['adsets']->count() }} set · {{ $campaign['ads_count'] }} iklan</small></button></td>
            <td>—</td><td>—</td><td>{{ $campaign['dates']['start'] }}</td><td>{{ $campaign['dates']['end'] }}</td><td>{{ $number($campaign['metrics']['reach']) }}</td><td>{{ $number($campaign['metrics']['impressions']) }}</td><td>{{ $number($campaign['metrics']['clicks']) }}</td><td>{{ $number($campaign['metrics']['engagement']) }}</td><td>{{ $money($campaign['metrics']['spend']) }}</td>
        </tr>
        @foreach($campaign['adsets'] as $adset)
            <tr class="paid-tree-row paid-tree-adset" data-paid-tree-row="{{ $adset['key'] }}" data-paid-tree-parent="{{ $campaign['key'] }}" data-paid-tree-level="adset" hidden>
                <td></td><td class="paid-tree-guide">↳</td>
                <td class="title"><button type="button" class="paid-tree-toggle" data-paid-tree-toggle="{{ $adset['key'] }}" aria-expanded="false"><span class="paid-tree-chevron" aria-hidden="true">›</span><span title="{{ $adset['name'] }}">{{ \Illuminate\Support\Str::limit($adset['name'],45) }}</span><small>{{ $adset['ads']->count() }} iklan</small></button></td>
                <td>—</td><td>{{ $adset['dates']['start'] }}</td><td>{{ $adset['dates']['end'] }}</td><td>{{ $number($adset['metrics']['reach']) }}</td><td>{{ $number($adset['metrics']['impressions']) }}</td><td>{{ $number($adset['metrics']['clicks']) }}</td><td>{{ $number($adset['metrics']['engagement']) }}</td><td>{{ $money($adset['metrics']['spend']) }}</td>
            </tr>
            @php
                $adsetDetails = $adset['details'] ?? [];
                $targeting = data_get($adsetDetails, 'targeting', []);
                $geo = data_get($targeting, 'geo_locations', []);
                $countries = collect(data_get($geo, 'countries', []));
                $regions = collect(data_get($geo, 'regions', []))->map(fn ($region) => $region['name'] ?? $region['key'] ?? null)->filter();
                $cities = collect(data_get($geo, 'cities', []))->map(fn ($city) => $city['name'] ?? $city['key'] ?? null)->filter();
                $genderLabels = collect(data_get($targeting, 'genders', []))->map(fn ($gender) => (int) $gender === 1 ? 'Pria' : ((int) $gender === 2 ? 'Wanita' : 'Semua'));
                $audienceDetails = collect(data_get($targeting, 'flexible_spec', []))->flatMap(function ($spec) {
                    return collect($spec)->flatten(1)->map(fn ($entry) => is_array($entry) ? ($entry['name'] ?? $entry['id'] ?? null) : $entry);
                })->filter()->unique()->take(12);
                $customAudiences = collect(data_get($targeting, 'custom_audiences', []))->map(fn ($audience) => $audience['name'] ?? $audience['id'] ?? null)->filter();
                $excludedAudiences = collect(data_get($targeting, 'excluded_custom_audiences', []))->map(fn ($audience) => $audience['name'] ?? $audience['id'] ?? null)->filter();
                $placements = collect([
                    'Platform' => data_get($targeting, 'publisher_platforms', []),
                    'Facebook' => data_get($targeting, 'facebook_positions', []),
                    'Instagram' => data_get($targeting, 'instagram_positions', []),
                    'Messenger' => data_get($targeting, 'messenger_positions', []),
                    'Audience Network' => data_get($targeting, 'audience_network_positions', []),
                    'Perangkat' => data_get($targeting, 'device_platforms', []),
                ])->filter(fn ($values) => count((array) $values) > 0);
            @endphp
            <tr class="paid-tree-row paid-tree-detail-row" data-paid-tree-parent="{{ $adset['key'] }}" data-paid-tree-level="detail" hidden>
                <td></td><td colspan="10">
                    <div class="paid-adset-detail">
                        <section><h4>Ringkasan</h4><dl><div><dt>Status</dt><dd>{{ $metaLabel(data_get($adsetDetails, 'effective_status') ?: data_get($adsetDetails, 'status')) }}</dd></div><div><dt>Optimasi</dt><dd>{{ $metaLabel(data_get($adsetDetails, 'optimization_goal')) }}</dd></div><div><dt>Penagihan</dt><dd>{{ $metaLabel(data_get($adsetDetails, 'billing_event')) }}</dd></div><div><dt>Strategi bid</dt><dd>{{ $metaLabel(data_get($adsetDetails, 'bid_strategy')) }}</dd></div></dl></section>
                        <section><h4>Budget &amp; Delivery</h4><dl><div><dt>Budget harian</dt><dd>{{ data_get($adsetDetails, 'daily_budget') !== null ? $money(data_get($adsetDetails, 'daily_budget')) : '-' }}</dd></div><div><dt>Budget lifetime</dt><dd>{{ data_get($adsetDetails, 'lifetime_budget') !== null ? $money(data_get($adsetDetails, 'lifetime_budget')) : '-' }}</dd></div><div><dt>Sisa budget</dt><dd>{{ data_get($adsetDetails, 'budget_remaining') !== null ? $money(data_get($adsetDetails, 'budget_remaining')) : '-' }}</dd></div><div><dt>Jadwal</dt><dd>{{ $adset['dates']['start'] }} – {{ $adset['dates']['end'] }}</dd></div></dl></section>
                        <section><h4>Audience</h4><dl><div><dt>Usia</dt><dd>{{ data_get($targeting, 'age_min', 18) }}–{{ data_get($targeting, 'age_max', '65+') }}</dd></div><div><dt>Gender</dt><dd>{{ $genderLabels->isNotEmpty() ? $genderLabels->join(', ') : 'Semua' }}</dd></div><div><dt>Lokasi</dt><dd>{{ $countries->concat($regions)->concat($cities)->unique()->take(8)->join(', ') ?: '-' }}</dd></div>@if($audienceDetails->isNotEmpty())<div><dt>Target terperinci</dt><dd>{{ $audienceDetails->join(', ') }}</dd></div>@endif @if($customAudiences->isNotEmpty())<div><dt>Custom audience</dt><dd>{{ $customAudiences->join(', ') }}</dd></div>@endif @if($excludedAudiences->isNotEmpty())<div><dt>Dikecualikan</dt><dd>{{ $excludedAudiences->join(', ') }}</dd></div>@endif</dl></section>
                        <section><h4>Placement</h4>@forelse($placements as $placementLabel => $values)<div class="paid-placement-group"><span>{{ $placementLabel }}</span><div>@foreach((array) $values as $value)<b>{{ $metaLabel($value) }}</b>@endforeach</div></div>@empty<p class="paid-detail-empty">Placement otomatis / tidak dirinci oleh Meta.</p>@endforelse</section>
                    </div>
                </td>
            </tr>
            @foreach($adset['ads'] as $item)
                <tr class="paid-tree-row paid-tree-ad" data-paid-tree-parent="{{ $adset['key'] }}" data-paid-tree-level="ad" hidden>
                    <td></td><td class="paid-tree-guide">·</td><td class="paid-tree-guide">↳</td><td class="title" title="{{ $item['judul'] }}">{{ \Illuminate\Support\Str::limit($item['judul'],55) }}</td><td>{{ $item['start_date'] ?? '-' }}</td><td>{{ $item['end_date'] ?? '-' }}</td><td>{{ $number($item['reach']) }}</td><td>{{ $number($item['impressions']) }}</td><td>{{ $number($item['clicks']) }}</td><td>{{ $number($item['engagement']) }}</td><td>{{ $money($item['spend']) }}</td>
                </tr>
            @endforeach
        @endforeach
    @empty
        <tr><td colspan="11" class="meta-empty">Belum ada iklan {{ $platform }} pada periode yang dipilih.</td></tr>
    @endforelse
    </tbody></table></div></div>
@endif
</section>

<style>[data-paid-field][hidden]{display:none!important}.paid-field-menu{width:430px;max-width:min(430px,calc(100vw - 32px))}.paid-field-options{max-height:330px;grid-template-columns:repeat(2,minmax(0,1fr));overflow-y:auto;padding-right:4px}</style>
<style>
.paid-tree-table{min-width:1180px}.paid-tree-table th:nth-child(2),.paid-tree-table th:nth-child(3),.paid-tree-table th:nth-child(4){min-width:185px}.paid-tree-row[hidden]{display:none!important}.paid-tree-row{transition:background-color .15s ease}.paid-tree-campaign{background:#f4f4f5}.paid-tree-campaign td{border-top-color:#e4e4e7;color:#18181b;font-weight:700}.paid-tree-adset{background:#fafafa}.paid-tree-adset td{color:#3f3f46;font-weight:600}.paid-tree-ad td{background:#fff}.paid-tree-toggle{display:flex;width:100%;align-items:center;gap:7px;border:0;background:transparent;padding:0;color:inherit;text-align:left;font:inherit;cursor:pointer}.paid-tree-toggle small{margin-left:auto;border-radius:999px;background:#fff;padding:3px 7px;color:#71717a;font-size:8px;font-weight:650;white-space:nowrap}.paid-tree-chevron{display:grid;width:18px;height:18px;flex:0 0 18px;place-items:center;border:1px solid #d4d4d8;border-radius:5px;background:#fff;font-size:17px;line-height:1;transition:transform .18s ease}.paid-tree-toggle[aria-expanded="true"] .paid-tree-chevron{transform:rotate(90deg);border-color:#a1a1aa}.paid-tree-guide{color:#a1a1aa!important;text-align:center}.paid-tree-ad td:nth-child(4){padding-left:20px}.paid-tree-collapse-all{min-height:34px;padding:6px 10px}.paid-tree-detail-row>td{padding:0!important;background:#f7f7f8}.paid-adset-detail{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;margin:0 12px 12px;border:1px solid #e4e4e7;border-radius:12px;overflow:hidden;background:#e4e4e7}.paid-adset-detail section{min-width:0;background:#fff;padding:15px}.paid-adset-detail h4{margin:0 0 12px;color:#18181b;font-size:10px;text-transform:uppercase;letter-spacing:.08em}.paid-adset-detail dl{display:grid;gap:9px;margin:0}.paid-adset-detail dl div{display:grid;gap:3px}.paid-adset-detail dt,.paid-placement-group>span{color:#a1a1aa;font-size:8px;font-weight:650}.paid-adset-detail dd{margin:0;color:#3f3f46;font-size:9px;font-weight:650;line-height:1.45}.paid-placement-group{display:grid;gap:5px;margin-bottom:10px}.paid-placement-group div{display:flex;flex-wrap:wrap;gap:4px}.paid-placement-group b{border-radius:999px;background:#f4f4f5;padding:4px 7px;color:#52525b;font-size:8px;font-weight:650}.paid-detail-empty{margin:0;color:#71717a;font-size:9px;line-height:1.5}@media(max-width:1100px){.paid-adset-detail{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:760px){.paid-tree-collapse-all{display:none}.paid-adset-detail{grid-template-columns:1fr}}
</style>
<script>
document.addEventListener('DOMContentLoaded',function(){
    const table=document.querySelector('.paid-tree-table');
    if(!table)return;
    const directChildren=parent=>[...table.querySelectorAll(`[data-paid-tree-parent="${parent}"]`)];
    function closeBranch(key){
        const toggle=table.querySelector(`[data-paid-tree-toggle="${key}"]`);
        if(toggle)toggle.setAttribute('aria-expanded','false');
        directChildren(key).forEach(row=>{
            row.hidden=true;
            const childKey=row.dataset.paidTreeRow;
            if(childKey)closeBranch(childKey);
        });
    }
    table.addEventListener('click',event=>{
        const toggle=event.target.closest('[data-paid-tree-toggle]');
        if(!toggle)return;
        const key=toggle.dataset.paidTreeToggle,isOpen=toggle.getAttribute('aria-expanded')==='true';
        if(isOpen){closeBranch(key);return;}
        toggle.setAttribute('aria-expanded','true');
        directChildren(key).forEach(row=>row.hidden=false);
    });
    document.querySelector('[data-paid-tree-collapse]')?.addEventListener('click',()=>{
        table.querySelectorAll('[data-paid-tree-level="campaign"]').forEach(row=>closeBranch(row.dataset.paidTreeRow));
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
const raw=@json($paidItems->values()),definitions=@json(collect($paidMetricDefinitions)->map(fn($metric)=>['label'=>$metric[0],'unit'=>$metric[2]])),max=9,key='meta-analysis-paid-chart-fields-{{ strtolower($platform) }}-v2',defaults=['reach','impressions','clicks','ctr'],checks=[...document.querySelectorAll('[data-paid-field-checkbox]')],menu=document.querySelector('[data-paid-field-menu]'),toggle=document.querySelector('[data-paid-field-toggle]'),count=document.querySelector('[data-paid-field-count]'),canvas=document.querySelector('[data-paid-metrics-chart]'),colors=['#18181b','#64748b','#4f46e5','#0f766e','#ea580c','#be123c','#7e22ce','#0369a1','#65a30d'];let chart,selected;try{selected=JSON.parse(localStorage.getItem(key))}catch(e){selected=null}if(!Array.isArray(selected)||!selected.length)selected=defaults;selected=selected.filter(field=>definitions[field]).slice(0,max);
const formatter=new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'short',year:'numeric'}),daily=new Map();
raw.forEach((item,index)=>{const date=new Date(item.tanggal_posting),valid=!Number.isNaN(date.getTime()),dayKey=valid?date.toISOString().slice(0,10):(item.tanggal_posting||`tanpa-tanggal-${index}`),current=daily.get(dayKey)||{tanggal_posting:valid?formatter.format(date):(item.tanggal_posting||'-'),timestamp:valid?date.getTime():Number.MAX_SAFE_INTEGER,_ad_count:0};Object.entries(item).forEach(([field,rawValue])=>{if(field==='tanggal_posting'||field==='judul')return;const numeric=Number(rawValue);if(Number.isFinite(numeric))current[field]=Number(current[field]||0)+numeric});current._ad_count+=1;daily.set(dayKey,current)});
const chartItems=[...daily.values()].sort((a,b)=>a.timestamp-b.timestamp);
function ratio(top,bottom,multiplier=1){return Number(bottom)>0?Number(top||0)/Number(bottom)*multiplier:0}
function value(item,field){if(field==='ads')return item._ad_count;if(field==='frequency')return ratio(item.impressions,item.reach);if(field==='ctr')return ratio(item.clicks,item.impressions,100);if(field==='unique_ctr')return ratio(item.unique_clicks,item.reach,100);if(field==='inline_link_click_ctr')return ratio(item.inline_link_clicks,item.impressions,100);if(field==='cpc')return ratio(item.spend,item.clicks);if(field==='cpm')return ratio(item.spend,item.impressions,1000);if(field==='cpp')return ratio(item.spend,item.reach,1000);return Number(item[field]||0)}
function datasets(){return selected.map((field,index)=>({label:definitions[field].label,data:chartItems.map(item=>value(item,field)),borderColor:colors[index],backgroundColor:index===0?'rgba(24,24,27,.08)':'transparent',fill:index===0,tension:.35,borderWidth:2.5,pointRadius:5,pointHoverRadius:7,pointBorderWidth:2,pointBorderColor:colors[index],pointBackgroundColor:'#fff',yAxisID:definitions[field].unit==='currency'?'yCurrency':definitions[field].unit==='percent'?'yPercent':'y'}))}
function render(){if(!canvas||typeof Chart==='undefined')return;const labels=chartItems.map(item=>item.tanggal_posting);if(chart){chart.data.labels=labels;chart.data.datasets=datasets();chart.update();chart.resize();return}chart=new Chart(canvas.getContext('2d'),{type:'line',data:{labels,datasets:datasets()},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom'},tooltip:{callbacks:{afterTitle(context){const item=chartItems[context[0]?.dataIndex];return item?`${item._ad_count} iklan pada tanggal ini`:''}}}},scales:{x:{grid:{display:false},ticks:{minRotation:0,maxRotation:0,autoSkip:true,maxTicksLimit:10}},y:{beginAtZero:true,position:'left',grid:{color:'#f1f5f9'}},yCurrency:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},display:'auto'},yPercent:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},display:'auto'}}}})}
function apply(){checks.forEach(check=>{check.checked=selected.includes(check.value);check.disabled=!check.checked&&selected.length>=max});if(count)count.textContent=`${selected.length}/${max}`;render()}
toggle?.addEventListener('click',event=>{event.stopPropagation();menu.hidden=!menu.hidden});checks.forEach(check=>check.addEventListener('change',()=>{const next=checks.filter(item=>item.checked).map(item=>item.value);if(!next.length||next.length>max){check.checked=!check.checked;return}selected=next;localStorage.setItem(key,JSON.stringify(selected));apply()}));document.addEventListener('click',event=>{if(menu&&!event.target.closest('[data-paid-field-menu]')&&!event.target.closest('[data-paid-field-toggle]'))menu.hidden=true});window.addEventListener('analysis:source-changed',event=>{if(event.detail?.active==='paid')requestAnimationFrame(render)});localStorage.setItem(key,JSON.stringify(selected));apply();
});
</script>
<script>
document.addEventListener('DOMContentLoaded',function(){
const max=9,key='meta-analysis-paid-card-fields-{{ strtolower($platform) }}-v2',defaults=['ads','reach','impressions','clicks','ctr','cpc','engagement','spend'],cards=[...document.querySelectorAll('[data-paid-field]')],checks=[...document.querySelectorAll('[data-paid-card-field-checkbox]')],menu=document.querySelector('[data-paid-card-field-menu]'),toggle=document.querySelector('[data-paid-card-field-toggle]'),count=document.querySelector('[data-paid-card-field-count]');let selected;try{selected=JSON.parse(localStorage.getItem(key))}catch(e){selected=null}if(!Array.isArray(selected)||!selected.length)selected=defaults;selected=selected.filter(value=>checks.some(check=>check.value===value)).slice(0,max);if(!selected.length)selected=defaults;
function apply(){cards.forEach(card=>card.hidden=!selected.includes(card.dataset.paidField));checks.forEach(check=>{check.checked=selected.includes(check.value);check.disabled=!check.checked&&selected.length>=max});if(count)count.textContent=`${selected.length}/${max}`}
toggle?.addEventListener('click',event=>{event.stopPropagation();menu.hidden=!menu.hidden});checks.forEach(check=>check.addEventListener('change',()=>{const next=checks.filter(item=>item.checked).map(item=>item.value);if(!next.length||next.length>max){check.checked=!check.checked;return}selected=next;localStorage.setItem(key,JSON.stringify(selected));apply()}));document.addEventListener('click',event=>{if(menu&&!event.target.closest('[data-paid-card-field-menu]')&&!event.target.closest('[data-paid-card-field-toggle]'))menu.hidden=true});localStorage.setItem(key,JSON.stringify(selected));apply();
});
</script>
