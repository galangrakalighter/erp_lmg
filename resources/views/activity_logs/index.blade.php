@extends('layout.app')

@section('content')
<style>
    .activity-page { --activity-primary: #4f46e5; --activity-ink: #18212f; }
    .activity-hero { background: linear-gradient(135deg, #312e81 0%, #4f46e5 55%, #7c3aed 100%); border-radius: 18px; color: #fff; padding: 1.4rem 1.6rem; box-shadow: 0 14px 35px rgba(79,70,229,.18); }
    .activity-hero .admin-pill { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); border-radius: 999px; padding: .45rem .8rem; }
    .activity-stat { background: #fff; border: 1px solid #edf0f6; border-radius: 14px; padding: 1rem 1.1rem; box-shadow: 0 7px 20px rgba(31,41,55,.05); height: 100%; }
    .activity-stat-icon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; }
    .activity-panel { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(31,41,55,.07); }
    .activity-filters { background: linear-gradient(180deg,#fff,#fafbff); padding: 1.15rem; border-bottom: 1px solid #eef0f5; }
    .activity-filters .form-control, .activity-filters .input-group-text { border-color: #e2e6ef; min-height: 38px; }
    .activity-table thead th { border: 0; background: #f7f8fc; color: #64748b; font-size: .72rem; letter-spacing: .055em; text-transform: uppercase; padding: .9rem 1rem; }
    .activity-table tbody td { border-color: #f0f2f7; padding: 1rem; vertical-align: top; color: var(--activity-ink); }
    .activity-table tbody tr:hover { background: #fafaff; }
    .activity-avatar { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 12px; display: grid; place-items: center; color: #4338ca; background: #eef2ff; font-weight: 800; }
    .activity-badge { border-radius: 999px; padding: .38rem .65rem; font-size: .68rem; letter-spacing: .04em; }
    .activity-module { border-radius: 7px; padding: .25rem .45rem; color: #475569; background: #f1f5f9; font-size: .75rem; }
    .activity-detail { background: #f8fafc; border-left: 3px solid #c7d2fe; border-radius: 7px; padding: .65rem .75rem; }
    .activity-pagination .page-link { border: 0; border-radius: 9px !important; margin: 0 .15rem; color: #475569; min-width: 36px; text-align: center; }
    .activity-pagination .active .page-link { background: var(--activity-primary); box-shadow: 0 5px 12px rgba(79,70,229,.25); }
    .activity-page { transition: opacity .18s ease, transform .18s ease; }
    .activity-page.is-loading { opacity: .52; transform: translateY(2px); pointer-events: none; }
    @media (max-width: 767px) { .activity-hero { padding: 1.1rem; } .activity-table tbody td { padding: .75rem; } }
</style>

<div class="container-fluid px-4 activity-page" id="activityDashboardContent">
    <div class="activity-hero d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <div class="small text-uppercase mb-1" style="opacity:.72; letter-spacing:.12em">Monitoring sistem</div>
            <h1 class="h3 mb-1 text-white">Activity Log</h1>
            <p class="mb-0" style="opacity:.78">Pantau riwayat aktivitas seluruh akun dalam satu tempat.</p>
        </div>
        <span class="admin-pill mt-3 mt-sm-0">
            <i class="fas fa-shield-alt mr-1"></i> Khusus Admin
        </span>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-xl-3 mb-3 mb-xl-0">
            <div class="activity-stat d-flex align-items-center">
                <div class="activity-stat-icon mr-3" style="background:#eef2ff;color:#4f46e5"><i class="fas fa-list-ul"></i></div>
                <div><div class="small text-muted">Total Log</div><div class="h5 font-weight-bold mb-0">{{ number_format($total) }}</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-3 mb-xl-0">
            <div class="activity-stat d-flex align-items-center">
                <div class="activity-stat-icon mr-3" style="background:#ecfdf5;color:#059669"><i class="fas fa-edit"></i></div>
                <div><div class="small text-muted">Perubahan Data</div><div class="h5 font-weight-bold mb-0">{{ number_format(($actionCounts['CREATE'] ?? 0) + ($actionCounts['UPDATE'] ?? 0) + ($actionCounts['DELETE'] ?? 0)) }}</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="activity-stat d-flex align-items-center">
                <div class="activity-stat-icon mr-3" style="background:#eff6ff;color:#0284c7"><i class="fas fa-eye"></i></div>
                <div><div class="small text-muted">Kunjungan</div><div class="h5 font-weight-bold mb-0">{{ number_format($actionCounts['VISIT'] ?? 0) }}</div></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="activity-stat d-flex align-items-center">
                <div class="activity-stat-icon mr-3" style="background:#fff7ed;color:#ea580c"><i class="fas fa-users"></i></div>
                <div><div class="small text-muted">Akun Terdeteksi</div><div class="h5 font-weight-bold mb-0">{{ number_format($uniqueUsers) }}</div></div>
            </div>
        </div>
    </div>

    @if($error)
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}
        </div>
    @endif

    <div class="card activity-panel mb-4">
        <div class="activity-filters">
            <div class="form-row mb-3">
                <div class="col-lg-7 mb-2 mb-lg-0">
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
                <div class="col-lg-2 d-flex align-items-end">
                    <button id="activityReset" type="button" class="btn btn-sm btn-outline-secondary btn-block">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>

            <form method="GET" action="{{ route('activity-logs.index') }}" id="activityServerFilters">
                <div class="form-row">
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <label for="activityEmail" class="small font-weight-bold">Email pengguna</label>
                        <select id="activityEmail" name="email" class="form-control form-control-sm">
                            <option value="">Semua email</option>
                            @foreach($emails as $email)
                                <option value="{{ $email }}" @selected($selectedEmail === $email)>{{ $email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <label for="activityDivision" class="small font-weight-bold">Divisi</label>
                        <select id="activityDivision" name="division" class="form-control form-control-sm">
                            <option value="">Semua divisi</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" @selected($selectedDivision === (string) $division->id)>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-2 mb-lg-0">
                        <label for="activityStartDate" class="small font-weight-bold">Dari tanggal</label>
                        <input id="activityStartDate" name="start_date" type="date" value="{{ $startDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-lg-3">
                        <label for="activityEndDate" class="small font-weight-bold">Sampai tanggal</label>
                        <input id="activityEndDate" name="end_date" type="date" value="{{ $endDate }}" class="form-control form-control-sm">
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table activity-table mb-0" id="activityTable">
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
                                    <div class="d-flex align-items-center">
                                        <div class="activity-avatar mr-2">{{ strtoupper(substr($log['user_name'] ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <div class="font-weight-bold">{{ $log['user_name'] ?? '-' }}</div>
                                            <div class="small text-muted">{{ $log['email'] ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge activity-badge badge-{{ $badge }}">{{ $action }}</span></td>
                                <td><span class="activity-module">{{ str_replace('_', ' ', $log['module'] ?? '-') }}</span></td>
                                <td>
                                    <div>{{ $log['description'] ?? '-' }}</div>
                                </td>
                                <td style="min-width: 300px">
                                    @if($log['human_detail'])
                                        <div class="small activity-detail">
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

        <div class="card-footer bg-white d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
            <div class="small text-muted mb-2 mb-md-0">
                Menampilkan <strong>{{ $from }}–{{ $to }}</strong> dari <strong>{{ number_format($total) }}</strong> log
            </div>
            @if($lastPage > 1)
                @php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($lastPage, $currentPage + 2);
                    $pageQuery = fn($page) => array_filter([
                        'page' => $page,
                        'email' => $selectedEmail ?: null,
                        'division' => $selectedDivision ?: null,
                        'start_date' => $startDate ?: null,
                        'end_date' => $endDate ?: null,
                    ]);
                @endphp
                <nav aria-label="Pagination activity log">
                    <ul class="pagination pagination-sm activity-pagination mb-0">
                        <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $currentPage > 1 ? route('activity-logs.index', $pageQuery($currentPage - 1)) : '#' }}" aria-label="Sebelumnya">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        @if($startPage > 1)
                            <li class="page-item"><a class="page-link" href="{{ route('activity-logs.index', $pageQuery(1)) }}">1</a></li>
                            @if($startPage > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                        @endif
                        @for($page = $startPage; $page <= $endPage; $page++)
                            <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ route('activity-logs.index', $pageQuery($page)) }}">{{ $page }}</a>
                            </li>
                        @endfor
                        @if($endPage < $lastPage)
                            @if($endPage < $lastPage - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                            <li class="page-item"><a class="page-link" href="{{ route('activity-logs.index', $pageQuery($lastPage)) }}">{{ $lastPage }}</a></li>
                        @endif
                        <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $currentPage < $lastPage ? route('activity-logs.index', $pageQuery($currentPage + 1)) : '#' }}" aria-label="Berikutnya">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function applyFilter() {
        const search = document.getElementById('activitySearch');
        const action = document.getElementById('activityAction');
        const rows = Array.from(document.querySelectorAll('[data-activity-row]'));
        const noResult = document.getElementById('activityNoResult');

        if (!search || !action || !noResult) return;

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

    async function loadActivityPage(url, addHistory = true) {
        const container = document.getElementById('activityDashboardContent');
        if (!container) return;

        container.classList.add('is-loading');

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Gagal memuat activity log.');

            const html = await response.text();
            const documentResult = new DOMParser().parseFromString(html, 'text/html');
            const freshContent = documentResult.getElementById('activityDashboardContent');

            if (!freshContent) throw new Error('Konten activity log tidak ditemukan.');

            container.innerHTML = freshContent.innerHTML;
            container.classList.remove('is-loading');

            if (addHistory) window.history.pushState({ activityLog: true }, '', url);
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            container.classList.remove('is-loading');
            window.location.href = url;
        }
    }

    document.addEventListener('input', function (event) {
        if (event.target.matches('#activitySearch')) applyFilter();
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('#activityAction')) {
            applyFilter();
            return;
        }

        if (event.target.matches('#activityEmail, #activityDivision, #activityStartDate, #activityEndDate')) {
            const form = document.getElementById('activityServerFilters');
            const url = new URL(@json(route('activity-logs.index')), window.location.origin);

            new FormData(form).forEach(function (value, key) {
                if (value) url.searchParams.set(key, value);
            });

            loadActivityPage(url.toString());
        }
    });

    document.addEventListener('submit', function (event) {
        if (!event.target.matches('#activityServerFilters')) return;
        event.preventDefault();
    });

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('.activity-pagination a.page-link');
        if (paginationLink) {
            event.preventDefault();
            if (paginationLink.closest('.disabled')) return;
            loadActivityPage(paginationLink.href);
            return;
        }

        const reset = event.target.closest('#activityReset');
        if (!reset) return;

        const serverFilters = document.querySelectorAll('#activityServerFilters select, #activityServerFilters input');
        const hasServerFilter = Array.from(serverFilters).some(function (field) { return field.value; });
        if (hasServerFilter) {
            loadActivityPage(@json(route('activity-logs.index')));
            return;
        }

        const search = document.getElementById('activitySearch');
        const action = document.getElementById('activityAction');

        search.value = '';
        action.value = '';
        applyFilter();
    });

    window.addEventListener('popstate', function () {
        loadActivityPage(window.location.href, false);
    });
});
</script>
@endsection
