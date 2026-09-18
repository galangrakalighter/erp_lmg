@extends('layout.app')

@section('content')
<style>
    .workspace-tab.bg-zinc-950 {
        color: #ffffff !important;
    }
</style>
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1800px] space-y-5">
        <header class="rounded-[28px] bg-zinc-950 px-6 py-7 text-white shadow-xl shadow-zinc-950/10 sm:px-8">
            <p class="text-[10px] font-bold uppercase tracking-[.22em] text-zinc-400">{{ $workspaceLabel }} · Client Workspace</p>
            <div class="mt-2 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ $client->nama }}</h1>
                    <p class="mt-1 text-sm text-zinc-400">{{ $workspaceDescription }}</p>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-end lg:w-auto">
                    @if($platforms->isNotEmpty())
                        <label class="w-full sm:w-64">
                            <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-400">Platform</span>
                            <select id="dmWorkspacePlatform" class="w-full rounded-xl border border-white/15 bg-white px-3 py-2.5 text-sm font-semibold text-zinc-950 outline-none">
                                @foreach($platforms as $platform)
                                    <option value="{{ $platform }}">{{ $platform }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <a id="dmWorkspaceExport" href="{{ route('invoice.download', ['id' => $client->id, 'halaman' => 'Market Research']) }}" class="inline-flex h-[42px] items-center justify-center gap-2 rounded-xl border border-white/15 bg-white px-4 text-xs font-semibold text-zinc-950 transition hover:bg-zinc-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m-4-4 4 4 4-4M5 21h14"/></svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </header>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <nav id="dmWorkspaceTabs" class="flex gap-1 overflow-x-auto border-b border-zinc-200 bg-zinc-50 p-2" aria-label="Menu client {{ $workspaceLabel }}">
                @php
                    $firstPlatform = $platforms->first();
                    $apiNamespace = $workspaceApiNamespace ?? 'api.digital-marketing';
                    $tabs = [
                        ['key' => 'market-research', 'label' => 'Market Research', 'url' => $firstPlatform ? route($apiNamespace.'.market-research', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'branding', 'label' => 'Branding', 'url' => $firstPlatform ? route($apiNamespace.'.branding', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'analisis', 'label' => 'Analisis', 'url' => $firstPlatform ? route($apiNamespace.'.analisis', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'content-plan', 'label' => 'Content Plan', 'url' => $firstPlatform ? route($apiNamespace.'.content-plan', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'dokumen', 'label' => 'File Dokumen', 'url' => route('PDF', ['id_produk' => $client->id, 'division_id' => $workspaceDivisionId]), 'platform' => false],
                    ];
                @endphp

                @foreach($tabs as $tab)
                    <button type="button"
                        data-workspace-tab="{{ $tab['key'] }}"
                        data-base-url="{{ $tab['url'] }}"
                        data-needs-platform="{{ $tab['platform'] ? '1' : '0' }}"
                        @disabled(!$tab['url'])
                        class="workspace-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition hover:bg-white hover:text-zinc-950 disabled:cursor-not-allowed disabled:opacity-40">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>

            @if($platforms->isEmpty())
                <div class="border-b border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800">
                    Platform client belum disetel. File Dokumen tetap dapat dibuka.
                </div>
            @endif

            <div id="dmWorkspacePanels" class="relative min-h-[70vh] bg-[#f5f5f3]">
                <div id="dmWorkspaceEmpty" class="grid min-h-[70vh] place-items-center px-6 text-center text-sm text-zinc-500">
                    Pilih tab untuk membuka workspace client.
                </div>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('[data-workspace-tab]'));
    const platformSelect = document.getElementById('dmWorkspacePlatform');
    const panels = document.getElementById('dmWorkspacePanels');
    const empty = document.getElementById('dmWorkspaceEmpty');
    const exportButton = document.getElementById('dmWorkspaceExport');
    const exportUrls = {
        'market-research': @json(route('invoice.download', ['id' => $client->id, 'halaman' => 'Market Research'])),
        'branding': @json(route('invoice.download', ['id' => $client->id, 'halaman' => 'Branding'])),
        'content-plan': @json(route('invoice.download', ['id' => $client->id, 'halaman' => 'Manajemen Konten']))
    };
    const panelCache = new Map();
    let activeKey = null;

    function urlFor(button) {
        const base = button.dataset.baseUrl;
        if (!base) return null;

        let url = base;
        if (button.dataset.needsPlatform === '1' && platformSelect) {
            const parsed = new URL(base, window.location.origin);
            const parts = parsed.pathname.split('/');
            parts[parts.length - 1] = encodeURIComponent(platformSelect.value);
            parsed.pathname = parts.join('/');
            url = parsed.toString();
        }

        const parsed = new URL(url, window.location.origin);
        parsed.searchParams.set('embed', '1');
        return parsed.toString();
    }

    function openTab(button, updateHistory = true) {
        const url = urlFor(button);
        if (!url) return;

        const platform = button.dataset.needsPlatform === '1' ? (platformSelect?.value || '') : 'global';
        const cacheKey = `${button.dataset.workspaceTab}:${platform}`;

        tabs.forEach(tab => tab.classList.remove('bg-zinc-950', 'text-white'));
        button.classList.add('bg-zinc-950', 'text-white');
        panelCache.forEach(panel => panel.classList.add('hidden'));
        empty.classList.add('hidden');

        const panel = ensurePanel(button);
        if (!panel) return;

        panel.classList.remove('hidden');
        activeKey = button.dataset.workspaceTab;

        if (exportButton) {
            const exportUrl = exportUrls[activeKey];
            exportButton.classList.toggle('hidden', !exportUrl);
            if (exportUrl) exportButton.href = exportUrl;
        }

        if (updateHistory) {
            const pageUrl = new URL(window.location.href);
            pageUrl.searchParams.set('tab', activeKey);
            if (platformSelect) pageUrl.searchParams.set('platform', platformSelect.value);
            history.replaceState({}, '', pageUrl);
        }
    }

    function ensurePanel(button) {
        const url = urlFor(button);
        if (!url) return null;

        const platform = button.dataset.needsPlatform === '1' ? (platformSelect?.value || '') : 'global';
        const cacheKey = `${button.dataset.workspaceTab}:${platform}`;
        let panel = panelCache.get(cacheKey);

        if (panel) return panel;

        panel = document.createElement('div');
        panel.className = 'hidden h-[75vh] min-h-[620px] bg-white';
        panel.innerHTML = `
            <div class="grid h-full place-items-center bg-white text-sm text-zinc-500" data-panel-loading>
                <span>Memuat ${button.textContent.trim()}...</span>
            </div>
            <iframe src="${url}" title="${button.textContent.trim()}" class="hidden h-full w-full border-0 bg-white" loading="eager" scrolling="yes"></iframe>
        `;

        const iframe = panel.querySelector('iframe');
        const loading = panel.querySelector('[data-panel-loading]');
        iframe.addEventListener('load', function () {
            try {
                iframe.contentDocument?.body.classList.add('lmg-embedded');
            } catch (error) {
                // Endpoint workspace tetap same-origin; abaikan bila browser membatasi akses.
            }

            loading?.remove();
            iframe.classList.remove('hidden');
        });

        panels.appendChild(panel);
        panelCache.set(cacheKey, panel);
        return panel;
    }

    tabs.forEach(button => button.addEventListener('click', () => openTab(button)));
    platformSelect?.addEventListener('change', function () {
        const active = tabs.find(tab => tab.dataset.workspaceTab === activeKey && !tab.disabled)
            || tabs.find(tab => !tab.disabled);
        if (active) openTab(active);
    });

    const params = new URLSearchParams(window.location.search);
    if (platformSelect && params.get('platform') && Array.from(platformSelect.options).some(option => option.value === params.get('platform'))) {
        platformSelect.value = params.get('platform');
    }
    const initial = tabs.find(tab => tab.dataset.workspaceTab === params.get('tab') && !tab.disabled)
        || tabs.find(tab => !tab.disabled);
    if (initial) openTab(initial, false);
});
</script>
@endsection
