function escapeProdukHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function editProduk(id) {

    const $modal = $('#modalEditGlobal');
    const $container = $('#modalEditContent');

    showLoadingEditModal($modal, $container);

    const userId = window.produkConfig.userId;
    const folderName = window.location.pathname
        .split('/')
        .filter(Boolean)[0];

    const urlDetail = `${window.produkConfig.detailUrl}/${id}/${userId}/${folderName}`;
    const urlUpdate = `${window.produkConfig.updateUrl}/${id}`;

    $('#formEditProduk')
        .attr('action', urlUpdate);

    $.ajax({

        url: urlDetail,
        type: 'GET',

        success: function (res) {

            if (res.status !== 'success') {
                return;
            }

            $('#modalEditContent')
                .html(buildEditModalHtml(res));

            initializeEditModal();

            $('#modalEditGlobal')
                .modal('show');

        },

        error: function (xhr) {

            showEditError(xhr);

        }

    });

}

function initializeEditModal() {

    initializeEditSelect2();

    setupEditHierarchy();

    setupEditPlatform();

}


function initializeEditSelect2() {
    $('#modalEditGlobal .select2-multiple').each(function () {
        const $select = $(this);

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: $select.data('placeholder'),
            dropdownParent: $('#modalEditGlobal')
        });
    });
}

