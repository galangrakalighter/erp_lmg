@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    const initializeChoices = root => {
        if (typeof Choices === 'undefined') return;
        root.querySelectorAll('.select-choices-creator, .select-choices-edit, .select-choices-blacklist-edit').forEach(element => {
            if (element.dataset.choice !== 'active') {
                new Choices(element, { searchEnabled: true, itemSelectText: '', shouldSort: false, searchPlaceholderValue: 'Cari affiliator...' });
            }
        });
    };
    initializeChoices(document);

    document.querySelectorAll('[data-native-table]').forEach((table) => {
        const pagination = document.getElementById(`${table.id}Pagination`);
        const apiUrl = table.dataset.api;
        let currentPage = 1, lastPage = 1, requestController = null, filterTimer = null;
        const filterIds = table.dataset.nativeTable === 'creator'
            ? ['filterKategori', 'filterLokasi', 'filterPlatform', 'filterFollowers', 'filterPlatformGMV', 'filterGMV', 'filterGender']
            : table.dataset.nativeTable === 'blacklist' ? ['filterBulan', 'filterJob', 'filterKet'] : [];

        const query = page => {
            const params = new URLSearchParams({ page });
            filterIds.forEach(id => {
                const element = document.getElementById(id);
                if (element?.value) params.set(id, element.value);
            });
            return params;
        };

        function showMessage(message, color = 'text-zinc-500', spinner = false) {
            const columns = table.tHead?.rows[0]?.cells.length || 1;
            table.tBodies[0].innerHTML = `<tr><td colspan="${columns}" class="px-5 py-12 text-center text-sm ${color}"><span class="inline-flex items-center gap-2">${spinner ? '<span class="h-4 w-4 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-900"></span>' : ''}${message}</span></td></tr>`;
        }

        function replaceModals(parsed) {
            document.getElementById(`${table.id}ApiModals`)?.remove();
            const host = document.createElement('div');
            host.id = `${table.id}ApiModals`;
            const type = table.dataset.nativeTable;
            if (type === 'blacklist') {
                parsed.querySelectorAll('.modal[id^="modalEdit"], .modal[id^="modalHapus"]').forEach(item => host.append(item.cloneNode(true)));
            } else {
                const prefixes = type === 'creator' ? ['editModal', 'deleteModal'] : ['modalEditToggle', 'modalHapusToggle'];
                prefixes.forEach(prefix => parsed.querySelectorAll(`input[id^="${prefix}"]`).forEach(input => {
                    host.append(input.cloneNode(true));
                    if (input.nextElementSibling) host.append(input.nextElementSibling.cloneNode(true));
                }));
            }
            document.body.append(host);
            if (typeof lucide !== 'undefined') lucide.createIcons();
            initializeChoices(host);
        }

        function renderPagination(meta) {
            if (!pagination) return;
            pagination.innerHTML = `<span>Menampilkan ${meta.from || 0}–${meta.to || 0} dari ${meta.total || 0} data</span><div class="native-table-pagination__actions"><button type="button" data-page="prev" ${currentPage <= 1 ? 'disabled' : ''}>‹</button><button type="button" class="is-active">${currentPage}</button><button type="button" data-page="next" ${currentPage >= lastPage ? 'disabled' : ''}>›</button></div>`;
        }

        async function load(page = 1) {
            if (!apiUrl) return;
            requestController?.abort();
            requestController = new AbortController();
            showMessage('Memuat data...', 'text-zinc-500', true);
            try {
                const response = await fetch(`${apiUrl}?${query(page)}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, signal: requestController.signal });
                if (!response.ok) throw new Error('Request gagal');
                const result = await response.json();
                const parsed = new DOMParser().parseFromString(result.html, 'text/html');
                const incoming = parsed.getElementById(table.id);
                table.tBodies[0].innerHTML = incoming?.tBodies[0]?.innerHTML || '';
                currentPage = result.current_page || 1;
                lastPage = result.last_page || 1;
                replaceModals(parsed);
                renderPagination(result);
            } catch (error) {
                if (error.name !== 'AbortError') showMessage('Data gagal dimuat. Silakan muat ulang halaman.', 'text-red-600');
            }
        }

        pagination?.addEventListener('click', event => {
            const direction = event.target.closest('button')?.dataset.page;
            if (direction === 'prev' && currentPage > 1) load(currentPage - 1);
            if (direction === 'next' && currentPage < lastPage) load(currentPage + 1);
        });
        const reload = () => { clearTimeout(filterTimer); filterTimer = setTimeout(() => load(1), 250); };
        filterIds.forEach(id => {
            document.getElementById(id)?.addEventListener('input', reload);
            document.getElementById(id)?.addEventListener('change', reload);
        });
        if (table.dataset.nativeTable === 'creator') {
            window.filterTable = reload;
            window.resetFilter = () => { document.getElementById('filterForm')?.reset(); load(1); };
        }
        if (table.dataset.nativeTable === 'blacklist') {
            window.resetBlacklistFilter = () => { document.getElementById('blacklistFilterForm')?.reset(); load(1); };
        }
        load(1);
    });
});
</script>
@endonce
