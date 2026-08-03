<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="robots" content="noindex, nofollow">

    <title>ERP | {{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.jpg') }}">

    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #accordionSidebar {
            position: fixed;
            width: 260px; /* Nilai default */
            height: 100vh;
            z-index: 1050;
            padding-top: 20px;
            /* Hapus transition global agar tidak konflik saat di-resize */
            transition: width 0.3s, background-color 0.3s; 
            overflow-x: hidden;
        }

        #accordionSidebar::-webkit-scrollbar {
            display: none;
        }

        .collapse-inner .collapse-item {
            margin-right: 15px !important; /* Jarak dari batas kanan */
            margin-left: 10px !important;  /* Jarak dari garis/batas kiri */
            border-radius: 0.35rem;        /* Membuat sudut menu lebih lembut saat di-hover */
            display: block;
            padding: 0.5rem 1rem !important;
        }

        #content-wrapper {
            width: 100%;
            /* margin-left harus sinkron dengan lebar sidebar */
            margin-left: 260px; 
            min-height: 100vh;
            background-color: #f8fafc;
            transition: margin-left 0.3s;
        }

        .collapse-inner {
            max-height: 60vh;
            overflow-y: auto;
        }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        body.loading-active {
            overflow: hidden !important;
        }
        .spinner-modern {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .progress-estimation {
            height: 6px;
            width: 250px;
            background-color: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 15px;
        }
        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: #4f46e5;
            transition: width 0.4s ease;
        }
    </style>
</head>
<body id="page-top">
     <div id="loadingOverlay">
        <div class="spinner-modern mb-3"></div>
        <h6 class="fw-bold text-dark mb-1" id="loadingStatus">Menyiapkan Data...</h6>
        <p class="text-muted small mb-0" id="loadingSubtext"></p>
        <div class="progress-estimation">
            <div class="progress-bar-fill" id="progressBar"></div>
        </div>
        <small class="text-primary fw-bold mt-2" id="progressPercentage">0%</small>
    </div>
    <div id="wrapper">
        @include('partials.sidebar_new')

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('partials.navbar')

                <div class="container-fluid">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2021</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left py-3 px-4 rounded-lg bg-gold text-black font-bold shadow-lg shadow-gold/20 transition duration-200">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <!-- 2. Bootstrap & Plugins lainnya -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- 3. DataTables Core (PENTING: Harus sebelum file demo/custom Anda) -->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.rawgit.com/ashl1/datatables-rowsgroup/v1.0.0/dataTables.rowsGroup.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <!-- 4. Script Custom Anda -->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('js/demo/datatables-demo.js') }}"></script> <!-- Jika masih error, hapus baris ini -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/split.js/dist/split.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <script src="{{ asset('js/produk/datatable.js') }}"></script>
    <script src="{{ asset('js/produk/create.js') }}"></script>
    <script src="{{ asset('js/produk/edit.js') }}"></script>
    <script src="{{ asset('js/produk/delete.js') }}"></script>
    <script src="{{ asset('js/produk/produk.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
            const sidebarLinks = document.querySelectorAll('#accordionSidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    // Pemicu loading untuk link yang menuju halaman/client analisis
                    if (href && href !== '#' && !this.hasAttribute('data-toggle')) {
                        startEnhancedLoading()
                    }
                });
            });
        });

        function hitungJamSelesaiManual(jamMulai, durasiMenit) {
            if (!jamMulai) return "00:00";
            const [jam, menit] = jamMulai.split(':').map(Number);
            const totalMenit = (jam * 60) + menit + parseInt(durasiMenit || 0);
            console.log(totalMenit);
            
            const h = Math.floor(totalMenit / 60);
            const m = totalMenit % 60;
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        }

        function formatDurasi(menit) {
            if (!menit || menit <= 0) return "0 Menit";
            let jam = Math.floor(menit / 60);
            let sisaMenit = menit % 60;
            if (jam > 0) {
                return sisaMenit > 0 ? `${jam} Jam ${sisaMenit} Menit` : `${jam} Jam`;
            } else {
                return `${menit} Menit`;
            }
        }
        
        $(document).ready(function() {
            var tableId = '#target_audience';

            // 1. Hancurkan instance lama jika ada (mencegah error reinitialise)
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }

            // 2. Inisialisasi dengan RowsGroup
            $(tableId).DataTable({
                responsive: true,
                pageLength: 25, // Satu produk = 5 baris, jadi 25 baris = 5 produk
                ordering: false, // Wajib FALSE agar baris profile tidak terpisah
                autoWidth: false,
                rowsGroup: [
                    0, // Kolom No
                    1, // Kolom Produk
                    2, // Kolom Indikator
                    5, // Kolom Gaya Hidup
                    6, // Kolom Status Sosial
                    7, // Kolom Penggunaan Produk
                    8,  // Kolom Manfaat
                    9
                ],
                columnDefs: [
                    {
                        targets: [0, 1, 2, 5, 6, 7, 8],
                        className: 'text-center align-middle' // Teks di tengah-tengah kotak besar
                    }
                ],
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
            });
        });
    </script>
    @stack('script')
</body>
</html>