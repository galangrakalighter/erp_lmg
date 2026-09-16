<style>
    body.lmg-embedded .lmg-content { padding: 18px 20px 28px !important; }
    .client-table-tools {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 12px 14px; border: 1px solid #e4e4e7; border-bottom: 0;
        border-radius: 12px 12px 0 0; background: #fafafa;
    }
    .client-table-search {
        width: min(280px, 100%); border: 1px solid #d4d4d8; border-radius: 10px;
        background: #fff; padding: 8px 11px; color: #18181b; font-size: 12px; outline: none;
    }
    .client-table-search:focus { border-color: #18181b; box-shadow: 0 0 0 3px rgba(24,24,27,.07); }
    .client-table-footer {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 12px 14px; border: 1px solid #e4e4e7; border-top: 0;
        border-radius: 0 0 12px 12px; background: #fff; color: #71717a; font-size: 12px;
    }
    .client-table-pages { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; }
    .client-table-page {
        display: inline-flex; min-width: 32px; height: 32px; align-items: center; justify-content: center;
        border: 1px solid #e4e4e7; border-radius: 8px; background: #fff; padding: 0 9px;
        color: #52525b; font-size: 12px; font-weight: 700; cursor: pointer;
    }
    .client-table-page:hover:not(:disabled) { background: #f4f4f5; }
    .client-table-page.active { border-color: #161616; background: #161616; color: #fff; }
    .client-table-page:disabled { cursor: not-allowed; opacity: .38; }
    .client-native-table-wrap > .table-responsive { border-radius: 0 !important; }
    body.lmg-client-tabs .card-body { padding: 16px !important; }
    body.lmg-client-tabs .client-native-table-wrap {
        margin: 4px 0 16px !important;
        overflow: hidden;
        border-radius: 14px;
        box-shadow: 0 1px 2px rgba(22, 22, 22, .04);
    }
    body.lmg-client-tabs .client-native-table-wrap .client-table-tools {
        border-radius: 14px 14px 0 0;
    }
    body.lmg-client-tabs .client-native-table-wrap .client-table-footer {
        border-radius: 0 0 14px 14px;
    }
    @media (max-width: 640px) {
        body.lmg-embedded .lmg-content { padding: 12px !important; }
        .client-table-tools, .client-table-footer { align-items: stretch; flex-direction: column; }
        .client-table-search { width: 100%; }
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.lmg-content .table-responsive > table.table').forEach(function (table) {
        if (table.closest('.modal') || table.id === 'tableManajemenKonten' || table.id === 'produkTable' || table.dataset.nativePaginated) return;
        table.dataset.nativePaginated = '1';

        const responsive = table.parentElement;
        const shell = document.createElement('div');
        shell.className = 'client-native-table-wrap mb-3';
        responsive.parentNode.insertBefore(shell, responsive);
        shell.appendChild(responsive);

        const tools = document.createElement('div');
        tools.className = 'client-table-tools';
        tools.innerHTML = '<strong style="font-size:12px;color:#27272a">Daftar Data</strong><input class="client-table-search" type="search" placeholder="Cari data..." aria-label="Cari data">';
        shell.insertBefore(tools, responsive);

        const footer = document.createElement('div');
        footer.className = 'client-table-footer';
        footer.innerHTML = '<span data-table-info></span><div class="client-table-pages" data-table-pages></div>';
        shell.appendChild(footer);

        const rows = Array.from(table.tBodies[0]?.rows || []);
        const input = tools.querySelector('input');
        const info = footer.querySelector('[data-table-info]');
        const pages = footer.querySelector('[data-table-pages]');
        const perPage = 10;
        let page = 1;

        function render() {
            const query = input.value.trim().toLocaleLowerCase('id');
            const filtered = rows.filter(row => row.textContent.toLocaleLowerCase('id').includes(query));
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            page = Math.min(page, totalPages);
            const start = (page - 1) * perPage;
            const visible = new Set(filtered.slice(start, start + perPage));
            rows.forEach(row => row.style.display = visible.has(row) ? '' : 'none');

            info.textContent = filtered.length
                ? `Menampilkan ${start + 1}–${Math.min(start + perPage, filtered.length)} dari ${filtered.length} data`
                : 'Tidak ada data';

            const buttons = [];
            buttons.push(`<button type="button" class="client-table-page" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}>‹</button>`);
            for (let number = 1; number <= totalPages; number++) {
                if (totalPages > 7 && number > 2 && number < totalPages - 1 && Math.abs(number - page) > 1) {
                    if (buttons[buttons.length - 1] !== '<span>…</span>') buttons.push('<span>…</span>');
                    continue;
                }
                buttons.push(`<button type="button" class="client-table-page ${number === page ? 'active' : ''}" data-page="${number}">${number}</button>`);
            }
            buttons.push(`<button type="button" class="client-table-page" data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''}>›</button>`);
            pages.innerHTML = buttons.join('');
        }

        input.addEventListener('input', function () { page = 1; render(); });
        pages.addEventListener('click', function (event) {
            const button = event.target.closest('[data-page]');
            if (!button || button.disabled) return;
            page = Number(button.dataset.page);
            render();
        });
        render();
    });
});
</script>
