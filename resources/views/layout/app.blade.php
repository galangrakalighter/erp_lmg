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

    {{-- Legacy pages still use the existing Font Awesome icon classes. --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">

    @if(request()->routeIs('market_research', 'branding', 'analisis', 'manajemen', 'PDF', 'api.digital-marketing.*', 'api.business-dev.*', 'api.multimedia.*'))
        {{-- Keep the original Bootstrap/SB Admin component placement inside client tabs. --}}
        <link rel="stylesheet" href="{{ asset('css/sb-admin-2.min.css') }}">
    @endif

    {{-- =========================================================
        THIRD PARTY CSS
        Bootstrap/SB Admin CSS SUDAH DIHAPUS
    ========================================================== --}}

    {{-- Select2 --}}
    <link href="{{ asset('vendor-local/select2/select2.min.css') }}" rel="stylesheet">

    {{-- Flatpickr --}}
    <link rel="stylesheet" href="{{ asset('vendor-local/flatpickr/flatpickr.min.css') }}">

    <link rel="stylesheet" href="{{ asset('vendor-local/flatpickr/plugins/confirmDate/confirmDate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-filter-datepicker.css') }}">

    {{-- Choices --}}
    <link rel="stylesheet" href="{{ asset('vendor-local/choices/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor-local/sweetalert2/sweetalert2.min.css') }}">

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
            z-index: 1100;

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

        @media (min-width: 1025px) {
            body.sidebar-collapsed #accordionSidebar {
                transform: translateX(-100%);
            }

            body.sidebar-collapsed #content-wrapper {
                width: 100%;
                margin-left: 0;
            }
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
            background: rgba(245, 245, 243, 0.38);
        }

        .loading-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 1px solid #e4e4e7;
            border-radius: 14px;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.12);
        }

        .loading-mark {
            display: flex;
            align-items: center;
            gap: 4px;
            min-width: 42px;
        }

        .loading-mark span {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #161616;
            animation: loadingPulse .9s ease-in-out infinite;
        }

        .loading-mark span:nth-child(2) { animation-delay: .12s; }
        .loading-mark span:nth-child(3) { animation-delay: .24s; }

        @keyframes loadingPulse {
            0%, 70%, 100% { transform: translateY(0); opacity: .28; }
            35% { transform: translateY(-5px); opacity: 1; }
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

            #accordionSidebar.translate-x-0 {
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

        /* Client workspace modules: shared visual language for Market Research,
           Branding, Analysis, Content Plan, and client documents. */
        body.lmg-client-module {
            background: #f5f5f3 !important;
        }

        body.lmg-client-module .lmg-content {
            color: #27272a;
        }

        /* Legacy Bootstrap CSS is no longer loaded. Keep its modal contract:
           hidden by default, visible only after the JS adds .show. */
        .modal {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            overflow-y: auto;
            background: rgba(9, 9, 11, .55);
        }

        .modal:not(.show) {
            display: none !important;
        }

        .modal.show {
            display: block;
        }

        .modal-dialog {
            position: relative;
            width: calc(100% - 2rem);
            max-width: 520px;
            margin: 2rem auto !important;
            pointer-events: none;
        }

        .modal-dialog.modal-lg {
            max-width: 800px;
        }

        .modal-dialog.modal-xl {
            max-width: 1140px;
        }

        .modal-dialog-centered {
            display: flex;
            min-height: calc(100% - 4rem);
            align-items: center;
        }

        .modal-content {
            position: relative;
            width: 100%;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            background: #fff;
            color: #27272a;
            pointer-events: auto;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .24);
        }

        .modal-header,
        .modal-footer {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            padding: 1rem 1.25rem;
        }

        .modal-header {
            justify-content: space-between;
            border-bottom: 1px solid #e4e4e7;
            background: #161616 !important;
            color: #fff !important;
        }

        .modal-header .modal-title,
        .modal-header .close {
            color: #fff !important;
        }

        .modal-footer {
            justify-content: flex-end;
            gap: .65rem;
            border-top: 1px solid #e4e4e7;
        }

        .modal-open {
            overflow: hidden !important;
        }

        body.lmg-client-module .lmg-content > .container,
        body.lmg-client-module .lmg-content > .container-fluid {
            max-width: none !important;
            padding-right: 0 !important;
            padding-left: 0 !important;
        }

        body.lmg-client-module .row {
            margin-right: -8px;
            margin-left: -8px;
        }

        body.lmg-client-module .row > [class*="col-"] {
            padding-right: 8px;
            padding-left: 8px;
        }

        /* Research and branding contain wide data sets. Stacking their old
           half-width columns prevents tables and actions from colliding. */
        body.lmg-module-catalog .lmg-content > .row > .col-lg-6 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
        }

        body.lmg-client-module .card,
        body.lmg-client-module .card.card-body {
            overflow: hidden;
            margin-bottom: 18px;
            border: 1px solid #e4e4e7 !important;
            border-radius: 16px !important;
            background: #fff !important;
            box-shadow: 0 8px 24px rgba(24, 24, 27, .055) !important;
        }

        body.lmg-client-module .card-header {
            min-height: 64px;
            padding: 14px 18px !important;
            border: 1px solid #e4e4e7 !important;
            border-bottom: 0 !important;
            border-radius: 16px 16px 0 0 !important;
            background: #fff !important;
        }

        body.lmg-client-module .card-header + .card,
        body.lmg-client-module .card-header + .card-body {
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
        }

        body.lmg-client-module .card-header h6,
        body.lmg-client-module .card-header h5,
        body.lmg-client-module .card-header .text-primary,
        body.lmg-client-module .card-header .text-dark {
            color: #18181b !important;
            font-size: .88rem;
            font-weight: 750 !important;
            letter-spacing: .01em;
        }

        body.lmg-client-module .card-header h6 i,
        body.lmg-client-module .card-header h5 i {
            display: inline-grid;
            width: 30px;
            height: 30px;
            margin-right: 9px !important;
            place-items: center;
            border-radius: 9px;
            background: #f4f4f5;
            color: #52525b;
        }

        body.lmg-client-module .card-body {
            padding: 18px !important;
        }

        body.lmg-client-module .table-container {
            padding: 0 18px 18px !important;
        }

        body.lmg-client-module .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto !important;
            overflow-y: visible !important;
            border: 1px solid #e4e4e7;
            border-radius: 12px;
            background: #fff;
            scrollbar-width: thin;
            scrollbar-color: #a1a1aa #f4f4f5;
            -webkit-overflow-scrolling: touch;
        }

        body.lmg-client-module .table-responsive::-webkit-scrollbar {
            height: 10px;
        }

        body.lmg-client-module .table-responsive::-webkit-scrollbar-track {
            background: #f4f4f5;
        }

        body.lmg-client-module .table-responsive::-webkit-scrollbar-thumb {
            border: 2px solid #f4f4f5;
            border-radius: 999px;
            background: #a1a1aa;
        }

        body.lmg-client-module table.table {
            width: 100% !important;
            min-width: 900px;
            margin: 0 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            color: #3f3f46;
        }

        body.lmg-client-module table.table th,
        body.lmg-client-module table.table th.bg-dark,
        body.lmg-client-module table.table thead.thead-light th,
        body.lmg-client-module table.table thead.bg-light th {
            padding: 13px 14px !important;
            border: 0 !important;
            border-bottom: 1px solid #27272a !important;
            background: #161616 !important;
            color: #fff !important;
            font-size: .68rem !important;
            font-weight: 750 !important;
            letter-spacing: .065em;
            line-height: 1.3;
            text-transform: uppercase;
            white-space: nowrap;
        }

        body.lmg-client-module table.table td,
        body.lmg-client-module table.table tbody th {
            padding: 14px !important;
            border: 0 !important;
            border-bottom: 1px solid #ededee !important;
            background: #fff;
            color: #3f3f46;
            font-size: .8rem;
            line-height: 1.55;
            vertical-align: middle !important;
        }

        body.lmg-client-module table.table tbody tr:last-child td,
        body.lmg-client-module table.table tbody tr:last-child th {
            border-bottom: 0 !important;
        }

        body.lmg-client-module table.table tbody tr:hover td,
        body.lmg-client-module table.table tbody tr:hover th {
            background: #fafafa !important;
        }

        body.lmg-client-module label {
            margin-bottom: 6px;
            color: #52525b !important;
            font-size: .72rem !important;
            font-weight: 700 !important;
            letter-spacing: .035em;
        }

        body.lmg-client-module .form-control,
        body.lmg-client-module .custom-select,
        body.lmg-client-module select.form-control {
            min-height: 42px;
            border: 1px solid #d4d4d8 !important;
            border-radius: 10px !important;
            background-color: #fff !important;
            color: #27272a !important;
            box-shadow: none !important;
        }

        body.lmg-client-module textarea.form-control {
            min-height: 104px;
        }

        body.lmg-client-module .form-control:focus,
        body.lmg-client-module .custom-select:focus {
            border-color: #71717a !important;
            box-shadow: 0 0 0 3px rgba(24, 24, 27, .08) !important;
        }

        body.lmg-client-module .btn {
            min-height: 36px;
            padding: 8px 13px;
            border-radius: 9px !important;
            font-size: .75rem;
            font-weight: 700;
            box-shadow: none !important;
        }

        body.lmg-client-module .btn-primary,
        body.lmg-client-module .btn-success,
        body.lmg-client-module .btn-info,
        body.lmg-client-module .modal .btn-warning {
            border-color: #161616 !important;
            background: #161616 !important;
            color: #fff !important;
        }

        body.lmg-client-module .btn-primary:hover,
        body.lmg-client-module .btn-success:hover,
        body.lmg-client-module .btn-info:hover,
        body.lmg-client-module .modal .btn-warning:hover {
            border-color: #3f3f46 !important;
            background: #3f3f46 !important;
        }

        body.lmg-client-module .btn-warning {
            border-color: #e4e4e7 !important;
            background: #f4f4f5 !important;
            color: #3f3f46 !important;
        }

        body.lmg-client-module .btn-danger {
            border-color: #fee2e2 !important;
            background: #fff1f2 !important;
            color: #dc2626 !important;
        }

        body.lmg-client-module .btn-secondary,
        body.lmg-client-module .btn-light,
        body.lmg-client-module .btn-outline-secondary {
            border: 1px solid #d4d4d8 !important;
            background: #fff !important;
            color: #52525b !important;
        }

        body.lmg-client-module .dataTables_wrapper .dataTables_length,
        body.lmg-client-module .dataTables_wrapper .dataTables_filter,
        body.lmg-client-module .dataTables_wrapper .dataTables_info,
        body.lmg-client-module .dataTables_wrapper .dataTables_paginate {
            padding: 12px 0 !important;
            color: #71717a !important;
            font-size: .75rem;
        }

        body.lmg-client-module .dataTables_wrapper .dataTables_filter input,
        body.lmg-client-module .dataTables_wrapper .dataTables_length select {
            min-height: 36px;
            margin-left: 7px;
            border: 1px solid #d4d4d8 !important;
            border-radius: 9px !important;
            background: #fff !important;
        }

        body.lmg-client-module .dataTables_wrapper .paginate_button {
            min-width: 34px;
            margin: 0 2px !important;
            padding: 7px 10px !important;
            border: 1px solid #e4e4e7 !important;
            border-radius: 8px !important;
            background: #fff !important;
            color: #52525b !important;
        }

        body.lmg-client-module .dataTables_wrapper .paginate_button.current,
        body.lmg-client-module .dataTables_wrapper .paginate_button.current:hover {
            border-color: #161616 !important;
            background: #161616 !important;
            color: #fff !important;
        }

        body.lmg-client-module .alert {
            border: 1px solid #e4e4e7;
            border-radius: 11px;
        }

        body.lmg-client-module img.img-fluid {
            border-radius: 12px !important;
        }

        body.lmg-client-module .stat-card:hover {
            transform: translateY(-2px) !important;
        }

        body.lmg-embedded.lmg-client-module .lmg-content {
            padding: 18px !important;
        }

        html:has(body.lmg-embedded) {
            width: 100%;
            height: 100% !important;
            overflow-x: hidden !important;
            overflow-y: scroll !important;
            scrollbar-gutter: stable;
        }

        body.lmg-embedded {
            width: 100%;
            min-height: 100% !important;
            overflow: visible !important;
        }

        body.lmg-embedded #content,
        body.lmg-embedded #content-wrapper,
        body.lmg-embedded .lmg-content {
            height: auto !important;
            min-height: 100% !important;
            overflow: visible !important;
        }

        body.lmg-client-module table#tableManajemenKonten {
            min-width: 1800px;
        }

        body.lmg-client-module table#target_audience,
        body.lmg-client-module table#segmentasi,
        body.lmg-client-module table#insight_1,
        body.lmg-client-module table#insight_2,
        body.lmg-client-module table#dataTableAnalisisBaru {
            min-width: 1200px;
        }

        @media (max-width: 767px) {
            body.lmg-client-module .card-header {
                align-items: flex-start !important;
                gap: 10px;
            }

            body.lmg-client-module .card-header > div:last-child,
            body.lmg-client-module .card-header > .btn {
                flex-shrink: 0;
            }

            body.lmg-client-module .dataTables_wrapper .dataTables_filter,
            body.lmg-client-module .dataTables_wrapper .dataTables_length {
                float: none !important;
                text-align: left !important;
            }
        }

        body.lmg-embedded #accordionSidebar,
        body.lmg-embedded .lmg-navbar,
        body.lmg-embedded .lmg-footer,
        body.lmg-embedded #floatingAiButton,
        body.lmg-embedded .ai-assistant-panel {
            display: none !important;
        }

        body.lmg-embedded #content-wrapper {
            width: 100% !important;
            min-height: 100vh;
            margin-left: 0 !important;
        }

        body.lmg-embedded .lmg-content {
            padding: 0 !important;
        }
    </style>

    @stack('styles')
