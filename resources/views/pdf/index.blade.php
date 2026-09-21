@extends('layout.app')
@section('content')
@include('partials.client_data_theme')
@include('partials.client_native_tables')
<style>

#pdfViewerPage{

    position:fixed;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:#f2f3f5;

    z-index:99999;

    display:none;

}

.viewer-header{

    height:65px;

    background:#ffffff;

    display:flex;

    align-items:center;

    gap:20px;

    padding:0 25px;

    border-bottom:1px solid #ddd;

}

.viewer-header h5{

    font-weight:600;

    margin:0;

}

#viewerBody{

    overflow-y:auto;

    overflow-x:hidden;

    height:calc(100vh - 65px);

    padding:35px;

    display:flex;

    flex-direction:column;

    align-items:center;

    background:#d9d9d9;

}

.pdf-page{

    background:white;

    margin-bottom:25px;

    box-shadow:0 2px 12px rgba(0,0,0,.18);

}

/* File Dokumen dibuka di dalam iframe workspace. Berikan area scroll
   tersendiri agar baris yang melebihi tinggi tab tidak terpotong. */
.lmg-pdf-table-scroll {
    width: 100%;
    max-height: calc(100vh - 230px);
    min-height: 220px;
    overflow-x: auto !important;
    overflow-y: auto !important;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}

.lmg-pdf-table-scroll table {
    min-width: 680px;
}

.lmg-pdf-table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}

body.lmg-embedded #pageContent {
    min-height: 100%;
    padding-bottom: 24px;
}

body.lmg-embedded .lmg-pdf-table-scroll {
    max-height: calc(100dvh - 190px);
}

/* Pastikan modal editor tetap berada di dalam viewport iframe. Header dan
   footer tidak ikut terpotong; hanya isi modal yang digulir. */
#pageContent .modal-dialog {
    margin-top: 16px !important;
    margin-bottom: 16px !important;
}

#pageContent .modal-content {
    max-height: calc(100dvh - 32px) !important;
}

#pageContent .modal-content > form,
#pageContent form.modal-content {
    display: flex;
    min-height: 0;
    max-height: calc(100dvh - 32px);
    flex-direction: column;
    overflow: hidden;
}

#pageContent .modal-body {
    min-height: 0;
    flex: 1 1 auto;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    overscroll-behavior: contain;
}

#pageContent .modal-header,
#pageContent .modal-footer {
    position: relative;
    z-index: 4;
    flex: 0 0 auto;
    background: #fff;
}

#pageContent .lmg-editor-workspace:not(.lmg-fullscreen) .lmg-document-stage {
    height: clamp(300px, 44dvh, 480px);
}

