@php
    $paidItems = collect($paidPosts ?? []);
    $sum = fn (string $key) => (float) $paidItems->sum($key);
    $paidReach = $sum('reach'); $paidImpressions = $sum('impressions'); $paidClicks = $sum('clicks');
    $paidUniqueClicks = $sum('unique_clicks'); $paidSpend = $sum('spend'); $paidCurrencyLabel = $paidCurrency ?? 'IDR';
    $number = fn ($value, int $decimals = 0) => number_format($value, $decimals, ',', '.');
    $money = fn ($value) => $paidCurrencyLabel.' '.$number($value, 0);
    $percent = fn ($value) => $number($value, 2).'%';
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
@endphp

<section data-analysis-source-panel="paid" class="hidden" hidden style="display:none">
@if($paidError)
    <div class="meta-panel"><div class="meta-empty"><strong>Data Paid belum tersedia</strong><br>{{ $paidError }}</div></div>
@else
    <div class="meta-kpi-grid">@foreach($paidMetricDefinitions as $fieldKey => $metric)<article class="meta-kpi" data-paid-field="{{ $fieldKey }}"><label>{{ $metric[0] }}</label><strong>{{ $metric[1] }}</strong><small>Data Paid Meta</small><svg viewBox="0 0 62 25" fill="none"><path d="M1 20L10 16L18 18L28 9L38 13L49 6L61 2" stroke="#52525b" stroke-width="1.5"/><path d="M1 24H61" stroke="#e4e4e7"/></svg></article>@endforeach</div>

    <div class="meta-board-grid">
        <div class="meta-panel meta-chart-panel"><div class="meta-panel-head"><div><h3>Performance Trend Paid</h3><p>Pergerakan metrik iklan yang dipilih.</p></div><div class="meta-field-picker"><button type="button" class="meta-v2-btn" data-paid-field-toggle>Atur Field Chart</button><div class="meta-field-menu paid-field-menu" data-paid-field-menu hidden><h4>Seluruh field Meta <span data-paid-field-count style="float:right;color:#71717a;font-weight:500"></span></h4><div class="meta-field-options paid-field-options">@foreach($paidMetricDefinitions as $fieldKey => $definition)<label class="meta-field-option"><input type="checkbox" value="{{ $fieldKey }}" data-paid-field-checkbox><span>{{ $definition[0] }}</span></label>@endforeach</div><p style="margin:9px 0 0;color:#71717a;font-size:9px">Pilih minimal 1 dan maksimal 9 metrik untuk kartu dan line chart.</p></div></div></div><div class="meta-chart-body"><canvas data-paid-metrics-chart></canvas></div></div>
        <aside class="meta-panel"><div class="meta-panel-head"><div><h3>Insights &amp; Recommendation</h3><p>Temuan utama performa iklan.</p></div></div><div class="meta-insights">
            <div class="meta-insight"><span class="meta-insight-dot">01</span><div><strong>Iklan terbaik</strong><p title="{{ data_get($topPaidItem,'judul','Belum ada iklan') }}">{{ \Illuminate\Support\Str::limit(data_get($topPaidItem,'judul','Belum ada iklan'),70) }} memiliki engagement tertinggi.</p></div></div>
            <div class="meta-insight"><span class="meta-insight-dot">02</span><div><strong>Efisiensi klik</strong><p>CTR {{ $percent($ratio($paidClicks,$paidImpressions,100)) }} dengan CPC {{ $money($ratio($paidSpend,$paidClicks)) }}.</p></div></div>
            <div class="meta-insight"><span class="meta-insight-dot">03</span><div><strong>Distribusi</strong><p>{{ $number($paidItems->count()) }} iklan menjangkau {{ $number($paidReach) }} akun.</p></div></div>
            <div class="meta-insight"><span class="meta-insight-dot">04</span><div><strong>Belanja iklan</strong><p>Total spend {{ $money($paidSpend) }} pada periode terpilih.</p></div></div>
        </div></aside>
    </div>

    <div class="meta-panel meta-table-panel"><div class="meta-panel-head"><div><h3>Content Performance &amp; Analysis</h3><p>Data performa Paid {{ $platform }}.</p></div></div><div class="meta-table-wrap"><table class="meta-table"><thead><tr><th>No</th><th>Iklan</th><th>Campaign</th><th>Tanggal</th><th>Reach</th><th>Impresi</th><th>Klik</th><th>Engagement</th><th>Spend</th></tr></thead><tbody>
    @forelse($paidItems as $item)<tr><td>{{ $loop->iteration }}</td><td class="title" title="{{ $item['judul'] }}">{{ \Illuminate\Support\Str::limit($item['judul'],60) }}</td><td title="{{ $item['campaign'] }}">{{ \Illuminate\Support\Str::limit($item['campaign'],45) }}</td><td>{{ $item['tanggal_posting'] }}</td><td>{{ $number($item['reach']) }}</td><td>{{ $number($item['impressions']) }}</td><td>{{ $number($item['clicks']) }}</td><td>{{ $number($item['engagement']) }}</td><td>{{ $money($item['spend']) }}</td></tr>@empty<tr><td colspan="9" class="meta-empty">Belum ada iklan {{ $platform }} pada periode yang dipilih.</td></tr>@endforelse
    </tbody></table></div></div>
