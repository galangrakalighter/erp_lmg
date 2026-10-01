<div class="flex min-h-[420px] items-center justify-center bg-zinc-50 p-6">
    <div class="w-full max-w-xl rounded-2xl border border-amber-200 bg-white p-7 text-center shadow-sm">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-amber-100 text-xl font-bold text-amber-700">!</div>
        <h2 class="mt-4 text-lg font-semibold text-zinc-950">{{ $platform }} belum siap digunakan</h2>
        <p class="mt-2 text-sm leading-6 text-zinc-600">{{ $message }}</p>
        <p class="mt-4 rounded-xl bg-zinc-50 px-4 py-3 text-xs leading-5 text-zinc-500">
            @if(strcasecmp($platform, 'TikTok') === 0)
                Buka Dashboard CMS → Client → Edit, kemudian hubungkan atau sambungkan ulang akun TikTok untuk client ini.
            @else
                Buka Dashboard CMS → Client → Edit, periksa aset Meta yang tersedia, kemudian pilih Facebook Page dan akun terkait.
            @endif
        </p>
    </div>
</div>