</style>
<div id="pageContent">
    <div class="card shadow">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Data File PDF · {{ $divisionName }}
            </h6>

            <div>
                <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#buatDokumenModal">
                    <i class="fas fa-file-alt mr-1"></i> Buat Dokumen
                </button>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#tambahModal">
                    <i class="fas fa-upload mr-1"></i> Upload PDF
                </button>
            </div>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0 pl-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="GET" action="{{ route('PDF', ['id_produk' => $id, 'division_id' => $divisionId]) }}" class="d-flex flex-wrap align-items-end mb-3" style="gap:10px">
                <input type="hidden" name="division_id" value="{{ $divisionId }}">
                <label class="mb-0 flex-grow-1" style="min-width:220px;max-width:380px"><span class="d-block small font-weight-bold text-uppercase text-muted mb-1">Pengunggah</span><select name="uploaded_by" class="form-control" onchange="this.form.submit()"><option value="">Semua Pengunggah</option>@foreach($uploaders as $uploader)<option value="{{ $uploader->id }}" @selected((int)$selectedUploaderId === (int)$uploader->id)>{{ $uploader->name ?: $uploader->email }}</option>@endforeach</select></label>
                @if($selectedUploaderId)<a href="{{ route('PDF', ['id_produk' => $id, 'division_id' => $divisionId]) }}" class="btn btn-light border">Reset</a>@endif
            </form>

            <div class="table-responsive lmg-pdf-table-scroll">

                <table class="table table-bordered table-hover" id="dataTable">

                    <thead class="thead-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama File</th>
                            <th>Pengunggah</th>
                            <th>PDF</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($clients as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $item->nama_file }}
                                @if($item->is_pinned)
                                    <span class="badge badge-secondary ml-1"><i class="fas fa-thumbtack mr-1"></i>Read-only</span>
                                @endif
                            </td>

                            <td>{{ $item->uploader->name ?? $item->uploader->email ?? ($item->is_system_template ? 'Sistem' : '-') }}</td>

                            <td>
                                {{-- Dokumen pinned/bawaan tetap dapat dilihat, hanya tidak dapat dihapus. --}}
                                <a
                                    href="{{ route('PDF.files.view', $item) }}"
                                    class="btn btn-info btn-sm"
                                    title="Buka PDF di halaman penuh">
                                    <i class="fa fa-eye"></i> Lihat
                                </a>
                            </td>

                            <td>

                                @unless($item->is_pinned)
                                <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#editModal_{{ $item->id }}">
                                    Edit
                                </button>
                                @endunless

                                @unless($item->is_system_template)
                                <button class="btn btn-danger btn-sm"
                                        data-toggle="modal"
                                        data-target="#hapusModal_{{ $item->id }}">
                                    Hapus
                                </button>
                                @endunless

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="text-center">
                                Belum ada file.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

{{-- ==================== BUAT DOKUMEN ==================== --}}
<div class="modal fade" id="buatDokumenModal">
    <div class="modal-dialog modal-xl">
        <form id="clientDocumentForm" action="{{ route('PDF.documents.store') }}" method="POST" class="modal-content">
            @csrf
            <input type="hidden" name="client" value="{{ $id }}">
            <input type="hidden" name="division_id" value="{{ $divisionId }}">
            <input type="hidden" name="form_type" value="create_document">
            <input type="hidden" id="clientDocumentContent" name="content">
            <div class="modal-header">
                <div><h5 class="modal-title font-weight-bold">Buat Dokumen PDF</h5><small class="text-muted">Tulis isi dokumen lalu simpan langsung sebagai PDF.</small></div>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body bg-light">
                <div class="form-group">
                    <label class="font-weight-bold">Nama File <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="nama_file" class="form-control" value="{{ old('form_type') === 'create_document' ? old('nama_file') : '' }}" maxlength="150" required>
                        <div class="input-group-append"><span class="input-group-text font-weight-bold">.pdf</span></div>
                    </div>
                </div>
                <div id="clientDocumentWorkspace" class="lmg-editor-workspace">
                <div class="lmg-document-toolbar">
                    <select class="form-control form-control-sm mr-2 mb-1" style="width:145px" onchange="clientEditorCommand('clientDocumentEditor', 'formatBlock', this.value); this.value='p';">
                        <option value="p">Paragraf</option><option value="h1">Judul 1</option><option value="h2">Judul 2</option><option value="h3">Judul 3</option>
                    </select>
                    <div class="btn-group btn-group-sm mr-2 mb-1">
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','bold')"><i class="fas fa-bold"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','italic')"><i class="fas fa-italic"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','underline')"><i class="fas fa-underline"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','strikeThrough')"><i class="fas fa-strikethrough"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm mr-2 mb-1">
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','justifyLeft')"><i class="fas fa-align-left"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','justifyCenter')"><i class="fas fa-align-center"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','justifyRight')"><i class="fas fa-align-right"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','justifyFull')"><i class="fas fa-align-justify"></i></button>
                    </div>
                    <div class="btn-group btn-group-sm mb-1">
                        <button type="button" class="btn btn-light border" onclick="lmgInsertLink('clientDocumentEditor')"><i class="fas fa-link"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','insertUnorderedList')"><i class="fas fa-list-ul"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','insertOrderedList')"><i class="fas fa-list-ol"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','undo')"><i class="fas fa-undo"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','redo')"><i class="fas fa-redo"></i></button>
                        <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientDocumentEditor','removeFormat')"><i class="fas fa-eraser"></i></button>
                        <button type="button" class="btn btn-light border" onclick="lmgInsertTable('clientDocumentEditor')" title="Sisipkan tabel"><i class="fas fa-table"></i></button>
                    </div>
                    <select class="form-control form-control-sm" style="width:130px" onchange="if(this.value!==''){lmgSetTableBorderOpacity('clientDocumentEditor',this.value);this.value=''}"><option value="">Opacity garis</option><option value="1">100%</option><option value="0.75">75%</option><option value="0.5">50%</option><option value="0.25">25%</option><option value="0">Tanpa garis</option></select>
                    <button type="button" class="btn btn-light border btn-sm" onclick="lmgToggleEditorFullscreen('clientDocumentWorkspace')"><i class="fas fa-expand mr-1"></i>Fullscreen</button>
                </div>
                <div class="lmg-document-stage">
                    <button type="button" class="btn btn-dark btn-sm lmg-editor-exit" onclick="lmgToggleEditorFullscreen('clientDocumentWorkspace')"><i class="fas fa-compress mr-1"></i>Keluar Fullscreen</button>
                    <div id="clientDocumentEditor" contenteditable="true" class="lmg-document-editor"><p>{{ old('form_type') === 'create_document' ? strip_tags(old('content')) : 'Mulai tulis dokumen di sini...' }}</p></div>
                </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="fas fa-file-pdf mr-1"></i>Simpan sebagai PDF</button></div>
        </form>
    </div>