@endif
</section>

<style>[data-paid-field][hidden]{display:none!important}.paid-field-menu{width:430px;max-width:min(430px,calc(100vw - 32px))}.paid-field-options{max-height:330px;grid-template-columns:repeat(2,minmax(0,1fr));overflow-y:auto;padding-right:4px}</style>
<script>
document.addEventListener('DOMContentLoaded',function(){
const raw=@json($paidItems->values()),definitions=@json(collect($paidMetricDefinitions)->map(fn($metric)=>['label'=>$metric[0],'unit'=>$metric[2]])),max=9,key='meta-analysis-paid-fields-{{ strtolower($platform) }}-v2',defaults=['ads','reach','impressions','clicks','ctr','cpc','engagement','spend'],cards=[...document.querySelectorAll('[data-paid-field]')],checks=[...document.querySelectorAll('[data-paid-field-checkbox]')],menu=document.querySelector('[data-paid-field-menu]'),toggle=document.querySelector('[data-paid-field-toggle]'),count=document.querySelector('[data-paid-field-count]'),canvas=document.querySelector('[data-paid-metrics-chart]'),colors=['#18181b','#64748b','#4f46e5','#0f766e','#ea580c','#be123c','#7e22ce','#0369a1','#65a30d'];let chart,selected;try{selected=JSON.parse(localStorage.getItem(key))}catch(e){selected=null}if(!Array.isArray(selected)||!selected.length)selected=defaults;selected=selected.filter(field=>definitions[field]).slice(0,max);
function value(item,field){if(field==='ads')return 1;if(field==='ctr')return Number(item.impressions)>0?Number(item.clicks||0)/Number(item.impressions)*100:0;if(field==='unique_ctr')return Number(item.reach)>0?Number(item.unique_clicks||0)/Number(item.reach)*100:0;if(field==='inline_link_click_ctr')return Number(item.impressions)>0?Number(item.inline_link_clicks||0)/Number(item.impressions)*100:0;return Number(item[field]||0)}
function datasets(){return selected.map((field,index)=>({label:definitions[field].label,data:raw.map(item=>value(item,field)),borderColor:colors[index],backgroundColor:index===0?'rgba(24,24,27,.08)':'transparent',fill:index===0,tension:.35,pointRadius:2,yAxisID:definitions[field].unit==='currency'?'yCurrency':definitions[field].unit==='percent'?'yPercent':'y'}))}
function render(){if(!canvas||typeof Chart==='undefined')return;const labels=raw.map(item=>{const title=item.judul||item.tanggal_posting||'Iklan';return title.length>22?`${title.slice(0,22)}…`:title});if(chart){chart.data.labels=labels;chart.data.datasets=datasets();chart.update();chart.resize();return}chart=new Chart(canvas.getContext('2d'),{type:'line',data:{labels,datasets:datasets()},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom'}},scales:{x:{grid:{display:false},ticks:{minRotation:0,maxRotation:0,autoSkip:true,maxTicksLimit:10}},y:{beginAtZero:true,position:'left',grid:{color:'#f1f5f9'}},yCurrency:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},display:'auto'},yPercent:{beginAtZero:true,position:'right',grid:{drawOnChartArea:false},display:'auto'}}}})}
function apply(){cards.forEach(card=>card.hidden=!selected.includes(card.dataset.paidField));checks.forEach(check=>{check.checked=selected.includes(check.value);check.disabled=!check.checked&&selected.length>=max});if(count)count.textContent=`${selected.length}/${max}`;render()}
toggle?.addEventListener('click',event=>{event.stopPropagation();menu.hidden=!menu.hidden});checks.forEach(check=>check.addEventListener('change',()=>{const next=checks.filter(item=>item.checked).map(item=>item.value);if(!next.length||next.length>max){check.checked=!check.checked;return}selected=next;localStorage.setItem(key,JSON.stringify(selected));apply()}));document.addEventListener('click',event=>{if(menu&&!event.target.closest('[data-paid-field-menu]')&&!event.target.closest('[data-paid-field-toggle]'))menu.hidden=true});window.addEventListener('analysis:source-changed',event=>{if(event.detail?.active==='paid')requestAnimationFrame(render)});localStorage.setItem(key,JSON.stringify(selected));apply();
});
</script>
