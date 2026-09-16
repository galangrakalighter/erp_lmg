@php
    $isIdmeWorkspace = Route::is('idme.workspace');
    // IDME adalah workspace lintas divisi khusus admin. Jabatan admin boleh
    // terisi maupun null; satu-satunya syarat akses adalah isAdmin = true.
    $isIdmeUser = (bool) ($auth?->isAdmin ?? false);
    $idmeOpen = $isIdmeWorkspace;
@endphp

@if($isIdmeUser)
<div class="px-1">
    <button type="button" data-sidebar-collapse="collapseIDME"
        class="group flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-xs font-semibold transition {{ $idmeOpen ? 'bg-white text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}"
        aria-expanded="{{ $idmeOpen ? 'true' : 'false' }}">
        <span class="flex items-center gap-2.5">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg>
            <span>IDME</span>
        </span>
        <svg class="sidebar-chevron h-3.5 w-3.5 transition-transform {{ $idmeOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6"/></svg>
    </button>

    <div id="collapseIDME" class="{{ $idmeOpen ? '' : 'hidden' }} ml-2 mt-1 border-l border-zinc-700 pl-2">
        <button type="button" data-sidebar-collapse="collapseIDMEClients"
            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-[10px] font-bold uppercase tracking-[.12em] text-zinc-400 transition hover:bg-zinc-800 hover:text-white"
            aria-expanded="{{ $idmeOpen ? 'true' : 'false' }}">
            <span>Client</span>
            <svg class="sidebar-chevron h-3.5 w-3.5 transition-transform {{ $idmeOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 9l6 6 6-6"/></svg>
        </button>
        <div id="collapseIDMEClients" class="{{ $idmeOpen ? '' : 'hidden' }} mt-1 space-y-1 border-l border-zinc-800 pl-2">
            @forelse($idmeClients as $client)
                <a href="{{ route('idme.workspace', $client->id) }}"
                    class="block truncate rounded-lg px-2.5 py-2 text-xs transition {{ $isIdmeWorkspace && (int) $currentProduct === (int) $client->id ? 'bg-white font-semibold text-zinc-950' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }}">
                    {{ $client->nama }}
                </a>
            @empty
                <p class="px-2 py-2 text-[10px] italic text-zinc-600">Belum ada client.</p>
            @endforelse
        </div>
    </div>
</div>
@endif
