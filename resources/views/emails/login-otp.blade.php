<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Kode OTP Login</title></head>
<body style="margin:0;background:#f4f4f5;font-family:Arial,sans-serif;color:#18181b">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f4f5;padding:32px 16px">
    <tr><td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fff;border:1px solid #e4e4e7;border-radius:20px;overflow:hidden">
            <tr><td style="background:#18181b;padding:28px 32px;color:#fff"><div style="font-size:11px;letter-spacing:2px;color:#a1a1aa;font-weight:bold">LIGHTER MEDIA GROUP</div><div style="margin-top:8px;font-size:24px;font-weight:bold">Verifikasi Login ERP</div></td></tr>
            <tr><td style="padding:32px">
                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;color:#52525b">Gunakan kode berikut untuk menyelesaikan proses login. Jangan berikan kode ini kepada siapa pun.</p>
                <div style="margin:24px 0;padding:22px;text-align:center;background:#f4f4f5;border-radius:14px;font-size:34px;font-weight:bold;letter-spacing:10px;color:#18181b">{{ $code }}</div>
                <p style="margin:0;font-size:13px;line-height:1.7;color:#71717a">Kode berlaku sampai <strong>{{ $expiresAt->timezone('Asia/Jakarta')->format('H:i') }} WIB</strong>. Jika Anda tidak mencoba login, abaikan email ini dan segera hubungi administrator jika aktivitas terlihat mencurigakan.</p>
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