</div>

{{-- ==================== MODAL TAMBAH ==================== --}}

<div class="modal fade" id="tambahModal">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form action="{{ route('PDF.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <input type="hidden" name="client" value="{{ $id }}">
                <input type="hidden" name="division_id" value="{{ $divisionId }}">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Tambah File PDF
                    </h5>

                    <button class="close"
                            data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Nama File</label>

                        <input type="text"
                               name="nama_file"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label>File PDF</label>

                        <input type="file"
                               name="pdf"
                               class="form-control"
                               accept=".pdf"
                               required>
                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary"
                            data-dismiss="modal">
                        Batal
                    </button>

                    <button class="btn btn-primary">
                        Simpan
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

{{-- ==================== MODAL EDIT ==================== --}}

@foreach($clients as $item)

@unless($item->is_pinned)
<div class="modal fade" id="editModal_{{ $item->id }}">

    <div class="modal-dialog {{ $item->is_generated_document ? 'modal-xl' : 'modal-lg' }}">

        <div class="modal-content">

            <form action="{{ route('PDF.update',$item->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="client-edit-document-form"
                  data-file-id="{{ $item->id }}">

                @csrf
                @method('PUT')
                <input type="hidden" name="edit_file_id" value="{{ $item->id }}">
                @if($item->is_generated_document)<input type="hidden" id="clientEditContent_{{ $item->id }}" name="content">@endif

                <div class="modal-header">

                    <h5>Edit File PDF</h5>

                    <button class="close"
                            data-dismiss="modal">
                        &times;
                    </button>

                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Nama File</label>
                        <input type="text"
                               name="nama_file"
                               class="form-control"
                               value="{{ $item->nama_file }}"
                               required>
                    </div>

                    @if($item->is_generated_document)
                    <div class="form-group">
                        <label class="font-weight-bold">Isi Dokumen</label>
                        <div id="clientEditWorkspace_{{ $item->id }}" class="lmg-editor-workspace">
                        <div class="lmg-document-toolbar">
                            <div class="btn-group btn-group-sm mr-2">
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','bold')"><i class="fas fa-bold"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','italic')"><i class="fas fa-italic"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','underline')"><i class="fas fa-underline"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','justifyCenter')"><i class="fas fa-align-center"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','insertUnorderedList')"><i class="fas fa-list-ul"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','insertOrderedList')"><i class="fas fa-list-ol"></i></button>
                                <button type="button" class="btn btn-light border" onclick="lmgInsertLink('clientEditEditor_{{ $item->id }}')"><i class="fas fa-link"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','undo')"><i class="fas fa-undo"></i></button>
                                <button type="button" class="btn btn-light border" onclick="clientEditorCommand('clientEditEditor_{{ $item->id }}','redo')"><i class="fas fa-redo"></i></button>
                                <button type="button" class="btn btn-light border" onclick="lmgInsertTable('clientEditEditor_{{ $item->id }}')"><i class="fas fa-table"></i></button>
                            </div>
                            <select class="form-control form-control-sm" style="width:130px" onchange="if(this.value!==''){lmgSetTableBorderOpacity('clientEditEditor_{{ $item->id }}',this.value);this.value=''}"><option value="">Opacity garis</option><option value="1">100%</option><option value="0.75">75%</option><option value="0.5">50%</option><option value="0.25">25%</option><option value="0">Tanpa garis</option></select>
                            <button type="button" class="btn btn-light border btn-sm" onclick="lmgToggleEditorFullscreen('clientEditWorkspace_{{ $item->id }}')"><i class="fas fa-expand mr-1"></i>Fullscreen</button>
                        </div>
                        <div class="lmg-document-stage">
                            <button type="button" class="btn btn-dark btn-sm lmg-editor-exit" onclick="lmgToggleEditorFullscreen('clientEditWorkspace_{{ $item->id }}')"><i class="fas fa-compress mr-1"></i>Keluar Fullscreen</button>
                            <div id="clientEditEditor_{{ $item->id }}" contenteditable="true" class="lmg-document-editor">{!! $item->editor_content !!}</div>
                        </div>
                        </div>
                    </div>
                    @else
                    <div class="form-group">

                        <label>Ganti PDF (Opsional)</label>

                        <input type="file"
                               name="pdf"
                               class="form-control"
                               accept=".pdf">

                    </div>
                    @endif

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary"
                            data-dismiss="modal">
                        Batal
                    </button>

                    <button class="btn btn-primary">
                        Update
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@endunless

