@extends('layout.app')

@section('content')

<div
    class="activity-page min-h-screen transition-all duration-200"
    id="activityDashboardContent"
>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <div class="mb-6 rounded-2xl bg-zinc-950 px-5 py-6 text-white sm:px-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                        Monitoring Sistem
                    </div>

                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">
                        Activity Log
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">
                        Pantau riwayat aktivitas seluruh akun dalam satu tempat.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-medium text-zinc-200">
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 3l7 3v5c0 4.6-3 8.2-7 10-4-1.8-7-5.4-7-10V6l7-3z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9.5 12l1.7 1.7 3.5-3.5"
                        />
                    </svg>

                    Khusus Admin
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">

            {{-- Total Log --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5">
                <div class="flex items-center gap-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 6h13M8 12h13M8 18h13"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 6h.01M3 12h.01M3 18h.01"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="text-xs text-zinc-500">
                            Total Log
                        </div>
                        <div class="mt-0.5 text-lg font-semibold tracking-tight text-zinc-950">
                            {{ number_format($total) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Perubahan Data --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5">
                <div class="flex items-center gap-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 20h9"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4L16.5 3.5z"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="text-xs text-zinc-500">
                            Perubahan Data
                        </div>
                        <div class="mt-0.5 text-lg font-semibold tracking-tight text-zinc-950">
                            {{ number_format(($actionCounts['CREATE'] ?? 0) + ($actionCounts['UPDATE'] ?? 0) + ($actionCounts['DELETE'] ?? 0)) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kunjungan --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5">
                <div class="flex items-center gap-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
                            />
                            <circle cx="12" cy="12" r="2.5" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="text-xs text-zinc-500">
                            Kunjungan
                        </div>
                        <div class="mt-0.5 text-lg font-semibold tracking-tight text-zinc-950">
                            {{ number_format($actionCounts['VISIT'] ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Akun Terdeteksi --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5">
                <div class="flex items-center gap-4">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-zinc-100 text-zinc-950">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"
                            />
                            <circle cx="9" cy="7" r="4" />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="text-xs text-zinc-500">
                            Akun Terdeteksi
                        </div>
                        <div class="mt-0.5 text-lg font-semibold tracking-tight text-zinc-950">
                            {{ number_format($uniqueUsers) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error --}}
        @if($error)
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                role="alert"
            >
                <svg
                    class="mt-0.5 h-5 w-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path
                        stroke-linecap="round"
                        d="M12 8v4"
                    />
                    <path
                        stroke-linecap="round"
                        d="M12 16h.01"
                    />
                </svg>

                <span>{{ $error }}</span>
            </div>
        @endif

        {{-- Activity Panel --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">

            {{-- Filters --}}
            <div class="border-b border-zinc-200 bg-zinc-50/70 p-4 sm:p-5">

                {{-- Client-side filter --}}
                <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-12">

                    <div class="lg:col-span-7">
                        <label
                            for="activitySearch"
                            class="mb-1.5 block text-xs font-semibold text-zinc-700"
                        >
                            Cari aktivitas
                        </label>

                        <div class="relative">
                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <circle cx="11" cy="11" r="7" />
                                <path
                                    stroke-linecap="round"
                                    d="M20 20l-4-4"
                                />
                            </svg>

                            <input
                                id="activitySearch"
                                type="search"
                                class="h-10 w-full rounded-lg border border-zinc-200 bg-white pl-9 pr-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                placeholder="Nama, email, modul, atau deskripsi..."
                            >
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <label
                            for="activityAction"
                            class="mb-1.5 block text-xs font-semibold text-zinc-700"
                        >
                            Aksi
                        </label>

                        <select
                            id="activityAction"
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="">Semua aksi</option>

                            @foreach(['VISIT', 'LOGIN', 'LOGOUT', 'CREATE', 'UPDATE', 'DELETE'] as $action)
                                <option value="{{ $action }}">
                                    {{ $action }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end lg:col-span-2">
                        <button
                            id="activityReset"
                            type="button"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 text-sm font-medium text-zinc-700 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 12a9 9 0 101.8-5.4"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 4v5h5"
                                />
                            </svg>

                            Reset
                        </button>
                    </div>
                </div>

                {{-- Server-side filters --}}
                <form
                    method="GET"
                    action="{{ route('activity-logs.index') }}"
                    id="activityServerFilters"
                >
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                        <div>
                            <label
                                for="activityEmail"
                                class="mb-1.5 block text-xs font-semibold text-zinc-700"
                            >
                                Email pengguna
                            </label>

                            <select
                                id="activityEmail"
                                name="email"
                                class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="">Semua email</option>

                                @foreach($emails as $email)
                                    <option
                                        value="{{ $email }}"
                                        @selected($selectedEmail === $email)
                                    >
                                        {{ $email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                for="activityDivision"
                                class="mb-1.5 block text-xs font-semibold text-zinc-700"
                            >
                                Divisi
                            </label>

                            <select
                                id="activityDivision"
                                name="division"
                                class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="">Semua divisi</option>

                                @foreach($divisions as $division)
                                    <option
                                        value="{{ $division->id }}"
                                        @selected($selectedDivision === (string) $division->id)
                                    >
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label
                                for="activityStartDate"
                                class="mb-1.5 block text-xs font-semibold text-zinc-700"
                            >
                                Dari tanggal
                            </label>

                            <input
                                id="activityStartDate"
                                name="start_date"
                                type="date"
                                value="{{ $startDate }}"
                                class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>

                        <div>
                            <label
                                for="activityEndDate"
                                class="mb-1.5 block text-xs font-semibold text-zinc-700"
                            >
                                Sampai tanggal
                            </label>

                            <input
                                id="activityEndDate"
                                name="end_date"
                                type="date"
                                value="{{ $endDate }}"
                                class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>

                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table
                    class="w-full min-w-[1100px] text-left text-sm"
                    id="activityTable"
                >
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50">
                            <th class="whitespace-nowrap px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Waktu
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Pengguna
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Aksi
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Modul
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Aktivitas
                            </th>

                            <th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">
                                Detail
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @forelse($logs as $log)
                            @php
                                $action = strtoupper($log['action'] ?? '-');

                                $badge = match($action) {
                                    'CREATE', 'LOGIN' => 'success',
                                    'UPDATE' => 'warning',
                                    'DELETE', 'LOGOUT' => 'danger',
                                    'VISIT' => 'info',
                                    default => 'secondary',
                                };

                                $createdAt = $log['created_at'] ?? null;

                                try {
                                    $displayTime = $createdAt
                                        ? \Illuminate\Support\Carbon::parse($createdAt)
                                            ->timezone('Asia/Jakarta')
                                            ->format('d M Y H:i:s')
                                        : '-';
                                } catch (\Throwable) {
                                    $displayTime = (string) $createdAt;
                                }

                                $searchable = strtolower(implode(' ', [
                                    $log['user_name'] ?? '',
                                    $log['email'] ?? '',
                                    $log['module'] ?? '',
                                    $log['description'] ?? '',
                                    $log['target_name'] ?? '',
                                    $action,
                                ]));
                            @endphp

                            <tr
                                data-activity-row
                                data-action="{{ $action }}"
                                data-search="{{ $searchable }}"
                                class="transition-colors hover:bg-zinc-50"
                            >
                                {{-- Time --}}
                                <td class="whitespace-nowrap px-5 py-4 align-top text-xs text-zinc-500">
                                    {{ $displayTime }}
                                </td>

                                {{-- User --}}
                                <td class="px-5 py-4 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-zinc-100 text-sm font-bold text-zinc-800">
                                            {{ strtoupper(substr($log['user_name'] ?? '?', 0, 1)) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div class="truncate font-semibold text-zinc-900">
                                                {{ $log['user_name'] ?? '-' }}
                                            </div>

                                            <div class="mt-0.5 truncate text-xs text-zinc-500">
                                                {{ $log['email'] ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Action --}}
                                <td class="px-5 py-4 align-top">
                                    @php
                                        $actionClasses = match($badge) {
                                            'success' => 'bg-zinc-950 text-white',
                                            'warning' => 'bg-zinc-200 text-zinc-800',
                                            'danger' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
                                            'info' => 'bg-zinc-100 text-zinc-700',
                                            default => 'bg-zinc-100 text-zinc-500',
                                        };
                                    @endphp

                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.08em] {{ $actionClasses }}">
                                        {{ $action }}
                                    </span>
                                </td>

                                {{-- Module --}}
                                <td class="px-5 py-4 align-top">
                                    <span class="inline-flex rounded-md bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600">
                                        {{ str_replace('_', ' ', $log['module'] ?? '-') }}
                                    </span>
                                </td>

                                {{-- Description --}}
                                <td class="px-5 py-4 align-top text-sm leading-6 text-zinc-700">
                                    {{ $log['description'] ?? '-' }}
                                </td>

                                {{-- Detail --}}
                                <td class="min-w-[300px] px-5 py-4 align-top">
                                    @if($log['human_detail'])
                                        <div class="rounded-lg border-l-2 border-zinc-300 bg-zinc-50 px-3 py-2.5 text-xs leading-5 text-zinc-700">
                                            <div class="mb-2 font-semibold text-zinc-900">
                                                {{ $log['human_detail']['summary'] }}
                                            </div>

                                            @forelse($log['human_detail']['items'] as $item)
                                                <div class="mb-1.5 last:mb-0">
                                                    <span class="text-zinc-500">
                                                        {{ $item['label'] }}:
                                                    </span>

                                                    @if(array_key_exists('old', $item))
                                                        @if($item['old_url'])
                                                            <a
                                                                href="{{ $item['old_url'] }}"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="font-medium text-zinc-700 underline decoration-zinc-300 underline-offset-2 hover:text-zinc-950"
                                                            >
                                                                {{ $item['old'] }}
                                                            </a>
                                                        @else
                                                            <span class="text-red-600">
                                                                <s>{{ $item['old'] }}</s>
                                                            </span>
                                                        @endif

                                                        <svg
                                                            class="mx-1 inline h-3 w-3 text-zinc-400"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            aria-hidden="true"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M5 12h14M13 6l6 6-6 6"
                                                            />
                                                        </svg>

                                                        @if($item['new_url'])
                                                            <a
                                                                href="{{ $item['new_url'] }}"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="font-medium text-zinc-900 underline decoration-zinc-300 underline-offset-2 hover:decoration-zinc-950"
                                                            >
                                                                {{ $item['new'] }}
                                                            </a>
                                                        @else
                                                            <span class="font-medium text-zinc-900">
                                                                {{ $item['new'] }}
                                                            </span>
                                                        @endif

                                                    @elseif($item['url'])
                                                        <a
                                                            href="{{ $item['url'] }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="font-medium text-zinc-900 underline decoration-zinc-300 underline-offset-2 hover:decoration-zinc-950"
                                                        >
                                                            {{ $item['value'] }}
                                                        </a>
                                                    @else
                                                        <span class="text-zinc-700">
                                                            {{ $item['value'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-zinc-500">
                                                    Tidak ada rincian atribut.
                                                </span>
                                            @endforelse
                                        </div>
                                    @else
                                        <span class="text-zinc-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr id="activityEmptyRow">
                                <td
                                    colspan="6"
                                    class="px-6 py-16 text-center"
                                >
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="mb-4 grid h-12 w-12 place-items-center rounded-2xl bg-zinc-100 text-zinc-500">
                                            <svg
                                                class="h-6 w-6"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M12 8v4l2.5 1.5"
                                                />
                                                <circle cx="12" cy="12" r="9" />
                                            </svg>
                                        </div>

                                        <div class="font-semibold text-zinc-900">
                                            Belum ada activity log
                                        </div>

                                        <div class="mt-1 text-sm text-zinc-500">
                                            Riwayat aktivitas akan muncul di sini.
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse

                        <tr
                            id="activityNoResult"
                            class="hidden"
                        >
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center"
                            >
                                <div class="text-sm text-zinc-500">
                                    Tidak ada log yang sesuai dengan filter.
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer / Pagination --}}
            <div class="flex flex-col gap-4 border-t border-zinc-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">

                <div class="text-xs text-zinc-500">
                    Menampilkan
                    <strong class="font-semibold text-zinc-900">
                        {{ $from }}–{{ $to }}
                    </strong>
                    dari
                    <strong class="font-semibold text-zinc-900">
                        {{ number_format($total) }}
                    </strong>
                    log
                </div>

                @if($lastPage > 1)
                    @php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);

                        $pageQuery = fn($page) => array_filter([
                            'page' => $page,
                            'email' => $selectedEmail ?: null,
                            'division' => $selectedDivision ?: null,
                            'start_date' => $startDate ?: null,
                            'end_date' => $endDate ?: null,
                        ]);
                    @endphp

                    <nav aria-label="Pagination activity log">
                        <ul class="flex items-center gap-1">

                            {{-- Previous --}}
                            <li class="{{ $currentPage <= 1 ? 'pointer-events-none opacity-40' : '' }}">
                                <a
                                    class="grid h-9 w-9 place-items-center rounded-lg border border-transparent text-zinc-600 transition hover:border-zinc-200 hover:bg-zinc-50 hover:text-zinc-950"
                                    href="{{ $currentPage > 1 ? route('activity-logs.index', $pageQuery($currentPage - 1)) : '#' }}"
                                    aria-label="Sebelumnya"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 18l-6-6 6-6"
                                        />
                                    </svg>
                                </a>
                            </li>

                            {{-- First page --}}
                            @if($startPage > 1)
                                <li>
                                    <a
                                        class="grid h-9 min-w-9 place-items-center rounded-lg px-2 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                                        href="{{ route('activity-logs.index', $pageQuery(1)) }}"
                                    >
                                        1
                                    </a>
                                </li>

                                @if($startPage > 2)
                                    <li>
                                        <span class="grid h-9 w-9 place-items-center text-xs text-zinc-400">
                                            …
                                        </span>
                                    </li>
                                @endif
                            @endif

                            {{-- Pages --}}
                            @for($page = $startPage; $page <= $endPage; $page++)
                                <li>
                                    <a
                                        class="grid h-9 min-w-9 place-items-center rounded-lg px-2 text-xs font-medium transition
                                            {{ $page === $currentPage
                                                ? 'bg-zinc-950 text-white'
                                                : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-950' }}"
                                        href="{{ route('activity-logs.index', $pageQuery($page)) }}"
                                    >
                                        {{ $page }}
                                    </a>
                                </li>
                            @endfor

                            {{-- Last page --}}
                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <li>
                                        <span class="grid h-9 w-9 place-items-center text-xs text-zinc-400">
                                            …
                                        </span>
                                    </li>
                                @endif

                                <li>
                                    <a
                                        class="grid h-9 min-w-9 place-items-center rounded-lg px-2 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                                        href="{{ route('activity-logs.index', $pageQuery($lastPage)) }}"
                                    >
                                        {{ $lastPage }}
                                    </a>
                                </li>
                            @endif

                            {{-- Next --}}
                            <li class="{{ $currentPage >= $lastPage ? 'pointer-events-none opacity-40' : '' }}">
                                <a
                                    class="grid h-9 w-9 place-items-center rounded-lg border border-transparent text-zinc-600 transition hover:border-zinc-200 hover:bg-zinc-50 hover:text-zinc-950"
                                    href="{{ $currentPage < $lastPage ? route('activity-logs.index', $pageQuery($currentPage + 1)) : '#' }}"
                                    aria-label="Berikutnya"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 18l6-6-6-6"
                                        />
                                    </svg>
                                </a>
                            </li>

                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function applyFilter() {
        const search = document.getElementById('activitySearch');
        const action = document.getElementById('activityAction');
        const rows = Array.from(document.querySelectorAll('[data-activity-row]'));
        const noResult = document.getElementById('activityNoResult');

        if (!search || !action || !noResult) return;

        const term = search.value.trim().toLowerCase();
        const selectedAction = action.value;
        let visible = 0;

        rows.forEach(function (row) {
            const matchesSearch =
                !term || row.dataset.search.includes(term);

            const matchesAction =
                !selectedAction || row.dataset.action === selectedAction;

            const show = matchesSearch && matchesAction;

            row.classList.toggle('hidden', !show);

            visible += show ? 1 : 0;
        });

        noResult.classList.toggle(
            'hidden',
            visible !== 0 || rows.length === 0
        );
    }

    async function loadActivityPage(url, addHistory = true) {
        const container =
            document.getElementById('activityDashboardContent');

        if (!container) return;

        container.classList.add('is-loading');

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal memuat activity log.');
            }

            const html = await response.text();

            const documentResult =
                new DOMParser().parseFromString(html, 'text/html');

            const freshContent =
                documentResult.getElementById('activityDashboardContent');

            if (!freshContent) {
                throw new Error(
                    'Konten activity log tidak ditemukan.'
                );
            }

            container.innerHTML = freshContent.innerHTML;
            container.classList.remove('is-loading');

            if (addHistory) {
                window.history.pushState(
                    { activityLog: true },
                    '',
                    url
                );
            }

            container.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

        } catch (error) {
            container.classList.remove('is-loading');
            window.location.href = url;
        }
    }

    document.addEventListener('input', function (event) {
        if (event.target.matches('#activitySearch')) {
            applyFilter();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('#activityAction')) {
            applyFilter();
            return;
        }

        if (
            event.target.matches(
                '#activityEmail, #activityDivision, #activityStartDate, #activityEndDate'
            )
        ) {
            const form =
                document.getElementById('activityServerFilters');

            const url = new URL(
                @json(route('activity-logs.index')),
                window.location.origin
            );

            if (event.target.matches('#activityDivision')) {
                const email =
                    document.getElementById('activityEmail');

                if (email) {
                    email.value = '';
                }
            }

            new FormData(form).forEach(function (value, key) {
                if (value) {
                    url.searchParams.set(key, value);
                }
            });

            loadActivityPage(url.toString());
        }
    });

    document.addEventListener('submit', function (event) {
        if (!event.target.matches('#activityServerFilters')) return;

        event.preventDefault();
    });

    document.addEventListener('click', function (event) {
        const paginationLink =
            event.target.closest('.activity-pagination a.page-link');

        if (paginationLink) {
            event.preventDefault();

            if (paginationLink.closest('.disabled')) {
                return;
            }

            loadActivityPage(paginationLink.href);
            return;
        }

        const reset =
            event.target.closest('#activityReset');

        if (!reset) return;

        const serverFilters =
            document.querySelectorAll(
                '#activityServerFilters select, #activityServerFilters input'
            );

        const hasServerFilter =
            Array.from(serverFilters).some(function (field) {
                return field.value;
            });

        if (hasServerFilter) {
            loadActivityPage(
                @json(route('activity-logs.index'))
            );
            return;
        }

        const search =
            document.getElementById('activitySearch');

        const action =
            document.getElementById('activityAction');

        search.value = '';
        action.value = '';

        applyFilter();
    });

    window.addEventListener('popstate', function () {
        loadActivityPage(window.location.href, false);
    });
});
</script>

@endsection