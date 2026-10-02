@php
    $trendLabels = [
        'posts' => 'Jumlah Konten', 'reach' => 'Reach', 'impressions' => 'Impresi',
        'engagement' => 'Engagement', 'likes' => 'Like/Reaksi', 'comments' => 'Komentar',
        'shares' => 'Share', 'clicks' => 'Klik', 'spend' => 'Spend',
    ];
    $trendPeriods = ['three_days' => 3, 'seven_days' => 7];
    $trendSources = ['organic' => 'Organik', 'paid' => 'Paid'];
@endphp

<style>
.trend-dashboard{display:grid;gap:14px}.trend-source-tabs{display:flex;gap:4px;width:max-content;border:1px solid #e4e4e7;border-radius:11px;background:#fff;padding:4px}.trend-source-tab{min-width:110px;border:0;border-radius:8px;background:transparent;padding:9px 16px;color:#71717a;font-size:11px;font-weight:750;cursor:pointer}.trend-source-tab.is-active{background:#18181b;color:#fff}.trend-source-panel[hidden]{display:none!important}.trend-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.trend-head h2{margin:3px 0 0;font-size:18px}.trend-head p{margin:0;color:#a1a1aa;font-size:10px}.trend-date-badge{border-radius:999px;background:#f4f4f5;padding:7px 11px;color:#52525b;font-size:10px;font-weight:700}.trend-kpi-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}.trend-kpi{min-width:0;border:1px solid #e4e4e7;border-radius:12px;background:#fff;padding:15px;box-shadow:0 1px 3px rgba(24,24,27,.04)}.trend-kpi label{display:block;overflow:hidden;color:#52525b;font-size:9px;font-weight:750;text-overflow:ellipsis;text-transform:uppercase;white-space:nowrap}.trend-kpi strong{display:block;margin-top:9px;font-size:21px;letter-spacing:-.035em}.trend-kpi-progress{display:flex;align-items:center;gap:5px;margin-top:7px;font-size:9px;font-weight:700}.trend-kpi-progress.is-up{color:#15803d}.trend-kpi-progress.is-down{color:#be123c}.trend-kpi-progress.is-steady{color:#71717a}.trend-kpi small{display:block;margin-top:5px;color:#a1a1aa;font-size:8px}.trend-main-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(250px,.72fr);gap:14px}.trend-chart{height:340px;padding:17px}.trend-summary{display:grid;gap:9px;padding:13px}.trend-summary-item{display:flex;align-items:center;justify-content:space-between;gap:12px;border:1px solid #f0f0f1;border-radius:10px;padding:11px}.trend-summary-item span{font-size:10px}.trend-summary-item strong{font-size:12px}.trend-status{display:inline-flex;align-items:center;border-radius:999px;padding:4px 8px;font-size:8px;font-weight:750}.trend-status--up{background:#dcfce7;color:#166534}.trend-status--down{background:#ffe4e6;color:#9f1239}.trend-status--new{background:#dbeafe;color:#1d4ed8}.trend-status--steady{background:#f4f4f5;color:#52525b}.trend-progress-table td.title{max-width:340px}.trend-item-context{display:block;margin-top:3px;color:#a1a1aa;font-size:8px;font-weight:500}.trend-delta{font-weight:750}.trend-delta.is-up{color:#15803d}.trend-delta.is-down{color:#be123c}.trend-empty{border:1px dashed #d4d4d8;border-radius:13px;background:#fafafa;padding:35px;text-align:center;color:#71717a;font-size:11px;line-height:1.6}@media(max-width:1200px){.trend-kpi-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:900px){.trend-main-grid{grid-template-columns:1fr}}@media(max-width:600px){.trend-kpi-grid{grid-template-columns:repeat(2,1fr)}.trend-head{flex-direction:column}}
</style>

@foreach($trendPeriods as $periodKey => $dayCount)
    <section data-analysis-period-panel="{{ $periodKey }}" hidden style="display:none;" class="trend-dashboard">
        <div class="trend-source-tabs" role="tablist" aria-label="Sumber progres {{ $dayCount }} hari">
            @foreach($trendSources as $sourceKey => $sourceLabel)
                <button type="button" class="trend-source-tab {{ $loop->first ? 'is-active' : '' }}" data-trend-source-tab="{{ $periodKey }}:{{ $sourceKey }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $sourceLabel }}</button>
            @endforeach
        </div>

        @foreach($trendSources as $sourceKey => $sourceLabel)
            @php
                $comparison = data_get($trendComparisons ?? [], $sourceKey.'.'.$periodKey, []);
                $metrics = collect($comparison['metrics'] ?? []);
                $items = collect($comparison['items'] ?? []);
                $upCount = $items->where('status', 'up')->count();
                $downCount = $items->where('status', 'down')->count();
                $newCount = $items->where('status', 'new')->count();
                $steadyCount = $items->where('status', 'steady')->count();
                $chartItems = $items->take(10)->values()->map(fn ($item) => [
                    'title' => \Illuminate\Support\Str::limit($item['title'] ?? 'Konten', 30),
                    'reach_current' => data_get($item, 'progress.reach.current', 0),
                    'reach_previous' => data_get($item, 'progress.reach.previous', 0),
                    'engagement_current' => data_get($item, 'progress.engagement.current', 0),
                    'engagement_previous' => data_get($item, 'progress.engagement.previous', 0),
                ])->all();
            @endphp
            <div class="trend-source-panel" data-trend-source-panel="{{ $periodKey }}:{{ $sourceKey }}" @if(!$loop->first) hidden @endif>
                <div class="trend-head">
                    <div><p>{{ $sourceLabel }} · {{ $platform }}</p><h2>Progress {{ $dayCount }} Hari</h2></div>
                    @if($comparison['available'] ?? false)<span class="trend-date-badge">vs {{ \Carbon\Carbon::parse($comparison['previous_date'])->translatedFormat('d M Y') }}</span>@endif
                </div>

                @if(!($comparison['available'] ?? false))
                    <div class="trend-empty">Snapshot {{ $dayCount }} hari sebelumnya belum tersedia.<br>Sistem akan menampilkan progres otomatis setelah riwayat data mencukupi.</div>
                @else
                    <div class="trend-kpi-grid">
                        @foreach($metrics as $key => $metric)
                            @php
                                $delta = (float) ($metric['delta'] ?? 0);
                                $percent = $metric['percent'] ?? null;
                                $isSpend = $key === 'spend';
                                $state = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'steady');
                            @endphp
                            <article class="trend-kpi"><label>{{ $trendLabels[$key] ?? ucfirst($key) }}</label><strong>{{ $isSpend ? ($paidCurrency ?? 'IDR').' ' : '' }}{{ number_format($metric['current'] ?? 0, 0, ',', '.') }}</strong><span class="trend-kpi-progress is-{{ $state }}">{{ $delta > 0 ? '↑ Naik' : ($delta < 0 ? '↓ Turun' : '— Tetap') }} {{ $isSpend ? ($paidCurrency ?? 'IDR').' ' : '' }}{{ number_format(abs($delta), 0, ',', '.') }}@if($percent !== null) · {{ number_format(abs($percent), 1, ',', '.') }}%@endif</span><small>Sebelumnya {{ $isSpend ? ($paidCurrency ?? 'IDR').' ' : '' }}{{ number_format($metric['previous'] ?? 0, 0, ',', '.') }}</small></article>
                        @endforeach
                    </div>

                    <div class="trend-main-grid">
                        <article class="meta-panel"><div class="meta-panel-head"><div><h3>Progress Konten {{ $dayCount }} Hari</h3><p>Perbandingan reach dan engagement setiap {{ $sourceKey === 'paid' ? 'iklan' : 'postingan' }}.</p></div></div><div class="trend-chart">@if($chartItems !== [])<canvas data-trend-item-chart data-trend-items="{{ e(json_encode($chartItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}"></canvas>@else<div class="trend-empty">Riwayat per postingan mulai dicatat dari snapshot terbaru.</div>@endif</div></article>
                        <aside class="meta-panel"><div class="meta-panel-head"><div><h3>Ringkasan Progress</h3><p>Perubahan dibandingkan {{ $dayCount }} hari lalu.</p></div></div><div class="trend-summary"><div class="trend-summary-item"><span>Meningkat</span><strong class="trend-status trend-status--up">{{ $upCount }} konten</strong></div><div class="trend-summary-item"><span>Menurun</span><strong class="trend-status trend-status--down">{{ $downCount }} konten</strong></div><div class="trend-summary-item"><span>Konten baru</span><strong class="trend-status trend-status--new">{{ $newCount }} konten</strong></div><div class="trend-summary-item"><span>Belum berubah</span><strong class="trend-status trend-status--steady">{{ $steadyCount }} konten</strong></div></div></aside>
                    </div>

                    <article class="meta-panel meta-table-panel"><div class="meta-panel-head"><div><h3>Progress per {{ $sourceKey === 'paid' ? 'Iklan' : 'Postingan' }}</h3><p>Konten dengan progress terbesar ditampilkan lebih dahulu.</p></div></div><div class="meta-table-wrap"><table class="meta-table trend-progress-table"><thead><tr><th>No</th><th>{{ $sourceKey === 'paid' ? 'Iklan' : 'Postingan' }}</th><th>Reach Sebelumnya</th><th>Reach Sekarang</th><th>Progress Reach</th><th>Engagement Sebelumnya</th><th>Engagement Sekarang</th><th>Status</th></tr></thead><tbody>
                        @forelse($items as $item)
                            @php $status = $item['status'] ?? 'steady'; $reachDelta = (float) data_get($item, 'progress.reach.delta', 0); @endphp
                            <tr><td>{{ $loop->iteration }}</td><td class="title" title="{{ $item['title'] ?? '' }}">{{ \Illuminate\Support\Str::limit($item['title'] ?? 'Konten tanpa judul', 65) }}@if(filled($item['campaign'] ?? null) || filled($item['adset'] ?? null))<small class="trend-item-context">{{ collect([$item['campaign'] ?? null, $item['adset'] ?? null])->filter()->join(' · ') }}</small>@endif</td><td>{{ number_format(data_get($item, 'progress.reach.previous', 0), 0, ',', '.') }}</td><td>{{ number_format(data_get($item, 'progress.reach.current', 0), 0, ',', '.') }}</td><td class="trend-delta {{ $reachDelta > 0 ? 'is-up' : ($reachDelta < 0 ? 'is-down' : '') }}">{{ $reachDelta > 0 ? '+' : '' }}{{ number_format($reachDelta, 0, ',', '.') }}</td><td>{{ number_format(data_get($item, 'progress.engagement.previous', 0), 0, ',', '.') }}</td><td>{{ number_format(data_get($item, 'progress.engagement.current', 0), 0, ',', '.') }}</td><td><span class="trend-status trend-status--{{ $status }}">{{ ['up'=>'Meningkat','down'=>'Menurun','new'=>'Baru','steady'=>'Tetap'][$status] ?? 'Tetap' }}</span></td></tr>
                        @empty
                            <tr><td colspan="8" class="meta-empty">Snapshot sebelumnya belum memiliki rincian per postingan. Data akan tersedia pada perbandingan berikutnya.</td></tr>
                        @endforelse
                    </tbody></table></div></article>
                @endif
            </div>
        @endforeach
    </section>
