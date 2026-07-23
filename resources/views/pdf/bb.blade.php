<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Buku Besar</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header h3 { margin: 0 0 3px 0; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 11px; color: #555; }
        .account-section { margin-bottom: 25px; page-break-inside: avoid; }
        .account-title { font-size: 11px; font-weight: bold; background: #e9ecef; padding: 6px 8px; border: 1px solid #bbb; margin-bottom: 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 5px 7px; vertical-align: top; }
        th { background-color: #343a40; color: #fff; text-align: center; font-size: 10px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot tr { background-color: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h3>Laporan Buku Besar (General Ledger) - Seluruh Akun</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('F Y') }}</p>
    </div>

    @forelse($mutasi as $accountId => $rows)
        @php
            $firstRow = $rows->first();
            $subTotalDebit = 0;
            $subTotalKredit = 0;
            $runningBalance = 0;
        @endphp

        <div class="account-section">
            <div class="account-title">
                [{{ $firstRow->account_code }}] - {{ $firstRow->account_name }} 
                <span style="font-weight: normal; font-size: 9px; float: right;">(Normal: {{ strtoupper($firstRow->account_type) }})</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%">Tanggal</th>
                        <th style="width: 12%">No. Jurnal</th>
                        <th style="width: 15%">No. Invoice</th>
                        <th style="width: 28%">Keterangan</th>
                        <th style="width: 12%">Debit (Rp)</th>
                        <th style="width: 12%">Kredit (Rp)</th>
                        <th style="width: 11%">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $subTotalDebit += $row->debit;
                            $subTotalKredit += $row->kredit;
                            
                            // Hitung saldo berjalan berdasarkan tipe normal akun ('D' atau 'K')
                            if(strtoupper($firstRow->account_type) === 'K') {
                                $runningBalance += ($row->kredit - $row->debit);
                            } else {
                                $runningBalance += ($row->debit - $row->kredit);
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ \Carbon\Carbon::parse($row->tgl_transaksi)->format('d/m/Y') }}</td>
                            <td class="text-center"><strong>{{ $row->no_jurnal }}</strong></td>
                            <td>{{ $row->no_invoice ?? '-' }}</td>
                            <td>{{ $row->keterangan_item ?: $row->journal_keterangan }}</td>
                            <td class="text-right">{{ number_format($row->debit, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($row->kredit, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($runningBalance, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right">TOTAL MUTASI BULAN INI:</td>
                        <td class="text-right">{{ number_format($subTotalDebit, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($subTotalKredit, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($runningBalance, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <p class="text-center" style="padding: 30px; color: #777;">Tidak ada transaksi buku besar yang berstatus 'posted' pada periode ini.</p>
    @endforelse

</body>
</html>