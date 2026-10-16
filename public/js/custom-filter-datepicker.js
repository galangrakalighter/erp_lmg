(function () {
    'use strict';

    const filterNames = new Set([
        'start_date',
        'end_date',
        'date_from',
        'date_to',
        'from_date',
        'to_date'
    ]);

    const filterIdPattern = /(^|[-_])(filter[-_]?start|filter[-_]?end|date[-_]?start|date[-_]?end|start[-_]?date|end[-_]?date)($|[-_])/i;

    const localeId = {
        weekdays: {
            shorthand: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            longhand: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            longhand: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        },
        firstDayOfWeek: 1
    };

    function isFilterDate(input) {
        if (!(input instanceof HTMLInputElement)) return false;
        if (input.dataset.nativeDate === 'true' || input.closest('[data-native-date="true"]')) return false;

        if (input.type !== 'date' && input.dataset.datePicker === undefined) return false;

        // This helper is intended for report/list filters only. Initialising every
        // date field also replaces transactional inputs (journal date, due date,
        // payroll date, etc.) with a read-only Flatpickr alternative input.
        // Keep ordinary form dates native and editable unless they explicitly opt
        // in through data-date-picker or match one of the known filter patterns.
        return input.dataset.datePicker !== undefined || usesFilterDefault(input);
    }

    function usesFilterDefault(input) {
        if (input.type !== 'date') return false;

        const name = String(input.name || '').toLowerCase();
        const id = String(input.id || '').toLowerCase();

        return input.dataset.filterDate !== undefined || filterNames.has(name) || filterIdPattern.test(id);
    }

    function jakartaDate(offsetDays) {
        const date = new Date();
        date.setDate(date.getDate() + offsetDays);

        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).formatToParts(date);
        const values = Object.fromEntries(parts.map(part => [part.type, part.value]));

        return `${values.year}-${values.month}-${values.day}`;
    }

    function applyDefault(input) {
        if (!usesFilterDefault(input) || input.value || input.dataset.noDefaultDate !== undefined) return;

        const policy = document.body?.dataset.filterDateDefault || 'today';
        if (policy === 'none' || policy === 'yesterday-to-today') return;

        input.value = jakartaDate(0);
    }

    function initialise(input) {
        if (!isFilterDate(input) || input._flatpickr || typeof window.flatpickr !== 'function') return;

        applyDefault(input);

        const submitFormat = input.dataset.datePickerFormat || (input.type === 'date' ? 'Y-m-d' : 'd M Y');

        const readOnly = input.readOnly;

        window.flatpickr(input, {
            altInput: true,
            altFormat: 'd/m/Y',
            altInputClass: 'lmg-filter-date-picker',
            allowInput: false,
            animate: false,
            clickOpens: !readOnly,
            dateFormat: submitFormat,
            defaultDate: input.value || null,
            disableMobile: true,
            locale: localeId,
            monthSelectorType: 'dropdown',
            onReady: function (_selectedDates, _dateStr, instance) {
                instance.calendarContainer.classList.add('lmg-filter-calendar');
                instance.altInput.setAttribute('autocomplete', 'off');
                instance.altInput.setAttribute('aria-label', input.getAttribute('aria-label') || 'Pilih tanggal');
                if (input.disabled) instance.altInput.disabled = true;
                if (readOnly) {
                    instance.altInput.readOnly = true;
                    instance.altInput.setAttribute('aria-readonly', 'true');
                }
                if (input.required) {
                    instance.altInput.required = true;
                    input.required = false;
                }
            },
            onOpen: function (_selectedDates, _dateStr, instance) {
                // Modal edit sering mengisi input asli lewat JavaScript. Sinkronkan
                // nilainya tepat sebelum kalender dibuka agar tanggal tetap benar.
                const selectedValue = instance.selectedDates[0]
                    ? instance.formatDate(instance.selectedDates[0], submitFormat)
                    : '';
                if (input.value && input.value !== selectedValue) {
                    instance.setDate(input.value, false, submitFormat);
                }
            }
        });
    }

    function scan(root) {
        if (root instanceof HTMLInputElement) initialise(root);
        root.querySelectorAll?.('input[type="date"], input[data-date-picker]').forEach(initialise);
    }

    function boot() {
        scan(document);

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node instanceof HTMLElement) scan(node);
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
