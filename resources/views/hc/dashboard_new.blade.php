@include('partials.header')
<style>
    /* Mengatur agar kolom 5 bisa sejajar di layar besar */
    @media (min-width: 992px) {
        .col-lg-2-4 { flex: 0 0 20%; max-width: 20%; }
    }
    .card-stat { transition: transform 0.2s; }
    .card-stat:hover { transform: translateY(-5px); }
</style>
<div class="container-fluid">
    <input type="hidden" id="divisi" value="{{ $divisiId }}">
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-4">
        <div class="header-text">
            <h2 class="fw-bold">Halo, {{ Auth::user()->name }} 👋</h2>
            <p class="text-muted mb-0">Selamat bekerja hari ini</p>
        </div>
    
        <div class="d-flex align-items-center gap-2">
            <!-- Filter Project -->
            <select id="filter-client" class="form-select custom-select-filter" onchange="loadTaskStats()">
                <option value="all">Semua Project</option>
                @foreach($client as $p)
                    <option value="{{ $p->id_client }}">{{ $p->nama_client }}</option>
                @endforeach
            </select>
    
            <!-- Filter Bawahan (Hanya Admin) -->
            <select id="filter-pic" class="form-select custom-select-filter" onchange="loadTaskStats()">
                <option value="all">Semua Bawahan</option>
                @foreach($allUser as $u)
                    <option value="{{ $u->id }}">{{ $u->email }}</option>
                @endforeach
            </select>
    
            <!-- Filter Tanggal -->
            <div class="d-flex align-items-center bg-white border rounded-3 px-2 shadow-sm">
                <input type="date" id="dateStart" class="border-0 bg-transparent py-2" style="font-size: 14px;" onchange="loadTaskStats()">
                <span class="text-muted px-1">s/d</span>
                <input type="date" id="dateEnd" class="border-0 bg-transparent py-2" style="font-size: 14px;" onchange="loadTaskStats()">
            </div>
    
            <!-- Tombol Reset -->
            <button onclick="resetFilters()" class="btn btn-light border rounded-3 px-3 shadow-sm">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Menggunakan sistem 5 kolom manual agar lebih seimbang -->
        @php
            $statsData = [
                ['id' => 'val-total-task', 'label' => 'Total Task', 'sub' => 'Hari Ini', 'icon' => 'bi-list-check', 'bg' => 'primary'],
                ['id' => 'val-selesai', 'label' => 'Selesai', 'sub' => 'Done', 'icon' => 'bi-check2-circle', 'bg' => 'success'],
                ['id' => 'val-progress', 'label' => 'Running', 'sub' => 'On Track', 'icon' => 'bi-clock', 'bg' => 'warning'],
                ['id' => 'val-belum-mulai', 'label' => 'Not Start', 'sub' => 'Pending', 'icon' => 'bi-hourglass-split', 'bg' => 'secondary'],
                ['id' => 'val-overdue', 'label' => 'Overdue', 'sub' => 'Urgent', 'icon' => 'bi-exclamation-triangle', 'bg' => 'danger'],
            ];
        @endphp

        @foreach($statsData as $item)
        <div class="col-md-4 col-lg-2-4"> <!-- Menggunakan custom class untuk pembagian 5 -->
            <div class="card p-3 border-0 shadow-sm rounded-4 h-100 card-stat">
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-{{ $item['bg'] }}-subtle p-2 rounded-circle text-{{ $item['bg'] }} me-2">
                        <i class="bi {{ $item['icon'] }}"></i>
                    </div>
                    <div class="text-muted small fw-medium">{{ $item['label'] }}</div>
                </div>
                <div class="h4 fw-bold m-0" id="{{ $item['id'] }}">0</div>
                <div class="text-muted" style="font-size: 11px;">{{ $item['sub'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="row mt-4">
        <!-- AREA KIRI (7 Kolom) -->
        <div class="col-lg-8">
            <!-- Progress Bar Card -->
            <div class="card border-0 shadow-sm p-4 rounded-4 mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="m-0">Progress Hari Ini</h5>
                    <!-- Tambahkan id di sini -->
                    <h5 id="progress-text" class="m-0 text-primary fw-bold">0%</h5>
                </div>
                <div class="progress" style="height: 12px; border-radius: 6px;">
                    <!-- Tambahkan id di sini -->
                    <div id="progress-bar-fill" class="progress-bar" style="width: 0%"></div>
                </div>
                <!-- Tambahkan id di sini -->
                <div id="progress-desc" class="text-muted small mt-2">0 dari 0 task selesai</div>
            </div>
    
            <!-- Tabel Task -->
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h5 class="mb-4">Task Hari Ini</h5>
                <table class="table align-middle">
                    <thead>
                        <tr class="text-uppercase text-muted small">
                            <th>Task</th><th>PIC</th><th>Client</th><th>Prioritas</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="task-tbody">
                        <!-- Contoh 1 Row -->
                    </tbody>
                </table>
                <nav id="pagination-controls" class="d-flex justify-content-center mt-3"></nav>
            </div>
        </div>
    
        <!-- AREA KANAN (5 Kolom) -->
        <div class="col-lg-4">
            <!-- Sedang Dikerjakan -->
            <div class="card border-0 shadow-sm p-4 rounded-4 mb-4" id="card-active-task">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-circle-fill text-success me-2" style="font-size: 10px;"></i> 
                    Sedang Dikerjakan
                </div>
                
                <div id="active-task-content" class="d-flex align-items-center mb-3">
                    <!-- Konten akan diisi oleh JS -->
                    <div class="bg-light p-3 rounded-circle me-3"><i class="bi bi-briefcase text-secondary"></i></div>
                    <div class="text-muted small">Tidak ada task yang sedang dikerjakan.</div>
                </div>
                
                <div class="d-flex justify-content-between small text-muted mb-2">
                    <span id="active-task-start">Mulai: -</span>
                </div>
            </div>
    
            <!-- Antrian -->
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h5 class="mb-3">Antrian Berikutnya</h5>
                <div id="antrian-list">
                    <!-- Data akan di-inject di sini -->
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <!-- Produktivitas -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-graph-up-arrow text-success me-2"></i>
                    <h6 class="m-0 fw-bold">Produktivitas Hari Ini</h6>
                </div>
                <div class="d-flex justify-content-between">
                    <!-- Jam Kerja -->
                    <div>
                        <div class="text-muted small">Jam Kerja</div>
                        <div class="h4 fw-bold text-primary mb-0" id="prod-jam-kerja">0j 0m</div>
                        <div class="small text-muted" id="jamkerja"></div>
                    </div>
                    <!-- Task / Jam -->
                    <div>
                        <div class="text-muted small">Task / Jam</div>
                        <div class="h4 fw-bold text-primary mb-0" id="prod-task-jam">0</div>
                        <div class="small text-muted">Rata-rata</div>
                    </div>
                    <!-- Estimasi -->
                    <div>
                        <div class="text-muted small">Estimasi</div>
                        <div class="h4 fw-bold text-success mb-0" id="prod-estimasi">0j</div>
                        <div class="small text-muted">Total estimasi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aktivitas (Timeline) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-activity text-primary me-2"></i>
                    <h6 class="m-0 fw-bold">Aktivitas Hari Ini</h6>
                </div>
                <div class="timeline small" id="activity-timeline">
                    <div class="text-muted">Tidak ada aktivitas.</div>
                </div>
            </div>
        </div>

        <!-- Focus Timer -->
        <div class="col-md-4" id="focus-timer-card">
            <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-stopwatch text-primary me-2"></i>
                    <h6 class="m-0 fw-bold">Focus Timer</h6>
                </div>
                
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <!-- Tambahkan ID status-timer-text -->
                        <div id="status-timer-text" class="text-muted small">Sedang Tidak Fokus</div>
                        <div class="h3 fw-bold text-primary my-1" id="timer-display">00:00</div>
                        <div class="small text-muted" id="timer-target">Target 0 menit</div>
                    </div>
                    
                    <!-- Lingkaran Progress -->
                    <div class="position-relative" style="width: 60px; height: 60px;">
                        <svg class="w-100 h-100" viewBox="0 0 36 36">
                            <path class="text-light" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" />
                            <path class="text-primary" stroke-dasharray="75, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    // Fungsi pembantu untuk menentukan label dan warna status
    let currentPage = 1;
    const rowsPerPage = 10;
    let currentData = [];
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

    async function loadTaskStats() {
        if (window.timerInterval) {
            clearInterval(window.timerInterval);
            window.timerInterval = null;
        }
        const pic = document.getElementById('filter-pic').value;
        const client = document.getElementById('filter-client').value;
        const dateStart = document.getElementById('dateStart').value;
        const dateEnd = document.getElementById('dateEnd').value;
        
        const res = await fetch(`/task/stats?pic=${pic}&client=${client}&start=${dateStart}&end=${dateEnd}`); 
        const data = await res.json();
        console.log(data);

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

        const activeCardContent = document.getElementById('active-task-content');
        const startEl = document.getElementById('active-task-start');
        
        if (data.active_task) {
            // Jika ada task yang aktif, isi content dengan elemen detail
            activeCardContent.innerHTML = `
                <div class="bg-danger-subtle p-3 rounded-circle me-3"><i class="bi bi-briefcase text-danger"></i></div>
                <div>
                    <div class="fw-bold">${data.active_task.nama_task}</div>
                    <div class="small text-muted">${data.active_task.nama_client || 'Project tidak diketahui'}</div>
                </div>
            `;
            startEl.innerText = `Mulai: ${data.active_task.jam_mulai || '-'}`;
        } else {
            // Jika tidak ada task, isi content dengan tampilan kosong
            activeCardContent.innerHTML = `
                <div class="bg-light p-3 rounded-circle me-3"><i class="bi bi-briefcase text-secondary"></i></div>
                <div class="text-muted small">Tidak ada task yang sedang dikerjakan.</div>
            `;
            startEl.innerText = 'Mulai: -';
        }

        // 3. Render Antrian
        const queueList = document.getElementById('antrian-list');
        queueList.innerHTML = '';

        if (data.queue && data.queue.length > 0) {
            data.queue.forEach((q, index) => {
                queueList.innerHTML += `
                <div class="d-flex align-items-center mb-3">
                    <!-- Indikator Angka -->
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px; font-size: 12px;">${index + 1}</div>
                    
                    <div class="flex-grow-1">
                        <div class="fw-bold">${q.nama_task}</div>
                        <div class="small text-muted">${q.nama_client}</div>
                    </div>

                    <!-- Jam Mulai -->
                    <div class="text-end">
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-clock me-1"></i>${q.jam_mulai || '-'}
                        </span>
                    </div>
                </div>`;
            });
        } else {
            queueList.innerHTML = '<p class="small text-muted">Tidak ada antrian.</p>';
        }

        document.getElementById('prod-jam-kerja').innerText = data.productivity.jam_kerja;
        document.getElementById('prod-task-jam').innerText = data.productivity.task_per_jam + '/ Jam';
        let divisi = document.getElementById('divisi').value;
        if(divisi == 5){
            document.getElementById('prod-estimasi').innerText = 8 + " Jam";
            document.getElementById('jamkerja').innerText = "Dari 8 Jam Kerja";
        }else{
            const hariIni = new Date();
            const namaHari = hariIni.toLocaleDateString('id-ID', { weekday: 'long' });
            if(namaHari == "Sabtu"){
                document.getElementById('prod-estimasi').innerText = 5 + " Jam";
                document.getElementById('jamkerja').innerText = "Dari 5 Jam Kerja";
            }else{
                document.getElementById('jamkerja').innerText = "Dari 7 Jam Kerja";
                document.getElementById('prod-estimasi').innerText = 7 + " Jam";
            }
        }

        // Update Timeline
        const timeline = document.getElementById('activity-timeline');
        timeline.innerHTML = '';
        data.activities.forEach(act => {
            timeline.innerHTML += `
                <div class="d-flex mb-2">
                    <span class="me-3 text-muted">${act.time}</span> 
                    <i class="bi bi-circle-fill text-${act.color} me-2" style="font-size: 8px; margin-top:6px"></i> 
                    ${act.desc}
                </div>`;
        });

        const focusTimerContainer = document.getElementById('focus-timer-card');
        const statusText = document.getElementById('status-timer-text');

        if (data.focus_timer && data.focus_timer.is_active) {
            statusText.innerText = "Sedang Fokus"; // Ubah status jadi Fokus
            document.getElementById('timer-target').innerText = `Target ${data.focus_timer.target_menit} menit`;
            let totalDetik = data.focus_timer.detik_berlalu; 
            
            // Set tampilan awal
            function updateDisplay() {
                let jamMulai = data.active_task.jam_mulai;
                let menit = data.active_task.menit;
                let startTime;
                try {
                    const p = jamMulai.split(/[^\d]/); 
                    
                    if (p.length >= 5) {
                        startTime = new Date(parseInt(p[0]), parseInt(p[1])-1, parseInt(p[2]), parseInt(p[3]), parseInt(p[4]), parseInt(p[5]) || 0);
                    } else {
                        startTime = new Date();
                        startTime.setHours(parseInt(p[0]), parseInt(p[1]), parseInt(p[2]) || 0, 0);
                    }
                } catch (e) {
                    console.log(e);
                    document.getElementById('timer-display').innerHTML = "Err Parse";
                    return;
                }

                const now = new Date();
                const diffMs = now.getTime() - startTime.getTime();
                
                if (isNaN(diffMs)) {
                    document.getElementById('timer-display').innerHTML = "Waktu Invalid";
                    return;
                }

                // Hitung maju (detik yang sudah berjalan)
                const detikTerpakai = Math.floor(diffMs / 1000);
                const m = Math.floor(detikTerpakai / 60);
                const s = detikTerpakai % 60;
                
                // Tampilkan format MM:SS
                const displayM = isNaN(m) ? 0 : m;
                const displayS = isNaN(s) ? 0 : s;

                document.getElementById('timer-display').innerText = 
                    `${displayM}:${displayS.toString().padStart(2, '0')}`;

                // Opsional: Jika ingin lingkaran progres tetap berfungsi (hitung maju)
                const totalDetikLimit = menit * 60;
                const persen = Math.min((detikTerpakai / totalDetikLimit) * 100, 100);
                const progressPath = document.querySelector('.text-primary[stroke-dasharray]');
                if (progressPath) {
                    progressPath.setAttribute('stroke-dasharray', `${persen}, 100`);
                }
            }
            
            updateDisplay();
            
            // Update setiap detik
            window.timerInterval = setInterval(updateDisplay, 1000);
        }else{
            statusText.innerText = "Sedang Tidak Fokus"; // Teks default
            document.getElementById('timer-display').innerText = "00:00";
            document.getElementById('timer-target').innerText = "Target 0 menit";
            
            // Reset lingkaran progres ke 0
            const progressPath = document.querySelector('.text-primary[stroke-dasharray]');
            if (progressPath) {
                progressPath.setAttribute('stroke-dasharray', `0, 100`);
            }

            // Bersihkan interval agar tidak berjalan di latar belakang
            if (window.timerInterval) clearInterval(window.timerInterval);
        }

        // 4. Update data untuk Paginasi dan Render Tabel
        currentData = data.tasks; 
        currentPage = 1;          
        renderTable(); // Panggil ini sekali saja untuk merender data + paginasi
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
    function renderTable() {
        const tbody = document.getElementById('task-tbody');
        tbody.innerHTML = '';

        // Hitung index awal dan akhir
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedItems = currentData.slice(start, end);

        // Render baris
        paginatedItems.forEach(t => {
            console.log(t);
            tbody.innerHTML += `<tr>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-circle me-3"></i>
                        <div><div class="fw-bold">${t.nama_task}</div><div class="small text-muted">${t.id_kpi != null ? t.nama_kpi : 'Tidak Termasuk KPI'}</div></div>
                    </div>
                </td>
                <td><small>${t.email_pic}</small></td> 
                <td><small>${t.nama_client}</small></td> 
                <td>${getPriorityBadge(t.priority)}</td>
                <td>${getStatusBadge(t.status)}</td>
            </tr>`;
        });

        renderPagination();
    }
    function renderPagination() {
        const nav = document.getElementById('pagination-controls');
        const pageCount = Math.ceil(currentData.length / rowsPerPage);
        nav.innerHTML = '';

        if (pageCount <= 1) return;

        // Tombol Prev
        nav.innerHTML += `<button class="btn btn-sm btn-outline-primary mx-1" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">«</button>`;

        // Angka Halaman
        for (let i = 1; i <= pageCount; i++) {
            nav.innerHTML += `<button class="btn btn-sm ${currentPage === i ? 'btn-primary' : 'btn-outline-primary'} mx-1" onclick="changePage(${i})">${i}</button>`;
        }

        // Tombol Next
        nav.innerHTML += `<button class="btn btn-sm btn-outline-primary mx-1" ${currentPage === pageCount ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">»</button>`;
    }

    function changePage(page) {
        currentPage = page;
        renderTable();
    }
    function resetFilters() {
        // 1. Reset nilai Select (kembalikan ke 'all')
        const filterPic = document.getElementById('filter-pic');
        const filterClient = document.getElementById('filter-client');
        
        if (filterPic) filterPic.value = 'all';
        if (filterClient) filterClient.value = 'all';
        
        // 2. Reset nilai Date Picker
        const dateStart = document.getElementById('dateStart');
        const dateEnd = document.getElementById('dateEnd');
        
        if (dateStart) dateStart.value = '';
        if (dateEnd) dateEnd.value = '';

        // 3. (Opsional) Jika Anda menambahkan logic disable filter PIC saat Client dipilih
        // pastikan untuk mengaktifkannya kembali jika ada
        if (filterPic) filterPic.disabled = false;

        // 4. Panggil kembali fungsi loadTaskStats 
        // agar data kembali ke kondisi default (tanpa filter)
        loadTaskStats();
    }
</script>

@include('partials.footer')