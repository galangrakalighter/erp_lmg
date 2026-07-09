@include('partials.header')
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

</style>
<div id="pageContent">
<div class="container-fluid mt-4">

    <div class="card shadow">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Data File PDF
            </h6>

            <button type="button"
                    class="btn btn-primary"
                    data-toggle="modal"
                    data-target="#tambahModal">
                Tambah File
            </button>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">

                <table class="table table-bordered table-hover" id="dataTable">

                    <thead class="thead-light">
                        <tr>
                            <th width="60">No</th>
                            <th>Nama File</th>
                            <th>PDF</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($clients as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $item->nama_file }}</td>

                            <td>
                                <button
                                    class="btn btn-info btn-sm btn-preview"
                                    data-pdf="{{ asset('storage/'.$item->location) }}"
                                    data-title="{{ $item->nama_file }}">
                                    <i class="fa fa-eye"></i> Lihat
                                </button>
                            </td>

                            <td>

                                <button class="btn btn-warning btn-sm"
                                        data-toggle="modal"
                                        data-target="#editModal_{{ $item->id }}">
                                    Edit
                                </button>

                                <button class="btn btn-danger btn-sm"
                                        data-toggle="modal"
                                        data-target="#hapusModal_{{ $item->id }}">
                                    Hapus
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center">
                                Belum ada file.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

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

<div class="modal fade" id="editModal_{{ $item->id }}">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form action="{{ route('PDF.update',$item->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

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
                               value="{{ $item->nama_file }}">

                    </div>

                    <div class="form-group">

                        <label>Ganti PDF (Opsional)</label>

                        <input type="file"
                               name="pdf"
                               class="form-control"
                               accept=".pdf">

                    </div>

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

{{-- ==================== MODAL HAPUS ==================== --}}

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
pdfjsLib.GlobalWorkerOptions.workerSrc =
'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
</script>

<script>

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
@include('partials.footer')