{{-- ==================== MODAL HAPUS ==================== --}}

@unless($item->is_system_template)
<div class="modal fade" id="hapusModal_{{ $item->id }}">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="{{ route('PDF.destroy',$item->id) }}"
                  method="POST">

                @csrf
                @method('DELETE')

                <div class="modal-header bg-danger text-white">

                    <h5>Hapus File</h5>

                    <button class="close text-white"
                            data-dismiss="modal">
                        &times;
                    </button>

                </div>

                <div class="modal-body text-center">

                    <p>Yakin ingin menghapus file</p>

                    <strong>{{ $item->nama_file }}</strong>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-secondary"
                            data-dismiss="modal">
                        Batal
                    </button>

                    <button class="btn btn-danger">
                        Hapus
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@endunless

@endforeach
</div>


<div id="pdfViewerPage">

    <div class="viewer-header">

        <div class="viewer-left">

            <button id="btnBack"
                    class="btn btn-light">

                ←

            </button>

            <strong id="viewerTitle"></strong>

        </div>

        <div class="viewer-right">

            <button
                class="btn btn-light"
                id="zoomOut">

                -

            </button>

            <button
                class="btn btn-light"
                id="zoomIn">

                +

            </button>

        </div>

    </div>

    <div id="viewerBody">

    </div>

</div>

@include('partials.document_editor_tools')

@push('script')
<script>

