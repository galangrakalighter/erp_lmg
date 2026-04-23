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
                    <label class="font-weight-bold small text-muted text-uppercase">Divisi</label>
                    <select id="filter_divisi" class="form-control form-control-sm border-primary">
                        <option value="">-- Semua Divisi --</option>
                        @foreach($divisions as $div) {{-- Asumsi Anda mengirim data $divisions dari controller --}}
                            <option value="{{ $div->name }}">{{ $div->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="font-weight-bold small text-muted text-uppercase">Role</label>
                    <select id="filter_role" class="form-control form-control-sm border-primary">
                        <option value="">-- Semua Role --</option>
                        @foreach($roles as $role) {{-- Asumsi Anda mengirim data $roles dari controller --}}
                            <option value="{{ strtoupper($role->name) }}">{{ strtoupper(str_replace('_', ' ', $role->name)) }}</option>
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

            {{-- <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable_baru" width="100%" cellspacing="0">
                    <thead class="bg-light text-center small font-weight-bold">
                        <tr>
                            <th width="5%">No</th>
                            <th>User (Email)</th>
                            <th>Penempatan (Divisi & Role)</th>
                            <th>Atasan Langsung</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                        <tr class="small text-dark align-middle">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                <div class="text-xs text-muted">{{ $item->email }}</div>
                            </td>
                            
                            <td class="text-center">
                                @foreach($item->userDivisions as $ud)
                                    <div class="mb-1">
                                        <span class="badge badge-info">{{ $ud->division->name ?? '-' }}</span>
                                        <span class="badge badge-outline-secondary border text-dark">
                                            {{ strtoupper(str_replace('_', ' ', $ud->role->name ?? '-')) }}
                                        </span>
                                    </div>
                                @endforeach
                            </td>

                            <td>
                                @foreach($item->userDivisions as $ud)
                                    @if($ud->parent)
                                        <div class="text-xs mb-1">
                                            <i class="fas fa-chevron-right text-primary mr-1"></i>
                                            <strong>{{ $ud->parent->email }}</strong>
                                            <span class="text-muted">({{ $ud->division->name }})</span>
                                        </div>
                                    @else
                                        <div class="text-xs text-muted mb-1">• Tanpa Atasan ({{ $ud->division->name }})</div>
                                    @endif
                                @endforeach
                            </td>

                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editModal_{{ $item->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if($isSuperAdmin)
                                    <button class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteModal_{{ $item->id }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> --}}
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
                            <input type="text" name="name" class="form-control" placeholder="Nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted">EMAIL</label>
                            <input type="email" name="email" class="form-control" placeholder="email@lmg.com" required>
                        </div>
                        @if(Auth::user()->role == "super_admin")
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-muted">DIVISI (BISA PILIH > 1)</label>
                            {{-- Perhatikan penambahan name="divisi[]" dan atribut multiple --}}
                            <select name="divisi[]" class="form-control select2-divisi" {{ Auth::user()->role == "super_admin" ?  'multiple="multiple"' : ''}} required>
                                <option value="1">Digital Marketing</option>
                                <option value="2">Business Dev</option>
                            </select>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold small text-primary">ROLE JABATAN</label>
                            <select name="role" id="role_select" class="form-control border-primary shadow-sm" onchange="toggleHierarchy(this.value)" required>
                                <option value="">-- Pilih Role --</option>
                                @if(Auth::user()->role == "super_admin")
                                    <option value="head_of_division">HOD</option>
                                @endif
                                <option value="digital_marketing_manager">DMM</option>
                                <option value="digital_marketing">DM</option>
                                <option value="content_creator">CC</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold small text-primary">PASSWORD</label>
                            <input type="password" name="password" class="form-control border-primary" required>
                        </div>
                    </div>

                    <div id="hierarchy_panel" class="card bg-light border-0 d-none mt-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-right d-none" id="section_atasan">
                                    <h6 class="font-weight-bold text-info small mb-3">ATASAN LANGSUNG</h6>
                                    <div id="div_pilih_hod" class="form-group d-none">
                                        <label class="small">Pilih HOD</label>
                                        <select name="hod_id" class="form-control bg-white">
                                            <option value="">-- Tanpa HOD --</option>
                                            @foreach($data->where('role', 'head_of_division') as $m)
                                                <option value="{{ $m->id }}">{{ $m->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="div_pilih_dmm" class="form-group d-none">
                                        <label class="small">Pilih Manager (DMM)</label>
                                        <select name="dmm_id" class="form-control bg-white">
                                            <option value="">-- Tanpa Manager --</option>
                                            @foreach($data->where('role', 'digital_marketing_manager') as $m)
                                                <option value="{{ $m->id }}">{{ $m->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="div_pilih_dm" class="form-group d-none">
                                        <label class="small">Pilih Atasan (DM)</label>
                                        <select name="dm_id" class="form-control bg-white">
                                            <option value="">-- Tanpa Atasan --</option>
                                            @foreach($data->where('role', 'digital_marketing') as $d)
                                                <option value="{{ $d->id }}">{{ $d->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 d-none" id="section_bawahan">
                                    <h6 class="font-weight-bold text-success small mb-3">PILIH BANYAK BAWAHAN</h6>
                                    <div id="div_bawahan_dm" class="form-group d-none">
                                        <label class="small">Daftar Staf DM</label>
                                        <select name="bawahan_ids[]" class="form-control select2-multiple" multiple="multiple" style="width: 100%">
                                            @foreach($data->where('role', 'digital_marketing') as $sub)
                                                <option value="{{ $sub->id }}">{{ $sub->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="div_bawahan_cc" class="form-group d-none">
                                        <label class="small">Daftar Content Creator</label>
                                        <select name="bawahan_ids[]" class="form-control select2-multiple" multiple="multiple" style="width: 100%">
                                            @foreach($data->where('role', 'content_creator') as $sub)
                                                <option value="{{ $sub->id }}">{{ $sub->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>
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

@foreach ($data as $item)
    <div class="modal fade" id="editModal_{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('manajemen_user.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit User: {{ $item->name }}</h5>
                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Nama</label>
                                <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $item->email }}" required>
                            </div>
                            @if(Auth::user()->role == "super_admin")
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-muted text-uppercase">Divisi</label>
                                    @php 
                                        // Pastikan $item->divisi adalah array (hasil cast dari model)
                                        $userDivisions = is_array($item->divisi) ? $item->divisi : json_decode($item->divisi, true) ?? [];
                                    @endphp
                                <select name="{{ $item->role == 'head_of_division' ? '' : 'divisi[]' }}" class="form-control select2-divisi-edit " id="nothod_{{ $item->id }}" {{ Auth::user()->role == "super_admin" ?  'multiple="multiple"' : ''}} required>
                                    <option value="1" {{ in_array(1, $userDivisions) ? 'selected' : '' }}>Digital Marketing</option>
                                    <option value="2" {{ in_array(2, $userDivisions) ? 'selected' : '' }}>Business Dev</option>
                                </select>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold small text-warning text-uppercase">Role Jabatan</label>
                                <select name="role" class="form-control border-warning" onchange="toggleHierarchyEdit(this.value, '{{ $item->id }}')" required>
                                     @if(Auth::user()->role == "super_admin")
                                        <option value="head_of_division" {{ $item->role == 'head_of_division' ? 'selected' : '' }}>HOD</option>
                                    @endif
                                    <option value="digital_marketing_manager" {{ $item->role == 'digital_marketing_manager' ? 'selected' : '' }}>DMM</option>
                                    <option value="digital_marketing" {{ $item->role == 'digital_marketing' ? 'selected' : '' }}>DM</option>
                                    <option value="content_creator" {{ $item->role == 'content_creator' ? 'selected' : '' }}>CC</option>
                                </select>
                            </div>
                        </div>

                        <div id="hierarchy_panel_edit_{{ $item->id }}" class="card bg-light border-0 mt-2 {{ $item->role == 'head_of_division' ? 'd-none' : '' }}">
                            <div class="card-body">
                                <div class="row">
                                    <div class="{{ $item->role == 'content_creator' ? 'col-md-12' : 'col-md-6' }} border-right" id="section_atasan_edit_{{ $item->id }}">
                                        <h6 class="font-weight-bold text-info small mb-3">ATASAN LANGSUNG</h6>
                                        <div id="div_edit_hod_{{ $item->id }}" class="form-group {{ $item->role == 'digital_marketing_manager' && Auth::user()->role == 'super_admin' ? '' : 'd-none' }}">
                                            <label class="small">Pilih Head Division (HOD)</label>
                                            <select name="hod_id" class="form-control bg-white">
                                                <option value="">-- Tanpa Head Division --</option>
                                                @foreach($data->where('role', 'head_of_division') as $m)
                                                    <option value="{{ $m->id }}" {{ $item->hod == $m->id ? 'selected' : '' }}>{{ $m->email }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div id="div_edit_dmm_{{ $item->id }}" class="form-group {{ $item->role == 'digital_marketing' ? '' : 'd-none' }}">
                                            <label class="small">Pilih Manager (DMM)</label>
                                            <select name="dmm" class="form-control bg-white">
                                                <option value="">-- Tanpa Manager --</option>
                                                @foreach($data->where('role', 'digital_marketing_manager') as $m)
                                                    <option value="{{ $m->id }}" {{ $item->dmm == $m->id ? 'selected' : '' }}>{{ $m->email }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div id="div_edit_dm_{{ $item->id }}" class="form-group {{ $item->role == 'content_creator' ? '' : 'd-none' }}">
                                            <label class="small">Pilih Atasan (DM)</label>
                                            <select name="dm" class="form-control bg-white">
                                                <option value="">-- Tanpa Atasan --</option>
                                                @foreach($data->where('role', 'digital_marketing') as $d)
                                                    <option value="{{ $d->id }}" {{ $item->dm == $d->id ? 'selected' : '' }}>{{ $d->email }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 {{ in_array($item->role, ['digital_marketing_manager', 'digital_marketing']) ? '' : 'd-none' }}" id="section_bawahan_edit_{{ $item->id }}">
                                        <h6 class="font-weight-bold text-success small mb-3">DAFTAR BAWAHAN</h6>
                                        <select name="bawahan_ids[]" class="form-control select2-edit" multiple="multiple" style="width: 100%">
                                            @if($item->role == 'digital_marketing_manager')
                                                @foreach($data->where('role', 'digital_marketing') as $sub)
                                                    <option value="{{ $sub->id }}" {{ $sub->dmm == $item->id ? 'selected' : '' }}>{{ $sub->email }}</option>
                                                @endforeach
                                            @elseif($item->role == 'digital_marketing')
                                                @foreach($data->where('role', 'content_creator') as $sub)
                                                    <option value="{{ $sub->id }}" {{ $sub->dm == $item->id ? 'selected' : '' }}>{{ $sub->email }}</option>
                                                @endforeach
                                            @endif
                                        </select>
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

    <div class="modal fade" id="deleteModal_{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="{{ route('manajemen_user.destroy', $item->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center py-4">
                        <i class="fas fa-trash-alt fa-4x text-danger mb-3"></i>
                        <h5>Hapus <strong>{{ $item->name }}</strong>?</h5>
                        <p class="text-muted small">Data tidak dapat dikembalikan setelah dihapus.</p>
                    </div>
                    <div class="modal-footer bg-light justify-content-center">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger px-4 rounded-pill shadow">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
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
    
    renderTable(1, "Digital Marketing");

    // Listener saat ganti tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        let divisiId = $(e.target).data('divisi');
        let divisiName = $(e.target).data('name');
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

    // Filter Divisi (Kolom 3 / Index 2)
    $('#filter_divisi').on('change', function() {
        table.column(2).search(this.value).draw();
    });

    // Filter Role (Kolom 4 / Index 3)
    $('#filter_role').on('change', function() {
        table.column(3).search(this.value).draw();
    });

    // Reset Filter
    $('#reset_filter').on('click', function() {
        $('#filter_divisi, #filter_role').val('');
        table.columns().search('').draw();
    });

    // 2. Inisialisasi Select2
    $('.select2-multiple').select2({
        placeholder: "-- Pilih Bawahan --",
        allowClear: true
    });

    $('.select2-divisi').select2({
        placeholder: "-- Pilih Divisi --",
        allowClear: true,
        width: '100%'
    });

    // Re-inisialisasi untuk Modal Edit saat dibuka
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('.select2-divisi-edit').select2({
            dropdownParent: $(this), 
            placeholder: "-- Pilih Divisi --",
            allowClear: true,
            width: '100%'
        });
    });

    // Re-inisialisasi Select2 saat Modal muncul
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('.select2-edit').select2({
            dropdownParent: $(this), 
            placeholder: "-- Pilih Bawahan --",
            allowClear: true
        });
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
            <th>HOD</th>
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
            url: "{{ route('users.json') }}", // Sesuaikan route API Anda
            dataSrc: (json) => divisiId === 'all' ? json : json.filter(u => u.user_divisions.some(ud => ud.division_id == divisiId))
        },
        columns: getColumnsConfig(divisiId)
    });
}

function getColumnsConfig(divisiId) {
    let cols = [
        { data: null, render: (d, t, r, meta) => meta.row + 1, className: 'text-center' },
        { data: null, render: r => `<strong>${r.name}</strong><div class="text-xs text-muted">${r.email}</div>` }
    ];

    if (divisiId === 'all') {
        cols.push(
            { data: 'user_divisions', render: uds => uds.map(ud => `<span class="badge badge-info mb-1">${ud.division.name}</span>`).join(' ') },
            { data: 'user_clients', render: ucs => ucs.map(uc => `<span class="badge badge-light border mb-1">${uc.client.nama}</span>`).join(' ') },
            { data: 'user_divisions', render: uds => uds.map(ud => ud.parent ? `<div class="text-xs">• ${ud.parent.email}</div>` : '').join('') }
        );
    } else {
        // Logika Hierarki untuk Tab Divisi Spesifik
        cols.push(
            { 
                data: 'user_divisions', 
                render: uds => {
                    let current = uds.find(ud => ud.division_id == divisiId);
                    return `<span class="badge badge-outline-secondary text-dark">${current.role.name.replace(/_/g, ' ')}</span>`;
                }
            },
            { data: null, render: r => mapAtasan(r, divisiId, 'HEAD OF DIVISION') },
            { data: null, render: r => mapAtasan(r, divisiId, 'DIGITAL MARKETING MANAGER') },
            { data: null, render: r => mapAtasan(r, divisiId, 'DIGITAL MARKETING') },
            { data: null, render: r => mapAtasan(r, divisiId, 'SCC') },
            { data: null, render: r => mapAtasan(r, divisiId, 'CONTENT CREATOR') }
        );
    }

    cols.push({ data: 'id', render: id => `<button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>`, className: 'text-center' });
    return cols;
}

function mapAtasan(user, divisiId, roleName) {
    let ud = user.user_divisions.find(item => item.division_id == divisiId);
    if (!ud) return '-';

    // Jika user itu sendiri yang jabatannya ini
    if (ud.role.name.toUpperCase() === roleName) {
        return '<span class="badge badge-success px-2">Self</span>';
    }

    // Jika user ini punya atasan (parent) dengan role tersebut
    // Catatan: Anda perlu memastikan data 'parent' membawa info 'role' di JSON API
    if (ud.parent && ud.parent.role_name === roleName) {
        return `<span class="text-xs font-weight-bold text-primary">${ud.parent.email}</span>`;
    }

    return '<span class="text-muted">-</span>';
}
</script>

@include('partials.footer')