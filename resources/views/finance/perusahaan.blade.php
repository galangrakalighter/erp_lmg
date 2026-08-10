@extends('layout.app')
@section('content')

<div class="card shadow">
    {{-- CARD HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Produk Perusahaan</h6>
        <button class="btn btn-primary" onclick="addPerusahaan()">
            <i class="fas fa-plus"></i> Tambah
        </button>
    </div>

    {{-- CARD BODY --}}
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="perusahaanTable" style="width:100%">
                <thead class="thead-light text-center">
                    <tr>
                        <th width="10%">No</th>
                        <th>Perusahaan</th>
                        <th width="20%">Status</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= MODAL TAMBAH & EDIT ================= --}}
<div class="modal fade" id="produkModal" tabindex="-1" role="dialog" aria-labelledby="produkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="produkForm">
                @csrf
                <input type="hidden" id="produk_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Produk Perusahaan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_perusahaan">Nama Perusahaan</label>
                        <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" required placeholder="Masukkan nama perusahaan">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="1">Ditampilkan</option>
                            <option value="0">Disembunyikan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= MODAL HAPUS ================= --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data perusahaan <span id="delete_nama" class="font-weight-bold"></span>?</p>
                <input type="hidden" id="delete_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="deleteDataConfirm()">Hapus</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let isEditing = false;
    $(document).ready(function () {
        loadData();

        // Submit form untuk Tambah / Edit
        $('#produkForm').on('submit', function (e) {
            e.preventDefault();
            savePerusahaan();
        });
    });

    async function loadData() {
        // Menggunakan helper route() bawaan Laravel di JS atau url manual /perusahaan/get-data
        const url = `{{ route('perusahaan.getDataPerusahaan') }}`;

        try {
            const response = await fetch(url);
            const result = await response.json();

            $('#perusahaanTable').DataTable({
                destroy: true,
                data: result.data,
                columns: [
                    {
                        data: null,
                        className: 'text-center',
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    {
                        data: 'nama_perusahaan',
                        className: 'font-weight-bold'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function (data) {
                            return data == 1
                                ? '<span class="badge badge-success">Ditampilkan</span>'
                                : '<span class="badge badge-secondary">Disembunyikan</span>';
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            return `
                                <button class="btn btn-sm btn-warning text-white" onclick='editPerusahaan(${JSON.stringify(row)})'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" onclick='deletePerusahaan(${row.id}, "${row.nama_perusahaan}")'>
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            `;
                        }
                    }
                ]
            });

        } catch (err) {
            console.error(err);
        }
    }

    // Fungsi Buka Modal Tambah
    function addPerusahaan() {
        isEditing = false;
        $('#produkForm')[0].reset();
        $('#produk_id').val('');
        $('#modalTitle').text('Tambah Produk Perusahaan');
        $('#saveBtn').text('Simpan');
        $('#produkModal').modal('show');
    }

    // Fungsi Buka Modal Edit
    function editPerusahaan(row) {
        isEditing = true;
        console.log(row);
        $('#produkForm')[0].reset();
        $('#produk_id').val(row.id);
        $('#nama_perusahaan').val(row.nama_perusahaan);
        let statusValue = (row.status === true || row.status == 1) ? "1" : "0";
        $('#status').val(statusValue);
        $('#modalTitle').text('Edit Produk Perusahaan');
        $('#saveBtn').text('Update');
        $('#produkModal').modal('show');
    }

    // Fungsi Simpan (Tambah/Edit) via AJAX menyesuaikan Resource Route
    async function savePerusahaan() {
        let id = $('#produk_id').val();
        
        // Jika isEditing true -> arahkan ke URL PUT /perusahaan/{id}
        // Jika false -> arahkan ke URL POST /perusahaan
        let url = isEditing ? `/perusahaan/${id}` : `{{ route('perusahaan.store') }}`;

        let formData = {
            _token: '{{ csrf_token() }}',
            nama_perusahaan: $('#nama_perusahaan').val(),
            status: $('#status').val()
        };

        try {
            let response = await fetch(url, {
                method: isEditing ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            let result = await response.json();

            if (response.ok) {
                $('#produkModal').modal('hide');
                loadData();
            } else {
                alert(result.message || 'Terjadi kesalahan saat menyimpan data.');
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Fungsi Buka Modal Hapus
    function deletePerusahaan(id, nama) {
        $('#delete_id').val(id);
        $('#delete_nama').text(nama);
        $('#deleteModal').modal('show');
    }

    // Fungsi Konfirmasi Hapus via AJAX menyesuaikan Resource Route
    async function deleteDataConfirm() {
        let id = $('#delete_id').val();
        let url = `/perusahaan/${id}`; // Mengarah ke method destroy resource

        try {
            let response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (response.ok) {
                $('#deleteModal').modal('hide');
                loadData();
            } else {
                alert('Gagal menghapus data.');
            }
        } catch (err) {
            console.error(err);
        }
    }
</script>

@endsection