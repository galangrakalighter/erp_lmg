<style>
    body.lmg-analysis-page { background: #f5f5f3 !important; }
    body.lmg-analysis-page .lmg-content { padding: 20px !important; }
    body.lmg-analysis-page .lmg-content > .row { margin-right: -8px; margin-left: -8px; }
    body.lmg-analysis-page .lmg-content > .row.g-4 { gap: 0 !important; }
    body.lmg-analysis-page .lmg-content > .row > [class*="col-"] { padding-right: 8px; padding-left: 8px; }

    body.lmg-analysis-page .card {
        overflow: hidden; margin-bottom: 16px; border: 1px solid #e4e4e7 !important;
        border-radius: 16px !important; background: #fff !important;
        box-shadow: 0 5px 18px rgba(24,24,27,.045) !important;
    }
    body.lmg-analysis-page .card-header {
        min-height: 62px; padding: 15px 18px !important; border-bottom: 1px solid #e4e4e7 !important;
        background: #fff !important;
    }
    body.lmg-analysis-page .card-header h5 {
        color: #18181b !important; font-size: 14px !important; font-weight: 750 !important;
    }
    body.lmg-analysis-page .card-header p { margin-top: 3px; color: #a1a1aa !important; }
    body.lmg-analysis-page .card-body { padding: 18px !important; }

    body.lmg-analysis-page .lmg-content > .row:first-of-type .card {
        border: 0 !important; background: #161616 !important; color: #fff !important;
        box-shadow: 0 12px 28px rgba(22,22,22,.14) !important;
    }
    body.lmg-analysis-page .lmg-content > .row:first-of-type h5 { color: #fff !important; font-size: 16px; }
    body.lmg-analysis-page .lmg-content > .row:first-of-type .text-primary { color: #d4d4d8 !important; }
    body.lmg-analysis-page .date-range-pill {
        min-height: 42px; border: 1px solid rgba(255,255,255,.18) !important;
        border-radius: 11px !important; background: rgba(255,255,255,.09) !important;
        box-shadow: none !important;
    }
    body.lmg-analysis-page .date-input-independent { color: #fff !important; }
    body.lmg-analysis-page .date-input-independent::placeholder { color: #a1a1aa; }
    body.lmg-analysis-page .date-separator { color: #71717a; }
    body.lmg-analysis-page #applyFilter { border-color: #fff !important; background: #fff !important; color: #161616 !important; }
    body.lmg-analysis-page #resetAll { border-color: #52525b !important; background: transparent !important; color: #fff !important; }

    body.lmg-analysis-page .stat-card {
        position: relative; min-height: 118px; padding: 20px !important; border-left: 0 !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    body.lmg-analysis-page .stat-card::after {
        content: ''; position: absolute; top: 18px; right: 18px; width: 10px; height: 10px;
        border: 2px solid #d4d4d8; border-radius: 999px; background: #fff;
    }
    body.lmg-analysis-page .stat-card:hover { transform: translateY(-2px) !important; box-shadow: 0 10px 24px rgba(24,24,27,.08) !important; }
    body.lmg-analysis-page .stat-card p {
        margin-bottom: 9px !important; color: #71717a !important; font-size: 10px !important;
        font-weight: 750 !important; letter-spacing: .1em;
    }
    body.lmg-analysis-page .stat-card h4 {
        color: #18181b !important; font-size: 25px !important; font-weight: 750 !important; letter-spacing: -.035em;
    }

    body.lmg-analysis-page .table-responsive { border-color: #e4e4e7; border-radius: 10px; }
    body.lmg-analysis-page table.table { min-width: 760px; }
    body.lmg-analysis-page table.table thead th {
        padding: 12px 14px !important; border-bottom: 1px solid #e4e4e7 !important;
        background: #fafafa !important; color: #71717a !important; font-size: 10px !important;
        font-weight: 750 !important; letter-spacing: .08em; text-transform: uppercase;
    }
    body.lmg-analysis-page table.table tbody td,
    body.lmg-analysis-page table.table tbody th {
        padding: 13px 14px !important; border-bottom: 1px solid #f0f0f1 !important;
        background: #fff !important; color: #3f3f46 !important; font-size: 12px;
    }
    body.lmg-analysis-page table.table tbody tr:hover td,
    body.lmg-analysis-page table.table tbody tr:hover th { background: #fafafa !important; }
    body.lmg-analysis-page table.table a { color: #18181b !important; font-weight: 650; text-decoration: underline; text-underline-offset: 3px; }
    body.lmg-analysis-page .client-table-tools { padding: 12px 14px; background: #fff; }
    body.lmg-analysis-page .client-table-footer { background: #fafafa; }

    body.lmg-analysis-page .chart-wrapper {
        height: 360px !important; min-height: 360px; padding: 4px; border-radius: 12px; background: #fff;
    }
    body.lmg-analysis-page .progress { overflow: hidden; height: 6px !important; border-radius: 999px; background: #e4e4e7 !important; }
    body.lmg-analysis-page .progress-bar { border-radius: 999px; background: #52525b !important; }
    body.lmg-analysis-page .btn-pill { min-height: 40px; border-radius: 10px !important; padding: 8px 15px; }

    @media (max-width: 991px) {
        body.lmg-analysis-page .col-lg-9,
        body.lmg-analysis-page .col-lg-6,
        body.lmg-analysis-page .col-lg-3 { flex: 0 0 100%; max-width: 100%; }
    }
    @media (max-width: 767px) {
        body.lmg-analysis-page .lmg-content { padding: 12px !important; }
        body.lmg-analysis-page .lmg-content > .row:first-of-type .d-flex { align-items: stretch !important; flex-direction: column !important; }
        body.lmg-analysis-page .date-range-pill { width: 100%; justify-content: space-between; }
        body.lmg-analysis-page .date-input-independent { width: 40%; }
        body.lmg-analysis-page #applyFilter,
        body.lmg-analysis-page #resetAll { width: 100%; }
        body.lmg-analysis-page .chart-wrapper { height: 280px !important; min-height: 280px; }
    }
</style>
