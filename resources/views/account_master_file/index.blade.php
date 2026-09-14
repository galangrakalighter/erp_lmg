@extends('layout.app')
@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
 <div class="mx-auto max-w-[1600px] space-y-5">
  <header class="relative overflow-hidden rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
   <div class="absolute -right-20 -top-24 h-72 w-72 rounded-full bg-lime-300/10 blur-3xl"></div>
   <div class="relative"><p class="text-[11px] font-bold uppercase tracking-[.24em] text-lime-300">Lighter Media Group · Library</p><h1 class="mt-3 text-3xl font-semibold tracking-tight">File Master</h1><p class="mt-2 max-w-2xl text-sm text-zinc-400">Kelola file Anda dan akses dokumen tim sesuai jalur pelaporan.</p></div>
  </header>

  <form method="GET" class="grid gap-3 rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_1fr_auto_auto]">
   <label><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-zinc-500">Divisi</span><select name="division_id" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm focus:border-zinc-900 focus:ring-zinc-900"><option value="">Semua Divisi</option>@foreach($divisions as $division)<option value="{{ $division->id }}" @selected((int) request('division_id') === (int) $division->id)>{{ $division->name }}</option>@endforeach</select></label>
   <label><span class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-zinc-500">Jabatan</span><select name="role_id" class="w-full rounded-xl border-zinc-200 bg-zinc-50 px-3 py-2.5 text-sm focus:border-zinc-900 focus:ring-zinc-900"><option value="">Semua Jabatan</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((int) request('role_id') === (int) $role->id)>{{ $role->role_name }} · {{ $role->division->name ?? '-' }}</option>@endforeach</select></label>
   <button class="self-end rounded-xl bg-zinc-950 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-lime-400 hover:text-zinc-950">Terapkan</button>
   @if(request()->hasAny(['division_id','role_id']))<a href="{{ route('file-master.index') }}" class="self-end rounded-xl border border-zinc-200 px-4 py-2.5 text-center text-sm font-semibold text-zinc-600 hover:bg-zinc-50">Reset</a>@endif
  </form>

  <div class="flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-zinc-400">Direktori pengguna</p><h2 class="mt-1 text-xl font-semibold text-zinc-950">{{ $users->count() }} akun dapat diakses</h2></div></div>
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
   @forelse($users as $account)
   <a href="{{ route('file-master.show', $account) }}" class="group flex min-h-56 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-xl hover:shadow-zinc-950/5">
    <div class="flex items-start justify-between gap-3"><div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-zinc-950 text-sm font-bold uppercase text-lime-300">{{ Illuminate\Support\Str::substr($account->name, 0, 2) }}</div><span class="rounded-full bg-lime-50 px-2.5 py-1 text-xs font-bold text-lime-800">{{ $account->account_master_files_count }} file</span></div>
    <div class="mt-5 min-w-0"><h3 class="truncate text-base font-semibold text-zinc-950">{{ $account->name }}</h3><p class="mt-1 truncate text-sm text-zinc-500">{{ $account->email }}</p></div>
    <div class="mt-4 flex flex-wrap gap-1.5">@forelse($account->userDivisions as $assignment)<span class="rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[10px] font-bold text-zinc-600">{{ $assignment->division->name ?? '-' }} · {{ $assignment->role->role_name ?? '-' }}</span>@empty<span class="text-xs text-zinc-400">Belum memiliki divisi</span>@endforelse</div>
    <div class="mt-auto flex items-center justify-between border-t border-zinc-100 pt-4 text-xs font-bold text-zinc-400"><span>Buka direktori</span><svg class="h-4 w-4 transition group-hover:translate-x-1 group-hover:text-zinc-950" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
   </a>
   @empty
   <div class="col-span-full rounded-3xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center"><div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-zinc-100 text-zinc-400"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg></div><h3 class="mt-4 font-semibold text-zinc-900">Tidak ada akun ditemukan</h3><p class="mt-1 text-sm text-zinc-500">Ubah filter atau periksa akses jalur pelaporan Anda.</p></div>
   @endforelse
  </section>
 </div>
</main>
@endsection
