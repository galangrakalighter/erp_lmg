@extends('layout.app')

@section('content')
<div class="container-fluid mt-4">

    <!-- Header & Filter Periode -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Dashboard Keuangan Owner</h1>
        
        <form action="{{ route('finance.dashboard') }}" method="GET" class="form-inline">
            <label for="periode" class="mr-2 font-weight-bold text-secondary small">Filter Periode:</label>
            <input type="month" name="periode" id="periode" class="form-control form-control-sm shadow-sm" 
                   value="{{ $periode }}" onchange="this.form.submit()">
        </form>
    </div>

    <!-- Ringkasan Utama (Cards) -->
    <div class="row">

        <!-- Card 1: Total Profit -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-{{ $totalProfit >= 0 ? 'success' : 'danger' }} shadow-sm h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $totalProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                Profit Bersih ({{ date('F Y', strtotime($periode)) }})
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalProfit, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-muted small">
                                <span class="text-success"><i class="fas fa-arrow-up mr-1"></i>Pendapatan: {{ number_format($totalPendapatan, 0, ',', '.') }}</span> | 
                                <span class="text-danger"><i class="fas fa-arrow-down mr-1"></i>Beban: {{ number_format($totalBeban, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Net Cashflow -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Net Cashflow (Arus Kas Bersih)
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($netCashflow, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-muted small">
                                <span class="text-primary"><i class="fas fa-plus-circle mr-1"></i>Masuk: {{ number_format($cashflowIn, 0, ',', '.') }}</span> | 
                                <span class="text-warning"><i class="fas fa-minus-circle mr-1"></i>Keluar: {{ number_format($cashflowOut, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Saldo Utama (Akun Kategori Aset) -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100 py-2 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Saldo Aset (Kas & Bank)
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                Rp {{ number_format($totalSaldoUtama, 0, ',', '.') }}
                            </div>
                            <div class="mt-2 text-muted small">
                                Akumulasi saldo kategori aset terkini
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Detail Saldo Akun Kategori Aset -->
    <div class="row">
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list-ul mr-2"></i>Rincian Saldo Akun Aset (Kas / Bank / Lainnya)
                    </h6>
                    <span class="badge bg-primary text-white p-2">Total Akun: {{ $mainBalances->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Akun</th>
                                    <th>Nama Akun</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Saldo Terkini</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mainBalances as $acc)
                                <tr>
                                    <td><code>{{ $acc->kode }}</code></td>
                                    <td class="font-weight-bold text-dark">{{ $acc->nama_akun }}</td>
                                    <td><span class="badge bg-info text-white text-uppercase">{{ $acc->type }}</span></td>
                                    <td class="text-end font-weight-bold text-success">
                                        Rp {{ number_format($acc->balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada data akun aset.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-secondary font-weight-bold">
                                <tr>
                                    <th colspan="3" class="text-center">TOTAL KESELURUHAN ASET</th>
                                    <th class="text-end text-dark">Rp {{ number_format($totalSaldoUtama, 0, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Informasi / Pintasan Quick Links -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle mr-2"></i>Informasi & Akses Cepat
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Halaman ini dikhususkan bagi Owner untuk memantau indikator kesehatan finansial secara langsung berdasarkan jurnal umum perusahaan.
                    </p>
                    <hr>
                    <a href="{{ route('Ledger.jurnal_penutup') }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                        <i class="fas fa-file-invoice-dollar mr-2"></i>Lihat Jurnal Penutup
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection