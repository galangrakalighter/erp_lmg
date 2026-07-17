@extends('layout.app')
@section('content')
    <div class="card shadow">
        {{-- CARD HEADER --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Jenis Body</h6>
            {{-- <a href="{{ route('create_status_jasa') }}" class="btn btn-sm btn-primary">
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
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Status</th>
                            @if($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
                            <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr>
                                <th>{{ $loop->iteration }}</th>
                                <th>{{ $item->nama_body }}</th>
                                <th>{{ $item->status == 1 ? "Ditampilkan" : "Disembunyikan"}}</th>
                                @if($role == 'Digital Marketer' || $role == 'Busines Unit Head' || $role == 'Super Admin')
                                <th>
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal_{{ $item->id }}">Edit</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $item->id }}">Hapus</button>
                                </th>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <form action="{{ route('body_create', $activeProductCode) }}" method="POST">
                    @csrf

                    {{-- MODAL HEADER --}}
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Jenis Body</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    {{-- MODAL BODY --}}
                    <div class="modal-body">

                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1">Ditampilkan</option>
                                <option value="2">Disembunyikan</option>
                            </select>
                        </div>
                    </div>

                    {{-- MODAL FOOTER --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @foreach ($data as $item)
    {{-- Modal Edit --}}
    <div class="modal fade" id="editModal_{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <form action="{{ route('body_edit', $item->id) }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Jenis Body</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Jenis Body</label>
                            <input type="text" name="nama" class="form-control"
                                value="{{ $item->nama_body }}">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $item->status ? 'selected' : '' }}>Ditampilkan</option>
                                <option value="2" {{ !$item->status ? 'selected' : '' }}>Disembunyikan</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button class="btn btn-primary">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- Modal Hapus --}}
    <div class="modal fade" id="deleteModal_{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <form action="{{ route('body_delete', $item->id) }}" method="POST">
                    @csrf
                    {{-- HEADER --}}
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Hapus Body</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            &times;
                        </button>
                    </div>

                    {{-- BODY --}}
                    <div class="modal-body text-center">
                        <p class="mb-2">
                            Apakah kamu yakin ingin menghapus
                        </p>
                        <strong>{{ $item->nama_body }}</strong>?
                        <p class="text-muted mt-2">
                            Data yang dihapus tidak dapat dikembalikan.
                        </p>
                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <button class="btn btn-danger">
                            Ya, Hapus
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
    @endforeach

@endsection