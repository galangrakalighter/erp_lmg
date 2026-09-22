@extends('layout.app')

@section('content')
<style>
    #crmApp .crm-table-shell {
        max-height: 68vh;
        scrollbar-color: #a1a1aa #f4f4f5;
        scrollbar-width: thin;
    }

    #crmApp .crm-table-shell table {
        border-collapse: separate;
        border-spacing: 0;
    }

    #crmApp .crm-table-shell thead th {
        position: sticky;
        top: 0;
        z-index: 15;
        border-bottom: 1px solid #e4e4e7;
        background: #fafafa;
    }

    #crmApp .crm-table-shell thead .crm-column-group th {
        top: 0;
        height: 34px;
        background: #f4f4f5;
        color: #71717a;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    #crmApp .crm-table-shell thead .crm-column-labels th {
        top: 34px;
        height: 44px;
        box-shadow: inset 0 -1px #e4e4e7;
    }

    #crmApp .crm-table-shell thead .crm-single-header th {
        top: 0;
    }

    #crmApp .crm-table-shell tbody tr:nth-child(even) td {
        background-color: #fafafa;
    }

    #crmApp .crm-table-shell tbody tr:hover td {
        background-color: #f4f4f5;
    }

    #crmApp .crm-table-shell tbody td {
        max-width: 240px;
        padding: 14px 16px;
        border-bottom: 1px solid #f4f4f5;
        color: #3f3f46;
        font-size: 12px;
        line-height: 1.5;
        vertical-align: top;
        transition: background-color 150ms ease;
    }

    #crmApp .crm-table-shell tbody td:first-child {
        color: #a1a1aa;
        font-variant-numeric: tabular-nums;
        text-align: center;
    }

    #crmApp .crm-table-shell tbody td:last-child,
    #crmApp .crm-table-shell thead th:last-child {
        position: sticky;
        right: 0;
        z-index: 12;
        min-width: 132px;
        border-left: 1px solid #e4e4e7;
        box-shadow: -8px 0 16px -16px rgba(0, 0, 0, .45);
    }

    /*
     * Kolom Action harus opaque karena berada di atas kolom lain
     * ketika tabel digulir secara horizontal.
     */
    #crmApp .crm-table-shell tbody tr td:last-child {
        background-color: #fff;
    }

    #crmApp .crm-table-shell tbody tr:nth-child(even) td:last-child {
        background-color: #fafafa;
    }

    #crmApp .crm-table-shell tbody tr:hover td:last-child {
        background-color: #f4f4f5;
    }

    #crmApp .crm-table-shell thead th:last-child {
        z-index: 20;
    }

    #crmApp .crm-table-shell .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        width: max-content;
        border: 1px solid #e4e4e7;
        border-radius: 9999px;
        background: #f4f4f5;
        padding: 4px 9px;
        color: #3f3f46;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
    }

    #crmApp .crm-table-shell .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        border: 1px solid #e4e4e7;
        border-radius: 8px;
        background: #fff;
        padding: 6px 9px;
        color: #52525b;
        font-size: 11px;
        line-height: 1;
        transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease, transform 150ms ease;
    }

    #crmApp .crm-table-shell .btn:hover {
        border-color: #a1a1aa;
        background: #f4f4f5;
        color: #18181b;
        transform: translateY(-1px);
    }

    #crmApp .crm-table-shell .btn-success { color: #047857; }
    #crmApp .crm-table-shell .btn-warning { color: #a16207; }
    #crmApp .crm-table-shell .btn-danger { color: #dc2626; }
    #crmApp .crm-table-shell .btn-primary { background: #18181b; color: #fff; border-color: #18181b; }
    #crmApp .crm-table-shell .btn-link { min-height: 0; border: 0; background: transparent; padding: 4px 0; color: #2563eb; }

    #crmApp .crm-table-shell input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #18181b;
    }

    #crmApp .crm-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        min-width: 0;
    }

    #crmApp .crm-stack > div[style] {
        min-width: 0 !important;
        max-width: 100% !important;
    }

    #crmApp .crm-field-label {
        color: #a1a1aa;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .1em;
        line-height: 1;
        text-transform: uppercase;
    }

    #crmApp .crm-field-gap {
        margin-top: 5px;
    }

    #crmApp .crm-lead-name {
        color: #18181b;
        font-size: 13px;
        font-weight: 700;
    }

    #crmApp .crm-muted {
        color: #71717a;
        overflow-wrap: anywhere;
    }

    #crmApp .crm-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
    }

    #crmApp .crm-actions .btn {
        width: 32px;
        height: 32px;
        padding: 7px;
    }

    #crmApp .crm-actions .btn svg {
        width: 16px;
        height: 16px;
        flex: none;
    }

    #crmApp .crm-progress-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
    }

    #crmApp .crm-progress-item {
        display: flex;
        min-height: 32px;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        border: 1px solid #e4e4e7;
        border-radius: 8px;
        background: #fafafa;
        padding: 6px 8px;
        color: #71717a;
        font-size: 9px;
        font-weight: 600;
    }

    #crmApp .crm-follow-up-detail {
        margin-top: 8px;
    }
