@extends('layout.app')

@section('content')
<style>
    .idme-division-tab.is-active,.idme-content-tab.is-active,
    .idme-division-tab.is-active:hover,.idme-content-tab.is-active:hover,
    .idme-division-tab.is-active:focus-visible,.idme-content-tab.is-active:focus-visible{background:#161616!important;color:#fff!important}
    .idme-division-tab:not(.is-active):hover,.idme-content-tab:not(.is-active):hover{background:#f4f4f5!important;color:#18181b!important}
</style>
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1800px] space-y-5">
        <header class="rounded-[28px] bg-[#161616] px-6 py-7 text-white shadow-xl shadow-zinc-950/10 sm:px-8">
            <p class="text-[10px] font-bold uppercase tracking-[.22em] text-zinc-400">IDME · Client Workspace</p>
            <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $client->nama }}</h1>
                    <p class="mt-1 text-sm text-zinc-400">Seluruh pekerjaan client dikelompokkan berdasarkan divisi.</p>
                </div>
                @if($platforms->isNotEmpty())
                    <label id="idmePlatformWrap" class="hidden text-xs font-semibold text-zinc-300">Platform
                        <select id="idmePlatform" class="ml-2 rounded-xl border border-white/15 bg-zinc-900 px-3 py-2 text-sm text-white outline-none">
                            @foreach($platforms as $platform)<option value="{{ $platform }}">{{ $platform }}</option>@endforeach
                        </select>
                    </label>
                @endif
            </div>
        </header>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 bg-zinc-50 px-3 pt-3">
                <p class="px-1 pb-2 text-[10px] font-bold uppercase tracking-[.18em] text-zinc-400">Divisi</p>
                <nav id="idmeDivisionTabs" class="flex gap-1 overflow-x-auto pb-3">
                    @forelse($divisionTabs as $division)
                        <button type="button" data-idme-division="{{ $division['id'] }}"
                            class="idme-division-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition hover:bg-white hover:text-zinc-950">{{ $division['name'] }}</button>
                    @empty
                        <span class="px-3 py-2 text-sm text-zinc-500">Client belum terhubung dengan divisi.</span>
                    @endforelse
                </nav>
            </div>
            <nav id="idmeContentTabs" class="flex gap-1 overflow-x-auto border-b border-zinc-200 bg-white p-2"></nav>
            <div id="idmeWorkspacePanel" class="relative min-h-[70vh] bg-white"></div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const divisions = @json($divisionTabs);
    const divisionButtons = Array.from(document.querySelectorAll('[data-idme-division]'));
    const contentTabs = document.getElementById('idmeContentTabs');
    const panel = document.getElementById('idmeWorkspacePanel');
    const platformSelect = document.getElementById('idmePlatform');
    const platformWrap = document.getElementById('idmePlatformWrap');
    const cache = new Map();
    let activeDivision = null;

    function resolvedUrl(tab) {
        if (!tab.url) return null;
        const url = new URL(tab.url, window.location.origin);
        if (tab.platform && platformSelect) {
            const parts = url.pathname.split('/');
            parts[parts.length - 1] = encodeURIComponent(platformSelect.value);
            url.pathname = parts.join('/');
        }
        url.searchParams.set('embed', '1');
        return url.toString();
    }

    function openContent(tab, button) {
        contentTabs.querySelectorAll('.idme-content-tab').forEach(item => item.classList.remove('is-active'));
        button.classList.add('is-active');
        Array.from(panel.children).forEach(child => child.classList.add('hidden'));
        const url = resolvedUrl(tab);
        if (!url) {
            const message = tab.message || 'Platform client belum tersedia.';
            panel.innerHTML = `<div class="grid min-h-[70vh] place-items-center px-6 text-center text-sm text-zinc-500">${message}</div>`;
            return;
        }
        const key = `${activeDivision.id}:${tab.key}:${tab.platform ? platformSelect?.value : 'global'}`;
        let content = cache.get(key);
        if (!content) {
            content = document.createElement('div');
            content.className = 'h-[75vh] min-h-[620px]';
            content.innerHTML = `<iframe src="${url}" title="${tab.label}" class="h-full w-full border-0 bg-white" loading="eager" scrolling="yes"></iframe>`;
            panel.appendChild(content);
            cache.set(key, content);
        }
        content.classList.remove('hidden');
    }

    function openDivision(division) {
        activeDivision = division;
        divisionButtons.forEach(button => button.classList.toggle('is-active', Number(button.dataset.idmeDivision) === Number(division.id)));
        platformWrap?.classList.toggle('hidden', !division.is_digital_marketing);
        contentTabs.innerHTML = '';
        panel.innerHTML = '';
        cache.clear();
        division.tabs.forEach((tab, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'idme-content-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950';
            button.textContent = tab.label;
            button.addEventListener('click', () => openContent(tab, button));
            contentTabs.appendChild(button);
            if (index === 0) openContent(tab, button);
        });
    }

    divisionButtons.forEach(button => button.addEventListener('click', () => openDivision(divisions.find(item => Number(item.id) === Number(button.dataset.idmeDivision)))));
    platformSelect?.addEventListener('change', () => { if (activeDivision) openDivision(activeDivision); });
    if (divisions.length) openDivision(divisions[0]);
});
</script>
@endsection
