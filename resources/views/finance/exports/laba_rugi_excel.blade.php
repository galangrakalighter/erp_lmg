<table border="1">
    <thead>
        <tr>
            <th colspan="4" style="font-weight: bold; font-size: 16px; text-align: center; border: 1px solid #000000; height: 30px; vertical-align: middle;">
                LAPORAN NERACA
            </th>
        </tr>
        <tr>
            <th colspan="4" style="text-align: center; border: 1px solid #000000; font-style: italic; background-color: #f9f9f9;">
                Periode: {{ \Carbon\Carbon::parse($periode)->translatedFormat('F Y') }}
            </th>
        </tr>
        <tr><th colspan="4" style="border: none;"></th></tr> <tr>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center; width: 15px;">Kode Akun</th>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center; width: 40px;">Nama Akun</th>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center; width: 20px;">Debet</th>
            <th style="background-color: #4F81BD; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center; width: 20px;">Kredit</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td style="border: 1px solid #BFBFBF; text-align: center;">{{ $row['kode'] }}</td>
                <td style="border: 1px solid #BFBFBF; padding-left: 10px;">{{ $row['nama_akun'] }}</td>
                <td style="border: 1px solid #BFBFBF; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">
                    {{ $row['debit'] ?: 0 }}
                </td>
                <td style="border: 1px solid #BFBFBF; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">
                    {{ $row['kredit'] ?: 0 }}
                </td>
            </tr>
        @endforeach
        
        <tr style="background-color: #D9E1F2; font-weight: bold;">
            <td colspan="2" style="border: 1px solid #000000; text-align: center;">Jumlah Sub-Total</td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">{{ $totals['n_debit'] }}</td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">{{ $totals['n_kredit'] }}</td>
        </tr>

        <tr style="background-color: #E2EFDA; font-weight: bold;">
            <td colspan="2" style="border: 1px solid #000000; text-align: center; font-style: italic;">
                Menyeimbangkan Laba/Rugi Bersih Periode Berjalan
            </td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">
                {{ $lr_balance >= 0 ? $lr_balance : 0 }}
            </td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">
                {{ $lr_balance < 0 ? abs($lr_balance) : 0 }}
            </td>
        </tr>

        <tr style="background-color: #8EA9DB; font-weight: bold; color: #ffffff;">
            <td colspan="2" style="border: 1px solid #000000; text-align: center;">TOTAL BALANCED</td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">
                {{ max($totals['n_debit'] + ($lr_balance > 0 ? $lr_balance : 0), $totals['n_kredit'] + ($lr_balance < 0 ? abs($lr_balance) : 0)) }}
            </td>
            <td style="border: 1px solid #000000; text-align: right; padding-right: 5px; mso-number-format:'\#\,\#\#0';">
                {{ max($totals['n_debit'] + ($lr_balance > 0 ? $lr_balance : 0), $totals['n_kredit'] + ($lr_balance < 0 ? abs($lr_balance) : 0)) }}
            </td>
        </tr>
    </tbody>
</table>