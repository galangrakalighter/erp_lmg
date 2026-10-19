import './bootstrap';
import axios from 'axios';
import Sortable from 'sortablejs';

window.axios = axios;
window.Sortable = Sortable;

/**
 * Apply the Tailwind DataTable presentation globally while preserving
 * the original Division tables requested by the application.
 */
function enhanceDataTables() {
    const isDivisionPage = Boolean(document.getElementById('divisi_id'));

    document.querySelectorAll('.dataTables_wrapper').forEach((wrapper) => {
        const excluded = isDivisionPage || Boolean(wrapper.closest(
            '[data-division-datatable], [data-divisi-datatable], ' +
            '[data-division-dropdown], [data-divisi-dropdown], ' +
            '.division-datatable, .divisi-datatable'
        ));

        wrapper.classList.toggle('lmg-tailwind-datatable', !excluded);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    enhanceDataTables();

    const observer = new MutationObserver((mutations) => {
        if (mutations.some((mutation) => mutation.addedNodes.length > 0)) {
            enhanceDataTables();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
});