</style>
<div
    id="crmApp"
    data-ai-url="{{ url('/api/crm/ai') }}"
    data-human-url="{{ url('/api/crm/human') }}"
    data-meeting-url="{{ url('/api/crm/meeting') }}"
    data-crud-url="{{ url('/api/crm') }}"
    data-history-url="{{ url('/api/crm/history') }}"
    data-human-history-url="{{ url('/api/crm/human/history') }}"
    data-human-checklist-url="{{ url('/api/crm/human') }}"
    class="min-h-screen"
>
    <div class="mx-auto max-w-[1600px]">

        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>

                <h1 class="text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl">
                    CRM Leads Ekosistem
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                    Kelola lead, percakapan, tindak lanjut, dan jadwal meeting
                    dalam satu workspace CRM.
                </p>

                <div
                    id="crmTotal"
                    class="mt-4 text-xs font-medium text-zinc-400"
                >
                    Data CRM
                </div>
            </div>

            <button
                type="button"
                id="addLeadButton"
                class="inline-flex w-fit items-center justify-center gap-2 rounded-lg bg-zinc-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M12 5v14M5 12h14" />
                </svg>

                Tambah Lead
            </button>
        </div>


        {{-- Main CRM Workspace --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">

            {{-- CRM Navigation --}}
            <div class="border-b border-zinc-200 bg-zinc-50/70 px-4 py-4 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                            Workspace
                        </p>

                        <p class="mt-1 text-sm font-medium text-zinc-900">
                            Pipeline & Aktivitas
                        </p>
                    </div>

                    <nav
                        class="flex w-full gap-1 overflow-x-auto rounded-xl border border-zinc-200 bg-white p-1 lg:w-auto"
                        aria-label="CRM navigation"
                    >
                        {{-- AI --}}
                        <button
                            type="button"
                            class="crm-tab group inline-flex min-w-[120px] items-center justify-center gap-2 rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                            data-tab="ai"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z" />
                                <path d="M19 16l.7 2.3L22 19l-2.3.7L19 22l-.7-2.3L16 19l2.3-.7L19 16Z" />
                            </svg>

                            AI
                        </button>

                        {{-- Human --}}
                        <button
                            type="button"
                            class="crm-tab group inline-flex min-w-[120px] items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950"
                            data-tab="human"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M20 21a8 8 0 0 0-16 0" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>

                            Human
                        </button>

                        {{-- Meeting --}}
                        <button
                            type="button"
                            class="crm-tab group inline-flex min-w-[150px] items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950"
                            data-tab="meeting"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect x="3" y="4" width="18" height="17" rx="2" />
                                <path d="M16 2v4M8 2v4M3 10h18" />
                            </svg>

                            Jadwal Meeting
                        </button>
                    </nav>
                </div>
            </div>


            {{-- Filter --}}
            <div class="border-b border-zinc-200 px-4 py-5 sm:px-6">
                <form id="crmFilterForm">

                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100">
                            <svg
                                class="h-4 w-4 text-zinc-600"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M4 6h16M7 12h10M10 18h4" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-zinc-900">
                                Filter Data
                            </p>

                            <p class="text-xs text-zinc-400">
                                Sesuaikan rentang dan status lead
                            </p>
                        </div>
                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">

                        {{-- Jenis Tanggal --}}
                        <div id="dateTypeField">
                            <label for="dateFieldFilter" class="mb-2 block text-xs font-semibold text-zinc-700">
                                Filter Tanggal
                            </label>

                            <select id="dateFieldFilter" class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950">
                                <option value="chat">Chat Terbaru</option>
                                <option value="first_chat">Pertama Kali Chat</option>
                            </select>
                        </div>

                        {{-- Dari Tanggal --}}
                        <div>
                            <label
                                for="startDate"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Dari Tanggal
                            </label>

                            <input
                                type="date"
                                id="startDate"
                                value="{{ now('Asia/Jakarta')->subDay()->toDateString() }}"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Sampai Tanggal --}}
                        <div>
                            <label
                                for="endDate"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Sampai Tanggal
                            </label>

                            <input
                                type="date"
                                id="endDate"
                                value="{{ now('Asia/Jakarta')->toDateString() }}"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Layanan --}}
                        <div>
                            <label
                                for="layananFilter"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Layanan
                            </label>

                            <select
                                id="layananFilter"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="">
                                    Semua Layanan
                                </option>
                            </select>
                        </div>


                        {{-- Status --}}
                        <div>
                            <label
                                for="statusFilter"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Status
                            </label>

                            <select
                                id="statusFilter"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="">
                                    Semua Status
                                </option>
                            </select>
                        </div>


                        {{-- Actions --}}
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M4 6h16M7 12h10M10 18h4" />
                                </svg>

                                Filter
                            </button>

                            <button
                                type="button"
                                id="resetFilter"
                                class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-600 transition hover:border-zinc-300 hover:bg-zinc-50 hover:text-zinc-950"
                            >
                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M4 4v6h6" />
                                    <path d="M20 20v-6h-6" />
                                    <path d="M5.5 15a8 8 0 0 0 13-2M18.5 9a8 8 0 0 0-13 2" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </form>
            </div>


            {{-- Error State --}}
            <div
                id="crmError"
                class="m-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-900 sm:m-6"
                role="alert"
            >
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 shrink-0">
                            <svg
                                class="h-5 w-5 text-amber-600"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M12 9v4" />
                                <path d="M12 17h.01" />
                                <path d="M10.3 3.8 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-semibold">
                                CRM sedang mengalami gangguan
                            </p>

                            <p
                                id="crmErrorMessage"
                                class="mt-1 text-xs text-amber-700"
                            >
                                CRM sedang mengalami gangguan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        id="retryButton"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-300 bg-white px-4 py-2 text-xs font-semibold text-amber-800 transition hover:bg-amber-100"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M20 11a8.1 8.1 0 0 0-14.9-4" />
                            <path d="M4 5v5h5" />
                            <path d="M4 13a8.1 8.1 0 0 0 14.9 4" />
                            <path d="M20 19v-5h-5" />
                        </svg>

                        Coba Lagi
                    </button>

                </div>
            </div>


            {{-- CRM Content --}}
            <div class="min-w-0">

                @include('crm.ai')

                @include('crm.human')

                @include('crm.meeting')

            </div>

        </div>
    </div>
</div>


{{-- Modals --}}
@include('crm.modals')


{{-- CRM Scripts --}}
<script src="{{ asset('js/crm/ai.js') }}?v={{ filemtime(public_path('js/crm/ai.js')) }}"></script>
<script src="{{ asset('js/crm/human.js') }}?v={{ filemtime(public_path('js/crm/human.js')) }}"></script>
<script src="{{ asset('js/crm/meeting.js') }}?v={{ filemtime(public_path('js/crm/meeting.js')) }}"></script>
<script src="{{ asset('js/crm/common.js') }}?v={{ filemtime(public_path('js/crm/common.js')) }}"></script>
@endsection
