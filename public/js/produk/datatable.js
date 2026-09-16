function initClientTable() {

    const columns = [
        {
            data: null,
            className: 'text-center',
            render: function (data, type, row, meta) {
                return meta.row + 1;
            }
        },
        {
            data: 'category'
        },
        {
            data: 'nama',
            className: 'font-weight-bold'
        },
        {
            data: 'status',
            className: 'text-center',
            render: renderStatus
        },
        {
            data: 'buh',
            render: data => renderBadges(data, 'badge-primary')
        },
        {
            data: 'dm',
            render: data => renderBadges(data, 'badge-info')
        },
        {
            data: 'ads',
            render: data => renderBadges(data, 'badge-dark')
        },
        {
            data: 'ccm',
            render: data => renderBadges(data, 'badge-warning text-dark')
        },
        {
            data: 'cc',
            render: data => renderBadges(data, 'badge-warning text-dark')
        }
    ];

    const allowAction = [
        'Digital Marketer',
        'Busines Unit Head',
        'Super Admin'
    ].includes(window.produkConfig.role);

    if (allowAction) {
        columns.push({
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-center',
            render: renderActionButton
        });
    }

    renderNativeClientTable({
        ajax: {
            url: window.location.href,
            dataSrc: function(json){
                console.log(json);
                return json.data;
            }
        },
        columns: columns
    });

}

function renderStatus(status) {

    if (parseInt(status) === 1) {
        return '<span class="badge badge-success">Ditampilkan</span>';
    }

    return '<span class="badge badge-secondary">Disembunyikan</span>';

}

function renderActionButton(data, type, row) {

    return `
        <div class="btn-group">

            <button
                type="button"
                class="btn btn-warning btn-sm"
                onclick="editProduk(${row.id})">

                <i class="fas fa-edit"></i>

            </button>

            <button
                type="button"
                class="btn btn-danger btn-sm"
                onclick="deleteProduk(${row.id}, '${escapeHtml(row.nama)}')">

                <i class="fas fa-trash"></i>

            </button>

        </div>
    `;

}

function renderBadges(data, badgeClass) {

    if (!Array.isArray(data) || data.length === 0) {
        return '<span class="text-muted small">-</span>';
    }

    return data.map(function(item){

        return `
            <small
                class="badge ${badgeClass} d-block mb-1"
                style="font-size:10px">

                ${item}

            </small>
        `;

    }).join('');

}

function escapeHtml(text) {

    if (!text) return '';

    return text
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;');

}
