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

    $('.select2-multiple').select2({

        theme:'bootstrap4',

        width:'100%',

        placeholder:function(){

            return $(this).data('placeholder');

        }

    });
}

function buildEditModalHtml(res){
    const p = res.produk;
    const opt = res.options;
    const isAdmin = res.is_admin;
    const currentTeams = res.current_teams || [];

    let platformData = [];

    try{
        platformData = JSON.parse(p.access_token) || [];
    }catch(e){
        platformData = [];
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
                        <select name="status" class="form-control custom-select" required>
                            <option value="1" ${p.status == 1 ? 'selected' : ''}>Ditampilkan</option>
                            <option value="2" ${p.status == 2 ? 'selected' : ''}>Disembunyikan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section-title mb-3 d-flex align-items-center mt-4">
                <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                <h6 class="text-warning font-weight-bold mb-0">Platform & Akses Token</h6>
            </div>

            <div class="card border-0 shadow-sm rounded-lg mb-4">
                <div class="card-body p-3" id="platform-container-edit">
                    ${platformData.length > 0 ? platformData.map((item, index) => `
                        <div class="platform-row mb-3">
                            <div class="input-group">
                                <select name="platform[]" class="form-control col-md-4">
                                    <option value="Instagram" ${item.platform == 'Instagram' ? 'selected' : ''}>Instagram</option>
                                    <option value="Facebook" ${item.platform == 'Facebook' ? 'selected' : ''}>Facebook</option>
                                    <option value="Tiktok" ${item.platform == 'Tiktok' ? 'selected' : ''}>Tiktok</option>
                                </select>
                                <input type="text" name="access[]" class="form-control" value="${item.access || ''}" placeholder="Input Access Token">
                                <div class="input-group-append">
                                    ${index === 0 ? 
                                        `<button type="button" class="btn btn-success add-platform-edit">+</button>` : 
                                        `<button type="button" class="btn btn-danger remove-platform-edit">-</button>`
                                    }
                                </div>
                            </div>
                        </div>
                    `).join('') : `
                        <div class="platform-row mb-3">
                            <div class="input-group">
                                <select name="platform[]" class="form-control col-md-4">
                                    <option value="Instagram">Instagram</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Tiktok">Tiktok</option>
                                </select>
                                <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success add-platform-edit">+</button>
                                </div>
                            </div>
                        </div>
                    `}
                </div>
            </div>

            <div class="section-title mb-3 d-flex align-items-center mt-4">
                <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                <h6 class="text-warning font-weight-bold mb-0">Assign Team Management</h6>
            </div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-danger">Busines Unit Head (BUH)</label>
                    ${isAdmin ? `
                        <select name="buh_ids[]" id="edit-select-buh" class="form-control select2-multiple" multiple data-placeholder="Pilih BUH">
                            ${(opt.buh || []).map(u => {
                                const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                                return `<option value="${u.id}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                            }).join('')}
                        </select>
                    ` : `
                        <input type="hidden" name="buh_ids[]" value="${p.user_id}">
                        <div class="p-2 border rounded bg-white shadow-sm">
                            ${(opt.buh || []).filter(u => currentTeams.some(t => (t.id || t) == u.id)).map(u => `<span class="badge badge-danger m-1">${u.name}</span>`).join('') || '<small class="text-muted">No DMM</small>'}
                        </div>
                    `}
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-warning">Digital Marketer (DM)</label>
                    <select name="dm_ids[]" id="edit-select-dm" class="form-control select2-multiple" multiple data-placeholder="Pilih DM" disabled>
                        ${(opt.dm || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" data-parent="${u.parent}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-dark">Advertising (ADS)</label>
                    <select name="ads_ids[]" id="edit-select-ads" class="form-control select2-multiple" multiple data-placeholder="Pilih ADS" disabled>
                        ${(opt.ads || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" data-parent="${u.parent}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-warning">Content Creator Manager (CCM)</label>
                    <select name="ccm_ids[]" id="edit-select-ccm" class="form-control select2-multiple" multiple data-placeholder="Pilih CCM" disabled>
                        ${(opt.ccm || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" data-parent="${u.parent}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small text-warning">Content Creator (CC)</label>
                    <select name="cc_ids[]" id="edit-select-cc" class="form-control select2-multiple" multiple data-placeholder="Pilih CC" disabled>
                        ${(opt.cc || []).map(u => {
                            const isSelected = currentTeams.some(t => (t.id || t) == u.id);
                            return `<option value="${u.id}" data-parent="${u.parent}" ${isSelected ? 'selected' : ''}>${u.name}</option>`;
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

function setupEditHierarchy(){

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
    $('#edit-select-ccm').trigger('change.select2');
    $('#edit-select-cc').trigger('change.select2');

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