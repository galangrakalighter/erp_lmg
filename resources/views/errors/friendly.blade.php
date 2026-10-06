<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f8fafc;color:#18181b;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.page{min-height:100vh;display:grid;place-items:center;padding:24px}.card{width:min(560px,100%);background:#fff;border:1px solid #e4e4e7;border-radius:22px;padding:36px;text-align:center;box-shadow:0 18px 50px rgba(24,24,27,.08)}.icon{width:62px;height:62px;margin:auto;display:grid;place-items:center;border-radius:50%;background:#fef3c7;color:#b45309;font-size:28px}.label{margin:18px 0 6px;color:#b45309;font-size:12px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}h1{margin:0;font-size:25px;line-height:1.3}.message{margin:12px auto 0;max-width:450px;color:#52525b;font-size:14px;line-height:1.7}.actions{margin-top:24px;display:flex;flex-wrap:wrap;justify-content:center;gap:10px}.button{display:inline-block;border-radius:10px;padding:10px 16px;text-decoration:none;font-size:14px;font-weight:650;border:1px solid #d4d4d8;color:#3f3f46;background:#fff}.button.primary{border-color:#18181b;color:#fff;background:#18181b}.help{margin-top:20px;color:#a1a1aa;font-size:12px;line-height:1.6}
    </style>
</head>
<body>
<main class="page">
    <section class="card">
        <div class="icon">!</div>
        <p class="label">Butuh perhatian</p>
        <h1>{{ $title }}</h1>
        <p class="message">{{ $message }}</p>
        <div class="actions">
            @if($showLogin ?? false)
                <a class="button primary" href="{{ route('login') }}">Login kembali</a>
            @else
                <button class="button primary" type="button" onclick="window.location.reload()">Coba lagi</button>
            @endif
            <a class="button" href="{{ url('/') }}">Kembali ke beranda</a>
        </div>
        <p class="help">Jika masalah terus muncul, hubungi admin. Detail teknis telah tersimpan dan tidak perlu Anda salin.</p>
    </section>
</main>
</body>
</html>