function buildEditModalHtml(res){
    console.log(res);
    const p = res.produk;
    const opt = res.options;
    const currentTeams = res.current_teams || [];
    const oauthConnections = res.oauth_connections || {};

    let rawPlatformData = p.access_token;

    if (typeof rawPlatformData === 'string') {
        try {
            rawPlatformData = JSON.parse(rawPlatformData);
        } catch (e) {
            rawPlatformData = null;
        }
    }

    let platformData = [];

    if (Array.isArray(rawPlatformData)) {
        // Mendukung format lama: [{ platform: 'Instagram', access: '...' }].
        platformData = rawPlatformData;
    } else if (rawPlatformData && Array.isArray(rawPlatformData.platform)) {
        // Format model saat ini: { platform: [...], access: [...] }.
        platformData = rawPlatformData.platform.map((platform, index) => ({
            platform,
            access: Array.isArray(rawPlatformData.access)
                ? (rawPlatformData.access[index] || '')
                : ''
        }));
    }

    return `
        <div class="modal-header bg-warning text-white py-3">
            <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit Produk Jasa</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body p-4" style="background-color: #f8f9fc;">
            <div class="section-title mb-3 d-flex align-items-center">
                <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                <h6 class="text-warning font-weight-bold mb-0">Informasi Dasar</h6>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="text-dark font-weight-bold small">Kategori Jasa <span class="text-danger">*</span></label>
                        <select name="kategori_jasa_id" class="form-control custom-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            ${(res.kategori || []).map(k => `<option value="${k.id}" ${p.kategori_jasa_id == k.id ? 'selected' : ''}>${k.category}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="text-dark font-weight-bold small">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="${p.nama || ''}" placeholder="Contoh: Social Media Management" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="text-dark font-weight-bold small">Status <span class="text-danger">*</span></label>
                        <input type="hidden" name="divisi" value="${res.division_id || ''}">
                        <select name="status" class="form-control custom-select" required>
                            <option value="1" ${res.division_status == 1 ? 'selected' : ''}>Ditampilkan</option>
                            <option value="2" ${res.division_status != 1 ? 'selected' : ''}>Disembunyikan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section-title mb-3 d-flex align-items-center mt-4">
                <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                <h6 class="text-warning font-weight-bold mb-0">Open Authorization (OAuth 2.0)</h6>
            </div>

            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-body p-4">
                    <p class="mb-3 text-sm text-zinc-500">Hubungkan akun melalui persetujuan resmi platform. Access token dan refresh token dikelola otomatis oleh server.</p>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        ${[
                            { key: 'meta', name: 'Meta', detail: 'Facebook & Instagram', platforms: ['Facebook', 'Instagram'], icon: 'fab fa-facebook' },
                            { key: 'threads', name: 'Threads', detail: 'Threads API', platforms: ['Threads'], icon: 'fas fa-at' },
                            { key: 'tiktok', name: 'TikTok', detail: 'Login Kit & Display API', platforms: ['Tiktok', 'TikTok'], icon: 'fab fa-tiktok' },
                            { key: 'google', name: 'Google', detail: 'Google & YouTube', platforms: ['Google', 'YouTube'], icon: 'fab fa-google' }
                        ].map(provider => {
                            const connection = oauthConnections[provider.key];
                            const connected = Boolean(connection?.connected) || platformData.some(item => provider.platforms.includes(item.platform) && item.access);
                            const ready = provider.key !== 'meta' ? connected : Boolean(connection?.ready);
                            return `
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700"><i class="${provider.icon}"></i></span>
                                        <div class="min-w-0">
                                            <p class="mb-0 text-sm font-bold text-zinc-900">${provider.name}</p>
                                            <p class="mb-0 text-[11px] text-zinc-500">${provider.detail}</p>
                                            <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ${ready ? 'bg-emerald-50 text-emerald-700' : connected ? 'bg-amber-50 text-amber-700' : 'bg-zinc-100 text-zinc-500'}">${ready ? 'Siap mengambil data' : connected ? 'Terhubung, aset belum tersedia' : 'Belum terhubung'}</span>
                                        </div>
                                    </div>
                                    <button type="button" data-oauth-provider="${provider.key}" data-client-id="${p.id}" class="shrink-0 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 hover:border-zinc-950 hover:bg-zinc-950 hover:text-white">${connected ? 'Hubungkan ulang' : 'Hubungkan'}</button>
                                </div>`;
                        }).join('')}
                    </div>
                    ${oauthConnections.meta?.connected ? `
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4" data-meta-assets-panel>
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                                <div><p class="mb-0 text-sm font-bold text-emerald-900">Terhubung sebagai ${escapeProdukHtml(oauthConnections.meta.provider_user_name || 'Pengguna Meta')}</p><p class="mb-0 text-[11px] text-emerald-700">Pilih aset yang digunakan untuk client ini.</p></div>
                                <button type="button" data-meta-disconnect data-client-id="${p.id}" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700">Putuskan</button>
                            </div>
                            <div class="grid gap-3 md:grid-cols-3">
                                <label class="text-xs font-semibold text-zinc-700">Facebook Page
                                    <select data-meta-page class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Pilih Facebook Page</option>
                                        ${(oauthConnections.meta.pages || []).map(page => `<option value="${escapeProdukHtml(page.id)}" ${String(oauthConnections.meta.selected_assets?.page_id || '') === String(page.id) ? 'selected' : ''}>${escapeProdukHtml(page.name)}${page.instagram?.username ? ` · @${escapeProdukHtml(page.instagram.username)}` : ''}</option>`).join('')}
                                    </select>
                                </label>
                                <label class="text-xs font-semibold text-zinc-700">Instagram Page
                                    <select data-meta-instagram-account class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Pilih Instagram Page</option>
                                        ${(oauthConnections.meta.instagram_accounts || []).map(account => `<option value="${escapeProdukHtml(account.id)}" ${String(oauthConnections.meta.selected_assets?.instagram_account_id || '') === String(account.id) ? 'selected' : ''}>${account.username ? `@${escapeProdukHtml(account.username)}` : escapeProdukHtml(account.name || 'Instagram')}${account.page_name ? ` · ${escapeProdukHtml(account.page_name)}` : ''}</option>`).join('')}
                                    </select>
                                </label>
                                <label class="text-xs font-semibold text-zinc-700">Akun Iklan
                                    <select data-meta-ad-account class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm">
                                        <option value="">Pilih akun iklan</option>
                                        ${(oauthConnections.meta.ad_accounts || []).map(account => `<option value="${escapeProdukHtml(account.id)}" ${String(oauthConnections.meta.selected_assets?.ad_account_id || '') === String(account.id) ? 'selected' : ''}>${escapeProdukHtml(account.name)}${account.account_id ? ` · ${escapeProdukHtml(account.account_id)}` : ''}</option>`).join('')}
                                    </select>
                                </label>
                            </div>
                            <button type="button" data-meta-save-assets data-client-id="${p.id}" class="mt-3 rounded-lg bg-zinc-950 px-4 py-2 text-xs font-semibold text-white">Simpan Pilihan Aset</button>
                        </div>
                    ` : ''}
                </div>
            </div>

            <div class="section-title mb-3 d-flex align-items-center mt-4">
                <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                <h6 class="text-warning font-weight-bold mb-0">Assign Team Management</h6>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-danger">Busines Unit Head (BUH)</label>
                    <select name="buh_ids[]" id="edit-select-buh" class="form-control select2-multiple" multiple data-placeholder="Pilih BUH">
                        ${(opt.buh || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-warning">Digital Marketer (DM)</label>
                    <select name="dm_ids[]" id="edit-select-dm" class="form-control select2-multiple" multiple data-placeholder="Pilih DM">
                        ${(opt.dm || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" data-parent="${u.parent != null ? u.parent : ''}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-dark">Advertising (ADS)</label>
                    <select name="ads_ids[]" id="edit-select-ads" class="form-control select2-multiple" multiple data-placeholder="Pilih ADS">
                        ${(opt.ads || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" data-parent="${u.parent != null ? u.parent : ''}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                        }).join('')}
                    </select>
                </div>
            </div>
        </div>

        <div class="modal-footer bg-white border-0 py-3">
            <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning px-5 font-weight-bold shadow-sm">
                <i class="fas fa-save mr-2"></i>Update Produk
            </button>
        </div>
    `;

}

function setupEditHierarchyLegacy(){

    const buh = $('#edit-select-buh').val() || [];
    const ccm = $('#edit-select-ccm').val() || [];

    // default disable
    $('#edit-select-dm').prop('disabled', true);
    $('#edit-select-ads').prop('disabled', true);
    $('#edit-select-ccm').prop('disabled', true);
    $('#edit-select-cc').prop('disabled', true);

    if(buh.length){

        $('#edit-select-dm').prop('disabled', false);
        $('#edit-select-ads').prop('disabled', false);
        $('#edit-select-ccm').prop('disabled', false);

        filterSelectOptions(
            buh,
            '#edit-select-dm',
            '#edit-select-ads',
            '#edit-select-ccm'
        );

    }

    if(ccm.length){

        $('#edit-select-cc').prop('disabled', false);

        filterSelectOptions(
            ccm,
            '#edit-select-cc'
        );

    }

    bindEditHierarchyEvent();

}

function filterSelectOptions(parentIds, ...selectors){

    selectors.forEach(function(selector){

        const $select = $(selector);

        $select.find('option').each(function(){

            const parent = $(this).data('parent');

            if(parent == undefined){
                return;
            }

            if(parentIds.includes(parent.toString())){

                $(this).prop('disabled', false);

            }else{

                $(this)
                    .prop('selected', false)
                    .prop('disabled', true);

            }

        });

        $select.trigger('change.select2');

    });

}

function bindEditHierarchyEvent(){

    // $('#edit-select-buh').off('change').on('change', function(){

    //     const ids = $(this).val() || [];

    //     if(ids.length){
    //         $('#edit-select-dm').prop('disabled', false);
    //         $('#edit-select-ads').prop('disabled', false);
    //         $('#edit-select-ccm').prop('disabled', false);

    //         filterSelectOptions(
    //             ids,
    //             '#edit-select-dm',
    //             '#edit-select-ads',
    //             '#edit-select-ccm'
    //         );

    //     }else{

            
    //         // $(this).val(null);
    //         console.log($(this).val())

    //         $('#edit-select-dm')
    //             .val(null)
    //             .prop('disabled', true)
    //             .trigger('change');

    //         $('#edit-select-ads')
    //             .val(null)
    //             .prop('disabled', true)
    //             .trigger('change');

    //         $('#edit-select-ccm')
    //             .val(null)
    //             .prop('disabled', true)
    //             .trigger('change');

    //         $('#edit-select-cc')
    //             .val(null)
    //             .prop('disabled', true)
    //             .trigger('change');

    //     }

    // });

    // $('#edit-select-ccm').off('change').on('change', function(){

    //     const ids = $(this).val() || [];

    //     if(ids.length){

    //         $('#edit-select-cc')
    //             .prop('disabled', false);

    //         filterSelectOptions(
    //             ids,
    //             '#edit-select-cc'
    //         );

    //     }else{

    //         $('#edit-select-cc')
    //             .val(null)
    //             .prop('disabled', true)
    //             .trigger('change');

    //     }

    // });

    $('#edit-select-buh').change(function(){

        let ids=$(this).val();

        if(ids && ids.length){

            $('#edit-select-dm').prop('disabled',false);

            $('#edit-select-ads').prop('disabled',false);

            $('#edit-select-ccm').prop('disabled',false);

            filterOptions('edit-select-dm',ids);

            filterOptions('edit-select-ads',ids);

            filterOptions('edit-select-ccm',ids);

        }else{

            resetSelectEdit([
                'edit-select-dm',
                'edit-select-ads',
                'edit-select-ccm',
                'edit-select-cc'
            ]);

        }

    });

    $('#edit-select-ccm').change(function(){

        let ids=$(this).val();

        if(ids && ids.length){

            $('#edit-select-cc').prop('disabled',false);

            filterOptions('edit-select-cc',ids);

        }else{

            resetSelectEdit(['edit-select-cc']);

        }

    });

}

// Versi hierarki yang konsisten untuk semua browser/perangkat.
// Deklarasi ini menggantikan setupEditHierarchy lama di atas.
function setupEditHierarchy() {
    const $buh = $('#edit-select-buh');
    const $dm = $('#edit-select-dm');
    const $ads = $('#edit-select-ads');

    const normalizeIds = function (ids) {
        return (ids || [])
            .filter(function (id) {
                return id !== null && id !== undefined && id !== '';
            })
            .map(function (id) {
                return String(id);
            });
    };

    const refreshSelect2 = function ($select) {
        $select.trigger('change.select2');
    };

    const disableAndClear = function ($select) {
        $select
            .val(null)
            .prop('disabled', true);

        refreshSelect2($select);
    };

    const filterByParent = function ($select, parentIds) {
        const allowedParentIds = normalizeIds(parentIds);

        $select.find('option').each(function () {
            const $option = $(this);
            const parentId = $option.attr('data-parent');

            // Data tanpa parent tetap ditampilkan agar perbedaan data API
            // tidak menyebabkan seluruh pilihan terkunci.
            if (parentId === undefined || parentId === null || parentId === '') {
                $option.prop('disabled', false);
                return;
            }

            const allowed = allowedParentIds.indexOf(String(parentId)) !== -1;

            $option.prop('disabled', !allowed);

            if (!allowed) {
                $option.prop('selected', false);
            }
        });

        refreshSelect2($select);
    };

    const refreshFromBuh = function () {
        const buhIds = normalizeIds($buh.val());

        if (buhIds.length === 0) {
            disableAndClear($dm);
            disableAndClear($ads);
            return;
        }

        $dm.prop('disabled', false);
        $ads.prop('disabled', false);

        filterByParent($dm, buhIds);
        filterByParent($ads, buhIds);
    };

    $buh
        .off('change.editHierarchy')
        .on('change.editHierarchy', refreshFromBuh);

    // Terapkan kondisi awal berdasarkan data selected dari API.
    refreshFromBuh();
}

function setupEditPlatform(){

    $(document)
        .off('click', '.add-platform-edit')
        .on('click', '.add-platform-edit', function(){

            $('#platform-container-edit').append(`
                <div class="platform-row mb-3">
                    <div class="input-group">

                        <select name="platform[]" class="form-control col-md-4">
                            <option value="">-- Pilih Platform --</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Threads">Threads</option>
                            <option value="Tiktok">Tiktok</option>
                        </select>

                        <input
                            type="text"
                            name="access[]"
                            class="form-control"
                            placeholder="Input Access Token">

                        <div class="input-group-append">
                            <button
                                type="button"
                                class="btn btn-danger remove-platform-edit">
                                -
                            </button>
                        </div>

                    </div>
                </div>
            `);

        });

    $(document)
        .off('click', '.remove-platform-edit')
        .on('click', '.remove-platform-edit', function(){

            $(this)
                .closest('.platform-row')
                .remove();

        });

}


function showEditError(xhr){

    $('#modalEditContent').html(`
        <div class="modal-body">

            <div class="alert alert-danger">

                <strong>Gagal mengambil data.</strong>

                <hr>

                ${xhr.status} - ${xhr.statusText}

            </div>

        </div>
    `);

}

function restoreEditSelectedValue(){
    console.log("masuk restore");
    $('#edit-select-buh').trigger('change.select2');
    $('#edit-select-dm').trigger('change.select2');
    $('#edit-select-ads').trigger('change.select2');

}

function showLoadingEditModal($modal, $container){

    $container.html(`
        <div class="text-center p-5">

            <div class="spinner-border text-warning"></div>

            <p class="mt-3 mb-0">
                Memuat data...
            </p>

        </div>
    `);

    $modal.modal('show');

}

function resetSelectEdit(ids){

    ids.forEach(function(id){

        $('#'+id)
            .val(null)
            .trigger('change')
            .prop('disabled',true);

    });

}
