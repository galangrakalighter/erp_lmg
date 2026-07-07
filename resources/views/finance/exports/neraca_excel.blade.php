<table>
    <thead>
        <tr>
            <th colspan="4" style="font-weight: bold; font-size: 14px; text-align: center;">LAPORAN NERACA</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center;">Periode: {{ \Carbon\Carbon::parse($periode)->translatedFormat('F Y') }}</th>
        </tr>
        <tr></tr> 
        <tr>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000;">Kode Akun</th>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000; width: 250px;">Nama Akun</th>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000;">Debet</th>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000;">Kredit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td style="border: 1px solid #BFBFBF; text-align: center;">{{ $row['kode'] }}</td>
                <td style="border: 1px solid #BFBFBF;">{{ $row['nama_akun'] }}</td>
                <td style="border: 1px solid #BFBFBF; text-align: right;">{{ $row['debit'] ?: 0 }}</td>
                <td style="border: 1px solid #BFBFBF; text-align: right;">{{ $row['kredit'] ?: 0 }}</td>
            </tr>
        @endforeach
        
        <tr style="background-color: #D9E1F2; font-weight: bold;">
            <td colspan="2" style="border: 1px solid #000000; text-align: center;">Jumlah Sub-Total</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $totals['n_debit'] }}</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $totals['n_kredit'] }}</td>
        </tr>

        <tr style="background-color: #E2EFDA; font-weight: bold;">
            <td colspan="2" style="border: 1px solid #000000; text-align: center; font-style: italic;">
                Menyeimbangkan Laba/Rugi Bersih Periode Berjalan
            </td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $lr_balance >= 0 ? $lr_balance : 0 }}</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ $lr_balance < 0 ? abs($lr_balance) : 0 }}</td>
        </tr>

        <tr style="background-color: #8EA9DB; font-weight: bold;">
            <td colspan="2" style="border: 1px solid #000000; text-align: center;">TOTAL BALANCED</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ max($totals['n_debit'], $totals['n_kredit']) }}</td>
            <td style="border: 1px solid #000000; text-align: right;">{{ max($totals['n_debit'], $totals['n_kredit']) }}</td>
        </tr>
    </tbody>
</table>