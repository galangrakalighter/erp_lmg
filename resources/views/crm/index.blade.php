@extends('layout.app')

@section('content')
<div
    id="crmApp"
    data-ai-url="{{ url('/api/crm/ai') }}"
    data-human-url="{{ url('/api/crm/human') }}"
    data-meeting-url="{{ url('/api/crm/meeting') }}"
    data-crud-url="{{ url('/api/crm') }}"
    data-history-url="{{ url('/api/crm/history') }}"
    data-human-history-url="{{ url('/api/crm/human/history') }}"
    data-human-checklist-url="{{ url('/api/crm/human') }}"
>
    <div class="card shadow mb-4">

        {{-- Header --}}
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="m-0 font-weight-bold text-primary">
                    CRM Leads Ekosistem LMG
                </h6>

                <small id="crmTotal" class="text-muted">
                    Data CRM
                </small>
            </div>

            <button
                type="button"
                id="addLeadButton"
                class="btn btn-primary btn-sm"
            >
                <i class="fas fa-plus mr-1"></i>
                Tambah Lead
            </button>
        </div>

        {{-- Tab --}}
        <div class="card-header bg-white pb-0">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <button
                        type="button"
                        class="nav-link active crm-tab"
                        data-tab="ai"
                    >
                        <i class="fas fa-robot mr-1"></i>
                        AI
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        type="button"
                        class="nav-link crm-tab"
                        data-tab="human"
                    >
                        <i class="fas fa-user mr-1"></i>
                        Human
                    </button>
                </li>

                <li class="nav-item">
                    <button
                        type="button"
                        class="nav-link crm-tab"
                        data-tab="meeting"
                    >
                        <i class="fas fa-calendar-alt mr-1"></i>
                        Jadwal Meeting
                    </button>
                </li>
            </ul>
        </div>

        {{-- Filter --}}
        <div class="card-body border-bottom">
            <form id="crmFilterForm">
                <div class="row align-items-end">

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">
                            Dari Tanggal
                            <span id="startDateContext">Chat</span>
                        </label>

                        <input
                            type="date"
                            id="startDate"
                            class="form-control form-control-sm"
                        >
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">
                            Sampai Tanggal
                            <span id="endDateContext">Chat</span>
                        </label>

                        <input
                            type="date"
                            id="endDate"
                            class="form-control form-control-sm"
                        >
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">
                            Layanan
                        </label>

                        <select
                            id="layananFilter"
                            class="form-control form-control-sm"
                        >
                            <option value="">
                                Semua Layanan
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">
                            Status
                        </label>

                        <select
                            id="statusFilter"
                            class="form-control form-control-sm"
                        >
                            <option value="">
                                Semua Status
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-2">
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                        >
                            <i class="fas fa-filter mr-1"></i>
                            Filter
                        </button>

                        <button
                            type="button"
                            id="resetFilter"
                            class="btn btn-secondary btn-sm"
                        >
                            <i class="fas fa-undo mr-1"></i>
                            Reset
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Pesan gangguan --}}
        <div
            id="crmError"
            class="alert alert-warning m-3 d-none"
            role="alert"
        >
            <i class="fas fa-exclamation-triangle mr-2"></i>

            <span id="crmErrorMessage">
                CRM sedang mengalami gangguan.
            </span>

            <button
                type="button"
                id="retryButton"
                class="btn btn-warning btn-sm ml-3"
            >
                <i class="fas fa-redo mr-1"></i>
                Coba Lagi
            </button>
        </div>

        @include('crm.ai')
        @include('crm.human')
        @include('crm.meeting')
    </div>
</div>

@include('crm.modals')

<script src="{{ asset('js/crm/ai.js') }}?v=1.0.0"></script>
<script src="{{ asset('js/crm/human.js') }}?v=1.1.1"></script>
<script src="{{ asset('js/crm/meeting.js') }}?v=1.0.0"></script>
<script src="{{ asset('js/crm/common.js') }}?v=1.0.0"></script>
@endsection
