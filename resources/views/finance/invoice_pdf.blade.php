<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #111; line-height: 1.4; margin: 0; padding: 20px; }
        .header-table, .info-table, .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .company-name { font-weight: bold; font-size: 13px; margin-top: 10px; }
        .invoice-header-right { text-align: right; vertical-align: top; }
        .invoice-title { font-size: 22px; font-weight: bold; letter-spacing: 1px; margin-bottom: 5px; }
        
        .client-section { margin-bottom: 20px; }
        
        .items-table th, .items-table td { border: 1px solid #111; padding: 7px 10px; vertical-align: middle; }
        .items-table th { background-color: #f9f9f9; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .signature-section { margin-top: 30px; float: right; text-align: center; width: 220px; }
        .signature-space { height: 55px; }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <table style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top;">
                            <img src="{{ public_path('img/icon-lmg.jpeg') }}" alt="Logo" style="width: 85px; height: auto; display: block;">
                            <div class="company-name">PT. Ekosistem Media Digital Terintegrasi</div>
                            <div style="font-size: 11.5px; color: #222; margin-top: 3px;">Graha DLA, Jl. Otto Iskandar Dinata No.392, Nyengseret, Astanaanyar, Kota Bandung</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;" class="invoice-header-right">
                <div class="invoice-title">INVOICE</div>
                <div style="font-size: 13px; font-weight: bold; margin-bottom: 2px;">{{ $invoice_number }}</div>
                <div style="font-size: 12px;">Tanggal Invoice : {{ $invoice_date }}</div>
            </td>
        </tr>
    </table>

    <!-- Client Section -->
    <div class="client-section">
        <div>Kepada Yth.</div>
        <div style="font-weight: bold; font-size: 13px; margin-top: 2px;">{{ $client_company }}</div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 7%;">No.</th>
                <th>Layanan</th>
                <th style="width: 22%;">Harga</th>
                <th style="width: 22%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotal = 0; 

                // Fungsi helper lokal untuk mengubah angka menjadi terbilang bahasa Indonesia
                function penyebut($nilai) {
                    $nilai = abs($nilai);
                    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
                    $temp = "";
                    if ($nilai < 12) {
                        $temp = " ". $huruf[$nilai];
                    } else if ($nilai < 20) {
                        $temp = penyebut($nilai - 10). " belas";
                    } else if ($nilai < 100) {
                        $temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
                    } else if ($nilai < 200) {
                        $temp = " seratus". penyebut($nilai - 100);
                    } else if ($nilai < 1000) {
                        $temp = penyebut($nilai/100)." ratus". penyebut($nilai % 100);
                    } else if ($nilai < 2000) {
                        $temp = " seribu". penyebut($nilai - 1000);
                    } else if ($nilai < 1000000) {
                        $temp = penyebut($nilai/1000)." ribu". penyebut($nilai % 1000);
                    } else if ($nilai < 1000000000) {
                        $temp = penyebut($nilai/1000000)." juta". penyebut($nilai % 1000000);
                    } else if ($nilai < 1000000000000) {
                        $temp = penyebut($nilai/1000000000)." miliar". penyebut(fmod($nilai,1000000000));
                    } else if ($nilai < 1000000000000000) {
                        $temp = penyebut($nilai/1000000000000)." triliun". penyebut(fmod($nilai,1000000000000));
                    }     
                    return $temp;
                }

                function terbilang($nilai) {
                    if($nilai < 0) {
                        $hasil = "minus ". trim(penyebut($nilai));
                    } else {
                        $hasil = trim(penyebut($nilai));
                    }     
                    return $hasil ? ucwords($hasil) . " Rupiah" : "Nol Rupiah";
                }
            @endphp

            @php
                $items = is_string($invoice->items) ? json_decode($invoice->items, true) : ($invoice->items ?? []);
            @endphp

            @foreach($items as $index => $item)
                @php 
                    $price = $item['price'] ?? 0;
                    $grandTotal += $price;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item['description'] ?? '-' }}</td>
                    <td class="text-center">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="text-center">Rp {{ number_format($price, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            <!-- Grand Total Row -->
            <tr>
                <td colspan="2" style="border-top: 1px solid #111; border-bottom: none;"></td>
                <td style="font-weight: bold;">Grand Total</td>
                <td class="text-right" style="font-weight: bold;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
            <!-- Terbilang Row -->
            <tr>
                <td colspan="4" style="font-style: italic;" class="text-center">
                    Terbilang : {{ terbilang($grandTotal) }}
                </td>
            </tr>
            <!-- Bank Transfer Row -->
            <tr>
                <td class="text-center" colspan="4" style="font-weight: bold;">
                    Bank Transfer : {{ $bank_name }} a.n {{ $account_name }}, No. Rek : {{ $account_number }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Area -->
    <div class="signature-section">
        <div>Hormat Kami,</div>
        <div style="font-size: 11px; margin-top: 2px;">PT. Ekosistem Media Digital Terintegrasi</div>
        <div class="signature-space"></div>
        <div style="font-weight: bold; text-decoration: underline;">Zhaira Monika Shofa</div>
        <div style="font-size: 12px;">Direktur</div>
    </div>

</body>
</html>