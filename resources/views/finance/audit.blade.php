@extends('layout.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Audit Trail</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Riwayat aktivitas dan perubahan data sistem</li>
    </ol>

    <!-- Form Filter Live Search -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body border-bottom bg-light">
            <form id="filterForm" onsubmit="event.preventDefault();">
                <div class="row">
                    <!-- Cari Deskripsi / Keyword -->
                    <div class="col-md-3 mb-3">
                        <label class="small font-weight-bold text-dark">Cari Deskripsi:</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="filterSearch" name="search" class="form-control border-primary" placeholder="Cari keterangan..." onkeyup="fetchAudits()">
                            <div class="input-group-append">
                                <span class="input-group-text bg-primary border-primary text-white">
                                    <i class="fas fa-search fa-sm"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Modul -->
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Filter Modul:</label>
                        <select id="filterModule" name="module" class="form-control form-control-sm border-primary" onchange="fetchAudits()">
                            <option value="">- Semua Modul -</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}">{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Aksi -->
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Filter Aksi:</label>
                        <select id="filterAction" name="action" class="form-control form-control-sm border-primary" onchange="fetchAudits()">
                            <option value="">- Semua Aksi -</option>
                            <option value="CREATED">CREATED</option>
                            <option value="UPDATED">UPDATED</option>
                            <option value="DELETED">DELETED</option>
                        </select>
                    </div>

                    <!-- Dari Tanggal -->
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Dari Tanggal:</label>
                        <input type="date" id="filterStartDate" name="start_date" class="form-control form-control-sm border-primary" onchange="fetchAudits()">
                    </div>

                    <!-- Sampai Tanggal -->
                    <div class="col-md-2 mb-3">
                        <label class="small font-weight-bold text-dark">Sampai Tanggal:</label>
                        <input type="date" id="filterEndDate" name="end_date" class="form-control form-control-sm border-primary" onchange="fetchAudits()">
                    </div>

                    <!-- Tombol Reset -->
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="button" onclick="resetFilters()" class="btn btn-sm btn-secondary px-2 shadow-sm w-100" title="Reset">
                            <i class="fas fa-undo fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Data & Hasil AJAX -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white py-3">
            <i class="fas fa-table me-1"></i> <strong>Daftar Log Aktivitas Sistem</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna (User)</th>
                            <th>Aksi</th>
                            <th>Modul</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody id="auditTableBody">
                        <!-- Data akan dimuat melalui JavaScript / AJAX -->
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Informasi & Pagination AJAX -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small" id="paginationInfo">
                    Menampilkan data 0 sampai 0 dari total 0 data
                </div>
                <div id="paginationLinks">
                    <!-- Link pagination akan dirender dinamis via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Universal -->
<div class="modal fade text-start" id="universalDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Detail Audit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p class="mb-1"><strong>Aksi:</strong> <span id="modalActionBadge" class="badge bg-secondary">-</span></p>
                    <p class="mb-1"><strong>Deskripsi:</strong> <span id="modalDescription">-</span></p>
                    <p class="mb-0"><strong>Waktu:</strong> <span id="modalTime">-</span> | <strong>Oleh:</strong> <span id="modalUser">-</span></p>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h6 class="text-danger fw-bold">Data Lama (Old Values):</h6>
                        <pre class="bg-dark text-light p-3 border rounded" style="max-height: 350px; overflow-y: auto;"><code id="modalOldValues">-</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-success fw-bold">Data Baru / Perubahan (New Values):</h6>
                        <pre class="bg-dark text-light p-3 border rounded" style="max-height: 350px; overflow-y: auto;"><code id="modalNewValues">-</code></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk Live Search & Render Modal -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetchAudits();
    });

    let searchTimer;

    function fetchAudits(page = 1) {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            let search = document.getElementById('filterSearch').value;
            let module = document.getElementById('filterModule').value;
            let action = document.getElementById('filterAction').value;
            let startDate = document.getElementById('filterStartDate').value;
            let endDate = document.getElementById('filterEndDate').value;

            let params = new URLSearchParams({
                page: page,
                search: search,
                module: module,
                action: action,
                start_date: startDate,
                end_date: endDate
            });

            fetch(`{{ route('api.audit.index') }}?${params.toString()}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        renderTable(res.data);
                    }
                })
                .catch(error => console.error('Error fetching audits:', error));
        }, 300); // Delay kecil agar tidak terlalu sering memanggil API saat mengetik
    }

    function renderTable(paginator) {
        let tbody = document.getElementById('auditTableBody');
        tbody.innerHTML = '';

        if (paginator.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data audit log yang tercatat.</td></tr>`;
            document.getElementById('paginationInfo').innerText = `Menampilkan data 0 sampai 0 dari total 0 data`;
            document.getElementById('paginationLinks').innerHTML = '';
            return;
        }

        paginator.data.forEach(audit => {
            let badgeClass = 'bg-secondary';
            if (audit.action === 'CREATED') badgeClass = 'bg-success';
            else if (audit.action === 'UPDATED') badgeClass = 'bg-warning text-dark';
            else if (audit.action === 'DELETED') badgeClass = 'bg-danger';

            let userName = audit.user ? audit.user.name : 'System / Guest';
            let ipAddress = audit.ip_address ?? '-';
            let createdAt = audit.created_at ? audit.created_at.replace('T', ' ').substring(0, 19) : '-';

            let row = `
                <tr>
                    <td>${createdAt}</td>
                    <td>
                        <strong>${userName}</strong>
                        <br><small class="text-muted">IP: ${ipAddress}</small>
                    </td>
                    <td><span class="badge ${badgeClass}">${audit.action}</span></td>
                    <td>${audit.module ?? '-'}</td>
                    <td>${audit.description ?? '-'}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Update Text Info Pagination
        document.getElementById('paginationInfo').innerText = 
            `Menampilkan data ke-${paginator.from ?? 0} sampai ${paginator.to ?? 0} dari total ${paginator.total} data`;

        // Render Pagination Links
        let paginationHtml = `<ul class="pagination pagination-sm mb-0">`;
        paginator.links.forEach(link => {
            let activeClass = link.active ? 'active' : '';
            let disabledClass = !link.url ? 'disabled' : '';
            let pageNum = link.label.includes('&laquo;') ? '&laquo;' : (link.label.includes('&raquo;') ? '&raquo;' : link.label);
            
            if (link.url) {
                let urlParams = new URLSearchParams(link.url.split('?')[1]);
                let pageNumber = urlParams.get('page');
                paginationHtml += `<li class="page-item ${activeClass} ${disabledClass}"><a class="page-link" href="javascript:void(0)" onclick="fetchAudits(${pageNumber})">${pageNum}</a></li>`;
            } else {
                paginationHtml += `<li class="page-item ${disabledClass}"><span class="page-link">${pageNum}</span></li>`;
            }
        });
        paginationHtml += `</ul>`;
        document.getElementById('paginationLinks').innerHTML = paginationHtml;
    }

    function resetFilters() {
        document.getElementById('filterForm').reset();
        fetchAudits();
    }
</script>
@endsection