<div class="mb-4 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm" role="tablist" aria-label="Sumber konten analisis">
    <div class="grid grid-cols-2 gap-1 sm:inline-grid sm:min-w-[300px]">
        <button type="button" data-analysis-source-tab="organic" aria-selected="true" class="rounded-lg bg-zinc-950 px-5 py-2.5 text-xs font-bold text-white transition">Organik</button>
        <button type="button" data-analysis-source-tab="paid" aria-selected="false" class="rounded-lg px-5 py-2.5 text-xs font-bold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950">Paid</button>
    </div>
</div>

<style>
    [data-analysis-source-panel][hidden] { display: none !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function activateAnalysisSource(active) {
        document.querySelectorAll('[data-analysis-source-panel]').forEach(function (panel) {
            const selected = panel.dataset.analysisSourcePanel === active;
            panel.hidden = !selected;
            panel.style.display = selected ? '' : 'none';
            panel.classList.toggle('hidden', !selected);
        });
        document.querySelectorAll('[data-analysis-source-tab]').forEach(function (tab) {
            const selected = tab.dataset.analysisSourceTab === active;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.classList.toggle('bg-zinc-950', selected);
            tab.classList.toggle('text-white', selected);
            tab.classList.toggle('text-zinc-500', !selected);
        });
    }

    activateAnalysisSource('organic');

    document.querySelectorAll('[data-analysis-source-tab]').forEach(function (button) {
        button.addEventListener('click', function () {
            const active = button.dataset.analysisSourceTab;
            activateAnalysisSource(active);
            requestAnimationFrame(function () {
                window.dispatchEvent(new CustomEvent('analysis:source-changed', { detail: { active } }));
                window.dispatchEvent(new Event('resize'));
            });
        });
    });
});
</script>
