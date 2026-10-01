@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1800px] space-y-5">
        <header class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 shadow-sm sm:px-6">
            <p class="text-[10px] font-bold uppercase tracking-[.2em] text-zinc-400">Content Guide</p>
            <h1 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950">Panduan Konten {{ $client->nama }}</h1>
            <p class="mt-1 text-xs text-zinc-500">Referensi Pilar, Hook, Body, dan CTA untuk membantu penyusunan content plan.</p>
        </header>

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
                <div class="border-b border-zinc-200 px-5 py-4 sm:px-6">
                    <h2 class="text-sm font-semibold text-zinc-950">Panduan {{ $section['label'] }}</h2>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Nama, fungsi singkat, dan contoh penggunaan.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] border-collapse text-left">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="w-12 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">No</th>
                                <th class="w-1/5 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Nama</th>
                                <th class="w-2/5 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Fungsi Singkat</th>
                                <th class="w-2/5 px-5 py-3 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400">Contoh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($section['items'] as $item)
                                <tr class="align-top transition hover:bg-zinc-50/70">
                                    <td class="px-5 py-4 text-xs font-semibold text-zinc-400">{{ $loop->iteration }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-zinc-900">{{ $item->name }}</td>
                                    <td class="px-5 py-4 text-sm leading-6 text-zinc-600">{{ $item->fungsi_singkat ?: '-' }}</td>
                                    <td class="px-5 py-4 text-sm italic leading-6 text-zinc-600">{{ $item->contoh ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-14 text-center text-sm text-zinc-500">Belum ada data {{ $section['label'] }} aktif.</td></tr>
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
    }));
});
</script>
@endsection
