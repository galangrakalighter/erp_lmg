<style>
    .division-cms-page { padding: 4px; }
    .division-cms-page > .card {
        overflow: hidden;
        border: 1px solid #e4e4e7 !important;
        border-radius: 24px !important;
        background: #fff;
        box-shadow: 0 16px 45px rgba(0,0,0,.06) !important;
    }
    .division-cms-page > .card > .card-header {
        position: relative;
        display: flex !important;
        min-height: 104px;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 20px;
        padding: 24px 28px !important;
        border: 0 !important;
        background: #161616 !important;
        color: #fff;
    }
    .division-cms-page > .card > .card-header::before { display: none; }
    .division-cms-page > .card > .card-header h6::before {
        content: 'LIGHTER MEDIA GROUP · CMS MANAGEMENT';
        display: block;
        margin-bottom: 7px;
        color: #b8ff5c;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
    }
    .division-cms-page > .card > .card-header h6 {
        min-width: 0;
        flex: 1 1 auto;
        color: #fff !important;
        font-size: 22px;
        font-weight: 700 !important;
        letter-spacing: -.025em;
    }
    .division-cms-page > .card > .card-header h6::after {
        content: 'Kelola data CMS dan konten operasional dalam satu tempat.';
        display: block;
        margin-top: 8px;
        color: #a1a1aa;
        font-size: 12px;
        font-weight: 400;
        letter-spacing: 0;
    }
    .division-cms-page > .card > .card-header .btn-primary {
        flex: 0 0 auto;
        min-height: 42px;
        border: 1px solid rgba(255,255,255,.18) !important;
        border-radius: 12px !important;
        background: transparent !important;
        padding: 9px 16px !important;
        color: #fff !important;
        font-size: 13px;
        font-weight: 700;
        box-shadow: none !important;
    }
    .division-cms-page > .card > .card-header .btn-primary:hover { background: #27272a !important; }
    .division-cms-page > .card > .card-body { padding: 20px !important; }
    .division-cms-page .cms-content-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
        padding: 2px 2px 0;
    }
    .division-cms-page .cms-content-actions h2 {
        margin: 0;
        color: #18181b;
        font-size: 15px;
        font-weight: 750;
        letter-spacing: -.01em;
    }
    .division-cms-page .cms-content-actions .btn {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid #161616 !important;
        border-radius: 10px !important;
        background: #161616 !important;
        padding: 8px 14px !important;
        color: #fff !important;
        font-size: 12px;
        font-weight: 700;
        box-shadow: 0 5px 14px rgba(22,22,22,.12) !important;
    }
    .division-cms-page .cms-content-actions .btn:hover { background: #3f3f46 !important; }
    .division-cms-page .dataTables_wrapper { color: #3f3f46; }
    .division-cms-page #produkTable_wrapper > .row:first-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 !important;
        padding: 12px 14px;
        border: 1px solid #e4e4e7;
        border-bottom: 0;
        border-radius: 14px 14px 0 0;
        background: #fafafa;
    }
    .division-cms-page #produkTable_wrapper > .row:first-child::before {
        display: none;
        content: '';
    }
    .division-cms-page #produkTable_wrapper > .row:first-child > div:first-child { display: none !important; }
    .division-cms-page #produkTable_wrapper > .row:first-child > div:last-child {
        margin-left: auto;
        flex: 0 1 280px;
        width: min(280px, 100%);
        max-width: 280px;
        padding: 0 !important;
    }
    .division-cms-page #produkTable_wrapper > .row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 !important;
        padding: 12px 14px;
        border: 1px solid #e4e4e7;
        border-top: 0;
        border-radius: 0 0 14px 14px;
        background: #fff;
    }
    .division-cms-page #produkTable_wrapper > .row:last-child > div { padding: 0 !important; }
    .division-cms-page .dataTables_length label,
    .division-cms-page .dataTables_filter label { color: #71717a; font-size: 12px; font-weight: 700; }
    .division-cms-page .dataTables_length select,
    .division-cms-page .dataTables_filter input {
        height: 40px !important;
        border: 1px solid #d4d4d8 !important;
        border-radius: 11px !important;
        background: #fafafa !important;
        color: #18181b !important;
        box-shadow: none !important;
    }
    .division-cms-page .dataTables_filter { width: 100%; margin: 0 !important; text-align: right !important; }
    .division-cms-page .client-table-tools > strong { display: none; }
    .division-cms-page .client-table-tools { justify-content: flex-end; }
    .division-cms-page .dataTables_filter label { width: 100%; margin: 0 !important; font-size: 0; }
    .division-cms-page .dataTables_filter input {
        width: 100%;
        min-width: 0;
        margin: 0 !important;
        padding: 0 13px;
        font-size: 12px;
    }
    .division-cms-page .dataTables_filter input::placeholder { color: #a1a1aa; }
    .division-cms-page .table-responsive { overflow: auto; border: 1px solid #e4e4e7; border-radius: 14px; }
    .division-cms-page .table-responsive:has(#produkTable_wrapper) {
        border: 0;
        border-radius: 14px;
    }
    .division-cms-page #produkTable_wrapper .table-responsive,
    .division-cms-page #produkTable_wrapper > table { border-radius: 0 !important; }
    .division-cms-page #produkTable_wrapper #produkTable {
        border-right: 1px solid #e4e4e7 !important;
        border-left: 1px solid #e4e4e7 !important;
    }
    .division-cms-page table.dataTable,
    .division-cms-page #produkTable,
    .division-cms-page #dataTable { width: 100% !important; margin: 0 !important; border: 0 !important; border-collapse: separate !important; border-spacing: 0; }
    .division-cms-page #produkTable { min-width: 920px; }
    .division-cms-page #dataTable { min-width: 680px; }
    .division-cms-page #produkTable thead th,
    .division-cms-page #dataTable thead th {
        border: 0 !important;
        border-bottom: 1px solid #27272a !important;
        background: #161616 !important;
        padding: 15px 14px !important;
        color: #fff !important;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-align: left !important;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .division-cms-page #produkTable thead th:first-child,
    .division-cms-page #dataTable thead th:first-child { padding-left: 20px !important; }
    .division-cms-page #produkTable tbody td,
    .division-cms-page #produkTable tbody th,
    .division-cms-page #dataTable tbody td,
    .division-cms-page #dataTable tbody th {
        border: 0 !important;
        border-bottom: 1px solid #f0f0f0 !important;
        background: #fff !important;
        padding: 16px 14px !important;
        color: #3f3f46;
        font-size: 13px;
        vertical-align: middle;
    }
    .division-cms-page #produkTable tbody tr:last-child > *,
    .division-cms-page #dataTable tbody tr:last-child > * { border-bottom: 0 !important; }
    .division-cms-page #produkTable tbody tr:hover > *,
    .division-cms-page #dataTable tbody tr:hover > * { background: #fafafa !important; }
    .division-cms-page #produkTable .dataTables_empty,
    .division-cms-page #dataTable .dataTables_empty {
        padding: 48px 20px !important;
        color: #71717a !important;
        text-align: center !important;
    }
    .division-cms-page .dataTables_info { color: #71717a !important; font-size: 12px; }
    .division-cms-page .badge {
        border: 1px solid #e4e4e7 !important;
        border-radius: 999px !important;
        background: #f4f4f5 !important;
        padding: 5px 8px !important;
        color: #3f3f46 !important;
        font-size: 10px;
        font-weight: 700;
    }
    .division-cms-page .badge-success { border-color: #bbf7d0 !important; background: #f0fdf4 !important; color: #166534 !important; }
    .division-cms-page .badge-secondary { color: #71717a !important; }
    .division-cms-page .btn-group { gap: 6px; }
    .division-cms-page .btn-group .btn,
    .division-cms-page #produkTable .btn,
    .division-cms-page #dataTable .btn {
        display: inline-flex;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e4e7 !important;
        border-radius: 10px !important;
        background: #fff !important;
        color: #3f3f46 !important;
        box-shadow: none !important;
    }
    .division-cms-page #produkTable .btn-danger,
    .division-cms-page #dataTable .btn-danger { border-color: #fecdd3 !important; color: #e11d48 !important; }
    .division-cms-page #dataTable .btn-warning { color: #161616 !important; }
    .division-cms-page #dataTable tbody th { font-weight: 500; }
    .division-cms-page #dataTable .btn {
        width: auto;
        min-width: 70px;
        padding: 0 12px !important;
        font-size: 12px;
        font-weight: 700;
    }
    .division-cms-page .pagination { display: flex; flex-wrap: wrap; gap: 5px; margin: 0 !important; }
    .division-cms-page .page-link {
        display: inline-flex;
        min-width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e4e7 !important;
        border-radius: 8px !important;
        background: #fff !important;
        padding: 0 9px !important;
        color: #52525b !important;
        font-size: 12px;
        font-weight: 700;
        box-shadow: none !important;
    }
    .division-cms-page .page-item.active .page-link { border-color: #161616 !important; background: #161616 !important; color: #fff !important; }
    .division-cms-page .modal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 100000 !important;
        width: 100% !important;
        height: 100% !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        background: rgba(9, 9, 11, .68) !important;
        padding: 92px 20px 24px !important;
        backdrop-filter: blur(5px);
    }
    .division-cms-page .modal:not(.show) { display: none !important; }
    .division-cms-page .modal.show {
        display: flex !important;
        align-items: flex-start;
        justify-content: center;
    }
    .division-cms-page .modal-dialog {
        width: 100% !important;
        max-width: 620px;
        margin: 0 auto !important;
        pointer-events: none;
    }
    .division-cms-page .modal-dialog.modal-lg { max-width: 900px; }
    .division-cms-page .modal-dialog.modal-md { max-width: 520px; }
    .division-cms-page .modal-dialog-centered { min-height: 0 !important; }
    .division-cms-page .modal-content {
        display: flex;
        width: 100%;
        max-height: calc(100dvh - 116px);
        flex-direction: column;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.12) !important;
        border-radius: 22px !important;
        background: #fff !important;
        color: #27272a;
        pointer-events: auto;
        box-shadow: 0 30px 90px rgba(0,0,0,.35) !important;
    }
    .division-cms-page .modal-content > form {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
    }
    .division-cms-page .modal #modalEditContent {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
    }
    .division-cms-page .modal-header {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 0 !important;
        border-bottom: 1px solid #27272a !important;
        background: #161616 !important;
        padding: 18px 22px !important;
        color: #fff !important;
    }
    .division-cms-page .modal-header .modal-title {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: #fff !important;
        font-size: 17px;
        font-weight: 700;
        letter-spacing: -.01em;
    }
    .division-cms-page .modal-header .modal-title i {
        display: inline-flex;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border: 1px solid #3f3f46;
        border-radius: 10px;
        background: #27272a;
        color: #fff !important;
        font-size: 13px;
    }
    .division-cms-page .modal-header .close {
        display: inline-flex;
        width: 36px;
        height: 36px;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border: 1px solid #3f3f46 !important;
        border-radius: 10px !important;
        background: #27272a !important;
        padding: 0 !important;
        color: #fff !important;
        font-size: 22px;
        line-height: 1;
        opacity: 1;
        text-shadow: none;
    }
    .division-cms-page .modal-body {
        min-height: 0;
        max-height: none;
        flex: 1 1 auto;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        background: #f8f8f7 !important;
        padding: 22px !important;
        scrollbar-color: #a1a1aa #f4f4f5;
        scrollbar-width: thin;
    }
    .division-cms-page .modal-body::-webkit-scrollbar { width: 9px; }
    .division-cms-page .modal-body::-webkit-scrollbar-track { background: #f4f4f5; }
    .division-cms-page .modal-body::-webkit-scrollbar-thumb { border: 2px solid #f4f4f5; border-radius: 999px; background: #a1a1aa; }
    .division-cms-page .modal-footer {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        border: 0 !important;
        border-top: 1px solid #e4e4e7 !important;
        background: #fff !important;
        padding: 15px 22px !important;
    }
    .division-cms-page .modal .row {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin: 0 !important;
    }
    .division-cms-page .modal .row > [class*="col-"] { width: 100%; max-width: none; padding: 0 !important; }
    .division-cms-page .modal .col-12,
    .division-cms-page .modal .col-md-12 { grid-column: 1 / -1; }
    .division-cms-page .modal .col-md-8 { grid-column: span 1; }
    .division-cms-page .modal .col-md-4 { grid-column: span 1; }
    .division-cms-page .modal .form-group {
        margin: 0 0 16px;
        border: 1px solid #e8e8eb;
        border-radius: 14px;
        background: #fff;
        padding: 14px;
    }
    .division-cms-page .modal label {
        display: block;
        margin: 0 0 8px !important;
        color: #52525b !important;
        font-size: 10px;
        font-weight: 800 !important;
        letter-spacing: .075em;
        text-transform: uppercase;
    }
    .division-cms-page .form-control,
    .division-cms-page .custom-select,
    .division-cms-page .select2-selection {
        min-height: 42px !important;
        border: 1px solid #d4d4d8 !important;
        border-radius: 11px !important;
        background: #fafafa !important;
        padding: 9px 12px !important;
        color: #27272a !important;
        box-shadow: none !important;
    }
    .division-cms-page textarea.form-control { min-height: 105px !important; resize: vertical; }
    .division-cms-page .form-control:focus,
    .division-cms-page .custom-select:focus,
    .division-cms-page .select2-selection:focus {
        border-color: #52525b !important;
        background: #fff !important;
        box-shadow: 0 0 0 3px rgba(22,22,22,.07) !important;
    }
    .division-cms-page .modal .section-title { margin: 4px 0 14px !important; }
    .division-cms-page .modal .section-title > div:first-child { display: none; }
    .division-cms-page .modal .section-title h6 {
        margin: 0;
        color: #18181b !important;
        font-size: 12px;
        font-weight: 800 !important;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .division-cms-page .modal .section-title:not(:first-child) { padding-top: 8px; }
    .division-cms-page .modal .alert {
        border: 1px solid #e4e4e7 !important;
        border-radius: 13px !important;
        background: #fff !important;
        padding: 13px 15px !important;
        color: #52525b !important;
    }
    .division-cms-page .modal .card {
        border: 1px solid #e4e4e7 !important;
        border-radius: 14px !important;
        background: #fff !important;
        box-shadow: none !important;
    }
    .division-cms-page .modal .card .card-body { padding: 14px !important; }
    .division-cms-page .modal .platform-row:last-child { margin-bottom: 0 !important; }
    .division-cms-page .modal .input-group { display: flex; width: 100%; align-items: stretch; }
    .division-cms-page .modal .input-group > .form-control { min-width: 0; flex: 1 1 auto; }
    .division-cms-page .modal .input-group-append { display: flex; margin-left: 8px; }
    .division-cms-page .modal .select2-container { width: 100% !important; }
    .division-cms-page .modal .select2-selection { padding: 5px 8px !important; }
    .division-cms-page #produkModal .modal-body > hr {
        margin: 4px 0 20px !important;
        border: 0;
        border-top: 1px solid #e4e4e7;
    }
    .division-cms-page #produkModal .select2-container .select2-selection--multiple {
        display: flex;
        min-height: 46px !important;
        align-items: center;
        padding: 5px 9px !important;
    }
    .select2-container--open { z-index: 100100 !important; }
    .division-cms-page .modal-footer .btn { min-height: 40px; border-radius: 11px !important; padding: 8px 16px; font-size: 13px; font-weight: 700; }
    .division-cms-page .modal-footer .btn-primary { border-color: #161616 !important; background: #161616 !important; color: #fff !important; }
    .division-cms-page .modal-footer .btn-secondary,
    .division-cms-page .modal-footer .btn-light { border: 1px solid #d4d4d8 !important; background: #fff !important; color: #3f3f46 !important; }
    .division-cms-page .modal-footer .btn-danger { border-color: #e11d48 !important; background: #e11d48 !important; color: #fff !important; }
    .division-cms-page .modal[id*="delete"] .modal-dialog,
    .division-cms-page .modal[id*="Delete"] .modal-dialog { max-width: 460px; }
    .division-cms-page .modal[id*="delete"] .modal-body,
    .division-cms-page .modal[id*="Delete"] .modal-body { padding: 28px 24px !important; }
    .division-cms-page .modal[id*="delete"] .modal-body::before,
    .division-cms-page .modal[id*="Delete"] .modal-body::before {
        content: '!';
        display: flex;
        width: 48px;
        height: 48px;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        border: 1px solid #fecdd3;
        border-radius: 50%;
        background: #fff1f2;
        color: #e11d48;
        font-size: 20px;
        font-weight: 800;
    }
    .division-cms-page .modal[id*="delete"] .modal-footer,
    .division-cms-page .modal[id*="Delete"] .modal-footer { justify-content: center; }

    @if(!request()->routeIs('cms.produk', 'client.produk_divisi'))
        .division-cms-page #tambahModal .modal-dialog,
        .division-cms-page [id^="editModal_"] .modal-dialog { max-width: 560px; }
    @endif
    @media (max-width: 767px) {
        .division-cms-page { padding: 0; }
        .division-cms-page > .card > .card-header {
            align-items: center !important;
            flex-direction: row !important;
            gap: 12px;
            padding: 18px !important;
        }
        .division-cms-page > .card > .card-header h6 { font-size: 18px; }
        .division-cms-page > .card > .card-header h6::before { font-size: 8px; letter-spacing: .12em; }
        .division-cms-page > .card > .card-header h6::after { max-width: 210px; font-size: 10px; }
        .division-cms-page > .card > .card-header .btn-primary {
            min-height: 38px;
            padding: 8px 10px !important;
            white-space: nowrap;
        }
        .division-cms-page > .card > .card-body { padding: 12px !important; }
        .division-cms-page .dataTables_filter input { min-width: 0; width: calc(100% - 48px); }
        .division-cms-page .dataTables_length,
        .division-cms-page .dataTables_filter { width: 100%; margin-bottom: 10px; text-align: left !important; }
        .division-cms-page #produkTable_wrapper > .row:first-child,
        .division-cms-page #produkTable_wrapper > .row:last-child { align-items: stretch; flex-direction: column; }
        .division-cms-page #produkTable_wrapper > .row:first-child > div:last-child { width: 100%; max-width: none; }
        .division-cms-page .cms-content-actions { align-items: stretch; flex-direction: column; }
        .division-cms-page .cms-content-actions .btn { width: 100%; }
        .division-cms-page .modal { padding: 72px 10px 12px !important; }
        .division-cms-page .modal-content { max-height: calc(100dvh - 84px); border-radius: 17px !important; }
        .division-cms-page .modal-header,
        .division-cms-page .modal-body,
        .division-cms-page .modal-footer { padding-right: 16px !important; padding-left: 16px !important; }
        .division-cms-page .modal .row { grid-template-columns: 1fr; gap: 0; }
        .division-cms-page .modal .col-md-12 { grid-column: auto; }
        .division-cms-page .modal-footer { align-items: stretch; flex-direction: column-reverse; }
        .division-cms-page .modal-footer .btn { width: 100%; }
    }
</style>

<script>
    window.addEventListener('load', function () {
        const clientSearch = document.querySelector('#produkTable_filter input[type="search"]');
        if (clientSearch) {
            clientSearch.placeholder = 'Cari client...';
            clientSearch.setAttribute('aria-label', 'Cari client');
        }
    });
</script>

@if(request()->routeIs(
    'cms.produk',
    'client.produk_divisi',
    'cms.hook',
    'cms.strategy',
    'cms.status',
    'cms.type',
    'cms.pilar',
    'cms.body',
    'cms.cta'
))
    @include('partials.client_native_tables')
@endif
