window.CRMRenderers =
    window.CRMRenderers || {};

(function () {
    'use strict';

    const detailTexts = new Map();
    let detailCounter = 0;

    /**
     * Membuat tampilan singkat untuk teks yang lebih dari 6 kata.
     * Teks lengkap disimpan dalam Map, bukan data attribute,
     * sehingga aman untuk teks panjang, tanda kutip, dan baris baru.
     */
    function longText(value, title, helpers) {
        const originalValue =
            value === null ||
            value === undefined ||
            String(value).trim() === ''
                ? '-'
                : String(value).trim();

        const words = originalValue
            .split(/\s+/)
            .filter(Boolean);

        if (
            originalValue === '-' ||
            words.length <= 6
        ) {
            return `
                <span>
                    ${helpers.escape(originalValue)}
                </span>
            `;
        }

        const key = `ai-detail-${++detailCounter}`;
        const preview = words
            .slice(0, 6)
            .join(' ');

        detailTexts.set(key, {
            title: title || 'Detail Data',
            content: originalValue,
        });

        return `
            <div
                style="
                    min-width: 150px;
                    max-width: 230px;
                "
            >
                <div
                    title="${
                        helpers.escapeAttribute(
                            originalValue
                        )
                    }"
                    style="
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    "
                >
                    ${helpers.escape(preview)}...
                </div>

                <button
                    type="button"
                    class="
                        btn
                        btn-link
                        btn-sm
                        crm-show-detail
                        p-0
                        mt-1
                    "
                    data-detail-key="${
                        helpers.escapeAttribute(key)
                    }"
                    style="font-size: 0.75rem"
                >
                    <i class="fas fa-eye mr-1"></i>
                    Selengkapnya
                </button>
            </div>
        `;
    }

    /**
     * Merapikan sosial media yang dapat berupa JSON string,
     * object, array, ataupun teks biasa.
     */
    function normalizeSocialMedia(value) {
        const labels = {
            instagram: 'Instagram',
            ig: 'Instagram',
            facebook: 'Facebook',
            fb: 'Facebook',
            shopee: 'Shopee',
            tiktok: 'TikTok',
            tokopedia: 'Tokopedia',
            lazada: 'Lazada',
            youtube: 'YouTube',
            linkedin: 'LinkedIn',
            twitter: 'Twitter',
            x: 'X',
            website: 'Website',
            whatsapp: 'WhatsApp',
            wa: 'WhatsApp',
        };

        function isEmpty(item) {
            if (
                item === null ||
                item === undefined ||
                item === false
            ) {
                return true;
            }

            if (typeof item === 'string') {
                return [
                    '',
                    '-',
                    'null',
                    'undefined',
                    '[]',
                    '{}',
                ].includes(
                    item.trim().toLowerCase()
                );
            }

            if (Array.isArray(item)) {
                return item.length === 0;
            }

            if (typeof item === 'object') {
                return Object.keys(item)
                    .length === 0;
            }

            return false;
        }

        function cleanText(text) {
            if (isEmpty(text)) {
                return '';
            }

            return String(text)
                .replace(/\r\n?/g, '\n')
                .replace(/\s*[,;|]\s*/g, ' • ')
                .replace(/[ \t]+/g, ' ')
                .replace(/(?:\s*•\s*){2,}/g, ' • ')
                .trim();
        }

        function getLabel(key) {
            const normalized = String(key)
                .trim()
                .toLowerCase();

            return labels[normalized] ||
                normalized
                    .replace(/[_-]+/g, ' ')
                    .replace(/\b\w/g, function (letter) {
                        return letter.toUpperCase();
                    });
        }

        function normalize(currentValue) {
            if (isEmpty(currentValue)) {
                return '';
            }

            if (typeof currentValue === 'string') {
                const text = currentValue.trim();

                if (
                    (text.startsWith('{') &&
                        text.endsWith('}')) ||
                    (text.startsWith('[') &&
                        text.endsWith(']'))
                ) {
                    try {
                        return normalize(
                            JSON.parse(text)
                        );
                    } catch (error) {
                        // Bukan JSON valid; tampilkan sebagai teks.
                    }
                }

                return cleanText(text);
            }

            if (Array.isArray(currentValue)) {
                return [...new Set(
                    currentValue
                        .map(normalize)
                        .filter(Boolean)
                )].join(' • ');
            }

            if (typeof currentValue === 'object') {
                const platform =
                    currentValue.platform ||
                    currentValue.type ||
                    currentValue.label;

                const account =
                    currentValue.url ||
                    currentValue.link ||
                    currentValue.username ||
                    currentValue.handle ||
                    currentValue.account ||
                    currentValue.value;

                if (
                    !isEmpty(platform) &&
                    !isEmpty(account)
                ) {
                    return `${getLabel(platform)}: ${cleanText(account)}`;
                }

                const rows = Object.entries(
                    currentValue
                )
                    .map(function ([key, item]) {
                        const normalizedItem =
                            normalize(item);

                        if (!normalizedItem) {
                            return '';
                        }

                        return `${getLabel(key)}: ${normalizedItem}`;
                    })
                    .filter(Boolean);

                return [...new Set(rows)]
                    .join(' • ');
            }

            return cleanText(currentValue);
        }

        return normalize(value) ||
            'Belum ada sosial media';
    }

    function socialMediaCell(value, helpers) {
        const socialMedia =
            normalizeSocialMedia(value);

        if (
            socialMedia ===
            'Belum ada sosial media'
        ) {
            return `
                <span
                    class="
                        badge
                        badge-light
                        text-muted
                        border
                    "
                    title="Belum ada sosial media"
                >
                    <i
                        class="
                            fas
                            fa-minus-circle
                            mr-1
                        "
                    ></i>
                    Belum ada
                </span>
            `;
        }

        return longText(
            socialMedia,
            'Sosial Media',
            helpers
        );
    }

    window.CRMRenderers.ai = function (
        data,
        helpers
    ) {
        const body =
            document.getElementById(
                'aiTableBody'
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
                        colspan="17"
                        class="
                            text-center
                            text-muted
                            py-4
                        "
                    >
                        Tidak ada data AI pada
                        filter ini.
                    </td>
                </tr>
            `;

            return;
        }

        body.innerHTML = data
            .map((lead, index) => {
                const leadId =
                    helpers.escapeAttribute(
                        lead.id
                    );

                const leadName =
                    helpers.escapeAttribute(
                        lead.nama || 'No Name'
                    );

                const leadPhone =
                    helpers.escapeAttribute(
                        lead.phone || ''
                    );

                return `
                    <tr>
                        <td class="align-middle">
                            ${index + 1}
                        </td>

                        <td
                            class="
                                text-nowrap
                                align-middle
                            "
                        >
                            ${longText(
                                lead.chat_terbaru,
                                'Chat Terbaru',
                                helpers
                            )}
                        </td>

                        <td
                            class="
                                text-nowrap
                                align-middle
                            "
                        >
                            ${longText(
                                lead.created_at,
                                'Pertama Kali Chat',
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
                                lead.nama ||
                                    'No Name',
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
                                lead.kategori,
                                'Kategori Lead',
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
                                lead.email,
                                'Email Client',
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
                                lead.brand ||
                                    lead.nama_usaha,
                                'Nama Usaha',
                                helpers
                            )}
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

                        <td
                            class="
                                text-left
                                align-middle
                            "
                        >
                            ${longText(
                                lead.kendala,
                                'Kendala',
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

                        <td
                            class="
                                text-left
                                align-middle
                            "
                        >
                            ${socialMediaCell(
                                lead.sosial_media,
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
                                lead.kesimpulan,
                                'Kesimpulan',
                                helpers
                            )}
                        </td>

                        <td class="align-middle">
                            ${helpers.prospectBadge(
                                lead.prospek
                            )}
                        </td>

                        <td class="align-middle">
                            ${helpers.statusBadge(
                                lead.status_label || '-'
                            )}
                        </td>

                        <td
                            class="
                                text-nowrap
                                align-middle
                            "
                        >
                            ${longText(
                                lead.meeting,
                                'Jadwal Meeting',
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
                                    crm-chat-history
                                "
                                data-id="${leadId}"
                                data-name="${leadName}"
                                data-phone="${leadPhone}"
                                title="History Chat"
                            >
                                <i
                                    class="
                                        fab
                                        fa-whatsapp
                                    "
                                ></i>
                            </button>

                            <button
                                type="button"
                                class="
                                    btn
                                    btn-warning
                                    btn-sm
                                    edit-lead
                                "
                                data-id="${leadId}"
                                data-status="${
                                    helpers.escapeAttribute(
                                        lead.status || ''
                                    )
                                }"
                                title="Edit"
                            >
                                <i
                                    class="
                                        fas
                                        fa-edit
                                    "
                                ></i>
                            </button>

                            <button
                                type="button"
                                class="
                                    btn
                                    btn-danger
                                    btn-sm
                                    delete-lead
                                "
                                data-id="${leadId}"
                                title="Hapus"
                            >
                                <i
                                    class="
                                        fas
                                        fa-trash
                                    "
                                ></i>
                            </button>
                        </td>
                    </tr>
                `;
            })
            .join('');
    };

    /**
     * Menangani klik Selengkapnya.
     */
    document.addEventListener(
        'click',
        function (event) {
            console.log("di klik");
            const button =
                event.target.closest(
                    '.crm-show-detail'
                );

            if (!button) {
                return;
            }

            const detail = detailTexts.get(
                button.dataset.detailKey
            );

            if (!detail) {
                return;
            }

            const title =
                document.getElementById(
                    'crmTextDetailModalLabel'
                );

            const content =
                document.getElementById(
                    'crmTextDetailContent'
                );

            if (title) {
                title.textContent =
                    detail.title;
            }

            if (content) {
                content.textContent =
                    detail.content;
            }

            $('#crmTextDetailModal')
                .modal('show');
        }
    );

    /**
     * Menangani tombol History Chat.
     */
    document.addEventListener(
        'click',
        function (event) {
            const button =
                event.target.closest(
                    '.crm-chat-history'
                );

            if (!button) {
                return;
            }

            loadChatHistory(
                button.dataset.id,
                button.dataset.name,
                button.dataset.phone
            );
        }
    );

    async function loadChatHistory(
        leadId,
        leadName,
        phone
    ) {
        const app =
            document.getElementById(
                'crmApp'
            );

        const title =
            document.getElementById(
                'crmChatHistoryModalLabel'
            );

        const phoneText =
            document.getElementById(
                'crmChatHistoryPhone'
            );

        const modalBody =
            document.getElementById(
                'crmChatHistoryBody'
            );

        const whatsappLink =
            document.getElementById(
                'crmOpenWhatsApp'
            );

        if (!app || !modalBody) {
            return;
        }

        const historyBaseUrl =
            app.dataset.historyUrl ||
            '/api/crm/history';

        title.textContent =
            `History Chat: ${
                leadName || 'Client'
            }`;

        phoneText.textContent =
            phone
                ? `Nomor: ${phone}`
                : 'Nomor tidak tersedia';

        if (phone) {
            const cleanPhone =
                String(phone).replace(
                    /\D/g,
                    ''
                );

            whatsappLink.href =
                `https://wa.me/${cleanPhone}`;

            whatsappLink.style.display =
                'inline-block';
        } else {
            whatsappLink.href = '#';
            whatsappLink.style.display =
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
                        fas
                        fa-spinner
                        fa-spin
                        fa-2x
                        text-success
                        mb-3
                    "
                ></i>

                <div>
                    Mengambil riwayat chat...
                </div>
            </div>
        `;

        $('#crmChatHistoryModal')
            .modal('show');

        try {
            const response = await fetch(
                `${historyBaseUrl}/${
                    encodeURIComponent(leadId)
                }`,
                {
                    method: 'GET',
                    headers: {
                        Accept:
                            'application/json',
                    },
                }
            );

            const result =
                await response.json();

            console.log(result);

            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    'Gagal mengambil history chat.'
                );
            }

            renderChatHistory(
                result.chats,
                leadName,
                modalBody
            );
        } catch (error) {
            modalBody.innerHTML = '';

            const wrapper =
                document.createElement('div');

            wrapper.className =
                'text-center text-danger py-5';

            const icon =
                document.createElement('i');

            icon.className =
                'fas fa-exclamation-triangle fa-2x mb-3';

            const message =
                document.createElement('div');

            message.textContent =
                error.message ||
                'Gagal mengambil history chat.';

            wrapper.appendChild(icon);
            wrapper.appendChild(message);
            modalBody.appendChild(wrapper);
        }
    }

    function isDisplayableChatMessage(chat) {
        if (
            !chat ||
            typeof chat !== 'object'
        ) {
            return false;
        }

        if (
            chat.sender !== 'me' &&
            chat.sender !== 'client'
        ) {
            return false;
        }

        if (
            typeof chat.message !== 'string'
        ) {
            return false;
        }

        const content =
            chat.message.trim();

        if (!content) {
            return false;
        }

        const blockedValues = [
            '[]',
            '[ ]',
            '{}',
            '{ }',
            'null',
            'undefined',
            'sedang mencari jadwal',
        ];

        if (
            blockedValues.includes(
                content.toLowerCase()
            )
        ) {
            return false;
        }

        /*
        * Jika pesan merupakan JSON array/object,
        * jangan tampilkan.
        */
        try {
            const decoded =
                JSON.parse(content);

            if (
                decoded !== null &&
                typeof decoded === 'object'
            ) {
                return false;
            }
        } catch (error) {
            /*
            * Bukan JSON valid.
            * Pesan masih mungkin berupa teks biasa.
            */
        }

        /*
        * Pengaman untuk JSON rusak atau terpotong.
        */
        const looksLikeJsonArray =
            content.startsWith('[') &&
            content.endsWith(']');

        const looksLikeJsonObject =
            content.startsWith('{') &&
            content.endsWith('}');

        if (
            looksLikeJsonArray ||
            looksLikeJsonObject
        ) {
            return false;
        }

        return true;
    }

    function renderChatHistory(
        chats,
        leadName,
        modalBody
    ) {
        modalBody.innerHTML = '';

        const displayableChats =
            Array.isArray(chats)
                ? chats.filter(
                    isDisplayableChatMessage
                )
                : [];

        if (!displayableChats.length) {
            const emptyWrapper =
                document.createElement('div');

            emptyWrapper.className =
                'text-center text-muted py-5';

            const emptyIcon =
                document.createElement('i');

            emptyIcon.className =
                'fas fa-comments fa-2x mb-3';

            const emptyMessage =
                document.createElement('div');

            emptyMessage.textContent =
                'Belum ada riwayat chat yang dapat ditampilkan.';

            emptyWrapper.appendChild(
                emptyIcon
            );

            emptyWrapper.appendChild(
                emptyMessage
            );

            modalBody.appendChild(
                emptyWrapper
            );

            return;
        }

        displayableChats.forEach(
            function (chat) {
                const isAdmin =
                    chat.sender === 'me';

                const row =
                    document.createElement(
                        'div'
                    );

                row.className =
                    `d-flex mb-3 ${
                        isAdmin
                            ? 'justify-content-end'
                            : 'justify-content-start'
                    }`;

                const bubble =
                    document.createElement(
                        'div'
                    );

                bubble.className =
                    isAdmin
                        ? 'bg-success text-white rounded p-3 shadow-sm'
                        : 'bg-white border rounded p-3 shadow-sm';

                bubble.style.maxWidth =
                    '80%';

                bubble.style.whiteSpace =
                    'pre-wrap';

                bubble.style.overflowWrap =
                    'anywhere';

                const sender =
                    document.createElement(
                        'div'
                    );

                sender.className =
                    'font-weight-bold small mb-1';

                sender.textContent =
                    isAdmin
                        ? 'AI/Admin'
                        : leadName ||
                        'Client';

                const message =
                    document.createElement(
                        'div'
                    );

                message.textContent =
                    chat.message.trim();

                const time =
                    document.createElement(
                        'small'
                    );

                time.className =
                    `d-block mt-2 ${
                        isAdmin
                            ? 'text-light'
                            : 'text-muted'
                    }`;

                time.textContent =
                    chat.created_at || '-';

                bubble.appendChild(sender);
                bubble.appendChild(message);
                bubble.appendChild(time);

                row.appendChild(bubble);
                modalBody.appendChild(row);
            }
        );

        modalBody.scrollTop =
            modalBody.scrollHeight;
    }
})();
