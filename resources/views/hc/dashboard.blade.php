@include('partials.header')

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --sb-primary: #4e73df;
        --sb-success: #1cc88a;
        --sb-info: #36b9cc;
        --sb-warning: #f6c23e;
        --sb-danger: #e74a3b;
    }
    .card { transition: all 0.2s ease-in-out; border: none; }
    .card-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important; }
    .icon-circle { height: 40px; width: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .table thead th { background-color: #f8f9fc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; color: #a1acb8; }
    .text-xs-bold { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
    
    /* Badge Soft Styles */
    .badge-soft { font-weight: 700; padding: 0.5em 0.8em; border-radius: 50px; display: inline-flex; align-items: center; font-size: 0.7rem; }
    .bg-soft-primary { background-color: #e0e7ff !important; color: #4338ca !important; }
    .bg-soft-success { background-color: #dcfce7 !important; color: #15803d !important; }
    .bg-soft-warning { background-color: #fef9c3 !important; color: #a16207 !important; }
    .bg-soft-danger { background-color: #fee2e2 !important; color: #b91c1c !important; }
    .bg-soft-secondary { background-color: #f1f5f9 !important; color: #475569 !important; }

    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }

    .filter-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: rgba(255, 255, 255, 0.9);
    }

    .filter-card:hover {
        border-color: var(--sb-primary) !important;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.1) !important;
        transform: translateY(-1px);
    }

    /* Custom Date Styling */
    .custom-date-input {
        cursor: pointer;
        padding: 0.5rem 0.2rem;
        font-size: 0.85rem !important;
        min-width: 130px;
    }

    .custom-date-input:focus {
        color: var(--sb-primary);
        background: transparent;
    }

    /* Tracking tightness */
    .tracking-tight {
        letter-spacing: -0.5px;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }

    .modern-filter-group {
        transition: all 0.3s ease;
        border-color: #e3e6f0 !important;
    }

    .modern-filter-group:hover {
        border-color: #4e73df !important;
        box-shadow: 0 0.25rem 0.75rem rgba(78, 115, 223, 0.08) !important;
    }

    /* Menghilangkan ikon kalender asli browser agar tidak double */
    .custom-input::-webkit-calendar-picker-indicator {
        background: transparent;
        bottom: 0;
        color: transparent;
        cursor: pointer;
        height: auto;
        left: 0;
        position: absolute;
        right: 0;
        top: 0;
        width: auto;
    }

    .custom-input {
        position: relative;
        width: 110px;
        color: #6e707e !important;
        background: transparent;
    }

    /* Menghilangkan border focus biru default */
    .outline-none:focus {
        outline: none;
        box-shadow: none;
    }

    #user-filter:focus {
        box-shadow: none;
    }

    .text-gray-300 {
        color: #d1d3e2;
    }

    .transition {
        transition: opacity 0.3s ease;
    }
    
    .modern-segmented {
        position: relative;
        display: inline-grid;
        /* Ubah repeat(3, 1fr) menjadi repeat(2, 1fr) */
        grid-template-columns: repeat(2, 1fr); 
        background: #f1f5f9;
        padding: 4px;
        border-radius: 50px;
        width: 220px; /* Lebar diperkecil agar lebih proposional untuk 2 tombol */
        font-weight: 500;
        font-size: 14px;
    }

    .modern-segmented input {
        display: none;
    }

    .modern-segmented label {
        position: relative;
        text-align: center;
        padding: 8px 0;
        cursor: pointer;
        z-index: 2;
        border-radius: 50px;
        transition: color 0.3s ease;
        color: #64748b;
    }

    /* Active Text Color */
    .modern-segmented input:checked + label {
        color: #fff;
    }

    /* Sliding Background */
    .modern-segmented .slider {
        position: absolute;
        top: 4px;
        bottom: 4px;
        left: 4px;
        /* Lebar slider disesuaikan menjadi setengah (100% / 2) */
        width: calc((100% - 8px) / 2); 
        background: linear-gradient(135deg, #4e73df, #224abe);
        border-radius: 50px;
        transition: transform 0.3s cubic-bezier(.4,0,.2,1);
    }

    /* Slide Positions */
    #daily:checked ~ .slider {
        transform: translateX(0%);
    }

    /* Karena Weekly dihapus, Monthly langsung lompat ke posisi ke-2 (100%) */
    #monthly:checked ~ .slider {
        transform: translateX(100%);
    }
</style>

<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mt-4 mb-5 pb-4 border-bottom gap-4">
        <div class="d-flex align-items-center gap-3">
            <div>
                <h1 class="h4 mb-1 text-gray-900 fw-bold tracking-tight text-uppercase">Performance Analytics</h1>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-soft-success text-success p-0 px-2 small">Live</span>
                    <p class="text-muted small mb-0">Real-time productivity tracking & task distribution</p>
                </div>
            </div>
        </div>

       <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="position-relative" style="width: 25px;">
                <div id="loading-spinner" class="spinner-border spinner-border-sm text-primary position-absolute opacity-0 transition" role="status"></div>
            </div>

            <div class="modern-filter-group d-flex align-items-center shadow-sm border rounded-pill bg-white px-3 py-1 gap-2">
                <div class="d-flex align-items-center gap-2 border-end pe-3">
                    <i class="far fa-calendar text-primary small"></i>
                    <div class="d-flex align-items-center date-input-wrapper">
                        <input type="date" id="start-date" class="border-0 small fw-bold text-muted outline-none custom-input">
                        <span class="text-gray-300 mx-2">/</span>
                        <input type="date" id="end-date" class="border-0 small fw-bold text-muted outline-none custom-input">
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ps-2" style="min-width: 180px;">
                    <i class="far fa-user-circle text-primary small"></i>
                    <select id="user-filter" class="form-select border-0 fw-bold text-muted small bg-transparent cursor-pointer p-0 outline-none">
                        @if(Auth::user()->role != 'conten_creator')
                            @foreach($allUser as $emp)
                                <option value="{{ $emp->id }}" {{ $emp->id == Auth::id() ? 'selected' : '' }}>
                                    {{ $emp->email }} {{ $emp->id == Auth::id() ? '(Saya)' : '' }}
                                </option>
                            @endforeach
                        @else
                            <option value="{{ Auth::id() }}" selected>{{ Auth::user()->email }} (Saya)</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4">
            <div class="card card-hover shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs-bold text-primary mb-1">Total Task</div>
                            <div class="h4 mb-0 fw-bold text-dark" id="stat-total">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4">
            <div class="card card-hover shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs-bold text-success mb-1">Done</div>
                            <div class="h4 mb-0 fw-bold text-dark" id="stat-done">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4">
            <div class="card card-hover shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs-bold text-danger mb-1">Overdue</div>
                            <div class="h4 mb-0 fw-bold text-dark" id="stat-overdue">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card card-hover shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs-bold text-warning mb-1">Overdue (Done)</div>
                            <div class="h4 mb-0 fw-bold text-dark" id="stat-overdue-done">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-6">
            <div class="card card-hover shadow-sm border-0 rounded-4 h-100 overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs-bold text-info mb-1">Total Durasi</div>
                            <div class="h4 mb-0 fw-bold text-dark" id="stat-hours">0 Menit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <h6 class="m-0 font-weight-bold text-dark tracking-tight">Productivity Analytics</h6>
                            <p class="text-muted small mb-0" style="font-size: 0.7rem;">Metrics based on total minutes worked</p>
                        </div>
                    </div>

                    <div class="modern-segmented">

                        <input type="radio" name="chartFilter" id="daily" value="daily">
                        <label for="daily">Daily</label>

                        <input type="radio" name="chartFilter" id="monthly" value="monthly" checked>
                        <label for="monthly">Monthly</label>

                        <div class="slider"></div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2" style="height: 250px;">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">Latest Task Activities</h6>
        </div>
        <div class="card-body p-3"> <div class="table-responsive">
                <table id="myTaskTable" class="table table-hover align-middle mb-0 text-center" width="100%">
                    <thead>
                        <tr class="text-secondary">
                            <th class="px-4">Project / Task</th>
                            <th>Status</th>
                            <th class="text-center">Start Date</th>
                            <th class="text-center">Due Date</th>
                        </tr>
                    </thead>
                    <tbody id="task-table">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let barChart = null;
    let donutChart = null;

    // --- HELPER FUNCTIONS ---

    function formatDurasi(totalMenit) {
        if (!totalMenit || totalMenit == 0) return "0 Menit";
        const jam = Math.floor(totalMenit / 60);
        const menit = Math.floor(totalMenit % 60);
        console.log(menit);
        if (jam > 0) {
            return menit > 0 ? `${jam} Jam ${menit} Menit` : `${jam} Jam`;
        }
        return `${menit} Menit`;
    }

    function getStatusClass(status) {
        const s = status !== null && status !== undefined ? status.toString() : '';
        const map = {
            '0': 'bg-soft-secondary text-secondary',
            '1': 'bg-soft-primary text-primary',
            '2': 'bg-soft-danger text-danger',
            '3': 'bg-soft-warning text-warning',
            '4': 'bg-soft-success text-success',
            '7': 'bg-danger text-white animate-pulse shadow-sm',
            '8': 'bg-light text-muted border border-dashed'
        };
        return 'badge-soft ' + (map[s] || 'bg-light text-muted');
    }

    function getStatusIcon(status) {
        console.log(status);
        const s = status !== null && status !== undefined ? status.toString() : '';
        const map = {
            '0': '<i class="far fa-clock me-1"></i>',
            '1': '<i class="fas fa-spinner fa-spin me-1"></i>',
            '2': '<i class="fas fa-undo me-1"></i>',
            '3': '<i class="fas fa-pause-circle me-1"></i>',
            '4': '<i class="fas fa-check-circle me-1"></i>',
            '7': '<i class="fas fa-exclamation-triangle me-1"></i>',
            '8': '<i class="fas fa-hourglass-end me-1"></i>'
        };
        return map[s] || '';
    }

    function getStatusLabel(status, tanggal_approve = null) {
        const s = parseInt(status);
        
        // Logika Khusus untuk Status 7 (Overdue)
        if (s === 7) {
            return (tanggal_approve === null || tanggal_approve === "" || tanggal_approve === undefined) 
                ? 'Overdue' 
                : 'Overdue (Done)';
        }

        const map = { 
            0: 'Pending', 
            1: 'On Progress', 
            2: 'Revisi', 
            3: 'Waiting Approval', 
            4: 'Approved', 
            8: 'EndsTime' 
        };

        return map[s] || 'Unknown';
    }

    function showLoading(status) {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) spinner.style.opacity = status ? '1' : '0';
    }

    function animateValue(id, endValue) {
        const obj = document.getElementById(id);
        if (!obj) return;
        let startValue = 0;
        let duration = 800;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (endValue - startValue) + startValue);
            if (progress < 1) window.requestAnimationFrame(step);
        };
        window.requestAnimationFrame(step);
    }

    // --- DATA LOADING ---

    let currentChartType = 'monthly';

    async function loadData(userId) {
        showLoading(true);
        const start = document.getElementById('start-date').value;
        const end = document.getElementById('end-date').value;

        // Gunakan URLSearchParams agar query string rapi
        let params = new URLSearchParams({
            id: userId,
            type: currentChartType // Kirim tipe filter (daily/weekly/monthly)
        });
        
        if (start && end) {
            params.append('start_date', start);
            params.append('end_date', end);
        }

        try {
            const response = await fetch(`/api/dashboard-bawahan/${userId}?${params.toString()}`);
            const data = await response.json();

            // 1. Update Stats Cards
            animateValue("stat-total", data.summary.total_this_month);
            animateValue("stat-done", data.summary.done);
            animateValue("stat-overdue", data.summary.overdue);
            animateValue("stat-overdue-done", data.summary.overdue_done);
            
            document.getElementById('stat-hours').innerText = formatDurasi(data.summary.monthly_hours);

            // 2. Update Table
            const tbody = document.getElementById('task-table');
            console.log(data.recent_tasks);
            tbody.innerHTML = data.recent_tasks.length ? data.recent_tasks.map(t => `
                <tr>
                    <td class="px-4 py-3">
                        <div class="fw-bold text-dark text-center">${t.nama_task}</div>
                        <div class="text-xs text-muted text-center">${t.nama}</div>
                    </td>
                    <td class="text-center">
                        <span class="${getStatusClass(t.status)}">
                            ${getStatusIcon(t.status)} ${getStatusLabel(t.status, t.tanggal_approve)}
                        </span>
                    </td>
                    <td class="text-center px-4">
                        <div class="text-dark small fw-bold">${t.tanggal_mulai}</div>
                    </td>
                    <td class="text-center px-4">
                        <div class="text-dark small fw-bold">${t.tanggal_deadline}</div>
                    </td>
                </tr>
            `).join('') : '<tr><td colspan="3" class="p-4 text-center italic text-muted">No data available.</td></tr>';

            // 3. Update Charts
            updateCharts(data);
        } catch (error) {
            console.error("Fetch error:", error);
        } finally {
            showLoading(false);
        }
    }

    function parseChartLabelToDate(label) {
        const currentYear = new Date().getFullYear();
        const months = {
            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04', 'May': '05', 'Jun': '06',
            'Jul': '07', 'Aug': '08', 'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12',
            // Tambahkan versi Indonesia jika perlu
            'Januari': '01', 'Februari': '02', 'Maret': '03', 'April': '04', 'Mei': '05', 'Juni': '06',
            'Juli': '07', 'Agustus': '08', 'September': '09', 'Oktober': '10', 'November': '11', 'Desember': '12'
        };

        // Pecah label "04 Feb" menjadi ["04", "Feb"]
        const parts = label.split(' ');
        const day = parts[0].padStart(2, '0');
        const monthName = parts[1];
        const month = months[monthName] || '01';

        return `${currentYear}-${month}-${day}`; // Menghasilkan "2026-02-04"
    }

    function updateTaskTable(tasks) {
        const tableBody = document.getElementById('task-table');

        // Isi HTML ke dalam tbody
        tableBody.innerHTML = tasks.map(t => `
            <tr>
                <td class="px-4 py-3">
                    <div class="fw-bold text-dark text-start">${t.nama_task || t.name}</div>
                    <div class="text-xs text-muted text-start">${t.nama}</div>
                </td>
                <td>
                    <span class="${getStatusClass(t.status)}">
                        ${getStatusIcon(t.status)} ${getStatusLabel(t.status,t.tanggal_approve)}
                    </span>
                </td>
                <td class="text-center">
                    <div class="text-dark small fw-bold">${t.tanggal_mulai}</div>
                </td>
                </td>
                <td class="text-center">
                    <div class="text-dark small fw-bold">${t.tanggal_deadline   }</div>
                </td>
            </tr>
        `).join('');
    }

    document.querySelectorAll('input[name="chartFilter"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentChartType = this.value;
            const userId = document.getElementById('user-filter').value;
            loadData(userId);
        });
    });

    function reloadTaskTable(params) {
        const selectedUserId = document.getElementById('user-filter').value;
        // Tampilkan loading pada tabel
        $('#myTaskTable tbody').html('<tr><td colspan="5" class="text-center">Loading tasks...</td></tr>');
        let queryParams = new URLSearchParams(params);
        $.ajax({
            url: `/api/dashboard-bawahan/${selectedUserId}?${queryParams.toString()}`, // Sesuaikan URL Anda
            method: 'GET',
            data: params,
            success: function(response) {
                // Fungsi untuk merender ulang isi tabel myTaskTable
                updateTaskTable(response.recent_tasks);
                
                // Opsional: Scroll ke arah tabel agar user tahu data berubah
                $('html, body').animate({
                    scrollTop: $("#myTaskTable").offset().top - 100
                }, 500);
            }
        });
    }

    function updateCharts(data) {
        // --- BAR CHART ---
        var ctxBar = document.getElementById('barChart').getContext('2d');

        if (barChart) {
            barChart.destroy();
        }

        // 1. BUAT GRADIENT WARNA (Primary Blue ke Light Blue)
        var gradient = ctxBar.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#4e73df');   // Warna atas
        gradient.addColorStop(1, '#a6bdfa');   // Warna bawah (lebih terang)

        barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Total Durasi Kerja',
                    data: data.monthly_data,
                    backgroundColor: gradient,           // Pakai gradient
                    hoverBackgroundColor: '#2e59d9',     // Warna saat di-hover lebih gelap
                    borderColor: '#4e73df',
                    borderWidth: 1,
                    borderRadius: 10,                    // Membuat sudut melengkung (untuk Chart.js v3+)
                    barThickness: 25,                    // Ukuran batang yang ideal
                    maxBarThickness: 35,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20 // Beri ruang di atas agar tooltip tidak terpotong
                    }
                },
                legend: {
                    display: false // Sembunyikan legend karena sudah jelas dari judul
                },
                tooltips: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: "rgba(255,255,255,0.9)", // Putih bersih
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chartData) {
                            // Gunakan fungsi formatDurasi Anda
                            return ' Durasi: ' + formatDurasi(tooltipItem.yLabel);
                        }
                    }
                },
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0]._index;
                        const label = barChart.data.labels[index];
                        const filterType = $('input[name="chartFilter"]:checked').val();
                        
                        // Ambil data berdasarkan tipe filter yang aktif
                        let params = { type: filterType };
                        
                        if (filterType === 'daily') {
                            // Jika harian, label biasanya "DD MMM". 
                            // Sebaiknya simpan format YYYY-MM-DD di dataset hidden atau kirim string labelnya
                            params.clicked_date = parseChartLabelToDate(label); 
                        } else if (filterType === 'monthly') {
                            const monthMap = {
                                // Bahasa Inggris
                                'January': 1, 'February': 2, 'March': 3, 'April': 4, 'May': 5, 'June': 6,
                                'July': 7, 'August': 8, 'September': 9, 'October': 10, 'November': 11, 'December': 12,
                                // Bahasa Indonesia (Carbon translatedFormat)
                                'Januari': 1, 'Februari': 2, 'Maret': 3, 'April': 4, 'Mei': 5, 'Juni': 6,
                                'Juli': 7, 'Agustus': 8, 'September': 9, 'Oktober': 10, 'November': 11, 'Desember': 12
                            };

                            // Gunakan trim() untuk membuang spasi kosong jika ada
                            params.clicked_month = monthMap[label.trim()];
                        }

                        console.log(params);

                        // Panggil fungsi untuk reload tabel saja
                        reloadTaskTable(params);
                    }
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            padding: 10,
                            fontColor: "#858796",
                            stepSize: 60,
                            callback: function(value) {
                                if (value % 60 === 0) {
                                    return (value / 60) + ' Jam';
                                }
                                return '';
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            padding: 10,
                            fontColor: "#858796",
                        },
                        gridLines: {
                            display: false, // Hilangkan garis vertikal agar lebih bersih
                            drawBorder: false
                        }
                    }]
                }
            }
        });
        
        // --- DONUT CHART ---
        const ctxDonut = document.getElementById('donutChart');
        if (donutChart) donutChart.destroy();
        donutChart = new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Progress', 'Overdue', 'Approved', 'Overdue (Done)'],
                datasets: [{
                    data: [
                        data.status_count.pending,
                        data.status_count.on_progress,
                        data.status_count.overdue,
                        data.status_count.overdue_done,
                        data.status_count.approved
                    ],
                    backgroundColor: ['#f6c23e', '#4e73df', '#e74a3b', '#1cc88a', '#8b5cf6'],
                    borderWidth: 4,
                    borderColor: '#ffffff',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                onClick: (event, elements) => {
                    if (elements.length > 0) {
                        const index = elements[0]._index;
                        const label = donutChart.data.labels[index];

                        // Map Label ke ID Status sesuai database Anda
                        const statusMap = {
                            'Pending': 0,
                            'Progress': 1,
                            'Revisi': 2,
                            'Waiting Approval': 3,
                            'Approved': 4,
                            'Overdue': 7,
                            'Overdue (Done)': '7_done' // Berikan penanda khusus untuk Overdue (Done)
                        };

                        const selectedStatus = statusMap[label];

                        // Ambil filter tanggal yang sedang aktif
                        const start = document.getElementById('start-date').value;
                        const end = document.getElementById('end-date').value;

                        // Kirim filter status ke tabel
                        reloadTaskTable({ 
                            clicked_status: selectedStatus,
                            start_date: start,
                            end_date: end
                        });
                    }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                }
            }
        });
    }

    // --- EVENT LISTENERS ---

    document.addEventListener('DOMContentLoaded', () => {
        const userFilter = document.getElementById('user-filter');
        const startDate = document.getElementById('start-date');
        const endDate = document.getElementById('end-date');

        // Load Initial Data
        loadData(userFilter.value);

        // Listen for changes
        [userFilter, startDate, endDate].forEach(el => {
            el.addEventListener('change', () => loadData(userFilter.value));
        });
    });
</script>

@include('partials.footer')