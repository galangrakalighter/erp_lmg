<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Neraca</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0 0 5px; font-size: 15px; text-transform: uppercase; }
        .header h2 { margin: 0 0 4px; font-size: 13px; }
        .header p { margin: 0; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #374151; padding: 6px 7px; vertical-align: middle; }
        th { background: #9dc3e6; font-size: 11px; font-weight: bold; text-align: center; }
        .name { width: 34%; }
        .amount { width: 16%; text-align: right; white-space: nowrap; }
        .empty { color: transparent; }
        tfoot td { background: #eef4f9; font-weight: bold; border-top: 2px solid #111827; }
    </style>
</head>
<body>
    @php($rowCount = max($aktiva->count(), $passiva->count()))
    <div class="header">
        <h1>{{ $namaPerusahaan }}</h1>
        <h2>NERACA</h2>
        <p>{{ \Carbon\Carbon::parse($periode . '-01')->locale('id')->translatedFormat('F Y') }} (dalam Rp)</p>
    </div>
    <table>
        <thead>
            <tr><th colspan="2">AKTIVA</th><th colspan="2">PASSIVA</th></tr>
        </thead>
        <tbody>
            @for($i = 0; $i < $rowCount; $i++)
                @php($aktivaRow = $aktiva->get($i))
                @php($passivaRow = $passiva->get($i))
                <tr>
                    <td class="name {{ $aktivaRow ? '' : 'empty' }}">{{ $aktivaRow['nama_akun'] ?? '-' }}</td>
                    <td class="amount">{{ $aktivaRow ? number_format($aktivaRow['jumlah'], 0, ',', '.') : '' }}</td>
                    <td class="name {{ $passivaRow ? '' : 'empty' }}">{{ $passivaRow['nama_akun'] ?? '-' }}</td>
                    <td class="amount">{{ $passivaRow ? number_format($passivaRow['jumlah'], 0, ',', '.') : '' }}</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td>Total Aktiva</td><td class="amount">{{ number_format($aktiva->sum('jumlah'), 0, ',', '.') }}</td>
                <td>Total Passiva</td><td class="amount">{{ number_format($passiva->sum('jumlah'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
