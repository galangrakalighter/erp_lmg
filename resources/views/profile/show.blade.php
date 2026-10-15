@extends('layout.app')

@section('content')
@php
    $placement = $user->userDivisions->first();
    $organization = $user->isAdmin
        ? 'Lighter Media Group'
        : ($placement?->division?->name ?? 'Belum ada divisi');
    $position = $user->isAdmin
        ? strtoupper($user->jabatan ?: 'Admin')
        : ($placement?->role?->role_name ?? 'Belum ada jabatan');
    $canChangePassword = (bool) $user->isAdmin && blank($user->jabatan);
@endphp

<div class="mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-7">
        <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-400">Pengaturan Akun</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-[-0.035em] text-zinc-950 sm:text-3xl">Profil Saya</h1>
        <p class="mt-2 text-sm text-zinc-500">Perbarui foto profil dan nomor WhatsApp yang dapat dihubungi.</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Data belum dapat disimpan.</p>
            <ul class="mt-1 list-disc space-y-1 pl-5 text-xs">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 bg-[#161616] px-6 py-6 text-white sm:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/15 bg-white/10 text-2xl font-semibold">
                    @if($user->photo_profile)
                        <img src="{{ asset('storage/' . $user->photo_profile) }}" alt="Foto {{ $user->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($user->name ?: $user->email, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-xl font-semibold">{{ $user->name }}</h2>
                    <p class="mt-1 truncate text-sm text-zinc-300">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-200">{{ $organization }}</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-zinc-200">{{ str_replace('_', ' ', $position) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid gap-8 px-6 py-7 sm:px-8 lg:grid-cols-[240px_1fr]">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-950">Foto Profil</h3>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">Gunakan JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                </div>

                <div>
                    <label for="photo_profile" class="group flex cursor-pointer items-center gap-4 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-4 transition hover:border-zinc-500 hover:bg-zinc-100">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-zinc-600 shadow-sm">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.6-4.6a2 2 0 0 1 2.8 0L16 16m-2-2 1.6-1.6a2 2 0 0 1 2.8 0L20 14M8 8h.01M5 21h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-zinc-800">Pilih foto baru</span>
                            <span id="photoFileName" class="mt-1 block truncate text-xs text-zinc-400">Belum ada file dipilih</span>
                        </span>
                    </label>
                    <input id="photo_profile" name="photo_profile" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only">
                </div>

                <div class="border-t border-zinc-100 pt-7 lg:border-0 lg:pt-0">
                    <h3 class="text-sm font-semibold text-zinc-950">Kontak WhatsApp</h3>
                    <p class="mt-1 text-xs leading-5 text-zinc-500">Nomor ini digunakan sebagai kontak internal.</p>
                </div>

                <div class="border-t border-zinc-100 pt-7 lg:border-0 lg:pt-0">
                    <label for="phone" class="mb-2 block text-xs font-semibold text-zinc-700">Nomor WhatsApp</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm text-zinc-400">WA</span>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: +62 812 3456 7890" class="h-11 w-full rounded-xl border border-zinc-200 bg-white pl-11 pr-4 text-sm text-zinc-800 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                    </div>
                </div>

                <div class="border-t border-zinc-100 pt-7 lg:col-span-2">
                    @if($canChangePassword)
                        <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
                            <div>
                                <h3 class="text-sm font-semibold text-zinc-950">Keamanan Akun</h3>
                                <p class="mt-1 text-xs leading-5 text-zinc-500">Gunakan minimal 8 karakter untuk password baru.</p>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="sm:col-span-2">
                                    <span class="mb-2 block text-xs font-semibold text-zinc-700">Password Saat Ini</span>
                                    <input name="current_password" type="password" autocomplete="current-password" class="h-11 w-full rounded-xl border border-zinc-200 px-4 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                                </label>
                                <label>
                                    <span class="mb-2 block text-xs font-semibold text-zinc-700">Password Baru</span>
                                    <input name="password" type="password" autocomplete="new-password" class="h-11 w-full rounded-xl border border-zinc-200 px-4 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                                </label>
                                <label>
                                    <span class="mb-2 block text-xs font-semibold text-zinc-700">Konfirmasi Password Baru</span>
                                    <input name="password_confirmation" type="password" autocomplete="new-password" class="h-11 w-full rounded-xl border border-zinc-200 px-4 text-sm outline-none focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                                </label>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-zinc-500 shadow-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2" stroke-width="1.8"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </span>
                            <div><p class="text-sm font-semibold text-zinc-800">Perubahan password dibatasi</p><p class="mt-1 text-xs leading-5 text-zinc-500">Jika ingin mengganti password, silakan hubungi tim teknis.</p></div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4 sm:px-8">
                <a href="{{ url()->previous() }}" class="rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-xs font-semibold text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-950">Kembali</a>
                <button type="submit" class="rounded-lg bg-[#161616] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-zinc-800">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <div>
            <p class="text-sm font-semibold text-zinc-950">Koneksi AI</p>
            <p class="mt-1 max-w-2xl text-xs leading-5 text-zinc-500">Hubungkan ChatGPT atau kelola koneksi Codex untuk membaca data marketing sesuai akses Anda.</p>
        </div>
        <a href="{{ route('ai-connections.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-[#161616] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-zinc-800">Kelola koneksi AI</a>
    </div>
</div>
@endsection

@push('script')
<script>
document.getElementById('photo_profile')?.addEventListener('change', function () {
    const label = document.getElementById('photoFileName');
    if (label) label.textContent = this.files?.[0]?.name || 'Belum ada file dipilih';
});
</script>
@endpush
