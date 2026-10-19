@extends('layout.app')

@section('content')
<style>
    .pdf-preview-shell { position:fixed; inset:0; z-index:9999; background:#d9d9d9; }
    .pdf-preview-header { height:65px; padding:0 24px; background:#fff; border-bottom:1px solid #ddd; display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .pdf-preview-body { height:calc(100vh - 65px); overflow:auto; padding:35px; display:flex; flex-direction:column; align-items:center; }
    .pdf-page-wrap { position:relative; margin-bottom:25px; background:#fff; box-shadow:0 2px 12px rgba(0,0,0,.18); }
    .pdf-page-wrap canvas { display:block; }
    .pdf-link-annotation { position:absolute; display:block; cursor:pointer; background:rgba(78,115,223,.03); border-bottom:1px solid transparent; }
    .pdf-link-annotation:hover { background:rgba(78,115,223,.12); border-bottom-color:#4e73df; }
</style>

<div class="pdf-preview-shell">
    <div class="pdf-preview-header">
        <div class="d-flex align-items-center overflow-hidden">
            <a href="{{ route('master-folder.show', $folder) }}" class="btn btn-light border"><i class="fas fa-arrow-left"></i></a>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('master-folder.files.download', [$folder, $file]) }}" class="btn btn-light border mr-2" title="Download"><i class="fas fa-download"></i></a>
            <button id="pdfZoomOut" class="btn btn-light border" title="Perkecil"><i class="fas fa-minus"></i></button>
            <span id="pdfZoomLabel" class="small font-weight-bold mx-2" style="min-width:48px;text-align:center">135%</span>
            <button id="pdfZoomIn" class="btn btn-light border" title="Perbesar"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <div id="pdfPreviewBody" class="pdf-preview-body"><div class="text-center mt-5"><div class="spinner-border text-primary"></div><div class="mt-2">Memuat PDF...</div></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const body = document.getElementById('pdfPreviewBody');
    const label = document.getElementById('pdfZoomLabel');
    let pdf = null;
    let scale = 1.35;

    async function renderPages() {
        if (!pdf) return;
        body.innerHTML = '';
        label.textContent = Math.round(scale * 100) + '%';

        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            const page = await pdf.getPage(pageNumber);
            const viewport = page.getViewport({ scale: scale });
            const wrapper = document.createElement('div');
            wrapper.className = 'pdf-page-wrap';
            wrapper.style.width = viewport.width + 'px';
            wrapper.style.height = viewport.height + 'px';

            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            wrapper.appendChild(canvas);
            body.appendChild(wrapper);
            await page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise;

            const annotations = await page.getAnnotations({ intent: 'display' });
            annotations.filter(item => item.subtype === 'Link' && item.url).forEach(function (item) {
                const rectangle = viewport.convertToViewportRectangle(item.rect);
                const link = document.createElement('a');
                link.className = 'pdf-link-annotation';
                link.href = item.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.title = item.url;
                link.style.left = Math.min(rectangle[0], rectangle[2]) + 'px';
                link.style.top = Math.min(rectangle[1], rectangle[3]) + 'px';
                link.style.width = Math.abs(rectangle[2] - rectangle[0]) + 'px';
                link.style.height = Math.abs(rectangle[3] - rectangle[1]) + 'px';
                wrapper.appendChild(link);
            });
        }
    }

    try {
        if (typeof pdfjsLib === 'undefined') throw new Error('PDF viewer tidak tersedia.');
        pdf = await pdfjsLib.getDocument(@json(route('master-folder.files.preview', [$folder, $file]))).promise;
        await renderPages();
    } catch (error) {
        body.innerHTML = '<div class="alert alert-danger mt-5">PDF tidak dapat ditampilkan. Silakan gunakan tombol download.</div>';
    }

    document.getElementById('pdfZoomIn').addEventListener('click', async function () { if (pdf && scale < 3) { scale += .2; await renderPages(); } });
    document.getElementById('pdfZoomOut').addEventListener('click', async function () { if (pdf && scale > .6) { scale -= .2; await renderPages(); } });
    body.addEventListener('wheel', async function (event) {
        if (!event.ctrlKey || !pdf) return;
        event.preventDefault();
        scale = event.deltaY < 0 ? Math.min(3, scale + .1) : Math.max(.6, scale - .1);
        await renderPages();
    }, { passive:false });
});
</script>
@endsection
