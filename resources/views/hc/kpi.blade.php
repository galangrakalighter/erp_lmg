@include('partials.header')

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
</style>

<div class="container-fluid">

    <!-- HEADER BARU: Teks Kiri, Kalender Kanan -->
    <div class="dashboard-header">
        <div class="header-text">
            <h2>Halo, {{ Auth::user()->name }} 👋</h2>
            <p>Selamat bekerja hari ini</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Filter Project -->
            <select id="filterProject" class="custom-select-filter" onchange="loadStats()">
                <option value="">Semua Project</option>
                @foreach($listProject as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                @endforeach
            </select>

            <!-- Filter Bawahan -->
            <select id="filterUser" class="custom-select-filter" onchange="loadStats()">
                <option value="">Semua Bawahan</option>
                @foreach($listBawahan as $bawahan)
                    <option value="{{ $bawahan->id }}">{{ $bawahan->email }}</option>
                @endforeach
            </select>

            <!-- Filter Tanggal -->
            <div class="d-flex align-items-center bg-white border rounded-3 px-2">
                <input type="date" id="dateStart" class="border-0 bg-transparent py-2" style="font-size: 14px;" onchange="loadStats()">
                <span class="text-muted px-1">s/d</span>
                <input type="date" id="dateEnd" class="border-0 bg-transparent py-2" style="font-size: 14px;" onchange="loadStats()">
            </div>

            <!-- Tombol Reset -->
            <button onclick="resetFilters()" class="btn btn-light btn-sm border rounded-3 px-3">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- GRID KARTU BARU: Menggunakan CSS Grid agar lebih rapi dan konsisten -->
    <div class="stats-grid">

        <!-- Total KPI -->
        <div class="stat-card">
            <div class="icon-box icon-blue">
                <i class="bi bi-clipboard-data-fill"></i>
            </div>
            <div class="stat-title">Total KPI</div>
            <div class="stat-number text-blue" id="val-total">0</div>
            <div class="stat-desc">Semua KPI aktif</div>
        </div>

        <!-- Selesai -->
        <div class="stat-card">
            <div class="icon-box icon-green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-title">Selesai</div>
            <div class="stat-number text-green" id="val-selesai">0</div>
            <div class="stat-desc" id="desc-selesai">0% dari total KPI</div>
        </div>

        <!-- Progress -->
        <div class="stat-card">
            <div class="icon-box icon-yellow">
                <i class="bi bi-pie-chart-fill"></i>
            </div>
            <div class="stat-title">Progress</div>
            <div class="stat-number text-yellow" id="val-progress">0</div>
            <div class="stat-desc" id="desc-progress">0% dari total KPI</div>
        </div>

        <!-- Overdue -->
        <div class="stat-card">
            <div class="icon-box icon-red">
                <i class="bi bi-exclamation-triangle-fill"></i>
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
        <div class="list-container border-start ps-4">
            <!-- KPI Hari Ini -->
            <div class="kpi-mini-card" style="background: #f8f9ff;">
                <div class="kpi-icon-box bg-primary text-white"><i class="bi bi-calendar-event"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-bold">KPI Hari Ini</div>
                    <div class="small text-muted">Deadline hari ini</div>
                </div>
                <div class="h4 text-primary fw-bold m-0" id="val-kpi-hari-ini">0 <br><span style="font-size: 10px; font-weight: normal;">KPI</span></div>
            </div>

            <!-- Deadline Besok -->
            <div class="kpi-mini-card" style="background: #f6fdf9;">
                <div class="kpi-icon-box bg-success text-white"><i class="bi bi-calendar-check"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-bold">Deadline Besok</div>
                    <div class="small text-muted">Jangan sampai terlewat</div>
                </div>
                <div class="h4 text-success fw-bold m-0" id="val-deadline-besok">0 <br><span style="font-size: 10px; font-weight: normal;">KPI</span></div>
            </div>

            <!-- Point Bulan Ini -->
            <div class="kpi-mini-card" style="background: #fcf9ff;">
                <div class="kpi-icon-box" style="background: #8e44ad; color: white;"><i class="bi bi-trophy"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-bold">Point Bulan Ini</div>
                    <div class="small text-muted">Dari KPI yang diselesaikan</div>
                </div>
                <div class="h4 fw-bold m-0" style="color: #8e44ad;" id="val-point-bulan-ini">0 <br><span style="font-size: 10px; font-weight: normal;">Point</span></div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Deadline Hari Ini -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0"><i class="bi bi-clock text-primary me-2"></i> Deadline Hari Ini</h5>
                    <a href="#" class="text-decoration-none small">Lihat semua</a>
                </div>
                <!-- Target Injeksi Data -->
                <div id="list-deadline">
                    <!-- Data akan masuk ke sini via JS -->
                </div>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0"><i class="bi bi-lightning-charge text-warning me-2"></i> Aktivitas Terbaru</h5>
                    <a href="#" class="text-decoration-none small">Lihat semua</a>
                </div>
                <!-- Target Injeksi Data -->
                <div id="list-aktivitas">
                    <!-- Data akan masuk ke sini via JS -->
                </div>
            </div>
        </div>
    </div>

</div>

<!-- JavaScript untuk mengambil data statistik -->
<script>
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
    const data = await response.json();
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

    // 5. Update List Aktivitas Terbaru
    const listAktivitas = document.getElementById('list-aktivitas');
    listAktivitas.innerHTML = ''; // Reset isi
    if(data.aktivitas.length > 0){
        data.aktivitas.forEach(item => {
            listAktivitas.innerHTML += `
                <div class="d-flex align-items-start mb-3">
                    <div class="text-success mt-1 me-3"><i class="bi bi-circle-fill" style="font-size: 10px;"></i></div>
                    <div>
                        <div class="small text-muted">${item.waktu || 'Baru saja'}</div>
                        <div class="fw-bold">${item.deskripsi}</div>
                        <div class="small">${item.keterangan || ''}</div>
                    </div>
                </div>`;
        });
    }else{
        listAktivitas.innerHTML = '<p class="text-muted small text-center py-2">Tidak Ada Aktivitas Minggu Ini.</p>';
    }
}

// Panggil fungsi saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', loadStats);
// loadStats();

function resetFilters() {
    // Reset semua input ke nilai default
    document.getElementById('filterProject').value = '';
    if (document.getElementById('filterUser')) {
        document.getElementById('filterUser').value = '';
    }
    document.getElementById('dateStart').value = '';
    document.getElementById('dateEnd').value = '';
    
    // Panggil ulang loadStats
    loadStats();
}
</script>

@include('partials.footer')