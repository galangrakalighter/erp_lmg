window.CRMRenderers =
    window.CRMRenderers || {};

(function () {
    let count = 0;
    'use strict';

    const detailTexts = new Map();
    let detailCounter = 0;

    function longText(
        value,
        title,
        helpers
    ) {
        const original =
            value === null ||
            value === undefined ||
            String(value).trim() === ''
                ? '-'
                : String(value).trim();

        const words = original
            .split(/\s+/)
            .filter(Boolean);

        if (
            original === '-' ||
            words.length <= 6
        ) {
            return `
                <span>
                    ${helpers.escape(original)}
                </span>
            `;
        }

        const key =
            `human-${++detailCounter}`;

        detailTexts.set(key, {
            title: title,
            content: original,
        });

        return `
            <div
                style="
                    min-width:150px;
                    max-width:230px;
                "
            >
                <div
                    style="
                        overflow:hidden;
                        text-overflow:ellipsis;
                        white-space:nowrap;
                    "
                >
                    ${helpers.escape(
                        words.slice(0, 3).join(' ')
                    )}...
                </div>

                <button
                    type="button"
                    class="
                        btn btn-link btn-sm
                        human-show-detail
                        p-0 mt-1
                    "
                    data-detail-key="${
                        helpers.escapeAttribute(
                            key
                        )
                    }"
                >
                    <i class="fas fa-eye mr-1"></i>
                    Selengkapnya
                </button>
            </div>
        `;
    }

    function normalizeSocialMedia(value) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '-';
        }

        /*
        * Jika masih berupa string JSON,
        * coba ubah menjadi array/object.
        */
        if (typeof value === 'string') {
            const trimmedValue =
                value.trim();

            if (!trimmedValue) {
                return '-';
            }

            try {
                const decoded =
                    JSON.parse(trimmedValue);

                return normalizeSocialMedia(
                    decoded
                );
            } catch (error) {
                /*
                * Bukan JSON, berarti sudah
                * berupa teks biasa.
                */
                return trimmedValue;
            }
        }

        /*
        * Jika berupa array:
        *
        * [
        *     {"platform":"Instagram","url":"..."},
        *     {"platform":"TikTok","username":"..."}
        * ]
        */
        if (Array.isArray(value)) {
            const results = value
                .map(function (item) {
                    return normalizeSocialMedia(
                        item
                    );
                })
                .filter(function (item) {
                    return (
                        item &&
                        item !== '-'
                    );
                });

            return results.length
                ? results.join('\n')
                : '-';
        }

        /*
        * Jika berupa object.
        */
        if (
            typeof value === 'object'
        ) {
            const platform =
                value.platform ??
                value.type ??
                value.label ??
                value.name ??
                null;

            const account =
                value.url ??
                value.link ??
                value.username ??
                value.handle ??
                value.account ??
                value.value ??
                null;

            /*
            * Format object yang mempunyai
            * platform dan akun/link.
            */
            if (platform && account) {
                return (
                    `${normalizeSocialMedia(
                        platform
                    )}: ` +
                    normalizeSocialMedia(
                        account
                    )
                );
            }

            /*
            * Jika hanya memiliki link/username.
            */
            if (account) {
                return normalizeSocialMedia(
                    account
                );
            }

            /*
            * Format object biasa:
            *
            * {
            *     "instagram":"@nama",
            *     "tiktok":"@nama"
            * }
            */
            const results = Object.entries(
                value
            )
                .map(function (
                    [key, item]
                ) {
                    const normalizedItem =
                        normalizeSocialMedia(
                            item
                        );

                    if (
                        !normalizedItem ||
                        normalizedItem === '-'
                    ) {
                        return null;
                    }

                    const readableKey =
                        key
                            .replace(
                                /_/g,
                                ' '
                            )
                            .replace(
                                /\b\w/g,
                                function (
                                    character
                                ) {
                                    return character
                                        .toUpperCase();
                                }
                            );

                    return (
                        `${readableKey}: ` +
                        normalizedItem
                    );
                })
                .filter(Boolean);

            return results.length
                ? results.join('\n')
                : '-';
        }

        /*
        * Number atau boolean.
        */
        return String(value);
    }

    function booleanValue(value) {
        return (
            value === true ||
            value === 1 ||
            value === '1' ||
            value === 'true'
        );
    }

    function formatPrice(value, helpers) {
        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {
            return '-';
        }

        const price = Number(value);

        if (!Number.isFinite(price)) {
            return '-';
        }

        return helpers.escape(
            new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            }).format(price)
        );
    }

    function checklistCheckbox(
        leadId,
        field,
        value,
        label,
        helpers
    ) {
        const checked =
            booleanValue(value);

        return `
            <div
                class="
                    d-flex
                    align-items-center
                    justify-content-center
                "
            >
                <input
                    type="checkbox"
                    class="
                        human-auto-checkbox
                    "
                    data-id="${
                        helpers.escapeAttribute(
                            leadId
                        )
                    }"
                    data-field="${
                        helpers.escapeAttribute(
                            field
                        )
                    }"
                    data-previous-value="${
                        checked ? '1' : '0'
                    }"
                    aria-label="${
                        helpers.escapeAttribute(
                            label
                        )
                    }"
                    title="${
                        helpers.escapeAttribute(
                            label
                        )
                    }"
                    ${checked ? 'checked' : ''}
                    style="
                        width: 19px;
                        height: 19px;
                        cursor: pointer;
                    "
                >

                <span
                    class="
                        human-checkbox-status
                        ml-2
                    "
                    style="
                        min-width: 15px;
                        font-size: 0.75rem;
                    "
                ></span>
            </div>
        `;
    }

    const followUpStages = [
        // 'Mini Audit',
        // 'Insight',
        // 'Study Kasus',
        'Follow Up Ghosting 1',
        'Follow Up Ghosting 2',
        'Follow Up Ghosting 3',
    ];

    function todayFollowUpCheckbox(lead, helpers) {
        count = Math.min(
            3,
            Math.max(0, Number(lead.jumlah_follow_up) || 0)
        );
        const doneToday = booleanValue(lead.today);
        const disabled = doneToday || count >= 3;


        return `
            <div
                class="human-follow-up-today d-flex align-items-center justify-content-center"
                data-follow-up-id="${helpers.escapeAttribute(lead.id)}"
            >
                <input
                    type="checkbox"
                    class="human-follow-up-checkbox"
                    data-id="${helpers.escapeAttribute(lead.id)}"
                    ${doneToday ? 'checked' : ''}
                    ${disabled ? 'disabled' : ''}
                    title="${count >= 3 ? 'Follow up sudah selesai' : 'Follow up hari ini'}"
                    style="width:19px;height:19px;cursor:${disabled ? 'not-allowed' : 'pointer'}"
                >
                <span class="human-follow-up-status ml-2 small text-muted"></span>
            </div>
        `;
    }

    function followUpDetailButton(lead, helpers) {
        const count = Math.min(3, Math.max(
            0,
            Number(lead.jumlah_follow_up) || 0
        ));

        return `
            <button
                type="button"
                class="btn btn-info btn-sm human-follow-up-detail text-nowrap"
                data-name="${helpers.escapeAttribute(lead.nama || 'Human')}"
                data-count="${count}"
                data-toggle="modal"
                data-target="#humanFollowUpDetailModal"
            >
                <i class="fas fa-list mr-1"></i>
                Detail ${count}/3
            </button>
        `;
    }

    window.CRMRenderers.human =
        function (data, helpers) {
            const body =
                document.getElementById(
                    'humanTableBody'
                );

            if (!body) {
                return;
            }

            detailTexts.clear();
            detailCounter = 0;

            if (
                !Array.isArray(data) ||
                !data.length
            ) {
                body.innerHTML = `
                    <tr>
                        <td
                            colspan="15"
                            class="
                                text-center
                                text-muted
                                py-4
                            "
                        >
                            Tidak ada data Human.
                        </td>
                    </tr>
                `;

                return;
            }

            body.innerHTML = data
                .map(function (
                    lead,
                    index
                ) {
                    const id =
                        helpers.escapeAttribute(
                            lead.id || ''
                        );

                    const name =
                        helpers.escapeAttribute(
                            lead.nama ||
                            'No Name'
                        );

                    const phone =
                        helpers.escapeAttribute(
                            lead.phone || ''
                        );
                    const socialMedia =
                        normalizeSocialMedia(
                            lead.sosial_media
                        );

                    const sumberAi =
                        lead.sumber_ai === true ||
                        lead.sumber_ai === 1 ||
                        lead.sumber_ai === '1' ||
                        lead.sumber_ai === 'true';

                    return `
                        <tr class="${
                            sumberAi
                                ? 'table-info'
                                : ''
                        }">
                            <td class="align-middle">
                                ${index + 1}
                            </td>

                            <td class="align-middle">
                                ${longText(
                                    lead.tanggal_chat,
                                    'Tanggal Chat',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${longText(
                                    lead.created_at,
                                    'Tanggal Dibuat',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    font-weight-bold
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.nama,
                                    'Nama Client',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.brand,
                                    'Brand',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.kategori,
                                    'Kategori',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-nowrap
                                    align-middle
                                "
                            >
                                ${
                                    sumberAi
                                        ? `
                                            <span
                                                class="
                                                    badge
                                                    badge-info
                                                    px-2
                                                    py-2
                                                "
                                                title="
                                                    Lead ini berasal
                                                    dari percakapan AI
                                                "
                                            >
                                                <i
                                                    class="
                                                        fas
                                                        fa-robot
                                                        mr-1
                                                    "
                                                ></i>
                                                Paid
                                            </span>
                                        `
                                        : `
                                            <span
                                                class="
                                                    badge
                                                    badge-light
                                                    border
                                                    text-secondary
                                                    px-2
                                                    py-2
                                                "
                                                title="
                                                    Lead ini langsung
                                                    ditangani Human
                                                "
                                            >
                                                <i
                                                    class="
                                                        fas
                                                        fa-user
                                                        mr-1
                                                    "
                                                ></i>
                                                Organik
                                            </span>
                                        `
                                }
                            </td>

                            <td
                                class="
                                    text-nowrap
                                    align-middle
                                "
                            >
                                ${helpers.whatsapp(
                                    lead.phone
                                )}
                            </td>

                            <td class="align-middle text-nowrap">
                                <span class="badge badge-info px-2 py-2">
                                    ${helpers.escape(
                                        Number(lead.rate_balasan || 0)
                                            .toLocaleString('en-US', {
                                                minimumFractionDigits: 0,
                                                maximumFractionDigits: 6,
                                                useGrouping: false,
                                            })
                                    )}%
                                </span>
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.layanan,
                                    'Layanan',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle text-nowrap">
                                ${formatPrice(
                                    lead.price,
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.chanelling,
                                    'Chanelling',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    socialMedia,
                                    'Sosial Media',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.kebutuhan,
                                    'Kebutuhan',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${helpers.prospectBadge(
                                    lead.prospek
                                )}
                            </td>

                            <td class="align-middle human-lead-status">
                                ${helpers.statusBadge(
                                    lead.status || '-'
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'can_push',
                                    lead.can_push,
                                    'Can Push',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'service_plan',
                                    lead.service_plan,
                                    'Service Plan',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'need_analysys',
                                    lead.need_analysys,
                                    'Need Analysis',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'done_meeting',
                                    lead.done_meeting,
                                    'Done Meeting',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'mini_audit',
                                    lead.mini_audit,
                                    'Mni Audit',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'insight',
                                    lead.insight,
                                    'Insight',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${checklistCheckbox(
                                    lead.id,
                                    'study_kasus',
                                    lead.study_kasus,
                                    'Study Kasus',
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${todayFollowUpCheckbox(
                                    lead,
                                    helpers
                                )}
                            </td>

                            <td class="align-middle">
                                ${followUpDetailButton(
                                    lead,
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-left
                                    align-middle
                                "
                            >
                                ${longText(
                                    lead.note,
                                    'Note',
                                    helpers
                                )}
                            </td>

                            <td
                                class="
                                    text-nowrap
                                    align-middle
                                "
                            >
                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-success
                                        btn-sm
                                        human-history
                                    "
                                    data-id="${id}"
                                    data-name="${name}"
                                    data-phone="${phone}"
                                    title="History Chat"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.5 9.5 9.5 0 0 1-4.1-.9L3 20l1.2-4.1A8.2 8.2 0 0 1 3 11.5 8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5Z"/><path d="M8.5 9.2c.7 3 2.3 4.7 5.4 5.5"/></svg>
                                </button>

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-warning
                                        btn-sm
                                        edit-lead
                                    "
                                    data-id="${id}"
                                    title="Edit"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                </button>

                                <button
                                    type="button"
                                    class="
                                        btn
                                        btn-danger
                                        btn-sm
                                        delete-lead
                                    "
                                    data-id="${id}"
                                    title="Hapus"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg>
                                </button>
                            </td>
                        </tr>
                    `;
                })
                .join('');

            body.querySelectorAll('tr').forEach(function (row) {
                const cells = Array.from(row.children);

                if (cells.length !== 27) {
                    return;
                }

                const value = index => cells[index].innerHTML;
                const progress = [
                    ['Today', 23], ['Service Plan', 17],
                    ['Need Analysis', 18], ['Meeting', 19],
                    ['Mini Audit', 20], ['Insight', 21],
                    ['Study Kasus', 22], ['Can Push', 16],
                ].map(function ([label, index]) {
                    return `<div class="crm-progress-item"><span>${label}</span>${value(index)}</div>`;
                }).join('');

                row.innerHTML = `
                    <td>${value(0)}</td>
                    <td><div class="crm-stack"><span class="crm-field-label">Tanggal chat</span>${value(1)}<span class="crm-field-label crm-field-gap">Pertama chat</span>${value(2)}</div></td>
                    <td><div class="crm-stack"><span class="crm-field-label">Nama</span><strong class="crm-lead-name">${value(3)}</strong><span class="crm-field-label crm-field-gap">Nama usaha</span>${value(4)}</div></td>
                    <td>${value(12)}</td>
                    <td>${value(5)}</td>
                    <td><div class="crm-stack"><span class="crm-field-label">Telepon</span>${value(7)}<span class="crm-field-label crm-field-gap">Rate balasan</span>${value(8)}</div></td>
                    <td><div class="crm-stack"><span class="crm-field-label">Sumber</span>${value(6)}<span class="crm-field-label crm-field-gap">Chanelling</span><span class="crm-muted">${value(11)}</span></div></td>
                    <td>${value(9)}</td>
                    <td>${value(10)}</td>
                    <td>${value(13)}</td>
                    <td>${value(14)}</td>
                    <td class="human-lead-status">${value(15)}</td>
                    <td><div class="crm-progress-grid">${progress}</div><div class="crm-follow-up-detail">${value(24)}</div></td>
                    <td>${value(25)}</td>
                    <td><div class="crm-actions">${value(26)}</div></td>
                `;
            });
        };

    document.addEventListener(
        'click',
        function (event) {
            const detailButton =
                event.target.closest(
                    '.human-show-detail'
                );

            if (detailButton) {
                showDetail(
                    detailButton.dataset
                        .detailKey
                );

                return;
            }

            if (count >= 3) {
                const statusCell = row?.querySelector('.human-lead-status');

                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="badge badge-primary">
                            ghosting
                        </span>
                    `;
                }
            }

            const followUpDetailButton =
                event.target.closest(
                    '.human-follow-up-detail'
                );

            if (followUpDetailButton) {
                showFollowUpDetail(
                    followUpDetailButton
                );
                return;
            }

            const historyButton =
                event.target.closest(
                    '.human-history'
                );

            if (historyButton) {
                loadHistory({
                    id:
                        historyButton
                            .dataset.id,
                    name:
                        historyButton
                            .dataset.name,
                    phone:
                        historyButton
                            .dataset.phone,
                });
            }
        }
    );

    document.addEventListener(
        'change',
        function (event) {
            const followUpCheckbox =
                event.target.closest(
                    '.human-follow-up-checkbox'
                );

            if (followUpCheckbox) {
                incrementHumanFollowUp(followUpCheckbox);
                return;
            }

            const checkbox =
                event.target.closest(
                    '.human-auto-checkbox'
                );

            if (!checkbox) {
                return;
            }

            updateHumanChecklist(
                checkbox
            );
        }
    );

    async function incrementHumanFollowUp(checkbox) {
        if (!checkbox.checked) {
            return;
        }

        const app = document.getElementById('crmApp');
        const container = checkbox.closest('.human-follow-up-today');
        const status = container?.querySelector('.human-follow-up-status');
        const leadId = checkbox.dataset.id;
        const baseUrl = app.dataset.humanChecklistUrl;

        checkbox.disabled = true;
        if (status) {
            status.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';
        }

        try {
            const response = await fetch(
                `${baseUrl}/${encodeURIComponent(leadId)}/follow-up`,
                {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                }
            );
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mencatat follow up.');
            }

            const count = Number(result.data?.jumlah_follow_up) || 0;
            checkbox.checked = true;
            checkbox.disabled = true;

            const row = checkbox.closest('tr');
            const detailButton = row?.querySelector('.human-follow-up-detail');

            if (detailButton) {
                detailButton.dataset.count = String(count);
                detailButton.innerHTML = `
                    <i class="fas fa-list mr-1"></i>
                    Detail ${count}/3
                `;
            }

            if (status) {
                status.innerHTML = '<i class="fas fa-check-circle text-success" title="Tersimpan"></i>';
            }
        } catch (error) {
            checkbox.checked = false;
            checkbox.disabled = false;

            if (status) {
                status.textContent = 'Gagal menyimpan';
            }

            window.alert(error.message || 'Gagal mencatat follow up.');
        }
    }

    function showFollowUpDetail(button) {
        const count = Math.min(3, Math.max(
            0,
            Number(button.dataset.count) || 0
        ));
        const name = button.dataset.name || 'Human';
        const title = document.getElementById('humanFollowUpDetailModalLabel');
        const body = document.getElementById('humanFollowUpDetailBody');

        if (!body || !title) {
            return;
        }

        title.textContent = `Detail Follow Up - ${name}`;
        body.innerHTML = `
            <div class="list-group">
                ${followUpStages.map(function (label, index) {
                    const completed = index < count;
                    return `
                        <div class="list-group-item d-flex align-items-center">
                            <i class="fas ${completed ? 'fa-check-circle text-success' : 'fa-circle text-muted'} mr-3"></i>
                            <span class="${completed ? 'font-weight-bold' : 'text-muted'}">
                                ${index + 1}. ${escapeModalText(label)}
                            </span>
                            <span class="badge ${completed ? 'badge-success' : 'badge-secondary'} ml-auto">
                                ${completed ? 'Sudah' : 'Belum'}
                            </span>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        const modal = $('#humanFollowUpDetailModal');

        if (modal.length && typeof modal.modal === 'function') {
            window.CRMModal?.open('humanFollowUpDetailModal');
        }
    }

    function escapeModalText(value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    }

    async function updateHumanChecklist(
        checkbox
    ) {
        const app =
            document.getElementById(
                'crmApp'
            );

        const status =
            checkbox.parentElement
                .querySelector(
                    '.human-checkbox-status'
                );

        const leadId =
            checkbox.dataset.id;

        const field =
            checkbox.dataset.field;

        const previousValue =
            checkbox.dataset
                .previousValue === '1';

        const currentValue =
            checkbox.checked;

        const baseUrl =
            app.dataset
                .humanChecklistUrl;

        checkbox.disabled = true;

        if (status) {
            status.innerHTML = `
                <i
                    class="
                        fas
                        fa-spinner
                        fa-spin
                        text-primary
                    "
                    title="Menyimpan..."
                ></i>
            `;
        }

        try {
            const response = await fetch(
                `${baseUrl}/${
                    encodeURIComponent(
                        leadId
                    )
                }/checklist`,
                {
                    method: 'PATCH',
                    credentials:
                        'same-origin',
                    headers: {
                        Accept:
                            'application/json',

                        'Content-Type':
                            'application/json',
                    },
                    body: JSON.stringify({
                        field: field,
                        value: currentValue,
                    }),
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
                    'Gagal menyimpan checklist.'
                );
            }


            /*
            * Simpan state terakhir yang
            * berhasil masuk database.
            */
            checkbox.dataset
                .previousValue =
                    currentValue
                        ? '1'
                        : '0';

            checkbox.checked =
                booleanValue(
                    result.data?.value
                    ?? currentValue
                );

            if (status) {
                status.innerHTML = `
                    <i
                        class="
                            fas
                            fa-check-circle
                            text-success
                        "
                        title="Tersimpan"
                    ></i>
                `;

                window.setTimeout(
                    function () {
                        status.innerHTML = '';
                    },
                    1500
                );
            }
        } catch (error) {
            /*
            * Kembalikan checkbox ke nilai
            * sebelum request dilakukan.
            */
            checkbox.checked =
                previousValue;

            if (status) {
                status.innerHTML = `
                    <i
                        class="
                            fas
                            fa-exclamation-circle
                            text-danger
                        "
                        title="Gagal menyimpan"
                    ></i>
                `;
            }

            window.alert(
                error.message ||
                'Gagal menyimpan checklist.'
            );
        } finally {
            checkbox.disabled = false;
        }
    }

    function showDetail(key) {
        const detail =
            detailTexts.get(key);

        if (!detail) {
            return;
        }

        document.getElementById(
            'crmTextDetailModalLabel'
        ).textContent = detail.title;

        document.getElementById(
            'crmTextDetailContent'
        ).textContent = detail.content;

        window.CRMModal?.open('crmTextDetailModal');
    }

    async function loadHistory(lead) {
        const app =
            document.getElementById(
                'crmApp'
            );

        const modalBody =
            document.getElementById(
                'crmChatHistoryBody'
            );

        const title =
            document.getElementById(
                'crmChatHistoryModalLabel'
            );

        const phoneText =
            document.getElementById(
                'crmChatHistoryPhone'
            );

        const whatsapp =
            document.getElementById(
                'crmOpenWhatsApp'
            );

        const historyUrl =
            app.dataset.humanHistoryUrl;

        title.textContent =
            `History Human: ${
                lead.name || 'Client'
            }`;

        phoneText.textContent =
            lead.phone
                ? `Nomor: ${lead.phone}`
                : 'Nomor tidak tersedia';

        if (lead.phone) {
            whatsapp.href =
                `https://wa.me/${
                    lead.phone.replace(
                        /\D/g,
                        ''
                    )
                }`;

            whatsapp.style.display =
                'inline-block';
        } else {
            whatsapp.style.display =
                'none';
        }

        modalBody.innerHTML = `
            <div
                class="
                    text-center
                    text-muted
                    py-5
                "
            >
                <i
                    class="
                        fas fa-spinner
                        fa-spin fa-2x
                        text-success mb-3
                    "
                ></i>

                <div>
                    Mengambil history...
                </div>
            </div>
        `;

        window.CRMModal?.open('crmChatHistoryModal');

        try {
            const response = await fetch(
                `${historyUrl}/${
                    encodeURIComponent(
                        lead.id
                    )
                }`,
                {
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
                    'Gagal mengambil history.'
                );
            }

            renderHistory(
                result.chats || [],
                lead.name,
                modalBody
            );
        } catch (error) {
            modalBody.innerHTML = `
                <div
                    class="
                        text-center
                        text-danger
                        py-5
                    "
                >
                    <i
                        class="
                            fas
                            fa-exclamation-triangle
                            fa-2x mb-3
                        "
                    ></i>

                    <div id="humanHistoryError"></div>
                </div>
            `;

            document.getElementById(
                'humanHistoryError'
            ).textContent =
                error.message;
        }
    }

    function renderHistory(
        chats,
        leadName,
        modalBody
    ) {
        modalBody.innerHTML = '';

        if (
            !Array.isArray(chats) ||
            !chats.length
        ) {
            showEmptyHistory(modalBody);
            return;
        }

        chats.forEach(function (chat) {
            if (!chat || typeof chat !== 'object') {
                return;
            }

            const hasText =
                typeof chat.message === 'string' &&
                chat.message.trim() !== '';

            const hasMedia =
                chat.has_media === true ||
                Boolean(chat.media?.url);

            const hasLocation =
                chat.location &&
                chat.location.latitude !== undefined &&
                chat.location.longitude !== undefined;

            const hasVCard =
                Array.isArray(chat.vcards) &&
                chat.vcards.length > 0;

            if (
                !hasText &&
                !hasMedia &&
                !hasLocation &&
                !hasVCard
            ) {
                return;
            }

            const isAdmin =
                chat.sender === 'me';

            const row =
                document.createElement('div');

            row.className =
                `flex w-full mb-4 ${
                    isAdmin
                        ? 'justify-end'
                        : 'justify-start'
                }`;

            const bubble =
                document.createElement('div');

            bubble.className =
                isAdmin
                    ? 'rounded-2xl rounded-br-md bg-zinc-950 px-4 py-3 text-white shadow-sm'
                    : 'rounded-2xl rounded-bl-md border border-zinc-200 bg-white px-4 py-3 text-zinc-800 shadow-sm';

            bubble.style.maxWidth = '85%';
            bubble.style.whiteSpace = 'pre-wrap';
            bubble.style.overflowWrap = 'anywhere';

            const sender =
                document.createElement('div');

            sender.className = isAdmin
                ? 'mb-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-400'
                : 'mb-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-500';

            sender.textContent =
                isAdmin
                    ? 'Admin'
                    : leadName || 'Client';

            bubble.appendChild(sender);

            appendHumanMedia(
                bubble,
                chat,
                isAdmin
            );

            if (hasText) {
                const message =
                    document.createElement('div');

                message.className = 'mt-2';
                message.textContent =
                    chat.message.trim();

                bubble.appendChild(message);
            }

            if (hasLocation) {
                appendHumanLocation(
                    bubble,
                    chat.location
                );
            }

            if (hasVCard) {
                appendHumanVCards(
                    bubble,
                    chat.vcards
                );
            }

            const time =
                document.createElement('small');

            time.className =
                `mt-2 block text-[10px] ${
                    isAdmin
                        ? 'text-zinc-400'
                        : 'text-zinc-500'
                }`;

            time.textContent =
                chat.created_at || '-';

            bubble.appendChild(time);
            row.appendChild(bubble);
            modalBody.appendChild(row);
        });

        if (!modalBody.children.length) {
            showEmptyHistory(modalBody);
            return;
        }

        modalBody.scrollTop =
            modalBody.scrollHeight;
    }

    function safeMediaUrl(value) {
        if (typeof value !== 'string') {
            return null;
        }

        try {
            const url = new URL(
                value,
                window.location.origin
            );

            if (
                url.protocol !== 'http:' &&
                url.protocol !== 'https:'
            ) {
                return null;
            }

            return url.href;
        } catch (error) {
            return null;
        }
    }

    function appendHumanMedia(
        bubble,
        chat,
        isAdmin
    ) {
        if (!chat.media) {
            return;
        }

        const category =
            chat.media.category || 'document';

        const mediaUrl =
            safeMediaUrl(chat.media.url);

        const filename =
            chat.media.filename || 'File';

        if (!mediaUrl) {
            if (chat.has_media) {
                const unavailable =
                    document.createElement('div');

                unavailable.className =
                    isAdmin
                        ? 'border border-light rounded p-2'
                        : 'border rounded bg-light p-2';

                unavailable.textContent =
                    `📎 ${filename} — media tidak tersedia`;

                bubble.appendChild(unavailable);
            }

            return;
        }

        if (
            category === 'image' ||
            category === 'sticker'
        ) {
            const image =
                document.createElement('img');

            image.src = mediaUrl;
            image.alt = filename;
            image.loading = 'lazy';
            image.className =
                'img-fluid rounded d-block';

            image.style.maxWidth = '320px';
            image.style.maxHeight = '320px';
            image.style.objectFit = 'contain';

            image.addEventListener(
                'click',
                function () {
                    window.open(
                        mediaUrl,
                        '_blank',
                        'noopener,noreferrer'
                    );
                }
            );

            image.style.cursor = 'pointer';
            bubble.appendChild(image);
            return;
        }

        if (category === 'video') {
            const video =
                document.createElement('video');

            video.src = mediaUrl;
            video.controls = true;
            video.preload = 'metadata';
            video.className =
                'rounded d-block';

            video.style.maxWidth = '320px';
            video.style.width = '100%';

            bubble.appendChild(video);
            return;
        }

        if (category === 'audio') {
            const audio =
                document.createElement('audio');

            audio.src = mediaUrl;
            audio.controls = true;
            audio.preload = 'metadata';
            audio.style.maxWidth = '300px';
            audio.style.width = '100%';

            bubble.appendChild(audio);
            return;
        }

        const fileLink =
            document.createElement('a');

        fileLink.href = mediaUrl;
        fileLink.target = '_blank';
        fileLink.rel = 'noopener noreferrer';

        fileLink.className =
            isAdmin
                ? 'btn btn-light btn-sm'
                : 'btn btn-outline-primary btn-sm';

        fileLink.textContent =
            `📎 ${filename}`;

        bubble.appendChild(fileLink);
    }

    function appendHumanLocation(
        bubble,
        location
    ) {
        const latitude =
            Number(location.latitude);

        const longitude =
            Number(location.longitude);

        if (
            !Number.isFinite(latitude) ||
            !Number.isFinite(longitude)
        ) {
            return;
        }

        const link =
            document.createElement('a');

        link.href =
            `https://www.google.com/maps?q=${
                encodeURIComponent(latitude)
            },${
                encodeURIComponent(longitude)
            }`;

        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.className =
            'btn btn-outline-primary btn-sm d-block mt-2';

        link.textContent =
            location.name
                ? `📍 ${location.name}`
                : '📍 Buka lokasi';

        bubble.appendChild(link);
    }

    function appendHumanVCards(
        bubble,
        vcards
    ) {
        vcards.forEach(function (vcard) {
            const card =
                document.createElement('div');

            card.className =
                'border rounded p-2 mt-2';

            card.style.whiteSpace = 'pre-wrap';

            card.textContent =
                typeof vcard === 'string'
                    ? vcard
                    : JSON.stringify(
                        vcard,
                        null,
                        2
                    );

            bubble.appendChild(card);
        });
    }

    function showEmptyHistory(
        modalBody
    ) {
        modalBody.innerHTML = `
            <div
                class="
                    text-center
                    text-muted
                    py-5
                "
            >
                Belum ada history chat
                yang dapat ditampilkan.
            </div>
        `;
    }
})();
