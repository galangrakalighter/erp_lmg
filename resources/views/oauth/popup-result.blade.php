<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? 'Meta Terhubung' : 'Koneksi Meta Gagal' }}</title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f4f5;font-family:Arial,sans-serif;color:#18181b}.card{width:min(88%,420px);padding:32px;border:1px solid #e4e4e7;border-radius:20px;background:#fff;text-align:center;box-shadow:0 20px 45px rgba(0,0,0,.09)}.icon{display:grid;width:56px;height:56px;margin:0 auto 18px;place-items:center;border-radius:50%;background:{{ $success ? '#dcfce7' : '#fee2e2' }};color:{{ $success ? '#15803d' : '#b91c1c' }};font-size:28px;font-weight:bold}h1{font-size:20px;margin:0 0 10px}p{font-size:14px;line-height:1.6;color:#71717a;margin:0}
    </style>
</head>
<body>
<main class="card"><div class="icon">{{ $success ? '✓' : '!' }}</div><h1>{{ $success ? 'Meta berhasil terhubung' : 'Koneksi Meta gagal' }}</h1><p>{{ $message }}</p></main>
<script>
    const payload = @json([
        'type' => $success ? 'meta-oauth-success' : 'meta-oauth-error',
        'message' => $message,
        'connection' => $connection ?? null,
    ]);
    if (window.opener) {
        window.opener.postMessage(payload, @json(url('/')));
        setTimeout(() => window.close(), 700);
    }
</script>
</body>
</html>
