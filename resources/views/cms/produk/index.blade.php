@include('partials.header')

<div class="container-fluid mt-4">

    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Produk Jasa</h6>
            {{-- <a href="{{ route('create_produk_jasa') }}" class="btn btn-sm btn-primary">
                Tambah
            </a> --}}
            @if($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal">
                Tambah
            </button>
            @endif
        </div>


        {{-- CARD BODY --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="produkTable" style="width:100%">
                    <thead class="thead-light text-center">
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Kategori Jasa</th>
                            <th scope="col">Client</th>
                            <th scope="col">Status</th>
                            <th scope="col">BUH</th>
                            <th scope="col">Digital Marketer</th>
                            <th scope="col">Advertising</th>
                            <th scope="col">Content Creator Manager</th>
                            <th scope="col">Content Creator</th>

                            @if ($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
                                <th scope="col">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Kosong, akan diisi oleh AJAX DataTable --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <input type="hidden" id="user_id" value="{{ Auth::user()->id }}">

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('produk_create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="divisi" value="{{ $divisi }}">
                    
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Produk Jasa Baru</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Informasi Dasar</h6>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Kategori Jasa <span class="text-danger">*</span></label>
                                    <select name="kategori_jasa_id" class="form-control custom-select" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach ($kategori as $k)
                                            <option value="{{ $k->id }}">{{ $k->category }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" name="client" class="form-control" placeholder="Contoh: Social Media Management" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control custom-select" required>
                                        <option value="1">Ditampilkan</option>
                                        <option value="2">Disembunyikan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Platform & Akses Token</h6>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-body p-3" id="platform-container">
                                <div class="platform-row mb-3">
                                    <div class="input-group">
                                        <select name="platform[]" class="form-control col-md-4">
                                            <option value="">-- Pilih Platform --</option>
                                            <option value="Instagram">Instagram</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="Tiktok">Tiktok</option>
                                        </select>
                                        <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-success add-platform">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-primary mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-primary font-weight-bold mb-0">Assign Team Management</h6>
                        </div>

                        <div class="row">
                            @if(Auth::user()->isAdmin == true)
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-danger">Manager (DMM)</label>
                                <select name="dmm_ids[]" id="select-dmm" class="form-control select2-multiple" multiple data-placeholder="Pilih DMM">
                                    @foreach ($buh as $m)
                                        <option value="{{ $m->id }}">{{ $m->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                                <input type="hidden" name="dmm_ids[]" id="select-dmm-hidden" value="{{ Auth::user()->id }}">
                            @endif

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-primary">Digital Marketing (DM)</label>
                                <select name="dm_ids[]" id="select-dm" class="form-control select2-multiple" multiple data-placeholder="Pilih DM" {{ Auth::user()->role == 'digital_marketing_manager' ? '' : 'disabled' }}>
                                    @foreach ($dm as $d)
                                        @php $parentIdDm = $d->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $d->id }}" data-parent="{{ $parentIdDm }}">{{ $d->email }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-dark">Senior Content Creator (SCC)</label>
                                <select name="scc_ids[]" id="select-scc" class="form-control select2-multiple" multiple data-placeholder="Pilih SCC" disabled>
                                    @foreach ($ads as $s)
                                        @php $parentIScc = $s->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $s->id }}" data-parent="{{ $parentIScc }}">{{ $s->email }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-warning">Content Creator (CC)</label>
                                <select name="cc_ids[]" id="select-cc" class="form-control select2-multiple" multiple data-placeholder="Pilih CC" disabled>
                                    @foreach ($ccm as $c)
                                        @php $parentICc = $c->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $c->id }}" data-parent="{{ $parentICc }}">{{ $c->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold small text-warning">Content Creator (CC)</label>
                                <select name="cc_ids[]" id="select-cc" class="form-control select2-multiple" multiple data-placeholder="Pilih CC" disabled>
                                    @foreach ($cc as $c)
                                        @php $parentICc = $c->userDivisions->first()->parent_id ?? 0; @endphp
                                        <option value="{{ $c->id }}" data-parent="{{ $parentICc }}">{{ $c->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 py-3">
                        <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm">
                            <i class="fas fa-save mr-2"></i>Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditGlobal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="formEditProduk" method="POST">
                    @csrf
                    <div id="modalEditContent"></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDeleteGlobal" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form id="formDeleteProduk" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Hapus Produk Jasa</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body text-center">
                        <p class="mb-2">Apakah kamu yakin ingin menghapus</p>
                        <strong id="deleteTargetName"></strong>?
                        <p class="text-muted mt-2 small">
                            Data yang dihapus tidak dapat dikembalikan dan akses tim terkait akan otomatis diputus.
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- @endforeach --}}
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>

$(document).ready(function() {
    $('#produkTable').DataTable({
        processing: true,
        ajax: window.location.href, // Mengambil data dari method index di Controller
        columns: [
            // 1. Kolom Nomor
            { 
                data: null, 
                className: 'text-center', 
                width: '5%',
                render: (data, type, row, meta) => meta.row + 1 
            },
            
            // 2. Kolom Kategori Jasa
            { data: 'category', width: '10%' },
            
            // 3. Kolom Nama Client/Produk
            { data: 'nama', className: 'font-weight-bold', width: '15%' },
            
            // 4. Kolom Status
            { 
                data: 'status', 
                className: 'text-center',
                width: '10%',
                render: s => {
                    let badge = s == 1 ? 'badge-success' : 'badge-secondary';
                    let text = s == 1 ? 'Ditampilkan' : 'Disembunyikan';
                    return `<span class="badge ${badge}">${text}</span>`;
                }
            },
            
            // 5. Kolom DMM
            { 
                data: 'buh', 
                width: '12%',
                render: data => renderBadges(data, 'badge-primary') 
            },
            
            // 6. Kolom DM
            { 
                data: 'dm', 
                width: '12%',
                render: data => renderBadges(data, 'badge-info') 
            },
            
            // 7. Kolom SCC
            { 
                data: 'ads', 
                width: '12%',
                render: data => renderBadges(data, 'badge-dark') 
            },
            
            // 8. Kolom CC
            { 
                data: 'ccm', 
                width: '12%',
                render: data => renderBadges(data, 'badge-warning text-dark') 
            },
            { 
                data: 'cc', 
                width: '12%',
                render: data => renderBadges(data, 'badge-warning text-dark') 
            },
            
            @if($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
            { 
                data: null,
                className: 'text-center',
                width: '12%',
                orderable: false,
                searchable: false,
                render: r => `
                    <div class="btn-group">
                        <button class="btn btn-sm btn-warning" onclick="editProduk(${r.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduk(${r.id}, '${r.nama}')" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>`
            }
            @endif
        ]
    });

    const roleUser = "{{ $role }}";

    // Fungsi untuk reset dropdown di bawahnya
    function resetAndDisable(targetIds) {
        targetIds.forEach(id => {
            $(`#${id}`).val(null).trigger('change').prop('disabled', true);
        });
    }

    function filterOptions(childId, selectedParentIds) {
        const $childSelect = $(`#${childId}`);

        console.log($childSelect);
        
        // Sembunyikan semua opsi terlebih dahulu
        $childSelect.find('option').each(function() {
            let parentId = $(this).data('parent');
            console.log($(this));
            
            if (!parentId) return;

            if (selectedParentIds && selectedParentIds.includes(parentId.toString())) {
                $(this).show();
            } else {
                $(this).hide();
                $(this).prop('selected', false); // Batalkan pilihan jika tersembunyi
            }
        });

        // Trigger change untuk merantai reset ke level bawahnya
        $childSelect.trigger('change');
    }

    // 1. Logika DMM ke DM
    $('#select-dmm').on('change', function() {
        let selectedDmm = $(this).val();
        if (selectedDmm && selectedDmm.length > 0) {
            $('#select-dm').prop('disabled', false);
            filterOptions('select-dm', selectedDmm);
        } else {
            resetAndDisable(['select-dm', 'select-scc', 'select-cc']);
        }
    });

    // 2. Logika DM ke SCC
    $('#select-dm').on('change', function() {
        let selectedDm = $(this).val();
        if (selectedDm && selectedDm.length > 0) {
            $('#select-scc').prop('disabled', false);
            filterOptions('select-scc', selectedDm);
        } else {
            resetAndDisable(['select-scc', 'select-cc']);
        }
    });

    // 3. Logika SCC ke CC
    $('#select-scc').on('change', function() {
        let selectedScc = $(this).val();
        if (selectedScc && selectedScc.length > 0) {
            $('#select-cc').prop('disabled', false);
            filterOptions('select-cc', selectedScc);
        } else {
            resetAndDisable(['select-cc']);
        }
    });

    // Inisialisasi awal jika login sebagai DMM
    if (roleUser === 'Digital Marketing Manager') {
        $('#select-dm').prop('disabled', false);
    }
    const allPlatforms = ["Tiktok", "Instagram"];

    function updateOptions() {
        // Kita perlu updateOptions secara spesifik per Modal agar tidak bentrok antar modal
        $('.modal').each(function() {
            let $modal = $(this);
            let selectedValues = [];
            
            $modal.find('.platform-select').each(function() {
                if ($(this).val()) {
                    selectedValues.push($(this).val());
                }
            });

            $modal.find('.platform-select').each(function() {
                let currentVal = $(this).val();
                $(this).find('option').each(function() {
                    let optVal = $(this).val();
                    if (optVal !== "" && optVal !== currentVal && selectedValues.includes(optVal)) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });

            // Sembunyikan tombol tambah di modal ini jika platform sudah penuh
            if (selectedValues.length >= allPlatforms.length) {
                $modal.find('.add-platform, .add-platform-edit').hide();
            } else {
                $modal.find('.add-platform, .add-platform-edit').show();
            }
        });
    }

    // Event Klik Tombol Tambah (Dinamis untuk Tambah & Edit)
    $(document).on('click', '.add-platform, .add-platform-edit', function() {
        // 1. Tentukan target container (untuk modal tambah atau edit)
        let targetSelector = $(this).data('target') || '#platform-container';
        let $container = $(targetSelector);

        let platformOptions = allPlatforms.map(p => `<option value="${p}">${p}</option>`).join('');

        let newRow = `
            <div class="platform-row mb-3 mt-2">
                <div class="input-group shadow-sm">
                    <select name="platform[]" class="form-control platform-select col-md-4" required>
                        <option value="">-- Pilih Platform --</option>
                        ${platformOptions}
                    </select>
                    <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-danger remove-platform" title="Hapus baris">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        
        // 4. Masukkan ke dalam container
        $container.append(newRow);

        // 5. Jalankan fungsi filter agar pilihan yang sudah ada di baris lain tersembunyi
        if (typeof updateOptions === "function") {
            updateOptions();
        }
    });

    $(document).on('click', '.add-dm-row, .add-dm-row-edit', function() {
        let targetSelector = $(this).data('target') || '#dm-container';
        let $container = $(targetSelector);
        
        let options = `<option value="">-- Pilih DM --</option>`;
        @foreach ($dm as $d)
            options += `<option value="{{ $d->id }}">{{ $d->email }}</option>`;
        @endforeach

        let newRow = `
            <div class="dm-row mb-3 mt-2">
                <div class="input-group">
                    <select name="dm[]" class="form-control" required>${options}</select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger dm-remove-row">-</button>
                    </div>
                </div>
            </div>`;
        $container.append(newRow);
    });

    // --- Logic untuk Multiple CC ---
    $(document).on('click', '.add-cc-row, .add-cc-row-edit', function() {
        let targetSelector = $(this).data('target') || '#cc-container';
        let $container = $(targetSelector);

        let options = `<option value="">-- Pilih CC --</option>`;
        @foreach ($cc as $c)
            options += `<option value="{{ $c->id }}">{{ $c->email }}</option>`;
        @endforeach

        let newRow = `
            <div class="cc-row mb-3 mt-2">
                <div class="input-group">
                    <select name="cc[]" class="form-control" required>${options}</select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger cc-remove-row">-</button>
                    </div>
                </div>
            </div>`;
        $container.append(newRow);
    });

    // Event Klik Tombol Hapus
    $(document).on('click', '.remove-platform', function() {
        let $row = $(this).closest('.platform-row');
        $row.fadeOut(200, function() {
            $(this).remove();
            if (typeof updateOptions === "function") {
                updateOptions();
            }
        });
    });

    $(document).on('click', '.remove-dm-row', function() {
        $(this).closest('.dm-edit-row').remove();
    });

    $(document).on('click', '.remove-cc-row', function() {
        $(this).closest('.cc-edit-row').remove();
    });

    $(document).on('click', '.dm-remove-row', function() {
        $(this).closest('.dm-row').remove();
    });

    $(document).on('click', '.cc-remove-row', function() {
        $(this).closest('.cc-row').remove();
    });

    // Event saat pilihan berubah
    $(document).on('change', '.platform-select', function() {
        updateOptions();
    });

    // Jalankan sekali saat load untuk inisialisasi modal edit yang sudah ada datanya
    updateOptions();

    $(document).ready(function() {
        let baseUrl = window.location.origin;
        $('#dmm-select').on('change', function() {
            let dmmId = $(this).val();
            if(dmmId) {
                $.get(`${baseUrl}/api/get-dm/${dmmId}`, function(data) {
                    let html = '<option value="">-- Pilih DM --</option>';
                    data.forEach(d => html += `<option value="${d.id}">${d.email}</option>`);
                    $('.dm-staff-select').html(html);
                });
            }else{
                let html = '<option value="">-- Pilih DM --</option>';
                $('.dm-staff-select').html(html);
            }
        });
        
        $(document).on('change', '.dm-staff-select', function() {
            let dmId = $(this).val();
            let $ccSelect = $(this).closest('.row').find('.cc-staff-select'); // Mencari CC di baris yang sama

            if(dmId) {
                $.get(`${baseUrl}/api/get-cc/${dmId}`, function(data) {
                    let html = '<option value="">-- Pilih CC --</option>';
                    data.forEach(c => html += `<option value="${c.id}">${c.email}</option>`);
                    $ccSelect.html(html);
                });
            }
        });

        function loadDmEdit($modal, dmmId) {
            if (!dmmId) return;
            
            $.get(`${baseUrl}/api/get-dm/${dmmId}`, function(data) {
                $modal.find('.dm-staff-select-edit').each(function() {
                    let $select = $(this);
                    let selectedId = $select.data('selected');
                    
                    let html = '<option value="">-- Pilih DM --</option>';
                    data.forEach(d => {
                        let isSelected = (d.id == selectedId) ? 'selected' : '';
                        html += `<option value="${d.id}" ${isSelected}>${d.email}</option>`;
                    });
                    $select.html(html);
                });
            });
        }
        $(document).on('change', '.dm-staff-select-edit', function() {
            let dmId = $(this).val();
            let $modal = $(this).closest('.modal');
            let $ccSelects = $modal.find('.cc-staff-select-edit');

            if (dmId != "") {
                $.get(`${baseUrl}/api/get-cc/${dmId}`, function(data) {
                    let html = '<option value="">-- Pilih CC --</option>';
                    data.forEach(c => {
                        html += `<option value="${c.id}">${c.email}</option>`;
                    });
                    // Update semua dropdown CC di modal tersebut agar opsinya sesuai DM terbaru
                    $ccSelects.each(function() {
                        let selectedId = $(this).data('selected');
                        $(this).html(html);
                        // Jika ada data CC lama, tetap pilih datanya
                        if(selectedId) $(this).val(selectedId);
                    });
                });
            }else{
                let html = '<option value="">-- Pilih CC --</option>';
                $ccSelects.html(html).val("").trigger('change');
            }
        });
        $('.modal').on('shown.bs.modal', function () {
            let $modal = $(this);
            let dmmId = $modal.find('.dmm-select-edit').val();
            let firstDmId = $modal.find('.dm-staff-select-edit').first().val();

            if (dmmId) loadDmEdit($modal, dmmId);
            
            // Jika sudah ada DM terpilih, otomatis load CC-nya juga
            if (firstDmId) {
                $modal.find('.dm-staff-select-edit').first().trigger('change');
            }
        });

        // Handle Hapus Baris
        $(document).on('click', '.remove-row', function() {
            $(this).closest('.input-group').remove();
        });
    });
});

function renderBadges(emails, badgeClass) {
    if (!emails || emails.length === 0) return '<span class="text-muted small">-</span>';
    return emails.map(email => 
        `<small class="badge ${badgeClass} d-block mb-1" style="font-size:10px">${email}</small>`
    ).join('');
}

// Event Handler untuk perubahan dropdown
$(document).on('change', '#edit-select-dmm, #edit-select-dm', function() {
    applyHierarchyFilter();
});

function renderPlatformRowEdit(platform, access, isFirst, productId) {
    const listPlt = ['Instagram', 'Facebook', 'Tiktok'];
    let options = listPlt.map(plt => `<option value="${plt}" ${plt === platform ? 'selected' : ''}>${plt}</option>`).join('');
    
    return `
        <div class="platform-row mb-2">
            <div class="input-group shadow-sm">
                <select name="platform[]" class="form-control col-md-4">${options}</select>
                <input type="text" name="access[]" class="form-control" value="${access || ''}" placeholder="Access Token">
                <div class="input-group-append">
                    <button type="button" class="btn btn-${isFirst ? 'success add-platform-edit' : 'danger remove-platform'}" 
                            data-target="#platform-container-edit-${productId}">
                        <i class="fas fa-${isFirst ? 'plus' : 'minus'}"></i>
                    </button>
                </div>
            </div>
        </div>`;
}
</script>
@include('partials.footer')
