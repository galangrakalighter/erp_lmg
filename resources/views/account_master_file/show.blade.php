@extends('layout.app')

@section('content')
<style>
    .account-document-workspace.fullscreen { position:fixed; inset:0; z-index:100000; background:#dfe3e8; padding:16px; display:flex; flex-direction:column; }
    .account-document-workspace.fullscreen .account-editor-stage { flex:1; height:auto!important; max-height:none!important; }
    .account-editor { min-height:650px; background:#fff; padding:48px; outline:none; line-height:1.65; overflow-wrap:anywhere; }
    .account-editor img { max-width:100%; height:auto; }
    .fullscreen-exit { display:none; position:fixed; top:14px; right:18px; z-index:100002; }
    .account-document-workspace.fullscreen .fullscreen-exit { display:block; }
</style>
<div class="d-flex justify-content-between align-items-start mb-4">
    <div><a href="{{ route('file-master.index') }}" class="btn btn-sm btn-light border mb-3"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
        <h1 class="h4 font-weight-bold text-gray-800 mb-1">File Master · {{ $owner->name }}</h1>
        <div>@foreach($owner->userDivisions as $assignment)<span class="badge badge-light border mr-1">{{ $assignment->division->name ?? '-' }} · {{ $assignment->role->role_name ?? '-' }}</span>@endforeach</div>
    </div>
    @if($canManage)<div><button class="btn btn-success btn-sm" data-toggle="modal" data-target="#createAccountDocument"><i class="fas fa-file-pdf mr-1"></i>Buat Dokumen</button> <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#uploadAccountFile"><i class="fas fa-upload mr-1"></i>Upload</button></div>@endif
</div>

<div class="card shadow-sm border-0"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead class="thead-light"><tr><th>Nama File</th><th>Tipe</th><th>Ukuran</th><th>Dibuat</th><th class="text-right">Aksi</th></tr></thead><tbody>
    @forelse($files as $file)
        @php($pdf = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf' || $file->mime_type === 'application/pdf')
        <tr><td class="font-weight-bold">@if($pdf)<a href="{{ route('file-master.files.view', ['owner'=>$owner,'fileName'=>$file->original_name]) }}">{{ $file->original_name }}</a>@else{{ $file->original_name }}@endif</td>
            <td><span class="badge badge-{{ $pdf ? 'danger' : 'secondary' }}">{{ strtoupper(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'FILE') }}</span></td>
            <td>{{ number_format($file->size / 1024, 1) }} KB</td><td>{{ $file->created_at->format('d M Y H:i') }}</td>
            <td class="text-right"><a class="btn btn-sm btn-light border" href="{{ route('file-master.files.download', ['owner'=>$owner,'fileName'=>$file->original_name]) }}"><i class="fas fa-download"></i></a>
            @if($canManage)<form class="d-inline" method="POST" action="{{ route('file-master.files.destroy', [$owner,$file]) }}" onsubmit="return confirm('Hapus file ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>@endif</td></tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-5">Belum ada file.</td></tr>@endforelse
    </tbody></table></div></div>

@if($canManage)
<div class="modal fade" id="uploadAccountFile"><div class="modal-dialog"><form method="POST" enctype="multipart/form-data" action="{{ route('file-master.upload',$owner) }}" class="modal-content">@csrf
    <div class="modal-header"><h5 class="modal-title">Upload File</h5><button class="close" type="button" data-dismiss="modal">&times;</button></div>
    <div class="modal-body"><input type="file" name="files[]" class="form-control-file" multiple required><small class="text-muted">PDF maupun format file lainnya, maksimal 25 MB per file.</small></div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button><button class="btn btn-primary">Upload</button></div></form></div></div>

<div class="modal fade" id="createAccountDocument"><div class="modal-dialog modal-xl"><form id="account-document-form" method="POST" action="{{ route('file-master.documents.store',$owner) }}" class="modal-content">@csrf
    <input type="hidden" name="content" id="account-document-content">
    <div class="modal-header"><h5 class="modal-title">Buat Dokumen PDF</h5><button class="close" type="button" data-dismiss="modal">&times;</button></div>
    <div class="modal-body bg-light"><div class="form-group"><label class="font-weight-bold">Nama File</label><div class="input-group"><input name="document_name" class="form-control" maxlength="150" required><div class="input-group-append"><span class="input-group-text">.pdf</span></div></div></div>
        <div id="account-document-workspace" class="account-document-workspace">
            <div class="bg-white border rounded-top p-2 d-flex flex-wrap">
                <select class="form-control form-control-sm mr-2" style="width:140px" onchange="document.execCommand('formatBlock',false,this.value);this.value='p'"><option value="p">Paragraf</option><option value="h1">Judul 1</option><option value="h2">Judul 2</option><option value="h3">Judul 3</option></select>
                <div class="btn-group btn-group-sm mr-2"><button type="button" class="btn btn-light border" onclick="document.execCommand('bold')"><i class="fas fa-bold"></i></button><button type="button" class="btn btn-light border" onclick="document.execCommand('italic')"><i class="fas fa-italic"></i></button><button type="button" class="btn btn-light border" onclick="document.execCommand('underline')"><i class="fas fa-underline"></i></button></div>
                <div class="btn-group btn-group-sm mr-2"><button type="button" class="btn btn-light border" onclick="document.execCommand('justifyLeft')"><i class="fas fa-align-left"></i></button><button type="button" class="btn btn-light border" onclick="document.execCommand('justifyCenter')"><i class="fas fa-align-center"></i></button><button type="button" class="btn btn-light border" onclick="document.execCommand('justifyRight')"><i class="fas fa-align-right"></i></button></div>
                <div class="btn-group btn-group-sm"><button type="button" class="btn btn-light border" onclick="document.execCommand('insertUnorderedList')"><i class="fas fa-list-ul"></i></button><button type="button" class="btn btn-light border" onclick="document.execCommand('insertOrderedList')"><i class="fas fa-list-ol"></i></button><button type="button" class="btn btn-light border" onclick="toggleAccountEditorFullscreen()"><i class="fas fa-expand"></i></button></div>
            </div>
            <div class="account-editor-stage border rounded-bottom p-4" style="height:600px;overflow:auto;background:#dfe3e8"><button type="button" class="btn btn-dark btn-sm fullscreen-exit" onclick="toggleAccountEditorFullscreen()"><i class="fas fa-compress mr-1"></i>Keluar Fullscreen</button><div id="account-document-editor" class="account-editor shadow-sm" contenteditable="true"><p>Mulai tulis dokumen di sini...</p></div></div>
        </div>
    </div><div class="modal-footer"><button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button><button class="btn btn-success">Simpan sebagai PDF</button></div></form></div></div>
@endif

<script>
function toggleAccountEditorFullscreen(){document.getElementById('account-document-workspace')?.classList.toggle('fullscreen');document.body.classList.toggle('overflow-hidden');}
document.addEventListener('DOMContentLoaded',function(){
    const editor=document.getElementById('account-document-editor'); const form=document.getElementById('account-document-form');
    if(form&&editor) form.addEventListener('submit',function(){document.getElementById('account-document-content').value=editor.innerHTML;});
    if(editor) editor.addEventListener('paste',function(event){
        const images=Array.from(event.clipboardData?.items||[]).filter(x=>x.kind==='file'&&x.type.startsWith('image/'));
        if(!images.length)return; event.preventDefault();
        images.forEach(item=>{const reader=new FileReader();reader.onload=()=>document.execCommand('insertHTML',false,'<img src="'+reader.result+'" style="max-width:100%;height:auto">');reader.readAsDataURL(item.getAsFile());});
    });
});
</script>
@endsection
