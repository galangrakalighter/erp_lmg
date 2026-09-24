@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1400px] space-y-5">
        <header class="flex flex-wrap items-end justify-between gap-5 rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
            <div><p class="text-[11px] font-bold uppercase tracking-[.24em] text-lime-300">Monitoring · Pengaturan Akses</p><h1 class="mt-3 text-3xl font-semibold tracking-tight">Akses monitoring</h1><p class="mt-2 max-w-3xl text-sm text-zinc-400">Atur pasangan pengguna yang membagikan layar dan pengguna yang dapat memantaunya. Tidak bergantung pada divisi.</p></div>
            <div class="flex flex-wrap gap-2"><a href="{{ route('monitoring.index') }}" class="rounded-xl border border-white/20 px-4 py-2.5 text-sm font-bold text-white hover:bg-white/10">Kembali</a><button type="button" data-open-modal="create-access" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-zinc-950 hover:bg-zinc-200">+ Tambah Akses</button></div>
        </header>

        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>@endif

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4"><div><p class="text-xs font-bold uppercase tracking-[.16em] text-zinc-500">Daftar akses</p><p class="mt-1 text-sm text-zinc-600">{{ $accesses->count() }} akses monitoring terdaftar</p></div></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left">
                <thead class="bg-zinc-950 text-[11px] font-bold uppercase tracking-wider text-white"><tr><th class="px-5 py-4">No</th><th class="px-5 py-4">Pengguna membagikan layar</th><th class="px-5 py-4">Pengguna yang memantau</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                @forelse($accesses as $access)
                    <tr>
                        <td class="px-5 py-4 text-sm font-semibold text-zinc-500">{{ $loop->iteration }}</td>
                        <td class="px-5 py-4"><p class="font-semibold text-zinc-950">{{ $access->target->name ?: 'Tanpa nama' }}</p><p class="mt-1 text-sm text-zinc-500">{{ $access->target->email }}</p></td>
                        <td class="px-5 py-4"><p class="font-semibold text-zinc-950">{{ $access->viewer->name ?: 'Tanpa nama' }}</p><p class="mt-1 text-sm text-zinc-500">{{ $access->viewer->email }}</p></td>
                        <td class="px-5 py-4"><div class="flex justify-end gap-2"><button type="button" data-open-modal="edit-access-{{ $access->id }}" class="rounded-lg border border-zinc-200 px-3 py-2 text-sm font-bold text-zinc-700 hover:border-zinc-950 hover:bg-zinc-950 hover:text-white">Edit</button><form method="POST" action="{{ route('monitoring.access.destroy', $access) }}" onsubmit="return confirm('Hapus akses monitoring ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-2 text-sm font-bold text-red-700 hover:bg-red-50">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-14 text-center text-sm text-zinc-500">Belum ada akses monitoring. Klik <b>Tambah Akses</b> untuk membuat izin pertama.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>
    </div>
</main>

<dialog id="create-access" class="w-[min(94vw,560px)] rounded-2xl border-0 bg-transparent p-0 shadow-2xl backdrop:bg-zinc-950/55">
    <form method="POST" action="{{ route('monitoring.access.store') }}" class="overflow-hidden rounded-2xl bg-white">@csrf
        <div class="flex items-center justify-between bg-zinc-950 px-5 py-4 text-white"><h2 class="font-semibold">Tambah akses monitoring</h2><button type="button" data-close-modal class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 text-lg hover:bg-white/20">×</button></div>
        <div class="space-y-5 p-5"><p class="text-sm leading-6 text-zinc-600">Pengguna target akan dapat membagikan layar. Pengguna pemantau hanya dapat melihat target tersebut.</p>@include('monitoring.partials.access-form', ['access' => null, 'users' => $users])</div>
        <div class="flex justify-end gap-2 border-t border-zinc-100 px-5 py-4"><button type="button" data-close-modal class="rounded-lg px-4 py-2 text-sm font-bold text-zinc-600 hover:bg-zinc-100">Batal</button><button class="rounded-lg bg-zinc-950 px-4 py-2 text-sm font-bold text-white">Simpan akses</button></div>
    </form>
</dialog>

@foreach($accesses as $access)
<dialog id="edit-access-{{ $access->id }}" class="w-[min(94vw,560px)] rounded-2xl border-0 bg-transparent p-0 shadow-2xl backdrop:bg-zinc-950/55">
    <form method="POST" action="{{ route('monitoring.access.update', $access) }}" class="overflow-hidden rounded-2xl bg-white">@csrf @method('PUT')
        <div class="flex items-center justify-between bg-zinc-950 px-5 py-4 text-white"><h2 class="font-semibold">Ubah akses monitoring</h2><button type="button" data-close-modal class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 text-lg hover:bg-white/20">×</button></div>
        <div class="space-y-5 p-5">@include('monitoring.partials.access-form', ['access' => $access, 'users' => $users])</div>
        <div class="flex justify-end gap-2 border-t border-zinc-100 px-5 py-4"><button type="button" data-close-modal class="rounded-lg px-4 py-2 text-sm font-bold text-zinc-600 hover:bg-zinc-100">Batal</button><button class="rounded-lg bg-zinc-950 px-4 py-2 text-sm font-bold text-white">Simpan perubahan</button></div>
    </form>
</dialog>
@endforeach
@endsection

@push('script')
<script>
document.querySelectorAll('[data-open-modal]').forEach(button => button.addEventListener('click', () => document.getElementById(button.dataset.openModal)?.showModal()));
document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', () => button.closest('dialog').close()));
document.querySelectorAll('dialog').forEach(dialog => dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); }));
</script>
@endpush
