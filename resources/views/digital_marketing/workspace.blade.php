@extends('layout.app')

@section('content')
@php
    $platformMirroringEnabled = ($workspaceApiNamespace ?? 'api.digital-marketing') === 'api.digital-marketing';
@endphp
<style>
    .workspace-tab.is-active,
    .workspace-tab.is-active:hover,
    .workspace-tab.is-active:focus,
    .workspace-tab.is-active:focus-visible {
        background: #18181b !important;
        color: #ffffff !important;
        opacity: 1 !important;
    }
    .workspace-tab:not(.is-active):hover {
        background: #f4f4f5 !important;
        color: #18181b !important;
    }
    .workspace-group.is-active {
        background: #18181b !important;
        color: #ffffff !important;
    }
    #dmWorkspaceExport.is-exporting {
        cursor: wait;
        opacity: .78;
        pointer-events: none;
    }
    #dmWorkspaceExport.is-exporting svg {
        animation: dm-export-spin .75s linear infinite;
    }
    @keyframes dm-export-spin {
        to { transform: rotate(360deg); }
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
                                    <option value="{{ $platform }}" @selected(strcasecmp($platform, request('platform', 'Instagram')) === 0)>{{ $platform }}</option>
                                @endforeach
                            </select>
                        </label>
                        @if($platformMirroringEnabled && ($workspaceCanManageSettings ?? false))
                        <button type="button" id="dmMirrorButton" class="inline-flex h-[42px] items-center justify-center gap-2 rounded-xl border border-white/20 bg-zinc-900 px-4 text-xs font-semibold text-white transition hover:bg-zinc-800">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h11m0 0-3-3m3 3-3 3M16 17H5m0 0 3 3m-3-3 3-3"/></svg>
                            Mirroring
                        </button>
                        @endif
                    @endif
                    <a id="dmWorkspaceExport" href="{{ route('invoice.download', ['id' => $client->id, 'halaman' => 'Market Research']) }}" class="inline-flex h-[42px] items-center justify-center gap-2 rounded-xl border border-white/15 bg-white px-4 text-xs font-semibold text-zinc-950 transition hover:bg-zinc-100">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m-4-4 4 4 4-4M5 21h14"/></svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </header>

        @if($platformMirroringEnabled)
        <div id="dmMirrorModal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-black/55 p-4" aria-hidden="true">
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-5">
                    <div><h2 class="text-base font-semibold text-zinc-950">Mirroring Platform</h2><p class="mt-1 text-xs leading-5 text-zinc-500">Gabungkan data operasional platform terpilih tanpa menghapus atau memindahkan data aslinya.</p></div>
                    <button type="button" id="dmMirrorClose" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-950">&times;</button>
                </div>
                <form id="dmMirrorForm" class="px-6 py-5">
                    <label class="block text-xs font-semibold text-zinc-700">Bagian yang dimirroring
                        <select id="dmMirrorScope" class="mt-1.5 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900">
                            @foreach(($platformMirrorScopes ?? []) as $scope => $label)
                                <option value="{{ $scope }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <p class="mt-4 text-sm text-zinc-700">Mirror <strong id="dmMirrorSource"></strong> dengan:</p>
                    <div id="dmMirrorTargets" class="mt-4 grid grid-cols-2 gap-3"></div>
                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-800">Saat mirroring dimatikan, data yang sudah ada tetap tersimpan pada platform asalnya.</p>
                    <div id="dmMirrorError" class="mt-3 hidden text-xs text-red-600"></div>
                    <div class="mt-5 flex justify-end gap-2"><button type="button" id="dmMirrorCancel" class="rounded-xl border border-zinc-300 px-4 py-2.5 text-xs font-semibold text-zinc-700">Batal</button><button type="submit" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-semibold text-white">Simpan Mirroring</button></div>
                </form>
            </div>
        </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <nav id="dmWorkspaceTabs" class="border-b border-zinc-200 bg-zinc-50" aria-label="Menu client {{ $workspaceLabel }}">
                @php
                    $firstPlatform = $platforms->first();
                    $apiNamespace = $workspaceApiNamespace ?? 'api.digital-marketing';
                    $availableTabs = $workspaceTabs ?? ['market-research', 'branding', 'analisis', 'content-plan', 'dokumen', 'guide', 'timeline'];
                    $readOnlyTabs = $workspaceReadOnlyTabs ?? [];
                    $tabs = collect([
                        ['key' => 'market-research', 'label' => 'Market Research', 'group' => 'MR', 'url' => $firstPlatform ? route($apiNamespace.'.market-research', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'branding', 'label' => 'Branding', 'group' => 'MR', 'url' => $firstPlatform ? route($apiNamespace.'.branding', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'social-media-research', 'label' => 'Sosial Media Research', 'group' => 'MR', 'url' => $firstPlatform && Route::has($apiNamespace.'.social-media-research') ? route($apiNamespace.'.social-media-research', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'advertising-research', 'label' => 'Advertising Research', 'group' => 'MR', 'url' => $firstPlatform && Route::has($apiNamespace.'.advertising-research') ? route($apiNamespace.'.advertising-research', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'content-plan', 'label' => 'Content Plan', 'group' => 'CRE', 'url' => $firstPlatform ? route($apiNamespace.'.content-plan', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'guide', 'label' => 'Guide', 'group' => 'CRE', 'url' => route('client.content-guide', array_filter(['client' => $client->id, 'readonly' => $apiNamespace === 'api.multimedia' ? 1 : null])), 'platform' => false],
                        ['key' => 'analisis', 'label' => 'Data Performance', 'group' => 'P', 'url' => in_array('analisis', $availableTabs, true) && $firstPlatform ? route($apiNamespace.'.analisis', [$client->id, $firstPlatform]) : null, 'platform' => true],
                        ['key' => 'explore', 'label' => 'E', 'group' => 'E', 'url' => Route::has($apiNamespace.'.explore') ? route($apiNamespace.'.explore', [$client->id]) : null, 'platform' => false],
                        ['key' => 'dokumen', 'label' => 'File Dokumen', 'group' => 'utility', 'url' => route('PDF', ['id_produk' => $client->id, 'division_id' => $workspaceDivisionId]), 'platform' => false],
                        ['key' => 'timeline', 'label' => 'Timeline', 'group' => 'utility', 'url' => route('client.kpi-timeline', ['client' => $client->id]), 'platform' => false],
                    ])->whereIn('key', $availableTabs)->map(function ($tab) use ($readOnlyTabs) {
                        $tab['readonly'] = in_array($tab['key'], $readOnlyTabs, true);
                        return $tab;
                    })->values();
                @endphp

                @if($useMrCrepeNavigation ?? false)
                    <div class="flex gap-2 overflow-x-auto p-2">
                        @foreach(['MR', 'CRE', 'P', 'E'] as $group)
                            @if($tabs->where('group', $group)->isNotEmpty())
                                <button type="button" data-workspace-group="{{ $group }}" class="workspace-group shrink-0 rounded-xl px-5 py-2.5 text-sm font-black tracking-[.12em] text-zinc-500 transition hover:bg-white hover:text-zinc-950">{{ $group }}</button>
                            @endif
                        @endforeach
                        @if($tabs->where('group', 'utility')->isNotEmpty())
                            <div class="mx-1 w-px shrink-0 bg-zinc-200"></div>
                            @foreach($tabs->where('group', 'utility') as $tab)
                                <button type="button" data-workspace-tab="{{ $tab['key'] }}" data-workspace-utility="1" data-base-url="{{ $tab['url'] }}" data-needs-platform="0" data-readonly="{{ $tab['readonly'] ? '1' : '0' }}" class="workspace-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition">{{ $tab['label'] }}</button>
                            @endforeach
                        @endif
                    </div>
                    @foreach(['MR', 'CRE', 'P'] as $group)
                        <div data-workspace-submenu="{{ $group }}" class="hidden flex gap-1 overflow-x-auto border-t border-zinc-200 bg-white p-2 pl-4">
                            @foreach($tabs->where('group', $group) as $tab)
                                <button type="button" data-workspace-tab="{{ $tab['key'] }}" data-workspace-parent="{{ $group }}" data-base-url="{{ $tab['url'] }}" data-needs-platform="{{ $tab['platform'] ? '1' : '0' }}" data-readonly="{{ $tab['readonly'] ? '1' : '0' }}" @disabled(!$tab['url']) class="workspace-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition disabled:cursor-not-allowed disabled:opacity-40">{{ $tab['label'] }}</button>
                            @endforeach
                        </div>
                    @endforeach
                    @php($emptyTab = $tabs->firstWhere('group', 'E'))
                    @if($emptyTab)
                        <button type="button" data-workspace-tab="{{ $emptyTab['key'] }}" data-workspace-parent="E" data-base-url="{{ $emptyTab['url'] }}" data-needs-platform="0" data-readonly="{{ $emptyTab['readonly'] ? '1' : '0' }}" class="hidden"></button>
                    @endif
                @else
                    <div class="flex gap-1 overflow-x-auto p-2">
                        @foreach($tabs as $tab)
                            <button type="button" data-workspace-tab="{{ $tab['key'] }}" data-base-url="{{ $tab['url'] }}" data-needs-platform="{{ $tab['platform'] ? '1' : '0' }}" data-readonly="{{ $tab['readonly'] ? '1' : '0' }}" @disabled(!$tab['url']) class="workspace-tab whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold text-zinc-500 transition disabled:cursor-not-allowed disabled:opacity-40">{{ $tab['label'] }}</button>
                        @endforeach
                    </div>
                @endif
            </nav>

            @if($platforms->isEmpty())
                <div class="border-b border-amber-200 bg-amber-50 px-5 py-3 text-sm text-amber-800">
                    Platform client belum disetel. File Dokumen dan Timeline tetap dapat dibuka.
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
    const groupButtons = Array.from(document.querySelectorAll('[data-workspace-group]'));
    const submenus = Array.from(document.querySelectorAll('[data-workspace-submenu]'));
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
    const availablePlatforms = @json($platforms->values());
    const mirrorPairs = @json($platformMirrorPairs ?? []);
    const mirrorScopes = @json($platformMirrorScopes ?? []);
    const mirrorUrl = @json(route('digital-marketing.platform-mirroring', ['id_produk' => $client->id]));
    let activeKey = null;

    function showGroup(group) {
        groupButtons.forEach(button => button.classList.toggle('is-active', button.dataset.workspaceGroup === group));
        submenus.forEach(menu => menu.classList.toggle('hidden', menu.dataset.workspaceSubmenu !== group));
    }

    const mirrorModal = document.getElementById('dmMirrorModal');
    function closeMirrorModal() {
        mirrorModal?.classList.add('hidden');
        mirrorModal?.classList.remove('flex');
        mirrorModal?.setAttribute('aria-hidden', 'true');
    }
    function openMirrorModal() {
        if (!platformSelect || !mirrorModal) return;
        const source = platformSelect.value;
        document.getElementById('dmMirrorSource').textContent = source;
        renderMirrorTargets();
        mirrorModal.classList.remove('hidden');
        mirrorModal.classList.add('flex');
        mirrorModal.setAttribute('aria-hidden', 'false');
    }
    function renderMirrorTargets() {
        if (!platformSelect) return;
        const source = platformSelect.value;
        const scope = document.getElementById('dmMirrorScope')?.value || Object.keys(mirrorScopes)[0];
        const escapeMirrorText = value => String(value).replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        document.getElementById('dmMirrorTargets').innerHTML = availablePlatforms
            .filter(platform => platform.toLowerCase() !== source.toLowerCase())
            .map(platform => {
                const checked = mirrorPairs.some(pair =>
                    pair.scope === scope &&
                    (pair.platform_a.toLowerCase() === source.toLowerCase() && pair.platform_b.toLowerCase() === platform.toLowerCase()) ||
                    pair.scope === scope &&
                    (pair.platform_b.toLowerCase() === source.toLowerCase() && pair.platform_a.toLowerCase() === platform.toLowerCase())
                );
                return `<label class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 px-4 py-3 text-sm font-medium text-zinc-800 hover:bg-zinc-50"><input type="checkbox" name="targets[]" value="${escapeMirrorText(platform)}" ${checked ? 'checked' : ''} class="h-4 w-4 rounded border-zinc-300">${escapeMirrorText(platform)}</label>`;
            }).join('');
    }
    document.getElementById('dmMirrorButton')?.addEventListener('click', openMirrorModal);
    document.getElementById('dmMirrorClose')?.addEventListener('click', closeMirrorModal);
    document.getElementById('dmMirrorCancel')?.addEventListener('click', closeMirrorModal);
    mirrorModal?.addEventListener('click', event => { if (event.target === mirrorModal) closeMirrorModal(); });
    document.getElementById('dmMirrorScope')?.addEventListener('change', renderMirrorTargets);
    document.getElementById('dmMirrorForm')?.addEventListener('submit', async event => {
        event.preventDefault();
        const error = document.getElementById('dmMirrorError');
        error.classList.add('hidden');
        const response = await fetch(mirrorUrl, {
            method: 'PUT',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ source: platformSelect.value, scope: document.getElementById('dmMirrorScope').value, targets: Array.from(event.currentTarget.querySelectorAll('[name="targets[]"]:checked')).map(input => input.value) })
        });
        if (!response.ok) {
            const payload = await response.json().catch(() => ({}));
            error.textContent = payload.message || 'Mirroring gagal disimpan.';
            error.classList.remove('hidden');
            return;
        }
        window.location.reload();
    });

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
        if (button.dataset.readonly === '1') parsed.searchParams.set('readonly', '1');
        return parsed.toString();
    }

    function openTab(button, updateHistory = true) {
        const url = urlFor(button);
        if (!url) return;

        const platform = button.dataset.needsPlatform === '1' ? (platformSelect?.value || '') : 'global';
        const cacheKey = `${button.dataset.workspaceTab}:${platform}`;

        tabs.forEach(tab => {
            tab.classList.remove('is-active', 'bg-zinc-950', 'text-white');
            tab.setAttribute('aria-selected', 'false');
        });
        button.classList.add('is-active', 'bg-zinc-950', 'text-white');
        button.setAttribute('aria-selected', 'true');
        if (button.dataset.workspaceParent) {
            showGroup(button.dataset.workspaceParent);
        } else if (button.dataset.workspaceUtility === '1') {
            showGroup(null);
        }
        panelCache.forEach(panel => panel.classList.add('hidden'));
        empty.classList.add('hidden');

        const panel = ensurePanel(button);
        if (!panel) return;

        panel.classList.remove('hidden');
        activeKey = button.dataset.workspaceTab;

        if (exportButton) {
            const exportUrl = exportUrls[activeKey];
            exportButton.classList.toggle('hidden', !exportUrl);
            if (exportUrl) {
                const parsedExportUrl = new URL(exportUrl, window.location.origin);
                if (platformSelect) {
                    parsedExportUrl.searchParams.set('platform', platformSelect.value);
                }
                exportButton.href = parsedExportUrl.toString();
            }
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
    groupButtons.forEach(button => button.addEventListener('click', function () {
        const group = button.dataset.workspaceGroup;
        showGroup(group);
        const firstTab = tabs.find(tab => tab.dataset.workspaceParent === group && !tab.disabled);
        if (firstTab) openTab(firstTab);
    }));
    platformSelect?.addEventListener('change', function () {
        const active = tabs.find(tab => tab.dataset.workspaceTab === activeKey && !tab.disabled)
            || tabs.find(tab => !tab.disabled);
        if (active) openTab(active);
    });

    exportButton?.addEventListener('click', async function (event) {
        event.preventDefault();
        if (exportButton.classList.contains('is-exporting')) return;

        const exportUrl = new URL(exportButton.href);

        if (activeKey === 'content-plan') {
            const activePanel = Array.from(panels.children)
                .find(panel => !panel.classList.contains('hidden'));
            const iframe = activePanel?.querySelector('iframe');

            try {
                const contentUrl = new URL(iframe.contentWindow.location.href);
                ['start_date', 'end_date', 'content_source'].forEach(key => {
                    const value = contentUrl.searchParams.get(key);
                    if (value) exportUrl.searchParams.set(key, value);
                    else exportUrl.searchParams.delete(key);
                });
            } catch (error) {
                // Gunakan filter default jika isi iframe belum dapat dibaca.
            }
        }

        const originalContent = exportButton.innerHTML;
        exportButton.classList.add('is-exporting');
        exportButton.setAttribute('aria-disabled', 'true');
        exportButton.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" stroke-opacity=".25" stroke-width="2"></circle>
                <path d="M21 12a9 9 0 0 0-9-9" stroke-width="2" stroke-linecap="round"></path>
            </svg>
            Menyiapkan PDF...
        `;

        try {
            const response = await fetch(exportUrl.toString(), {
                headers: { 'Accept': 'application/pdf' },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('PDF tidak dapat dibuat.');

            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const encodedName = disposition.match(/filename\*=UTF-8''([^;]+)/i)?.[1];
            const regularName = disposition.match(/filename="?([^";]+)"?/i)?.[1];
            const fileName = encodedName
                ? decodeURIComponent(encodedName)
                : (regularName || 'content-plan.pdf');
            const objectUrl = URL.createObjectURL(blob);
            const downloadLink = document.createElement('a');
            downloadLink.href = objectUrl;
            downloadLink.download = fileName;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
            setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
        } catch (error) {
            window.alert(error.message || 'PDF tidak dapat dibuat. Silakan coba kembali.');
        } finally {
            exportButton.innerHTML = originalContent;
            exportButton.classList.remove('is-exporting');
            exportButton.removeAttribute('aria-disabled');
            window.stopEnhancedLoading?.();
        }
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
