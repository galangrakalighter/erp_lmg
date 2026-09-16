(function () {
    window.renderNativeClientTable = async function (config) {
        const table = document.getElementById('produkTable');
        if (!table) return;

        let rows = Array.isArray(config.data) ? config.data : [];
        if (config.ajax && config.ajax.url) {
            const response = await fetch(config.ajax.url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            if (!response.ok) {
                throw new Error(`Gagal mengambil data client (${response.status})`);
            }
            const json = await response.json();
            rows = config.ajax.dataSrc ? config.ajax.dataSrc(json) : (json.data || json);
        }

        const responsive = table.closest('.table-responsive');
        let shell = responsive.parentElement;
        if (!shell.classList.contains('client-native-table-wrap')) {
            shell = document.createElement('div');
            shell.className = 'client-native-table-wrap mb-3';
            responsive.parentNode.insertBefore(shell, responsive);
            shell.appendChild(responsive);
        }

        let tools = shell.querySelector('.client-table-tools');
        if (!tools) {
            tools = document.createElement('div');
            tools.className = 'client-table-tools';
            tools.innerHTML = '<input class="client-table-search" type="search" placeholder="Cari client..." aria-label="Cari client">';
            shell.insertBefore(tools, responsive);
        }

        let footer = shell.querySelector('.client-table-footer');
        if (!footer) {
            footer = document.createElement('div');
            footer.className = 'client-table-footer';
            footer.innerHTML = '<span data-table-info></span><div class="client-table-pages" data-table-pages></div>';
            shell.appendChild(footer);
        }

        const tbody = table.tBodies[0];
        const search = tools.querySelector('input');
        const info = footer.querySelector('[data-table-info]');
        const pages = footer.querySelector('[data-table-pages]');
        const perPage = 10;
        let page = 1;

        const escape = value => String(value ?? '').replace(/[&<>"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[character]));

        function draw() {
            const query = search.value.trim().toLocaleLowerCase('id');
            const filtered = rows.filter(row => Object.values(row).flat(Infinity).join(' ').toLocaleLowerCase('id').includes(query));
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            page = Math.min(page, totalPages);
            const start = (page - 1) * perPage;
            const visible = filtered.slice(start, start + perPage);

            if (!visible.length) {
                tbody.innerHTML = `<tr><td colspan="${config.columns.length}" class="dataTables_empty">Belum ada data client.</td></tr>`;
            } else {
                tbody.innerHTML = visible.map((row, rowIndex) => `<tr>${config.columns.map(column => {
                    const value = column.data === null ? row : row[column.data];
                    const content = column.render
                        ? column.render(value, 'display', row, { row: start + rowIndex })
                        : escape(value);
                    return `<td class="${column.className || ''}">${content ?? '-'}</td>`;
                }).join('')}</tr>`).join('');
            }

            info.textContent = filtered.length
                ? `Menampilkan ${start + 1}–${Math.min(start + perPage, filtered.length)} dari ${filtered.length} data`
                : 'Tidak ada data';

            const buttons = [`<button type="button" class="client-table-page" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}>‹</button>`];
            for (let number = 1; number <= totalPages; number++) {
                buttons.push(`<button type="button" class="client-table-page ${number === page ? 'active' : ''}" data-page="${number}">${number}</button>`);
            }
            buttons.push(`<button type="button" class="client-table-page" data-page="${page + 1}" ${page === totalPages ? 'disabled' : ''}>›</button>`);
            pages.innerHTML = buttons.join('');
        }

        search.oninput = function () { page = 1; draw(); };
        pages.onclick = function (event) {
            const button = event.target.closest('[data-page]');
            if (!button || button.disabled) return;
            page = Number(button.dataset.page);
            draw();
        };
        draw();
    };
})();
