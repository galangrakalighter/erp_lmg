@extends('layout.app')

@section('content')
<style>
    #masterPdfViewer { position:fixed; inset:0; width:100%; height:100%; background:#f2f3f5; z-index:99999; display:none; }
    .master-pdf-header { height:65px; background:#fff; display:flex; align-items:center; justify-content:space-between; gap:20px; padding:0 25px; border-bottom:1px solid #ddd; }
    .master-pdf-header-left, .master-pdf-header-right { display:flex; align-items:center; gap:12px; }
    #masterPdfBody { overflow:auto; height:calc(100vh - 65px); padding:35px; display:flex; flex-direction:column; align-items:center; background:#d9d9d9; }
    .master-pdf-page { background:#fff; margin-bottom:25px; box-shadow:0 2px 12px rgba(0,0,0,.18); max-width:none; }
    .pdf-file-link { color:#4e73df; cursor:pointer; text-decoration:none; }
    .pdf-file-link:hover { color:#224abe; text-decoration:underline; }
    .document-pages { display:flex; flex-direction:column; align-items:center; gap:28px; min-width:794px; }
    .document-page-wrap { width:794px; max-width:794px; }
    .document-page-heading { display:flex; justify-content:space-between; align-items:center; margin-bottom:7px; color:#5a6472; font-size:12px; font-weight:600; }
    .document-page { box-sizing:border-box; width:794px; height:1123px; padding:70px; background:#fff; color:#252a34; line-height:1.65; outline:none; overflow:hidden; overflow-wrap:anywhere; }
    .document-page.is-active { box-shadow:0 0 0 2px rgba(78,115,223,.3), 0 .125rem .25rem rgba(0,0,0,.075); }
    .document-page img { max-width:100%; }
    #createDocumentModal.document-fullscreen {
        padding: 0 !important;
    }

    #createDocumentModal.document-fullscreen .modal-dialog {
        width: 100%;
        max-width: none;
        height: 100vh;
        margin: 0;
    }

    #createDocumentModal.document-fullscreen .modal-content {
        height: 100vh;
        border: 0;
        border-radius: 0;
    }

    #createDocumentModal.document-fullscreen .modal-body {
        flex: 1;
        overflow: hidden;
    }

    #createDocumentModal.document-fullscreen .editor-stage {
        height: calc(100vh - 230px) !important;
        max-height: none !important;
    }

    #createDocumentModal.document-fullscreen .document-page {
        min-height: calc(100vh - 280px);
    }

    .document-single-page .document-page {
        width: 100%;
        min-height: 1000px;
        background: #fff;
        padding: 40px;
        overflow: visible;
    }

    .document-single-page {
        width: 100%;
    }

    /*
     * KERTAS EDITOR
     */
    .document-single-page {
        width: 100%;
        overflow: hidden;
        min-height: 100%;
    }

    .document-single-page .document-page {
        width: 100%;
        height: auto;
        min-height: 100%;
        max-width: 100%;
        box-sizing: border-box;

        background: #fff;
        padding: 40px;

        overflow-x: hidden;
        overflow-y: visible;

        overflow-wrap: anywhere;
        word-break: normal;

        outline: none;
    }

    .document-editor-workspace.editor-only-fullscreen {
        position: fixed !important;
        inset: 0 !important;
        z-index: 100000 !important;
        width: 100vw !important;
        height: 100vh !important;
        max-height: none !important;
        margin: 0 !important;
        padding: 16px !important;
        border: 0 !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        background: #dfe3e8;
        display: flex;
        flex-direction: column;
    }

    .editor-fullscreen-exit {
        display: none;
        position: fixed;
        top: 14px;
        right: 18px;
        z-index: 100002;
    }

    .document-editor-workspace.editor-only-fullscreen .editor-fullscreen-exit {
        display: inline-flex;
    }

    .document-editor-workspace.editor-only-fullscreen .document-toolbar {
        flex: 0 0 auto;
        padding-right: 155px !important;
    }

    .document-editor-workspace.editor-only-fullscreen .editor-stage {
        flex: 1 1 auto;
        height: auto !important;
        max-height: none !important;
        min-height: 0;
    }

    .document-editor-workspace.editor-only-fullscreen .document-single-page {
        min-height: 100%;
    }


    /*
     * SEMUA ISI DI DALAM KERTAS
     */
    .document-single-page .document-page * {
        max-width: 100%;
        box-sizing: border-box;
    }


    /*
     * TEKS PANJANG / URL PANJANG
     */
    .document-single-page .document-page p,
    .document-single-page .document-page div,
    .document-single-page .document-page span,
    .document-single-page .document-page td,
    .document-single-page .document-page th {
        overflow-wrap: anywhere;
        word-break: normal;
    }


    /*
     * GAMBAR
     */
    .document-single-page .document-page img {
        max-width: 100% !important;
        height: auto !important;
    }


    /*
     * TABEL DARI WORD
     */
    .document-single-page .document-page table {
        width: auto;
        max-width: 100% !important;
        table-layout: auto;
    }

    .document-single-page .document-page td,
    .document-single-page .document-page th {
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: normal;
    }


    /*
     * VIDEO / IFRAME / EMBED
     */
    .document-single-page .document-page iframe,
    .document-single-page .document-page video,
    .document-single-page .document-page embed,
    .document-single-page .document-page object {
        max-width: 100% !important;
    }


    /*
     * FULLSCREEN EDIT
     */
    [id^="editFileModal"].document-fullscreen {
        padding: 0 !important;
    }

    [id^="editFileModal"].document-fullscreen .modal-dialog {
        width: 100%;
        max-width: none;
        height: 100vh;
        margin: 0;
    }

    [id^="editFileModal"].document-fullscreen .modal-content {
        height: 100vh;
        border: 0;
        border-radius: 0;
    }

    [id^="editFileModal"].document-fullscreen .modal-body {
        flex: 1;
        overflow: hidden;
    }

    [id^="editFileModal"].document-fullscreen
    .edit-document-editor-container {
        height: calc(100vh - 260px) !important;
        max-height: none !important;
    }

    [id^="editFileModal"].document-fullscreen
    .document-page {
        min-height: calc(100vh - 320px);
    }

    body.document-editor-fullscreen-open {
        overflow: hidden !important;
    }
