@extends('layout.app')
@section('content')
@include('partials.client_data_theme')
@include('partials.client_native_tables')
@include('partials.analysis_workspace_theme')
<style>
    .stat-card { border-left: 4px solid #161616 !important; }
    .stat-card:hover { transform: translateY(-2px) !important; }
    .stat-card .text-primary, .stat-card .text-success,
    .stat-card .text-info, .stat-card .text-warning { color: #161616 !important; }
    .chart-container { min-height: 320px; }
    .card-body canvas { max-width: 100%; }
    .facebook-analysis-header .analysis-filter-actions { display: flex; align-items: center; gap: 10px; }
    .facebook-analysis-section-title { margin-bottom: 14px; }
    .facebook-analysis-section-title h5 { margin: 0; color: #18181b; font-size: 15px; font-weight: 750; }
    .facebook-analysis-section-title p { margin: 4px 0 0; color: #a1a1aa; font-size: 12px; }
    .facebook-analysis-table-card .card-body { padding: 0 !important; }
    .facebook-analysis-table-card .facebook-analysis-section-title { margin: 0; padding: 18px 18px 14px; border-bottom: 1px solid #e4e4e7; }
    .facebook-analysis-table-card .table-responsive { border: 0 !important; border-radius: 0 !important; }
    .facebook-analysis-chart-card .card-body { padding-top: 10px !important; }
    @media (max-width: 767px) {
        .filter-group { align-items: stretch !important; }
        .filter-group > * { width: 100%; }
        .facebook-analysis-header .analysis-filter-actions { width: 100%; flex-direction: column; align-items: stretch; }
        .facebook-analysis-header .date-range-pill { margin-right: 0 !important; }
    }
</style>
<style>
    /* Styling khusus untuk card dan tabel agar lebih modern (Tanpa styling body) */
    .card { 
        border-radius: 12px; 
        border: 1px solid #edf2f7; 
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header { 
        border-top-left-radius: 12px !important; 
        border-top-right-radius: 12px !important; 
        background-color: #ffffff;
        border-bottom: 1px solid #f1f5f9;
    }
    .table thead th { 
        background-color: #f8fafc; 
        text-transform: uppercase; 
        font-size: 0.7rem; 
        font-weight: 700;
        letter-spacing: 0.05em; 
        color: #64748b;
        padding: 12px 16px;
    }

    .date-range-pill {
        display: flex;
        align-items: center;
        background-color: #f8fafc;
        border-radius: 50px;
        padding: 4px 15px;
        border: 1px solid #e2e8f0;
        gap: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .date-input-independent {
        border: none;
        background: transparent;
        font-size: 0.85rem;
        font-weight: 600;
        color: #475569;
        width: 100px;
        text-align: center;
        outline: none;
        cursor: pointer;
    }

    .date-input-independent:focus {
        color: var(--bs-primary);
    }

    .date-separator {
        color: #94a3b8;
        font-weight: bold;
    }

    /* Warna badge kustom agar lebih lembut */
    .bg-success-light { background-color: #dcfce7; color: #15803d; }
    .bg-warning-light { background-color: #fef9c3; color: #a16207; }
    .bg-danger-light { background-color: #fee2e2; color: #b91c1c; }
    .bg-info-light { background-color: #e0f2fe; color: #0369a1; }
    
    /* SANGAT PENTING: Kontainer chart harus memiliki tinggi tetap di Bootstrap */
    .chart-wrapper {
        position: relative;
        height: 350px; /* Menentukan tinggi agar chart muncul */
        width: 100%;
    }

    /* Styling tambahan untuk bagian baru */
    .progress { height: 8px; border-radius: 4px; background-color: #f1f5f9; }
    .activity-item { position: relative; padding-left: 30px; margin-bottom: 20px; }
    .activity-item::before {
        content: "";
        position: absolute;
        left: 0; top: 0;
        height: 100%; width: 2px;
        background: #e2e8f0;
    }
    .activity-dot {
        position: absolute;
        left: -4px; top: 5px;
        width: 10px; height: 10px;
        border-radius: 50%;
        background: #4f46e5;
        border: 2px solid #fff;
        z-index: 1;
    }

    /* Styling Kartu Statistik */
    .stat-card { transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    .btn-pill {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
</style>
    <div class="row mb-4 facebook-analysis-header">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-filter-left me-2 text-primary"></i>Analisis Platform Facebook</h5>
                    <!-- Pencarian Independen di Sebelah Kanan -->
                    <div class="analysis-filter-actions ms-md-auto">
                        <div class="date-range-pill me-2">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <input type="text" id="startDate" class="date-input-independent" placeholder="Mulai" value="{{ request('start_date') ?? '' }}" readonly>
                            <span class="date-separator">-</span>
                            <input type="text" id="endDate" class="date-input-independent" placeholder="Selesai" value="{{ request('end_date') ?? '' }}" readonly>
                            <button class="btn btn-sm btn-link text-danger p-0 ms-1" id="clearDates" title="Bersihkan Tanggal"><i class="bi bi-x-circle-fill"></i></button>
                        </div>
                        <button id="applyFilter" class="btn btn-primary btn-pill shadow-sm">
                            Terapkan
                        </button>
                        <button id="resetAll" class="btn btn-outline-secondary btn-pill shadow-sm">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('analisis.partials.descriptive', [
        'platform' => 'Facebook',
        'analysisPosts' => $dataBaru,
        'followerMetric' => $jumlah_follower,
    ])
    <div data-analysis-panel="metrics" class="hidden">
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-secondary mb-0 small fw-bold text-uppercase">REACH</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalReach, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-secondary mb-0 small fw-bold text-uppercase">IMPRESI</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalImpressions, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-secondary mb-0 small fw-bold text-uppercase">ENGAGEMENT</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalEngagement, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-secondary mb-0 small fw-bold text-uppercase">TOTAL FOLLOWERS</p>
                        <h4 class="mb-0 fw-bold">{{ $jumlah_follower }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Grafik performa konten -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card facebook-analysis-chart-card">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark">Performa Konten Facebook</h5>
                            <p class="text-muted small mb-0">Perbandingan reach dan impresi berdasarkan tanggal publikasi</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="mixedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail jangkauan dan interaksi -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card facebook-analysis-table-card shadow-sm border-0">
                <div class="facebook-analysis-section-title">
                    <h5>Detail Jangkauan Konten</h5>
                    <p>Ringkasan reach, impresi, reaction, komentar, dan share per posting.</p>
                </div>
                <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="insight_1">
                        <thead class="bg-light">
                            <tr class="text-secondary" style="font-size: 0.85rem;">
                                <th>No</th>
                                <th>Create Time</th>
                                <th>Embed URL</th>
                                <th>Reach</th>
                                <th>Impresi</th>
                                <th>Reaction</th>
                                <th>Comments</th>
                                <th>Shares</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.875rem;">
                            @foreach ($dataBaru as $i => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td data-order="{{ date('YmdHi', $item['timestamp']) }}">{{ $item['tanggal_posting'] }}</td>
                                <td><a href="{{ $item['link'] }}" class="text-truncate d-inline-block" style="max-width: 120px;">{{ $item['link'] }}</a></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['reach'] }}</span>
                                        <div class="progress" style="height: 8px; width: 60px; background-color: #e9ecef;">
                                            <div class="progress-bar" 
                                                role="progressbar" 
                                                style="width: {{ $maxReach > 0 ? ($item['reach'] / $maxReach) * 100 : 0}}%; background-color: #8d7462;">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['impresi'] }}</span>
                                        <div class="progress" style="height: 8px; width: 60px; background-color: #e9ecef;">
                                            <div class="progress-bar" 
                                                role="progressbar" 
                                                style="width: {{ $maxViews > 0 ? ($item['impresi'] / $maxViews) * 100 : 0}}%; background-color: #c5d386;">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['reaction'] }}</span>
                                        <div class="progress" style="height: 8px; width: 60px; background-color: #e9ecef;">
                                            <div class="progress-bar" 
                                                role="progressbar" 
                                                style="width: {{ $maxLikes > 0 ? ($item['reaction'] / $maxLikes) * 100 : 0}}%; background-color: #ff7675;">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['comments'] }}</span>
                                        <div class="progress" style="height: 8px; width: 60px; background-color: #e9ecef;">
                                            <div class="progress-bar" 
                                                role="progressbar" 
                                                style="width: {{ $maxComment > 0 ? ($item['comments'] / $maxComment) * 100 : 0}}%; background-color: #6BCFDC;">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['shares'] }}</span>
                                        <div class="progress" style="height: 8px; width: 60px; background-color: #e9ecef;">
                                            <div class="progress-bar" 
                                                role="progressbar" 
                                                style="width: {{ $maxShares > 0 ? ($item['shares'] / $maxShares) * 100 : 0}}%; background-color: #BEAFC2;">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card facebook-analysis-table-card h-100">
                <div class="facebook-analysis-section-title">
                    <h5>Detail Engagement Konten</h5>
                    <p>Perbandingan kontribusi reaction, share, komentar, dan total engagement.</p>
                </div>
                <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="insight_2">
                        <thead class="bg-light text-nowrap">
                            <tr class="text-secondary" style="font-size: 0.85rem;">
                                <th>No</th>
                                <th>Create Time (Y-M-D)</th>
                                <th>Embed URL</th>
                                <th>Reaction</th>
                                <th>Shares</th>
                                <th>Comment</th>
                                <th>Engangements</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.875rem;">
                            @foreach ($dataBaru as $i => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td data-order="{{ date('YmdHi', $item['timestamp']) }}">{{ $item['tanggal_posting'] }}</td>
                                <td><a href="{{ $item['link'] }}" class="text-truncate d-inline-block" style="max-width: 120px;">{{ $item['link'] }}</a></td>
                                <td style="background-color: rgba(141, 116, 98, {{ $maxLikes ? ($item['reaction'] / $maxLikes) * 0.5 : 0}})">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['reaction'] }}</span>
                                    </div>
                                </td>

                                <td style="background-color: rgba(186, 204, 129, {{ $maxShares ? ($item['shares'] / $maxShares) * 0.5 : 0}})">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['shares'] }}</span>
                                    </div>
                                </td>

                                <td style="background-color: rgba(255, 107, 107, {{ $maxComment ? ($item['comments'] / $maxComment) * 0.5 : 0}})">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['comments'] }}</span>
                                    </div>
                                </td>

                                <td style="background-color: rgba(164, 225, 233, {{ $maxEngangement ? ($item['engagement'] / $maxEngangement) * 0.5 : 0}})">
                                    <div class="d-flex align-items-center">
                                        <span class="me-2" style="min-width: 25px;">{{ $item['engagement'] }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allPosts = @json($dataBaru);
        const ctx = document.getElementById('mixedChart').getContext('2d');
        let myChart;

        const loader = document.getElementById('loadingOverlay');
        const barFill = document.getElementById('progressBar');
        const barPercent = document.getElementById('progressPercentage');
        const statusText = document.getElementById('loadingStatus');
        function startEnhancedLoading() {
            loader.style.display = 'flex';
            document.body.classList.add('loading-active');
            
            let progress = 0;
            const phases = [
                { limit: 30, text: "Menghubungkan ke API Meta..." },
                { limit: 60, text: "Mengunduh Data Konten..." },
                { limit: 98, text: "Finalisasi Tampilan..." }
            ];

            let currentPhase = 0;
            const interval = setInterval(() => {
                if (progress < phases[currentPhase].limit) {
                    progress += Math.random() * 1.5;
                } else if (currentPhase < phases.length - 1) {
                    currentPhase++;
                    statusText.innerText = phases[currentPhase].text;
                }
                
                const rounded = Math.floor(progress);
                barFill.style.width = rounded + "%";
                barPercent.innerText = rounded + "%";
                
                if (progress >= 100) clearInterval(interval);
            }, 150);
        }

        // Render Chart
        function renderChart(data) {
            if (!ctx || typeof Chart === 'undefined') return;
            if (myChart) myChart.destroy();
            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(i => i.tanggal_posting),
                    datasets: [
                        { label: 'Reach', data: data.map(i => i.reach), backgroundColor: '#4f46e5', order: 2, borderRadius: 4 },
                        { label: 'Impressions', data: data.map(i => i.impresi), type: 'line', borderColor: '#fb7185', fill: false, tension: 0.4, order: 1 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        window.addEventListener('analysis:tab-changed', function(event) {
            if (event.detail?.active !== 'metrics') return;
            requestAnimationFrame(function() {
                if (myChart) {
                    myChart.resize();
                } else {
                    renderChart(allPosts);
                }
            });
        });

        // 3. Flatpickr
        const startP = flatpickr("#startDate", { dateFormat: "d M Y", animate: false, disableMobile: true });
        const endP = flatpickr("#endDate", { dateFormat: "d M Y", animate: false, disableMobile: true });

        // 4. Tombol Terapkan & Reset dengan Loading
        document.getElementById('applyFilter').addEventListener('click', function() {
            const s = startP.input.value;
            const e = endP.input.value;
            if (s && e) {
                startEnhancedLoading(); // Aktifkan loading
                window.location.href = `?start_date=${s}&end_date=${e}`;
            }
        });

        document.getElementById('resetAll').addEventListener('click', function() {
            startEnhancedLoading(); // Aktifkan loading
            window.location.href = window.location.pathname;
        });

        if (!document.querySelector('[data-analysis-panel="metrics"]')?.classList.contains('hidden')) {
            renderChart(allPosts);
        }
    });
</script>
