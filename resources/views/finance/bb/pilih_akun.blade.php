@include('partials.header')

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

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">{{ $title }}</h1>
            <p class="text-muted small mb-0 font-italic">Silahkan pilih salah satu akun di bawah untuk melihat mutasi Buku Besar secara real-time.</p>
        </div>
        
        <div class="mt-3 mt-md-0">
            <form action="{{ url()->current() }}" method="GET" class="form-inline bg-white p-2 rounded shadow-sm border">
                <label for="periode" class="mr-2 small font-weight-bold text-muted text-uppercase">Periode Buku:</label>
                <input type="month" name="periode" id="periode" class="form-control form-control-sm border-0 bg-light font-weight-bold text-gray-800" 
                       value="{{ $periode }}" onchange="this.form.submit()">
            </form>
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
</div>

@include('partials.footer')