@include('partials.header')

<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Manajemen Hierarki User</h6>
            <button type="button" class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah User
            </button>
        </div>
        
        <div class="card-body">
            <div class="row mb-4 bg-light p-3 rounded mx-0 border shadow-sm">
                <div class="col-md-3">
                    <label class="font-weight-bold small text-muted text-uppercase">Role</label>
                    <select id="filter_role" class="form-control form-control-sm border-primary">
                    <option value="">-- Semua Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ strtoupper($role->name) }}" data-divisi="{{ $role->division_id }}">
                            {{ strtoupper(str_replace('_', ' ', $role->role_name)) }}
                        </option>
                    @endforeach
                </select>
                </div>

                <div class="col-md-4">
                    <label class="font-weight-bold small text-muted text-uppercase">Pencarian Cepat</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-primary text-white border-primary"><i class="fas fa-search fa-sm"></i></span>
                        </div>
                        <input type="text" id="custom_search" class="form-control form-control-sm border-primary" placeholder="Ketik nama atau email...">
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button id="reset_filter" class="btn btn-sm btn-block btn-secondary shadow-sm">
                        <i class="fas fa-sync mr-1"></i> Reset
                    </button>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" id="divisionTabs" role="tablist">
                @foreach($divisions as $div)
                <li class="nav-item">
                    <a class="nav-link font-weight-bold" data-toggle="tab" href="#content" data-divisi="{{ $div->id }}" data-name="{{ $div->name }}">
                        {{ $div->name }}
                    </a>
                </li>
                @endforeach
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="content">
                    <div id="table-container" class="table-responsive">
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('manajemen_user.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Tambah User Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 class="font-weight-bold text-primary mb-3 text-uppercase small">Data Profil</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted">NAMA LENGKAP</label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted">EMAIL</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="email@lmg.com" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Divisi Penempatan</label>
                            <select name="divisi_id" id="modal_divisi" class="form-control form-control-sm select2" onchange="updateRoleOptions(this.value)" required>
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-primary text-uppercase">Role Jabatan</label>
                            <select name="role_id" id="modal_role" class="form-control form-control-sm border-primary" onchange="updateParentOptions()" required disabled>
                                <option value="">-- Pilih Divisi Dahulu --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-divisi="{{ $role->division_id }}" data-level="{{ $role->level }}">
                                        {{ strtoupper(str_replace('_', ' ', $role->role_name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold small text-primary">PASSWORD</label>
                            <input type="password" name="password" class="form-control form-control-sm border-primary" required>
                        </div>
                    </div>

                    <div id="hierarchy_panel" class="card bg-light border-0 d-none mt-3">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <h6 class="font-weight-bold text-info small mb-2 text-uppercase">Atasan Langsung</h6>
                                    <select name="parent_id" id="modal_parent" class="form-control form-control-sm bg-white select2-single">
                                        <option value="">-- Tanpa Atasan --</option>
                                    </select>
                                    <small class="text-muted">User ini akan melapor ke siapa?</small>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-success small mb-2 text-uppercase">Tarik Bawahan</h6>
                                    <select name="subordinate_ids[]" id="modal_subordinates" class="form-control form-control-sm bg-white select2-multiple" multiple="multiple">
                                        {{-- Diisi via JS --}}
                                    </select>
                                    <small class="text-muted">Siapa saja yang akan melapor ke user baru ini?</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow">Simpan User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formEditUser" method="POST">
            @csrf
            @method('PUT') <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <h6 class="font-weight-bold text-warning mb-3 text-uppercase small">Data Profil</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted">NAMA LENGKAP</label>
                            <input type="text" name="name" id="edit_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted">EMAIL</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase">Divisi Penempatan</label>
                            <select name="divisi_id" id="edit_divisi" class="form-control form-control-sm select2-single-edit" onchange="updateRoleOptionsEdit(this.value)" required>
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-warning text-uppercase">Role Jabatan</label>
                            <select name="role_id" id="edit_role" class="form-control form-control-sm border-warning" onchange="updateHierarchyPanelEdit()" required disabled>
                                <option value="">-- Pilih Divisi Dahulu --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" data-divisi="{{ $role->division_id }}" data-level="{{ $role->level }}">
                                        {{ strtoupper(str_replace('_', ' ', $role->role_name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold small text-warning text-uppercase">Ganti Password (Opsional)</label>
                            <input type="password" name="password" class="form-control form-control-sm border-warning" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                    </div>

                    <div id="hierarchy_panel_edit" class="card bg-light border-0 d-none mt-3">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <h6 class="font-weight-bold text-info small mb-2 text-uppercase">Atasan Langsung</h6>
                                    <select name="parent_id" id="edit_parent" class="form-control form-control-sm bg-white border-info select2-single-edit">
                                        <option value="">-- Tanpa Atasan --</option>
                                    </select>
                                    <small class="text-muted small">User ini akan melapor ke siapa?</small>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-success small mb-2 text-uppercase">Tarik Bawahan</h6>
                                    <select name="subordinate_ids[]" id="edit_subordinates" class="form-control form-control-sm bg-white select2-multiple-edit" multiple="multiple" style="width: 100%">
                                        {{-- Diisi via JS --}}
                                    </select>
                                    <small class="text-muted small">Siapa yang akan melapor ke user ini?</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-5 rounded-pill shadow text-white">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formDeleteUser" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-user-times text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5>Apakah Anda yakin ingin menghapus <br><strong id="delete_user_name" class="text-danger"></strong>?</h5>
                    <p class="text-muted small">Tindakan ini tidak dapat dibatalkan. Semua data terkait user ini di divisi dan hierarki akan dihapus.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-5 rounded-pill shadow">Ya, Hapus User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    if ($.isFunction($.fn.select2)) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    let initialDivisiId = $('#divisionTabs .nav-link.active').data('divisi') || 1;
    filterRoleByDivision(initialDivisiId);
    
    renderTable(1, "Digital Marketing");

    // Listener saat ganti tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let divisiId = $(e.target).data('divisi');
        let divisiName = $(e.target).data('name');

        // Jalankan filter dropdown role
        filterRoleByDivision(divisiId);

        // Jalankan render ulang tabel (fungsi yang sudah kita buat sebelumnya)
        renderTable(divisiId, divisiName);
    });
    // 1. Inisialisasi DataTable + Fitur Filtering
    var table = $('#dataTable_baru').DataTable({
        "destroy": true,
        "dom": '<"d-flex justify-content-between mb-3"l>rtip', 
        "ordering": true,
        "pageLength": 10,
        "language": {
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ user",
            "paginate": { "next": "Next", "previous": "Prev" }
        }
    });
    

    $('#custom_search').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('.select2-multiple-edit').select2({
        placeholder: "Pilih satu atau lebih bawahan",
        allowClear: true,
        dropdownParent: $('#editModal') // Penting agar select2 muncul di atas modal
    });

    // Filter Role (Kolom 4 / Index 3)
    $('#filter_role').on('change', function() {
        let val = $(this).val();
        $('#mainDataTable').DataTable().column(2).search(val ? '^' + val + '$' : '', true, false).draw();
    });

    function filterRoleByDivision(selectedDivisiId) {
        let $roleSelect = $('#filter_role');
        let $options = $roleSelect.find('option');

        // Reset pilihan ke "-- Semua Role --" setiap kali pindah tab
        $roleSelect.val('');

        $options.each(function() {
            let optionDivisiId = $(this).data('divisi');
            
            // Jika tab "Semua" diklik (id: all) atau option tidak punya divisi_id
            if (selectedDivisiId === 'all' || !optionDivisiId) {
                $(this).show();
            } 
            // Jika division_id pada role cocok dengan tab yang aktif
            else if (optionDivisiId == selectedDivisiId) {
                $(this).show();
            } 
            // Sembunyikan jika tidak cocok
            else {
                $(this).hide();
            }
        });
    }

    // Reset Filter
    $('#reset_filter').on('click', function() {
        $('#custom_search').val('');
        $('#filter_role').val('').trigger('change');
        
        // Kembalikan ke pencarian kosong
        let table = $('#mainDataTable').DataTable();
        table.search('').columns().search('').draw();
    });
});

function renderTable(divisiId, divisiName) {
    let container = $('#table-container');
    container.empty();

    // 1. Tentukan Struktur Header (THEAD)
    let thead = '';
    if (divisiId === 'all') {
        thead = `<tr>
            <th width="5%">No</th>
            <th>User (Email)</th>
            <th>Penempatan (Divisi & Role)</th>
            <th>Akses Produk</th>
            <th>Atasan Langsung</th>
            <th width="10%">Aksi</th>
        </tr>`;
    } else {
        // Header Khusus Divisi dengan Hierarki Detail
        thead = `<tr>
            <th width="5%">No</th>
            <th>User</th>
            <th>Role</th>
            <th>DMM</th>
            <th>DM</th>
            <th>SCC</th>
            <th>CC</th>
            <th width="5%">Aksi</th>
        </tr>`;
    }

    container.append(`<table class="table table-bordered table-hover w-100" id="mainDataTable">
        <thead class="bg-light text-center small font-weight-bold">${thead}</thead>
        <tbody class="small text-dark"></tbody>
    </table>`);

    // 2. Inisialisasi DataTable
    $('#mainDataTable').DataTable({
        ajax: {
            url: "https:/erp.lightermediagroup.com/users/json",
            dataSrc: (json) => divisiId === 'all' ? json : json.filter(u => u.user_divisions.some(ud => ud.division_id == divisiId))
        },
        columns: getColumnsConfig(divisiId)
    });
}

function getColumnsConfig(divisiId) {
    let cols = [
        { data: null, render: (d, t, r, meta) => meta.row + 1, className: 'text-center' },
        { data: null, render: r => `<strong>${r.name ?? '-'}</strong><div class="text-xs text-muted">${r.email ?? '-'}</div>` }
    ];

    if (divisiId === 'all') {
        cols.push(
            { 
                data: 'user_divisions', 
                render: uds => (uds ?? []).map(ud => `<span class="badge badge-info mb-1">${ud.division?.name ?? '-'}</span>`).join(' ') 
            },
            { 
                data: 'user_clients', 
                render: ucs => (ucs ?? []).map(uc => `<span class="badge badge-light border mb-1">${uc.client?.nama ?? '-'}</span>`).join(' ') 
            },
            { 
                data: 'user_divisions', 
                render: uds => (uds ?? []).map(ud => ud.parent ? `<div class="text-xs">• ${ud.parent.email}</div>` : '').join('') 
            }
        );
    } else {
        cols.push(
            { 
                data: 'user_divisions', 
                render: uds => {
                    let current = (uds ?? []).find(ud => ud.division_id == divisiId);
                    // Menampilkan role USER SENDIRI
                    let roleDisplay = current?.role ?? '-';
                    return `<span class="badge badge-outline-secondary text-dark">${roleDisplay.replace(/_/g, ' ').toUpperCase()}</span>`;
                }
            },
            // Kolom Hierarki: Sekarang akan otomatis mencari ke atas
            { data: null, render: r => mapAtasan(r, divisiId, 1) },
            { data: null, render: r => mapAtasan(r, divisiId, 2) },
            { data: null, render: r => mapAtasan(r, divisiId, 3) },
            { data: null, render: r => mapAtasan(r, divisiId, 4) }
        );
    }

    cols.push({ 
        data: 'id', 
        render: (id, type, r) => { // 'id' adalah isi dari data: 'id', 'r' adalah objek baris lengkap
            // Kita gunakan 'id' untuk parameter pertama
            // Kita gunakan 'r.name' untuk parameter kedua
            const userName = r.name ? r.name.replace(/'/g, "\\'") : '-';

            return `
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-warning" onclick="editUser(${id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${id}, '${userName}')" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }, 
        className: 'text-center' 
    });
    return cols;
}

function mapAtasan(user, divisiId, targetLevel) {
    const allData = $('#mainDataTable').DataTable().ajax.json();
    let currentUd = (user.user_divisions ?? []).find(ud => ud.division_id == divisiId);

    if (!currentUd || !currentUd.level) return '-';

    let myLevel = parseInt(currentUd.level); 

    // 1. Jika level user PAS dengan level kolom
    if (myLevel === targetLevel) {
        return `<div class="d-flex justify-content-center">
                    <span class="badge badge-success px-2">Self</span>
                </div>`;
    }

    // 2. JIKA TARGET DI ATAS (Cari Atasan - Rekursif Ke Atas)
    if (myLevel > targetLevel) {
        return traceHierarchy(currentUd.parent_id, divisiId, targetLevel, allData);
    }

    // 3. JIKA TARGET DI BAWAH (Cari Bawahan - Filter ke Bawah)
    if (myLevel < targetLevel) {
        return findSubordinate(user.id, divisiId, targetLevel, allData);
    }

    return '<span class="text-muted">-</span>';
}

function traceHierarchy(parentId, divisiId, targetLevel, allData) {
    if (!parentId || !allData) return '-';

    // Cari user yang menjadi parent (atasan langsung)
    let superior = allData.find(u => u.id == parentId);
    if (!superior) return '-';

    let supUd = (superior.user_divisions ?? []).find(ud => ud.division_id == divisiId);
    if (!supUd || !supUd.level) return '-';

    let supLevel = parseInt(supUd.level);

    // CEK: Apakah level atasan ini cocok dengan kolom yang kita cari?
    if (supLevel === targetLevel) {
        return `<div class="text-xs font-weight-bold text-primary">${superior.email}</div>`;
    }

    // REKURSIF: Jika level atasan ini masih lebih besar (misal Level 3) 
    // sedangkan kita mencari Level 1, maka tanya lagi: "Siapa parent dari si Level 3 ini?"
    if (supLevel > targetLevel && supUd.parent_id) {
        return traceHierarchy(supUd.parent_id, divisiId, targetLevel, allData);
    }

    return '-';
}

function findSubordinate(myId, divisiId, targetLevel, allData) {
    if (!myId || !allData) return '-';

    // Cari semua orang yang melapor ke myId dalam divisi ini
    let subordinates = allData.filter(u => {
        let ud = (u.user_divisions ?? []).find(d => d.division_id == divisiId);
        return ud && ud.parent_id == myId;
    });

    if (subordinates.length === 0) return '-';

    let results = [];

    subordinates.forEach(sub => {
        let subUd = sub.user_divisions.find(d => d.division_id == divisiId);
        let subLevel = parseInt(subUd.level);

        // Jika levelnya cocok, ambil emailnya
        if (subLevel === targetLevel) {
            results.push(`<div class="text-xs text-info font-weight-bold">${sub.email}</div>`);
        } 
        // Jika levelnya masih di atas target, cari lagi di bawah orang ini (Rekursif ke bawah)
        else if (subLevel < targetLevel) {
            let nested = findSubordinate(sub.id, divisiId, targetLevel, allData);
            if (nested !== '-') results.push(nested);
        }
    });

    // Hilangkan duplikat dan gabungkan
    let uniqueResults = [...new Set(results)];
    return uniqueResults.length > 0 ? uniqueResults.join('') : '-';
}

function updateRoleOptions(divisiId) {
    let $roleSelect = $('#modal_role');
    console.log($roleSelect);
    let $parentPanel = $('#parent_panel');
    
    $roleSelect.val('').prop('disabled', !divisiId);
    $parentPanel.addClass('d-none');

    // Filter Role berdasarkan divisi yang dipilih
    $roleSelect.find('option').each(function() {
        // console.log(this);
        let roleDivId = $(this).data('data-divisi');
        console.log($(this));
        
        // console.log(roleDivId);
        if (!roleDivId || roleDivId == divisiId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function updateParentOptions() {
    let divisiId = $('#modal_divisi').val();
    let roleId = $('#modal_role').val();
    let selectedLevel = parseInt($('#modal_role option:selected').data('level'));
    
    let $parentSelect = $('#modal_parent');
    let $subSelect = $('#modal_subordinates');
    let $panel = $('#hierarchy_panel');

    if (!roleId) {
        $panel.addClass('d-none');
        return;
    }

    $panel.removeClass('d-none');
    
    // Ambil data user dari tabel (AJAX JSON)
    const allData = $('#mainDataTable').DataTable().ajax.json();

    // 1. FILTER CALON ATASAN (Level < Level Sekarang)
    // Contoh: Jika saya Lvl 3, maka atasan adalah Lvl 1 & 2
    let htmlParent = '<option value="">-- Tanpa Atasan --</option>';
    let parents = allData.filter(u => {
        let ud = (u.user_divisions ?? []).find(d => d.division_id == divisiId);
        return ud && parseInt(ud.level) < selectedLevel;
    });
    parents.forEach(p => {
        htmlParent += `<option value="${p.id}">${p.name} (${p.email})</option>`;
    });
    $parentSelect.html(htmlParent);

    // 2. FILTER CALON BAWAHAN (Level > Level Sekarang)
    // Contoh: Jika saya Lvl 3, maka bawahan adalah Lvl 4
    let htmlSub = '';
    let subordinates = allData.filter(u => {
        // Cari data divisi yang cocok dengan divisiId yang dipilih
        let ud = (u.user_divisions ?? []).find(d => d.division_id == divisiId);
        
        if (ud) {
            let userLevel = parseInt(ud.level);
            return userLevel === (selectedLevel + 1);
        }
        return false;
    });
    console.log(subordinates);
    subordinates.forEach(s => {
        htmlSub += `<option value="${s.id}">${s.name} (${s.email})</option>`;
    });
    $subSelect.html(htmlSub);

    // Refresh Select2 jika Anda menggunakannya
    $('.select2-multiple').select2({
        placeholder: "Pilih satu atau lebih bawahan",
        allowClear: true
    });
}

function editUser(id) {
    // 1. Ambil data JSON dari DataTable
    const allData = $('#mainDataTable').DataTable().ajax.json();
    console.log(allData);
    const user = allData.find(u => u.id == id);

    if (!user) return Swal.fire('Error', 'Data tidak ditemukan', 'error');

    // 2. Set Action Form URL (Route Update)
    $('#formEditUser').attr('action', `/manajemen_user/${id}`);

    // 3. Set Judul Modal
    $('#editModal .modal-title').html(`<i class="fas fa-edit mr-2"></i>Edit User: ${user.name}`);

    // 4. Isi Field Profil Basic
    $('#edit_name').val(user.name);
    $('#edit_email').val(user.email);

    // 5. Logika Set Divisi & Role
    // Asumsi kita mengedit penempatan pertama (user_divisions[0])
    const currentPlacement = user.user_divisions[0];

    if (currentPlacement) {
        // A. Set Divisi dan picu filter role
        $('#edit_divisi').val(currentPlacement.division_id).trigger('change');

        // B. Gunakan sedikit delay agar option role selesai difilter oleh updateRoleOptionsEdit
        setTimeout(() => {
            // Set Role
            $('#edit_role').val(currentPlacement.role_id).trigger('change');

            // C. Delay lagi agar daftar atasan/bawahan selesai dimuat
            setTimeout(() => {
                // Set Atasan saat ini
                $('#edit_parent').val(currentPlacement.parent_id);

                // Set Bawahan saat ini (Cari siapa saja yang parent_id-nya adalah user ini)
                let currentSubordinateIds = allData.filter(u => {
                    let ud = (u.user_divisions ?? []).find(d => d.division_id == currentPlacement.division_id);
                    return ud && ud.parent_id == user.id;
                }).map(u => u.id);

                $('#edit_subordinates').val(currentSubordinateIds).trigger('change');

            }, 200); // Delay untuk hierarki
        }, 100); // Delay untuk role
    } else {
        // Reset jika tidak ada penempatan
        $('#edit_divisi').val('').trigger('change');
    }

    // Tampilkan Modal
    $('#editModal').modal('show');
}

function deleteUser(id, name) {
    $('#formDeleteUser').attr('action', `/manajemen_user/${id}`);
    
    // 2. Set Nama di Modal
    $('#delete_user_name').text(name);
    
    // 3. Tampilkan Modal
    $('#deleteModal').modal('show');
}

function updateRoleOptionsEdit(divisiId) {
    let $roleSelect = $('#edit_role');
    let $panel = $('#hierarchy_panel_edit');

    // Reset role dan panel
    $roleSelect.val('').prop('disabled', !divisiId);
    $panel.addClass('d-none');

    $roleSelect.find('option').each(function() {
        let roleDivId = $(this).data('divisi');
        // Tampilkan option default atau yang divisinya cocok
        if (!roleDivId || roleDivId == divisiId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function updateHierarchyPanelEdit() {
    let divisiId = $('#edit_divisi').val();
    let selectedLevel = parseInt($('#edit_role option:selected').data('level'));
    let currentUserId = $('#formEditUser').attr('action').split('/').pop();
    
    let $parentSelect = $('#edit_parent');
    let $subSelect = $('#edit_subordinates');
    let $panel = $('#hierarchy_panel_edit');

    // Jika role belum dipilih atau level 1, sembunyikan panel
    if (!divisiId || isNaN(selectedLevel)) {
        $panel.addClass('d-none');
        return;
    }

    $panel.removeClass('d-none');

    // Ambil data user dari tabel
    const allData = $('#mainDataTable').DataTable().ajax.json();

    // --- LOGIKA SAMA PERSIZ DENGAN MODAL TAMHAB ---

    // 1. FILTER CALON ATASAN (Level Angka < Level Sekarang, misal 2 < 3)
    let htmlParent = '<option value="">-- Tanpa Atasan --</option>';
    let potentialParents = allData.filter(u => {
        let ud = (u.user_divisions ?? []).find(d => d.division_id == divisiId);
        // Pastikan bukan dirinya sendiri (penting saat edit)
        return ud && parseInt(ud.level) < selectedLevel && u.id != $('#formEditUser').attr('action').split('/').pop();
    });
    potentialParents.forEach(p => {
        htmlParent += `<option value="${p.id}">${p.name} (${p.email})</option>`;
    });
    $parentSelect.html(htmlParent);

    // 2. FILTER CALON BAWAHAN (Level Angka > Level Sekarang, misal 4 > 3)
    let htmlSub = '';
    let potentialSubordinates = allData.filter(u => {
        // 1. Cari data divisi user tersebut yang cocok dengan divisiId yang dipilih
        let ud = (u.user_divisions ?? []).find(d => d.division_id == divisiId);
        
        if (ud) {
            let userLevel = parseInt(ud.level);
            
            // 2. Logika Filter:
            // - Harus Level + 1 (Bawahan langsung)
            // - Bukan dirinya sendiri (u.id != currentUserId)
            return userLevel === (selectedLevel + 1) && u.id != currentUserId;
        }
        
        return false;
    });
    potentialSubordinates.forEach(s => {
        htmlSub += `<option value="${s.id}">${s.name} (${s.email})</option>`;
    });
    $subSelect.html(htmlSub);

    // Refresh Select2 Multiple
    $subSelect.select2({
        placeholder: "Pilih satu atau lebih bawahan",
        allowClear: true,
        dropdownParent: $('#editModal')
    });
}
</script>

@include('partials.footer')