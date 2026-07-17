@extends('layout.app')
@section('content')
<style>
    /* Mengatur agar kolom 5 bisa sejajar di layar besar */
    @media (min-width: 992px) {
        .col-lg-2-4 { flex: 0 0 20%; max-width: 20%; }
    }
    .card-stat {
        transition: all 0.3s ease;
        border: 2px solid transparent !important;
    }
    
    .card-stat:hover { transform: translateY(-5px); }
    .card-stat.active-filter {
        border: 2px solid var(--bs-primary) !important;
        background-color: #f0f7ff !important;
        transform: scale(1.02);
    }

    .carousel-inner {
        padding-left: 40px;
        padding-right: 40px;
    }

    /* 2. Styling tombol navigasi agar berada di posisi yang pas */
    .carousel-control-prev, 
    .carousel-control-next {
        width: 35px !important;
        height: 35px !important;
        background-color: #4e73df !important;
        border-radius: 50% !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        opacity: 0.9 !important;
        box-shadow: 0 3px 6px rgba(0,0,0,0.15) !important;
        z-index: 10; /* Pastikan tombol di atas */
    }

    /* Posisikan tombol tepat di pinggir agar tidak menutupi teks */
    .carousel-control-prev { left: 0px !important; }
    .carousel-control-next { right: 0px !important; }
    
    .carousel-control-prev-icon, 
    .carousel-control-next-icon {
        width: 15px !important;
        height: 15px !important;
    }
    .stats-container {
        display: grid;
        /* Membuat 5 kolom dengan lebar sama persis */
        grid-template-columns: repeat(5, 1fr); 
        gap: 15px; /* Jarak antar kartu */
        width: 100%;
    }

    /* Responsif: Jika layar mengecil (tablet/HP), ubah jadi 2 atau 3 kolom */
    @media (max-width: 992px) {
        .stats-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 576px) {
        .stats-container {
            grid-template-columns: repeat(1, 1fr);
        }
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
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
    .stat-card.active { border: 2px solid #007bff; }
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
    .text-gray { color: #6c757d; }

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

    .stat-card.active {
        border-color: #4e73df; /* Sesuaikan dengan warna tema Anda */
        background-color: #f8f9fc;
        box-shadow: 0 0 10px rgba(78, 115, 223, 0.2);
    }
</style>

    <input type="hidden" id="divisi" value="{{ $divisiId }}">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <!-- Header Text -->
        <div>
            <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Halo, {{ Auth::user()->name }} 👋</h1>
            <p class="text-muted small">Selamat bekerja hari ini</p>
        </div>

        <!-- Filter Section -->
        <div class="d-flex align-items-center">
            <!-- Filter Project -->
            <select id="filter-client" class="form-control form-control-sm mr-2" style="width: auto; min-width: 150px;" onchange="loadTaskStats()">
                <option value="all">Semua Project</option>
                @foreach($client as $p)
                    <option value="{{ $p->id_client }}">{{ $p->nama_client }}</option>
                @endforeach
            </select>

            <!-- Filter Bawahan -->
            <select id="filter-pic" class="form-control form-control-sm mr-2" style="width: auto; min-width: 150px;" onchange="loadTaskStats()">
                <option value="all">Semua Bawahan</option>
                @foreach($allUser as $u)
                    <option value="{{ $u->id }}">{{ $u->email }}</option>
                @endforeach
            </select>

            <select id="filter-divisi" class="form-control form-control-sm mr-2" style="width: auto; min-width: 150px;" onchange="loadTaskStats()">
                <option value="all">Semua Divisi</option>
                @foreach($divisiList as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>

            <!-- Filter Tanggal -->
            <div class="input-group input-group-sm mr-2" style="width: auto;">
                <input type="date" id="dateStart" class="form-control" onchange="loadTaskStats()">
                <div class="input-group-append">
                    <span class="input-group-text">s/d</span>
                </div>
                <input type="date" id="dateEnd" class="form-control" onchange="loadTaskStats()">
            </div>

            <!-- Tombol Reset -->
            <button onclick="resetFilters()" class="btn btn-sm btn-light border" title="Reset Filter">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="stats-grid mb-4">
        @php
            $statsData = [
                ['id' => 'val-total-task', 'label' => 'Total Task', 'sub' => 'Hari Ini', 'icon' => 'fa-list-ul', 'color' => 'blue', 'status' => 'all'],
                ['id' => 'val-selesai', 'label' => 'Selesai', 'sub' => 'Done', 'icon' => 'fa-check-circle', 'color' => 'green', 'status' => 4],
                ['id' => 'val-progress', 'label' => 'Running', 'sub' => 'On Track', 'icon' => 'fa-chart-pie', 'color' => 'yellow', 'status' => 1],
                ['id' => 'val-belum-mulai', 'label' => 'Not Start', 'sub' => 'Pending', 'icon' => 'fa-hourglass-half', 'color' => 'gray', 'status' => 0],
                ['id' => 'val-overdue', 'label' => 'Overdue', 'sub' => 'Urgent', 'icon' => 'fa-exclamation-triangle', 'color' => 'red', 'status' => 7],
            ];
        @endphp

        @foreach($statsData as $item)
            <div class="stat-card cursor-pointer" onclick="filterByStatus('{{ $item['status'] }}', this)">
                <!-- Ikon Box Kustom -->
                <div class="icon-box icon-{{ $item['color'] }}">
                    <i class="fas {{ $item['icon'] }}"></i>
                </div>
                
                <!-- Teks -->
                <div class="stat-title">{{ $item['label'] }}</div>
                <div class="stat-number text-{{ $item['color'] }}" id="{{ $item['id'] }}">0</div>
                <div class="stat-desc">{{ $item['sub'] }}</div>
            </div>
        @endforeach
    </div>
    
    <div class="row mt-4">
        <!-- AREA KIRI (8 Kolom) -->
        <div class="col-lg-8">
            <!-- Progress Bar Card -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="m-0 font-weight-bold text-gray-800">Progress</h6>
                        <h6 id="progress-text" class="m-0 text-primary font-weight-bold">0%</h6>
                    </div>
                    <div class="progress mb-2" style="height: 12px;">
                        <div id="progress-bar-fill" class="progress-bar bg-primary" style="width: 0%"></div>
                    </div>
                    <div id="progress-desc" class="text-muted small">0 dari 0 task selesai</div>
                </div>
            </div>

            <!-- Tabel Task -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Task Hari Ini</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr class="text-uppercase text-muted small">
                                    <th>Task</th><th>PIC</th><th>Client</th><th>Tanggal</th><th>Jam Mulai</th><th>Prioritas</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="task-tbody"></tbody>
                        </table>
                    </div>
                    <nav id="pagination-controls" class="d-flex justify-content-center mt-3"></nav>
                </div>
            </div>
        </div>

        <!-- AREA KANAN (4 Kolom) -->
        <div class="col-lg-4">
            <!-- Sedang Dikerjakan -->
            <div class="card shadow mb-4" id="card-active-task">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Sedang Dikerjakan</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <!-- Filter Divisi -->
                        <div class="me-2" style="flex: 1;">
                            @if(Auth::user()->isAdmin !== true)
                                <div class="custom-control custom-switch mt-1">
                                    <input type="checkbox" class="custom-control-input" id="divisi-filter" value="{{ $divisiId }}" onchange="loadActiveTask()">
                                    <label class="custom-control-label small" for="divisi-filter">Divisi Saya</label>
                                </div>
                            @else
                                <select id="divisi-filter" class="form-control form-control-sm" onchange="updateUserDropdown()">
                                    <option value="all">Semua Divisi</option>
                                    @foreach($divisiList as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <!-- Filter PIC -->
                        <div style="flex: 1;">
                            <select id="filter-active-task-pic" class="form-control form-control-sm" onchange="loadActiveTask()">
                                <option value="all">Semua User</option>
                                @foreach($allUser as $u)
                                    <option value="{{ $u->id }}">{{ $u->email }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden Fields (Tepikan agar tidak mengganggu layout) -->
                        <input type="hidden" id="current-user-id" value="{{ auth()->id() }}">
                        <input type="hidden" id="is-admin" value="{{ Auth::user()->isAdmin == true ? '1' : '0' }}">
                    </div>

                    <div id="active-task-list" class="d-flex flex-column">
                        <!-- Item akan di-generate oleh JS -->
                    </div>
                </div>
            </div>

            <!-- Antrian -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Antrian Berikutnya</h6>
                </div>
                <div class="card-body" id="antrian-list">
                    <!-- Data akan di-inject di sini -->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Produktivitas -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-chart-line text-success mr-2"></i>
                        <h6 class="m-0 font-weight-bold text-gray-800">Produktivitas Hari Ini</h6>
                    </div>
                    <div class="d-flex justify-content-between text-center">
                        <div>
                            <div class="small text-muted">Jam Kerja</div>
                            <div class="h5 font-weight-bold text-primary mb-0" id="prod-jam-kerja">0j 0m</div>
                            <div class="small text-muted" id="jamkerja"></div>
                        </div>
                        <div>
                            <div class="small text-muted">Task / Jam</div>
                            <div class="h5 font-weight-bold text-primary mb-0" id="prod-task-jam">0</div>
                            <div class="small text-muted">Rata-rata</div>
                        </div>
                        <div>
                            <div class="small text-muted">Estimasi</div>
                            <div class="h5 font-weight-bold text-success mb-0" id="prod-estimasi">0j</div>
                            <div class="small text-muted">Total estimasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktivitas (Timeline) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-bolt text-primary mr-2"></i>
                            <h6 class="m-0 font-weight-bold text-gray-800">Aktivitas Hari Ini</h6>
                        </div>
                        <!-- Filter User -->
                        <select id="filter-user-activity" class="form-control form-control-sm" style="width: 100px;" onchange="loadTaskStats()">
                            <option value="all">Semua</option>
                            @foreach($allUser as $u)
                                <option value="{{ $u->id }}">{{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="timeline small" id="activity-timeline">
                        <div class="text-muted">Memuat aktivitas...</div>
                    </div>
                    <div id="activity-pagination" class="mt-3 d-flex justify-content-center">
                        <button id="prev-activity" class="btn btn-sm btn-outline-secondary mr-2" onclick="changePageActivity(-1)" disabled>Prev</button>
                        <span id="page-info" class="align-self-center small font-weight-bold">Page 1</span>
                        <button id="next-activity" class="btn btn-sm btn-outline-secondary ml-2" onclick="changePageActivity(1)">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Focus Timer -->
        <div class="col-md-4 mb-4" id="focus-timer-card">
            <div class="card shadow h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-stopwatch text-primary mr-2"></i>
                            <h6 class="m-0 font-weight-bold text-gray-800">Focus Timer</h6>
                        </div>
                        <!-- Filter User -->
                        <select id="filter-user-timer" class="form-control form-control-sm" style="width: 100px;" onchange="loadTaskStats()">
                            <option value="all">Semua</option>
                            @foreach($allUser as $u)
                                <option value="{{ $u->id }}">{{ $u->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div id="status-timer-text" class="text-muted small">Sedang Tidak Fokus</div>
                            <div class="h3 font-weight-bold text-primary my-1" id="timer-display">00:00</div>
                            <div class="small text-muted" id="timer-target">Target 0 menit</div>
                        </div>
                        
                        <div class="text-primary">
                            <svg width="60" height="60" viewBox="0 0 36 36" style="transform: rotate(-90deg);">
                                <!-- Lingkaran Background (Abu-abu) -->
                                <circle cx="18" cy="18" r="15.915" fill="none" stroke="#e2e8f0" stroke-width="3" />
                                <!-- Lingkaran Progress (Warna Utama) -->
                                <circle id="progress-circle" cx="18" cy="18" r="15.915" fill="none" stroke="currentColor" stroke-width="3" 
                                        stroke-dasharray="0, 100" stroke-dashoffset="0" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



<script>
    window.onload = function() {
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        
        // Format tanggal hari ini: YYYY-MM-DD
        const today = `${year}-${month}-${day}`;

        // Terapkan ke input
        const dateStart = document.getElementById('dateStart');
        const dateEnd = document.getElementById('dateEnd');

        // Hanya isi jika nilainya masih kosong
        if (!dateStart.value) dateStart.value = today;
        if (!dateEnd.value) dateEnd.value = today;

        // Panggil fungsi untuk load data pertama kali
        if (typeof loadTaskStats === 'function') {
            loadTaskStats();
        }
    };
    // Fungsi pembantu untuk menentukan label dan warna status
    let currentPage = 1;
    const rowsPerPage = 10;
    let currentData = [];
    let activityData = [];
    let currentActivityPage = 1;
    function getStatusBadge(status) {
        const map = {
            0: { label: 'Not Start', class: 'bg-secondary-subtle text-secondary' },
            1: { label: 'Running', class: 'bg-primary-subtle text-primary' },
            3: { label: 'Wait Approval', class: 'bg-info-subtle text-info' },
            4: { label: 'Done', class: 'bg-success-subtle text-success' },
            7: { label: 'Overdue', class: 'bg-danger-subtle text-danger' },
            8: { label: 'Expired', class: 'bg-warning-subtle text-warning' },
            9: { label: 'Expired', class: 'bg-warning-subtle text-warning' },
        };
        const s = map[status] || { label: 'Unknown', class: 'bg-dark' };
        return `<span class="badge ${s.class}">${s.label}</span>`;
    }

    async function updateUserDropdown() {
        const filterEl = document.getElementById('divisi-filter');
        const userSelect = document.getElementById('filter-active-task-pic');
        
        // 1. Tentukan divisiId berdasarkan tipe inputnya
        let divisiId = 'all';
        if (filterEl.type === 'checkbox') {
            divisiId = filterEl.checked ? filterEl.value : 'all';
        } else {
            divisiId = filterEl.value;
        }
        
        // 2. Fetch user berdasarkan divisiId yang sudah didapat
        const response = await fetch(`/api/get-user-divisions?divisi_id=${divisiId}`);
        const users = await response.json();
        
        // 3. Reset dan isi ulang dropdown user
        userSelect.innerHTML = '<option value="all">Semua User</option>';
        users.forEach(u => {
            userSelect.innerHTML += `<option value="${u.id}">${u.email || u.name}</option>`;
        });
        
        // 4. Panggil loadActiveTask untuk menyegarkan konten carousel
        loadActiveTask(); 
    }

    async function loadTaskStats() {
        const pic = document.getElementById('filter-pic').value;
        const selectPIC = document.getElementById('filter-pic');
        const selectDivisi = document.getElementById('filter-divisi').value;
        const client = document.getElementById('filter-client').value;
        const dateStart = document.getElementById('dateStart').value;
        const dateEnd = document.getElementById('dateEnd').value;
        const filterUserActivity = document.getElementById('filter-user-activity').value;
        const timerPic = document.getElementById('filter-user-timer').value;

        if(client != 'all'){
            pic = 'all';
        }
        
        const res = await fetch(`/task/stats?pic=${pic}&client=${client}&start=${dateStart}&end=${dateEnd}&activity_pic=${filterUserActivity}&timer_pic=${timerPic}&divisi_id=${selectDivisi}`); 
        const data = await res.json();

        const total = data.totalHariIni;
        const selesai = data.stats.done;
        const percentage = total > 0 ? Math.round((selesai / total) * 100) : 0;
        
        document.getElementById('progress-text').innerText = `${percentage}%`;
        const progressBar = document.getElementById('progress-bar-fill');
        progressBar.style.width = `${percentage}%`;

        document.getElementById('progress-desc').innerText = `${selesai} dari ${total} task selesai`;
        
        // 1. Update Statistik Card Atas
        document.getElementById('val-total-task').innerText = data.totalHariIni;
        document.getElementById('val-overdue').innerText = data.stats.overdue;
        document.getElementById('val-selesai').innerText = data.stats.done;
        document.getElementById('val-progress').innerText = data.stats.running;
        document.getElementById('val-belum-mulai').innerText = data.stats.not_start;

        loadActiveTask();

        // 3. Render Antrian
        const queueList = document.getElementById('antrian-list');
        queueList.innerHTML = '';

        if (data.queue && data.queue.length > 0) {
            data.queue.forEach((q, index) => {
                // Ganti 'q.name' sesuai dengan nama kolom user di database Anda
                const picName = q.email_pic || 'Unassigned'; 
                
                queueList.innerHTML += `
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${q.nama_task}</div>
                            <div class="small text-muted">
                                ${q.nama_client} 
                                <span class="text-primary ms-1">(${picName})</span>
                            </div>
                        </div>

                        <!-- Jam Mulai -->
                        <div class="text-end ms-2">
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-clock me-1"></i>${q.jam_mulai ? q.jam_mulai.substring(0, 5) : '-'}
                            </span>
                        </div>
                    </div>`;
            });
        } else {
            queueList.innerHTML = '<p class="small text-muted">Tidak ada antrian.</p>';
        }

        document.getElementById('prod-jam-kerja').innerText = data.productivity.jam_kerja;
        document.getElementById('prod-task-jam').innerText = 
            data.productivity.task_per_jam + '/ Jam';

        let divisi = document.getElementById('divisi').value;
        let labelJam = ""; 

        // Penentuan dasar durasi jam kerja
        if (divisi == 5) {
            document.getElementById('prod-estimasi').innerText = "8 Jam";
            labelJam = "8 Jam Kerja";
        } else {
            const hariIni = new Date();
            const namaHari = hariIni.toLocaleDateString('id-ID', { weekday: 'long' });
            
            if (namaHari === "Sabtu") {
                document.getElementById('prod-estimasi').innerText = "5 Jam";
                labelJam = "5 Jam Kerja";
            } else {
                document.getElementById('prod-estimasi').innerText = "7 Jam";
                labelJam = "7 Jam Kerja";
            }
        }

        // Logika pembeda teks berdasarkan PIC
        const jamKerjaEl = document.getElementById('jamkerja');

        if (pic === 'all') {
            jamKerjaEl.innerHTML = "Rata-rata seluruh bawahan<br>(" + labelJam + ")";
        } else {
            jamKerjaEl.innerText = "Dari " + labelJam;
        }

        activityData = data.activities || [];
        currentActivityPage = 1; // Reset ke halaman 1 setiap ganti filter
        renderActivityPage();

        const statusText = document.getElementById('status-timer-text');
        const timerDisplay = document.getElementById('timer-display');
        const timerTarget = document.getElementById('timer-target');
        const progressPath = document.getElementById('progress-circle');

        if (window.timerInterval) {
            clearInterval(window.timerInterval);
            window.timerInterval = null;
        }

        if (data.focus_timer && data.focus_timer.is_active) {
            statusText.innerText = "Sedang Fokus";
            timerTarget.innerText = `Target ${data.focus_timer.target_menit} menit`;

            function updateDisplay() {
                let jamMulai = data.active_task.jam_mulai;
                let menit = data.active_task.menit;
                let startTime;

                try {
                    const p = jamMulai.split(/[^\d]/);
                    if (p.length >= 5) {
                        startTime = new Date(parseInt(p[0]), parseInt(p[1]) - 1, parseInt(p[2]), parseInt(p[3]), parseInt(p[4]), parseInt(p[5]) || 0);
                    } else {
                        startTime = new Date();
                        startTime.setHours(parseInt(p[0]), parseInt(p[1]), parseInt(p[2]) || 0, 0);
                    }
                } catch (e) {
                    timerDisplay.innerHTML = "Err Parse";
                    return;
                }

                const now = new Date();
                const diffMs = now.getTime() - startTime.getTime();

                if (isNaN(diffMs)) {
                    timerDisplay.innerHTML = "Waktu Invalid";
                    return;
                }

                const detikTerpakai = Math.floor(diffMs / 1000);
                const m = Math.floor(detikTerpakai / 60);
                const s = detikTerpakai % 60;

                timerDisplay.innerText = `${m}:${s.toString().padStart(2, '0')}`;

                // Update Progres
                if (progressPath) {
                    const totalDetikLimit = menit * 60;
                    const persen = Math.min((detikTerpakai / totalDetikLimit) * 100, 100);
                    progressPath.setAttribute('stroke-dasharray', `${persen}, 100`);
                }
            }

            updateDisplay();
            // 2. Simpan referensi interval di window
            window.timerInterval = setInterval(updateDisplay, 1000);
            if (progressPath) {
                // Gunakan 'menit' dari data.focus_timer.target_menit (sesuaikan jika logic Anda berbeda)
                const totalMenit = data.focus_timer.target_menit; 
                const totalDetikLimit = totalMenit * 60;
                
                // Hitung persentase (0 - 100)
                const persen = Math.min((detikTerpakai / totalDetikLimit) * 100, 100);
                
                // Update stroke-dasharray: format "persen, 100"
                progressPath.setAttribute('stroke-dasharray', `${persen}, 100`);
            }
        } else {
            // 3. Pastikan UI reset saat tidak fokus
            statusText.innerText = "Sedang Tidak Fokus";
            timerDisplay.innerText = "00:00";
            timerTarget.innerText = "Target 0 menit";

            if (progressPath) {
                progressPath.setAttribute('stroke-dasharray', `0, 100`);
            }
        }

        // 4. Update data untuk Paginasi dan Render Tabel
        currentData = data.tasks; 
        currentPage = 1;          
        renderTable(); // Panggil ini sekali saja untuk merender data + paginasi
    }

    function renderActivityPage() {
        const timeline = document.getElementById('activity-timeline');
        timeline.innerHTML = '';

        const start = (currentActivityPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedItems = activityData.slice(start, end);

        if (paginatedItems.length > 0) {
            paginatedItems.forEach(act => {
                timeline.innerHTML += `
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-muted small" style="min-width: 60px;">${act.time.substring(0, 5)}</span> 
                        <i class="bi bi-circle-fill text-${act.color} mx-2" style="font-size: 6px;"></i> 
                        <div class="text-truncate" style="flex: 1;">
                            <span class="fw-bold text-dark">${act.desc}</span>
                            <span class="text-muted small italic" style="opacity: 0.7;">&bull; ${act.email}</span>
                        </div>
                    </div>`;
            });
        } else {
            timeline.innerHTML = '<div class="text-muted small italic">Tidak ada aktivitas.</div>';
        }

        // Update tombol navigasi
        document.getElementById('page-info').innerText = `Page ${currentActivityPage}`;
        document.getElementById('prev-activity').disabled = (currentActivityPage === 1);
        document.getElementById('next-activity').disabled = (end >= activityData.length);
    }

    async function loadActiveTask() {
        const isAdmin = document.getElementById('is-admin').value === '1';
        const currentUserId = document.getElementById('current-user-id').value;
        
        // Logika pemilihan User ID
        let userId = document.getElementById('filter-active-task-pic').value;     
        const userSelect = document.getElementById('filter-active-task-pic');
        // Logika pemilihan Divisi ID
        const divisiFilter = document.getElementById('divisi-filter');

        const controls = document.getElementById('carousel-controls');

        let divisiId = 'all';
        if (divisiFilter.checked == false && isAdmin == false && userId == 'all') {
            userId = currentUserId;
        }

        if(divisiFilter.checked && userId != 'all'){
            userId = 'all';
            userSelect.value = 'all';
        }
        
        if (divisiFilter) {
            divisiId = (divisiFilter.type === 'checkbox') ? 
                    (divisiFilter.checked ? divisiFilter.value : 'all') : 
                    divisiFilter.value;
        }

        try {
            // Fetch data dengan parameter yang sudah disesuaikan
            const response = await fetch(`/kpi/get-active-task?user_id=${userId}&divisi_id=${divisiId}`);
            const data = await response.json();
            console.log(data);

            if (data.count > 1) {
                if (controls) controls.style.display = 'block';
            } else {
                if (controls) controls.style.display = 'none';
            }
            
            const activeContent = document.getElementById('active-task-list');
            const activeStats = document.getElementById('active-task-stats');

            if (activeContent) {
                activeContent.innerHTML = ''; 
                
                // Tampilkan jumlah task aktif
                if(activeStats) activeStats.innerText = `Total: ${data.count || 0} Task Aktif`;

                if (data.count > 0 && data.tasks) {
                    activeContent.innerHTML = ''; // Pastikan container kosong
                    
                    data.tasks.forEach((task) => {
                        const picName = task.pic_name || 'No PIC'; 
                        
                        // Menggunakan struktur list (tidak ada carousel-item)
                        const jamMulai = task.jam_mulai || '-'; 

                        activeContent.innerHTML += `
                            <div class="card shadow-sm border-0 mb-2">
                                <div class="card-body p-2 d-flex align-items-center">
                                    <div class="bg-success-subtle p-2 rounded-circle me-3">
                                        <i class="bi bi-briefcase text-success"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small text-truncate" style="max-width: 200px;">${task.nama}</div>
                                        <div class="text-muted" style="font-size: 11px;">${task.project}</div>
                                        
                                        <!-- Baris PIC dan Jam Mulai -->
                                        <div class="d-flex justify-content-between">
                                            <div class="text-dark fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-person-fill"></i> ${picName}
                                            </div>
                                            <div class="text-primary fw-bold" style="font-size: 10px;">
                                                <i class="bi bi-clock"></i> ${jamMulai}
                                            </div>
                                        </div>
                                        
                                        <div class="text-primary" style="font-size: 10px;">Mulai: ${task.mulai}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    activeContent.innerHTML = `
                        <div class="text-muted small p-3 text-center">Tidak ada task aktif.</div>
                    `;
                }

                // RE-INITIALIZE CAROUSEL
                const carouselElement = $('#activeTaskCarousel');
                carouselElement.carousel('dispose');
                carouselElement.carousel({ interval: 5000, wrap: true });
            }
        } catch (error) {
            console.error("Error:", error);
        }
    }

    function getPriorityBadge(priority) {
        const map = {
            1: { label: 'Rendah', class: 'bg-secondary-subtle text-secondary' },
            2: { label: 'Sedang', class: 'bg-warning-subtle text-warning' },
            3: { label: 'Tinggi', class: 'bg-danger-subtle text-danger' }
        };
        const p = map[priority] || { label: '-', class: 'bg-light' };
        return `<span class="badge ${p.class}">${p.label}</span>`;
    }

    document.addEventListener('DOMContentLoaded', loadTaskStats);
    let currentFilter = 'all';
    function filterByStatus(status, element) {
        // 1. Update filter global
        currentFilter = status;
        currentPage = 1;
        renderTable();

        // 2. Hapus class 'active-filter' dari semua kartu stat
        document.querySelectorAll('.card-stat').forEach(card => {
            card.classList.remove('active-filter');
        });

        // 3. Tambahkan class hanya jika status BUKAN 'all'
        if (status === 'all') {
            // Hapus semua class active jika ada
            document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
        } else {
            // 2. Hapus class 'active' dari semua kartu
            document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
            
            // 3. Tambahkan class 'active' ke elemen yang diklik
            element.classList.add('active');
        }
    }
    function renderTable() {
        const tbody = document.getElementById('task-tbody');
        tbody.innerHTML = '';

        // Filter data berdasarkan currentFilter
        let filteredData = currentData;
        if (currentFilter !== 'all') {
            filteredData = currentData.filter(t => t.status == currentFilter);
        }

        // CEK JIKA DATA KOSONG
        if (filteredData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            Tidak ada data task yang ditemukan.
                        </div>
                    </td>
                </tr>`;
            
            // Tetap panggil renderPagination dengan 0 data agar UI pagination reset/hilang
            renderPagination(0);
            return; // Keluar dari fungsi
        }

        // Gunakan filteredData untuk paginasi jika ada data
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedItems = filteredData.slice(start, end);

        paginatedItems.forEach(t => {
            tbody.innerHTML += `<tr>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-circle me-3"></i>
                        <div>
                            <div class="fw-bold">${t.nama_task}</div>
                            <div class="small text-muted">${t.id_kpi != null ? t.nama_kpi : 'Tidak Termasuk KPI'}</div>
                        </div>
                    </div>
                </td>
                <td><small>${t.email_pic}</small></td> 
                <td><small>${t.nama_client}</small></td> 
                <td><small>${t.tanggal_deadline}</small></td> 
                <td><small>${t.jam_mulai}</small></td> 
                <td>${getPriorityBadge(t.priority)}</td>
                <td>${getStatusBadge(t.status)}</td>
            </tr>`;
        });

        renderPagination(filteredData.length);
    }

    // Update fungsi renderPagination agar menerima total item
    function renderPagination(totalItems) {
        const nav = document.getElementById('pagination-controls');
        const pageCount = Math.ceil(totalItems / rowsPerPage);
        nav.innerHTML = '';

        if (pageCount <= 1) return;

        nav.innerHTML += `<button class="btn btn-sm btn-outline-primary mx-1" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">«</button>`;

        for (let i = 1; i <= pageCount; i++) {
            nav.innerHTML += `<button class="btn btn-sm ${currentPage === i ? 'btn-primary' : 'btn-outline-primary'} mx-1" onclick="changePage(${i})">${i}</button>`;
        }

        nav.innerHTML += `<button class="btn btn-sm btn-outline-primary mx-1" ${currentPage === pageCount ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">»</button>`;
    }

    function changePage(page) {
        currentPage = page;
        renderTable();
    }

    function changePageActivity(direction) {
        currentActivityPage += direction;
        renderActivityPage();
    }
    function resetFilters() {
        // 1. Reset nilai Select (kembalikan ke 'all')
        const filterPic = document.getElementById('filter-pic');
        const filterClient = document.getElementById('filter-client');
        
        if (filterPic) filterPic.value = 'all';
        if (filterClient) filterClient.value = 'all';
        
        // 2. Reset nilai Date Picker ke hari ini
        const now = new Date();
        const today = `${now.getFullYear()}-${(now.getMonth() + 1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')}`;
        
        const dateStart = document.getElementById('dateStart');
        const dateEnd = document.getElementById('dateEnd');
        
        if (dateStart) dateStart.value = today;
        if (dateEnd) dateEnd.value = today;

        // 3. Reset Filter Kartu (Visual & Data)
        // Cari kartu "Total Task" (yang mengandung 'all')
        const totalTaskCard = document.querySelector('[onclick*="all"]');
        if (totalTaskCard) {
            filterByStatus('all', null);    
        }

        // 4. Pastikan filter PIC aktif kembali
        if (filterPic) filterPic.disabled = false;

        // 5. Panggil kembali fungsi loadTaskStats
        if (typeof loadTaskStats === 'function') {
            loadTaskStats();
        }
    }
</script>
@endsection