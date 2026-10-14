@extends('layout.app')

@section('content')
<div class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-400">Pengaturan Akun</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-zinc-950 sm:text-3xl">Hubungkan Codex</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">Buat token personal agar Codex Anda dapat membaca analitik marketing sesuai akses ERP Anda.</p>
        </div>
        <a href="{{ route('profile.show') }}" class="text-xs font-semibold text-zinc-500 hover:text-zinc-950">Kembali ke profil</a>
    </div>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Koneksi belum dapat dibuat.</p>
            <ul class="mt-1 list-disc space-y-1 pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if($newToken)
        <section class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">Simpan sekarang</p>
            <h2 class="mt-2 text-lg font-semibold text-zinc-950">Token untuk {{ $deviceName }}</h2>
            <p class="mt-1 text-sm text-zinc-600">Token lengkap hanya ditampilkan satu kali. Jangan kirimkan token ini melalui chat.</p>
            <div class="mt-4 flex gap-2">
                <input id="newCodexToken" type="password" readonly value="{{ $newToken }}" class="h-11 min-w-0 flex-1 rounded-lg border border-amber-300 bg-white px-3 font-mono text-xs text-zinc-800">
                <button type="button" data-reveal-token class="rounded-lg border border-amber-300 bg-white px-4 text-xs font-semibold text-zinc-700 hover:bg-amber-100">Lihat</button>
                <button type="button" data-copy="#newCodexToken" class="rounded-lg bg-zinc-950 px-4 text-xs font-semibold text-white hover:bg-zinc-800">Salin</button>
            </div>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_0.9fr]">
        <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-base font-semibold text-zinc-950">Buat koneksi baru</h2>
            <p class="mt-1 text-xs leading-5 text-zinc-500">Maksimal {{ $maxActiveTokens }} koneksi aktif. Setiap token berlaku 90 hari dan hanya memiliki izin membaca data marketing.</p>

            <form action="{{ route('codex-connection.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-xs font-semibold text-zinc-700">Nama perangkat</span>
                    <input name="device_name" value="{{ old('device_name') }}" required maxlength="60" placeholder="Contoh: Laptop Marketing" class="h-11 w-full rounded-xl border border-zinc-200 px-4 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                </label>
                <label class="block">
                    <span class="mb-2 block text-xs font-semibold text-zinc-700">Password ERP</span>
                    <input name="current_password" type="password" required autocomplete="current-password" class="h-11 w-full rounded-xl border border-zinc-200 px-4 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                    <span class="mt-2 block text-[11px] leading-4 text-zinc-400">Konfirmasi ini memastikan token dibuat oleh pemilik akun.</span>
                </label>
                <button type="submit" class="w-full rounded-lg bg-[#161616] px-5 py-3 text-xs font-semibold text-white transition hover:bg-zinc-800">Generate token Codex</button>
            </form>
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-zinc-950 p-6 text-white shadow-sm sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400">Konfigurasi Codex</p>
            <h2 class="mt-2 text-base font-semibold">Gunakan dua environment variable</h2>
            <p class="mt-2 text-xs leading-5 text-zinc-400">Setelah plugin ERP LMG Marketing terpasang, masukkan URL berikut dan token yang baru dibuat ke Environment Variables Windows, lalu restart Codex Desktop.</p>
            <div class="mt-5 space-y-3">
                <div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">ERP_LMG_API_URL</span>
                    <div class="mt-1 flex gap-2"><input id="codexApiUrl" readonly value="{{ $apiUrl }}" class="h-10 min-w-0 flex-1 rounded-lg border border-white/10 bg-white/5 px-3 font-mono text-xs text-zinc-200"><button type="button" data-copy="#codexApiUrl" class="rounded-lg border border-white/10 px-3 text-xs font-semibold hover:bg-white/10">Salin</button></div>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/5 p-3 text-xs leading-5 text-zinc-400">Nama variabel token: <span class="font-mono text-zinc-200">ERP_LMG_SANCTUM_TOKEN</span></div>
            </div>
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-6 py-5 sm:px-8">
            <h2 class="text-base font-semibold text-zinc-950">Koneksi aktif</h2>
            <p class="mt-1 text-xs text-zinc-500">Cabut koneksi yang sudah tidak digunakan atau perangkat yang hilang.</p>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse($tokens as $token)
                <div class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8">
                    <div>
                        <p class="text-sm font-semibold text-zinc-900">{{ trim(Illuminate\Support\Str::after($token->name, 'codex-erp-lmg:')) }}</p>
                        <p class="mt-1 text-[11px] text-zinc-400">Dibuat {{ $token->created_at?->format('d M Y H:i') }} · Terakhir digunakan {{ $token->last_used_at?->diffForHumans() ?? 'belum pernah' }} · Kedaluwarsa {{ $token->expires_at?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <form action="{{ route('codex-connection.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Cabut koneksi Codex ini?')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg border border-red-200 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">Cabut</button>
                    </form>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-zinc-400">Belum ada koneksi Codex aktif.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('script')
<script>
document.querySelector('[data-reveal-token]')?.addEventListener('click', function () {
    const input = document.getElementById('newCodexToken');
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? 'Lihat' : 'Sembunyikan';
});

document.querySelectorAll('[data-copy]').forEach(button => button.addEventListener('click', async function () {
    const input = document.querySelector(this.dataset.copy);
    if (!input) return;
    await navigator.clipboard.writeText(input.value);
    const original = this.textContent;
    this.textContent = 'Tersalin';
    setTimeout(() => this.textContent = original, 1600);
}));
</script>
@endpush
