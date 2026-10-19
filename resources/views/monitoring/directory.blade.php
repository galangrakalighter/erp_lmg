@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-5">
        <header class="flex flex-wrap items-end justify-between gap-5 rounded-[28px] bg-zinc-950 px-6 py-8 text-white shadow-xl shadow-zinc-950/10 sm:px-9">
            <div><p class="text-[11px] font-bold uppercase tracking-[.24em] text-lime-300">Lighter Media Group · Monitoring</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight">Live Monitoring</h1>
                <p class="mt-2 max-w-2xl text-sm text-zinc-400">Buka pengguna untuk melihat layar dan kamera yang sedang dibagikan secara langsung.</p></div>
            <div class="flex flex-wrap gap-2">
                @if($canShareOwnScreen)<a href="{{ route('monitoring.index', ['user' => $currentUser->id]) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-zinc-950 transition hover:bg-zinc-200">Bagikan layar saya</a>@endif
                @if($canManage)<a href="{{ route('monitoring.access.index') }}" class="rounded-xl border border-white/25 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/10">Kelola Akses</a>@endif
            </div>
        </header>

        @if($canShareOwnScreen)<div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900"><b>Saat membagikan layar:</b> biarkan tab ini tetap terbuka selama monitoring berlangsung. Menutup atau me-refresh tab akan menghentikan share layar dan kamera.</div>@endif

        <div><p class="text-xs font-bold uppercase tracking-[.18em] text-zinc-400">Direktori pengguna</p><h2 class="mt-1 text-xl font-semibold text-zinc-950">Pilih pengguna untuk membuka monitoring</h2><p class="mt-1 text-sm text-zinc-500">Hanya pengguna yang telah diberi akses oleh administrator yang ditampilkan.</p></div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach($users as $user)
                <a href="{{ route('monitoring.index', ['user' => $user->id]) }}" class="group flex min-h-56 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-xl hover:shadow-zinc-950/5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="grid h-12 w-12 place-items-center overflow-hidden rounded-2xl bg-zinc-950 text-sm font-bold uppercase text-white">@if($user->photo_profile)<img src="{{ asset('storage/'.$user->photo_profile) }}" alt="" class="h-full w-full object-cover">@else{{ Illuminate\Support\Str::substr($user->name ?: $user->email, 0, 2) }}@endif</div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->active_monitoring_count ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">{{ $user->active_monitoring_count ? 'Live' : 'Offline' }}</span>
                    </div>
                    <div class="mt-5 min-w-0"><h3 class="truncate text-base font-semibold text-zinc-950">{{ $user->name ?: 'Tanpa nama' }}</h3><p class="mt-1 truncate text-sm text-zinc-500">{{ $user->email }}</p></div>
                    <div class="mt-4 flex flex-wrap gap-1.5">@forelse($user->userDivisions as $assignment)<span class="rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[10px] font-bold text-zinc-600">{{ $assignment->division->name ?? '-' }} · {{ $assignment->role->role_name ?? '-' }}</span>@empty<span class="text-xs text-zinc-400">Belum memiliki divisi</span>@endforelse</div>
                    <div class="mt-auto flex items-center justify-between border-t border-zinc-100 pt-4 text-xs font-bold text-zinc-400"><span>Buka monitoring</span><svg class="h-4 w-4 transition group-hover:translate-x-1 group-hover:text-zinc-950" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></div>
                </a>
            @endforeach
        </section>
    </div>
</main>
@endsection
