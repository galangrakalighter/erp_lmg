<nav class="mx-auto mb-5 flex w-full max-w-[1600px] gap-1 rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-sm" aria-label="Jenis dashboard">
    <a href="{{ route('overview.dashboard_kpi') }}"
        class="flex-1 rounded-xl px-4 py-2.5 text-center text-xs font-bold transition {{ Route::is('overview.dashboard_kpi') ? 'bg-zinc-950 text-white' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950' }}">
        Dashboard KPI
    </a>
    <a href="{{ route('overview.dashboard') }}"
        class="flex-1 rounded-xl px-4 py-2.5 text-center text-xs font-bold transition {{ Route::is('overview.dashboard') ? 'bg-zinc-950 text-white' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950' }}">
        Dashboard Hari Ini
    </a>
</nav>
