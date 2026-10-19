<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Laporan Arus Kas</title>
<style>@page{margin:32px 42px}body{font-family:DejaVu Sans,sans-serif;color:#111827;font-size:10px}.header{text-align:center;margin-bottom:20px}.header h1{margin:0 0 5px;font-size:15px;text-transform:uppercase}.header h2{margin:0 0 4px;font-size:13px}.header p{margin:0;font-size:11px;font-weight:bold;text-transform:uppercase}table{width:100%;border-collapse:collapse}th,td{border:1px solid #d1d5db;padding:7px 8px}th{background:#9dc3e6;border-color:#374151;text-align:left}.section td{background:#eef4f9;font-weight:bold;color:#1f2937}.amount{text-align:right;white-space:nowrap}.subtotal td{font-weight:bold;background:#f9fafb}.total td{font-weight:bold;color:#fff;background:#315b7d;border-color:#315b7d}</style>
</head><body>
<div class="header"><h1>{{ $namaPerusahaan }}</h1><h2>LAPORAN ARUS KAS</h2><p>{{ \Carbon\Carbon::parse($periode . '-01')->locale('id')->translatedFormat('F Y') }} (dalam Rp)</p></div>
<table><thead><tr><th style="width:15%">Kode</th><th>Nama Akun</th><th style="width:20%;text-align:right">Kas Masuk</th><th style="width:20%;text-align:right">Kas Keluar</th></tr></thead><tbody>
@foreach(['Operasional'=>$operasional,'Investasi'=>$investasi,'Pendanaan'=>$pendanaan] as $label=>$items)
<tr class="section"><td colspan="4">{{ $loop->iteration }}. Aktivitas {{ $label }}</td></tr>
@forelse($items as $item)<tr><td>{{ $item['kode'] }}</td><td>{{ $item['nama_akun'] }}</td><td class="amount">{{ $item['jumlah'] > 0 ? number_format($item['jumlah'],0,',','.') : '-' }}</td><td class="amount">{{ $item['jumlah'] < 0 ? number_format(abs($item['jumlah']),0,',','.') : '-' }}</td></tr>@empty<tr><td colspan="4" style="text-align:center;color:#6b7280">Tidak ada transaksi.</td></tr>@endforelse
<tr class="subtotal"><td colspan="3">Total {{ $label }}</td><td class="amount">{{ number_format($label === 'Operasional' ? $totalOperasional : ($label === 'Investasi' ? $totalInvestasi : $totalPendanaan),0,',','.') }}</td></tr>
@endforeach
<tr class="total"><td colspan="3">Kenaikan / (Penurunan) Bersih Kas</td><td class="amount">{{ number_format($totalSaldoAkhir,0,',','.') }}</td></tr>
</tbody></table></body></html>
