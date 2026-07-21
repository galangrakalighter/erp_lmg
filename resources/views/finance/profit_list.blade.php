@extends('layout.app')

@section('content')
<div class="row" id="project-container">
    @foreach($clients as $c)
    <div class="col-xl-4 col-md-6 mb-4 project-card">
        <a href="{{ route('profit.detail', $c->id) }}" class="client-card card shadow-sm border-0 border-left-{{ $c->balance > 0 ? 'warning' : 'success' }} h-100 text-decoration-none">
            <div class="card-body">
                <!-- Nama Client -->
                <h5 class="font-weight-bold text-gray-800 mb-3 text-truncate" title="{{ $c->nama }}">
                    {{ $c->nama }}
                </h5>

                <!-- Rincian Keuangan -->
                <div class="small text-muted mb-1">
                    Total Tagihan: <span class="float-right font-weight-bold text-dark">Rp {{ number_format($c->total_invoice ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="small text-muted mb-2">
                    Total Dibayar: <span class="float-right font-weight-bold text-success">Rp {{ number_format($c->total_bayar ?? 0, 0, ',', '.') }}</span>
                </div>
                
                <hr class="my-2">

                <!-- Status Plus / Minus (Balance) -->
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small font-weight-bold text-secondary">Selisih (Sisa):</span>
                    <span class="font-weight-bold {{ $c->balance > 0 ? 'text-danger' : 'text-success' }}">
                        Rp {{ number_format($c->balance, 0, ',', '.') }}
                        {!! $c->balance > 0 ? '<i class="fas fa-arrow-up small ml-1"></i>' : '<i class="fas fa-arrow-down small ml-1"></i>' !!}
                    </span>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection