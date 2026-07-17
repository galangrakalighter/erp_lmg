@extends('layout.app')
<!-- Begin Page Content -->
@section('content')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Visi & Misi Section -->
    <div class="row">
        <!-- Visi Card -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">Visi</div>
                            <div class="font-weight-bold text-gray-800" style="line-height: 1.6;">
                                "Menjadi ekosistem media digital terintegrasi yang profesional dan terpercaya untuk mendorong pertumbuhan bisnis klien secara terukur dan berkelanjutan di Indonesia."
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Misi Card -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-3">Misi</div>
                            <ul class="list-unstyled mb-0 text-gray-800" style="line-height: 1.6;">
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Mengintegrasikan media, layanan spesialis, teknologi, dan commerce dalam satu sistem kerja.</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Menjadikan media sebagai aset <i>traffic</i> dan <i>trust</i> untuk pertumbuhan bisnis klien.</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Mengembangkan strategi berbasis data, riset, dan performa yang terukur.</li>
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> Memanfaatkan teknologi dan AI untuk efisiensi dan skalabilitas.</li>
                                <li><i class="fas fa-check-circle text-success mr-2"></i> Membangun tim spesialis yang solid untuk eksekusi berkualitas.</li>
                            </ul>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rocket fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection