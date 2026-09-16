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

    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">

    <link href="{{ asset('vendor-local/select2/select2.min.css') }}" rel="stylesheet">
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
    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        @include('partials.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                @include('partials.navbar')
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif
                <!-- End of Topbar -->
