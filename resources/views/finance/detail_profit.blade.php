@extends('layout.app')

@section('content')

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}: {{ $client->nama }}</h1>
        <a href="{{ route('profit.list') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Kembali
        </a>
    </div>

    <!-- Ringkasan Kartu Keuangan -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Piutang (Invoice)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalInvoice, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Pembayaran Masuk</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBayar, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-{{ $balance > 0 ? 'danger' : 'success' }} shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }} text-uppercase mb-1">Sisa Piutang (Balance)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($balance, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Piutang & Payment -->
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Rincian Piutang dan Pembayaran</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Keterangan / Transaksi</th>
                            <th>Tanggal</th>
                            <th>Metode</th>
                            <th>Jumlah (Piutang / Masuk)</th>
                            <th>Sisa Piutang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($client->invoices as $index => $inv)
                            @php
                                $paidThisInvoice = $inv->payments->sum('amount');
                                $remainingThisInvoice = $inv->total_amount - $paidThisInvoice;
                            @endphp

                            <!-- BARIS UTAMA: PIUTANG / INVOICE -->
                            <tr class="table-primary font-weight-bold">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <i class="fas fa-file-invoice mr-1 text-primary"></i> 
                                    PIUTANG / INVOICE #{{ $inv->id }}
                                </td>
                                <td>{{ $inv->created_at ? $inv->created_at->format('d M Y') : '-' }}</td>
                                <td>-</td>
                                <td class="text-primary">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                <td class="{{ $remainingThisInvoice > 0 ? 'text-danger' : 'text-success' }}">
                                    Rp {{ number_format($remainingThisInvoice, 0, ',', '.') }}
                                </td>
                            </tr>

                            <!-- BARIS DI BAWAHNYA: PAYMENT / PEMBAYARAN -->
                            @if($inv->payments->count() > 0)
                                @foreach($inv->payments as $pay)
                                <tr class="table-light">
                                    <td></td>
                                    <td class="pl-4">
                                        <span class="text-success font-weight-bold">
                                            <i class="fas fa-arrow-right mr-1"></i> Pembayaran Masuk
                                        </span>
                                        @if($pay->file_path)
                                            <br><small><a href="{{ $pay->file_url }}" target="_blank" class="text-info"><i class="fas fa-paperclip"></i> Lihat Bukti Transfer</a></small>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge badge-success">{{ $pay->method }}</span>
                                    </td>
                                    <td class="text-success font-weight-bold">
                                        + Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                    </td>
                                    <td>-</td>
                                </tr>
                                @endforeach
                            @else
                                <tr class="table-light">
                                    <td></td>
                                    <td colspan="5" class="pl-4 text-muted font-italic small">
                                        <i class="fas fa-info-circle mr-1"></i> Belum ada pembayaran untuk invoice ini.
                                    </td>
                                </tr>
                            @endif

                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data piutang/invoice untuk client ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection