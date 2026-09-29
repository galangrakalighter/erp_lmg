<style>
    body.lmg-analysis-page { background:#f7f8fa !important; }
    body.lmg-analysis-page .lmg-content { max-width:1920px; padding:22px 26px !important; }

    body.lmg-analysis-page .meta-analysis-heading h5 {
        margin:0; color:#18181b !important; font-size:24px !important; font-weight:750; letter-spacing:-.025em;
    }
    body.lmg-analysis-page .meta-analysis-heading p { margin:4px 0 0; color:#71717a; font-size:12px; }
    body.lmg-analysis-page .lmg-content > .row:first-of-type .card {
        overflow:visible; margin-bottom:18px; border:0 !important; background:transparent !important;
        color:#18181b !important; box-shadow:none !important;
    }
    body.lmg-analysis-page .lmg-content > .row:first-of-type h5 { color:#18181b !important; }
    body.lmg-analysis-page .date-range-pill {
        min-height:42px; border:1px solid #e4e4e7 !important; background:#fff !important; color:#3f3f46;
        box-shadow:0 1px 2px rgba(24,24,27,.04) !important;
    }
    body.lmg-analysis-page .date-input-independent { color:#3f3f46 !important; }
    body.lmg-analysis-page .date-input-independent::placeholder { color:#a1a1aa; }
    body.lmg-analysis-page #applyFilter { border:1px solid #18181b !important; background:#18181b !important; color:#fff !important; }
    body.lmg-analysis-page #resetAll { border:1px solid #d4d4d8 !important; background:#fff !important; color:#3f3f46 !important; }

    body.lmg-analysis-page [aria-label="Periode penyimpanan analisis"] {
        margin-bottom:10px !important; border:0; border-bottom:1px solid #e4e4e7; border-radius:0;
        background:transparent; padding:0; box-shadow:none;
    }
    body.lmg-analysis-page [aria-label="Periode penyimpanan analisis"] > div { display:flex; min-width:0; gap:28px; }
    body.lmg-analysis-page [data-analysis-period-tab] {
        margin-bottom:-1px; border-radius:0; background:transparent !important; padding:11px 3px;
        color:#71717a !important; box-shadow:none;
    }
    body.lmg-analysis-page [data-analysis-period-tab][aria-selected="true"] {
        border-bottom:2px solid #18181b; color:#18181b !important;
    }
    body.lmg-analysis-page [aria-label="Sumber konten analisis"] {
        border:1px solid #e4e4e7; border-radius:12px; background:#fff; padding:4px; box-shadow:0 1px 3px rgba(24,24,27,.04);
    }
    body.lmg-analysis-page [aria-label="Sumber konten analisis"] > div { width:100%; }
    body.lmg-analysis-page [data-analysis-source-tab] { width:50%; }

    body.lmg-analysis-page [data-analysis-panel="descriptive"] > div:first-child,
    body.lmg-analysis-page [data-paid-analysis-panel="descriptive"] > div:first-child {
        border:1px solid #e4e4e7 !important; background:#fff !important; color:#18181b !important;
        box-shadow:0 1px 3px rgba(24,24,27,.04) !important;
    }
    body.lmg-analysis-page [data-analysis-panel="descriptive"] > div:first-child p,
    body.lmg-analysis-page [data-paid-analysis-panel="descriptive"] > div:first-child p { color:#71717a !important; }
    body.lmg-analysis-page [data-analysis-panel="descriptive"] > div:first-child strong,
    body.lmg-analysis-page [data-paid-analysis-panel="descriptive"] > div:first-child strong { color:#18181b !important; }
    body.lmg-analysis-page [data-analysis-panel="descriptive"] > div:first-child p:first-child,
    body.lmg-analysis-page [data-paid-analysis-panel="descriptive"] > div:first-child p:first-child { color:#3f6212 !important; }

    body.lmg-analysis-page [data-analysis-source-panel] article,
    body.lmg-analysis-page [data-analysis-period-panel] article,
    body.lmg-analysis-page [data-paid-analysis-panel] > div {
        border-color:#e4e4e7 !important; box-shadow:0 1px 3px rgba(24,24,27,.045) !important;
    }
    body.lmg-analysis-page [data-analysis-source-panel] table { font-size:11px; }
    body.lmg-analysis-page [data-analysis-source-panel] thead { background:#fafafa; }

    @media(max-width:767px) {
        body.lmg-analysis-page .lmg-content { padding:14px !important; }
        body.lmg-analysis-page .meta-analysis-heading h5 { font-size:20px !important; }
        body.lmg-analysis-page [aria-label="Periode penyimpanan analisis"] > div { width:100%; justify-content:space-between; gap:8px; }
    }
</style>
