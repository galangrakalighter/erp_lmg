@extends('layout.app')
@section('content')

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-1">{{ $title }}</h1>
            <p class="text-muted small mb-0">Kalkulasi real-time seluruh saldo berjalan akun per periode akuntansi.</p>
        </div>
        
        <div class="mt-3 mt-md-0 d-flex flex-wrap align-items-center gap-2">
            <form action="{{ url()->current() }}" method="GET" class="form-inline bg-white p-2 rounded shadow-sm border mr-2">
                <label for="periode" class="mr-2 small font-weight-bold text-muted">PERIODE:</label>
                <input type="month" name="periode" id="periode" class="form-control form-control-sm border-0 bg-light font-weight-bold text-dark" 
                       value="{{ $periode }}" onchange="this.form.submit()">
            </form>

            <a href="{{ route('Ledger.export_neraca_saldo', ['periode' => $periode]) }}" class="btn btn-sm btn-success shadow-sm font-weight-bold px-3 py-2">
                <i class="fas fa-file-excel mr-2"></i> Export Excel
            </a>
        </div>
    </div>

    @if(round($grandTotalDebit, 2) === round($grandTotalKredit, 2))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-check-circle mr-3 fa-lg"></i>
            <div>
                <strong>Status Pembukuan: SEIMBANG (Balanced)!</strong> Total nilai aset, kewajiban, dan modal telah cocok sempurna.
            </div>
        </div>
    @else
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle mr-3 fa-lg"></i>
            <div>
                <strong>Status Pembukuan: ADA SELISIH (Unbalanced)!</strong> Terjadi ketidakcocokan antara posisi Debit dan Kredit. Periksa kembali entri transaksi pada Jurnal Umum Anda.
            </div>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="bg-gray-100 text-gray-700 text-xs font-weight-bold text-uppercase border-bottom">
                        <tr>
                            <th class="px-4 py-3" style="width: 15%;">Kode Akun</th>
                            <th class="py-3">Nama Akun / COA</th>
                            <th class="py-3" style="width: 20%;">Kategori</th>
                            <th class="text-right py-3" style="width: 20%;">Saldo Akhir (Debit)</th>
                            <th class="text-right px-4 py-3" style="width: 20%;">Saldo Akhir (Kredit)</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-4 py-3 font-mono font-weight-bold text-gray-900">{{ $row['kode'] }}</td>
                            <td class="py-3 text-gray-800 font-weight-bold">{{ $row['nama_akun'] }}</td>
                            <td class="py-3 text-muted text-capitalize">{{ $row['category'] }}</td>
                            
                            <td class="text-right py-3 font-mono text-gray-900">
                                {{ $row['saldo_debit'] > 0 ? "Rp. " . number_format($row['saldo_debit'], 0, ',', '.') : '' }}
                            </td>
                            
                            <td class="text-right px-4 py-3 font-mono text-gray-900">
                                {{ $row['saldo_kredit'] > 0 ? "Rp. " . number_format($row['saldo_kredit'], 0, ',', '.') : '' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Tidak ada riwayat akun atau transaksi aktif di periode ini.</td>
                        </tr>
                        @endforelse 
                    </tbody>
                    
                    <tfoot class="bg-dark text-white font-weight-bold border-top">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-uppercase text-xs tracking-wider">
                                Total Neraca Keseluruhan:
                            </td>
                            <td class="text-right py-3 font-mono text-warning">
                                Rp. {{ number_format($grandTotalDebit, 0, ',', '.') }}
                            </td>
                            <td class="text-right px-4 py-3 font-mono text-warning">
                                Rp. {{ number_format($grandTotalKredit, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

@endsection