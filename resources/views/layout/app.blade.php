<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="description" content="ERP - Lighter Media Group">
    <meta name="author" content="Lighter Media Group">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ERP | {{ $title ?? 'Dashboard' }}</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.jpg') }}">

    {{-- =========================================================
        TAILWIND / VITE
    ========================================================== --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- =========================================================
        THIRD PARTY CSS
        Bootstrap/SB Admin CSS SUDAH DIHAPUS
    ========================================================== --}}

    {{-- Select2 --}}
    <link
        href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
        rel="stylesheet"
    />

    {{-- Flatpickr --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.css"
    >

    {{-- Choices --}}
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css"
    />

    {{-- =========================================================
        CUSTOM GLOBAL STYLE
        LMG / PREMIUM EDITORIAL STYLE
    ========================================================== --}}
    <style>
        :root {
            --lmg-black: #09090b;
            --lmg-dark: #111113;
            --lmg-white: #fafafa;
            --lmg-muted: #71717a;
            --lmg-border: #e4e4e7;
            --lmg-soft: #f4f4f5;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: #f5f5f3;
            color: #18181b;
            font-family:
                'Instrument Sans',
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        body.loading-active {
            overflow: hidden !important;
        }

        /* =====================================================
           SCROLLBAR
        ====================================================== */

        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #d4d4d8;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1aa;
        }

        /* =====================================================
           SIDEBAR
        ====================================================== */

        #accordionSidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 270px;
            height: 100vh;
            z-index: 1050;

            background: #09090b;
            color: #fff;

            overflow-x: hidden;
            overflow-y: auto;

            border-right: 1px solid rgba(255, 255, 255, 0.08);

            transition:
                width 0.3s ease,
                transform 0.3s ease;
        }

        #accordionSidebar::-webkit-scrollbar {
            width: 0;
        }

        /* =====================================================
           MAIN CONTENT
        ====================================================== */

        #content-wrapper {
            width: calc(100% - 270px);
            margin-left: 270px;
            min-height: 100vh;

            background: #f5f5f3;

            transition:
                margin-left 0.3s ease,
                width 0.3s ease;
        }

        #content {
            min-height: calc(100vh - 80px);
        }

        /* =====================================================
           SIDEBAR LINK COMPATIBILITY
        ====================================================== */

        .collapse-inner {
            max-height: 60vh;
            overflow-y: auto;
        }

        .collapse-inner .collapse-item {
            display: flex;
            align-items: center;

            margin: 3px 10px;
            padding: 10px 14px;

            border-radius: 10px;

            color: #a1a1aa;
            text-decoration: none;

            transition:
                background-color 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }

        .collapse-inner .collapse-item:hover {
            background: rgba(255, 255, 255, 0.07);
            color: #fff;
        }

        /* =====================================================
           LMG NAVBAR
        ====================================================== */

        .lmg-navbar {
            position: sticky;
            top: 0;
            z-index: 900;

            min-height: 76px;

            background: rgba(250, 250, 250, 0.88);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border-bottom: 1px solid rgba(228, 228, 231, 0.9);
        }

        /* =====================================================
           CONTENT CONTAINER
        ====================================================== */

        .lmg-content {
            width: 100%;
            max-width: 1800px;
            margin: 0 auto;

            padding: 28px 32px;
        }

        /* =====================================================
           CARDS
        ====================================================== */

        .lmg-card {
            background: #fff;

            border: 1px solid #e4e4e7;
            border-radius: 16px;

            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.02);

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                transform 0.2s ease;
        }

        .lmg-card:hover {
            border-color: #d4d4d8;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.04);
        }

        /* =====================================================
           FORM ELEMENTS
        ====================================================== */

        input,
        textarea,
        select {
            font-family: inherit;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
        }

        /* =====================================================
           SELECT2
        ====================================================== */

        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            height: 42px !important;

            border: 1px solid #d4d4d8 !important;
            border-radius: 10px !important;

            background: #fff !important;
        }

        .select2-container
        .select2-selection--single
        .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 13px !important;

            color: #18181b !important;
            font-size: 14px;
        }

        .select2-container
        .select2-selection--single
        .select2-selection__arrow {
            height: 40px !important;
        }

        .select2-dropdown {
            border: 1px solid #d4d4d8 !important;
            border-radius: 10px !important;

            overflow: hidden;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.08);
        }

        .select2-results__option {
            padding: 9px 12px;
            font-size: 14px;
        }

        .select2-results__option--highlighted {
            background: #09090b !important;
        }

        /* =====================================================
           CHOICES.JS
        ====================================================== */

        .choices {
            margin-bottom: 0;
        }

        .choices__inner {
            min-height: 42px;

            padding: 5px 10px;

            background: #fff;

            border: 1px solid #d4d4d8;
            border-radius: 10px;

            font-size: 14px;
        }

        .choices.is-focused .choices__inner,
        .choices.is-open .choices__inner {
            border-color: #18181b;
        }

        /* =====================================================
           DATATABLE
        ====================================================== */

        .dataTables_wrapper {
            color: #3f3f46;
            font-size: 14px;
        }

        .dataTables_wrapper select,
        .dataTables_wrapper input {
            min-height: 38px;

            padding: 7px 11px;

            background: #fff;

            border: 1px solid #d4d4d8;
            border-radius: 9px;

            color: #18181b;
        }

        .dataTables_wrapper select:focus,
        .dataTables_wrapper input:focus {
            border-color: #18181b;
            box-shadow: 0 0 0 3px rgba(24, 24, 27, 0.08);
        }

        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0;
        }

        table.dataTable thead th {
            background: #fafafa;
            color: #52525b;

            border-bottom: 1px solid #e4e4e7 !important;

            font-size: 11px;
            font-weight: 700;

            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        table.dataTable tbody td {
            border-bottom: 1px solid #f0f0f1 !important;
        }

        table.dataTable tbody tr:hover {
            background: #fafafa;
        }

        .dataTables_info {
            color: #71717a !important;
        }

        .dataTables_paginate .paginate_button {
            border-radius: 8px !important;
            border: 1px solid transparent !important;

            color: #52525b !important;
        }

        .dataTables_paginate .paginate_button:hover {
            background: #f4f4f5 !important;
            border-color: #e4e4e7 !important;

            color: #18181b !important;
        }

        .dataTables_paginate .paginate_button.current {
            background: #09090b !important;
            border-color: #09090b !important;

            color: #fff !important;
        }

        /* =====================================================
           ALERT
        ====================================================== */

        .lmg-alert {
            position: relative;

            display: flex;
            align-items: flex-start;
            gap: 12px;

            padding: 14px 16px;

            margin-bottom: 20px;

            border: 1px solid;
            border-radius: 12px;

            font-size: 14px;
        }

        .lmg-alert-success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .lmg-alert-error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .lmg-alert-close {
            margin-left: auto;

            border: 0;
            background: transparent;

            color: currentColor;

            font-size: 18px;
            line-height: 1;

            cursor: pointer;
        }

        /* =====================================================
           LOADING OVERLAY
        ====================================================== */

        #loadingOverlay {
            position: fixed;
            inset: 0;

            z-index: 9999;

            display: none;

            align-items: center;
            justify-content: center;
            flex-direction: column;

            background: rgba(250, 250, 250, 0.82);

            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .loading-box {
            width: min(420px, calc(100vw - 40px));

            padding: 30px;

            background: rgba(255, 255, 255, 0.95);

            border: 1px solid #e4e4e7;
            border-radius: 18px;

            text-align: center;

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.10);
        }

        .spinner-modern {
            width: 42px;
            height: 42px;

            margin: 0 auto 18px;

            border: 3px solid #e4e4e7;
            border-top-color: #09090b;

            border-radius: 999px;

            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .progress-estimation {
            width: 100%;
            height: 5px;

            margin-top: 20px;

            overflow: hidden;

            background: #e4e4e7;
            border-radius: 999px;
        }

        .progress-bar-fill {
            width: 0%;
            height: 100%;

            background: #09090b;

            transition: width 0.4s ease;
        }

        /* =====================================================
           LOGOUT MODAL
        ====================================================== */

        #logoutModal {
            position: fixed;
            inset: 0;

            z-index: 9998;

            display: none;

            align-items: center;
            justify-content: center;

            padding: 20px;

            background: rgba(9, 9, 11, 0.55);

            backdrop-filter: blur(5px);
        }

        #logoutModal.active {
            display: flex;
        }

        .logout-dialog {
            width: 100%;
            max-width: 430px;

            background: #fff;

            border: 1px solid #e4e4e7;
            border-radius: 18px;

            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.20);

            overflow: hidden;

            animation: modalIn 0.2s ease-out;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* =====================================================
           AI ASSISTANT
        ====================================================== */

        .floating-ai-btn {
            position: fixed;

            right: 24px;
            bottom: 24px;

            width: 56px;
            height: 56px;

            z-index: 1100;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 0;
            border-radius: 999px;

            background: #09090b;
            color: #fff;

            cursor: pointer;

            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.20);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }

        .floating-ai-btn:hover {
            transform: translateY(-3px);

            background: #18181b;

            box-shadow:
                0 18px 40px rgba(0, 0, 0, 0.25);
        }

        .floating-ai-btn::after {
            content: "";

            position: absolute;
            inset: -5px;

            border: 1px solid rgba(9, 9, 11, 0.15);
            border-radius: 999px;

            animation: aiPulse 2.5s infinite;
        }

        @keyframes aiPulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }

            100% {
                transform: scale(1.35);
                opacity: 0;
            }
        }

        .ai-assistant-panel {
            position: fixed;

            right: 24px;
            bottom: 95px;

            width: 380px;
            height: 560px;

            z-index: 1099;

            display: none;
            flex-direction: column;

            background: #fff;

            border: 1px solid #e4e4e7;
            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.16);

            animation: aiOpen 0.25s ease;
        }

        .ai-assistant-panel.active {
            display: flex;
        }

        @keyframes aiOpen {
            from {
                opacity: 0;
                transform: translateY(15px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .ai-header {
            padding: 16px 18px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            background: #09090b;
            color: #fff;

            border-bottom: 1px solid #27272a;
        }

        .ai-header strong {
            display: block;

            font-size: 14px;
            font-weight: 700;

            letter-spacing: -0.01em;
        }

        .ai-header small {
            display: block;

            margin-top: 2px;

            color: #a1a1aa;

            font-size: 11px;
        }

        .ai-header button {
            width: 32px;
            height: 32px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 1px solid #3f3f46;
            border-radius: 8px;

            background: transparent;
            color: #fff;

            cursor: pointer;

            transition: background 0.2s ease;
        }

        .ai-header button:hover {
            background: #27272a;
        }

        .ai-messages {
            flex: 1;

            padding: 16px;

            overflow-y: auto;

            background: #f7f7f6;
        }

        .ai-message {
            width: fit-content;
            max-width: 88%;

            margin-bottom: 10px;

            padding: 10px 13px;

            background: #fff;

            border: 1px solid #e4e4e7;
            border-radius: 12px;

            color: #3f3f46;

            font-size: 13px;
            line-height: 1.55;

            box-shadow:
                0 1px 2px rgba(0, 0, 0, 0.03);

            white-space: pre-line;
        }

        .ai-quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;

            padding: 10px 12px;

            background: #fff;

            border-top: 1px solid #e4e4e7;
        }

        .quick-btn {
            padding: 6px 9px;

            background: #f4f4f5;
            color: #27272a;

            border: 1px solid #e4e4e7;
            border-radius: 7px;

            font-size: 11px;

            cursor: pointer;

            transition:
                background 0.2s ease,
                border-color 0.2s ease;
        }

        .quick-btn:hover {
            background: #e4e4e7;
            border-color: #d4d4d8;
        }

        .ai-input-area {
            display: flex;
            align-items: flex-end;
            gap: 8px;

            padding: 10px;

            background: #fff;

            border-top: 1px solid #e4e4e7;
        }

        .ai-input-area textarea {
            flex: 1;

            min-height: 42px;
            max-height: 120px;

            resize: none;

            padding: 10px 12px;

            background: #fafafa;

            border: 1px solid #d4d4d8;
            border-radius: 10px;

            color: #18181b;

            font-family: inherit;
            font-size: 13px;
        }

        .ai-input-area textarea:focus {
            background: #fff;
            border-color: #18181b;

            box-shadow:
                0 0 0 3px rgba(24, 24, 27, 0.06);
        }

        #sendAiMessage {
            width: 42px;
            height: 42px;

            flex-shrink: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 0;
            border-radius: 10px;

            background: #09090b;
            color: #fff;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        #sendAiMessage:hover {
            background: #27272a;
            transform: translateY(-1px);
        }

        /* =====================================================
           FOOTER
        ====================================================== */

        .lmg-footer {
            padding: 22px 32px;

            background: #f5f5f3;

            border-top: 1px solid #e4e4e7;
        }

        /* =====================================================
           MOBILE
        ====================================================== */

        @media (max-width: 1024px) {

            #accordionSidebar {
                width: 270px;

                transform: translateX(-100%);
            }

            #accordionSidebar.show {
                transform: translateX(0);
            }

            #content-wrapper {
                width: 100%;
                margin-left: 0;
            }

            .lmg-content {
                padding: 22px 20px;
            }

            .lmg-footer {
                padding: 20px;
            }
        }

        @media (max-width: 640px) {

            .lmg-content {
                padding: 18px 14px;
            }

            .lmg-footer {
                padding: 18px 14px;
            }

            .floating-ai-btn {
                right: 16px;
                bottom: 16px;
            }

            .ai-assistant-panel {
                right: 12px;
                left: 12px;
                bottom: 84px;

                width: auto;
                height: min(70vh, 560px);
            }

            .loading-box {
                padding: 24px;
            }
        }
    </style>

    @stack('styles')