</head>


@php
    $filterDateDefault = 'today';

    if (request()->routeIs('crm.*')) {
        $filterDateDefault = 'yesterday-to-today';
    } elseif (request()->routeIs(
        'digital-marketing.*',
        'multimedia.*',
        'api.digital-marketing.*',
        'api.multimedia.*',
        'market_research',
        'branding',
        'analisis',
        'manajemen',
        'finance.*',
        'finance_coa.*',
        'general_journal.*',
        'adjustment_journal.*',
        'invoice.*',
        'invoices.*',
        'payroll.*',
        'ledger.*',
        'audit.*'
    )) {
        $filterDateDefault = 'none';
    }
@endphp
<body id="page-top" data-filter-date-default="{{ $filterDateDefault }}" class="min-h-screen bg-[#f5f5f3] text-zinc-900 {{ request()->boolean('embed') ? 'lmg-embedded' : '' }} {{ request()->routeIs('market_research', 'branding', 'analisis', 'manajemen', 'PDF', 'api.digital-marketing.*', 'api.business-dev.*', 'api.multimedia.*') ? 'lmg-client-tabs' : '' }} {{ request()->routeIs('analisis', 'api.digital-marketing.analisis', 'api.business-dev.analisis', 'api.multimedia.analisis') ? 'lmg-analysis-page' : '' }}">

    {{-- =========================================================
        LOADING OVERLAY
    ========================================================== --}}
    <div id="loadingOverlay" aria-hidden="true">

        <div class="loading-box">
            <div class="loading-mark" aria-hidden="true">
                <span></span><span></span><span></span>
            </div>
            <div>
                <h6 id="loadingStatus" class="mb-0 text-sm font-semibold tracking-tight text-zinc-900">
                    Membuka halaman
                </h6>
                <p id="loadingSubtext" class="mb-0 mt-0.5 text-[11px] text-zinc-500">Mohon tunggu sebentar</p>
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
    <script src="{{ asset('vendor-local/axios/axios.min.js') }}"></script>

    {{-- jQuery
         Tetap dipertahankan karena DataTables / Select2
         dan beberapa halaman ERP Anda masih menggunakannya. --}}
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    {{-- Chart.js --}}
    <script src="{{ asset('vendor-local/chartjs/chart.umd.js') }}"></script>

    {{-- Moment --}}
    <script src="{{ asset('vendor-local/moment/moment.min.js') }}"></script>

    {{-- Flatpickr --}}
    <script src="{{ asset('vendor-local/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('js/custom-filter-datepicker.js') }}"></script>

    {{-- DataTables Core --}}
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>

    {{-- RowsGroup --}}
    {{-- Choices --}}
    <script src="{{ asset('vendor-local/choices/choices.min.js') }}"></script>

    {{-- Select2 --}}
    <script src="{{ asset('vendor-local/select2/select2.min.js') }}"></script>

    {{-- Split.js --}}
    <script src="{{ asset('vendor-local/split/split.min.js') }}"></script>

    {{-- PDF.js --}}
    <script src="{{ asset('vendor-local/pdfjs/pdf.min.js') }}"></script>
    <script src="{{ asset('vendor-local/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                "{{ asset('vendor-local/pdfjs/pdf.worker.min.js') }}";
        }
    </script>


    {{-- =========================================================
        PRODUCT SCRIPTS
    ========================================================== --}}
    <script src="{{ asset('js/native-client-table.js') }}?v={{ filemtime(public_path('js/native-client-table.js')) }}"></script>
    <script src="{{ asset('js/produk/datatable.js') }}"></script>
    <script src="{{ asset('js/produk/create.js') }}"></script>
    <script src="{{ asset('js/produk/edit.js') }}"></script>
    <script src="{{ asset('js/produk/delete.js') }}"></script>
    <script src="{{ asset('js/produk/produk.js') }}"></script>


    {{-- =========================================================
        LEGACY MODAL COMPATIBILITY
        Keeps Bootstrap-style data-toggle/data-dismiss markup working
        without loading the removed Bootstrap stylesheet/plugin.
    ========================================================== --}}
    <script>
        (function () {
            function modalTarget(value) {
                if (!value || value.charAt(0) !== '#') return null;

                try {
                    return document.querySelector(value);
                } catch (error) {
                    return null;
                }
            }

            function openModal(modal) {
                if (!modal) return;

                if (window.jQuery) window.jQuery(modal).trigger('show.bs.modal');
                modal.classList.add('show');
                modal.style.display = 'block';
                modal.removeAttribute('aria-hidden');
                modal.setAttribute('aria-modal', 'true');
                document.body.classList.add('modal-open');
                if (window.jQuery) window.jQuery(modal).trigger('shown.bs.modal');
            }

            function closeModal(modal) {
                if (!modal) return;

                if (window.jQuery) window.jQuery(modal).trigger('hide.bs.modal');
                modal.classList.remove('show');
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                modal.removeAttribute('aria-modal');

                if (!document.querySelector('.modal.show')) {
                    document.body.classList.remove('modal-open');
                }

                if (window.jQuery) window.jQuery(modal).trigger('hidden.bs.modal');
            }

            window.lmgOpenModal = openModal;
            window.lmgCloseModal = closeModal;

            document.addEventListener('click', function (event) {
                const opener = event.target.closest('[data-toggle="modal"][data-target]');

                if (opener) {
                    const modal = modalTarget(opener.getAttribute('data-target'));
                    if (!modal) return;

                    event.preventDefault();
                    openModal(modal);
                    return;
                }

                const closer = event.target.closest('[data-dismiss="modal"]');

                if (closer) {
                    event.preventDefault();
                    closeModal(closer.closest('.modal'));
                    return;
                }

                if (event.target.classList.contains('modal') && event.target.classList.contains('show')) {
                    closeModal(event.target);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeModal(document.querySelector('.modal.show'));
                }
            });

            if (window.jQuery && !window.jQuery.fn.modal) {
                window.jQuery.fn.modal = function (action) {
                    return this.each(function () {
                        if (action === 'hide') {
                            closeModal(this);
                        } else if (action === 'toggle') {
                            this.classList.contains('show') ? closeModal(this) : openModal(this);
                        } else {
                            openModal(this);
                        }
                    });
                };
            }
        })();
    </script>


    {{-- =========================================================
        GLOBAL SCRIPT
    ========================================================== --}}
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            /* =====================================================
               LOADING
            ====================================================== */

            const loader = document.getElementById('loadingOverlay');
            const statusText = document.getElementById('loadingStatus');
            let loadingTimer = null;

            function showLoadingNow(message = 'Membuka halaman') {
                if (!loader) return;
                if (statusText) statusText.textContent = message;
                loader.style.display = 'flex';
                loader.setAttribute('aria-hidden', 'false');
                document.body.classList.add('loading-active');
            }

            function startEnhancedLoading(message = 'Membuka halaman') {
                clearTimeout(loadingTimer);
                // Navigasi cepat tidak perlu menampilkan kilatan loader.
                loadingTimer = setTimeout(() => showLoadingNow(message), 120);
            }

            function stopEnhancedLoading() {
                clearTimeout(loadingTimer);
                loadingTimer = null;
                if (!loader) return;
                loader.style.display = 'none';
                loader.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('loading-active');
            }

            window.startEnhancedLoading = startEnhancedLoading;
            window.stopEnhancedLoading = stopEnhancedLoading;

            // pageshow dijalankan kembali ketika halaman berasal dari BFCache.
            window.addEventListener('pageshow', stopEnhancedLoading);
            window.addEventListener('load', stopEnhancedLoading);
            stopEnhancedLoading();

            /* =====================================================
               INTERNAL LINK LOADING
            ====================================================== */

            document.addEventListener('click', function (event) {
                const link = event.target.closest('a[href]');
                if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                if (link.hasAttribute('download') || link.target === '_blank' || link.hasAttribute('data-toggle')) return;

                const rawHref = link.getAttribute('href');
                if (!rawHref || rawHref === '#' || rawHref.startsWith('javascript:')) return;

                const destination = new URL(link.href, window.location.href);
                if (destination.origin !== window.location.origin) return;
                if (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return;

                startEnhancedLoading();
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.target === '_blank') return;
                queueMicrotask(() => {
                    if (!event.defaultPrevented) startEnhancedLoading('Memproses data');
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

            if (document.body.classList.contains('lmg-client-tabs')) {
                return;
            }

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

    {{-- <button
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

    </button> --}}


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


    {{-- Memastikan scroll halaman kembali normal setelah modal ditutup. --}}
    <script>
        (function () {
            function hasOpenModal() {
                if (document.querySelector('.modal.show')) return true;

                return Array.from(document.querySelectorAll('[data-modal-backdrop]')).some(function (backdrop) {
                    const modal = backdrop.closest('[id]');

                    return modal &&
                        !modal.classList.contains('hidden') &&
                        modal.getAttribute('aria-hidden') !== 'true' &&
                        window.getComputedStyle(modal).display !== 'none';
                });
            }

            function restorePageScroll() {
                if (hasOpenModal()) return;

                document.body.classList.remove('modal-open', 'overflow-hidden', 'overflow-y-hidden');
                document.documentElement.classList.remove('modal-open', 'overflow-hidden', 'overflow-y-hidden');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('overflow-y');
                document.body.style.removeProperty('padding-right');
                document.documentElement.style.removeProperty('overflow');
                document.documentElement.style.removeProperty('overflow-y');

                document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                    backdrop.remove();
                });
            }

            function scheduleScrollRestore() {
                window.setTimeout(restorePageScroll, 0);
                window.setTimeout(restorePageScroll, 250);
            }

            window.lmgRestorePageScroll = restorePageScroll;

            document.addEventListener('click', function (event) {
                const closeControl = event.target.closest(
                    '[data-dismiss="modal"], [data-bs-dismiss="modal"], [data-modal-close], [data-audit-close], .modal-close'
                );

                if (closeControl) {
                    scheduleScrollRestore();
                    return;
                }

                const backdrop = event.target.closest('[data-modal-backdrop]');

                if (backdrop && event.target === backdrop) {
                    scheduleScrollRestore();
                }
            });

            document.addEventListener('hidden.bs.modal', restorePageScroll);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') scheduleScrollRestore();
            });

            new MutationObserver(function () {
                scheduleScrollRestore();
            }).observe(document.body, {
                attributes: true,
                attributeFilter: ['class', 'style', 'aria-hidden', 'hidden'],
                childList: true,
                subtree: true,
            });

            window.addEventListener('pageshow', restorePageScroll);
        })();
    </script>


    {{-- Sinkronisasi pilihan divisi dengan pengguna pada filter dan modal. --}}
    <script>
        (function () {
            const selectableUsersUrl = @json(route('api.selectable-users'));
            const userSelector = [
                'select[name="email"]',
                'select[name="user_id"]',
                'select[name="pic"]',
                'select[name="pic[]"]',
                'select[id*="pic"]',
                'select[id="user-filter"]',
                'select[id="filterUser"]',
                'select[id="form-pic"]',
            ].join(', ');
            const divisionSelector = [
                'select[name="divisi"]',
                'select[name="division"]',
                'select[name="division_id"]',
                'select[id="form-division"]',
                'select[id*="division-filter"]',
                'select[id*="divisi-filter"]',
            ].join(', ');

            function scopeFor(element) {
                return element.closest('form, .modal, [role="dialog"], .fixed.inset-0') || document;
            }

            function divisionFor(userSelect) {
                const scope = scopeFor(userSelect);
                const division = scope.querySelector(divisionSelector);

                return division && division.value && division.value !== 'all'
                    ? division.value
                    : '';
            }

            function selectedValues(select) {
                return Array.from(select.selectedOptions)
                    .map(function (option) { return option.value; })
                    .filter(Boolean);
            }

            function defaultLabel(select) {
                const currentEmptyOption = Array.from(select.options)
                    .find(function (option) { return option.value === ''; });

                if (currentEmptyOption) return currentEmptyOption.textContent;

                return select.required ? '-- Pilih pengguna --' : 'Semua pengguna';
            }

            async function populateUserSelect(select, divisionId) {
                if (!(select instanceof HTMLSelectElement) || select.dataset.userOptionsLoading === 'true') return;

                const selected = selectedValues(select);
                const label = defaultLabel(select);
                const requestedDivision = divisionId === undefined ? divisionFor(select) : divisionId;
                const url = new URL(selectableUsersUrl, window.location.origin);

                if (requestedDivision) url.searchParams.set('division_id', requestedDivision);

                select.dataset.userOptionsLoading = 'true';
                select.setAttribute('aria-busy', 'true');

                try {
                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) throw new Error('Gagal memuat pengguna');

                    const users = await response.json();
                    select.replaceChildren();

                    if (!select.multiple) {
                        select.add(new Option(label, ''));
                    }

                    users.forEach(function (user) {
                        const option = new Option(
                            user.name ? `${user.email} — ${user.name}` : user.email,
                            user.id,
                            false,
                            selected.includes(String(user.id)),
                        );
                        select.add(option);
                    });

                    if (window.jQuery && window.jQuery(select).hasClass('select2-hidden-accessible')) {
                        window.jQuery(select).trigger('change.select2');
                    }
                } catch (error) {
                    // Biarkan pilihan server-side tetap dapat dipakai bila jaringan sedang bermasalah.
                    console.warn('Pilihan pengguna tidak dapat diperbarui.', error);
                } finally {
                    delete select.dataset.userOptionsLoading;
                    select.removeAttribute('aria-busy');
                }
            }

            function refreshUserSelects(scope) {
                const root = scope || document;
                root.querySelectorAll(userSelector).forEach(function (select) {
                    populateUserSelect(select);
                });
            }

            window.lmgPopulateUserSelect = populateUserSelect;
            window.lmgRefreshUserSelects = refreshUserSelects;

            document.addEventListener('change', function (event) {
                if (!event.target.matches(divisionSelector)) return;

                const scope = scopeFor(event.target);
                scope.querySelectorAll(userSelector).forEach(function (select) {
                    populateUserSelect(select, event.target.value === 'all' ? '' : event.target.value);
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('[data-modal-open], [data-toggle="modal"]')) return;

                window.setTimeout(function () {
                    refreshUserSelects(document);
                }, 0);
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    refreshUserSelects(document);
                });
            } else {
                refreshUserSelects(document);
            }
        })();
    </script>


    {{-- =========================================================
        STACK SCRIPT
    ========================================================== --}}
    @stack('script')

</body>

</html>
