<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Laporan Laba Rugi</title>
<style>@page{margin:32px 42px}body{font-family:DejaVu Sans,sans-serif;color:#111827;font-size:10px}.header{text-align:center;margin-bottom:20px}.header h1{margin:0 0 5px;font-size:15px;text-transform:uppercase}.header h2{margin:0 0 4px;font-size:13px}.header p{margin:0;font-size:11px;font-weight:bold;text-transform:uppercase}table{width:100%;border-collapse:collapse}th,td{border:1px solid #d1d5db;padding:7px 8px}th{background:#9dc3e6;border-color:#374151;text-align:left}.amount{text-align:right;white-space:nowrap}.total td{font-weight:bold;background:#eef4f9;border-top:2px solid #374151}.result td{font-weight:bold;color:#fff;background:#315b7d;border-color:#315b7d}</style>
</head><body>
<div class="header"><h1>{{ $namaPerusahaan }}</h1><h2>LAPORAN LABA RUGI</h2><p>{{ \Carbon\Carbon::parse($periode . '-01')->locale('id')->translatedFormat('F Y') }} (dalam Rp)</p></div>
<table><thead><tr><th style="width:15%">Kode Akun</th><th>Nama Akun</th><th style="width:20%;text-align:right">Debit</th><th style="width:20%;text-align:right">Kredit</th></tr></thead><tbody>
@forelse($data as $row)<tr><td>{{ $row['kode'] }}</td><td>{{ $row['nama_akun'] }}</td><td class="amount">{{ $row['debit'] ? number_format($row['debit'],0,',','.') : '-' }}</td><td class="amount">{{ $row['kredit'] ? number_format($row['kredit'],0,',','.') : '-' }}</td></tr>@empty<tr><td colspan="4" style="text-align:center">Tidak ada data.</td></tr>@endforelse
<tr class="total"><td colspan="2">Jumlah</td><td class="amount">{{ number_format($totals['lr_debit'],0,',','.') }}</td><td class="amount">{{ number_format($totals['lr_kredit'],0,',','.') }}</td></tr>
<tr class="result"><td colspan="3">{{ $lr_balance >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}</td><td class="amount">{{ number_format(abs($lr_balance),0,',','.') }}</td></tr>
</tbody></table></body></html>
