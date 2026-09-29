<div class="mb-4 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm" role="tablist" aria-label="Sumber konten analisis">
    <div class="grid grid-cols-2 gap-1 sm:inline-grid sm:min-w-[300px]">
        <button type="button" data-analysis-source-tab="organic" aria-selected="true" class="rounded-lg bg-zinc-950 px-5 py-2.5 text-xs font-bold text-white transition">Organik</button>
        <button type="button" data-analysis-source-tab="paid" aria-selected="false" class="rounded-lg px-5 py-2.5 text-xs font-bold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950">Paid</button>
    </div>
</div>

<style>
    [data-analysis-source-panel][hidden] { display: none !important; }
    #wrapper:has([data-analysis-source-panel]),
    #content-wrapper:has([data-analysis-source-panel]) { min-height: 0 !important; }
    #content:has([data-analysis-source-panel]) {
        flex: 0 0 auto !important;
        min-height: 0 !important;
        height: auto !important;
    }
    main.lmg-content:has([data-analysis-source-panel]) {
        flex: none !important;
        min-height: 0 !important;
        height: auto !important;
    }
</style>

<script>
document.body.classList.add('lmg-analysis-page');
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
        const heading = document.querySelector('[data-meta-analysis-title]');
        if (heading) {
            const platform = heading.textContent.includes('Facebook') ? 'Facebook' : 'Instagram';
            heading.textContent = `Analisis ${active === 'paid' ? 'Paid' : 'Organik'} ${platform}`;
        }
        const organicFieldPicker = document.querySelector('[data-organic-field-picker]');
        if (organicFieldPicker) organicFieldPicker.hidden = active !== 'organic';
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
