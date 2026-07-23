<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice_number }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 14px; color: #1F2937; line-height: 1.5; margin: 0; padding: 20px; }
        .header { width: 100%; border-bottom: 2px solid #2563EB; padding-bottom: 20px; margin-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1F2937; }
        .invoice-title { font-size: 28px; font-weight: bold; color: #2563EB; text-align: right; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .client-info { background: #F3F4F6; padding: 15px; border-radius: 5px; }
        .bill-card { border: 1px solid #E5E7EB; padding: 20px; margin: 20px 0; }
        .amount { font-size: 20px; font-weight: bold; color: #2563EB; }
        .footer { margin-top: 50px; font-size: 12px; text-align: center; color: #6B7280; }
        .signature-area { margin-top: 40px; float: right; width: 200px; text-align: center; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="company-name">Lighter Media Group</div>
                <div>Jl. Contoh Alamat No. 123, Bandung</div>
                <div>Email: hello@lightermedia.com | Telp: 0812-3456-7890</div>
            </td>
            <td class="invoice-title">INVOICE</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <strong>Tagihan Kepada:</strong><br>
                {{ $client_company }}
            </td>
            <td style="width: 40%; vertical-align: top;">
                <strong>Invoice #:</strong> {{ $invoice_number }}<br>
                <strong>Tanggal:</strong> {{ $invoice_date }}<br>
                <strong>Jatuh Tempo:</strong> {{ $due_date }}
            </td>
        </tr>
    </table>

    <div class="bill-card">
        <h3>Deskripsi Layanan</h3>
        <p>Tagihan layanan digital dari Lighter Media Group.</p>
        <hr>
        <p>Total yang harus dibayar:</p>
        <div class="amount">Rp {{ number_format($amount, 0, ',', '.') }}</div>
    </div>

    <div>
        <h3>Informasi Pembayaran</h3>
        <p>
            Nama Bank: <strong>{{ $bank_name }}</strong><br>
            Nama Rekening: <strong>{{ $account_name }}</strong><br>
            Nomor Rekening: <strong>{{ $account_number }}</strong>
        </p>
    </div>

    <p><em>Terima kasih telah mempercayakan kebutuhan digital Anda kepada Lighter Media Group.</em></p>

    <div class="signature-area">
        <p>Hormat Kami,</p>
        <br><br><br>
        <p><strong>Lighter Media Group</strong></p>
    </div>


</body>
</html>