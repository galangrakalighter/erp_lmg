<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jurnal Penyesuaian</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 8px; }
        .header h3 { margin: 0 0 3px 0; font-size: 14px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 11px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #bbb; padding: 5px 7px; vertical-align: top; }
        th { background-color: #4e73df; color: #fff; text-align: center; font-size: 10px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        tfoot tr { background-color: #f8f9fc; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h3>Laporan Jurnal Penyesuaian (Adjustment Journal)</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($periodeInput)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 46%;">Keterangan Akun / Transaksi</th>
                <th style="width: 12%;">Kode Akun</th>
                <th style="width: 15%;">Debet (Rp)</th>
                <th style="width: 15%;">Kredit (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalDebet = 0;
                $grandTotalKredit = 0;
            @endphp

            @forelse($journals as $journal)
                @foreach($journal->details as $index => $detail)
                    @php
                        $grandTotalDebet += (float) $detail->debit;
                        $grandTotalKredit += (float) $detail->kredit;
                    @endphp
                    <tr>
                        <td class="text-center">
                            @if($index === 0)
                                {{ \Carbon\Carbon::parse($journal->tgl_transaksi)->format('d/m/Y') }}
                            @endif
                        </td>
                        <td>
                            @if((float) $detail->debit > 0)
                                {{ $detail->account->nama_akun ?? 'N/A' }}
                            @else
                                &nbsp;&nbsp;&nbsp;&nbsp;{{ $detail->account->nama_akun ?? 'N/A' }}
                            @endif

                            @if($index === count($journal->details) - 1 && $journal->keterangan)
                                <div style="font-size: 9px; color: #555; font-style: italic; margin-top: 2px;">
                                    ({{ $journal->keterangan }})
                                </div>
                            @endif
                        </td>
                        <td class="text-center">{{ $detail->account->kode ?? '-' }}</td>
                        <td class="text-right">
                            {{ (float)$detail->debit > 0 ? number_format($detail->debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ (float)$detail->kredit > 0 ? number_format($detail->kredit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #777;">Tidak ada data jurnal penyesuaian pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($journals) > 0)
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalDebet, 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalKredit, 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>