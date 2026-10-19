<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Content Plan - {{ $produk->nama }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm 9mm 11mm; }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #27272a;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            line-height: 1.35;
        }

        .document-header {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .document-header td { vertical-align: middle; }
        .brand-cell { width: 54px; }
        .brand-logo { width: 42px; height: 42px; object-fit: contain; }
        .title-cell { padding-left: 8px; }
        .eyebrow {
            margin: 0 0 2px;
            color: #71717a;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }
        h1 {
            margin: 0;
            color: #18181b;
            font-size: 19px;
            letter-spacing: -.3px;
        }
        .client-name {
            margin-top: 3px;
            color: #52525b;
            font-size: 8px;
        }
        .header-accent {
            height: 3px;
            margin-bottom: 10px;
            border-radius: 2px;
            background: #18181b;
        }

        .metadata {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .metadata td {
            width: 25%;
            border-top: 1px solid #e4e4e7;
            border-bottom: 1px solid #e4e4e7;
            background: #fafafa;
            padding: 7px 9px;
        }
        .metadata td:first-child {
            border-left: 1px solid #e4e4e7;
            border-radius: 6px 0 0 6px;
        }
        .metadata td:last-child {
            border-right: 1px solid #e4e4e7;
            border-radius: 0 6px 6px 0;
        }
        .meta-label {
            display: block;
            margin-bottom: 2px;
            color: #a1a1aa;
            font-size: 5.7px;
            font-weight: bold;
            letter-spacing: .7px;
            text-transform: uppercase;
        }
        .meta-value {
            color: #27272a;
            font-size: 7.5px;
            font-weight: bold;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .content-table thead { display: table-header-group; }
        .content-table tr { page-break-inside: avoid; }
        .content-table th {
            border: 1px solid #27272a;
            background: #27272a;
            padding: 6px 3px;
            color: #fff;
            font-size: 5.7px;
            font-weight: bold;
            letter-spacing: .25px;
            line-height: 1.2;
            text-align: center;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .content-table td {
            border: 1px solid #d4d4d8;
            padding: 5px 4px;
            color: #3f3f46;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .content-table tbody tr:nth-child(even) td { background: #fafafa; }
        .content-table .number,
        .content-table .center { text-align: center; vertical-align: middle; }
        .content-table .title { color: #18181b; font-weight: bold; }
        .content-table .date { white-space: nowrap; font-size: 6.3px; }
        .content-table .muted { color: #a1a1aa; }
        .content-table a { color: #2563eb; text-decoration: none; }

        .status {
            display: inline-block;
            border: 1px solid #d4d4d8;
            border-radius: 8px;
            background: #f4f4f5;
            padding: 2px 5px;
            color: #3f3f46;
            font-size: 5.8px;
            font-weight: bold;
        }
        .status-done {
            border-color: #a7f3d0;
            background: #ecfdf5;
            color: #047857;
        }
        .status-progress {
            border-color: #fde68a;
            background: #fffbeb;
            color: #a16207;
        }

        .empty-state {
            border: 1px solid #e4e4e7;
            border-radius: 8px;
            background: #fafafa;
            padding: 36px 20px;
            color: #71717a;
            font-size: 9px;
            text-align: center;
        }
        .footer-note {
            margin-top: 8px;
            color: #a1a1aa;
            font-size: 5.8px;
            text-align: right;
        }
    </style>
</head>
<body>
@php
    $formatDate = static function ($value): string {
        if (blank($value)) return '-';
        try {
            return \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d M Y');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };
    $statusClass = static function ($value): string {
        $normalized = strtolower(trim((string) $value));
        if (str_contains($normalized, 'selesai') || str_contains($normalized, 'publish') || str_contains($normalized, 'done')) {
            return 'status status-done';
        }
        if (str_contains($normalized, 'proses') || str_contains($normalized, 'progress') || str_contains($normalized, 'review')) {
            return 'status status-progress';
        }
        return 'status';
    };
@endphp

    <table class="document-header">
        <tr>
            <td class="brand-cell">
                <img class="brand-logo" src="{{ public_path('img/logo.jpg') }}" alt="Lighter Media Group">
            </td>
            <td class="title-cell">
                <p class="eyebrow">Lighter Media Group · Digital Marketing</p>
                <h1>Content Plan</h1>
                <div class="client-name">{{ $produk->nama }}</div>
            </td>
        </tr>
    </table>
    <div class="header-accent"></div>

    <table class="metadata">
        <tr>
            <td>
                <span class="meta-label">Platform</span>
                <span class="meta-value">{{ $platform ?: 'Semua Platform' }}</span>
            </td>
            <td>
                <span class="meta-label">Sumber Konten</span>
                <span class="meta-value">{{ ucfirst($contentSource) }}</span>
            </td>
            <td>
                <span class="meta-label">Periode</span>
                <span class="meta-value">{{ $formatDate($startDate) }} - {{ $formatDate($endDate) }}</span>
            </td>
            <td>
                <span class="meta-label">Total Konten</span>
                <span class="meta-value">{{ $data->count() }} item</span>
            </td>
        </tr>
    </table>

    @if($data->isEmpty())
        <div class="empty-state">
            Belum ada content plan untuk platform dan periode yang dipilih.
        </div>
    @else
        <table class="content-table">
            <colgroup>
                <col style="width: 2.5%">
                <col style="width: 10%">
                <col style="width: 5.5%">
                <col style="width: 5.5%">
                <col style="width: 5.5%">
                <col style="width: 5.5%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 6%">
                <col style="width: 5.5%">
                <col style="width: 4%">
                @if(($kategory->id ?? null) == 2)<col style="width: 5%">@endif
                <col style="width: 6%">
                <col style="width: 7%">
                <col style="width: 6.5%">
                <col style="width: 6.5%">
                <col style="width: 4.5%">
                <col style="width: 6%">
            </colgroup>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul</th>
                    <th>Inspirasi</th>
                    <th>Tipe</th>
                    <th>Strategi</th>
                    <th>Funnel</th>
                    <th>Pilar</th>
                    <th>Hook</th>
                    <th>Body</th>
                    <th>CTA</th>
                    <th>Durasi</th>
                    @if(($kategory->id ?? null) == 2)<th>Warna</th>@endif
                    <th>Komposisi</th>
                    <th>Catatan</th>
                    <th>Posting</th>
                    <th>Deadline</th>
                    <th>Link</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td class="number">{{ $loop->iteration }}</td>
                        <td class="title">{{ filled($item->judul) ? $item->judul : '-' }}</td>
                        <td class="center">
                            @if(filled($item->inspirasi))
                                <a href="{{ $item->inspirasi }}">Lihat</a>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                        <td>{{ filled($item->type) ? $item->type : '-' }}</td>
                        <td>{{ filled($item->strategy) ? $item->strategy : '-' }}</td>
                        <td>{{ filled($item->funneling) ? $item->funneling : '-' }}</td>
                        <td>{{ filled($item->pilar) ? $item->pilar : '-' }}</td>
                        <td>{{ filled($item->hooks) ? $item->hooks : '-' }}</td>
                        <td>{{ filled($item->nama_body) ? $item->nama_body : '-' }}</td>
                        <td>{{ filled($item->nama_cta) ? $item->nama_cta : '-' }}</td>
                        <td class="center">{{ filled($item->durasi) ? $item->durasi : '-' }}</td>
                        @if(($kategory->id ?? null) == 2)
                            <td>{{ filled($item->background) ? $item->background : '-' }}</td>
                        @endif
                        <td>{{ filled($item->komposisi) ? $item->komposisi : '-' }}</td>
                        <td>{{ filled($item->note) ? $item->note : '-' }}</td>
                        <td class="center date">{{ $formatDate($item->tanggal_posting) }}</td>
                        <td class="center date">{{ $formatDate($item->tanggal_deadline) }}</td>
                        <td class="center">
                            @if(filled($item->link))
                                <a href="{{ $item->link }}">Buka</a>
                            @else
                                <span class="muted">-</span>
                            @endif
                        </td>
                        <td class="center">
                            <span class="{{ $statusClass($item->nama_status) }}">
                                {{ filled($item->nama_status) ? $item->nama_status : 'Belum diatur' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer-note">
        Diekspor {{ now('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }} WIB
    </div>
</body>
</html>
