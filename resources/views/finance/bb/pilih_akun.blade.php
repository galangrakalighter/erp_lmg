@extends('layout.app')
@section('content')
<style>
    /* Efek hover interaktif agar card terasa premium */
    .account-card {
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        text-decoration: none !important;
    }
    .account-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .badge-fixed-width {
        min-width: 60px;
        text-align: center;
    }
</style>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">{{ $title }}</h1>
            <p class="text-muted small mb-0 font-italic">Silahkan pilih salah satu akun di bawah untuk melihat mutasi Buku Besar secara real-time.</p>
        </div>
        
        <div class="mt-3 mt-md-0 d-flex align-items-center">
            <!-- Form Periode -->
            <form action="{{ url()->current() }}" method="GET" class="form-inline bg-white p-2 rounded shadow-sm border mr-2">
                <!-- Jika ada filter akun yang sedang aktif, sertakan juga sebagai hidden input agar tidak hilang saat ganti bulan -->
                @if(request('chart_of_account_id'))
                    <input type="hidden" name="chart_of_account_id" value="{{ request('chart_of_account_id') }}">
                @endif

                <label for="periode" class="mr-2 small font-weight-bold text-muted text-uppercase">Periode Buku:</label>
                <input type="month" name="periode" id="periode" class="form-control form-control-sm border-0 bg-light font-weight-bold text-gray-800" 
                    value="{{ $periode }}" onchange="this.form.submit()">
            </form>

            <!-- Tombol Export PDF Buku Besar -->
            <button type="button" class="btn btn-danger btn-sm shadow-sm px-3 py-2" onclick="exportLedgerPdf()">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </button>
        </div>
    </div>

    <div class="row">
        @foreach($accounts as $account)
        @php
            $isDebit = strtoupper($account->type) === 'D';
            $borderColor = $isDebit ? 'primary' : 'success';
            $badgeColor = $isDebit ? 'badge-primary' : 'badge-success';
        @endphp
        
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('finance_ledger.show', ['finance_ledger' => $account->id, 'periode' => $periode]) }}" 
               class="account-card card shadow-sm border-0 border-left-{{ $borderColor }} h-100">
                
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-xs font-weight-bold text-muted">
                            KODE: <span class="text-gray-900 font-weight-bold">{{ $account->kode }}</span>
                        </div>
                        <span class="badge {{ $badgeColor }} px-2 py-1 text-xs text-uppercase font-weight-bold badge-fixed-width">
                            {{ $isDebit ? 'Debit' : 'Kredit' }}
                        </span>
                    </div>

                    <h6 class="font-weight-bold text-gray-800 mb-2 text-truncate" title="{{ $account->nama_akun }}">
                        {{ $account->nama_akun }}
                    </h6>

                    <hr class="my-2 style-light" style="border-top: 1px dashed #e3e6f0;">

                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="text-muted small">Saldo Saat Ini:</span>
                        <span class="font-weight-bold text-sm text-gray-900 font-mono">
                            Rp. {{ number_format($account->saldo_saat_ini, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <script>
        function exportLedgerPdf() {
            let periode = document.getElementById('periode').value;
            // Ambil akun yang sedang aktif (jika ada elemen select akun di halaman Anda)
            let accountId = document.getElementById('chart_of_account_id') ? document.getElementById('chart_of_account_id').value : '';

            let params = new URLSearchParams({
                periode: periode,
                chart_of_account_id: accountId
            });

            window.open(`{{ route('ledger.export-pdf') }}?${params.toString()}`, '_blank');
        }
    </script>

@endsection