document.querySelectorAll('[data-target^="#editModal_"], [data-target="#buatDokumenModal"], [data-target="#tambahModal"]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
        const modal = document.querySelector(trigger.getAttribute('data-target'));
        window.setTimeout(function () {
            if (!modal) return;
            modal.scrollTop = 0;
            const modalBody = modal.querySelector('.modal-body');
            if (modalBody) modalBody.scrollTop = 0;
        }, 0);
    });
});

function clientEditorCommand(editorId, command, value) {
    const editor = document.getElementById(editorId);
    editor.focus();
    document.execCommand(command, false, value || null);
}

const clientDocumentForm = document.getElementById('clientDocumentForm');
if (clientDocumentForm) {
    clientDocumentForm.addEventListener('submit', function () {
        document.getElementById('clientDocumentContent').value = document.getElementById('clientDocumentEditor').innerHTML;
    });
}

document.querySelectorAll('.client-edit-document-form').forEach(function (form) {
    form.addEventListener('submit', function () {
        const fileId = form.dataset.fileId;
        const editor = document.getElementById('clientEditEditor_' + fileId);
        const contentInput = document.getElementById('clientEditContent_' + fileId);
        if (editor && contentInput) contentInput.value = editor.innerHTML;
    });
});

@if($errors->any() && old('form_type') === 'create_document')
    $('#buatDokumenModal').modal('show');
@elseif($errors->any() && old('edit_file_id'))
    $('#editModal_{{ (int) old('edit_file_id') }}').modal('show');
@endif

const viewer = document.getElementById("pdfViewerPage");
const content = document.getElementById("pageContent");
const viewerBody = document.getElementById("viewerBody");
const viewerTitle = document.getElementById("viewerTitle");

let currentPdf=null;

let currentUrl=null;

let scale=1.35;

document.querySelectorAll(".btn-preview").forEach(button => {

    button.addEventListener("click", function () {

        content.style.display = "none";
        viewer.style.display = "block";

        viewerTitle.innerHTML = this.dataset.title;

        loadPDF(this.dataset.pdf);

    });

});

document.getElementById("zoomIn").onclick=async function(){

    scale+=0.2;

    await renderAllPages();

}

document.getElementById("zoomOut").onclick=async function(){

    if(scale<=0.6) return;

    scale-=0.2;

    await renderAllPages();

}

document.getElementById("btnBack").addEventListener("click", function () {

    viewer.style.display = "none";
    content.style.display = "block";

    viewerBody.innerHTML = "";

    currentPdf = null;

});

async function loadPDF(url){

    viewerBody.innerHTML = `
        <div class="text-center mt-5">
            <div class="spinner-border text-primary"></div>
            <br>
            Memuat PDF...
        </div>
    `;

    const loadingTask = pdfjsLib.getDocument(url);

    currentPdf=await loadingTask.promise;

    currentUrl=url;

    viewerBody.innerHTML = "";

    for(let page = 1; page <= currentPdf.numPages; page++){

        await renderPage(page);

    }

}

async function renderPage(pageNumber){

    const page = await currentPdf.getPage(pageNumber);

    const viewport = page.getViewport({
        scale: scale
    });

    const canvas = document.createElement("canvas");

    canvas.className = "pdf-page";

    const context = canvas.getContext("2d");

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    viewerBody.appendChild(canvas);

    await page.render({

        canvasContext: context,

        viewport: viewport

    }).promise;

}

async function renderAllPages(){

    viewerBody.innerHTML="";

    for(let page=1;page<=currentPdf.numPages;page++){

        await renderPage(page);

    }

}

viewerBody.addEventListener("wheel",async function(e){

    if(!e.ctrlKey) return;

    e.preventDefault();

    if(e.deltaY<0){

        scale+=0.1;

    }else{

        if(scale<=0.6) return;

        scale-=0.1;

    }

    await renderAllPages();

},{passive:false});

</script>
@endpush
@endsection
