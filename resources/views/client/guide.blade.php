@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1800px] space-y-5">
        <header class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 shadow-sm sm:px-6">
            <p class="text-[10px] font-bold uppercase tracking-[.2em] text-zinc-400">Content Guide</p>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950">Panduan Konten {{ $client->nama }}</h1>
            <p class="mt-1 text-xs text-zinc-500">Kelola Strategi, Status, Type, Pilar, Hook, Body, CTA, dan Komposisi untuk penyusunan Content Plan.</p>
        </header>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-sm" aria-label="Kategori content guide">
            @foreach($sections as $index => $section)
                <button type="button" data-guide-tab="{{ $section['key'] }}"
                    class="guide-tab whitespace-nowrap rounded-xl px-5 py-2.5 text-xs font-bold transition {{ $index === 0 ? 'bg-zinc-950 text-white' : 'text-zinc-500 hover:bg-zinc-50 hover:text-zinc-950' }}">
                    {{ $section['label'] }}
                    <span class="ml-1 opacity-60">{{ $section['items']->count() }}</span>
                </button>
            @endforeach
        </nav>

        @foreach($sections as $index => $section)
            <section data-guide-panel="{{ $section['key'] }}" class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm {{ $index === 0 ? '' : 'hidden' }}">
                <div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-950">Panduan {{ $section['label'] }}</h2>
                        <p class="mt-0.5 text-[11px] text-zinc-500">{{ $section['guidance'] ? 'Nama, fungsi singkat, contoh penggunaan, dan status.' : 'Nama dan status penggunaan.' }}</p>
                    </div>
                    @if($canManage)
                        <button type="button" data-guide-create="{{ $section['key'] }}" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white">Tambah {{ $section['label'] }}</button>
                    @endif
                </div>
                @if($canManage)
                    <form data-guide-create-form="{{ $section['key'] }}" action="{{ route('client.content-guide.store', ['client' => $client->id, 'section' => $section['key']]) }}" method="POST" class="hidden grid gap-3 border-b border-zinc-200 bg-zinc-50 p-5 md:grid-cols-2">
                        @csrf
                        <input name="name" required maxlength="255" placeholder="Nama {{ $section['label'] }}" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none">
                        <select name="status" required class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none"><option value="1">Ditampilkan</option><option value="2">Disembunyikan</option></select>
                        @if($section['guidance'])
                            <textarea name="fungsi_singkat" maxlength="1000" placeholder="Fungsi singkat" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none"></textarea>
                            <textarea name="contoh" maxlength="3000" placeholder="Contoh penggunaan" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none"></textarea>
                        @endif
                        <div class="flex justify-end gap-2 md:col-span-2"><button type="button" data-guide-create-cancel="{{ $section['key'] }}" class="rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold">Batal</button><button class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-semibold text-white">Simpan</button></div>
                    </form>
                @endif
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] border-collapse text-left">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="w-12 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">No</th>
                                <th class="w-1/5 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Nama</th>
                                @if($section['guidance'])
                                    <th class="w-1/4 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Fungsi Singkat</th>
                                    <th class="w-1/4 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Contoh</th>
                                @endif
                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Status</th>
                                @if($canManage)<th class="px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Aksi</th>@endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($section['items'] as $item)
                                <tr class="align-top transition hover:bg-zinc-50/70">
                                    <td class="px-5 py-4 text-xs font-semibold text-zinc-400">{{ $loop->iteration }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-zinc-900">{{ $item->name }}</td>
                                    @if($section['guidance'])
                                        <td class="px-5 py-4 text-sm leading-6 text-zinc-600">{{ $item->fungsi_singkat ?: '-' }}</td>
                                        <td class="px-5 py-4 text-sm italic leading-6 text-zinc-600">{{ $item->contoh ?: '-' }}</td>
                                    @endif
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status == 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">{{ $item->status == 1 ? 'Ditampilkan' : 'Disembunyikan' }}</span></td>
                                    @if($canManage)
                                        <td class="px-5 py-4">
                                            <div class="flex gap-2">
                                                <button type="button" data-guide-edit="{{ $section['key'] }}-{{ $item->id }}" class="rounded-lg border border-zinc-300 px-3 py-1.5 text-xs font-semibold">Edit</button>
                                                <form action="{{ route('client.content-guide.destroy', ['client' => $client->id, 'section' => $section['key'], 'item' => $item->id]) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">Hapus</button></form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                @if($canManage)
                                    <tr data-guide-edit-form="{{ $section['key'] }}-{{ $item->id }}" class="hidden bg-zinc-50">
                                        <td colspan="{{ $section['guidance'] ? 6 : 4 }}" class="p-4">
                                            <form action="{{ route('client.content-guide.update', ['client' => $client->id, 'section' => $section['key'], 'item' => $item->id]) }}" method="POST" class="grid gap-3 md:grid-cols-2">
                                                @csrf @method('PUT')
                                                <input name="name" value="{{ $item->name }}" required maxlength="255" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none">
                                                <select name="status" required class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none"><option value="1" @selected($item->status == 1)>Ditampilkan</option><option value="2" @selected($item->status == 2)>Disembunyikan</option></select>
                                                @if($section['guidance'])
                                                    <textarea name="fungsi_singkat" maxlength="1000" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none">{{ $item->fungsi_singkat }}</textarea>
                                                    <textarea name="contoh" maxlength="3000" class="rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm outline-none">{{ $item->contoh }}</textarea>
                                                @endif
                                                <div class="flex justify-end gap-2 md:col-span-2"><button type="button" data-guide-edit-cancel="{{ $section['key'] }}-{{ $item->id }}" class="rounded-xl border border-zinc-300 px-4 py-2 text-xs font-semibold">Batal</button><button class="rounded-xl bg-zinc-950 px-4 py-2 text-xs font-semibold text-white">Simpan Perubahan</button></div>
                                            </form>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="{{ $section['guidance'] ? ($canManage ? 6 : 5) : ($canManage ? 4 : 3) }}" class="px-5 py-14 text-center text-sm text-zinc-500">Belum ada data {{ $section['label'] }}.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('[data-guide-tab]'));
    tabs.forEach(tab => tab.addEventListener('click', function () {
        const active = tab.dataset.guideTab;
        document.querySelectorAll('[data-guide-panel]').forEach(panel => panel.classList.toggle('hidden', panel.dataset.guidePanel !== active));
        tabs.forEach(item => {
            const selected = item.dataset.guideTab === active;
            item.classList.toggle('bg-zinc-950', selected);
            item.classList.toggle('text-white', selected);
            item.classList.toggle('text-zinc-500', !selected);
        });
        const url = new URL(window.location.href);
        url.searchParams.set('guide', active);
        history.replaceState({}, '', url);
    }));
    document.querySelectorAll('[data-guide-create]').forEach(button => button.addEventListener('click', () => document.querySelector(`[data-guide-create-form="${button.dataset.guideCreate}"]`)?.classList.remove('hidden')));
    document.querySelectorAll('[data-guide-create-cancel]').forEach(button => button.addEventListener('click', () => document.querySelector(`[data-guide-create-form="${button.dataset.guideCreateCancel}"]`)?.classList.add('hidden')));
    document.querySelectorAll('[data-guide-edit]').forEach(button => button.addEventListener('click', () => document.querySelector(`[data-guide-edit-form="${button.dataset.guideEdit}"]`)?.classList.remove('hidden')));
    document.querySelectorAll('[data-guide-edit-cancel]').forEach(button => button.addEventListener('click', () => document.querySelector(`[data-guide-edit-form="${button.dataset.guideEditCancel}"]`)?.classList.add('hidden')));
    const requestedTab = new URLSearchParams(window.location.search).get('guide');
    tabs.find(tab => tab.dataset.guideTab === requestedTab)?.click();
});
</script>
@endsection
