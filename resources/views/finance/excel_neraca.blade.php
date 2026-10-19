<table>
    <thead>
        <tr>
            <th colspan="4" style="font-weight: bold; font-size: 14px; text-align: center; vertical-align: center;">LAPORAN NERACA SALDO</th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center; font-style: italic; color: #555555;">Periode: {{ $periode }}</th>
        </tr>
        <tr></tr> <tr>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #4F81BD; color: #FFFFFF; text-align: center; vertical-align: center;">KODE AKUN</th>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #4F81BD; color: #FFFFFF; text-align: center; vertical-align: center;">NAMA AKUN</th>
            <th colspan="2" style="font-weight: bold; border: 1px solid #000000; background-color: #4F81BD; color: #FFFFFF; text-align: center; vertical-align: center;">NERACA SALDO</th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #DCE6F1; text-align: center;">DEBET</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #DCE6F1; text-align: center;">KREDIT</th>
        </tr>
    </thead>

    <tbody>
        @foreach($reportData as $row)
        <tr>
            <td style="border: 1px solid #000000; text-align: center;">{{ $row['kode'] }}</td>
            <td style="border: 1px solid #000000;">{{ $row['nama_akun'] }}</td>
            
            <td style="border: 1px solid #000000; text-align: right;">
                {{ $row['saldo_debit'] > 0 ? $row['saldo_debit'] : '' }}
            </td>
            
            <td style="border: 1px solid #000000; text-align: right;">
                {{ $row['saldo_kredit'] > 0 ? $row['saldo_kredit'] : '' }}
            </td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="2" style="font-weight: bold; border: 1px solid #000000; text-align: center; background-color: #B8CCE4;">Total</td>
            <td style="font-weight: bold; border: 1px solid #000000; text-align: right; background-color: #B8CCE4;">{{ $grandTotalDebit }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; text-align: right; background-color: #B8CCE4;">{{ $grandTotalKredit }}</td>
        </tr>
    </tfoot>
</table>