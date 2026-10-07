<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index,follow">
    <title>@yield('title') · Lighter Media Group</title>
    <style>
        :root { color-scheme: light; --ink:#18181b; --muted:#71717a; --line:#e4e4e7; --paper:#fff; --wash:#f4f4f5; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--wash); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; line-height:1.7; }
        header { border-bottom:1px solid #27272a; background:#09090b; color:#fff; }
        nav { display:flex; max-width:980px; margin:auto; padding:18px 24px; align-items:center; justify-content:space-between; gap:24px; }
        .brand { color:#fff; font-size:14px; font-weight:800; letter-spacing:.08em; text-decoration:none; text-transform:uppercase; }
        .links { display:flex; flex-wrap:wrap; gap:18px; }
        .links a { color:#a1a1aa; font-size:12px; font-weight:600; text-decoration:none; }
        .links a:hover { color:#fff; }
        main { max-width:900px; margin:42px auto; padding:0 20px; }
        article { border:1px solid var(--line); border-radius:22px; background:var(--paper); padding:clamp(26px,5vw,54px); box-shadow:0 18px 50px rgba(24,24,27,.07); }
        .eyebrow { margin:0 0 10px; color:var(--muted); font-size:11px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        h1 { margin:0; font-size:clamp(30px,5vw,48px); line-height:1.15; letter-spacing:-.035em; }
        h2 { margin:34px 0 8px; font-size:19px; }
        p, li { color:#52525b; }
        ul, ol { padding-left:22px; }
        .table-wrap { margin:20px 0; overflow-x:auto; }
        table { width:100%; min-width:780px; border-collapse:collapse; font-size:13px; }
        th, td { border:1px solid var(--line); padding:12px; text-align:left; vertical-align:top; }
        th { background:#f4f4f5; color:var(--ink); }
        .updated { margin:14px 0 30px; color:#a1a1aa; font-size:13px; }
        .notice { margin:26px 0; border:1px solid #d4d4d8; border-radius:14px; background:#fafafa; padding:18px 20px; }
        .notice strong { display:block; margin-bottom:4px; color:var(--ink); }
        a { color:#18181b; }
        footer { padding:0 20px 36px; color:#a1a1aa; text-align:center; font-size:12px; }
        @media (max-width:640px) { nav { align-items:flex-start; flex-direction:column; } .links { gap:12px; } main { margin-top:24px; } }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="brand" href="https://lightermediagroup.com">Lighter Media Group</a>
            <div class="links">
                <a href="{{ auth()->check() ? route('home') : route('login') }}">{{ auth()->check() ? 'Buka ERP' : 'Masuk ERP' }}</a>
                <a href="{{ route('legal.privacy') }}">Kebijakan Privasi</a>
                <a href="{{ route('legal.terms') }}">Ketentuan Layanan</a>
                <a href="{{ route('legal.data-deletion') }}">Penghapusan Data</a>
            </div>
        </nav>
    </header>
    <main><article>@yield('content')</article></main>
    <footer>&copy; {{ date('Y') }} Lighter Media Group. Seluruh hak cipta dilindungi.</footer>
</body>
</html>
