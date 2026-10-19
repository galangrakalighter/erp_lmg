<div class="mb-4 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm" role="tablist" aria-label="Periode penyimpanan analisis">
    <div class="grid grid-cols-3 gap-1 sm:inline-grid sm:min-w-[420px]">
        <button type="button" data-analysis-period-tab="realtime" aria-selected="true" class="rounded-lg bg-zinc-950 px-4 py-2.5 text-xs font-bold text-white">Realtime</button>
        <button type="button" data-analysis-period-tab="three_days" aria-selected="false" class="rounded-lg px-4 py-2.5 text-xs font-bold text-zinc-500">3 Hari</button>
        <button type="button" data-analysis-period-tab="seven_days" aria-selected="false" class="rounded-lg px-4 py-2.5 text-xs font-bold text-zinc-500">7 Hari</button>
    </div>
</div>

<style>[data-analysis-period-panel][hidden] { display: none !important; }</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function activateAnalysisPeriod(active) {
        document.querySelectorAll('[data-analysis-period-panel]').forEach(function (panel) {
            const selected = panel.dataset.analysisPeriodPanel === active;
            panel.hidden = !selected;
            panel.style.display = selected ? '' : 'none';
        });
        document.querySelectorAll('[data-analysis-period-tab]').forEach(function (tab) {
            const selected = tab.dataset.analysisPeriodTab === active;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.classList.toggle('bg-zinc-950', selected);
            tab.classList.toggle('text-white', selected);
            tab.classList.toggle('text-zinc-500', !selected);
        });
    }

    activateAnalysisPeriod('realtime');
    document.querySelectorAll('[data-analysis-period-tab]').forEach(function (button) {
        button.addEventListener('click', function () {
            activateAnalysisPeriod(button.dataset.analysisPeriodTab);
            window.dispatchEvent(new Event('resize'));
        });
    });
});
</script>
