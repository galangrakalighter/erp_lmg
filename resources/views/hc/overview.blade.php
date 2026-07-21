@extends('layout.app')
@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Point Belum Selesai
                    </div>
                    <div class="h4 mb-0 font-weight-bold" id="pointBelum">0</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow-sm">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Point Selesai
                    </div>
                    <div class="h4 mb-0 font-weight-bold" id="pointSelesai">0</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-primary shadow-sm">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Point
                    </div>
                    <div class="h4 mb-0 font-weight-bold" id="totalPoint">0</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary shadow-sm">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total KPI
                    </div>
                    <div class="h4 mb-0 font-weight-bold" id="totalKPI">0</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            {{-- Menampilkan Nama Client jika tersedia --}}
            <h6 class="m-0 font-weight-bold text-primary">Project Overview: {{ $client->nama }}</h6>
            
            @if(Auth::user()->role != "content_creator")
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm mr-1"></i> Tambah Task
            </button>
            @endif
        </div>

        <div class="card-body border-bottom bg-light">
            {{-- Pastikan action mengarah ke route index dengan parameter id_client --}}
            <form id="filterForm" action="{{ route('hc_overview.index', $id) }}" method="GET">
                <div class="row">
                    {{-- Filter Baris 1 --}}
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Mulai:</label>
                        <input type="date" name="start_date" class="form-control form-control-sm border-primary" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Sampai:</label>
                        <input type="date" name="end_date" class="form-control form-control-sm border-primary" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Divisi:</label>
                        <select name="divisi" class="form-control form-control-sm border-primary">
                            <option value="">- Semua -</option>
                            @foreach($result as $divId => $nama)
                                <option value="{{ $divId }}" {{ request('divisi') == $divId ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Point:</label>
                        <select name="priority" class="form-control form-control-sm border-primary">
                        <option value="">- Semua -</option>
                        @foreach($difficultyLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>
                                {{ $key }} - {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">PIC:</label>
                        <select name="pic" class="form-control form-control-sm border-primary">
                            <option value="">- Semua PIC -</option>
                            @foreach($vic_list as $user)
                                <option value="{{ $user->id }}" {{ request('pic') == $user->id ? 'selected' : '' }}>{{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Status:</label>
                        <select name="status" class="form-control form-control-sm border-primary">
                            <option value="">- Semua -</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Belum Selesai</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Sudah Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12 d-flex justify-content-start">
                        <button type="submit" class="btn btn-sm btn-primary px-4 shadow-sm mr-2">
                            <i class="fas fa-filter fa-sm mr-1"></i> Filter
                        </button>
                        <a href="#" id="resetFilter" class="btn btn-sm btn-secondary">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>KPI</th>
                            <th>Divisi</th>
                            <th>Pembuat</th>
                            <th>Penerima</th>
                            <th>Point</th>
                            <th>Mulai</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah KPI Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('kpi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="project" value="{{ $id }}">
                
                <div class="modal-body p-4" style="background-color: #f8f9fc;">
                    
                    <h6 class="text-primary font-weight-bold uppercase small mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi KPI</h6>
                    
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small">NAMA KPI</label>
                        <input type="text" name="nama_kpi" class="form-control shadow-sm border-0" placeholder="Masukkan nama KPI..." required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold text-dark small">CLIENT</label>
                            <select name="project" class="form-control custom-select shadow-sm border-0" required>
                                <option value="" selected disabled>-- Pilih Client --</option>
                                    <option value="{{ $client->id }}" {{ $client->id == $id ? 'selected' : '' }}>{{ $client->nama }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold text-dark small">DIVISI</label>
                            <select name="divisi" class="form-control custom-select shadow-sm border-0" onchange="HandleChangeDivsion(this, 'pic_select')" required>
                                <option value="" selected disabled>-- Pilih Divisi --</option>
                                @foreach($result as $divId => $nama)
                                    <option value="{{ $divId }}">{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold text-dark small">PIC (PERSON IN CHARGE)</label>
                            <select name="pic[]" id="pic_select" class="form-control custom-select shadow-sm border-0" multiple required>
                                </select>
                            <small class="text-muted">Gunakan Ctrl + Klik untuk memilih banyak PIC</small>
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold text-dark small">TINGKAT KESULITAN (POINT)</label>
                            <select name="point" class="form-control custom-select shadow-sm border-0" required>
                                <option value="" selected disabled>-- Pilih Tingkat Kesulitan --</option>
                                @foreach($difficultyLabels as $key => $label)
                                    <option value="{{ $key }}">{{ $key }} - {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold text-dark small">TANGGAL MULAI</label>
                            <input type="date" name="tanggal_mulai" class="form-control shadow-sm border-0" required>
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label class="font-weight-bold text-dark small">DEADLINE</label>
                            <input type="date" name="tanggal_deadline" class="form-control shadow-sm border-0" required>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small">CATATAN</label>
                        <textarea name="note" class="form-control shadow-sm border-0" rows="3" style="border-radius: 8px;" placeholder="Tambahkan rincian KPI..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-white border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light font-weight-bold px-4 text-muted" data-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-primary shadow-lg px-5 font-weight-bold" style="border-radius: 8px; letter-spacing: 1px;">
                        <i class="fas fa-save mr-2"></i>SIMPAN KPI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title font-weight-bold">Edit KPI</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body" id="editModalBody">
                    </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Update KPI</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="hapusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-weight-bold">Konfirmasi Hapus</h5>
            </div>
            <div class="modal-body">Yakin ingin menghapus KPI ini?</div>
            <div class="modal-footer">
                <form id="hapusForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('script')
<script>
    const baseUrl = window.location.origin;
    function loadTable() {
        const params = new URLSearchParams({
            start_date: $('input[name="start_date"]').val(),
            end_date: $('input[name="end_date"]').val(),
            divisi: $('select[name="divisi"]').val(),
            priority: $('select[name="priority"]').val(),
            pic: $('select[name="pic"]').val(),
            status: $('select[name="status"]').val(),
        });

        const apiUrl = `/api/kpi-data/{{ $client->id }}/{{ Auth::id() }}?${params.toString()}`;

        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(response) {
                // 1. Hancurkan DataTable jika sudah ada agar tidak error
                if ($.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable().destroy();
                }

                let tbody = $('#dataTable tbody');
                tbody.empty();

                let pointBelum = 0;
                let pointSelesai = 0;
                let totalPoint = 0;
                console.log(response.data);

                $.each(response.data, function(index, item) {
                    let status = (item.status == 1) ? 
                        '<span class="badge badge-success">Sudah Selesai</span>' : 
                        '<span class="badge badge-warning">Belum Selesai</span>';
                    
                    // Escaping data yang kompleks agar tidak merusak HTML
                    let itemEncoded = JSON.stringify(item).replace(/"/g, '&quot;');

                    totalPoint += Number(item.point);

                    if (item.status == 1) {
                        pointSelesai += Number(item.point);
                    } else {
                        pointBelum += Number(item.point);
                    }

                    let row = `<tr>
                        <td>${index + 1}</td>
                        <td>${item.nama_kpi}</td>
                        <td>${item.nama_divisi}</td>
                        <td>${item.pembuat}</td>
                        <td>${item.vic_email}</td>
                        <td>${item.point}</td>
                        <td>${item.tanggal_mulai}</td>
                        <td>${item.tanggal_deadline}</td>
                        <td>${status}</td>
                        <td>${item.note ?? ''}</td>
                        <td>
                            ${
                                item.status == 0
                                ? `
                                    <button class="btn btn-sm btn-info" onclick="openEditModal(${itemEncoded})">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <button class="btn btn-sm btn-success" onclick="selesaikanKPI(${item.id})">
                                        <i class="fas fa-check"></i> Selesai KPI
                                    </button>

                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal(${item.id})">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                `
                                : `
                                    <span class="badge badge-success p-2">
                                        <i class="fas fa-check-circle"></i> KPI Selesai
                                    </span>
                                `
                            }
                        </td>
                    </tr>`;
                    tbody.append(row);
                });

                $('#pointBelum').text(pointBelum);
                $('#pointSelesai').text(pointSelesai);
                $('#totalPoint').text(totalPoint);
                $('#totalKPI').text(response.data.length);

                // 2. Inisialisasi ulang DataTable setelah data masuk
                $('#dataTable').DataTable({
                    "responsive": true,
                    "autoWidth": false
                });
            }
        });
    }
    $(document).ready(function() {
        $('#filterForm').submit(function(e){
            e.preventDefault();
            loadTable();
        });
        $('#resetFilter').click(function(e){

            e.preventDefault();

            $('#filterForm')[0].reset();

            loadTable();

        });
        
        loadTable();
    });

    async function selesaikanKPI(id) {

        if (!confirm('Yakin ingin menyelesaikan KPI ini?')) {
            return;
        }

        try {

            const response = await fetch(`/kpi/${id}/selesai`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                alert('KPI berhasil diselesaikan.');
                loadTable();
            } else {
                alert(result.message);
            }

        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan.');
        }
    }

    function openEditModal(item) {
        // 1. Set form action ke route update
        $('#editForm').attr('action', '/kpi/' + item.id);
        
        // 2. Isi modal body dengan semua field
        $('#editModalBody').html(`
            <div class="row">
                <div class="col-md-12 form-group">
                    <label class="font-weight-bold small">NAMA KPI</label>
                    <input type="text" name="nama_kpi" class="form-control" value="${item.nama_kpi}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small">DIVISI</label>
                    <select name="divisi" onchange="HandleChangeDivsion(this, 'edit_pic_select')" class="form-control" required>
                        <option value="${item.divisi}" selected>${item.nama_divisi}</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small">PIC</label>
                    <select name="pic[]" id="edit_pic_select" class="form-control" multiple required>
                        </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small">POINT (KESULITAN)</label>
                    <select name="point" class="form-control" required>
                        ${generateDifficultyOptions(item.point)}
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small">STATUS</label>
                    <select name="status" class="form-control" required>
                        <option value="0" ${item.status == 0 ? 'selected' : ''}>Belum Selesai</option>
                        <option value="1" ${item.status == 1 ? 'selected' : ''}>Sudah Selesai</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small">MULAI</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="${item.tanggal_mulai}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label class="font-weight-bold small">DEADLINE</label>
                    <input type="date" name="tanggal_deadline" class="form-control" value="${item.tanggal_deadline}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="font-weight-bold small">CATATAN</label>
                <textarea name="note" class="form-control" rows="2">${item.note ?? ''}</textarea>
            </div>
        `);

        $('#edit_pic_select').select2({
            width: '100%',
            placeholder: "Pilih PIC"
        });
        
        $('#editModal').modal('show');

        let selectedPics = item.pics.map(pic => pic.id.toString());

        HandleChangeDivsion(
            { value: item.divisi },
            'edit_pic_select',
            selectedPics
        );
    }

    function generateDifficultyOptions(selectedPoint) {
        let options = '';
        for (let i = 1; i <= 5; i++) {
            options += `<option value="${i}" ${i == selectedPoint ? 'selected' : ''}>${i}</option>`;
        }
        return options;
    }

    function openDeleteModal(id) {
        $('#hapusForm').attr('action', '/kpi/' + id);
        $('#hapusModal').modal('show');
    }

    async function HandleChangeDivsion(el, targetId, selectedPic = []) {
        let $picSelect = $('#' + targetId);
        let userId = {{ Auth::id() }};

        $picSelect.empty();

        try {
            const response = await fetch(`${baseUrl}/api/get-users/${el.value}/${userId}`);
            if (!response.ok) throw new Error();

            const data = await response.json();

            data.forEach(pic => {

                let selected = selectedPic.includes(pic.id.toString());

                let option = new Option(
                    pic.email,
                    pic.id,
                    selected,
                    selected
                );

                $picSelect.append(option);
            });

            $picSelect.trigger('change');

        } catch(err){
            console.log(err);
        }
    }
    $(document).ready(function() {
        $('#pic_select').select2({
            placeholder: "-- Pilih PIC --",
            width: '100%'
        });
    });
</script>
@endpush
@endsection