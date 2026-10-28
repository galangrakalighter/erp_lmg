<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Verifikasi OTP ERP Lighter Media Group">
    <title>ERP - Verifikasi OTP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f5f3] font-['Inter'] text-zinc-900">
<main class="flex min-h-screen items-center justify-center px-6 py-12">
    <div class="w-full max-w-lg overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-[0_25px_80px_rgba(0,0,0,0.08)]">
        <div class="p-8 sm:p-12">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 overflow-hidden rounded-xl bg-white"><img src="{{ asset('img/logo.jpg') }}" class="h-full w-full object-cover" alt="Lighter Media Group"></div>
                <div><p class="text-sm font-bold tracking-[.2em]">LIGHTER</p><p class="text-[10px] tracking-[.25em] text-zinc-400">MEDIA GROUP</p></div>
            </div>
            <div class="mt-10">
                <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-zinc-950 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l7 3v5c0 4.6-3 8.2-7 10-4-1.8-7-5.4-7-10V6l7-3zM9.5 12l1.7 1.7 3.5-3.5"/></svg>
                </div>
                <p class="mt-5 text-xs font-semibold uppercase tracking-[.2em] text-zinc-400">Langkah keamanan</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950">Masukkan kode OTP</h1>
                <p class="mt-3 text-sm leading-6 text-zinc-500">Kode 6 angka telah dikirim ke <strong class="text-zinc-800">{{ $maskedEmail }}</strong>. Kode berlaku selama {{ $expiresInMinutes }} menit.</p>
            </div>

            @if(session('status'))<div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800">{{ session('status') }}</div>@endif
            @error('otp')<div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $message }}</div>@enderror

            <form id="otpForm" method="POST" action="{{ route('login.otp.verify') }}" class="mt-7 space-y-5">
                @csrf
                <label class="block"><span class="mb-2 block text-sm font-semibold text-zinc-800">Kode OTP</span><input id="otp" name="otp" value="{{ old('otp') }}" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" autofocus required placeholder="000000" class="block w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-4 text-center text-2xl font-bold tracking-[.45em] outline-none transition focus:border-zinc-950 focus:bg-white focus:ring-4 focus:ring-zinc-950/5"></label>
                <button id="verifyButton" class="flex w-full items-center justify-center rounded-xl bg-black px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-zinc-800"><span id="verifyLabel">Verifikasi & Masuk</span></button>
            </form>

            <div class="mt-6 flex flex-col items-center gap-3 border-t border-zinc-100 pt-6 text-xs">
                <form method="POST" action="{{ route('login.otp.resend') }}">@csrf<button id="resendButton" class="font-semibold text-zinc-700 hover:text-black" data-wait="{{ $resendAfter }}">Kirim ulang kode</button></form>
                <form method="POST" action="{{ route('login.otp.cancel') }}">@csrf<button class="text-zinc-400 underline hover:text-zinc-700">Kembali ke login</button></form>
            </div>
        </div>
    </div>
</main>
<script>
    const otpInput = document.getElementById('otp');
    otpInput.addEventListener('input', () => otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6));
    document.getElementById('otpForm').addEventListener('submit', () => { const button = document.getElementById('verifyButton'); button.disabled = true; button.classList.add('cursor-wait','opacity-80'); document.getElementById('verifyLabel').textContent = 'Memverifikasi...'; });
    const resend = document.getElementById('resendButton'); let wait = Number(resend.dataset.wait || 0);
    const updateResend = () => { resend.disabled = wait > 0; resend.classList.toggle('cursor-not-allowed', wait > 0); resend.classList.toggle('text-zinc-400', wait > 0); resend.textContent = wait > 0 ? `Kirim ulang dalam ${wait} detik` : 'Kirim ulang kode'; };
    updateResend(); if (wait > 0) setInterval(() => { wait = Math.max(0, wait - 1); updateResend(); }, 1000);
</script>
</body>
</html>
