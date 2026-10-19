@once
<style>
    .lmg-document-toolbar { background:#fff; border:1px solid #dfe3e8; border-radius:.35rem .35rem 0 0; padding:.5rem; display:flex; flex-wrap:wrap; align-items:center; gap:.35rem; }
    .lmg-document-stage { height:600px; overflow:auto; padding:1.5rem; background:#dfe3e8; border:1px solid #dfe3e8; border-top:0; border-radius:0 0 .35rem .35rem; }
    .lmg-document-editor { width:100%; min-height:100%; padding:40px; background:#fff; color:#252a34; font-family:"DejaVu Sans",Arial,sans-serif; font-size:11pt; line-height:1.65; outline:none; overflow-wrap:anywhere; box-shadow:0 .125rem .25rem rgba(0,0,0,.075); }
    .lmg-document-editor h1 { font-size:22pt; margin:0 0 18px; line-height:1.25; }
    .lmg-document-editor h2 { font-size:17pt; margin:20px 0 10px; line-height:1.3; }
    .lmg-document-editor h3 { font-size:13pt; margin:16px 0 8px; }
    .lmg-document-editor p { margin:0 0 10px; }
    .lmg-document-editor ul, .lmg-document-editor ol { margin:8px 0 12px 22px; padding:0; }
    .lmg-document-editor blockquote { margin:12px 0; padding:8px 14px; border-left:3px solid #4e73df; color:#5a5c69; background:#f8f9fc; }
    .lmg-document-editor img, .lmg-document-editor table { max-width:100%; }
    .lmg-document-editor img { height:auto; }
    .lmg-editor-workspace.lmg-fullscreen { position:fixed; inset:0; z-index:100000; padding:16px; background:#dfe3e8; display:flex; flex-direction:column; }
    .lmg-editor-workspace.lmg-fullscreen .lmg-document-stage { flex:1; height:auto!important; min-height:0; }
    .lmg-editor-exit { display:none; position:fixed; right:18px; top:14px; z-index:100002; }
    .lmg-editor-workspace.lmg-fullscreen .lmg-editor-exit { display:inline-flex; }
    .lmg-editor-workspace.lmg-fullscreen .lmg-document-toolbar { padding-right:155px; }
</style>
<script>
window.lmgEditorCommand = function (editorId, command, value) {
    const editor = document.getElementById(editorId); if (!editor) return;
    editor.focus(); document.execCommand(command, false, value || null);
};
window.lmgInsertTable = function (editorId) {
    const editor = document.getElementById(editorId); if (!editor) return;
    editor.focus();
    document.execCommand('insertHTML', false, '<table style="width:100%;border-collapse:collapse"><tbody><tr><td style="border:1px solid #cfd4da;padding:6px"><br></td><td style="border:1px solid #cfd4da;padding:6px"><br></td></tr><tr><td style="border:1px solid #cfd4da;padding:6px"><br></td><td style="border:1px solid #cfd4da;padding:6px"><br></td></tr></tbody></table><p><br></p>');
};
window.lmgInsertLink = function (editorId) {
    const editor = document.getElementById(editorId); if (!editor) return;
    editor.focus();
    let url = prompt('Masukkan alamat link:', 'https://'); if (!url) return;
    url = url.trim(); if (!/^(https?:\/\/|mailto:|#)/i.test(url)) url = 'https://' + url;
    const selection = window.getSelection();
    if (selection && !selection.isCollapsed) document.execCommand('createLink', false, url);
    else document.execCommand('insertHTML', false, '<a href="' + url.replace(/"/g, '&quot;') + '" target="_blank">' + url.replace(/</g, '&lt;') + '</a>');
};
window.lmgSetTableBorderOpacity = function (editorId, opacity) {
    const editor = document.getElementById(editorId); if (!editor) return;
    const selection = window.getSelection();
    const anchor = selection && selection.anchorNode;
    const element = anchor && (anchor.nodeType === 1 ? anchor : anchor.parentElement);
    const table = (element && element.closest && editor.contains(element) ? element.closest('table') : null) || editor._activeTable;
    if (!table || !editor.contains(table)) { alert('Klik salah satu bagian tabel terlebih dahulu.'); return; }
    const alpha = Math.max(0, Math.min(1, Number(opacity)));
    const borderColor = alpha === 0 ? 'transparent' : 'rgba(0,0,0,' + alpha + ')';
    table.style.borderCollapse = 'collapse';
    table.style.borderColor = borderColor;
    table.querySelectorAll('td, th').forEach(function (cell) {
        const width = parseFloat(getComputedStyle(cell).borderTopWidth) || 1;
        cell.style.borderWidth = width + 'px';
        cell.style.borderStyle = 'solid';
        cell.style.borderColor = borderColor;
    });
    table.dataset.borderOpacity = String(alpha);
};
window.lmgToggleEditorFullscreen = function (workspaceId) {
    const workspace = document.getElementById(workspaceId); if (!workspace) return;
    const active = workspace.classList.toggle('lmg-fullscreen');
    document.body.classList.toggle('overflow-hidden', active);
    if (active) workspace.querySelector('[contenteditable="true"]')?.focus();
};
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.lmg-document-editor').forEach(function (editor) {
        editor.addEventListener('click', function (event) {
            const table = event.target.closest && event.target.closest('table');
            if (table && editor.contains(table)) editor._activeTable = table;
        });
        if (editor.dataset.richPasteManaged === '1') return;
        editor.addEventListener('paste', function (event) {
            const images = Array.from(event.clipboardData?.items || []).filter(item => item.kind === 'file' && item.type.startsWith('image/'));
            if (!images.length || event.clipboardData.getData('text/html')) return;
            event.preventDefault();
            images.forEach(function (item) {
                const reader = new FileReader();
                reader.onload = function () { editor.focus(); document.execCommand('insertHTML', false, '<img src="' + reader.result + '" style="max-width:100%;height:auto">'); };
                reader.readAsDataURL(item.getAsFile());
            });
        });
    });
});
</script>
@endonce
