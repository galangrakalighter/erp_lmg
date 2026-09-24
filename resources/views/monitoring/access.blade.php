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
                        <td class="px-5 py-4">
                            <div data-monitoring-picker class="min-w-80">
                                <button type="button" data-picker-toggle class="flex w-full items-center justify-between gap-3 rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-left text-sm transition hover:border-zinc-500 focus:border-zinc-950 focus:outline-none">
                                    <span data-picker-summary class="truncate font-medium text-zinc-700">Pilih pengguna</span>
                                    <svg class="h-4 w-4 shrink-0 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div data-picker-panel hidden class="mt-2 rounded-xl border border-zinc-200 bg-zinc-50 p-3 shadow-sm">
                                    <div class="relative"><input data-picker-search type="search" placeholder="Cari nama atau email…" class="w-full rounded-lg border border-zinc-300 bg-white py-2 pl-3 pr-3 text-sm outline-none focus:border-zinc-950"><span class="pointer-events-none absolute right-3 top-2.5 text-xs text-zinc-400">Cari</span></div>
                                    <div data-picker-options class="mt-2 max-h-48 space-y-1 overflow-y-auto pr-1">
                                    @foreach($users->where('id', '!=', $target->id) as $viewer)
                                        <label data-picker-option class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 hover:bg-white">
                                            <input form="monitoring-access-{{ $target->id }}" name="viewer_ids[]" value="{{ $viewer->id }}" type="checkbox" @checked($target->monitoringViewers->contains('id', $viewer->id)) class="h-4 w-4 shrink-0 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-900">
                                            <span class="min-w-0"><span class="block truncate text-sm font-medium text-zinc-800">{{ $viewer->name ?: $viewer->email }}</span><span class="block truncate text-xs text-zinc-500">{{ $viewer->email }}</span></span>
                                        </label>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right"><button form="monitoring-access-{{ $target->id }}" class="rounded-lg bg-zinc-950 px-4 py-2 text-sm font-bold text-white hover:bg-zinc-800">Simpan</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </section>
    </div>
</main>
@endsection

@push('script')
<script>
(() => {
    const updateSummary = picker => {
        const selected = [...picker.querySelectorAll('input[type="checkbox"]:checked')];
        const summary = picker.querySelector('[data-picker-summary]');
        if (!selected.length) return summary.textContent = 'Belum ada pemantau dipilih';
        const names = selected.map(input => input.closest('label').querySelector('.font-medium').textContent.trim());
        summary.textContent = names.length === 1 ? names[0] : `${names.length} pengguna dapat memantau`;
    };

    document.querySelectorAll('[data-monitoring-picker]').forEach(picker => {
        const panel = picker.querySelector('[data-picker-panel]');
        picker.querySelector('[data-picker-toggle]').addEventListener('click', () => {
            document.querySelectorAll('[data-picker-panel]').forEach(other => { if (other !== panel) other.hidden = true; });
            panel.hidden = !panel.hidden;
            if (!panel.hidden) panel.querySelector('[data-picker-search]').focus();
        });
        picker.querySelector('[data-picker-search]').addEventListener('input', event => {
            const query = event.target.value.toLowerCase().trim();
            picker.querySelectorAll('[data-picker-option]').forEach(option => option.hidden = !option.textContent.toLowerCase().includes(query));
        });
        picker.querySelectorAll('input[type="checkbox"]').forEach(input => input.addEventListener('change', () => updateSummary(picker)));
        updateSummary(picker);
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('[data-monitoring-picker]')) document.querySelectorAll('[data-picker-panel]').forEach(panel => panel.hidden = true);
    });
})();
</script>
@endpush
