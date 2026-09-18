@extends('layout.app')

@section('content')
<style>
    .client-pdf-viewer { position: fixed; inset: 0; z-index: 1300; display: flex; flex-direction: column; background: #d9d9d9; }
    .client-pdf-viewer__header { display: flex; min-height: 64px; align-items: center; gap: 12px; border-bottom: 1px solid #e4e4e7; background: #fff; padding: 10px 24px; }
    .client-pdf-viewer__title { min-width: 0; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 15px; font-weight: 800; text-transform: uppercase; color: #161616; }
    .client-pdf-viewer__button { display: inline-flex; height: 40px; min-width: 40px; align-items: center; justify-content: center; border: 0; border-radius: 8px; background: #f7f7f8; color: #161616; font-size: 15px; font-weight: 700; transition: background .15s ease; }
    .client-pdf-viewer__button:hover { background: #e4e4e7; }
    .client-pdf-viewer__body { height: calc(100vh - 64px); overflow: auto; padding: 35px; display: flex; flex-direction: column; align-items: center; background: #d9d9d9; }
    .client-pdf-viewer__page { position: relative; margin-bottom: 25px; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.18); }
    .client-pdf-viewer__page canvas { display: block; }
    .client-pdf-viewer__link { position: absolute; display: block; z-index: 2; cursor: pointer; }
    .client-pdf-viewer__link:hover { outline: 2px solid rgba(37, 99, 235, .55); outline-offset: -2px; background: rgba(59, 130, 246, .08); }
</style>

<main class="client-pdf-viewer">
    <header class="client-pdf-viewer__header">
        <button type="button" onclick="window.history.back()" class="client-pdf-viewer__button" aria-label="Kembali">←</button>
        <strong class="client-pdf-viewer__title">{{ $file->nama_file }}</strong>
        <button id="zoomOut" type="button" class="client-pdf-viewer__button" aria-label="Perkecil">−</button>
        <button id="zoomIn" type="button" class="client-pdf-viewer__button" aria-label="Perbesar">+</button>
    </header>
    <div id="pdfBody" class="client-pdf-viewer__body">
        <div class="mt-5 text-center text-sm text-zinc-600"><div class="spinner-border spinner-border-sm text-dark"></div><p class="mt-2">Memuat PDF…</p></div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const body = document.getElementById('pdfBody');
    const sourceUrl = @json(route('PDF.files.preview', $file));
    let pdf = null;
    let scale = 1.35;

    async function addLinkAnnotations(page, viewport, wrapper) {
        const annotations = await page.getAnnotations();
        annotations.filter(annotation => annotation.subtype === 'Link' && annotation.rect && (annotation.url || annotation.unsafeUrl))
            .forEach(function (annotation) {
                const rect = viewport.convertToViewportRectangle(annotation.rect);
                const link = document.createElement('a');
                link.className = 'client-pdf-viewer__link';
                link.href = annotation.url || annotation.unsafeUrl;
                link.style.left = Math.min(rect[0], rect[2]) + 'px';
                link.style.top = Math.min(rect[1], rect[3]) + 'px';
                link.style.width = Math.abs(rect[0] - rect[2]) + 'px';
                link.style.height = Math.abs(rect[1] - rect[3]) + 'px';
                link.title = 'Buka tautan';
                wrapper.appendChild(link);
            });
    }

    async function render() {
        if (!pdf) return;
        body.innerHTML = '';
        for (let number = 1; number <= pdf.numPages; number++) {
            const page = await pdf.getPage(number);
            const viewport = page.getViewport({ scale });
            const wrapper = document.createElement('div');
            wrapper.className = 'client-pdf-viewer__page';
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            wrapper.appendChild(canvas);
            body.appendChild(wrapper);
            await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
            await addLinkAnnotations(page, viewport, wrapper);
        }
    }

    try {
        pdf = await pdfjsLib.getDocument(sourceUrl).promise;
        await render();
    } catch (error) {
        body.innerHTML = '<div class="mt-5 rounded-lg bg-white px-5 py-4 text-sm text-red-600 shadow">PDF tidak dapat ditampilkan.</div>';
    }

    document.getElementById('zoomIn').addEventListener('click', async function () {
        if (pdf && scale < 3) { scale += .2; await render(); }
    });
    document.getElementById('zoomOut').addEventListener('click', async function () {
        if (pdf && scale > .6) { scale -= .2; await render(); }
    });
});
</script>
@endsection
