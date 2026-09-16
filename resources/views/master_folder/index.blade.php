@extends('layout.app')
@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
 <div class="mx-auto max-w-[1600px] space-y-5">
  <header class="relative overflow-hidden rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
   <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>
    <div class="relative flex flex-wrap items-end justify-between gap-5">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[.24em] text-lime-300">Lighter Media Group · Shared Library</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight">Folder Master</h1>
            <p class="mt-2 text-sm text-zinc-400">Dokumen terpusat dengan akses berdasarkan divisi dan jabatan.</p>
        </div>
        @if($isSuperAdmin)
        <button type="button" data-open="create-folder" class="rounded-xl bg-lime-300 px-5 py-2.5 text-sm font-bold text-zinc-950 transition hover:bg-lime-200">＋ Folder Baru</button>
        @endif
    </div>
  </header>
  @if(session('success'))
  <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <strong>Data belum dapat disimpan.</strong>
        <ul class="mt-1 list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif
  <form method="GET" action="{{ route('master-folder.index') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm">
    <label class="min-w-64 flex-1">
        <span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-zinc-500">Filter divisi</span>
        <span class="lmg-select-shell">
            <span class="lmg-select-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M7 12h10M10 18h4"/></svg></span>
            <select name="division_id" class="lmg-filter-select" onchange="this.form.submit()">
                <option value="">Semua Divisi</option>
                @foreach($divisions as $division)
                <option value="{{ $division->id }}" @selected((int)$selectedDivisionId===(int)$division->id)>{{ $division->name }}</option>
                @endforeach
            </select>
        </span>
    </label>
   @if($selectedDivisionId)
    <a href="{{ route('master-folder.index') }}" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-600">Reset</a>
   @endif
  </form>
    <div>
        <p class="text-xs font-bold uppercase tracking-[.18em] text-zinc-400">Direktori bersama</p>
        <h2 class="mt-1 text-xl font-semibold text-zinc-950">{{ $folders->count() }} folder tersedia</h2>
    </div>
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
    @forelse($folders as $folder)
        <article class="flex min-h-64 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-950/5">
            <div class="flex items-start justify-between gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-lime-100 text-lime-800">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                </div>
                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-bold text-zinc-600">{{ $folder->division->name ?? 'Tanpa divisi' }}</span>
            </div>
            <a href="{{ route('master-folder.show',$folder) }}" class="mt-5">
                <h3 class="truncate text-base font-semibold text-zinc-950">{{ $folder->name }}</h3>
                <p class="mt-2 min-h-10 text-sm leading-5 text-zinc-500">{{ Illuminate\Support\Str::limit($folder->description ?: 'Tanpa deskripsi',90) }}</p>
            </a>
            @if($isSuperAdmin)
            <div class="mt-3 flex flex-wrap gap-1">
                @foreach($folder->roles->take(3) as $role)
                    <span class="rounded-full border border-zinc-200 px-2 py-1 text-[9px] font-bold text-zinc-500">{{ $role->role_name }}</span>
                @endforeach
                @if($folder->roles->count()>3)
                    <span class="px-1 py-1 text-[10px] text-zinc-400">+{{ $folder->roles->count()-3 }}</span>
                @endif
            </div>
            @endif
            <div class="mt-auto flex items-center justify-between border-t border-zinc-100 pt-4">
                <a href="{{ route('master-folder.show',$folder) }}" class="text-xs font-bold text-zinc-500 hover:text-zinc-950">{{ $folder->files_count }} file →</a>
                @if($isSuperAdmin)<div class="flex gap-1">
                    <button type="button" data-open="edit-folder-{{ $folder->id }}" class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-bold text-amber-700">Edit</button>
                    <form method="POST" action="{{ route('master-folder.destroy',$folder) }}" onsubmit="return confirm('Hapus folder dan seluruh file di dalamnya?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-bold text-rose-700">Hapus</button>
                    </form>
                </div>
                @endif
            </div>
        </article>
    @empty
    <div class="col-span-full rounded-3xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center">
        <div class="mx-auto h-14 w-14 rounded-2xl bg-zinc-100"></div>
        <h3 class="mt-4 font-semibold">Belum ada folder</h3>
        <p class="mt-1 text-sm text-zinc-500">{{ $isSuperAdmin ? 'Buat folder baru untuk memulai.' : 'Belum ada folder yang diberikan untuk jabatan Anda.' }}</p>
    </div>
    @endforelse
  </section>
 </div>
</main>

