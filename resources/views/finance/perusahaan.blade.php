@extends('layout.app')
@section('content')

<div class="card shadow">
    {{-- CARD HEADER --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Produk Perusahaan</h6>
        <button class="btn btn-primary" onclick="addProduk()">
            Tambah
        </button>
    </div>

    {{-- CARD BODY --}}
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="perusahaanTable" style="width:100%">
                <thead class="thead-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Perusahaan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    let isEditing = false;
    $(document).ready(function () {

        $('.select2-multiple').select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: $('#produkModal')
        });

        loadData();

    });

    async function loadData() {

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
                    }
                ]
            });

        } catch (err) {
            console.error(err);
        }

    }
</script>

@endsection