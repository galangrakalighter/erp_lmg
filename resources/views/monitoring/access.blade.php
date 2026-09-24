@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1500px] space-y-5">
        <header class="flex flex-wrap items-end justify-between gap-5 rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
            <div><p class="text-[11px] font-bold uppercase tracking-[.24em] text-lime-300">Monitoring · Pengaturan Akses</p><h1 class="mt-3 text-3xl font-semibold tracking-tight">Atur akses pengguna</h1><p class="mt-2 max-w-3xl text-sm text-zinc-400">Tentukan siapa yang boleh membagikan layar dan siapa yang boleh memantaunya. Pengaturan ini tidak bergantung pada divisi.</p></div>
            <a href="{{ route('monitoring.index') }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-zinc-950 hover:bg-zinc-200">Kembali ke Monitoring</a>
        </header>

        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full min-w-[900px] text-left">
                <thead class="bg-zinc-950 text-[11px] font-bold uppercase tracking-wider text-white"><tr><th class="px-5 py-4">Pengguna target</th><th class="px-5 py-4">Boleh share layar</th><th class="px-5 py-4">Pengguna yang boleh memantau</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                @foreach($users as $target)
                    <tr class="align-top">
                        <td class="px-5 py-4"><form id="monitoring-access-{{ $target->id }}" method="POST" action="{{ route('monitoring.access.update', $target) }}">@csrf @method('PUT')</form><p class="font-semibold text-zinc-950">{{ $target->name ?: 'Tanpa nama' }}</p><p class="mt-1 text-sm text-zinc-500">{{ $target->email }}</p></td>
                        <td class="px-5 py-4"><label class="inline-flex cursor-pointer items-center gap-2"><input form="monitoring-access-{{ $target->id }}" type="hidden" name="monitoring_share_enabled" value="0"><input form="monitoring-access-{{ $target->id }}" name="monitoring_share_enabled" value="1" type="checkbox" @checked($target->monitoring_share_enabled) class="h-4 w-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-900"><span class="text-sm text-zinc-700">Diizinkan</span></label></td>
                        <td class="px-5 py-4"><select form="monitoring-access-{{ $target->id }}" name="viewer_ids[]" multiple size="4" class="min-w-72 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-800 outline-none focus:border-zinc-950">@foreach($users->where('id', '!=', $target->id) as $viewer)<option value="{{ $viewer->id }}" @selected($target->monitoringViewers->contains('id', $viewer->id))>{{ $viewer->name ?: $viewer->email }} — {{ $viewer->email }}</option>@endforeach</select><p class="mt-1 text-xs text-zinc-500">Gunakan Ctrl/Cmd untuk memilih lebih dari satu pengguna.</p></td>
                        <td class="px-5 py-4 text-right"><button form="monitoring-access-{{ $target->id }}" class="rounded-lg bg-zinc-950 px-4 py-2 text-sm font-bold text-white hover:bg-zinc-800">Simpan</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </section>
    </div>
</main>
@endsection