@if($isSuperAdmin)
<div id="create-folder" class="lmg-modal fixed inset-0 z-[100000] hidden items-start justify-center overflow-y-auto bg-zinc-950/60 px-4 py-8 backdrop-blur-sm">
    <form method="POST" action="{{ route('master-folder.store') }}" class="flex max-h-[calc(100dvh-4rem)] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
        @csrf
            <div class="sticky top-0 flex items-center justify-between border-b border-zinc-100 bg-white px-6 py-5">
                <h3 class="text-xl font-semibold">Buat Folder Baru</h3>
                <button type="button" data-close class="rounded-xl bg-zinc-100 px-3 py-2">✕</button>
            </div>
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-6">
                <label class="block"><span class="lmg-label">Nama folder</span>
                    <input name="name" value="{{ old('name') }}" maxlength="150" required class="lmg-input">
                </label>
                <label class="block">
                    <span class="lmg-label">Deskripsi</span>
                    <textarea name="description" rows="3" class="lmg-input">{{ old('description') }}</textarea>
                </label>
                <label class="block">
                    <span class="lmg-label">Divisi utama</span>
                    <select name="division_id" required class="lmg-input">
                        <option value="">Pilih divisi</option>
                        @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @selected((int)old('division_id')===(int)$division->id)>{{ $division->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div>
                    <span class="lmg-label">Role yang dapat melihat</span>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        @foreach($rolesByDivision as $divisionRoles)
                            @foreach($divisionRoles as $role)
                                <label class="flex items-center gap-2 rounded-xl border border-zinc-200 p-3 text-sm">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id,old('role_ids',[]))) class="rounded border-zinc-300 text-zinc-950 focus:ring-zinc-900">
                                    <span>{{ $role->role_name }} <small class="block text-zinc-400">{{ $role->division->name ?? '-' }}</small></span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="sticky bottom-0 flex justify-end gap-2 border-t bg-zinc-50 px-6 py-4">
                <button type="button" data-close class="rounded-xl border px-4 py-2.5 text-sm font-semibold">Batal</button>
                <button class="rounded-xl bg-zinc-950 px-5 py-2.5 text-sm font-bold text-white">Simpan Folder</button>
            </div>
    </form>
</div>
@foreach($folders as $folder)
<div id="edit-folder-{{ $folder->id }}" class="lmg-modal fixed inset-0 z-[100000] hidden items-start justify-center overflow-y-auto bg-zinc-950/60 px-4 py-8 backdrop-blur-sm">
    <form method="POST" action="{{ route('master-folder.update',$folder) }}" class="flex max-h-[calc(100dvh-4rem)] w-full max-w-3xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
        @csrf
        @method('PUT')
        <div class="flex items-center justify-between border-b bg-white px-6 py-5">
            <h3 class="text-xl font-semibold">Edit Folder</h3>
            <button type="button" data-close class="rounded-xl bg-zinc-100 px-3 py-2">✕</button>
        </div>
        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-6">
            <label class="block">
                <span class="lmg-label">Nama folder</span>
                <input name="name" value="{{ $folder->name }}" required class="lmg-input"></label>
                <label class="block">
                    <span class="lmg-label">Deskripsi</span>
                    <textarea name="description" rows="3" class="lmg-input">{{ $folder->description }}</textarea>
                </label>
                <label class="block">
                    <span class="lmg-label">Divisi utama</span>
                    <select name="division_id" required class="lmg-input">
                        @foreach($divisions as $division)
                        <option value="{{ $division->id }}" @selected($folder->division_id===$division->id)>{{ $division->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div>
                    <span class="lmg-label">Role yang dapat melihat</span>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        @foreach($rolesByDivision as $divisionRoles)
                            @foreach($divisionRoles as $role)
                            <label class="flex items-center gap-2 rounded-xl border border-zinc-200 p-3 text-sm">
                                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked($folder->roles->contains('id',$role->id)) class="rounded">
                                    <span>{{ $role->role_name }} <small class="block text-zinc-400">{{ $role->division->name ?? '-' }}</small></span>
                            </label>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t bg-zinc-50 px-6 py-4">
                <button type="button" data-close class="rounded-xl border px-4 py-2.5 text-sm font-semibold">Batal</button>
                <button class="rounded-xl bg-zinc-950 px-5 py-2.5 text-sm font-bold text-white">Simpan Perubahan</button>
            </div>
    </form>
</div>
@endforeach
@endif
<style>
.lmg-label{margin-bottom:.375rem;display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#71717a}
.lmg-input,.lmg-filter-select{width:100%;border-radius:.75rem;border:1px solid #e4e4e7;background-color:#fafafa;padding:.65rem .75rem;font-size:.875rem;color:#27272a;transition:border-color .2s,box-shadow .2s,background-color .2s}
.lmg-input:focus,.lmg-filter-select:focus{border-color:#18181b;outline:none;box-shadow:0 0 0 3px rgba(24,24,27,.08);background-color:#fff}
.lmg-select-shell{position:relative;display:block;width:100%}
.lmg-select-icon{pointer-events:none;position:absolute;left:5px;top:5px;bottom:5px;z-index:1;display:flex;width:36px;align-items:center;justify-content:center;border-radius:9px;background:#161616;color:#fff}
.lmg-select-icon svg{width:16px;height:16px}
.lmg-filter-select{min-height:46px;cursor:pointer;appearance:none;padding-left:52px;padding-right:2.75rem;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23161616' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .9rem center;background-size:16px;font-weight:650;box-shadow:0 1px 2px rgba(22,22,22,.04)}
.lmg-filter-select:hover{border-color:#a1a1aa;background-color:#fff}
select.lmg-input{cursor:pointer;appearance:none;padding-right:2.75rem;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2352525b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .9rem center}
</style>
<script>document.addEventListener('DOMContentLoaded',()=>{const close=m=>{m.classList.add('hidden');m.classList.remove('flex');document.body.style.overflow=''};document.addEventListener('click',e=>{const opener=e.target.closest('[data-open]');if(opener){const modal=document.getElementById(opener.dataset.open);modal?.classList.remove('hidden');modal?.classList.add('flex');document.body.style.overflow='hidden'}const closer=e.target.closest('[data-close]');if(closer)close(closer.closest('.lmg-modal'));if(e.target.classList.contains('lmg-modal'))close(e.target)})});</script>
@endsection
