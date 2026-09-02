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
</style>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
    <div>
        <a href="{{ $backToMine ? route('master-folder.mine') : route('master-folder.index', ['division_id' => $folder->division_id]) }}" class="btn btn-sm btn-light border shadow-sm mb-3">
            <i class="fas fa-arrow-left mr-1"></i>Kembali ke Folder Master
        </a>
        <h1 class="h4 text-gray-800 font-weight-bold mb-1"><i class="fas fa-folder-open text-warning mr-2"></i>{{ $folder->name }}</h1>
        <p class="text-muted small mb-0">{{ optional($folder->division)->name ?? 'Folder Pribadi' }} &middot; {{ $folder->description ?: 'Tanpa deskripsi' }}</p>
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

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar File</h6>
        <span class="badge badge-light border">{{ $folder->files->count() }} file</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light"><tr><th class="pl-4">Nama File</th><th>Akses Role</th><th>Ukuran</th><th>Pengunggah</th><th>Tanggal</th><th class="text-center">Aksi</th></tr></thead>
                <tbody>
                @forelse($folder->files->sortByDesc('created_at') as $file)
                    <tr>
                        <td class="pl-4 align-middle">
                            <i class="fas fa-file-{{ strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf' || $file->mime_type === 'application/pdf' ? 'pdf text-danger' : 'alt text-primary' }} mr-2"></i>
                            @if(strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf' || $file->mime_type === 'application/pdf')
                                <a href="#" class="font-weight-bold pdf-file-link btn-master-pdf-preview" data-pdf="{{ route('master-folder.files.preview', [$folder, $file]) }}" data-title="{{ $file->original_name }}">{{ $file->original_name }}</a>
                            @else
                                <span class="font-weight-bold text-gray-800">{{ $file->original_name }}</span>
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
                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-cloud-upload-alt fa-3x mb-3 text-gray-300 d-block"></i>Belum ada file yang dapat Anda lihat di folder ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($canManage)
@foreach($folder->files as $file)
    <div class="modal fade" id="editFileModal{{ $file->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog {{ $file->is_generated_document ? 'modal-xl' : 'modal-lg' }}" role="document">
            <form id="edit-file-form-{{ $file->id }}" method="POST" action="{{ route('master-folder.files.update', [$folder, $file]) }}" class="modal-content edit-file-form" data-file-id="{{ $file->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_file_id" value="{{ $file->id }}">
                @if($file->is_generated_document)<input type="hidden" id="edit-file-content-{{ $file->id }}" name="content">@endif
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-edit text-warning mr-2"></i>Edit {{ $file->is_generated_document ? 'Dokumen' : 'File' }}</h5>
                        <small class="text-muted">{{ $file->is_generated_document ? 'Perubahan isi akan menghasilkan ulang file PDF.' : 'File asli tidak diubah, hanya nama dan aksesnya.' }}</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body {{ $file->is_generated_document ? 'bg-light' : '' }}">
                    <div class="form-row mb-3">
                        <div class="col-md-7">
                            <label for="edit-file-name-{{ $file->id }}" class="font-weight-bold">Nama File <span class="text-danger">*</span></label>
                            @if($file->is_generated_document)
                                <div class="input-group">
                                    <input id="edit-file-name-{{ $file->id }}" name="file_name" value="{{ (int) old('edit_file_id') === $file->id ? preg_replace('/\.pdf$/i', '', old('file_name')) : preg_replace('/\.pdf$/i', '', $file->original_name) }}" class="form-control" maxlength="150" required>
                                    <div class="input-group-append"><span class="input-group-text font-weight-bold">.pdf</span></div>
                                </div>
                            @else
                                <input id="edit-file-name-{{ $file->id }}" name="file_name" value="{{ (int) old('edit_file_id') === $file->id ? old('file_name') : $file->original_name }}" class="form-control" maxlength="150" required>
                            @endif
                        </div>
                        <div class="col-md-5">
                            <label class="font-weight-bold">Role yang Dapat Melihat <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap">
                                @php
                                    $selectedFileRoles = (int) old('edit_file_id') === $file->id
                                        ? old('role_ids', [])
                                        : $file->roles->pluck('id')->all();
                                @endphp
                                @foreach($folder->roles as $role)
                                    <label class="border bg-white rounded py-2 px-3 mr-2 mb-1">
                                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mr-1" @checked(in_array($role->id, $selectedFileRoles))>
                                        {{ $role->role_name }} ({{ $role->division->name ?? '-' }})
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($file->is_generated_document)
                        <div class="bg-white border rounded-top p-2 d-flex flex-wrap align-items-center">
                            <select class="form-control form-control-sm mr-2 mb-1" style="width:145px" onchange="editDocumentCommand({{ $file->id }}, 'formatBlock', this.value); this.value='p';">
                                <option value="p">Paragraf</option><option value="h1">Judul 1</option><option value="h2">Judul 2</option><option value="h3">Judul 3</option>
                            </select>
                            <div class="btn-group btn-group-sm mr-2 mb-1">
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'bold')"><i class="fas fa-bold"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'italic')"><i class="fas fa-italic"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'underline')"><i class="fas fa-underline"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'strikeThrough')"><i class="fas fa-strikethrough"></i></button>
                            </div>
                            <div class="btn-group btn-group-sm mr-2 mb-1">
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'justifyLeft')"><i class="fas fa-align-left"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'justifyCenter')"><i class="fas fa-align-center"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'justifyRight')"><i class="fas fa-align-right"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'justifyFull')"><i class="fas fa-align-justify"></i></button>
                            </div>
                            <div class="btn-group btn-group-sm mb-1">
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'insertUnorderedList')"><i class="fas fa-list-ul"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'insertOrderedList')"><i class="fas fa-list-ol"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'undo')"><i class="fas fa-undo"></i></button>
                                <button type="button" class="btn btn-light border" onclick="editDocumentCommand({{ $file->id }}, 'redo')"><i class="fas fa-redo"></i></button>
                            </div>
                        </div>
                        <div class="border border-top-0 rounded-bottom p-4" style="background:#dfe3e8; max-height:600px; overflow:auto;">
                            <div id="edit-file-editor-{{ $file->id }}" contenteditable="true" class="bg-white mx-auto shadow-sm p-5" style="max-width:794px; min-height:520px; outline:none; line-height:1.65; color:#252a34;">@if((int) old('edit_file_id') === $file->id)<p>{{ strip_tags(old('content')) }}</p>@else{!! $file->editor_content !!}@endif</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
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
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('insertUnorderedList')"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('insertOrderedList')"><i class="fas fa-list-ol"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('outdent')"><i class="fas fa-outdent"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('indent')"><i class="fas fa-indent"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm mb-1">
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('undo')"><i class="fas fa-undo"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('redo')"><i class="fas fa-redo"></i></button>
                        <button type="button" class="btn btn-light border" onclick="document.execCommand('removeFormat')" title="Hapus format"><i class="fas fa-eraser"></i></button>
                    </div>
                </div>
                <div class="editor-stage border border-top-0 rounded-bottom p-4" style="background:#dfe3e8; max-height:600px; overflow:auto;">
                    <div id="document-editor" contenteditable="true" class="bg-white mx-auto shadow-sm p-5" style="max-width:794px; min-height:520px; outline:none; line-height:1.65; color:#252a34;"><p>{{ old('form_type') === 'document' ? strip_tags(old('content')) : 'Mulai tulis dokumen di sini...' }}</p></div>
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
    const editor = document.getElementById('edit-file-editor-' + fileId);
    editor.focus();
    document.execCommand(command, false, value || null);
}

