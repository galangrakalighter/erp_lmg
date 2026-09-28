@php
    $isMultimediaWorkspace = request()->routeIs('multimedia.workspace');
    $isMultimediaCMS = request()->routeIs('client.produk_divisi')
        && (int) request()->route('division') === (int) $multimediaDivisionId;
    $multimediaOpen = $isMultimediaWorkspace || $isMultimediaCMS;
    $canManageMultimedia = $isSuperAdmin
        || (($myDivision->name ?? '') === 'Multimedia' && ($myRole->level ?? 99) < 2);
@endphp

<div class="px-1">
    <button type="button" data-sidebar-collapse="collapseMultimedia" data-sidebar-group="division"
        class="group flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-xs font-semibold transition {{ $multimediaOpen ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}"
        aria-expanded="{{ $multimediaOpen ? 'true' : 'false' }}">
        <span class="flex min-w-0 items-center gap-2.5">
            <svg class="h-4 w-4 shrink-0 {{ $multimediaOpen ? 'text-zinc-950' : 'text-zinc-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="5" width="18" height="14" rx="2" stroke-width="1.8" />
                <path d="m9 9 6 3-6 3V9Z" stroke-linejoin="round" stroke-width="1.8" />
            </svg>
            <span class="truncate">Multimedia</span>
        </span>
        <svg class="sidebar-chevron h-3.5 w-3.5 transition-transform {{ $multimediaOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6" />
        </svg>
    </button>

    <div id="collapseMultimedia" class="{{ $multimediaOpen ? '' : 'hidden' }}">
        <div class="mt-1 ml-2 space-y-1 border-l border-zinc-800 pl-3">
        @if($canManageMultimedia)
            <a href="{{ route('client.produk_divisi', $multimediaDivisionId) }}"
                class="group flex items-center gap-2.5 rounded-md px-3 py-2 text-sm transition-colors duration-200 {{ $isMultimediaCMS ? 'bg-white font-semibold text-black' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="h-4 w-4 shrink-0 {{ $isMultimediaCMS ? 'text-black' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066 1.724 1.724 0 012.973 1.675 1.724 1.724 0 001.066 2.573 1.724 1.724 0 010 3.35 1.724 1.724 0 00-1.066 2.573 1.724 1.724 0 01-2.973 1.675 1.724 1.724 0 00-2.573 1.066 1.724 1.724 0 01-3.35 0 1.724 1.724 0 00-2.573-1.066 1.724 1.724 0 01-2.973-1.675 1.724 1.724 0 00-1.066-2.573 1.724 1.724 0 010-3.35 1.724 1.724 0 001.066-2.573 1.724 1.724 0 01-2.973-1.675 1.724 1.724 0 00-2.573-1.066z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Dashboard CMS</span>
            </a>
        @endif

        <button type="button" data-sidebar-collapse="cat_multimedia"
            class="group flex w-full items-center justify-between rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 {{ $isMultimediaWorkspace ? 'bg-white/5 text-white' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}"
            aria-expanded="{{ $isMultimediaWorkspace ? 'true' : 'false' }}">
            <span class="flex items-center">
                <svg class="mr-2.5 h-4 w-4 shrink-0 {{ $isMultimediaWorkspace ? 'text-white' : 'text-zinc-500 group-hover:text-zinc-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
                <span>Client</span>
            </span>
            <svg class="sidebar-chevron h-3.5 w-3.5 shrink-0 text-zinc-600 transition-transform duration-200 {{ $isMultimediaWorkspace ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
            </svg>
        </button>

        <div id="cat_multimedia" class="{{ $isMultimediaWorkspace ? '' : 'hidden' }}">
        <div class="ml-2 space-y-1 border-l border-zinc-800 pl-2">
        @foreach($multimediaProduk as $client)
            @php
                $hasClientAccess = in_array($client->client_id, $myClientIds);
                $canOpen = $isSuperAdmin || (($myDivision->name ?? '') === 'Multimedia' && $hasClientAccess);
                $isActiveClient = $multimediaOpen && (int) request()->route('id_produk') === (int) $client->client_id;
            @endphp

            @if($canOpen)
                <a href="{{ route('multimedia.workspace', ['id_produk' => $client->client_id]) }}"
                    class="group mb-1 flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-xs transition {{ $isActiveClient ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 19a4 4 0 00-8 0M11 11a3 3 0 100-6 3 3 0 000 6z" />
                    </svg>
                    <span class="truncate">{{ $client->nama }}</span>
                </a>
            @elseif(($myDivision->name ?? '') === 'Multimedia')
                <div class="mb-1 flex cursor-not-allowed items-center gap-2 rounded-lg px-2.5 py-2 text-xs text-zinc-700" title="Anda tidak memiliki akses ke client ini">
                    <span class="truncate">{{ $client->nama }}</span>
                </div>
            @endif
        @endforeach
        </div>
        </div>
        </div>
    </div>
</div>
