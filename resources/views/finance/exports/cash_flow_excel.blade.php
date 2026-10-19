<table border="1">
    <thead>
        <tr>
            <th colspan="4" style="text-align:center; font-size:16px; font-weight:bold; background-color:#ffffff; height:30px; vertical-align:middle;">
                LAPORAN KEUANGAN - {{ \Carbon\Carbon::parse($periode.'-01')->format('F Y') }}
            </th>
        </tr>
        <tr style="background-color:#4472c4; color:#ffffff; font-weight:bold; text-align:center; height:25px;">
            <th style="width:15px;">Kode</th>
            <th style="width:40px;">Nama Akun</th>
            <th style="width:20px;">Kas Masuk</th>
            <th style="width:20px;">Kas Keluar</th>
        </tr>
    </thead>
    <tbody>
        @foreach(['Operasional' => $operasional, 'Investasi' => $investasi, 'Pendanaan' => $pendanaan] as $title => $items)
            <tr>
                <td colspan="4" style="background-color:#d9e1f2; font-weight:bold; padding-left:10px;">
                    {{ $loop->iteration }}. Aktivitas {{ $title }}
                </td>
            </tr>
            @foreach($items as $item)
                <tr>
                    <td style="text-align:center;">{{ $item['kode'] }}</td>
                    <td style="padding-left:10px;">{{ $item['nama_akun'] }}</td>
                    <td style="text-align:right; mso-number-format:'\#\,\#\#0';">{{ $item['jumlah'] > 0 ? $item['jumlah'] : 0 }}</td>
                    <td style="text-align:right; mso-number-format:'\#\,\#\#0';">{{ $item['jumlah'] < 0 ? abs($item['jumlah']) : 0 }}</td>
                </tr>
            @endforeach
        @endforeach
        
        <tr style="font-weight:bold; background-color:#f2f2f2; border-top:2px solid #000000;">
            <td colspan="2" style="text-align:right; padding-right:10px;">Saldo Akhir Kas &amp; Bank</td>
            <td colspan="2" style="text-align:center; mso-number-format:'\#\,\#\#0';">{{ $totalSaldoAkhir }}</td>
        </tr>
    </tbody>
</table>