</head>


<body id="page-top" class="min-h-screen bg-[#f5f5f3] text-zinc-900">

    {{-- =========================================================
        LOADING OVERLAY
    ========================================================== --}}
    <div id="loadingOverlay">

        <div class="loading-box">

            <div class="spinner-modern"></div>

            <h6
                id="loadingStatus"
                class="mb-1 text-sm font-semibold tracking-tight text-zinc-900"
            >
                Menyiapkan Data...
            </h6>

            <p
                id="loadingSubtext"
                class="mb-0 text-xs text-zinc-500"
            ></p>

            <div class="progress-estimation">
                <div
                    class="progress-bar-fill"
                    id="progressBar"
                ></div>
            </div>

            <div
                id="progressPercentage"
                class="mt-2 text-xs font-semibold text-zinc-900"
            >
                0%
            </div>

        </div>

    </div>


    {{-- =========================================================
        MAIN WRAPPER
    ========================================================== --}}
    <div id="wrapper" class="min-h-screen">

        {{-- =====================================================
            SIDEBAR
            File sidebar juga sebaiknya sudah Tailwind
        ====================================================== --}}
        @include('partials.sidebar_new')


        {{-- =====================================================
            CONTENT WRAPPER
        ====================================================== --}}
        <div
            id="content-wrapper"
            class="flex min-h-screen flex-col"
        >

            <div id="content" class="flex-1">

                {{-- =================================================
                    NAVBAR
                ================================================== --}}
                @include('partials.navbar')


                {{-- =================================================
                    MAIN CONTENT
                ================================================== --}}
                <main class="lmg-content">

                    {{-- ERROR --}}
                    @if (session('error'))

                        <div
                            id="alert-error"
                            class="lmg-alert lmg-alert-error"
                            role="alert"
                        >

                            <svg
                                class="mt-0.5 h-5 w-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>

                            <div class="flex-1">
                                {{ session('error') }}
                            </div>

                            <button
                                type="button"
                                class="lmg-alert-close"
                                onclick="document.getElementById('alert-error')?.remove()"
                                aria-label="Close"
                            >
                                &times;
                            </button>

                        </div>

                    @endif


                    {{-- SUCCESS --}}
                    @if (session('success'))

                        <div
                            id="alert-success"
                            class="lmg-alert lmg-alert-success"
                            role="alert"
                        >

                            <svg
                                class="mt-0.5 h-5 w-5 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            <div class="flex-1">
                                {{ session('success') }}
                            </div>

                            <button
                                type="button"
                                class="lmg-alert-close"
                                onclick="document.getElementById('alert-success')?.remove()"
                                aria-label="Close"
                            >
                                &times;
                            </button>

                        </div>

                    @endif


                    {{-- =================================================
                        PAGE CONTENT
                    ================================================== --}}
                    @yield('content')

                </main>

            </div>


            {{-- =====================================================
                FOOTER
            ====================================================== --}}
            <footer class="lmg-footer">

                <div
                    class="flex flex-col gap-2 text-xs text-zinc-400 sm:flex-row sm:items-center sm:justify-between"
                >

                    <span>
                        &copy; {{ date('Y') }}
                        <span class="font-medium text-zinc-500">
                            Lighter Media Group
                        </span>
                        . All rights reserved.
                    </span>

                    <span class="font-semibold tracking-[0.14em] text-zinc-400">
                        INTEGRATED DIGITAL ECOSYSTEM
                    </span>

                </div>

            </footer>

        </div>

    </div>


    {{-- =========================================================
        LOGOUT MODAL
    ========================================================== --}}
    <div
        id="logoutModal"
        aria-hidden="true"
    >

        <div class="logout-dialog">

            {{-- Header --}}
            <div
                class="flex items-center justify-between border-b border-zinc-200 px-6 py-5"
            >

                <div>

                    <p
                        class="mb-1 text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400"
                    >
                        Account
                    </p>

                    <h5
                        class="text-lg font-semibold tracking-tight text-zinc-900"
                    >
                        Ready to leave?
                    </h5>

                </div>

                <button
                    type="button"
                    id="closeLogoutModal"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900"
                    aria-label="Close"
                >

                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>

                </button>

            </div>


            {{-- Body --}}
            <div class="px-6 py-6">

                <p class="text-sm leading-6 text-zinc-500">
                    Anda akan keluar dari sistem ERP.
                    Pastikan seluruh pekerjaan Anda sudah tersimpan.
                </p>

            </div>


            {{-- Footer --}}
            <div
                class="flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4"
            >

                <button
                    type="button"
                    id="cancelLogout"
                    class="rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
                >
                    Cancel
                </button>


                <form
                    action="{{ route('logout') }}"
                    method="POST"
                >
                    @csrf

                    <button
                        type="submit"
                        class="rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                    >
                        Logout
                    </button>

                </form>

            </div>

        </div>

    </div>


    {{-- =========================================================
        JAVASCRIPT LIBRARIES
        Bootstrap JS TIDAK DIGUNAKAN LAGI
    ========================================================== --}}

    {{-- Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    {{-- jQuery
         Tetap dipertahankan karena DataTables / Select2
         dan beberapa halaman ERP Anda masih menggunakannya. --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Moment --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    {{-- Flatpickr --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- DataTables Core --}}
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>

    {{-- RowsGroup --}}
    <script src="https://cdn.rawgit.com/ashl1/datatables-rowsgroup/v1.0.0/dataTables.rowsGroup.js"></script>

    {{-- Choices --}}
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Split.js --}}
    <script src="https://cdn.jsdelivr.net/npm/split.js/dist/split.min.js"></script>

    {{-- PDF.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>


    {{-- =========================================================
        PRODUCT SCRIPTS
    ========================================================== --}}
    <script src="{{ asset('js/produk/datatable.js') }}"></script>
    <script src="{{ asset('js/produk/create.js') }}"></script>
    <script src="{{ asset('js/produk/edit.js') }}"></script>
    <script src="{{ asset('js/produk/delete.js') }}"></script>
    <script src="{{ asset('js/produk/produk.js') }}"></script>


    {{-- =========================================================
        GLOBAL SCRIPT
    ========================================================== --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            /* =====================================================
               LOADING
            ====================================================== */

            const loader = document.getElementById('loadingOverlay');
            const barFill = document.getElementById('progressBar');
            const barPercent = document.getElementById('progressPercentage');
            const statusText = document.getElementById('loadingStatus');

            function startEnhancedLoading() {

                if (!loader) return;

                loader.style.display = 'flex';

                document.body.classList.add('loading-active');

                let progress = 0;

                const phases = [
                    {
                        limit: 30,
                        text: "Menghubungkan ke API Meta..."
                    },
                    {
                        limit: 60,
                        text: "Mengunduh Data Konten..."
                    },
                    {
                        limit: 98,
                        text: "Finalisasi Tampilan..."
                    }
                ];

                let currentPhase = 0;

                const interval = setInterval(() => {

                    if (progress < phases[currentPhase].limit) {

                        progress += Math.random() * 1.5;

                    } else if (currentPhase < phases.length - 1) {

                        currentPhase++;

                        if (statusText) {
                            statusText.innerText =
                                phases[currentPhase].text;
                        }

                    }

                    const rounded = Math.min(
                        Math.floor(progress),
                        99
                    );

                    if (barFill) {
                        barFill.style.width = rounded + '%';
                    }

                    if (barPercent) {
                        barPercent.innerText = rounded + '%';
                    }

                    if (progress >= 99) {
                        clearInterval(interval);
                    }

                }, 150);
            }


            /* =====================================================
               SIDEBAR LINK LOADING
            ====================================================== */

            const sidebarLinks =
                document.querySelectorAll(
                    '#accordionSidebar a'
                );

            sidebarLinks.forEach(link => {

                link.addEventListener('click', function (e) {

                    const href =
                        this.getAttribute('href');

                    if (
                        href &&
                        href !== '#' &&
                        !this.hasAttribute('data-toggle')
                    ) {

                        startEnhancedLoading();

                    }

                });

            });


            /* =====================================================
               LOGOUT MODAL
            ====================================================== */

            const logoutModal =
                document.getElementById('logoutModal');

            const closeLogoutModal =
                document.getElementById('closeLogoutModal');

            const cancelLogout =
                document.getElementById('cancelLogout');


            function openLogoutModal() {

                if (!logoutModal) return;

                logoutModal.classList.add('active');

                logoutModal.setAttribute(
                    'aria-hidden',
                    'false'
                );

                document.body.classList.add(
                    'overflow-hidden'
                );
            }


            function hideLogoutModal() {

                if (!logoutModal) return;

                logoutModal.classList.remove('active');

                logoutModal.setAttribute(
                    'aria-hidden',
                    'true'
                );

                document.body.classList.remove(
                    'overflow-hidden'
                );
            }


            if (closeLogoutModal) {

                closeLogoutModal.addEventListener(
                    'click',
                    hideLogoutModal
                );

            }


            if (cancelLogout) {

                cancelLogout.addEventListener(
                    'click',
                    hideLogoutModal
                );

            }


            if (logoutModal) {

                logoutModal.addEventListener(
                    'click',
                    function (event) {

                        if (
                            event.target ===
                            logoutModal
                        ) {
                            hideLogoutModal();
                        }

                    }
                );

            }


            document.addEventListener(
                'keydown',
                function (event) {

                    if (
                        event.key === 'Escape' &&
                        logoutModal?.classList.contains('active')
                    ) {

                        hideLogoutModal();

                    }

                }
            );


            /* =====================================================
               GLOBAL LOGOUT TRIGGER
               Mendukung:
               - #logoutButton
               - [data-logout]
               - .logout-trigger
            ====================================================== */

            document
                .querySelectorAll(
                    '#logoutButton, [data-logout], .logout-trigger'
                )
                .forEach(button => {

                    button.addEventListener(
                        'click',
                        function (event) {

                            event.preventDefault();

                            openLogoutModal();

                        }
                    );

                });

        });


        /* =========================================================
           HELPER DURASI
        ========================================================== */

        function hitungJamSelesaiManual(
            jamMulai,
            durasiMenit
        ) {

            if (!jamMulai) {
                return "00:00";
            }

            const [
                jam,
                menit
            ] = jamMulai
                .split(':')
                .map(Number);

            const totalMenit =
                (jam * 60) +
                menit +
                parseInt(
                    durasiMenit || 0
                );

            const h =
                Math.floor(
                    totalMenit / 60
                );

            const m =
                totalMenit % 60;

            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        }


        function formatDurasi(menit) {

            if (!menit || menit <= 0) {
                return "0 Menit";
            }

            let jam =
                Math.floor(menit / 60);

            let sisaMenit =
                menit % 60;

            if (jam > 0) {

                return sisaMenit > 0
                    ? `${jam} Jam ${sisaMenit} Menit`
                    : `${jam} Jam`;

            }

            return `${menit} Menit`;
        }


        /* =========================================================
           DATATABLE TARGET AUDIENCE
        ========================================================== */

        $(document).ready(function () {

            const tableId =
                '#target_audience';


            if (
                $(tableId).length &&
                $.fn.DataTable.isDataTable(tableId)
            ) {

                $(tableId)
                    .DataTable()
                    .destroy();

            }


            if ($(tableId).length) {

                $(tableId).DataTable({

                    responsive: true,

                    pageLength: 25,

                    ordering: false,

                    autoWidth: false,

                    rowsGroup: [
                        0,
                        1,
                        2,
                        5,
                        6,
                        7,
                        8,
                        9
                    ],

                    columnDefs: [
                        {
                            targets: [
                                0,
                                1,
                                2,
                                5,
                                6,
                                7,
                                8
                            ],

                            className:
                                'text-center align-middle'
                        }
                    ],

                    dom:
                        '<"flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4"lf>' +
                        'rt' +
                        '<"flex flex-col gap-3 md:flex-row md:items-center md:justify-between mt-4"ip>'

                });

            }

        });

    </script>


    {{-- =========================================================
        AI ASSISTANT
    ========================================================== --}}

    <button
        id="floatingAiButton"
        class="floating-ai-btn"
        type="button"
        title="AI Assistant"
        aria-label="Open AI Assistant"
    >

        <svg
            class="h-5 w-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.8"
                d="M9.5 3.5h5a3 3 0 013 3v5a3 3 0 01-3 3h-5a3 3 0 01-3-3v-5a3 3 0 013-3z"
            />

            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.8"
                d="M8 14.5l-2 3 4-1.5M16 14.5l2 3-4-1.5M9.5 8.5h.01M14.5 8.5h.01M10 11h4"
            />
        </svg>

    </button>


    {{-- =========================================================
        AI PANEL
    ========================================================== --}}

    <div
        id="aiAssistantPanel"
        class="ai-assistant-panel"
    >

        {{-- Header --}}
        <div class="ai-header">

            <div>

                <strong>
                    AI Assistant
                </strong>

                <small>
                    Siap membantu Anda
                </small>

            </div>


            <button
                type="button"
                id="closeAiPanel"
                aria-label="Close AI Assistant"
            >

                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>

            </button>

        </div>


        {{-- Messages --}}
        <div
            id="aiMessages"
            class="ai-messages"
        >

            <div class="ai-message">
                Halo 👋 Ada yang bisa saya bantu?
            </div>

        </div>


        {{-- Quick Replies --}}
        <div class="ai-quick-replies">

            <button
                type="button"
                class="quick-btn"
                data-is-search="true"
                data-template="Mencari affiliator:
Minimal follower:
Maximal follower:
Target:"
            >
                🔍 Mencari Affiliator
            </button>

        </div>


        {{-- Input --}}
        <div class="ai-input-area">

            <textarea
                id="aiInput"
                placeholder="Tanyakan sesuatu..."
                rows="3"
                autocomplete="off"
            ></textarea>


            <button
                type="button"
                id="sendAiMessage"
                aria-label="Send message"
            >

                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M22 2L11 13"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M22 2l-7 20-4-9-9-4 20-7z"
                    />
                </svg>

            </button>

        </div>

    </div>


    {{-- =========================================================
        AI JAVASCRIPT
    ========================================================== --}}

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const aiButton =
                    document.getElementById(
                        'floatingAiButton'
                    );

                const aiPanel =
                    document.getElementById(
                        'aiAssistantPanel'
                    );

                const closeButton =
                    document.getElementById(
                        'closeAiPanel'
                    );

                const sendButton =
                    document.getElementById(
                        'sendAiMessage'
                    );

                const input =
                    document.getElementById(
                        'aiInput'
                    );

                const messages =
                    document.getElementById(
                        'aiMessages'
                    );

                const quickButtons =
                    document.querySelectorAll(
                        '.quick-btn'
                    );


                /* =================================================
                   LARAVEL AI PROXY
                ================================================== */

                const LARAVEL_PROXY_URL =
                    '/ai/chat-proxy';


                let isAffiliatorSearch = false;


                /* =================================================
                   OPEN / CLOSE AI
                ================================================== */

                if (aiButton) {

                    aiButton.addEventListener(
                        'click',
                        function () {

                            aiPanel.classList.toggle(
                                'active'
                            );

                            if (
                                aiPanel.classList.contains(
                                    'active'
                                )
                            ) {

                                input?.focus();

                            }

                        }
                    );

                }


                if (closeButton) {

                    closeButton.addEventListener(
                        'click',
                        function () {

                            aiPanel.classList.remove(
                                'active'
                            );

                        }
                    );

                }


                /* =================================================
                   QUICK BUTTON
                ================================================== */

                quickButtons.forEach(
                    btn => {

                        btn.addEventListener(
                            'click',
                            function () {

                                const templateText =
                                    this.getAttribute(
                                        'data-template'
                                    );

                                input.value =
                                    templateText || '';

                                isAffiliatorSearch =
                                    true;

                                input.focus();

                            }
                        );

                    }
                );


                /* =================================================
                   SEND MESSAGE
                ================================================== */

                async function sendMessage() {

                    const text =
                        input.value.trim();

                    if (!text) {
                        return;
                    }


                    if (
                        text
                            .toLowerCase()
                            .startsWith(
                                "mencari affiliator"
                            )
                    ) {

                        isAffiliatorSearch = true;

                    } else {

                        isAffiliatorSearch = false;

                    }


                    /* =============================================
                       USER MESSAGE
                    ============================================== */

                    const userMessage =
                        document.createElement(
                            'div'
                        );

                    userMessage.className =
                        'ai-message ml-auto bg-zinc-950 text-white border-zinc-950';

                    userMessage.textContent =
                        text;

                    messages.appendChild(
                        userMessage
                    );


                    input.value = '';


                    const wasSearch =
                        isAffiliatorSearch;

                    isAffiliatorSearch =
                        false;


                    messages.scrollTop =
                        messages.scrollHeight;


                    /* =============================================
                       LOADING MESSAGE
                    ============================================== */

                    const loadingMessage =
                        document.createElement(
                            'div'
                        );

                    loadingMessage.className =
                        'ai-message';

                    loadingMessage.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <span class="h-3 w-3 animate-spin rounded-full border-2 border-zinc-300 border-t-zinc-900"></span>
                            Sedang memproses permintaan...
                        </span>
                    `;

                    messages.appendChild(
                        loadingMessage
                    );

                    messages.scrollTop =
                        messages.scrollHeight;


                    /* =============================================
                       PAYLOAD
                    ============================================== */

                    try {

                        const payload = {

                            message:
                                wasSearch
                                    ? null
                                    : text,

                            raw_input:
                                text,

                            type:
                                wasSearch
                                    ? 'search_affiliator'
                                    : 'chat',

                            source:
                                'erp',

                            user_id:
                                '{{ $auth->id ?? "" }}',

                            divisi_id:
                                '{{ $auth->userDivisions->first()->division_id ?? "" }}',

                            timestamp:
                                new Date()
                                    .toISOString()

                        };


                        const response =
                            await fetch(
                                LARAVEL_PROXY_URL,
                                {
                                    method: 'POST',

                                    headers: {
                                        'Content-Type':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            document
                                                .querySelector(
                                                    'meta[name="csrf-token"]'
                                                )
                                                ?.getAttribute(
                                                    'content'
                                                ) || ''
                                    },

                                    body:
                                        JSON.stringify(
                                            payload
                                        )
                                }
                            );


                        if (!response.ok) {

                            throw new Error(
                                'Server Laravel mengembalikan status: HTTP ' +
                                response.status
                            );

                        }


                        const result =
                            await response.json();


                        console.log(result);


                        /* =============================================
                           REMOVE LOADING
                        ============================================== */

                        loadingMessage.remove();


                        /* =============================================
                           AI RESPONSE
                        ============================================== */

                        const aiMessage =
                            document.createElement(
                                'div'
                            );

                        aiMessage.className =
                            'ai-message';


                        let responseText =
                            'AI tidak memberikan jawaban.';


                        if (result) {

                            if (
                                typeof result ===
                                'string'
                            ) {

                                responseText =
                                    result;

                            } else if (
                                result.response
                            ) {

                                responseText =
                                    result.response;

                            } else if (
                                result.message
                            ) {

                                responseText =
                                    result.message;

                            } else if (
                                result.text
                            ) {

                                responseText =
                                    result.text;

                            } else {

                                responseText =
                                    JSON.stringify(
                                        result,
                                        null,
                                        2
                                    );

                            }

                        }


                        aiMessage.innerHTML =
                            responseText;


                        messages.appendChild(
                            aiMessage
                        );


                        messages.scrollTop =
                            messages.scrollHeight;


                    } catch (error) {

                        console.error(
                            'Error AI:',
                            error
                        );


                        loadingMessage.remove();


                        const errorMessage =
                            document.createElement(
                                'div'
                            );

                        errorMessage.className =
                            'ai-message bg-red-50 border-red-200 text-red-800';

                        errorMessage.textContent =
                            'Maaf, terjadi kesalahan saat menghubungi server Laravel.';


                        messages.appendChild(
                            errorMessage
                        );


                        messages.scrollTop =
                            messages.scrollHeight;

                    }

                }


                /* =================================================
                   SEND BUTTON
                ================================================== */

                if (sendButton) {

                    sendButton.addEventListener(
                        'click',
                        sendMessage
                    );

                }


                /* =================================================
                   ENTER TO SEND
                ================================================== */

                if (input) {

                    input.addEventListener(
                        'keydown',
                        function (e) {

                            if (
                                e.key === 'Enter' &&
                                !e.shiftKey
                            ) {

                                e.preventDefault();

                                sendMessage();

                            }

                        }
                    );

                }

            }

        );

    </script>


    {{-- =========================================================
        STACK SCRIPT
    ========================================================== --}}
    @stack('script')

</body>

</html>