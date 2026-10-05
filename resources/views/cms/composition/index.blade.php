@extends('layout.app')
@section('content')
<div class="division-cms-page">
    @include('cms.produk.partials.division_theme')
    @php($canManage = in_array($role, ['Digital Marketer', 'Busines Unit Head', 'Super Admin']))

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Komposisi</h6>
            @if($canManage)
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Komposisi
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable">
                    <thead class="thead-light"><tr><th>No</th><th>Nama</th><th>Fungsi Singkat</th><th>Contoh</th><th>Status</th>@if($canManage)<th>Aksi</th>@endif</tr></thead>
                    <tbody>
                    @foreach($data as $item)
                        <tr>
                            <th>{{ $loop->iteration }}</th>
                            <th>{{ $item->nama }}</th>
                            <td>{{ $item->fungsi_singkat ?: '-' }}</td>
                            <td>{{ $item->contoh ?: '-' }}</td>
                            <th>{{ $item->status == 1 ? 'Ditampilkan' : 'Disembunyikan' }}</th>
                            @if($canManage)
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#editModal_{{ $item->id }}">Edit</button>
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal_{{ $item->id }}">Hapus</button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($canManage)
    <div class="modal fade" id="tambahModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
            <form action="{{ route('composition_create', $activeProductCode) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Tambah Komposisi</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <div class="space-y-6 px-6 py-6 sm:px-7">
                    <div><label for="nama" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900">Nama Komposisi</label><input type="text" name="nama" id="nama" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5" required></div>
                    @include('cms.partials.content_guidance_fields')
                    <div><label for="status" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900">Status</label><select name="status" id="status" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none" required><option value="1">Ditampilkan</option><option value="2">Disembunyikan</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div></div>
    </div>

    @foreach($data as $item)
        <div class="modal fade" id="editModal_{{ $item->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
            <form action="{{ route('composition_edit', $item->id) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Edit Komposisi</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                <div class="space-y-6 px-6 py-6 sm:px-7">
                    <div><label for="nama_{{ $item->id }}" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900">Nama Komposisi</label><input type="text" name="nama" id="nama_{{ $item->id }}" value="{{ $item->nama }}" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none" required></div>
                    @include('cms.partials.content_guidance_fields', ['item' => $item])
                    <div><label for="status_{{ $item->id }}" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900">Status</label><select name="status" id="status_{{ $item->id }}" class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none" required><option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Ditampilkan</option><option value="2" {{ $item->status == 2 ? 'selected' : '' }}>Disembunyikan</option></select></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div></div></div>
        <div class="modal fade" id="deleteModal_{{ $item->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form action="{{ route('composition_delete', $item->id) }}" method="POST">@csrf
                <div class="modal-header bg-danger text-white"><h5 class="modal-title">Hapus Komposisi</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
                <div class="modal-body text-center"><p class="mb-2">Apakah kamu yakin ingin menghapus</p><strong>{{ $item->nama }}</strong>?<p class="text-muted mt-2">Data yang dihapus tidak dapat dikembalikan.</p></div>
                <div class="modal-footer justify-content-center"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Ya, Hapus</button></div>
            </form>
        </div></div></div>
    @endforeach
    @endif
</div>
@endsection
