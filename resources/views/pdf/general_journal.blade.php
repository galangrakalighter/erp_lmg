<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jurnal Umum</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #bbb;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background-color: #343a40;
            color: #fff;
            text-align: center;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 3px 6px;
            font-size: 9px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-posted {
            background-color: #28a745;
            color: #fff;
        }
        .badge-draft {
            background-color: #ffc107;
            color: #000;
        }
        tfoot tr {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <h3>Laporan Jurnal Umum</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Tanggal / No. Jurnal</th>
                <th style="width: 16%">No. Invoice / Keterangan</th>
                <th style="width: 12%">Kode Akun</th>
                <th style="width: 25%">Nama Akun COA</th>
                <th style="width: 15%">Debit (Rp)</th>
                <th style="width: 15%">Kredit (Rp)</th>
                <th style="width: 5%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalDebit = 0; 
                $totalKredit = 0; 
            @endphp

            @forelse($journals as $journal)
                @foreach($journal->details as $index => $detail)
                    @php 
                        $totalDebit += $detail->debit;
                        $totalKredit += $detail->kredit;
                    @endphp
                    <tr>
                        {{-- Kolom Tanggal & No Jurnal (Hanya dirender di baris pertama per grouping jurnal) --}}
                        @if($index === 0)
                            <td rowspan="{{ count($journal->details) }}">
                                {{ \Carbon\Carbon::parse($journal->tgl_transaksi)->format('d/m/Y') }}<br>
                                <strong>{{ $journal->no_jurnal }}</strong>
                            </td>
                            <td rowspan="{{ count($journal->details) }}">
                                {{ $journal->no_invoice ?? '-' }}<br>
                                <small style="color: #666;">{{ $journal->keterangan ?? '-' }}</small>
                            </td>
                        @endif

                        {{-- Kolom Detail Akun --}}
                        <td>{{ $detail->account_code ?? '-' }}</td>
                        <td>{{ $detail->account_name ?? '-' }}</td>
                        <td class="text-right">{{ number_format($detail->debit, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($detail->kredit, 2, ',', '.') }}</td>

                        {{-- Kolom Status (Hanya dirender di baris pertama) --}}
                        @if($index === 0)
                            <td class="text-center" rowspan="{{ count($journal->details) }}">
                                @php
                                    $statusClass = strtolower($journal->status) == 'posted' ? 'badge-posted' : 'badge-draft';
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    {{ strtoupper($journal->status ?? '-') }}
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #777;">
                        Tidak ada data jurnal umum yang ditemukan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right" style="padding-right: 10px;">TOTAL KESELURUHAN:</td>
                <td class="text-right">{{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalKredit, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>