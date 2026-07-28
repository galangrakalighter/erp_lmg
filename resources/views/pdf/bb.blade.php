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

    @forelse($ledger as $item)

        @php
            $account = $item['account'];
            $rows = $item['mutasi'];

            $subTotalDebit = 0;
            $subTotalKredit = 0;
        @endphp

        <div class="account-section">

            <div class="account-title">
                [{{ $account->kode }}] - {{ $account->nama_akun }}
                <span style="float:right">
                    (Normal : {{ strtoupper($account->type) }})
                </span>
            </div>

            <table>

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Jurnal</th>
                        <th>Keterangan</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                        <th>Saldo</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- Saldo Awal --}}
                    <tr>
                        <td colspan="5"><strong>Saldo Awal</strong></td>
                        <td class="text-right">
                            {{ number_format($item['opening_balance'],2,',','.') }}
                        </td>
                    </tr>

                    @foreach($rows as $row)

                        @php
                            $subTotalDebit += $row['debit'];
                            $subTotalKredit += $row['kredit'];
                        @endphp

                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td>{{ $row['nomor_jurnal'] }}</td>
                            <td>{{ $row['keterangan'] }}</td>
                            <td class="text-right">
                                {{ number_format($row['debit'], 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($row['kredit'], 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($row['saldo_berjalan'], 0, ',', '.') }}
                            </td>
                        </tr>

                    @endforeach

                </tbody>

                <tfoot>

                    <tr>
                        <td colspan="3" class="text-right">
                            <strong>Total Mutasi</strong>
                        </td>

                        <td class="text-right">
                            {{ number_format($subTotalDebit,2,',','.') }}
                        </td>

                        <td class="text-right">
                            {{ number_format($subTotalKredit,2,',','.') }}
                        </td>

                        <td class="text-right">
                            {{ number_format($item['final_balance'],2,',','.') }}
                        </td>
                    </tr>

                </tfoot>

            </table>

        </div>

    @empty

    <p>Tidak ada data.</p>

    @endforelse

</body>
</html>