@endforeach

<script>
document.addEventListener('DOMContentLoaded',function(){
    document.querySelectorAll('[data-trend-source-tab]').forEach(button=>button.addEventListener('click',function(){
        const [period]=button.dataset.trendSourceTab.split(':');
        document.querySelectorAll(`[data-trend-source-tab^="${period}:"]`).forEach(tab=>{const active=tab===button;tab.classList.toggle('is-active',active);tab.setAttribute('aria-selected',active?'true':'false')});
        document.querySelectorAll(`[data-trend-source-panel^="${period}:"]`).forEach(panel=>panel.hidden=panel.dataset.trendSourcePanel!==button.dataset.trendSourceTab);
        requestAnimationFrame(()=>window.dispatchEvent(new Event('resize')));
    }));
    document.querySelectorAll('[data-trend-item-chart]').forEach(canvas=>{
        if(typeof Chart==='undefined')return;
        let items=[];try{items=JSON.parse(canvas.dataset.trendItems||'[]')}catch(e){return}
        new Chart(canvas.getContext('2d'),{type:'line',data:{labels:items.map(item=>item.title),datasets:[
            {label:'Reach sebelumnya',data:items.map(item=>item.reach_previous),borderColor:'#a1a1aa',backgroundColor:'transparent',borderDash:[5,4],borderWidth:2,pointRadius:4,pointBackgroundColor:'#fff',pointBorderColor:'#a1a1aa',pointBorderWidth:2,tension:.3},
            {label:'Reach sekarang',data:items.map(item=>item.reach_current),borderColor:'#18181b',backgroundColor:'rgba(24,24,27,.06)',fill:true,borderWidth:2.5,pointRadius:5,pointBackgroundColor:'#fff',pointBorderColor:'#18181b',pointBorderWidth:2,tension:.3},
            {label:'Engagement sebelumnya',data:items.map(item=>item.engagement_previous),borderColor:'#99f6e4',backgroundColor:'transparent',borderDash:[5,4],borderWidth:2,pointRadius:4,pointBackgroundColor:'#fff',pointBorderColor:'#0f766e',pointBorderWidth:2,tension:.3},
            {label:'Engagement sekarang',data:items.map(item=>item.engagement_current),borderColor:'#0f766e',backgroundColor:'transparent',borderWidth:2.5,pointRadius:5,pointBackgroundColor:'#fff',pointBorderColor:'#0f766e',pointBorderWidth:2,tension:.3}
        ]},options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},plugins:{legend:{position:'bottom'}},scales:{x:{grid:{display:false},ticks:{minRotation:0,maxRotation:0,autoSkip:true,maxTicksLimit:10}},y:{beginAtZero:true,grid:{color:'#f1f5f9'}}}}});
    });
});
</script>
