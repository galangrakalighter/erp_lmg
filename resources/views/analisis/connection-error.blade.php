<div class="flex min-h-[420px] items-center justify-center bg-zinc-50 p-6">
    <div class="w-full max-w-xl rounded-2xl border border-amber-200 bg-white p-7 text-center shadow-sm">
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-amber-100 text-2xl text-amber-700">↻</div>
        <p class="mt-4 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Koneksi {{ $platform }}</p>
        <h2 class="mt-2 text-xl font-semibold text-zinc-950">Data belum dapat ditampilkan</h2>
        <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $message }}</p>
        <div class="mt-5 rounded-xl bg-zinc-50 px-4 py-3 text-left text-xs leading-5 text-zinc-600">
            <p class="font-semibold text-zinc-800">Yang dapat Anda lakukan:</p>
            @if(strcasecmp($platform, 'TikTok') === 0)
                <p class="mt-1">Buka Dashboard CMS → Client → Edit, kemudian hubungkan ulang akun TikTok untuk client ini.</p>
            @elseif(strcasecmp($platform, 'Threads') === 0)
                <p class="mt-1">Buka Dashboard CMS → Client → Edit, kemudian hubungkan ulang akun Threads untuk client ini.</p>
            @else
                <p class="mt-1">Buka Dashboard CMS → Client → Edit, hubungkan ulang Meta, lalu pilih Facebook Page dan akun Instagram yang benar.</p>
            @endif
        </div>
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            <button type="button" onclick="window.location.reload()" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700">Muat ulang</button>
            <a href="{{ route('cms.produk', 'LGTR') }}" target="_top" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Buka daftar client</a>
        </div>
        <p class="mt-4 text-xs text-zinc-400">Jika masalah berlanjut, hubungi admin. Detail teknis sudah tersimpan untuk pemeriksaan.</p>
    </div>
</div>
