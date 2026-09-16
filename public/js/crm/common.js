(function () {
    'use strict';

    const app =
        document.getElementById('crmApp');

    if (!app) {
        return;
    }

    const crmModal = {
        open(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        },
        close(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
            if (!document.querySelector('[id$="Modal"]:not(.hidden)')) {
                document.body.classList.remove('overflow-hidden');
            }
        },
    };

    window.CRMModal = crmModal;

    document.addEventListener('click', function (event) {
        const closer = event.target.closest('[data-modal-close], [data-modal-backdrop]');
        if (!closer) return;
        crmModal.close(closer.dataset.modalClose || closer.dataset.modalBackdrop);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.querySelectorAll('[id$="Modal"]:not(.hidden)').forEach(function (modal) {
            crmModal.close(modal.id);
        });
    });

    const elements = {
        error:
            document.getElementById('crmError'),

        errorMessage:
            document.getElementById(
                'crmErrorMessage'
            ),

        total:
            document.getElementById('crmTotal'),

        startDate:
            document.getElementById('startDate'),

        endDate:
            document.getElementById('endDate'),

        dateField:
            document.getElementById('dateFieldFilter'),

        dateTypeField:
            document.getElementById('dateTypeField'),

        service:
            document.getElementById(
                'layananFilter'
            ),

        status:
            document.getElementById(
                'statusFilter'
            ),

        filterForm:
            document.getElementById(
                'crmFilterForm'
            ),

        resetButton:
            document.getElementById(
                'resetFilter'
            ),

        retryButton:
            document.getElementById(
                'retryButton'
            ),

        addButton:
            document.getElementById(
                'addLeadButton'
            ),

        startDateContext:
            document.getElementById(
                'startDateContext'
            ),

        endDateContext:
            document.getElementById(
                'endDateContext'
            ),

        form:
            document.getElementById(
                'leadForm'
            ),

        formError:
            document.getElementById(
                'leadFormError'
            ),
    };

    const urls = {
        ai: app.dataset.aiUrl,
        human: app.dataset.humanUrl,
        meeting: app.dataset.meetingUrl,
        crud: app.dataset.crudUrl,
    };

    const state = {
        activeTab: 'ai',
        requestController: null,
        editingId: null,

        data: {
            ai: [],
            human: [],
            meeting: [],
        },
    };

    function escapeHtml(value) {
        const element =
            document.createElement('div');

        element.textContent = value ?? '';

        return element.innerHTML;
    }

    function escapeAttribute(value) {
        return escapeHtml(
            String(value ?? '')
        )
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function whatsappHtml(phone) {
        if (!phone) {
            return '-';
        }

        return `
            <a
                href="https://wa.me/${
                    encodeURIComponent(phone)
                }"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="fab fa-whatsapp text-success"></i>
                ${escapeHtml(phone)}
            </a>
        `;
    }

    function normalizeSocialMedia(value) {
        if (!value) {
            return {};
        }

        if (
            typeof value === 'object' &&
            !Array.isArray(value)
        ) {
            return value;
        }

        if (typeof value === 'string') {
            try {
                const decoded =
                    JSON.parse(value);

                if (
                    decoded &&
                    typeof decoded === 'object' &&
                    !Array.isArray(decoded)
                ) {
                    return decoded;
                }
            } catch (error) {
                return {
                    lainnya: value,
                };
            }
        }

        return {};
    }

    function firstSocialValue(value) {
        if (Array.isArray(value)) {
            return value[0] || '';
        }

        return value || '';
    }

    function socialHtml(value) {
        const social =
            normalizeSocialMedia(value);

        const output = [];

        Object.entries(social)
            .forEach(([platform, item]) => {
                const links =
                    Array.isArray(item)
                        ? item
                        : [item];

                links.forEach(link => {
                    if (!link) {
                        return;
                    }

                    if (/^https?:\/\//i.test(link)) {
                        output.push(`
                            <a
                                href="${
                                    escapeAttribute(link)
                                }"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="badge badge-light border text-primary mr-1 mb-1"
                            >
                                ${escapeHtml(platform)}
                            </a>
                        `);
                    } else {
                        output.push(`
                            <span
                                class="badge badge-light border mr-1 mb-1"
                            >
                                ${escapeHtml(platform)}:
                                ${escapeHtml(link)}
                            </span>
                        `);
                    }
                });
            });

        return output.length
            ? output.join('')
            : '-';
    }

    function prospectBadge(value) {
        const prospect =
            String(value || '')
                .toLowerCase();

        const badgeClass = {
            cold: 'badge-secondary',
            warm: 'badge-warning',
            hot: 'badge-danger',
        }[prospect] || 'badge-light';

        const label = prospect
            ? prospect.charAt(0).toUpperCase() +
              prospect.slice(1)
            : '-';

        return `
            <span class="badge ${badgeClass}">
                ${escapeHtml(label)}
            </span>
        `;
    }

    function statusBadge(value) {
        return `
            <span class="badge badge-primary">
                ${escapeHtml(value || '-')}
            </span>
        `;
    }

    const helpers = {
        escape: escapeHtml,
        escapeAttribute,
        whatsapp: whatsappHtml,
        social: socialHtml,
        prospectBadge,
        statusBadge,
    };

    function hideError() {
        elements.error?.classList.add(
            'hidden'
        );
    }

    function showError(message) {
        if (elements.errorMessage) {
            elements.errorMessage.textContent =
                message ||
                'CRM sedang mengalami gangguan.';
        }

        elements.error?.classList.remove(
            'hidden'
        );

        if (elements.total) {
            elements.total.textContent =
                'Data tidak tersedia';
        }
    }

    function findLeadById(id) {
        return state.data[
            state.activeTab
        ].find(
            lead =>
                String(lead.id) === String(id)
        ) || null;
    }

    function showTab(tab) {
        state.activeTab = ['ai', 'human', 'meeting'].includes(tab)
            ? tab
            : 'ai';

        hideError();

        document
            .querySelectorAll('.crm-tab')
            .forEach(button => {
                const active =
                    button.dataset.tab ===
                    state.activeTab;

                button.classList.toggle('bg-zinc-950', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('shadow-sm', active);
                button.classList.toggle('font-semibold', active);
                button.classList.toggle('text-zinc-500', !active);
                button.classList.toggle('font-medium', !active);
            });

        document
            .querySelectorAll(
                '.crm-tab-content'
            )
            .forEach(content => {
                content.classList.add(
                    'hidden'
                );
            });

        document
            .getElementById(
                `${state.activeTab}TabContent`
            )
            ?.classList.remove('hidden');

        const isMeeting = state.activeTab === 'meeting';
        const dateContext = isMeeting
            ? 'Meeting'
            : elements.dateField?.value === 'first_chat'
                ? 'Pertama Kali Chat'
                : 'Chat';

        elements.dateTypeField?.classList.toggle('hidden', isMeeting);

        if (elements.startDateContext) {
            elements.startDateContext.textContent = dateContext;
        }

        if (elements.endDateContext) {
            elements.endDateContext.textContent = dateContext;
        }
    }

    function buildApiUrl() {
        const baseUrl = urls[state.activeTab];

        const url = new URL(
            baseUrl,
            window.location.origin
        );

        const filters = {
            start_date:
                elements.startDate?.value,

            end_date:
                elements.endDate?.value,

            date_field:
                state.activeTab === 'meeting'
                    ? ''
                    : elements.dateField?.value,

            layanan:
                elements.service?.value,

            stage:
                elements.status?.value,
        };

        Object.entries(filters)
            .forEach(([key, value]) => {
                if (value) {
                    url.searchParams.set(
                        key,
                        value
                    );
                }
            });

        return url.toString();
    }

    function fillFilters(result) {
        const filters =
            result.filters || {};

        const selectedService =
            elements.service.value;

        const selectedStatus =
            elements.status.value;

        if (filters.date_field && elements.dateField) {
            elements.dateField.value = filters.date_field;
        }

        if (
            !elements.startDate.value &&
            filters.start_date
        ) {
            elements.startDate.value =
                filters.start_date;
        }

        if (
            !elements.endDate.value &&
            filters.end_date
        ) {
            elements.endDate.value =
                filters.end_date;
        }

        elements.service.innerHTML =
            '<option value="">Semua Layanan</option>';

        (filters.services || [])
            .forEach(service => {
                const option =
                    document.createElement('option');

                option.value = service;
                option.textContent = service;

                option.selected =
                    String(service) ===
                    String(selectedService);

                elements.service.appendChild(
                    option
                );
            });

        elements.status.innerHTML =
            '<option value="">Semua Status</option>';

        (filters.statuses || [])
            .forEach(status => {
                const option =
                    document.createElement('option');

                option.value = status.value;
                option.textContent = status.label;

                option.selected =
                    String(status.value) ===
                    String(selectedStatus);

                elements.status.appendChild(
                    option
                );
            });
    }

    function renderCurrentTab() {
        const activeTab = state.activeTab;
        const availableRenderers =
            window.CRMRenderers || {};

        const renderer =
            availableRenderers[activeTab];

        if (typeof renderer !== 'function') {
            console.error(
                '[CRM RENDER ERROR] Renderer tidak ditemukan.',
                {
                    activeTab,
                    renderer,
                    rendererType: typeof renderer,
                    availableRenderers:
                        Object.keys(
                            availableRenderers
                        ),
                    crmRenderers:
                        window.CRMRenderers,
                    data:
                        state.data[activeTab],
                }
            );

            showError(
                `Tampilan CRM ${activeTab} tidak dapat dimuat.`
            );

            return;
        }

        try {
            renderer(
                state.data[activeTab],
                helpers
            );
        } catch (error) {
            console.error(
                '[CRM RENDER ERROR] Terjadi error saat menjalankan renderer.',
                {
                    activeTab,
                    message: error.message,
                    stack: error.stack,
                    error,
                    data:
                        state.data[activeTab],
                    helpers,
                }
            );

            showError(
                `Terjadi kesalahan saat menampilkan data ${activeTab}.`
            );
        }
    }
    async function loadData() {
        hideError();

        state.requestController?.abort();

        const controller =
            new AbortController();

        state.requestController =
            controller;

        const requestedTab =
            state.activeTab;

        const timeoutId = setTimeout(
            () => controller.abort(),
            15000
        );

        try {
            const response = await fetch(
                buildApiUrl(),
                {
                    method: 'GET',

                    headers: {
                        Accept:
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',
                    },

                    signal: controller.signal,
                }
            );

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    'Data tidak dapat diambil.'
                );
            }

            if (
                requestedTab !==
                state.activeTab
            ) {
                return;
            }

            state.data[requestedTab] =
                result.data || [];

            fillFilters(result);
            renderCurrentTab();

            elements.total.textContent =
                `${result.total || 0} data ${
                    requestedTab === 'meeting'
                        ? 'Jadwal Meeting'
                        : requestedTab === 'human'
                            ? 'Human'
                            : 'AI'
                }`;
        } catch (error) {
            if (
                error.name === 'AbortError' &&
                requestedTab !==
                    state.activeTab
            ) {
                return;
            }

            showError(
                error.name === 'AbortError'
                    ? 'Permintaan data CRM melewati batas waktu. Silakan coba lagi.'
                    : error.message || 'Data CRM tidak dapat diambil.'
            );
        } finally {
            clearTimeout(timeoutId);
        }
    }

    function formValue(id) {
        const field =
            document.getElementById(id);

        const value =
            field?.value?.trim();

        return value || null;
    }

    function setFormValue(id, value) {
        const field =
            document.getElementById(id);

        if (field) {
            field.value = value ?? '';
        }
    }

    function normalizeDateTimeLocal(value) {
        if (!value) {
            return '';
        }

        if (
            /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/
                .test(value)
        ) {
            return value;
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return new Intl.DateTimeFormat(
            'sv-SE',
            {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }
        )
            .format(date)
            .replace(' ', 'T');
    }

    function resetLeadForm() {
        elements.form?.reset();

        state.editingId = null;

        elements.formError
            ?.classList.add('d-none');

        if (elements.formError) {
            elements.formError.textContent =
                '';
        }

        setFormValue(
            'leadProspek',
            'warm'
        );
    }

    function configureLeadForm() {
        const human =
            state.activeTab === 'human';

        document
            .getElementById('humanFields')
            ?.classList.toggle(
                'd-none',
                !human
            );

        document
            .getElementById('aiFields')
            ?.classList.toggle(
                'd-none',
                human
            );

        document
            .getElementById('emailField')
            ?.classList.toggle(
                'd-none',
                human
            );
    }

    function openCreateLead() {
        resetLeadForm();
        configureLeadForm();

        const title =
            document.getElementById(
                'leadFormTitle'
            );

        if (title) {
            title.textContent =
                state.activeTab === 'human'
                    ? 'Tambah Lead Human'
                    : 'Tambah Lead AI';
        }

        crmModal.open('leadFormModal');
    }

    function populateEditForm(lead) {
        setFormValue(
            'leadNama',
            lead.nama
        );

        setFormValue(
            'leadBrand',
            lead.brand ??
            lead.nama_usaha
        );

        setFormValue(
            'leadPhone',
            lead.phone
        );

        setFormValue(
            'leadEmail',
            lead.email
        );

        setFormValue(
            'leadKategori',
            lead.kategori
        );

        setFormValue(
            'leadLayanan',
            lead.layanan
        );

        setFormValue(
            'leadProspek',
            lead.prospek || 'warm'
        );

        setFormValue(
            'leadStatus',
            lead.status
        );

        setFormValue(
            'leadKebutuhan',
            lead.kebutuhan
        );

        setFormValue(
            'leadKendala',
            lead.kendala
        );

        setFormValue(
            'leadKesimpulan',
            lead.kesimpulan
        );

        setFormValue(
            'leadNote',
            lead.note
        );

        const social =
            normalizeSocialMedia(
                lead.sosial_media
            );

        setFormValue(
            'leadInstagram',
            firstSocialValue(
                social.instagram
            )
        );

        setFormValue(
            'leadFacebook',
            firstSocialValue(
                social.facebook
            )
        );

        setFormValue(
            'leadShopee',
            firstSocialValue(
                social.shopee
            )
        );

        const canPush =
            document.getElementById(
                'leadCanPush'
            );

        if (canPush) {
            canPush.checked =
                Boolean(lead.can_push);
        }
    }

    function openEditLead(id) {
        const lead = findLeadById(id);

        if (!lead) {
            showError(
                'Data yang akan diedit tidak ditemukan.'
            );

            return;
        }

        resetLeadForm();
        configureLeadForm();

        state.editingId = lead.id;

        const title =
            document.getElementById(
                'leadFormTitle'
            );

        if (title) {
            title.textContent =
                state.activeTab === 'human'
                    ? 'Edit Lead Human'
                    : 'Edit Lead AI';
        }

        /*
         * Modal CRM dibuka oleh helper Tailwind, bukan Bootstrap.
         * Isi nilai lama secara langsung agar tidak bergantung pada
         * event shown.bs.modal yang tidak dipicu oleh helper ini.
         */
        populateEditForm(lead);
        crmModal.open('leadFormModal');
    }

    function createPayload() {
        return {
            nama: formValue('leadNama'),
            brand: formValue('leadBrand'),
            phone: formValue('leadPhone'),
            email: formValue('leadEmail'),

            kategori:
                formValue('leadKategori'),

            layanan:
                formValue('leadLayanan'),

            kebutuhan:
                formValue('leadKebutuhan'),

            kendala:
                formValue('leadKendala'),

            kesimpulan:
                formValue(
                    'leadKesimpulan'
                ),

            note:
                formValue('leadNote'),

            can_push:
                document.getElementById(
                    'leadCanPush'
                )?.checked || false,

            prospek:
                formValue('leadProspek')
                || 'warm',

            status:
                formValue('leadStatus'),

            sosial_media: {
                instagram:
                    formValue(
                        'leadInstagram'
                    ),

                facebook:
                    formValue(
                        'leadFacebook'
                    ),

                shopee:
                    formValue(
                        'leadShopee'
                    ),
            },
        };
    }

    async function saveLead(event) {
        event.preventDefault();

        elements.formError
            ?.classList.add('d-none');

        const isEdit =
            state.editingId !== null;

        const endpoint = isEdit
            ? `${urls.crud}/${
                state.activeTab
            }/${encodeURIComponent(
                state.editingId
            )}`
            : `${urls.crud}/${
                state.activeTab
            }`;

        try {
            const response = await fetch(
                endpoint,
                {
                    method:
                        isEdit
                            ? 'PUT'
                            : 'POST',

                    headers: {
                        Accept:
                            'application/json',

                        'Content-Type':
                            'application/json',
                    },

                    body: JSON.stringify(
                        createPayload()
                    ),
                }
            );

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                const validation =
                    result.errors
                        ? Object.values(
                            result.errors
                        )
                            .flat()
                            .join(' ')
                        : null;

                throw new Error(
                    validation ||
                    result.message ||
                    'Data gagal disimpan.'
                );
            }

            crmModal.close('leadFormModal');

            await loadData();
        } catch (error) {
            if (elements.formError) {
                elements.formError
                    .textContent =
                        error.message ||
                        'Data gagal disimpan.';

                elements.formError
                    .classList.remove(
                        'd-none'
                    );
            }
        }
    }

    async function deleteLead(id) {
        const lead = findLeadById(id);

        if (!lead) {
            showError(
                'Data yang akan dihapus tidak ditemukan.'
            );

            return;
        }

        const confirmed = window.confirm(
            `Hapus data ${
                lead.nama || 'lead ini'
            }? Data tidak dapat dikembalikan.`
        );

        if (!confirmed) {
            return;
        }

        try {
            const response = await fetch(
                `${urls.crud}/${
                    state.activeTab
                }/${encodeURIComponent(
                    lead.id
                )}`,
                {
                    method: 'DELETE',

                    headers: {
                        Accept:
                            'application/json',
                    },
                }
            );

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    'Data gagal dihapus.'
                );
            }

            await loadData();
        } catch (error) {
            showError(
                error.message ||
                'Data gagal dihapus.'
            );
        }
    }

    document
        .querySelectorAll('.crm-tab')
        .forEach(button => {
            button.addEventListener(
                'click',
                function () {
                    const tab =
                        this.dataset.tab;

                    if (
                        tab ===
                        state.activeTab
                    ) {
                        return;
                    }

                    const previousTab = state.activeTab;

                    showTab(tab);

                    elements.addButton?.classList.toggle(
                        'hidden',
                        tab === 'meeting'
                    );

                    if (
                        tab === 'meeting' ||
                        previousTab === 'meeting'
                    ) {
                        elements.startDate.value = '';
                        elements.endDate.value = '';
                    }

                    elements.service.value =
                        '';

                    elements.status.value =
                        '';

                    loadData();
                }
            );
        });

    elements.filterForm
        ?.addEventListener(
            'submit',
            event => {
                event.preventDefault();
                loadData();
            }
        );

    elements.resetButton
        ?.addEventListener(
            'click',
            () => {
                elements.startDate.value =
                    '';

                elements.endDate.value =
                    '';

                if (elements.dateField) {
                    elements.dateField.value = 'chat';
                }

                elements.service.value =
                    '';

                elements.status.value =
                    '';

                loadData();
            }
        );

    elements.retryButton
        ?.addEventListener(
            'click',
            loadData
        );

    elements.dateField
        ?.addEventListener(
            'change',
            function () {
                const context = this.value === 'first_chat'
                    ? 'Pertama Kali Chat'
                    : 'Chat';

                if (elements.startDateContext) {
                    elements.startDateContext.textContent = context;
                }

                if (elements.endDateContext) {
                    elements.endDateContext.textContent = context;
                }
            }
        );

    elements.addButton
        ?.addEventListener(
            'click',
            openCreateLead
        );

    elements.form
        ?.addEventListener(
            'submit',
            saveLead
        );

    document.addEventListener(
        'click',
        event => {
            const editButton =
                event.target.closest(
                    '.edit-lead'
                );

            if (editButton) {
                openEditLead(
                    editButton.dataset.id
                );

                return;
            }

            const deleteButton =
                event.target.closest(
                    '.delete-lead'
                );

            if (deleteButton) {
                deleteLead(
                    deleteButton.dataset.id
                );
            }
        }
    );

    showTab('ai');
    loadData();

})();
