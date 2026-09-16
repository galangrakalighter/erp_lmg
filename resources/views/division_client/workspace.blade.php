@extends('layout.app')

@section('content')
<style>.division-workspace-tab.is-active{background:#161616!important;color:#fff!important}</style>
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1800px] space-y-5">
        <header class="rounded-[28px] bg-zinc-950 px-6 py-7 text-white shadow-xl shadow-zinc-950/10 sm:px-8">
            <p class="text-[10px] font-bold uppercase tracking-[.22em] text-zinc-400">{{ $divisionLabel }} · Client Workspace</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $client->nama }}</h1>
            <p class="mt-1 text-sm text-zinc-400">Kelola area {{ $divisionLabel }} client melalui satu halaman.</p>
        </header>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <nav class="flex gap-1 overflow-x-auto border-b border-zinc-200 bg-zinc-50 p-2">
                @foreach($tabs as $tab)
                    <button type="button" data-division-tab="{{ $tab['key'] }}"
                        data-url="{{ $tab['url'] ?? '' }}" data-message="{{ $tab['message'] ?? '' }}"
                        class="division-workspace-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition hover:bg-white hover:text-zinc-950">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
            <div id="divisionWorkspacePanel" class="relative min-h-[70vh] bg-white"></div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = Array.from(document.querySelectorAll('[data-division-tab]'));
    const panel = document.getElementById('divisionWorkspacePanel');
    const cache = new Map();

    function openTab(button) {
        buttons.forEach(item => item.classList.remove('is-active'));
        button.classList.add('is-active');
        Array.from(panel.children).forEach(child => child.classList.add('hidden'));

        let content = cache.get(button.dataset.divisionTab);
        if (!content) {
            content = document.createElement('div');
            content.className = 'h-[75vh] min-h-[620px]';
            if (button.dataset.url) {
                const url = new URL(button.dataset.url, window.location.origin);
                url.searchParams.set('embed', '1');
                content.innerHTML = `<iframe src="${url}" title="${button.textContent.trim()}" class="h-full w-full border-0 bg-white" loading="eager" scrolling="yes"></iframe>`;
            } else {
                content.innerHTML = `<div class="grid h-full place-items-center px-6 text-center"><div><div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-zinc-100 text-zinc-500"><i class="fas fa-layer-group"></i></div><p class="mt-4 text-sm font-semibold text-zinc-800">${button.textContent.trim()}</p><p class="mt-1 text-sm text-zinc-500">${button.dataset.message}</p></div></div>`;
            }
            panel.appendChild(content);
            cache.set(button.dataset.divisionTab, content);
        }
        content.classList.remove('hidden');
        const pageUrl = new URL(window.location.href);
        pageUrl.searchParams.set('tab', button.dataset.divisionTab);
        history.replaceState({}, '', pageUrl);
    }

    const requested = new URLSearchParams(window.location.search).get('tab');
    openTab(buttons.find(button => button.dataset.divisionTab === requested) || buttons[0]);
});
</script>
@endsection
