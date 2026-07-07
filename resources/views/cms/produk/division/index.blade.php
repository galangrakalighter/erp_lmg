@include('partials.header')

<div class="card shadow">
    {{-- CARD HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Produk Jasa</h6>
        {{-- <a href="{{ route('create_produk_jasa') }}" class="btn btn-sm btn-primary">
            Tambah
        </a> --}}
        @if(Auth::user()->role != "digital_marketing")
        <button class="btn btn-primary" onclick="addProduk()">
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
                        <th>No</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Head</th>
                        <th>Specialist Manager</th>
                        <th>Specialist</th>

                        @if($role != "MCN Specialis")
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="produkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="produkForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Produk</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="produk_id">
                    <input type="hidden" id="divisi_id" value="{{ $divisi }}">

                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label class="font-weight-bold">Client</label>
                            <input type="text" name="client" id="client" class="form-control" placeholder="Contoh: Social Media Management" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="font-weight-bold">Status</label>
                            <select id="status" class="form-control">
                                <option value="1">Ditampilkan</option>
                                <option value="0">Disembunyikan</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row">
                        
                        <!-- Head (Penuh 1 baris) -->
                        <div class="col-12 form-group">
                            <label class="font-weight-bold text-danger">Head</label>
                            <select id="buh" class="form-control select2-multiple" multiple>
                                @foreach($buh as $u)
                                    <option value="{{ $u->user_id }}">{{ $u->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Specialist Manager (Setengah lebar) -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold text-primary">Specialist Manager</label>
                            <select id="mcn" class="form-control select2-multiple" multiple>
                                @foreach($mcn as $u)
                                    @php $parent = $u->userDivisions->first()->parent_id ?? 0; @endphp
                                    <option value="{{ $u->user_id }}" data-parent="{{ $parent }}">{{ $u->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Specialist (Setengah lebar) -->
                        <div class="col-md-6 form-group">
                            <label class="font-weight-bold text-info">Specialist</label>
                            <select id="ms" class="form-control select2-multiple" multiple>
                                @foreach($ms as $u)
                                    @php $parent = $u->userDivisions->first()->parent_id ?? 0; @endphp
                                    <option value="{{ $u->user_id }}" data-parent="{{ $parent }}">{{ $u->email }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">
                    Hapus Produk
                </h5>

            </div>

            <div class="modal-body">

                <input type="hidden" id="delete_id">

                <p>
                    Yakin ingin menghapus
                    <strong id="delete_nama"></strong> ?
                </p>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary"
                        data-dismiss="modal">
                    Batal
                </button>

                <button class="btn btn-danger"
                        onclick="confirmDelete()">
                    Hapus
                </button>

            </div>

        </div>

    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {

        $('.select2-multiple').select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: $('#produkModal')
        });

        loadData();

    });

    async function loadData() {

        const user = "{{ $user }}";
        const divisi = "{{ $divisi }}";

        const url = `{{ secure_url(route('client.getDataDivision', ['user' => $user, 'division' => $divisi])) }}`;

        try {

            const response = await fetch(url);
            const result = await response.json();

            $('#produkTable').DataTable({
                destroy: true,
                data: result.data,

                columns: [

                    {
                        data: null,
                        className: 'text-center',
                        render: (data, type, row, meta) => meta.row + 1
                    },

                    {
                        data: 'nama',
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
                        data: 'buh',
                        render: function (data) {
                            return renderBadges(data, 'badge-danger');
                        }
                    },

                    {
                        data: 'mcn',
                        render: function (data) {
                            return renderBadges(data, 'badge-primary');
                        }
                    },

                    {
                        data: 'ms',
                        render: function (data) {
                            return renderBadges(data, 'badge-info');
                        }
                    },

                    @if($role != "MCN Specialis")
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',

                        render: function (row) {

                            return `
                                <div class="btn-group">

                                    <button class="btn btn-warning btn-sm"
                                        onclick="editProdukDivisi(${row.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-danger btn-sm btn-delete"
                                            data-id="${row.id}"
                                            data-nama="${row.nama}">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                </div>
                            `;
                        }
                    }
                    @endif

                ]
            });

        } catch (err) {

            console.error(err);

        }

    }

    function renderBadges(data, badge) {

        if (!Array.isArray(data) || data.length == 0) {
            return '<span class="text-muted">-</span>';
        }

        return data.map(item => `
            <span class="badge ${badge} mr-1 mb-1">${item}</span>
        `).join('');

    }

    function addProduk() {

        $('#modalTitle').text('Tambah Produk');

        $('#produkForm')[0].reset();

        $('#produk_id').val('');

        $('.select2-multiple').val(null).trigger('change');

        $('#mcn option').prop('hidden', false);

        $('#ms option').prop('hidden', false);

        $('#produkModal').modal('show');

    }

    async function editProdukDivisi(id) {

        try {

            const response = await fetch(`{{ url('api/produk-get') }}/${id}`);
            const data = await response.json();

            console.log(data);

            $('#modalTitle').text('Edit Produk');

            $('#produk_id').val(data.id);

            $('#client').val(data.client_id);

            $('#status').val(data.status);

            // reset semua
            $('.select2-multiple').val(null).trigger('change');

            // tampilkan semua option
            $('#mcn option').prop('hidden', false);
            $('#ms option').prop('hidden', false);

            $('#produkModal').modal('show');

            // pilih HEAD
            $('#buh')
                .val(data.buh.map(String))
                .trigger('change');

            // setelah Manager muncul
            setTimeout(function () {

                $('#mcn')
                    .val(data.mcn.map(String))
                    .trigger('change');

                // setelah Specialist muncul
                setTimeout(function () {

                    $('#ms')
                        .val(data.ms.map(String))
                        .trigger('change');

                }, 150);

            }, 150);

        } catch (err) {

            console.error(err);

        }

    }

    $(document).on('click', '.btn-edit', function () {

        editProdukDivisi($(this).data('id'));

    });

    $(document).on('click', '.btn-delete', function () {

        $('#delete_id').val($(this).data('id'));

        $('#delete_nama').text($(this).data('nama'));

        $('#deleteModal').modal('show');

    });

    $('#produkForm').submit(async function (e) {

        e.preventDefault();

        const id = $('#produk_id').val();
        const divisi = $('#divisi_id').val();

        const url = id
            ? `/api/produk/${id}`
            : `/api/produk/${divisi}`;

        const method = id ? 'PUT' : 'POST';

        await fetch(url, {

            method,

            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },

            body: JSON.stringify({

                client: $('#client').val(),
                status: $('#status').val(),
                buh: $('#buh').val(),
                mcn: $('#mcn').val(),
                ms: $('#ms').val()

            })

        });

        $('#produkModal').modal('hide');

        loadData();

    });

    function deleteProduk(id, nama) {

        $('#delete_id').val(id);

        $('#delete_nama').text(nama);

        $('#deleteModal').modal('show');

    }

    async function confirmDelete() {

        const id = $('#delete_id').val();

        const response = await fetch(`/api/produk/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            $('#deleteModal').modal('hide');
            loadData();
        } else {
            alert(result.message);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER HEAD -> MANAGER
    |--------------------------------------------------------------------------
    */

    $('#buh').on('change', function () {

        let head = $(this).val() || [];

        $('#mcn option').each(function () {

            let parent = $(this).data('parent');

            $(this).prop('hidden', !head.includes(parent.toString()));

        });

        $('#mcn').val(null).trigger('change');

        $('#ms').val(null).trigger('change');

    });

    /*
    |--------------------------------------------------------------------------
    | FILTER MANAGER -> SPECIALIST
    |--------------------------------------------------------------------------
    */

    $('#mcn').on('change', function () {

        let manager = $(this).val() || [];

        $('#ms option').each(function () {

            let parent = $(this).data('parent');

            $(this).prop('hidden', !manager.includes(parent.toString()));

        });

        $('#ms').val(null).trigger('change');

    });
</script>

@include('partials.footer')