@extends('layout.app')

@section('content')

    <!-- Page Heading & Toggle Switch On/Off -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            {{ $title }}
        </h1>

        <!-- Tombol Switch On / Off -->
        <div class="d-flex align-items-center mb-4">
            <span class="mr-2 font-weight-bold text-xs text-uppercase text-muted">Mode Detail Keseluruhan:</span>
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="viewToggleSwitch" {{ ($viewMode ?? 'off') === 'on' ? 'checked' : '' }} onchange="toggleViewMode(this)">
                <label class="custom-control-label font-weight-bold" for="viewToggleSwitch">
                    <span id="switchLabelText" class="{{ ($viewMode ?? 'off') === 'on' ? 'text-success' : 'text-secondary' }}">
                        {{ ($viewMode ?? 'off') === 'on' ? 'ON' : 'OFF' }}
                    </span>
                </label>
            </div>
        </div>
    </div>

    @if(($viewMode ?? 'off') === 'off')
        <!-- ================= TAMPILAN OFF: KARTU CLIENT SEPERTI SEMULA ================= -->
        <div class="row">
            @forelse($clients as $c)
            <div class="col-xl-4 col-md-6 mb-4">
                <!-- Sesuaikan route detail client Anda di sini -->
                <div class="card shadow-sm border-0 border-left-{{ $c->balance > 0 ? 'warning' : 'success' }} h-100">
                    <div class="card-body">
                        <h5 class="font-weight-bold text-gray-800 mb-3 text-truncate">{{ $c->nama }}</h5>
                        <div class="small text-muted mb-1">
                            Total Tagihan: <span class="float-right font-weight-bold text-dark">Rp {{ number_format($c->total_invoice, 0, ',', '.') }}</span>
                        </div>
                        <div class="small text-muted mb-2">
                            Total Dibayar: <span class="float-right font-weight-bold text-success">Rp {{ number_format($c->total_bayar, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small font-weight-bold text-secondary">Selisih (Sisa):</span>
                            <span class="font-weight-bold {{ $c->balance > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($c->balance, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12"><div class="alert alert-secondary text-center">Belum ada data client.</div></div>
            @endforelse
        </div>

    @else
        <!-- ================= TAMPILAN ON: TABEL KESELURUHAN & CARD SUMMARY ================= -->
        
        <!-- Kartu Ringkasan Global -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-primary shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Piutang Global</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalInvoiceAll, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-success shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Masuk Global</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalBayarAll, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-{{ $balanceAll > 0 ? 'danger' : 'success' }} shadow-sm h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold {{ $balanceAll > 0 ? 'text-danger' : 'text-success' }} text-uppercase mb-1">Total Sisa Piutang Global</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($balanceAll, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Detail Seluruh Client & Invoices -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Rincian Lengkap Invoice & Pembayaran per Client</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>Client / Keterangan Transaksi</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Jumlah (Invoice / Bayar)</th>
                                <th>Sisa Piutang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach($clients as $cli)
                                <tr class="bg-light">
                                    <td colspan="6" class="font-weight-bold text-dark">
                                        <i class="fas fa-building mr-1 text-primary"></i> Client: {{ $cli->nama }}
                                    </td>
                                </tr>
                                @foreach($cli->invoices as $inv)
                                    @php
                                        $paidInv = $inv->payments->sum('amount');
                                        $remInv = $inv->total_amount - $paidInv;
                                    @endphp
                                    <tr class="table-primary font-weight-bold">
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td><i class="fas fa-file-invoice mr-1"></i> INVOICE #{{ $inv->id }}</td>
                                        <td>{{ optional($inv->created_at)->format('d M Y') }}</td>
                                        <td>-</td>
                                        <td class="text-primary">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                        <td class="{{ $remInv > 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($remInv, 0, ',', '.') }}</td>
                                    </tr>
                                    @forelse($inv->payments as $pay)
                                    <tr class="table-light">
                                        <td></td>
                                        <td class="pl-4 text-success"><i class="fas fa-arrow-right mr-1"></i> Pembayaran Masuk</td>
                                        <td>{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td>
                                        <td><span class="badge badge-success">{{ $pay->method }}</span></td>
                                        <td class="text-success font-weight-bold">+ Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                                        <td>-</td>
                                    </tr>
                                    @empty
                                    <tr class="table-light">
                                        <td></td>
                                        <td colspan="5" class="pl-4 text-muted font-italic small">Belum ada pembayaran untuk invoice ini.</td>
                                    </tr>
                                    @endforelse
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Script JavaScript untuk Menangani Switch On/Off secara otomatis -->
    <script>
        function toggleViewMode(checkbox) {
            let url = new URL(window.location.href);
            url.searchParams.set('view', checkbox.checked ? 'on' : 'off');
            window.location.href = url.toString();
        }
    </script>

@endsection