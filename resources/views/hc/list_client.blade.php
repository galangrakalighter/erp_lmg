@include('partials.header')

<style>
    /* Efek hover agar card terlihat interaktif */
    .client-card {
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        cursor: pointer;
        text-decoration: none !important;
    }
    .client-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .progress-sm {
        height: 8px;
        border-radius: 5px;
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 text-gray-800 font-weight-bold mb-0">{{ $title }}</h1>
            <p class="text-muted small mb-0">Memantau progres seluruh project client secara real-time.</p>
        </div>
        <div class="text-right">
            <span class="badge badge-primary px-3 py-2 shadow-sm">Total: {{ $projects->count() }} Client</span>
        </div>
    </div>

    <div class="row">
        @foreach($projects as $p)
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="{{ route('hc_overview.index', $p['project_id']) }}" class="client-card card shadow-sm border-0 border-left-{{ $p['color'] }} h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div style="flex: 1; min-width: 0;">
                            <h5 class="font-weight-bold text-gray-800 mb-0 text-truncate" title="{{ $p['nama_client'] }}">
                                {{ $p['nama_client'] }}
                            </h5>
                        </div>
                        <div class="text-right ml-2">
                            <h5 class="font-weight-bold text-gray-800 mb-0">{{ $p['percentage'] }}%</h5>
                        </div>
                    </div>

                    @if($p['total'] > 0)
                        <div class="progress progress-sm mb-3">
                            <div class="progress-bar bg-{{ $p['color'] }} progress-bar-striped {{ $p['percentage'] < 100 ? 'progress-bar-animated' : '' }}" 
                                 role="progressbar" 
                                 style="width: {{ $p['percentage'] }}%" 
                                 aria-valuenow="{{ $p['percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>

                        <div class="row no-gutters small text-muted font-italic">
                            <div class="col-6">
                                <i class="fas fa-tasks mr-1"></i> {{ $p['total'] }} Total KPI
                            </div>
                            <div class="col-6 text-right">
                                <i class="fas fa-check-circle mr-1 text-success"></i> {{ $p['done'] }} Selesai
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border-0 py-3 mb-0 text-center small text-muted" style="background-color: #f8f9fc;">
                            <i class="fas fa-folder-open mb-2 d-block fa-2x opacity-5"></i>
                            Belum ada KPI yang ditambahkan.
                        </div>
                    @endif
                </div>

                @if($p['percentage'] == 100 && $p['total'] > 0)
                <div class="card-footer bg-success py-1 text-center border-0">
                    <small class="text-white font-weight-bold text-uppercase">Project Completed</small>
                </div>
                @endif
            </a>
        </div>
        @endforeach
    </div>
</div>

@include('partials.footer')