</style>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <a href="{{ route('master-folder.index') }}" class="btn btn-sm btn-light border shadow-sm mb-3">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Folder Master
        </a>
        <h1 class="h4 text-gray-800 font-weight-bold mb-1"><i class="fas fa-folder-open text-warning mr-2"></i>{{ $folder->name }}</h1>
        <p class="text-muted small mb-0">{{ $folder->description ?: 'Tanpa deskripsi' }}</p>
    </div>
    @if($canManage)
    <div class="mt-3 mt-md-0">
        <button class="btn btn-success btn-sm shadow-sm px-3 py-2 mr-2" data-toggle="modal" data-target="#createDocumentModal">
            <i class="fas fa-file-alt mr-1"></i> Buat Dokumen
        </button>
        <button class="btn btn-primary btn-sm shadow-sm px-3 py-2" data-toggle="modal" data-target="#uploadFileModal">
            <i class="fas fa-cloud-upload-alt mr-1"></i> Upload File
        </button>
    </div>
    @else
        <span class="badge badge-light border px-3 py-2 mt-3 mt-md-0"><i class="fas fa-eye mr-1"></i>Akses lihat saja</span>
    @endif
</div>

@php
    $primaryRoles = $folder->roles->where('division_id', $folder->division_id)->values();
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar File</h6>
        <div class="d-flex flex-wrap align-items-center mt-2 mt-md-0">
            <form method="GET" action="{{ route('master-folder.show', $folder) }}" class="form-inline mr-2 mb-1 mb-md-0">
                <label for="primary-role-filter" class="small text-muted mr-2 mb-0">Jabatan Utama</label>
                <select id="primary-role-filter" name="primary_role_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Semua Jabatan</option>
                    @foreach($primaryRoles as $role)
                        <option value="{{ $role->id }}" @selected((int) $selectedPrimaryRoleId === (int) $role->id)>{{ $role->role_name }}</option>
                    @endforeach
                </select>
                @if($selectedPrimaryRoleId)
                    <a href="{{ route('master-folder.show', $folder) }}" class="btn btn-sm btn-light border ml-2" title="Hapus filter"><i class="fas fa-times"></i></a>
                @endif
            </form>
            <span class="badge badge-light border">{{ $folder->files->count() }} file</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light"><tr><th class="pl-4">Nama File</th><th>Jabatan Utama</th><th>Akses Role</th><th>Ukuran</th><th>Pengunggah</th><th>Tanggal</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                @forelse($folder->files->sortByDesc('created_at') as $file)
                    <tr>
                        <td class="pl-4 align-middle">
                            <i class="fas fa-file-{{ strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf' || $file->mime_type === 'application/pdf' ? 'pdf text-danger' : 'alt text-primary' }} mr-2"></i>
                            @if(strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf' || $file->mime_type === 'application/pdf')
                                <a href="{{ route('master-folder.files.view', ['folder' => $folder, 'file' => $file->original_name]) }}" class="font-weight-bold pdf-file-link">{{ $file->original_name }}</a>
                            @else
                                <span class="font-weight-bold text-gray-800">{{ $file->original_name }}</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($file->primaryRole)
                                <span class="badge badge-warning">{{ $file->primaryRole->role_name }}</span>
                            @else
                                <span class="text-muted small">Belum ditentukan</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            @forelse($file->roles as $role)
                                <span class="badge badge-primary mr-1 mb-1">{{ $role->role_name }} &middot; {{ $role->division->name ?? '-' }}</span>
                            @empty
                                <span class="badge badge-secondary">Hanya pemilik</span>
                            @endforelse
                        </td>
                        <td class="align-middle text-muted small">{{ number_format($file->size / 1024, $file->size < 1048576 ? 1 : 0, ',', '.') }} KB</td>
                        <td class="align-middle text-muted small">{{ $file->uploader->name ?? '-' }}</td>
                        <td class="align-middle text-muted small">{{ $file->created_at->format('d M Y, H:i') }}</td>
                        <td class="align-middle text-center">
                            <a href="{{ route('master-folder.files.download', [$folder, $file]) }}" class="btn btn-sm btn-outline-primary" title="Download"><i class="fas fa-download"></i></a>
                            @if($canManage)
                            <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#editFileModal{{ $file->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteFileModal{{ $file->id }}" title="Hapus"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-cloud-upload-alt fa-3x mb-3 text-gray-300 d-block"></i>Belum ada file yang dapat Anda lihat di folder ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManage)
@foreach($folder->files as $file)
    <div class="modal fade"
        id="editFileModal{{ $file->id }}"
        tabindex="-1"
        role="dialog"
        aria-hidden="true">

        <div class="modal-dialog {{ $file->is_generated_document ? 'modal-xl' : 'modal-lg' }}"
            role="document">

            <form id="edit-file-form-{{ $file->id }}"
                method="POST"
                action="{{ route('master-folder.files.update', [$folder, $file]) }}"
                class="modal-content edit-file-form"
                data-file-id="{{ $file->id }}">

                @csrf
                @method('PUT')

                <input type="hidden"
                    name="edit_file_id"
                    value="{{ $file->id }}">

                @if($file->is_generated_document)
                    <input type="hidden"
                        id="edit-file-content-{{ $file->id }}"
                        name="content">
                @endif

                {{-- HEADER --}}
                <div class="modal-header">

                    <div>
                        <h5 class="modal-title font-weight-bold">
                            <i class="fas fa-edit text-warning mr-2"></i>

                            Edit
                            {{ $file->is_generated_document ? 'Dokumen' : 'File' }}
                        </h5>

                        <small class="text-muted">
                            {{ $file->is_generated_document
                                ? 'Perubahan isi akan menghasilkan ulang file PDF.'
                                : 'File asli tidak diubah, hanya nama dan aksesnya.' }}
                        </small>
                    </div>

                    <button type="button"
                            class="close"
                            data-dismiss="modal">

                        <span>&times;</span>
                    </button>

                </div>


                {{-- BODY --}}
                <div class="modal-body {{ $file->is_generated_document ? 'bg-light' : '' }}">

                    <div class="form-group">
                        <label for="edit-primary-role-{{ $file->id }}" class="font-weight-bold">Jabatan Utama <span class="text-danger">*</span></label>
                        <select id="edit-primary-role-{{ $file->id }}" name="primary_role_id" class="form-control" required>
                            <option value="">Pilih jabatan utama</option>
                            @foreach($primaryRoles as $role)
                                <option value="{{ $role->id }}" @selected((int) ((int) old('edit_file_id') === $file->id ? old('primary_role_id') : $file->primary_role_id) === (int) $role->id)>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Jabatan utama berasal dari divisi {{ $folder->division->name ?? 'utama folder' }} dan otomatis mendapat akses file.</small>
                    </div>

                    {{-- NAMA + ROLE --}}
                    <div class="form-row mb-3">

                        {{-- NAMA FILE --}}
                        <div class="col-md-7">

                            <label for="edit-file-name-{{ $file->id }}"
                                class="font-weight-bold">

                                Nama File
                                <span class="text-danger">*</span>

                            </label>

                            @if($file->is_generated_document)

                                <div class="input-group">

                                    <input id="edit-file-name-{{ $file->id }}"
                                        name="file_name"
                                        value="{{ (int) old('edit_file_id') === $file->id
                                            ? preg_replace('/\.(pdf)$/i', '', old('file_name'))
                                            : preg_replace('/\.(pdf)$/i', '', $file->original_name) }}"
                                        class="form-control"
                                        maxlength="150"
                                        required>

                                    <div class="input-group-append">

                                        <span class="input-group-text font-weight-bold">
                                            .pdf
                                        </span>

                                    </div>

                                </div>

                            @else

                                <input id="edit-file-name-{{ $file->id }}"
                                    name="file_name"
                                    value="{{ (int) old('edit_file_id') === $file->id
                                        ? old('file_name')
                                        : $file->original_name }}"
                                    class="form-control"
                                    maxlength="150"
                                    required>

                            @endif

                        </div>


                        {{-- ROLE --}}
                        <div class="col-md-5">

                            <label class="font-weight-bold">

                                Role yang Dapat Melihat
                                <span class="text-danger">*</span>

                            </label>

                            <div class="d-flex flex-wrap">

                                @php
                                    $selectedFileRoles = (int) old('edit_file_id') === $file->id
                                        ? old('role_ids', [])
                                        : $file->roles->pluck('id')->all();
                                @endphp

                                @foreach($folder->roles as $role)

                                    <label class="border bg-white rounded py-2 px-3 mr-2 mb-1">

                                        <input type="checkbox"
                                            name="role_ids[]"
                                            value="{{ $role->id }}"
                                            class="mr-1"
                                            @checked(in_array($role->id, $selectedFileRoles))>

                                        {{ $role->role_name }}
                                        ({{ $role->division->name ?? '-' }})

                                    </label>

                                @endforeach

                            </div>

                        </div>

                    </div>


                    @if($file->is_generated_document)

                        {{-- TOOLBAR --}}
                        <div class="bg-white border rounded-top p-2">

                            <div class="d-flex flex-wrap align-items-center">

                                {{-- FORMAT BLOCK --}}
                                <select class="form-control form-control-sm mr-2 mb-1"
                                        style="width:145px"
                                        onchange="
                                            editDocumentCommand(
                                                {{ $file->id }},
                                                'formatBlock',
                                                this.value
                                            );
                                            this.value='p';
                                        ">

                                    <option value="p">
                                        Paragraf
                                    </option>

                                    <option value="h1">
                                        Judul 1
                                    </option>

                                    <option value="h2">
                                        Judul 2
                                    </option>

                                    <option value="h3">
                                        Judul 3
                                    </option>

                                </select>


                                {{-- TEXT STYLE --}}
                                <div class="btn-group btn-group-sm mr-2 mb-1">

                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'bold'
                                                )
                                            "
                                            title="Bold">

                                        <i class="fas fa-bold"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'italic'
                                                )
                                            "
                                            title="Italic">

                                        <i class="fas fa-italic"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'underline'
                                                )
                                            "
                                            title="Underline">

                                        <i class="fas fa-underline"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'strikeThrough'
                                                )
                                            "
                                            title="Strikethrough">

                                        <i class="fas fa-strikethrough"></i>

                                    </button>

                                </div>


                                {{-- ALIGNMENT --}}
                                <div class="btn-group btn-group-sm mr-2 mb-1">

                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'justifyLeft'
                                                )
                                            "
                                            title="Rata kiri">

                                        <i class="fas fa-align-left"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'justifyCenter'
                                                )
                                            "
                                            title="Rata tengah">

                                        <i class="fas fa-align-center"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'justifyRight'
                                                )
                                            "
                                            title="Rata kanan">

                                        <i class="fas fa-align-right"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'justifyFull'
                                                )
                                            "
                                            title="Rata kiri-kanan">

                                        <i class="fas fa-align-justify"></i>

                                    </button>

                                </div>


                                {{-- LIST + LINK --}}
                                <div class="btn-group btn-group-sm mr-2 mb-1">

                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                insertDocumentLink(
                                                    'edit-file-editor-{{ $file->id }}'
                                                )
                                            "
                                            title="Sisipkan link">

                                        <i class="fas fa-link"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'insertUnorderedList'
                                                )
                                            "
                                            title="Bullet list">

                                        <i class="fas fa-list-ul"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'insertOrderedList'
                                                )
                                            "
                                            title="Numbered list">

                                        <i class="fas fa-list-ol"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'undo'
                                                )
                                            "
                                            title="Undo">

                                        <i class="fas fa-undo"></i>

                                    </button>


                                    <button type="button"
                                            class="btn btn-light border"
                                            onclick="
                                                editDocumentCommand(
                                                    {{ $file->id }},
                                                    'redo'
                                                )
                                            "
                                            title="Redo">

                                        <i class="fas fa-redo"></i>

                                    </button>

                                </div>


                                {{-- FULLSCREEN --}}
                                <button type="button"
                                        class="btn btn-light border btn-sm mb-1 ml-auto"
                                        id="edit-document-fullscreen-btn-{{ $file->id }}"
                                        onclick="
                                            toggleEditDocumentFullscreen(
                                                {{ $file->id }}
                                            )
                                        "
                                        title="Fullscreen">

                                    <i class="fas fa-expand"></i>

                                    <span class="ml-1">
                                        Fullscreen
                                    </span>

                                </button>

                            </div>

                        </div>


                        {{-- EDITOR --}}
                        <div class="edit-document-editor-container border border-top-0 rounded-bottom p-4"
                            style="
                                background:#dfe3e8;
                                height:600px;
                                overflow:auto;
                            ">

                            <div id="edit-file-editor-{{ $file->id }}"
                                class="document-pages document-single-page">

                                <div contenteditable="true"
                                    class="document-page shadow-sm">

                                    @if((int) old('edit_file_id') === $file->id)

                                        {!! old('content') !!}

                                    @else

                                        {!! $file->editor_content !!}

                                    @endif

                                </div>

                            </div>

                        </div>

                    @endif

                </div>


                {{-- FOOTER --}}
                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light border"
                            data-dismiss="modal">

                        Batal

                    </button>


                    <button type="submit"
                            class="btn btn-warning">

                        <i class="fas fa-save mr-1"></i>

                        Simpan Perubahan

                    </button>

                </div>

            </form>

        </div>

    </div>

    <div class="modal fade" id="deleteFileModal{{ $file->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('master-folder.files.destroy', [$folder, $file]) }}" class="modal-content">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Hapus File</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Yakin ingin menghapus <strong>{{ $file->original_name }}</strong>?</p>
                    <div class="alert alert-warning small mb-0">File akan dihapus permanen dan tidak dapat dipulihkan.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i>Ya, Hapus File</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endif

<div id="masterPdfViewer">
    <div class="master-pdf-header">
        <div class="master-pdf-header-left">
            <button type="button" id="masterPdfBack" class="btn btn-light border"><i class="fas fa-arrow-left"></i></button>
            <strong id="masterPdfTitle" class="text-truncate"></strong>
        </div>
        <div class="master-pdf-header-right">
            <button type="button" id="masterPdfZoomOut" class="btn btn-light border" title="Perkecil"><i class="fas fa-minus"></i></button>
            <span id="masterPdfZoomLabel" class="small font-weight-bold" style="min-width:48px;text-align:center">135%</span>
            <button type="button" id="masterPdfZoomIn" class="btn btn-light border" title="Perbesar"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div id="masterPdfBody"></div>
</div>

@if($canManage)
<div class="modal fade" id="uploadFileModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('master-folder.files.upload', $folder) }}" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="form_type" value="upload">
            <div class="modal-header"><h5 class="modal-title font-weight-bold">Upload File</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <label for="folder-files" class="font-weight-bold">Pilih File</label>
                <input id="folder-files" type="file" name="files[]" class="form-control-file border rounded p-3 w-100" multiple required>
                <small class="form-text text-muted">Maksimal 10 file sekali upload, masing-masing 25 MB.</small>
                <div class="form-group mt-4">
                    <label for="upload-primary-role" class="font-weight-bold">Jabatan Utama <span class="text-danger">*</span></label>
                    <select id="upload-primary-role" name="primary_role_id" class="form-control" required>
                        <option value="">Pilih jabatan utama</option>
                        @foreach($primaryRoles as $role)<option value="{{ $role->id }}" @selected(old('form_type') === 'upload' && (int) old('primary_role_id') === (int) $role->id)>{{ $role->role_name }}</option>@endforeach
                    </select>
                    <small class="form-text text-muted">Diambil dari divisi utama {{ $folder->division->name ?? '-' }}.</small>
                </div>
                <div class="mt-4">
                    <label class="font-weight-bold">Role yang Dapat Melihat File <span class="text-danger">*</span></label>
                    <p class="small text-muted">Hanya role yang sudah memiliki akses ke folder ini yang dapat dipilih.</p>
                    <div class="row">
                        @foreach($folder->roles as $role)
                            <div class="col-md-6 mb-2">
                                <label class="border rounded p-2 d-flex align-items-center mb-0 w-100">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mr-2" @checked(in_array($role->id, old('role_ids', [])))>
                                    <span>{{ $role->role_name }} <small class="text-muted">({{ $role->division->name ?? '-' }})</small></span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="fas fa-upload mr-1"></i>Upload</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="createDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <form id="document-form" method="POST" action="{{ route('master-folder.documents.store', $folder) }}" class="modal-content">
            @csrf
            <input type="hidden" name="form_type" value="document">
            <input type="hidden" id="document-content" name="content" value="">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-file-pdf text-danger mr-2"></i>Buat Dokumen PDF</h5>
                    <small class="text-muted">Tulis isi dokumen, lalu sistem akan menyimpannya sebagai PDF.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body bg-light">
                <div class="form-group">
                    <label for="document-primary-role" class="font-weight-bold">Jabatan Utama <span class="text-danger">*</span></label>
                    <select id="document-primary-role" name="primary_role_id" class="form-control" required>
                        <option value="">Pilih jabatan utama</option>
                        @foreach($primaryRoles as $role)<option value="{{ $role->id }}" @selected(old('form_type') === 'document' && (int) old('primary_role_id') === (int) $role->id)>{{ $role->role_name }}</option>@endforeach
                    </select>
                    <small class="form-text text-muted">Jabatan utama berasal dari divisi {{ $folder->division->name ?? 'utama folder' }} dan otomatis mendapat akses.</small>
                </div>
                <div class="form-row mb-3">
                    <div class="col-md-7">
                        <label for="document-name" class="font-weight-bold">Nama File <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input id="document-name" name="document_name" value="{{ old('form_type') === 'document' ? old('document_name') : '' }}" class="form-control" maxlength="150" required placeholder="Contoh: SOP Pengajuan Cuti">
                            <div class="input-group-append"><span class="input-group-text font-weight-bold">.pdf</span></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="font-weight-bold">Role yang Dapat Melihat <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap">
                            @foreach($folder->roles as $role)
                                <label class="border bg-white rounded py-2 px-3 mr-2 mb-1">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mr-1" @checked(old('form_type') === 'document' && in_array($role->id, old('role_ids', [])))>
                                    {{ $role->role_name }} ({{ $role->division->name ?? '-' }})
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="create-document-editor-workspace" class="document-editor-workspace">
                <div class="document-toolbar bg-white border rounded-top p-2 d-flex flex-wrap align-items-center">
                    <select class="form-control form-control-sm mr-2 mb-1" style="width:145px" onchange="document.execCommand('formatBlock', false, this.value); this.value='p';">
                        <option value="p">Paragraf</option><option value="h1">Judul 1</option><option value="h2">Judul 2</option><option value="h3">Judul 3</option>
                    </select>
                    <div class="btn-group btn-group-sm mr-2 mb-1">
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('bold')" title="Tebal"><i class="fas fa-bold"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('italic')" title="Miring"><i class="fas fa-italic"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('underline')" title="Garis bawah"><i class="fas fa-underline"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('strikeThrough')" title="Coret"><i class="fas fa-strikethrough"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm mr-2 mb-1">
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('justifyLeft')"><i class="fas fa-align-left"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('justifyCenter')"><i class="fas fa-align-center"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('justifyRight')"><i class="fas fa-align-right"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('justifyFull')"><i class="fas fa-align-justify"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm mr-2 mb-1">
                        <button type="button" class="btn btn-light border" onclick="insertDocumentLink('document-editor')" title="Sisipkan link"><i class="fas fa-link"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('insertUnorderedList')"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('insertOrderedList')"><i class="fas fa-list-ol"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('outdent')"><i class="fas fa-outdent"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('indent')"><i class="fas fa-indent"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm mb-1">
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('undo')"><i class="fas fa-undo"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('redo')"><i class="fas fa-redo"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('removeFormat')" title="Hapus format"><i class="fas fa-eraser"></i></button>
                        <button type="button" class="btn btn-light border" id="document-fullscreen-btn"title="Fullscreen"><i class="fas fa-expand"></i><span class="ml-1">Fullscreen</span>
                        </button>
                    </div>
                </div>
                <div id="create-document-editor-stage" class="editor-stage border rounded-bottom p-4" style="background:#dfe3e8; height:600px; overflow:auto;">
                    <button type="button" class="btn btn-dark btn-sm editor-fullscreen-exit" onclick="toggleCreateEditorFullscreen()"><i class="fas fa-compress mr-1"></i>Keluar Fullscreen</button>
                    <div id="document-editor" class="document-pages document-single-page">
                        <div contenteditable="true" class="document-page shadow-sm">
                        @if(old('form_type') === 'document')
                            {!! old('content') !!}
                        @else
                            <p>Mulai tulis dokumen di sini...</p>
                        @endif
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-file-pdf mr-1"></i>Simpan sebagai PDF</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function editDocumentCommand(fileId, command, value) {
        const editor =
            document.getElementById('edit-file-editor-' + fileId);

        const page =
            resolveDocumentPage(editor);

        if (!page) return;

        page.focus();

        document.execCommand(
            command,
            false,
            value || null
        );
    }


    function resolveDocumentPage(editor) {
        if (!editor) return null;

        const selection =
            window.getSelection();

        const anchor =
            selection && selection.anchorNode;

        const anchorElement =
            anchor &&
            (
                anchor.nodeType === Node.ELEMENT_NODE
                    ? anchor
                    : anchor.parentElement
            );

        const selectedPage =
            anchorElement &&
            anchorElement.closest
                ? anchorElement.closest('.document-page')
                : null;

        if (
            selectedPage &&
            editor.contains(selectedPage)
        ) {
            return selectedPage;
        }

        return (
            editor.querySelector('.document-page.is-active') ||
            editor.querySelector('.document-page')
        );
    }


    function insertDocumentLink(editorId) {
        const editor =
            document.getElementById(editorId);

        if (!editor) return;

        const page =
            resolveDocumentPage(editor);

        if (!page) return;

        page.focus();

        const selection =
            window.getSelection();

        const savedRange =
            selection && selection.rangeCount
                ? selection.getRangeAt(0).cloneRange()
                : null;

        let url =
            window.prompt(
                'Masukkan alamat link (contoh: https://example.com):',
                'https://'
            );

        if (!url) return;

        url = url.trim();

        if (
            !/^(https?:\/\/|mailto:|#)/i.test(url)
        ) {
            url = 'https://' + url;
        }

        if (savedRange) {
            selection.removeAllRanges();
            selection.addRange(savedRange);
        }

        if (
            !selection ||
            selection.isCollapsed
        ) {
            const label =
                window.prompt(
                    'Teks yang ditampilkan:',
                    url
                ) || url;

            const link =
                document.createElement('a');

            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = label;

            const range =
                selection && selection.rangeCount
                    ? selection.getRangeAt(0)
                    : null;

            if (range) {
                range.insertNode(link);
            } else {
                page.appendChild(link);
            }
        } else {
            document.execCommand(
                'createLink',
                false,
                url
            );

            page
                .querySelectorAll('a')
                .forEach(function (link) {
                    try {
                        if (
                            link.href ===
                            new URL(
                                url,
                                window.location.href
                            ).href
                        ) {
                            link.target = '_blank';
                            link.rel =
                                'noopener noreferrer';
                        }
                    } catch (error) {}
                });
        }
    }


    document.addEventListener(
        'DOMContentLoaded',
        function () {

            /*
            * ============================================================
            * DOCUMENT EDITOR
            * ============================================================
            */

            function bindDocumentPage(editor, page) {
                if (
                    !page ||
                    page.dataset.pageReady === '1'
                ) {
                    return;
                }

                page.dataset.pageReady = '1';

                page.addEventListener(
                    'focus',
                    function () {
                        editor
                            .querySelectorAll('.document-page')
                            .forEach(function (item) {
                                item.classList.remove(
                                    'is-active'
                                );
                            });

                        page.classList.add(
                            'is-active'
                        );
                    }
                );

                page.addEventListener(
                    'click',
                    function () {
                        editor
                            .querySelectorAll('.document-page')
                            .forEach(function (item) {
                                item.classList.remove(
                                    'is-active'
                                );
                            });

                        page.classList.add(
                            'is-active'
                        );
                    }
                );

                enableRichDocumentPaste(page);
            }


            /*
            * Satu editor = satu halaman.
            * Tidak ada lagi:
            * - paginateDocument()
            * - splitPagesAtBreakMarkers()
            * - appendDocumentPage()
            * - renumberDocumentPages()
            */
            function initializeDocumentEditor(editor) {
                if (!editor) return;

                const initialPage =
                    editor.querySelector(
                        '.document-page'
                    );

                const initialHtml =
                    initialPage
                        ? initialPage.innerHTML
                        : '<p><br></p>';

                editor.innerHTML = '';

                const page =
                    document.createElement('div');

                page.className =
                    'document-page shadow-sm';

                page.contentEditable = 'true';

                page.innerHTML =
                    initialHtml ||
                    '<p><br></p>';

                editor.appendChild(page);

                bindDocumentPage(
                    editor,
                    page
                );
            }


            /*
            * Ambil isi dari satu halaman saja.
            */
            function serializeDocumentPages(editor) {
                if (!editor) {
                    return '';
                }

                const page =
                    editor.querySelector(
                        '.document-page'
                    );

                return page
                    ? page.innerHTML
                    : '';
            }


            /*
            * Membekukan ukuran gambar sebelum disimpan.
            */
            function freezeImageDimensions(editor) {
                if (!editor) return;

                editor
                    .querySelectorAll('img')
                    .forEach(function (image) {

                        const rectangle =
                            image.getBoundingClientRect();

                        if (
                            rectangle.width <= 0 ||
                            rectangle.height <= 0
                        ) {
                            return;
                        }

                        image.style.width =
                            Math.round(
                                rectangle.width
                            ) + 'px';

                        image.style.height =
                            Math.round(
                                rectangle.height
                            ) + 'px';

                        image.style.maxWidth =
                            '100%';

                        image.setAttribute(
                            'width',
                            Math.round(
                                rectangle.width
                            )
                        );

                        image.setAttribute(
                            'height',
                            Math.round(
                                rectangle.height
                            )
                        );
                    });
            }


            /*
            * Insert HTML pada posisi cursor.
            */
            function insertHtmlAtSelection(
                editor,
                html
            ) {
                editor.focus();

                document.execCommand(
                    'insertHTML',
                    false,
                    html
                );
            }

            function normalizeDocumentContent(editor) {

                if (!editor) {
                    return;
                }

                const page =
                    editor.querySelector('.document-page');

                if (!page) {
                    return;
                }


                /*
                * Pastikan elemen tidak memiliki
                * ukuran yang melebihi kertas.
                */
                page.querySelectorAll('*').forEach(function (element) {

                    /*
                    * Hapus width yang terlalu besar
                    */
                    if (
                        element.style.width &&
                        !element.style.width.includes('%')
                    ) {
                        element.style.removeProperty('width');
                    }

                    /*
                    * Hapus min-width.
                    */
                    element.style.removeProperty(
                        'min-width'
                    );

                    /*
                    * Hapus max-width dari hasil Word.
                    * CSS kita yang akan mengatur.
                    */
                    element.style.removeProperty(
                        'max-width'
                    );


                    /*
                    * Height jangan dipaksakan dari Word
                    * untuk elemen biasa.
                    */
                    if (
                        element.tagName !== 'IMG'
                    ) {
                        element.style.removeProperty(
                            'height'
                        );

                        element.style.removeProperty(
                            'min-height'
                        );
                    }


                    /*
                    * Khusus tabel.
                    */
                    if (
                        element.tagName === 'TABLE'
                    ) {

                        element.style.removeProperty(
                            'width'
                        );

                        element.style.removeProperty(
                            'min-width'
                        );

                        element.style.maxWidth =
                            '100%';

                    }


                    /*
                    * Khusus gambar.
                    */
                    if (
                        element.tagName === 'IMG'
                    ) {

                        element.style.maxWidth =
                            '100%';

                        element.style.height =
                            'auto';

                    }

                });


                /*
                * Paksa tabel agar tidak melebihi kertas.
                */
                page.querySelectorAll('table')
                    .forEach(function (table) {

                        table.style.maxWidth =
                            '100%';

                        table.style.width =
                            'auto';

                        table.style.boxSizing =
                            'border-box';

                    });


                /*
                * Paksa gambar.
                */
                page.querySelectorAll('img')
                    .forEach(function (image) {

                        image.style.maxWidth =
                            '100%';

                        image.style.height =
                            'auto';

                    });

            }


            /*
            * ============================================================
            * PASTE HTML / WORD / GAMBAR
            * ============================================================
            */
            function enableRichDocumentPaste(editor) {
                if (!editor) return;

                editor.addEventListener(
                    'paste',
                    function (event) {

                        const items =
                            Array.from(
                                event.clipboardData?.items ||
                                []
                            );

                        const imageFiles =
                            items
                                .filter(function (item) {
                                    return (
                                        item.kind === 'file' &&
                                        item.type.startsWith(
                                            'image/'
                                        )
                                    );
                                })
                                .map(function (item) {
                                    return item.getAsFile();
                                })
                                .filter(Boolean);

                        const pastedHtml =
                            event.clipboardData.getData(
                                'text/html'
                            );

                        const pastedText =
                            event.clipboardData.getData(
                                'text/plain'
                            );

                        if (
                            !pastedHtml &&
                            !imageFiles.length
                        ) {
                            return;
                        }

                        event.preventDefault();

                        const placeholderIds = [];


                        /*
                        * ------------------------------------------------
                        * PASTE HTML
                        * ------------------------------------------------
                        */
                        if (pastedHtml) {

                            const parsed =
                                new DOMParser()
                                    .parseFromString(
                                        pastedHtml,
                                        'text/html'
                                    );

                            /*
                            * Hapus elemen berbahaya.
                            */
                            parsed
                                .querySelectorAll(
                                    'script, iframe, object, embed'
                                )
                                .forEach(
                                    function (element) {
                                        element.remove();
                                    }
                                );


                            /*
                            * Style yang diperbolehkan.
                            */
                            const allowedStyleProperties =
                                new Set([
                                    'text-align',
                                    'font-family',
                                    'font-size',
                                    'font-weight',
                                    'font-style',
                                    'text-decoration',
                                    'text-decoration-line',
                                    'color',
                                    'background',
                                    'background-color',
                                    'line-height',
                                    'letter-spacing',
                                    'word-spacing',
                                    'text-indent',

                                    'margin',
                                    'margin-top',
                                    'margin-right',
                                    'margin-bottom',
                                    'margin-left',

                                    'padding',
                                    'padding-top',
                                    'padding-right',
                                    'padding-bottom',
                                    'padding-left',

                                    'vertical-align',
                                    'white-space',
                                    'float',
                                    'clear',

                                    'border',
                                    'border-top',
                                    'border-right',
                                    'border-bottom',
                                    'border-left',
                                    'border-color',
                                    'border-style',
                                    'border-width',
                                    'border-collapse',
                                    'border-spacing',

                                    'list-style-type'
                                ]);


                            /*
                            * Baca style dari <style>.
                            */
                            parsed
                                .querySelectorAll('style')
                                .forEach(
                                    function (styleElement) {

                                        const css =
                                            styleElement
                                                .textContent ||
                                            '';

                                        const rulePattern =
                                            /([^{}]+)\{([^{}]+)\}/g;

                                        let rule;

                                        while (
                                            (
                                                rule =
                                                    rulePattern.exec(
                                                        css
                                                    )
                                            ) !== null
                                        ) {

                                            const declarations =
                                                rule[2]
                                                    .split(';')
                                                    .map(
                                                        function (
                                                            declaration
                                                        ) {

                                                            const separator =
                                                                declaration.indexOf(
                                                                    ':'
                                                                );

                                                            if (
                                                                separator <
                                                                1
                                                            ) {
                                                                return null;
                                                            }

                                                            let property =
                                                                declaration
                                                                    .slice(
                                                                        0,
                                                                        separator
                                                                    )
                                                                    .trim()
                                                                    .toLowerCase();

                                                            const value =
                                                                declaration
                                                                    .slice(
                                                                        separator + 1
                                                                    )
                                                                    .trim();

                                                            if (
                                                                property ===
                                                                'mso-page-break-before'
                                                            ) {
                                                                property =
                                                                    'page-break-before';
                                                            }

                                                            if (
                                                                property ===
                                                                'mso-page-break-after'
                                                            ) {
                                                                property =
                                                                    'page-break-after';
                                                            }

                                                            if (
                                                                !allowedStyleProperties.has(
                                                                    property
                                                                ) ||
                                                                /url\s*\(|expression\s*\(/i.test(
                                                                    value
                                                                )
                                                            ) {
                                                                return null;
                                                            }

                                                            return [
                                                                property,
                                                                value
                                                            ];
                                                        }
                                                    )
                                                    .filter(Boolean);

                                            rule[1]
                                                .split(',')
                                                .forEach(
                                                    function (
                                                        selector
                                                    ) {

                                                        selector =
                                                            selector
                                                                .trim()
                                                                .replace(
                                                                    /::?[a-z-]+(?:\([^)]*\))?/gi,
                                                                    ''
                                                                );

                                                        if (
                                                            !selector ||
                                                            !declarations.length
                                                        ) {
                                                            return;
                                                        }

                                                        try {

                                                            parsed
                                                                .querySelectorAll(
                                                                    selector
                                                                )
                                                                .forEach(
                                                                    function (
                                                                        element
                                                                    ) {

                                                                        declarations
                                                                            .forEach(
                                                                                function (
                                                                                    pair
                                                                                ) {
                                                                                    element.style.setProperty(
                                                                                        pair[0],
                                                                                        pair[1]
                                                                                    );
                                                                                }
                                                                            );
                                                                    }
                                                                );

                                                        } catch (
                                                            error
                                                        ) {}
                                                    }
                                                );
                                        }

                                        styleElement.remove();
                                    }
                                );


                            /*
                            * br[clear="all"] cukup diubah menjadi
                            * line break biasa.
                            *
                            * Tidak lagi membuat halaman baru.
                            */
                            parsed
                                .querySelectorAll(
                                    'br[clear="all"]'
                                )
                                .forEach(
                                    function (lineBreak) {

                                        lineBreak.removeAttribute(
                                            'clear'
                                        );

                                        lineBreak.style.removeProperty(
                                            'page-break-before'
                                        );

                                        lineBreak.style.removeProperty(
                                            'break-before'
                                        );
                                    }
                                );


                            /*
                            * Gambar dari paste Word.
                            */
                            parsed
                                .querySelectorAll('img')
                                .forEach(
                                    function (
                                        image,
                                        index
                                    ) {

                                        if (
                                            !imageFiles[index]
                                        ) {
                                            return;
                                        }

                                        const placeholder =
                                            document.createElement(
                                                'span'
                                            );

                                        const placeholderId =
                                            'paste-image-' +
                                            Date.now() +
                                            '-' +
                                            index +
                                            '-' +
                                            Math.random()
                                                .toString(36)
                                                .slice(2);

                                        placeholder.id =
                                            placeholderId;

                                        placeholderIds.push(
                                            placeholderId
                                        );

                                        image.replaceWith(
                                            placeholder
                                        );
                                    }
                                );


                            /*
                            * Masukkan HTML langsung ke editor.
                            *
                            * TIDAK ADA lagi:
                            * splitPagesAtBreakMarkers()
                            * paginateDocument()
                            */
                            insertHtmlAtSelection(
                                editor,
                                parsed.body.innerHTML
                            );

                            normalizeDocumentContent(editor);


                        /*
                        * ------------------------------------------------
                        * PASTE TEXT
                        * ------------------------------------------------
                        */
                        } else if (pastedText) {

                            const safeText =
                                pastedText
                                    .replace(
                                        /&/g,
                                        '&amp;'
                                    )
                                    .replace(
                                        /</g,
                                        '&lt;'
                                    )
                                    .replace(
                                        />/g,
                                        '&gt;'
                                    )
                                    .replace(
                                        /\n/g,
                                        '<br>'
                                    );

                            insertHtmlAtSelection(
                                editor,
                                safeText
                            );
                        }


                        /*
                        * ------------------------------------------------
                        * PASTE FILE GAMBAR
                        * ------------------------------------------------
                        */
                        imageFiles.forEach(
                            function (
                                file,
                                index
                            ) {

                                const reader =
                                    new FileReader();

                                reader.onload =
                                    function () {

                                        const image =
                                            document.createElement(
                                                'img'
                                            );

                                        image.src =
                                            reader.result;

                                        image.alt =
                                            file.name ||
                                            'Gambar dokumen';

                                        image.style.maxWidth =
                                            '100%';

                                        image.style.height =
                                            'auto';

                                        image.style.display =
                                            'block';

                                        image.style.margin =
                                            '12px auto';


                                        /*
                                        * Cari placeholder.
                                        */
                                        const placeholder =
                                            placeholderIds[index]
                                                ? editor.querySelector(
                                                    '#' +
                                                    placeholderIds[index]
                                                )
                                                : null;

                                        if (placeholder) {

                                            placeholder.replaceWith(
                                                image
                                            );

                                        } else {

                                            /*
                                            * Jika tidak ada
                                            * placeholder, masukkan
                                            * gambar di posisi akhir.
                                            */
                                            editor.appendChild(
                                                image
                                            );

                                            const paragraph =
                                                document.createElement(
                                                    'p'
                                                );

                                            paragraph.appendChild(
                                                document.createElement(
                                                    'br'
                                                )
                                            );

                                            editor.appendChild(
                                                paragraph
                                            );
                                        }
                                    };

                                reader.readAsDataURL(
                                    file
                                );
                            }
                        );
                    }
                );
            }


            /*
            * ============================================================
            * FULLSCREEN CREATE DOCUMENT
            * ============================================================
            */
            const createDocumentModal =
                document.getElementById(
                    'createDocumentModal'
                );

            const fullscreenButton =
                document.getElementById(
                    'document-fullscreen-btn'
                );

            const createEditorStage =
                document.getElementById(
                    'create-document-editor-stage'
                );

            const createEditorWorkspace =
                document.getElementById(
                    'create-document-editor-workspace'
                );

            if (
                createDocumentModal &&
                fullscreenButton &&
                createEditorStage &&
                createEditorWorkspace
            ) {

                function setCreateEditorFullscreen(isFullscreen) {
                    createEditorWorkspace.classList.toggle(
                        'editor-only-fullscreen',
                        isFullscreen
                    );

                    document.body.classList.toggle(
                        'document-editor-fullscreen-open',
                        isFullscreen
                    );

                    fullscreenButton.innerHTML = `
                        <i class="fas fa-expand"></i>
                        <span class="ml-1">Fullscreen</span>
                    `;

                    if (isFullscreen) {
                        const page = createEditorStage.querySelector('.document-page');
                        if (page) page.focus();
                    }
                }

                window.toggleCreateEditorFullscreen = function () {
                    setCreateEditorFullscreen(
                        !createEditorWorkspace.classList.contains('editor-only-fullscreen')
                    );
                };

                fullscreenButton.addEventListener(
                    'click',
                    function () {
                        window.toggleCreateEditorFullscreen();
                    }
                );


                /*
                * Jika modal ditutup, fullscreen
                * otomatis dinonaktifkan.
                */
                if (
                    window.jQuery
                ) {

                    $(createDocumentModal).on(
                        'hidden.bs.modal',
                        function () {

                            setCreateEditorFullscreen(false);
                        }
                    );
                }
            }


            /*
            * ============================================================
            * CREATE DOCUMENT FORM
            * ============================================================
            */
            const documentForm =
                document.getElementById(
                    'document-form'
                );

            const editor =
                document.getElementById(
                    'document-editor'
                );


            /*
            * Inisialisasi editor.
            *
            * Satu editor hanya memiliki satu
            * .document-page.
            */
            document
                .querySelectorAll('.document-pages')
                .forEach(
                    initializeDocumentEditor
                );


            if (
                documentForm &&
                editor
            ) {

                documentForm.addEventListener(
                    'submit',
                    function () {

                        freezeImageDimensions(
                            editor
                        );

                        const contentInput =
                            document.getElementById(
                                'document-content'
                            );

                        if (contentInput) {

                            contentInput.value =
                                serializeDocumentPages(
                                    editor
                                );
                        }
                    }
                );
            }


            /*
            * ============================================================
            * EDIT FILE FORM
            * ============================================================
            */
            document
                .querySelectorAll('.edit-file-form')
                .forEach(
                    function (form) {

                        form.addEventListener(
                            'submit',
                            function () {

                                const fileId =
                                    form.dataset.fileId;

                                const editEditor =
                                    document.getElementById(
                                        'edit-file-editor-' +
                                        fileId
                                    );

                                const hiddenContent =
                                    document.getElementById(
                                        'edit-file-content-' +
                                        fileId
                                    );

                                if (
                                    editEditor &&
                                    hiddenContent
                                ) {

                                    freezeImageDimensions(
                                        editEditor
                                    );

                                    hiddenContent.value =
                                        serializeDocumentPages(
                                            editEditor
                                        );
                                }
                            }
                        );
                    }
                );


            /*
            * ============================================================
            * PDF VIEWER
            * ============================================================
            */
            const pdfViewer =
                document.getElementById(
                    'masterPdfViewer'
                );

            const pdfBody =
                document.getElementById(
                    'masterPdfBody'
                );

            const pdfTitle =
                document.getElementById(
                    'masterPdfTitle'
                );

            const zoomLabel =
                document.getElementById(
                    'masterPdfZoomLabel'
                );

            let activePdf = null;

            let pdfScale = 1.35;


            async function renderPdfPages() {

                if (!activePdf) return;

                pdfBody.innerHTML = '';

                zoomLabel.textContent =
                    Math.round(
                        pdfScale * 100
                    ) + '%';

                for (
                    let pageNumber = 1;
                    pageNumber <= activePdf.numPages;
                    pageNumber++
                ) {

                    const page =
                        await activePdf.getPage(
                            pageNumber
                        );

                    const viewport =
                        page.getViewport({
                            scale: pdfScale
                        });

                    const canvas =
                        document.createElement(
                            'canvas'
                        );

                    const context =
                        canvas.getContext(
                            '2d'
                        );

                    canvas.className =
                        'master-pdf-page';

                    canvas.width =
                        viewport.width;

                    canvas.height =
                        viewport.height;

                    pdfBody.appendChild(
                        canvas
                    );

                    await page.render({
                        canvasContext: context,
                        viewport: viewport
                    }).promise;
                }
            }


            document
                .querySelectorAll(
                    '.btn-master-pdf-preview'
                )
                .forEach(
                    function (link) {

                        link.addEventListener(
                            'click',
                            async function (event) {

                                event.preventDefault();

                                pdfViewer.style.display =
                                    'block';

                                document.body.style.overflow =
                                    'hidden';

                                pdfTitle.textContent =
                                    link.dataset.title;

                                pdfScale = 1.35;

                                pdfBody.innerHTML = `
                                    <div class="text-center mt-5">
                                        <div class="spinner-border text-primary"></div>
                                        <div class="mt-2">
                                            Memuat PDF...
                                        </div>
                                    </div>
                                `;

                                try {

                                    if (
                                        typeof pdfjsLib ===
                                        'undefined'
                                    ) {
                                        throw new Error(
                                            'PDF viewer tidak tersedia.'
                                        );
                                    }

                                    activePdf =
                                        await pdfjsLib
                                            .getDocument(
                                                link.dataset.pdf
                                            )
                                            .promise;

                                    await renderPdfPages();

                                } catch (error) {

                                    activePdf = null;

                                    pdfBody.innerHTML = `
                                        <div class="alert alert-danger mt-5">
                                            PDF tidak dapat ditampilkan.
                                            Silakan gunakan tombol download.
                                        </div>
                                    `;
                                }
                            }
                        );
                    }
                );


            const masterPdfBack =
                document.getElementById(
                    'masterPdfBack'
                );

            if (masterPdfBack) {

                masterPdfBack.addEventListener(
                    'click',
                    function () {

                        pdfViewer.style.display =
                            'none';

                        document.body.style.overflow =
                            '';

                        pdfBody.innerHTML = '';

                        activePdf = null;
                    }
                );
            }


            const masterPdfZoomIn =
                document.getElementById(
                    'masterPdfZoomIn'
                );

            if (masterPdfZoomIn) {

                masterPdfZoomIn.addEventListener(
                    'click',
                    async function () {

                        if (
                            !activePdf ||
                            pdfScale >= 3
                        ) {
                            return;
                        }

                        pdfScale += 0.2;

                        await renderPdfPages();
                    }
                );
            }


            const masterPdfZoomOut =
                document.getElementById(
                    'masterPdfZoomOut'
                );

            if (masterPdfZoomOut) {

                masterPdfZoomOut.addEventListener(
                    'click',
                    async function () {

                        if (
                            !activePdf ||
                            pdfScale <= 0.6
                        ) {
                            return;
                        }

                        pdfScale -= 0.2;

                        await renderPdfPages();
                    }
                );
            }


            if (pdfBody) {

                pdfBody.addEventListener(
                    'wheel',
                    async function (event) {

                        if (
                            !event.ctrlKey ||
                            !activePdf
                        ) {
                            return;
                        }

                        event.preventDefault();

                        pdfScale =
                            event.deltaY < 0
                                ? Math.min(
                                    3,
                                    pdfScale + 0.1
                                )
                                : Math.max(
                                    0.6,
                                    pdfScale - 0.1
                                );

                        await renderPdfPages();
                    },
                    {
                        passive: false
                    }
                );
            }


            /*
            * ============================================================
            * ERROR / VALIDATION MODAL
            * ============================================================
            */
            @if($errors->any() && old('edit_file_id'))
                $('#editFileModal{{ (int) old('edit_file_id') }}').modal('show');

            @elseif($errors->any() && old('form_type') === 'document')
                $('#createDocumentModal').modal('show');

            @elseif($errors->any())
                $('#uploadFileModal').modal('show');
            @endif

        }
    );
</script>
@endsection
