<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Hubungkan Codex | ERP LMG</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-zinc-100 text-zinc-950">
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-5 py-10">
        <section class="w-full rounded-2xl border border-zinc-200 bg-white p-7 shadow-sm sm:p-9">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Koneksi aman ERP LMG</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight">Hubungkan {{ $client->name }}?</h1>
            <p class="mt-3 text-sm leading-6 text-zinc-600">
                Aplikasi ini meminta izin baca. Password ERP dan token OAuth tidak akan diberikan kepada aplikasi.
            </p>

            <div class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                <p class="text-xs font-semibold text-zinc-800">Izin yang diminta</p>
                <ul class="mt-2 space-y-2 text-sm text-zinc-600">
                    @forelse($scopes as $scope)
                        <li>&bull; {{ $scope->description }}</li>
                    @empty
                        <li>&bull; Mengidentifikasi akun yang sedang terhubung.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row">
                <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button class="w-full rounded-lg border border-zinc-300 px-5 py-3 text-sm font-semibold hover:bg-zinc-50">Batalkan</button>
                </form>
                <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button class="w-full rounded-lg bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Izinkan koneksi</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
