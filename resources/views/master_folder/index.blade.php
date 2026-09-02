@extends('layout.app')

@section('content')
<style>
    .folder-card { transition: all .3s cubic-bezier(.25,.8,.25,1); text-decoration: none !important; }
    .folder-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,.1) !important; }
    .folder-icon { width: 46px; height: 46px; display: grid; place-items: center; border-radius: 12px; background: #fff4d6; color: #f6c23e; font-size: 1.35rem; }
    .role-option { border: 1px solid #e3e6f0; border-radius: .4rem; padding: .65rem .75rem; }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <h1 class="h4 text-gray-800 font-weight-bold mb-0">{{ $title }}</h1>
        <p class="text-muted small mb-0 font-italic">Folder hanya tampil untuk jabatan yang diberi akses.</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center">
        @if($isSuperAdmin)
            <button class="btn btn-primary btn-sm shadow-sm px-3 py-2" data-toggle="modal" data-target="#createFolderModal">
                <i class="fas fa-folder-plus mr-1"></i> Folder Baru
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger shadow-sm" role="alert">
        <div class="font-weight-bold mb-1">Folder belum dapat disimpan:</div>
        <ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    @forelse($folders as $folder)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="folder-card card shadow-sm border-0 border-left-warning h-100">
                <a href="{{ route('master-folder.show', $folder) }}" class="text-decoration-none">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="folder-icon"><i class="fas fa-folder-open"></i></div>
                        <span class="badge badge-light border text-muted">Folder Master</span>
                    </div>
                    <h6 class="font-weight-bold text-gray-800 mb-1 text-truncate" title="{{ $folder->name }}">{{ $folder->name }}</h6>
                    <p class="small text-muted mb-3" style="min-height: 38px;">
                        {{ \Illuminate\Support\Str::limit($folder->description ?: 'Tanpa deskripsi', 70) }}
                    </p>
                    <hr class="my-2" style="border-top: 1px dashed #e3e6f0;">
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="small text-muted"><i class="fas fa-file mr-1"></i>{{ $folder->files_count }} file</span>
                        <span class="small text-primary">Buka <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                    <div class="mt-2">
                        @foreach($folder->roles->take(3) as $role)
                            <span class="badge badge-primary mr-1">{{ $role->role_name }} &middot; {{ $role->division->name ?? '-' }}</span>
                        @endforeach
                        @if($folder->roles->count() > 3)<span class="small text-muted">+{{ $folder->roles->count() - 3 }}</span>@endif
                    </div>
                </div>
                </a>
                @if($isSuperAdmin)
                <div class="card-footer bg-white border-0 pt-0 d-flex">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill mr-2" data-toggle="modal" data-target="#editFolderModal{{ $folder->id }}">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger flex-fill" data-toggle="modal" data-target="#deleteFolderModal{{ $folder->id }}">
                        <i class="fas fa-trash mr-1"></i>Hapus
                    </button>
                </div>
                @endif
            </div>
        </div>

        @if($isSuperAdmin)
        <div class="modal fade" id="editFolderModal{{ $folder->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form method="POST" action="{{ route('master-folder.update', $folder) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_folder_id" value="{{ $folder->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit text-primary mr-2"></i>Edit Folder</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-folder-name-{{ $folder->id }}" class="font-weight-bold">Nama Folder <span class="text-danger">*</span></label>
                            <input id="edit-folder-name-{{ $folder->id }}" name="name" value="{{ (int) old('edit_folder_id') === $folder->id ? old('name') : $folder->name }}" class="form-control" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-folder-description-{{ $folder->id }}" class="font-weight-bold">Deskripsi</label>
                            <textarea id="edit-folder-description-{{ $folder->id }}" name="description" class="form-control" rows="3" maxlength="1000">{{ (int) old('edit_folder_id') === $folder->id ? old('description') : $folder->description }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Role yang Dapat Melihat <span class="text-danger">*</span></label>
                            <p class="small text-muted">Pilih jabatan dari divisi pemilik maupun divisi lain.</p>
                            @php
                                $editRoleIds = (int) old('edit_folder_id') === $folder->id
                                    ? old('role_ids', [])
                                    : $folder->roles->pluck('id')->all();
                            @endphp
                            @foreach($rolesByDivision as $divisionId => $divisionRoles)
                                <div class="border rounded p-3 mb-3">
                                    <div class="small font-weight-bold text-primary text-uppercase mb-2">{{ $divisionRoles->first()->division->name ?? 'Divisi' }}</div>
                                    <div class="row">
                                        @foreach($divisionRoles as $role)
                                            <div class="col-md-6 mb-2">
                                                <label class="role-option d-flex align-items-center mb-0 w-100">
                                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mr-2" @checked(in_array($role->id, $editRoleIds))>
                                                    <span>{{ $role->role_name }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="modal fade" id="deleteFolderModal{{ $folder->id }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="POST" action="{{ route('master-folder.destroy', $folder) }}" class="modal-content">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Hapus Folder</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus folder <strong>{{ $folder->name }}</strong>?</p>
                        <div class="alert alert-warning mb-0 small">Seluruh {{ $folder->files_count }} file di dalam folder ini juga akan dihapus dan tidak dapat dipulihkan.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i>Ya, Hapus Folder</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body text-muted">
                    <i class="fas fa-folder-open fa-3x text-warning mb-3"></i>
                    <h6 class="font-weight-bold text-gray-700">Belum ada folder yang dapat Anda lihat</h6>
                    <p class="small mb-0">{{ $isSuperAdmin ? 'Buat folder baru untuk memulai.' : 'Admin belum memberikan akses folder kepada jabatan Anda.' }}</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($isSuperAdmin)
<div class="modal fade" id="createFolderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form method="POST" action="{{ route('master-folder.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-folder-plus text-warning mr-2"></i>Buat Folder Baru</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="folder-name" class="font-weight-bold">Nama Folder <span class="text-danger">*</span></label>
                    <input id="folder-name" name="name" value="{{ old('name') }}" class="form-control" maxlength="150" required placeholder="Contoh: SOP Divisi">
                </div>
                <div class="form-group">
                    <label for="folder-description" class="font-weight-bold">Deskripsi</label>
                    <textarea id="folder-description" name="description" class="form-control" rows="3" maxlength="1000" placeholder="Jelaskan isi folder secara singkat">{{ old('description') }}</textarea>
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">Role yang Dapat Melihat <span class="text-danger">*</span></label>
                    <p class="small text-muted">Pilih jabatan dari divisi mana pun yang dapat melihat folder ini.</p>
                    @foreach($rolesByDivision as $divisionId => $divisionRoles)
                        <div class="border rounded p-3 mb-3">
                            <div class="small font-weight-bold text-primary text-uppercase mb-2">{{ $divisionRoles->first()->division->name ?? 'Divisi' }}</div>
                            <div class="row">
                                @foreach($divisionRoles as $role)
                                    <div class="col-md-6 mb-2">
                                        <label class="role-option d-flex align-items-center mb-0 w-100">
                                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mr-2" @checked(in_array($role->id, old('role_ids', [])))>
                                            <span>{{ $role->role_name }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Simpan Folder</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($isSuperAdmin && $errors->any() && old('edit_folder_id'))
        $('#editFolderModal{{ (int) old('edit_folder_id') }}').modal('show');
    @elseif($isSuperAdmin && $errors->any())
        $('#createFolderModal').modal('show');
    @endif
});
</script>
@endsection
