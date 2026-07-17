@extends('layout.app')
@section('content')
<style>
    /* Styling Baru Mengikuti Referensi image_0.png */
    body {
        background: #f7f8fc;
        font-family: 'Segoe UI', sans-serif;
        color: #1b1f38;
    }

    .container-fluid {
        padding: 40px;
    }

    /* Header Dashboard */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start; /* Ubah dari center ke flex-start agar sejajar dengan kalender */
        margin-bottom: 40px;
    }

    .header-text h2 {
        font-weight: 700;
        color: #1b1f38;
        margin: 0 0 8px 0;
        font-size: 32px;
    }

    .header-text p {
        color: #7b8190;
        margin: 0;
        font-size: 16px;
    }

    /* Kalender Card - Dipindah ke sini agar sebaris */
    .date-card-container {
        background: #fff;
        border-radius: 12px;
        padding: 10px 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,.03); /* Bayangan lebih halus */
        border: 1px solid #eaeaea;
        display: flex;
        align-items: center;
        font-size: 14px;
        color: #333;
    }

    .date-card-container i {
        margin-right: 8px;
        color: #555;
    }

    /* Grid Kartu Statistik */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    .stat-card {
        background: #fff;
        border: none;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,.05); /* Bayangan lebih lembut */
        display: flex;
        flex-direction: column;
    }

    /* Tata Letak Ikon dan Teks Baru */
    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 24px; /* Jarak ikon ke judul */
    }

    /* Warna Ikon (Sama dengan sebelumnya) */
    .icon-blue { background: #e8f0fe; color: #0d6efd; }
    .icon-green { background: #e8f5e9; color: #198754; }
    .icon-yellow { background: #fff3cd; color: #ffc107; }
    .icon-red { background: #f8d7da; color: #dc3545; }

    /* Teks Kartu */
    .stat-title {
        color: #6c757d; /* Warna abu-abu judul */
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .stat-number {
        font-size: 48px; /* Ukuran font angka sedikit lebih kecil dari gambar asli agar proporsional */
        font-weight: 700;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-desc {
        color: #adb5bd; /* Warna abu-abu muda deskripsi */
        font-size: 14px;
    }

    /* Utility Classes untuk Warna */
    .text-blue { color: #0d6efd; }
    .text-green { color: #198754; }
    .text-yellow { color: #ffc107; }
    .text-red { color: #dc3545; }

    .custom-select-filter {
        appearance: none; /* Menghilangkan panah default bawaan browser jika ingin kustom */
        background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") right 0.75rem center/16px 10px no-repeat;
        border: 1px solid #e0e6ed;
        border-radius: 10px;
        padding: 8px 36px 8px 14px;
        font-size: 14px;
        color: #495057;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .custom-select-filter:focus {
        border-color: #2962ff;
        box-shadow: 0 0 0 4px rgba(41, 98, 255, 0.1);
        outline: none;
    }

    /* Card Utama */
    .main-completion-card {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        margin-top: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,.05);
        display: flex;
        gap: 2%;
    }

    /* Kiri: Donut Chart */
    .chart-container {
        flex: 0 0 50%; 
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Kanan: List Items */
    .list-container {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center; /* Agar list tetap rapi di tengah secara vertikal */
        gap: 16px;
        border-left: 1px solid #eaeaea;
        padding-left: 30px;
    }

    .kpi-mini-card {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        border-radius: 12px;
        border: 1px solid #f0f0f0;
    }

    .kpi-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }
    .donut-chart {
        width: 220px; /* Diperbesar dari 160px */
        height: 220px; /* Diperbesar dari 160px */
        border-radius: 50%;
        margin: 25px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(closest-side, white 70%, transparent 71% 100%),
                    conic-gradient(#0d6efd var(--percentage), #e9ecef 0);
        position: relative;
    }

    .percentage-text {
        font-size: 36px; /* Diperbesar agar lebih terlihat */
        font-weight: 800;
    }
    .bg-primary-subtle {
        background-color: #e8f0fe;
    }
    .rounded-4 {
        border-radius: 16px !important;
    }
    /* Menyesuaikan jarak antar card jika di mobile */
    @media (max-width: 768px) {
        .row > div {
            margin-bottom: 20px;
        }
    }

    .cursor-pointer { cursor: pointer; transition: 0.3s; }
    .cursor-pointer:hover { transform: scale(1.02); opacity: 0.9; }
    .stat-card.active { border: 2px solid #007bff; }
</style>

    <!-- HEADER BARU: Teks Kiri, Kalender Kanan -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <!-- Header Text -->
        <div>
            <h1 class="h3 mb-0 text-gray-800">Halo, {{ Auth::user()->name }} 👋</h1>
            <p class="text-muted small">Selamat bekerja hari ini</p>
        </div>

        <!-- Filter Section -->
        <div class="d-flex align-items-center flex-wrap gap-2">
            <!-- Filter Project & User -->
            <select id="filterProject" class="form-control form-control-sm mr-2" style="width: auto; min-width: 150px;" onchange="loadStats()">
                <option value="">Semua Project</option>
                @foreach($listProject as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                @endforeach
            </select>

            <select id="filterUser" class="form-control form-control-sm mr-2" style="width: auto; min-width: 150px;" onchange="loadStats()">
                <option value="">Semua Bawahan</option>
                @foreach($listBawahan as $bawahan)
                    <option value="{{ $bawahan->id }}">{{ $bawahan->email }}</option>
                @endforeach
            </select>

            <!-- Date Filters -->
            <div class="input-group input-group-sm mr-2" style="width: auto;">
                <input type="date" id="dateStart" class="form-control" onchange="loadStats()">
                <div class="input-group-append">
                    <span class="input-group-text">s/d</span>
                </div>
                <input type="date" id="dateEnd" class="form-control" onchange="loadStats()">
            </div>

            <!-- Reset Button -->
            <button onclick="resetFilters()" class="btn btn-sm btn-secondary" title="Reset Filter">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- GRID KARTU BARU: Menggunakan CSS Grid agar lebih rapi dan konsisten -->
    <div class="stats-grid">

        <!-- Total KPI -->
        <div class="stat-card">
            <div class="icon-box icon-blue">
                <i class="fas fa-clipboard-list"></i> <!-- Ganti ke Font Awesome -->
            </div>
            <div class="stat-title">Total KPI</div>
            <div class="stat-number text-blue" id="val-total">0</div>
            <div class="stat-desc">Semua KPI aktif</div>
        </div>

        <!-- Selesai -->
        <div class="stat-card cursor-pointer" onclick="filterByStatus('selesai')">
            <div class="icon-box icon-green">
                <i class="fas fa-check-circle"></i> <!-- Ganti ke Font Awesome -->
            </div>
            <div class="stat-title">Selesai</div>
            <div class="stat-number text-green" id="val-selesai">0</div>
            <div class="stat-desc" id="desc-selesai">0% dari total KPI</div>
        </div>

        <!-- Progress -->
        <div class="stat-card cursor-pointer" onclick="filterByStatus('progress')">
            <div class="icon-box icon-yellow">
                <i class="fas fa-chart-pie"></i> <!-- Ganti ke Font Awesome -->
            </div>
            <div class="stat-title">Progress</div>
            <div class="stat-number text-yellow" id="val-progress">0</div>
            <div class="stat-desc" id="desc-progress">0% dari total KPI</div>
        </div>

        <!-- Overdue -->
        <div class="stat-card cursor-pointer" onclick="filterByStatus('overdue')">
            <div class="icon-box icon-red">
                <i class="fas fa-exclamation-triangle"></i> <!-- Ganti ke Font Awesome -->
            </div>
            <div class="stat-title">Overdue</div>
            <div class="stat-number text-red" id="val-overdue">0</div>
            <div class="stat-desc" id="desc-overdue">0% dari total KPI</div>
        </div>

    </div>

    <div class="main-completion-card">
        <!-- Kolom Kiri: Donut Chart -->
        <div class="chart-container">
            <h4 class="w-100 mb-0">Completion Rate</h4>
            <p class="text-muted w-100">Target bulan ini</p>
            
            <div class="donut-chart" style="--percentage: 82%;" id="chart-donat">
                <span class="percentage-text" id="val-persen-chart">82%</span>
            </div>
            
            {{-- <div class="progress w-100" style="height: 10px; border-radius: 5px;">
                <div class="progress-bar bg-primary" id="bar-progress" style="width: 82%;"></div>
            </div> --}}
        </div>

        <!-- Kolom Kanan: Daftar Item -->
        <div class="list-container border-start ps-4 flex-grow-1">
            <div class="row">
                <!-- Bagian Kiri Container: KPI & Points -->
                <div class="col-md-6">
                    <!-- KPI Hari Ini -->
                    <div class="kpi-mini-card mb-3" style="background: #f8f9ff;">
                        <div class="kpi-icon-box bg-primary text-white"><i class="fas fa-calendar-day"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">KPI Hari Ini</div>
                            <div class="small text-muted">Deadline hari ini</div>
                        </div>
                        <div class="h4 text-primary fw-bold m-0" id="val-kpi-hari-ini">0</div>
                    </div>

                    <!-- Deadline Besok -->
                    <div class="kpi-mini-card mb-3" style="background: #f6fdf9;">
                        <div class="kpi-icon-box bg-success text-white"><i class="fas fa-calendar-check"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Deadline Besok</div>
                            <div class="small text-muted">Jangan sampai terlewat</div>
                        </div>
                        <div class="h4 text-success fw-bold m-0" id="val-deadline-besok">0</div>
                    </div>

                    <!-- Point Bulan Ini -->
                    <div class="kpi-mini-card" style="background: #fcf9ff;">
                        <div class="kpi-icon-box" style="background: #8e44ad; color: white;"><i class="fas fa-trophy"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">Point Bulan Ini</div>
                            <div class="small text-muted">Total point</div>
                        </div>
                        <div class="h4 fw-bold m-0" style="color: #8e44ad;" id="val-point-bulan-ini">0</div>
                    </div>
                </div>

                <!-- Bagian Kanan Container: Deadline Hari Ini (List) -->
                <div class="col-md-6">
                    <div class="h6 mb-3 fw-bold"><i class="bi bi-clock text-primary me-2"></i> Deadline Hari Ini</div>
                    <div id="list-deadline" style="max-height: 250px; overflow-y: auto;">
                        <!-- Data akan masuk ke sini via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deadline Hari Ini -->
    <div class="row mt-4">

        <!-- Aktivitas Terbaru -->
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0"><i class="bi bi-lightning-charge text-warning me-2"></i> Aktivitas Terbaru</h5>
                </div>
                <!-- Target Injeksi Data -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Client</th>
                                <th>Nama KPI</th>
                                <th>PIC (Email)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-aktivitas-body">
                            <!-- Data akan masuk ke sini via JS -->
                        </tbody>
                    </table>
                    <nav>
                        <ul class="pagination pagination-sm justify-content-center" id="pagination-controls">
                            <!-- Tombol akan muncul di sini -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>


<!-- JavaScript untuk mengambil data statistik -->
<script>
    let currentPage = 1;
    let data = null;
    const itemsPerPage = 5; // Ubah sesuai kebutuhan

    let currentFilter = 'all'; // Default menampilkan semua

    function filterByStatus(status) {
        // Toggle: Jika diklik filter yang sama, maka reset ke 'all'
        currentFilter = (currentFilter === status) ? 'all' : status;
        
        // Update visual kartu aktif
        document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
        if (currentFilter !== 'all') {
            // Logika sederhana untuk menandai kartu aktif (sesuaikan selector jika perlu)
            event.currentTarget.classList.add('active');
        }
        
        renderAktivitas(1); // Render ulang tabel dengan filter baru
    }

    async function loadStats() {
        const projectId = document.getElementById('filterProject').value;
        const userId = document.getElementById('filterUser') ? document.getElementById('filterUser').value : '';
        const dateStart = document.getElementById('dateStart').value;
        const dateEnd = document.getElementById('dateEnd').value;

        let url = `/kpi/stats?project_id=${projectId}`;
        if (userId) url += `&user_id=${userId}`; // Tambahkan param user
        if (dateStart) url += `&start_date=${dateStart}`;
        if (dateEnd) url += `&end_date=${dateEnd}`;

        const response = await fetch(url);
        data = await response.json();
        console.log(data);

        // Update Angka
        document.getElementById('val-total').innerText = data.total;
        document.getElementById('val-selesai').innerText = data.selesai;
        document.getElementById('val-progress').innerText = data.progress;
        document.getElementById('val-overdue').innerText = data.overdue;

        // Update Deskripsi
        document.getElementById('desc-selesai').innerText = `${data.persenSelesai}% dari total KPI`;
        document.getElementById('desc-progress').innerText = `${data.persenProgress}% dari total KPI`;
        document.getElementById('desc-overdue').innerText = `${data.persenOverdue}% dari total KPI`;

        const persen = data.persenSelesai || 0; // Menggunakan persenSelesai
        const chart = document.getElementById('chart-donat');
        const text = document.getElementById('val-persen-chart');

        chart.style.setProperty('--percentage', `${persen}%`);
        text.innerText = `${persen}%`;

        document.getElementById('val-kpi-hari-ini').innerHTML = `${data.kpiHariIni} <br><span style="font-size: 10px; font-weight: normal;">KPI</span>`;
        document.getElementById('val-deadline-besok').innerHTML = `${data.deadlineBesok} <br><span style="font-size: 10px; font-weight: normal;">KPI</span>`;
        document.getElementById('val-point-bulan-ini').innerHTML = `${data.totalPoint} <br><span style="font-size: 10px; font-weight: normal;">Point</span>`;

        const listDeadline = document.getElementById('list-deadline');
        listDeadline.innerHTML = ''; // Reset isi
        if (data.deadlines.length > 0) {
            data.deadlines.forEach(item => {
                listDeadline.innerHTML += `
                    <div class="d-flex align-items-center p-3 bg-light rounded-3 mb-2">
                        <div class="bg-white px-2 py-1 rounded border small fw-bold text-primary">${item.jam || '00:00'}</div>
                        <div class="ms-3">
                            <div class="fw-bold">${item.nama}</div>
                            <div class="small text-muted">${item.project_name || 'Tanpa Project'}</div>
                        </div>
                    </div>`;
            });
        } else {
            listDeadline.innerHTML = '<p class="text-muted small text-center py-2">Tidak Ada Deadline Hari Ini.</p>';
        }

        currentPage = 1;
        renderAktivitas(currentPage);
    }

    function renderAktivitas(page = 1) {
        const listBody = document.getElementById('list-aktivitas-body');
        const paginationControls = document.getElementById('pagination-controls');
        listBody.innerHTML = '';
        
        const today = new Date().toISOString().split('T')[0];

        // Filter data berdasarkan kriteria
        let filteredData = data.aktivitas.filter(item => {
            if (currentFilter === 'all') return true;
            if (currentFilter === 'selesai') return item.status == 1;
            if (currentFilter === 'progress') return item.status == 0 && item.tanggal_deadline >= today;
            if (currentFilter === 'overdue') return item.status == 0 && item.tanggal_deadline < today;
            return true;
        });

        // Paginasi pada hasil filter
        const start = (page - 1) * itemsPerPage;
        const paginatedItems = filteredData.slice(start, start + itemsPerPage);
        const totalPages = Math.ceil(filteredData.length / itemsPerPage);

        // Render Baris Tabel
        if (paginatedItems.length > 0) {
            paginatedItems.forEach(item => {
                const picEmails = item.pics && item.pics.length > 0 
                    ? item.pics.map(pic => `<span class="badge bg-light text-dark border m-1">${pic.email}</span>`).join('')
                    : '<span class="text-muted small">Tanpa PIC</span>';

                // MENGGANTI Ikon Bootstrap (bi) menjadi Font Awesome (fas)
                const btnSelesai = item.status == 0 
                    ? `<button class="btn btn-sm btn-success" onclick="selesaikanKPI(${item.id})">
                        <i class="fas fa-check"></i> Selesai
                    </button>` 
                    : '<span class="badge badge-secondary">Selesai</span>'; // Gunakan badge bawaan SB Admin 2

                listBody.innerHTML += `
                    <tr>
                        <td class="small text-muted">${item.tanggal_mulai || '-'}</td>
                        <td class="fw-bold">${item.project_client.nama}</td>
                        <td class="fw-bold">${item.nama_kpi}</td>
                        <td>${picEmails}</td>
                        <td>${btnSelesai}</td>
                    </tr>`;
            });
        } else {
            listBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Tidak ada aktivitas ditemukan.</td></tr>`;
        }

        // Render Tombol Pagination
        paginationControls.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            paginationControls.innerHTML += `
                <li class="page-item ${i === page ? 'active' : ''}">
                    <button class="page-link" onclick="changePage(${i})">${i}</button>
                </li>`;
        }
    }

    function changePage(page) {
        currentPage = page;
        renderAktivitas(currentPage);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const now = new Date();
        
        // Mendapatkan tahun dan bulan saat ini
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0'); // Bulan dimulai dari 0
        
        // Mendapatkan tanggal pertama bulan ini
        const firstDay = `${year}-${month}-01`;
        
        // Mendapatkan tanggal terakhir bulan ini
        const lastDay = new Date(year, now.getMonth() + 1, 0).getDate().toString().padStart(2, '0');
        const lastDayFormatted = `${year}-${month}-${lastDay}`;
        
        // Set value ke input
        document.getElementById('dateStart').value = firstDay;
        document.getElementById('dateEnd').value = lastDayFormatted;
        
        loadStats();
    });

    async function selesaikanKPI(id) {

        if (!confirm('Yakin ingin menyelesaikan KPI ini?')) {
            return;
        }

        try {

            const response = await fetch(`/kpi/${id}/selesai`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                alert('KPI berhasil diselesaikan.');
                loadStats();
            } else {
                alert(result.message);
            }

        } catch (error) {
            console.error(error);
            alert('Terjadi kesalahan.');
        }
    }

    function resetFilters() {
        // 1. Reset Select Input
        document.getElementById('filterProject').value = '';
        if (document.getElementById('filterUser')) {
            document.getElementById('filterUser').value = '';
        }

        filterByStatus('all');

        // 2. Hitung Tanggal Awal dan Akhir Bulan Ini
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        
        // Tanggal 1
        const firstDay = `${year}-${month}-01`;
        
        // Tanggal terakhir bulan ini
        const lastDay = new Date(year, now.getMonth() + 1, 0).getDate().toString().padStart(2, '0');
        const lastDayFormatted = `${year}-${month}-${lastDay}`;

        // 3. Terapkan ke Input
        document.getElementById('dateStart').value = firstDay;
        document.getElementById('dateEnd').value = lastDayFormatted;
        
        // 4. Panggil ulang loadStats
        loadStats();
    }
</script>

@endsection