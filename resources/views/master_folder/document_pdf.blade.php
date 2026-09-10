<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 70px 70px 65px; }
        body { font-family: DejaVu Sans, sans-serif; color: #252a34; font-size: 11pt; line-height: 1.65; }
        h1 { font-size: 22pt; margin: 0 0 18px; line-height: 1.25; }
        h2 { font-size: 17pt; margin: 20px 0 10px; line-height: 1.3; }
        h3 { font-size: 13pt; margin: 16px 0 8px; }
        p, div { margin: 0 0 10px; }
        ul, ol { margin: 8px 0 12px 22px; padding: 0; }
        blockquote { margin: 12px 0; padding: 8px 14px; border-left: 3px solid #4e73df; color: #5a5c69; background: #f8f9fc; }
        table { width: 100%; max-width: 100%; border-collapse: collapse; margin: 12px 0; }
        td, th { border: 1px solid #dfe3e8; padding: 7px; }
        .footer { position: fixed; bottom: -42px; left: 0; right: 0; text-align: center; color: #a0a4ab; font-size: 8pt; }
    </style>
</head>
<body>
    <div class="footer">Folder Master &middot; {{ $folder->name }}</div>
    @php
        $renderedContent = preg_replace(
            '/rgba\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*,\s*0(?:\.0+)?\s*\)/i',
            'transparent',
            $content
        );
    @endphp
    {!! $renderedContent !!}
</body>
</html>
