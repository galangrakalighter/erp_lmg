@extends('layout.app')

@section('content')
<div class="lmg-master-page">
@include('partials.lmg_legacy_workspace')
<style>
    .personal-folder-card { transition:all .25s ease; text-decoration:none!important; }
    .personal-folder-card:hover { transform:translateY(-4px); box-shadow:0 10px 20px rgba(0,0,0,.1)!important; }
    .personal-folder-icon { width:46px;height:46px;display:grid;place-items:center;border-radius:12px;background:#fff4d6;color:#f6c23e;font-size:1.35rem; }
</style>

<div class="mb-4">
    <h1 class="h4 text-gray-800 font-weight-bold mb-0">{{ $title }}</h1>
    <p class="text-muted small mb-0 font-italic">Folder divisi yang diberikan kepada jabatan Anda oleh admin.</p>
</div>

<div class="row">
    @forelse($folders as $folder)
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('master-folder.show', ['folder' => $folder, 'from' => 'mine']) }}" class="personal-folder-card card shadow-sm border-0 border-left-warning h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="personal-folder-icon"><i class="fas fa-folder-open"></i></div>
                        <span class="badge badge-info">{{ $folder->division->name ?? 'Divisi' }}</span>
                    </div>
                    <h6 class="font-weight-bold text-gray-800 mb-1 text-truncate" title="{{ $folder->name }}">{{ $folder->name }}</h6>
                    <p class="small text-muted mb-3" style="min-height:38px">{{ \Illuminate\Support\Str::limit($folder->description ?: 'Tanpa deskripsi', 70) }}</p>
                    <hr class="my-2" style="border-top:1px dashed #e3e6f0">
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="small text-muted"><i class="fas fa-file mr-1"></i>{{ $folder->files_count }} file dapat dilihat</span>
                        <span class="small text-primary">Buka <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="card shadow-sm border-0 text-center py-5"><div class="card-body text-muted">
            <i class="fas fa-folder-open fa-3x text-warning mb-3"></i>
            <h6 class="font-weight-bold text-gray-700">Belum ada folder yang diberikan kepada jabatan Anda</h6>
            <p class="small mb-0">Folder akan muncul otomatis setelah admin memberikan akses.</p>
        </div></div></div>
    @endforelse
</div>
</div>
@endsection
