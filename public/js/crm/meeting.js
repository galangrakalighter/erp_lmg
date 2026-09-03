window.CRMRenderers = window.CRMRenderers || {};

(function () {
    'use strict';

    window.CRMRenderers.meeting = function (data, helpers) {
        console.log(data);
        const body = document.getElementById('meetingTableBody');

        if (!body) {
            return;
        }

        if (!Array.isArray(data) || !data.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Tidak ada jadwal meeting pada periode ini.
                    </td>
                </tr>
            `;
            return;
        }

        body.innerHTML = data.map(function (lead, index) {
            const meetingUrl = String(lead.link_meeting || '').trim();
            const meetingLink = /^https?:\/\//i.test(meetingUrl)
                ? `
                    <a
                        href="${helpers.escapeAttribute(meetingUrl)}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-primary btn-sm text-nowrap"
                    >
                        <i class="fas fa-video mr-1"></i>
                        Buka Meeting
                    </a>
                `
                : '<span class="text-muted">-</span>';

                console.log(lead);

            return `
                <tr>
                    <td class="align-middle">${index + 1}</td>
                    <td class="align-middle text-nowrap font-weight-bold">
                        ${helpers.escape(lead.waktu_meeting || '-')}
                    </td>
                    <td class="align-middle text-left">
                        ${helpers.escape(lead.nama || '-')}
                    </td>
                    <td class="align-middle text-left">
                        ${helpers.escape(lead.brand || '-')}
                    </td>
                    <td class="align-middle text-nowrap">
                        ${helpers.whatsapp(lead.phone)}
                    </td>
                    <td class="align-middle text-left">
                        ${helpers.escape(lead.layanan || '-')}
                    </td>
                    <td class="align-middle">${meetingLink}</td>
                </tr>
            `;
        }).join('');
    };
})();
