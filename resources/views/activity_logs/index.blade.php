@extends('layout.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Activity Log</h1>
            <p class="mb-0 text-muted">Riwayat aktivitas seluruh akun. Halaman ini bersifat baca-saja.</p>
        </div>
        <span class="badge badge-secondary px-3 py-2">
            <i class="fas fa-lock mr-1"></i> Admin only
        </span>
    </div>

    @if($error)
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}
        </div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body border-bottom bg-light">
            <div class="form-row">
                <div class="col-lg-6 mb-2 mb-lg-0">
                    <label for="activitySearch" class="small font-weight-bold">Cari aktivitas</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input id="activitySearch" type="search" class="form-control" placeholder="Nama, email, modul, atau deskripsi...">
                    </div>
                </div>
                <div class="col-lg-3 mb-2 mb-lg-0">
                    <label for="activityAction" class="small font-weight-bold">Aksi</label>
                    <select id="activityAction" class="form-control form-control-sm">
                        <option value="">Semua aksi</option>
                        @foreach(['VISIT', 'LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE'] as $action)
                            <option value="{{ $action }}">{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex align-items-end">
                    <button id="activityReset" type="button" class="btn btn-sm btn-outline-secondary btn-block">
                        <i class="fas fa-undo mr-1"></i> Reset filter
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="activityTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-nowrap">Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Modul</th>
                            <th>Aktivitas</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $action = strtoupper($log['action'] ?? '-');
                                $badge = match($action) {
                                    'CREATE', 'LOGIN' => 'success',
                                    'UPDATE' => 'warning',
                                    'DELETE', 'LOGOUT' => 'danger',
                                    'VISIT' => 'info',
                                    default => 'secondary',
                                };
                                $createdAt = $log['created_at'] ?? null;
                                try {
                                    $displayTime = $createdAt
                                        ? \Illuminate\Support\Carbon::parse($createdAt)->timezone('Asia/Jakarta')->format('d M Y H:i:s')
                                        : '-';
                                } catch (\Throwable) {
                                    $displayTime = (string) $createdAt;
                                }
                                $searchable = strtolower(implode(' ', [
                                    $log['user_name'] ?? '', $log['email'] ?? '', $log['module'] ?? '',
                                    $log['description'] ?? '', $log['target_name'] ?? '', $action,
                                ]));
                            @endphp
                            <tr data-activity-row data-action="{{ $action }}" data-search="{{ $searchable }}">
                                <td class="small text-nowrap">{{ $displayTime }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $log['user_name'] ?? '-' }}</div>
                                    <div class="small text-muted">{{ $log['email'] ?? '-' }}</div>
                                </td>
                                <td><span class="badge badge-{{ $badge }}">{{ $action }}</span></td>
                                <td><code>{{ $log['module'] ?? '-' }}</code></td>
                                <td>
                                    <div>{{ $log['description'] ?? '-' }}</div>
                                    @if(!empty($log['target_name']))
                                        <div class="small text-muted">Target: {{ $log['target_name'] }}</div>
                                    @endif
                                </td>
                                <td style="min-width: 300px">
                                    @if($log['human_detail'])
                                        <div class="small">
                                            <div class="font-weight-bold mb-1">{{ $log['human_detail']['summary'] }}</div>
                                            @forelse($log['human_detail']['items'] as $item)
                                                <div class="mb-1">
                                                    <span class="text-muted">{{ $item['label'] }}:</span>
                                                    @if(array_key_exists('old', $item))
                                                        @if($item['old_url'])
                                                            <a href="{{ $item['old_url'] }}" target="_blank" rel="noopener noreferrer">{{ $item['old'] }}</a>
                                                        @else
                                                            <span class="text-danger"><s>{{ $item['old'] }}</s></span>
                                                        @endif
                                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                        @if($item['new_url'])
                                                            <a href="{{ $item['new_url'] }}" target="_blank" rel="noopener noreferrer">{{ $item['new'] }}</a>
                                                        @else
                                                            <span class="text-success">{{ $item['new'] }}</span>
                                                        @endif
                                                    @elseif($item['url'])
                                                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer">{{ $item['value'] }}</a>
                                                    @else
                                                        <span>{{ $item['value'] }}</span>
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-muted">Tidak ada rincian atribut.</span>
                                            @endforelse
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="activityEmptyRow">
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                    Belum ada activity log.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="activityNoResult" class="d-none">
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada log yang sesuai dengan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($nextPageToken)
            <div class="card-footer bg-white text-right">
                <a class="btn btn-sm btn-primary" href="{{ route('activity-logs.index', ['page_token' => $nextPageToken]) }}">
                    Log berikutnya <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('activitySearch');
    const action = document.getElementById('activityAction');
    const reset = document.getElementById('activityReset');
    const rows = Array.from(document.querySelectorAll('[data-activity-row]'));
    const noResult = document.getElementById('activityNoResult');

    function applyFilter() {
        const term = search.value.trim().toLowerCase();
        const selectedAction = action.value;
        let visible = 0;

        rows.forEach(function (row) {
            const matchesSearch = !term || row.dataset.search.includes(term);
            const matchesAction = !selectedAction || row.dataset.action === selectedAction;
            const show = matchesSearch && matchesAction;
            row.classList.toggle('d-none', !show);
            visible += show ? 1 : 0;
        });

        noResult.classList.toggle('d-none', visible !== 0 || rows.length === 0);
    }

    search.addEventListener('input', applyFilter);
    action.addEventListener('change', applyFilter);
    reset.addEventListener('click', function () {
        search.value = '';
        action.value = '';
        applyFilter();
    });
});
</script>
@endsection