document.addEventListener('DOMContentLoaded', function () {
    const documentForm = document.getElementById('document-form');
    const editor = document.getElementById('document-editor');
    if (documentForm && editor) {
        documentForm.addEventListener('submit', function () {
            document.getElementById('document-content').value = editor.innerHTML;
        });
    }

    document.querySelectorAll('.edit-file-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const fileId = form.dataset.fileId;
            const editEditor = document.getElementById('edit-file-editor-' + fileId);
            const hiddenContent = document.getElementById('edit-file-content-' + fileId);
            if (editEditor && hiddenContent) hiddenContent.value = editEditor.innerHTML;
        });
    });

    const pdfViewer = document.getElementById('masterPdfViewer');
    const pdfBody = document.getElementById('masterPdfBody');
    const pdfTitle = document.getElementById('masterPdfTitle');
    const zoomLabel = document.getElementById('masterPdfZoomLabel');
    let activePdf = null;
    let pdfScale = 1.35;

    async function renderPdfPages() {
        if (!activePdf) return;
        pdfBody.innerHTML = '';
        zoomLabel.textContent = Math.round(pdfScale * 100) + '%';

        for (let pageNumber = 1; pageNumber <= activePdf.numPages; pageNumber++) {
            const page = await activePdf.getPage(pageNumber);
            const viewport = page.getViewport({ scale: pdfScale });
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.className = 'master-pdf-page';
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            pdfBody.appendChild(canvas);
            await page.render({ canvasContext: context, viewport: viewport }).promise;
        }
    }

    document.querySelectorAll('.btn-master-pdf-preview').forEach(function (link) {
        link.addEventListener('click', async function (event) {
            event.preventDefault();
            pdfViewer.style.display = 'block';
            document.body.style.overflow = 'hidden';
            pdfTitle.textContent = link.dataset.title;
            pdfScale = 1.35;
            pdfBody.innerHTML = '<div class="text-center mt-5"><div class="spinner-border text-primary"></div><div class="mt-2">Memuat PDF...</div></div>';

            try {
                if (typeof pdfjsLib === 'undefined') throw new Error('PDF viewer tidak tersedia.');
                activePdf = await pdfjsLib.getDocument(link.dataset.pdf).promise;
                await renderPdfPages();
            } catch (error) {
                activePdf = null;
                pdfBody.innerHTML = '<div class="alert alert-danger mt-5">PDF tidak dapat ditampilkan. Silakan gunakan tombol download.</div>';
            }
        });
    });

    document.getElementById('masterPdfBack').addEventListener('click', function () {
        pdfViewer.style.display = 'none';
        document.body.style.overflow = '';
        pdfBody.innerHTML = '';
        activePdf = null;
    });

    document.getElementById('masterPdfZoomIn').addEventListener('click', async function () {
        if (!activePdf || pdfScale >= 3) return;
        pdfScale += .2;
        await renderPdfPages();
    });

    document.getElementById('masterPdfZoomOut').addEventListener('click', async function () {
        if (!activePdf || pdfScale <= .6) return;
        pdfScale -= .2;
        await renderPdfPages();
    });

    pdfBody.addEventListener('wheel', async function (event) {
        if (!event.ctrlKey || !activePdf) return;
        event.preventDefault();
        pdfScale = event.deltaY < 0 ? Math.min(3, pdfScale + .1) : Math.max(.6, pdfScale - .1);
        await renderPdfPages();
    }, { passive: false });

    @if($errors->any() && old('edit_file_id'))
        $('#editFileModal{{ (int) old('edit_file_id') }}').modal('show');
    @elseif($errors->any() && old('form_type') === 'document')
        $('#createDocumentModal').modal('show');
    @elseif($errors->any())
        $('#uploadFileModal').modal('show');
    @endif
});
</script>
@endsection
