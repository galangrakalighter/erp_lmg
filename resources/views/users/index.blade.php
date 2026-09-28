@extends('layout.app')

@section('content')

<div class="mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8">

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <div class="mb-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div>
                <h1 class="text-2xl font-semibold tracking-[-0.04em] text-zinc-950 sm:text-3xl">
                    Manajemen Hierarki User
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                    Kelola user, role, divisi, serta struktur organisasi
                    dalam satu sistem terintegrasi.
                </p>
            </div>

            <button
                type="button"
                id="openTambahModal"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-950 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-white transition hover:bg-zinc-800"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    class="h-4 w-4"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 5v14M5 12h14"
                    />
                </svg>

                Tambah User
            </button>

        </div>
    </div>


    {{-- =========================================================
        MAIN
    ========================================================== --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white">

        {{-- =====================================================
            ACCOUNT STATUS NAVIGATION
        ====================================================== --}}
        <div class="border-b border-zinc-200 bg-zinc-50/60 px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">Status Akun</p>
                    <p class="mt-1 text-xs text-zinc-500">Pisahkan akun aktif dan akun yang sudah dikunci.</p>
                </div>

                <div id="accountStatusTabs" class="inline-flex w-full rounded-xl border border-zinc-200 bg-zinc-100 p-1 sm:w-auto" role="tablist">
                    <button type="button" class="account-status-tab flex flex-1 items-center justify-center gap-2 rounded-lg bg-white px-4 py-2 text-xs font-semibold text-zinc-950 shadow-sm transition sm:flex-none" data-status="unlocked" role="tab" aria-selected="true">
                        Akun Aktif
                        <span data-status-count="unlocked" class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">0</span>
                    </button>
                    <button type="button" class="account-status-tab flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-xs font-semibold text-zinc-500 transition hover:text-zinc-950 sm:flex-none" data-status="locked" role="tab" aria-selected="false">
                        Akun Terkunci
                        <span data-status-count="locked" class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">0</span>
                    </button>
                </div>
            </div>
        </div>


        {{-- =====================================================
            DIVISION NAVIGATION
        ====================================================== --}}
        <div id="divisionNavigationSection" class="border-b border-zinc-200">

            <div class="flex flex-col">

                {{-- Navigation heading --}}
                <div class="flex items-center justify-between px-5 pt-5 sm:px-6">

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-400">
                            Organizational Structure
                        </p>

                        <h2 class="mt-1 text-sm font-semibold text-zinc-950">
                            Pilih Divisi
                        </h2>
                    </div>

                </div>


                {{-- Navigation --}}
                <div class="mt-5 overflow-x-auto px-4 pb-0 sm:px-5">
                    <nav
                        id="divisionTabs"
                        class="flex min-w-max gap-1"
                        role="tablist"
                    >

                        <button
                            type="button"
                            class="division-tab group relative flex min-w-[190px] items-center gap-3 border-b-2 border-zinc-950 px-4 py-4 text-left text-zinc-950 transition"
                            data-divisi="idme"
                            data-name="IDME"
                            role="tab"
                            aria-selected="true"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-zinc-950 text-[10px] font-bold text-white transition">IDME</span>
                            <span class="flex min-w-0 flex-1 flex-col">
                                <span class="truncate text-xs font-semibold uppercase tracking-[0.08em]">IDME</span>
                                <span class="division-count mt-1 text-[10px] font-medium text-zinc-400" data-count-division="idme">-</span>
                            </span>
                        </button>

                        @foreach($divisions as $index => $div)

                            <button
                                type="button"
                                class="division-tab group relative flex min-w-[145px] items-center gap-3 border-b-2 px-4 py-4 text-left transition
                                    border-transparent text-zinc-400 hover:border-zinc-300 hover:text-zinc-800"
                                data-divisi="{{ $div->id }}"
                                data-name="{{ $div->name }}"
                                role="tab"
                                aria-selected="false"
                            >

                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[10px] font-bold transition
                                        bg-zinc-100 text-zinc-400 group-hover:bg-zinc-200 group-hover:text-zinc-700"
                                >
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <span class="flex min-w-0 flex-1 flex-col">
                                    <span class="truncate text-xs font-semibold uppercase tracking-[0.08em]">
                                        {{ $div->name }}
                                    </span>

                                    <span
                                        class="division-count mt-1 text-[10px] font-medium text-zinc-400"
                                        data-count-division="{{ $div->id }}"
                                    >
                                        -
                                    </span>
                                </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="h-3.5 w-3.5 opacity-0 transition group-hover:opacity-100"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m9 18 6-6-6-6"
                                    />
                                </svg>

                            </button>

                        @endforeach

                    </nav>
                </div>

            </div>

        </div>


        {{-- =====================================================
            TOOLBAR
        ====================================================== --}}
        <div id="userToolbar" class="border-b border-zinc-200 bg-zinc-50/60 px-5 py-4 sm:px-6">

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

                {{-- Active division --}}
                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-950 text-white">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                            />
                            <circle cx="9" cy="7" r="4"></circle>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-zinc-400">
                            Current Division
                        </p>

                        <div class="flex items-center gap-2">
                            <h3
                                id="currentDivisionTitle"
                                class="text-sm font-semibold text-zinc-950"
                            >
                                -
                            </h3>

                            <span class="text-zinc-300">•</span>

                            <span
                                id="currentDivisionCount"
                                class="text-xs text-zinc-500"
                            >
                                0 user
                            </span>
                        </div>
                    </div>

                </div>


                {{-- Search --}}
                <div class="w-full md:max-w-sm">

                    <div class="relative">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-4 w-4 text-zinc-400"
                            >
                                <circle cx="11" cy="11" r="7"></circle>
                                <path
                                    stroke-linecap="round"
                                    d="m20 20-4-4"
                                />
                            </svg>
                        </div>

                        <input
                            type="text"
                            id="custom_search"
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white pl-9 pr-9 text-sm text-zinc-700 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            placeholder="Cari nama, email, atau role..."
                        >

                        <button
                            type="button"
                            id="clearSearch"
                            class="absolute inset-y-0 right-0 hidden items-center pr-3 text-zinc-400 transition hover:text-zinc-950"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-4 w-4"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            USER LIST
        ====================================================== --}}
        <div class="p-4 sm:p-6">

            <div
                id="table-container"
                class="overflow-hidden rounded-xl border border-zinc-200"
            ></div>

        </div>

    </div>


    {{-- =========================================================
        ADD USER MODAL
    ========================================================== --}}
    <div
        id="tambahModal"
        class="fixed inset-0 z-[100] hidden"
        aria-hidden="true"
    >

        <div
            class="modal-backdrop absolute inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-close-modal="tambahModal"
        ></div>

        <div class="relative flex min-h-full items-center justify-center p-4">

            <div
                class="relative max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
            >

                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-5">

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-zinc-400">
                            User Management
                        </p>

                        <h2 class="mt-1 text-lg font-semibold tracking-tight text-zinc-950">
                            Tambah User Baru
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="close-modal flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-950"
                        data-modal="tambahModal"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 6l12 12M18 6 6 18"
                            />
                        </svg>
                    </button>

                </div>


                <form
                    action="{{ route('manajemen_user.store') }}"
                    method="POST"
                >
                    @csrf

                    <div class="space-y-7 px-6 py-6">

                        {{-- Profile --}}
                        <div>

                            <div class="mb-4 flex items-center gap-3">
                                <span class="h-px w-5 bg-zinc-950"></span>

                                <h3 class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                    Data Profil
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Nama Lengkap
                                    </label>

                                    <input
                                        type="text"
                                        name="name"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-800 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        placeholder="Nama"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Email
                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-800 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        placeholder="email@lmg.com"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Divisi Penempatan
                                    </label>

                                    <select
                                        name="divisi_id"
                                        id="modal_divisi"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        onchange="updateRoleOptions(this.value)"
                                        required
                                    >
                                        <option value="">-- Pilih Divisi --</option>

                                        <option value="idme">IDME</option>

                                        @foreach($divisions as $div)
                                            <option value="{{ $div->id }}">
                                                {{ $div->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="modal_jabatan_group" class="hidden">
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">Jabatan IDME</label>
                                    <select name="jabatan" id="modal_jabatan" disabled class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950">
                                        <option value="">-- Pilih Jabatan --</option>
                                        <option value="ceo">CEO</option>
                                        <option value="coo">COO</option>
                                        <option value="cmo">CMO</option>
                                        <option value="cfo">CFO</option>
                                    </select>
                                </div>

                                <div id="modal_role_group">
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Role Jabatan
                                    </label>

                                    <select
                                        name="role_id"
                                        id="modal_role"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none disabled:cursor-not-allowed disabled:bg-zinc-100 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        onchange="updateParentOptions()"
                                        required
                                        disabled
                                    >
                                        <option value="">-- Pilih Divisi Dahulu --</option>

                                        @foreach($roles as $role)
                                            <option
                                                value="{{ $role->id }}"
                                                data-divisi="{{ $role->division_id }}"
                                                data-level="{{ $role->level }}"
                                                data-parent="{{ $role->parent_id }}"
                                            >
                                                {{ strtoupper(str_replace('_', ' ', $role->role_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Password
                                    </label>

                                    <div class="relative">
                                        <input
                                            type="password"
                                            name="password"
                                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 pr-11 text-sm text-zinc-800 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                            required
                                        >
                                        <button type="button" data-password-toggle class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-zinc-400 transition hover:text-zinc-900" aria-label="Tampilkan password" title="Tampilkan password">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </div>


                        {{-- Hierarchy --}}
                        <div
                            id="hierarchy_panel"
                            class="hidden rounded-xl border border-zinc-200 bg-zinc-50 p-5"
                        >

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                                <div id="modal_parent_panel" class="md:border-r md:border-zinc-200 md:pr-6">

                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                                        Atasan Langsung
                                    </p>

                                    <select
                                        name="parent_id"
                                        id="modal_parent"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                    >
                                        <option value="">-- Tanpa Atasan --</option>
                                    </select>

                                    <p class="mt-2 text-[11px] text-zinc-400">
                                        User ini akan melapor ke siapa?
                                    </p>

                                </div>

                                <div id="subordinate_panel">

                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                                        Tarik Bawahan
                                    </p>

                                    <select
                                        name="subordinate_ids[]"
                                        id="modal_subordinates"
                                        class="select2-multiple h-10 w-full rounded-lg border border-zinc-200 bg-white"
                                        multiple
                                    ></select>

                                    <p class="mt-2 text-[11px] text-zinc-400">
                                        Siapa saja yang akan melapor ke user baru ini?
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="flex justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4">

                        <button
                            type="button"
                            class="close-modal rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-950"
                            data-modal="tambahModal"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-zinc-950 px-6 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-white transition hover:bg-zinc-800"
                        >
                            Simpan User
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =========================================================
        EDIT USER MODAL
    ========================================================== --}}
    <div
        id="editModal"
        class="fixed inset-0 z-[100] hidden"
        aria-hidden="true"
    >

        <div
            class="modal-backdrop absolute inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-close-modal="editModal"
        ></div>

        <div class="relative flex min-h-full items-center justify-center p-4">

            <div
                class="relative max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
            >

                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-5">

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-zinc-400">
                            User Management
                        </p>

                        <h2
                            id="editModalTitle"
                            class="mt-1 text-lg font-semibold tracking-tight text-zinc-950"
                        >
                            Edit User
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="close-modal flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-950"
                        data-modal="editModal"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 6l12 12M18 6 6 18"
                            />
                        </svg>
                    </button>

                </div>


                <form
                    id="formEditUser"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="space-y-7 px-6 py-6">

                        <div>

                            <div class="mb-4 flex items-center gap-3">
                                <span class="h-px w-5 bg-zinc-950"></span>

                                <h3 class="text-[10px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                                    Data Profil
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Nama Lengkap
                                    </label>

                                    <input
                                        type="text"
                                        name="name"
                                        id="edit_name"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-800 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Email
                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        id="edit_email"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-800 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Divisi Penempatan
                                    </label>

                                    <select
                                        name="divisi_id"
                                        id="edit_divisi"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        onchange="updateRoleOptionsEdit(this.value)"
                                        required
                                    >
                                        <option value="">-- Pilih Divisi --</option>

                                        <option value="idme">IDME</option>

                                        @foreach($divisions as $div)
                                            <option value="{{ $div->id }}">
                                                {{ $div->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="edit_jabatan_group" class="hidden">
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">Jabatan IDME</label>
                                    <select name="jabatan" id="edit_jabatan" disabled class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950">
                                        <option value="">-- Pilih Jabatan --</option>
                                        <option value="ceo">CEO</option>
                                        <option value="coo">COO</option>
                                        <option value="cmo">CMO</option>
                                        <option value="cfo">CFO</option>
                                    </select>
                                </div>

                                <div id="edit_role_group">
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Role Jabatan
                                    </label>

                                    <select
                                        name="role_id"
                                        id="edit_role"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none disabled:cursor-not-allowed disabled:bg-zinc-100 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        onchange="updateHierarchyPanelEdit()"
                                        required
                                        disabled
                                    >
                                        <option value="">-- Pilih Divisi Dahulu --</option>

                                        @foreach($roles as $role)
                                            <option
                                                value="{{ $role->id }}"
                                                data-divisi="{{ $role->division_id }}"
                                                data-level="{{ $role->level }}"
                                                data-parent="{{ $role->parent_id }}"
                                            >
                                                {{ strtoupper(str_replace('_', ' ', $role->role_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1.5 block text-xs font-medium text-zinc-600">
                                        Ganti Password
                                        <span class="font-normal text-zinc-400">
                                            (Opsional)
                                        </span>
                                    </label>

                                    <div class="relative">
                                        <input
                                            type="password"
                                            name="password"
                                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 pr-11 text-sm text-zinc-800 outline-none placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                            placeholder="Kosongkan jika tidak ingin mengubah password"
                                        >
                                        <button type="button" data-password-toggle class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-zinc-400 transition hover:text-zinc-900" aria-label="Tampilkan password" title="Tampilkan password">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </div>


                        <div
                            id="hierarchy_panel_edit"
                            class="hidden rounded-xl border border-zinc-200 bg-zinc-50 p-5"
                        >

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                                <div id="edit_parent_panel" class="md:border-r md:border-zinc-200 md:pr-6">

                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                                        Atasan Langsung
                                    </p>

                                    <select
                                        name="parent_id"
                                        id="edit_parent"
                                        class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm text-zinc-700 outline-none focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                    >
                                        <option value="">-- Tanpa Atasan --</option>
                                    </select>

                                    <p class="mt-2 text-[11px] text-zinc-400">
                                        User ini akan melapor ke siapa?
                                    </p>

                                </div>

                                <div>

                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                                        Tarik Bawahan
                                    </p>

                                    <select
                                        name="subordinate_ids[]"
                                        id="edit_subordinates"
                                        class="select2-multiple-edit w-full rounded-lg border border-zinc-200 bg-white"
                                        multiple
                                    ></select>

                                    <p class="mt-2 text-[11px] text-zinc-400">
                                        Siapa yang akan melapor ke user ini?
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="flex justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4">

                        <button
                            type="button"
                            class="close-modal rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-950"
                            data-modal="editModal"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-zinc-950 px-6 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-white transition hover:bg-zinc-800"
                        >
                            Simpan Perubahan
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =========================================================
        DELETE MODAL
    ========================================================== --}}
    <div
        id="deleteModal"
        class="fixed inset-0 z-[110] hidden"
        aria-hidden="true"
    >

        <div
            class="modal-backdrop absolute inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-close-modal="deleteModal"
        ></div>

        <div class="relative flex min-h-full items-center justify-center p-4">

            <div
                class="relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl"
                role="dialog"
                aria-modal="true"
            >

                <div class="border-b border-zinc-200 px-6 py-5">

                    <div class="flex items-center gap-3">

                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-600">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v4"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 17h.01"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.3 3.7 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3L13.7 3.7a2 2 0 0 0-3.4 0Z"
                                />
                            </svg>
                        </div>

                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-red-500">
                                Warning
                            </p>

                            <h2 class="mt-1 text-lg font-semibold tracking-tight text-zinc-950">
                                Hapus Akun
                            </h2>
                        </div>

                    </div>

                </div>


                <div class="px-6 py-7 text-center">

                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-500">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="h-7 w-7"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 3h6"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 6h16"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m7 6 1 14h8l1-14"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M10 10v6M14 10v6"
                            />
                        </svg>

                    </div>

                    <p class="text-sm leading-6 text-zinc-600">
                        Apakah Anda yakin ingin menghapus
                    </p>

                    <p
                        id="delete_user_name"
                        class="mt-1 text-lg font-semibold text-red-600"
                    ></p>

                    <p class="mx-auto mt-4 max-w-sm text-xs leading-5 text-zinc-400">
                        Akun akan dihilangkan dari daftar dan tidak dapat login,
                        tetapi seluruh datanya tetap tersimpan di database.
                    </p>

                </div>


                <div class="flex justify-center gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4">

                    <button
                        type="button"
                        class="close-modal rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-zinc-600 transition hover:border-zinc-400 hover:text-zinc-950"
                        data-modal="deleteModal"
                    >
                        Batal
                    </button>

                    <form
                        id="formDeleteUser"
                        method="POST"
                        class="inline"
                    >
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="rounded-lg bg-red-600 px-6 py-2.5 text-xs font-semibold uppercase tracking-[0.08em] text-white transition hover:bg-red-700"
                        >
                            Hapus Akun
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


@push('script')

<script>

    /*
    |--------------------------------------------------------------------------
    | ORGANIZATION DATA
    |--------------------------------------------------------------------------
    */

    const divisionHierarchy = {
        "IDME": [
            { role: "CEO", label: "CEO" },
            { role: "COO", label: "COO" },
            { role: "CMO", label: "CMO" },
            { role: "CFO", label: "CFO" }
        ],

        "Digital Marketing": [
            { role: "Busines Unit Head", label: "Business Unit Head" },
            { role: "Digital Marketer", label: "Digital Marketer" },
            { role: "Advertising", label: "Advertising" }
        ],

        "Multimedia": [
            { role: "Busines Unit Head", label: "Business Unit Head" },
            { role: "Content Creator Manager", label: "Content Creator Manager" },
            { role: "Content Creator", label: "Content Creator" }
        ],

        "Business Dev": [
            { role: "Busines Unit Head", label: "Busines Unit Head" },
            { role: "Busines Development Manager", label: "Busines Development Manager" },
            { role: "Busines Development Specialist", label: "Busines Development Specialist" }
        ],

        "MCN": [
            { role: "Busines Unit Head", label: "Busines Unit Head" },
            { role: "MCN Specialis Manager", label: "MCN Specialis Manager" },
            { role: "MCN Specialis", label: "MCN Specialis" }
        ],

        "Tech": [
            { role: "Busines Unit Head", label: "Busines Unit Head" },
            { role: "Project Manager", label: "Project Manager" },
            { role: "Developer", label: "Developer" }
        ],

        "Finance": [
            { role: "Busines Unit Head", label: "Busines Unit Head" }
        ]
    };


    /*
    |--------------------------------------------------------------------------
    | GLOBAL STATE
    |--------------------------------------------------------------------------
    */

    let organization = {};
    let allUsers = [];
    let currentDivisionId = null;
    let currentDivisionName = null;
    let currentAccountStatus = 'unlocked';
    const financeDivisionId = @json($divisions->firstWhere('name', 'Finance')?->id);

    function isExecutivePlacement(divisiId) {
        return ['lmg', 'idme'].includes(String(divisiId));
    }

    function isFinancePlacement(divisiId) {
        return String(divisiId) === String(financeDivisionId);
    }

    function cfoUsers(excludeUserId = null) {
        return allUsers.filter(function (user) {
            return !user.is_locked && user.is_admin &&
                String(user.jabatan ?? '').toLowerCase() === 'cfo' &&
                String(user.id) !== String(excludeUserId ?? '');
        });
    }


    /*
    |--------------------------------------------------------------------------
    | MODAL HELPERS
    |--------------------------------------------------------------------------
    */

    function openModal(id) {

        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }


    function closeModal(id) {

        const modal = document.getElementById(id);

        if (!modal) return;

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');

        const anyOpen =
            !$('#tambahModal').hasClass('hidden') ||
            !$('#editModal').hasClass('hidden') ||
            !$('#deleteModal').hasClass('hidden');

        if (!anyOpen) {
            document.body.classList.remove('overflow-hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DOCUMENT READY
    |--------------------------------------------------------------------------
    */

    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | OPEN ADD USER
        |--------------------------------------------------------------------------
        */

        $('#openTambahModal').on('click', function () {
            openModal('tambahModal');
        });


        /*
        |--------------------------------------------------------------------------
        | CLOSE MODALS
        |--------------------------------------------------------------------------
        */

        $('.close-modal').on('click', function () {
            closeModal($(this).data('modal'));
        });


        $('[data-close-modal]').on('click', function () {
            closeModal($(this).data('close-modal'));
        });


        $(document).on('keydown', function (e) {

            if (e.key !== 'Escape') {
                return;
            }

            ['tambahModal', 'editModal', 'deleteModal'].forEach(function (id) {

                if (!$('#' + id).hasClass('hidden')) {
                    closeModal(id);
                }

            });

        });


        /*
        |--------------------------------------------------------------------------
        | SELECT2
        |--------------------------------------------------------------------------
        */

        if ($.isFunction($.fn.select2)) {

            $('.select2-multiple').select2({
                placeholder: "Pilih satu atau lebih bawahan",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#tambahModal')
            });

            $('.select2-multiple-edit').select2({
                placeholder: "Pilih satu atau lebih bawahan",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#editModal')
            });

        }


        /*
        |--------------------------------------------------------------------------
        | LOAD USERS ONCE
        |--------------------------------------------------------------------------
        */

        loadUsers();


        /*
        |--------------------------------------------------------------------------
        | DIVISION NAVIGATION
        |--------------------------------------------------------------------------
        */

        $('.division-tab').on('click', function () {

            const $this = $(this);

            const divisiId = $this.data('divisi');
            const divisiName = $this.data('name');

            switchDivision(
                divisiId,
                divisiName,
                $this
            );

        });


        /*
        |--------------------------------------------------------------------------
        | ACCOUNT STATUS TABS
        |--------------------------------------------------------------------------
        */

        $('.account-status-tab').on('click', function () {
            currentAccountStatus = $(this).data('status');

            $('.account-status-tab')
                .removeClass('bg-white text-zinc-950 shadow-sm')
                .addClass('text-zinc-500')
                .attr('aria-selected', 'false');

            $(this)
                .removeClass('text-zinc-500')
                .addClass('bg-white text-zinc-950 shadow-sm')
                .attr('aria-selected', 'true');

            $('#custom_search').val('');
            $('#clearSearch').addClass('hidden').removeClass('flex');

            const showingLockedAccounts = currentAccountStatus === 'locked';
            $('#divisionNavigationSection').toggleClass('hidden', showingLockedAccounts);

            if (showingLockedAccounts) {
                $('#currentDivisionTitle').text('Semua Divisi');
            } else {
                $('#currentDivisionTitle').text(currentDivisionName ?? '-');
            }

            updateDivisionCounts();
            renderUsers();
        });


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $('#custom_search').on('input', function () {

            const value =
                $(this)
                    .val()
                    .toLowerCase()
                    .trim();

            $('#clearSearch').toggleClass(
                'hidden',
                !value
            );

            $('#clearSearch').toggleClass(
                'flex',
                !!value
            );

            renderUsers();

        });


        /*
        |--------------------------------------------------------------------------
        | CLEAR SEARCH
        |--------------------------------------------------------------------------
        */

        $('#clearSearch').on('click', function () {

            $('#custom_search')
                .val('')
                .trigger('input');

        });

    });


    /*
    |--------------------------------------------------------------------------
    | LOAD USERS
    |--------------------------------------------------------------------------
    */

    function loadUsers() {

        const container =
            $('#table-container');

        container.html(`
            <div class="flex min-h-[220px] items-center justify-center">
                <div class="flex items-center gap-3 text-xs text-zinc-400">
                    <svg
                        class="h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        ></path>
                    </svg>

                    Memuat data user...
                </div>
            </div>
        `);


        $.ajax({

            url: 'api/users/json',

            method: 'GET',

            success: function (json) {

                allUsers =
                    Array.isArray(json)
                        ? json
                        : (json.data ?? []);


                updateDivisionCounts();


                const $firstTab =
                    $('#divisionTabs .division-tab').first();


                if ($firstTab.length) {

                    switchDivision(
                        $firstTab.data('divisi'),
                        $firstTab.data('name'),
                        $firstTab
                    );

                }

            },

            error: function () {

                container.html(`
                    <div class="flex min-h-[220px] flex-col items-center justify-center px-6 text-center">

                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-500">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v4"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 17h.01"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.3 3.7 2.8 17a2 2 0 0 0 1.74 3h14.92a2 2 0 0 0 1.74-3L13.7 3.7a2 2 0 0 0-3.4 0Z"
                                />
                            </svg>
                        </div>

                        <p class="text-sm font-semibold text-zinc-800">
                            Gagal memuat data
                        </p>

                        <p class="mt-1 text-xs text-zinc-400">
                            Silakan refresh halaman dan coba kembali.
                        </p>

                    </div>
                `);

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | SWITCH DIVISION
    |--------------------------------------------------------------------------
    */

    function switchDivision(
        divisiId,
        divisiName,
        $activeTab
    ) {

        currentDivisionId = divisiId;
        currentDivisionName = divisiName;


        /*
        | Active navigation
        */

        $('.division-tab')
            .each(function () {

                const $tab = $(this);

                const isActive =
                    String($tab.data('divisi')) ===
                    String(divisiId);


                const $number =
                    $tab.find('span').first();


                if (isActive) {

                    $tab
                        .removeClass(
                            'border-transparent text-zinc-400'
                        )
                        .addClass(
                            'border-zinc-950 text-zinc-950'
                        );

                    $tab.attr(
                        'aria-selected',
                        'true'
                    );

                    $number
                        .removeClass(
                            'bg-zinc-100 text-zinc-400'
                        )
                        .addClass(
                            'bg-zinc-950 text-white'
                        );

                } else {

                    $tab
                        .removeClass(
                            'border-zinc-950 text-zinc-950'
                        )
                        .addClass(
                            'border-transparent text-zinc-400'
                        );

                    $tab.attr(
                        'aria-selected',
                        'false'
                    );

                    $number
                        .removeClass(
                            'bg-zinc-950 text-white'
                        )
                        .addClass(
                            'bg-zinc-100 text-zinc-400'
                        );

                }

            });


        /*
        | Update heading
        */

        $('#currentDivisionTitle')
            .text(divisiName);

        $('#activeDivisionLabel')
            .text(divisiName);

        updateAccountStatusCounts();
        updateDivisionCounts();


        /*
        | Reset search when changing division
        */

        $('#custom_search')
            .val('');

        $('#clearSearch')
            .addClass('hidden')
            .removeClass('flex');


        /*
        | Render
        */

        renderUsers();

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE DIVISION COUNTS
    |--------------------------------------------------------------------------
    */

    function updateDivisionCounts() {

        $('.division-count').each(function () {

            const divisionId =
                $(this).data('count-division');


            const count =
                allUsers.filter(function (user) {

                    const matchesStatus = currentAccountStatus === 'locked'
                        ? Boolean(user.is_locked)
                        : !user.is_locked;

                    return matchesStatus && (user.user_divisions ?? [])
                        .some(function (division) {

                            return String(
                                division.division_id
                            ) === String(divisionId);

                        });

                }).length;


            $(this).text(
                `${count} ${currentAccountStatus === 'locked' ? 'terkunci' : 'aktif'}`
            );

        });

    }


    function getCurrentDivisionUsersByStatus(status) {
        return allUsers.filter(function (user) {
            const matchesStatus = status === 'locked'
                ? Boolean(user.is_locked)
                : !user.is_locked;

            if (status === 'locked') {
                return matchesStatus;
            }

            if (!currentDivisionId) {
                return false;
            }

            return matchesStatus && (user.user_divisions ?? []).some(function (division) {
                return String(division.division_id) === String(currentDivisionId);
            });
        });
    }


    function updateAccountStatusCounts() {
        $('[data-status-count="unlocked"]').text(allUsers.filter(user => !user.is_locked).length);
        $('[data-status-count="locked"]').text(allUsers.filter(user => Boolean(user.is_locked)).length);
    }


    /*
    |--------------------------------------------------------------------------
    | GET CURRENT DIVISION USERS
    |--------------------------------------------------------------------------
    */

    function getCurrentDivisionUsers() {
        return getCurrentDivisionUsersByStatus(currentAccountStatus);

    }


    /*
    |--------------------------------------------------------------------------
    | RENDER USERS
    |--------------------------------------------------------------------------
    */

    function renderUsers() {

        const container =
            $('#table-container');

        updateAccountStatusCounts();

        if (currentAccountStatus === 'locked') {
            renderLockedAccounts();
            return;
        }


        let users =
            getCurrentDivisionUsers();


        const search =
            $('#custom_search')
                .val()
                .toLowerCase()
                .trim();


        /*
        | Search current division only
        */

        if (search) {

            users =
                users.filter(function (user) {

                    const placement =
                        (user.user_divisions ?? [])
                            .find(function (division) {

                                return String(
                                    division.division_id
                                ) === String(
                                    currentDivisionId
                                );

                            });


                    const role =
                        placement?.role ?? '';


                    const searchable = [
                        user.name ?? '',
                        user.email ?? '',
                        role,
                        role.replace(/_/g, ' ')
                    ]
                        .join(' ')
                        .toLowerCase();


                    return searchable.includes(search);

                });

        }


        /*
        | Update count
        */

        const totalCurrent =
            getCurrentDivisionUsers().length;


        const showing =
            users.length;


        $('#currentDivisionCount')
            .text(
                search
                    ? `${showing} dari ${totalCurrent} user`
                    : `${totalCurrent} ${totalCurrent === 1 ? 'user' : 'users'}`
            );


        /*
        | Build organization
        */

        buildOrganization(
            getCurrentDivisionUsers(),
            currentDivisionId
        );


        /*
        | Empty
        */

        if (!users.length) {

            container.html(`
                <div class="flex min-h-[220px] flex-col items-center justify-center px-6 text-center">

                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            class="h-6 w-6"
                        >
                            <circle cx="11" cy="11" r="7"></circle>

                            <path
                                stroke-linecap="round"
                                d="m20 20-4-4"
                            />

                        </svg>

                    </div>

                    <p class="text-sm font-semibold text-zinc-800">
                        ${search ? 'User tidak ditemukan' : 'Belum ada user'}
                    </p>

                    <p class="mt-1 max-w-sm text-xs leading-5 text-zinc-400">
                        ${
                            search
                                ? 'Coba gunakan kata kunci lain.'
                                : currentAccountStatus === 'locked'
                                    ? `Tidak ada akun terkunci pada divisi ${currentDivisionName}.`
                                    : `Belum ada akun aktif pada divisi ${currentDivisionName}.`
                        }
                    </p>

                </div>
            `);

            return;

        }


        /*
        | Hierarchy
        */

        const hierarchy =
            divisionHierarchy[currentDivisionName] || [];


        /*
        | Table header
        */

        const hierarchyHeaders =
            hierarchy
                .map(function (item) {

                    return `
                        <th class="whitespace-nowrap px-4 py-3">
                            ${item.label}
                        </th>
                    `;

                })
                .join('');

        const relationshipHeaders = `
            <th class="min-w-[170px] px-4 py-3">Atasan langsung</th>
            <th class="min-w-[170px] px-4 py-3">Bawahan langsung</th>
        `;


        container.html(`

            <div class="overflow-x-auto">

                <table class="w-full min-w-[950px] text-left text-xs">

                    <thead class="border-b border-zinc-200 bg-zinc-50">

                        <tr class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">

                            <th class="w-12 px-4 py-3 text-center">
                                No
                            </th>

                            <th class="min-w-[220px] px-4 py-3">
                                User
                            </th>

                            <th class="min-w-[150px] px-4 py-3">
                                Role
                            </th>

                            ${relationshipHeaders}

                            ${hierarchyHeaders}

                            <th class="w-32 px-4 py-3 text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        id="userTableBody"
                        class="divide-y divide-zinc-100"
                    ></tbody>

                </table>

            </div>

            <div class="border-t border-zinc-100 bg-zinc-50/50 px-4 py-3">

                <div class="flex items-center justify-between">

                    <p class="text-[10px] uppercase tracking-[0.12em] text-zinc-400">
                        ${users.length} user ditampilkan
                    </p>

                    <p class="hidden text-[10px] text-zinc-400 sm:block">
                        ${currentDivisionName}
                    </p>

                </div>

            </div>

        `);


        /*
        | Rows
        */

        const rows =
            users.map(function (user, index) {

                return buildUserRow(
                    user,
                    index,
                    hierarchy
                );

            })
            .join('');


        $('#userTableBody')
            .html(rows);

    }


    function renderLockedAccounts() {
        const container = $('#table-container');
        const search = $('#custom_search').val().toLowerCase().trim();
        let users = allUsers.filter(user => Boolean(user.is_locked));

        if (search) {
            users = users.filter(function (user) {
                const placements = (user.user_divisions ?? []).flatMap(function (division) {
                    return [division.division?.name ?? '', division.role ?? ''];
                });
                return [user.name ?? '', user.email ?? '', ...placements]
                    .join(' ')
                    .toLowerCase()
                    .includes(search);
            });
        }

        const totalLocked = allUsers.filter(user => Boolean(user.is_locked)).length;
        $('#currentDivisionCount').text(search ? `${users.length} dari ${totalLocked} akun` : `${totalLocked} akun terkunci`);

        if (!users.length) {
            container.html(`
                <div class="flex min-h-[240px] flex-col items-center justify-center px-6 text-center">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-6 w-6"><rect x="5" y="10" width="14" height="11" rx="2"></rect><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                    </div>
                    <p class="text-sm font-semibold text-zinc-800">${search ? 'Akun tidak ditemukan' : 'Tidak ada akun terkunci'}</p>
                    <p class="mt-1 text-xs text-zinc-400">${search ? 'Coba gunakan kata kunci lain.' : 'Semua akun saat ini dalam keadaan aktif.'}</p>
                </div>
            `);
            return;
        }

        const rows = users.map(function (user, index) {
            const placements = user.user_divisions ?? [];
            const divisions = [...new Set(placements.map(item => item.division?.name).filter(Boolean))];
            const roles = [...new Set(placements.map(item => item.role).filter(Boolean))];
            const escapedName = escapeHtml(user.name ?? '-');
            const escapedEmail = escapeHtml(user.email ?? '-');

            return `
                <tr class="group transition hover:bg-zinc-50">
                    <td class="px-4 py-4 text-center text-[11px] font-medium text-zinc-300">${String(index + 1).padStart(2, '0')}</td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-950 text-[10px] font-bold uppercase text-white">
                                ${user.photo_profile_url ? `<img src="${escapeHtml(user.photo_profile_url)}" alt="Foto ${escapedName}" class="h-full w-full object-cover">` : getInitials(user.name)}
                            </div>
                            <div class="min-w-0"><div class="truncate font-semibold text-zinc-900">${escapedName}</div><div class="mt-0.5 truncate text-[11px] text-zinc-400">${escapedEmail}</div></div>
                        </div>
                    </td>
                    <td class="px-4 py-4"><div class="flex flex-wrap gap-1">${divisions.length ? divisions.map(item => `<span class="rounded-full border border-zinc-200 bg-zinc-50 px-2 py-1 text-[10px] font-semibold text-zinc-600">${escapeHtml(item)}</span>`).join('') : '<span class="text-zinc-300">—</span>'}</div></td>
                    <td class="px-4 py-4"><div class="flex flex-wrap gap-1">${roles.length ? roles.map(item => `<span class="rounded-full bg-zinc-100 px-2 py-1 text-[10px] font-semibold text-zinc-600">${escapeHtml(item)}</span>`).join('') : '<span class="text-zinc-300">—</span>'}</div></td>
                    <td class="px-4 py-4">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="toggleLockUser(${user.id}, '${escapeJs(user.name ?? '-')}', true)" title="Buka kunci akun" class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-amber-300 bg-amber-50 px-2.5 text-[10px] font-semibold text-amber-700 transition hover:bg-amber-100">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><rect x="5" y="10" width="14" height="11" rx="2"></rect><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 7.5-2"></path></svg> Buka Kunci
                            </button>
                            <button type="button" onclick="editUser(${user.id})" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200 text-zinc-500 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14 6 4 4M5 19l3.5-.75L18.5 8.25a1.5 1.5 0 0 0-2.12-2.12L6.38 16.13 5 19Z"></path></svg></button>
                            <button type="button" onclick="deleteUser(${user.id}, '${escapeJs(user.name ?? '-')}')" title="Hapus akun" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200 text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path stroke-linecap="round" d="M4 7h16M10 11v6M14 11v6M9 7l1-2h4l1 2M6 7l1 14h10l1-14"></path></svg></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        container.html(`
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-xs">
                    <thead class="border-b border-zinc-200 bg-zinc-50"><tr class="text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500"><th class="w-12 px-4 py-3 text-center">No</th><th class="min-w-[240px] px-4 py-3">User</th><th class="min-w-[180px] px-4 py-3">Divisi</th><th class="min-w-[180px] px-4 py-3">Role</th><th class="w-48 px-4 py-3 text-center">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-zinc-100">${rows}</tbody>
                </table>
            </div>
            <div class="border-t border-zinc-100 bg-zinc-50/50 px-4 py-3 text-[10px] uppercase tracking-[0.12em] text-zinc-400">${users.length} akun terkunci ditampilkan dari seluruh divisi</div>
        `);
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD USER ROW
    |--------------------------------------------------------------------------
    */

    function buildUserRow(
        user,
        index,
        hierarchy
    ) {

        const current =
            (user.user_divisions ?? [])
                .find(function (division) {

                    return String(
                        division.division_id
                    ) === String(currentDivisionId);

                });


        const role =
            current?.role ?? '-';

        const divisionUsers = getCurrentDivisionUsers();
        const parent = current?.parent_id
            ? divisionUsers.find(item => String(item.id) === String(current.parent_id))
            : null;
        const parentEmail = current?.parent?.email ?? parent?.email ?? null;
        const subordinates = divisionUsers.filter(function (item) {
            const placement = (item.user_divisions ?? []).find(division => String(division.division_id) === String(currentDivisionId));
            return placement && String(placement.parent_id) === String(user.id);
        });
        const relationshipCells = `
            <td class="px-4 py-4"><span class="text-[11px] font-medium text-zinc-600">${parentEmail ? escapeHtml(parentEmail) : '—'}</span></td>
            <td class="px-4 py-4"><div class="flex flex-col gap-1">${subordinates.length ? subordinates.map(item => `<span class="text-[11px] font-medium text-zinc-600">${escapeHtml(item.email)}</span>`).join('') : '<span class="text-zinc-300">—</span>'}</div></td>
        `;


        const hierarchyCells =
            hierarchy
                .map(function (item) {

                    const targets =
                        getHierarchyUser(
                            user,
                            currentDivisionId,
                            item.role
                        );


                    if (!targets.length) {

                        return `
                            <td class="px-4 py-3">
                                <span class="text-zinc-300">
                                    —
                                </span>
                            </td>
                        `;

                    }


                    return `
                        <td class="px-4 py-3">

                            <div class="flex flex-col gap-1">

                                ${
                                    targets
                                        .map(function (target) {

                                            const isSelf =
                                                target.email &&
                                                user.email &&
                                                target.email.toLowerCase() ===
                                                user.email.toLowerCase();


                                            if (isSelf) {

                                                return `
                                                    <span class="inline-flex w-fit rounded-full bg-zinc-950 px-2 py-1 text-[9px] font-semibold uppercase tracking-wide text-white">
                                                        Self
                                                    </span>
                                                `;

                                            }


                                            return `
                                                <span class="text-[11px] font-medium text-zinc-600">
                                                    ${escapeHtml(target.email ?? '-')}
                                                </span>
                                            `;

                                        })
                                        .join('')

                                }

                            </div>

                        </td>
                    `;

                })
                .join('');


        const escapedName =
            escapeHtml(user.name ?? '-');


        const escapedEmail =
            escapeHtml(user.email ?? '-');


        const escapedRole =
            escapeHtml(
                role
                    .replace(/_/g, ' ')
                    .toUpperCase()
            );


        return `
            <tr class="group transition hover:bg-zinc-50">

                <td class="px-4 py-4 text-center">
                    <span class="text-[11px] font-medium text-zinc-300">
                        ${String(index + 1).padStart(2, '0')}
                    </span>
                </td>


                <td class="px-4 py-4">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-950 text-[10px] font-bold uppercase text-white">
                            ${user.photo_profile_url
                                ? `<img src="${escapeHtml(user.photo_profile_url)}" alt="Foto ${escapedName}" class="h-full w-full object-cover">`
                                : getInitials(user.name)}
                        </div>

                        <div class="min-w-0">

                            <div class="truncate font-semibold text-zinc-900">
                                ${escapedName}
                            </div>

                            ${user.is_locked ? '<span class="mt-1 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-amber-700">Terkunci</span>' : ''}

                            <div class="mt-0.5 truncate text-[11px] text-zinc-400">
                                ${escapedEmail}
                            </div>

                        </div>

                    </div>

                </td>


                <td class="px-4 py-4">

                    <span class="inline-flex rounded-full border border-zinc-200 bg-zinc-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-zinc-600">
                        ${escapedRole}
                    </span>

                </td>


                ${relationshipCells}


                ${hierarchyCells}


                <td class="px-4 py-4">

                    <div class="flex items-center justify-center gap-1">

                        <button
                            type="button"
                            onclick="toggleLockUser(${user.id}, '${escapeJs(user.name ?? '-')}', ${user.is_locked ? 'true' : 'false'})"
                            title="${user.is_locked ? 'Buka kunci akun' : 'Kunci akun'}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border transition ${user.is_locked ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-zinc-200 text-zinc-500 hover:border-zinc-950 hover:bg-zinc-950 hover:text-white'}"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5">
                                <rect x="5" y="10" width="14" height="11" rx="2"></rect>
                                <path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>
                        </button>

                        <button
                            type="button"
                            onclick="editUser(${user.id})"
                            title="Edit"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200 text-zinc-500 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m14 6 4 4"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 19 3.5-.75L18.5 8.25a1.5 1.5 0 0 0-2.12-2.12L6.38 16.13 5 19Z"
                                />
                            </svg>

                        </button>


                        <button
                            type="button"
                            onclick="deleteUser(${user.id}, '${escapeJs(user.name ?? '-')}')"
                            title="Hapus akun"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-zinc-200 text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white"
                        >

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-3.5 w-3.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 3h6"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4 6h16"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m7 6 1 14h8l1-14"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10 10v6M14 10v6"
                                />
                            </svg>

                        </button>

                    </div>

                </td>

            </tr>
        `;

    }


    /*
    |--------------------------------------------------------------------------
    | BUILD ORGANIZATION
    |--------------------------------------------------------------------------
    */

    function buildOrganization(
        users,
        divisiId
    ) {

        organization = {};


        users.forEach(function (user) {

            const current =
                (user.user_divisions ?? [])
                    .find(function (division) {

                        return String(
                            division.division_id
                        ) === String(divisiId);

                    });


            if (!current) {
                return;
            }


            let root = user;

            let parentId =
                current.parent_id;


            while (parentId) {

                const parentUser =
                    users.find(function (u) {

                        return String(u.id) ===
                            String(parentId);

                    });


                if (!parentUser) {
                    break;
                }


                const parentDivision =
                    (parentUser.user_divisions ?? [])
                        .find(function (division) {

                            return String(
                                division.division_id
                            ) === String(divisiId);

                        });


                if (!parentDivision) {
                    break;
                }


                root =
                    parentUser;

                parentId =
                    parentDivision.parent_id;

            }


            if (!organization[root.id]) {
                organization[root.id] = {};
            }


            if (!organization[root.id][current.role]) {
                organization[root.id][current.role] = [];
            }


            organization[root.id][current.role].push({

                id: user.id,

                email: user.email,

                name: user.name

            });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | GET HIERARCHY USER
    |--------------------------------------------------------------------------
    */

    function getHierarchyUser(
        row,
        divisiId,
        roleName
    ) {

        const current =
            (row.user_divisions ?? [])
                .find(function (division) {

                    return String(
                        division.division_id
                    ) === String(divisiId);

                });


        if (!current) {
            return [];
        }


        let root = row;

        let parentId =
            current.parent_id;


        const users =
            getCurrentDivisionUsers();


        while (parentId) {

            const parentUser =
                users.find(function (user) {

                    return String(user.id) ===
                        String(parentId);

                });


            if (!parentUser) {
                break;
            }


            const parentDivision =
                (parentUser.user_divisions ?? [])
                    .find(function (division) {

                        return String(
                            division.division_id
                        ) === String(divisiId);

                    });


            if (!parentDivision) {
                break;
            }


            root =
                parentUser;

            parentId =
                parentDivision.parent_id;

        }


        if (!organization[root.id]) {
            return [];
        }


        return organization[root.id][roleName] ?? [];

    }


    /*
    |--------------------------------------------------------------------------
    | ADD USER - ROLE OPTIONS
    |--------------------------------------------------------------------------
    */

    function updateRoleOptions(divisiId) {

        const $roleSelect =
            $('#modal_role');

        const isLmg = isExecutivePlacement(divisiId);

        $('#modal_jabatan_group').toggleClass('hidden', !isLmg);
        $('#modal_role_group').toggleClass('hidden', isLmg);
        $('#modal_jabatan')
            .prop('disabled', !isLmg)
            .prop('required', isLmg);

        if (!isLmg) {
            $('#modal_jabatan').val('');
        }


        $roleSelect
            .val('')
            .prop(
                'disabled',
                !divisiId || isLmg
            )
            .prop('required', !!divisiId && !isLmg);

        if (isLmg) {
            $('#hierarchy_panel').addClass('hidden');
            return;
        }


        $roleSelect.find('option').each(function () {

            const roleDivId =
                $(this).data('divisi');


            if (
                !roleDivId ||
                roleDivId == divisiId
            ) {

                $(this).show();

            } else {

                $(this).hide();

            }

        });


        $roleSelect.trigger('change.select2');

    }


    /*
    |--------------------------------------------------------------------------
    | ADD USER - HIERARCHY
    |--------------------------------------------------------------------------
    */

    function updateParentOptions() {

        const divisiId =
            $('#modal_divisi').val();

        const roleId =
            $('#modal_role').val();

        const selectedLevel =
            parseInt(
                $('#modal_role option:selected')
                    .data('level')
            );

        const parentRoleId =
            $('#modal_role option:selected').data('parent');


        const $parentSelect =
            $('#modal_parent');

        const $subSelect =
            $('#modal_subordinates');

        const $panel =
            $('#hierarchy_panel');

        const $subPanel =
            $('#subordinate_panel');

        const $parentPanel =
            $('#modal_parent_panel');


        if (!roleId) {

            $panel.addClass('hidden');

            return;
        }


        $panel.removeClass('hidden');

        const isFinance = isFinancePlacement(divisiId);
        const requiresParent = isFinance || !!parentRoleId;
        $parentPanel.toggleClass('hidden', !requiresParent);
        $parentSelect.prop('disabled', !requiresParent);


        const allData =
            getCurrentDivisionUsers();


        let maxLevel = 0;


        $('#modal_role option').each(function () {

            if (
                $(this).data('divisi') ==
                divisiId
            ) {

                const level =
                    parseInt(
                        $(this).data('level')
                    );


                if (level > maxLevel) {
                    maxLevel = level;
                }

            }

        });


        let htmlParent =
            '<option value="">-- Tanpa Atasan --</option>';


        const parents = isFinance
            ? cfoUsers()
            : allData.filter(function (user) {

                const ud =
                    (user.user_divisions ?? [])
                        .find(function (division) {

                            return division.division_id ==
                                divisiId;

                        });


                return (
                    ud &&
                    String(ud.role_id) ===
                    String(parentRoleId)
                );

            });


        parents.forEach(function (parent) {

            htmlParent += `
                <option value="${parent.id}">
                    ${escapeHtml(parent.name)} (${escapeHtml(parent.email)})
                </option>
            `;

        });


        $parentSelect.html(
            htmlParent
        );


        let htmlSub = '';


        const subordinates = isFinance
            ? []
            : allData.filter(function (user) {

                const ud =
                    (user.user_divisions ?? [])
                        .find(function (division) {

                            return division.division_id ==
                                divisiId;

                        });


                return (
                    ud &&
                    String(
                        $('#modal_role option[value="' + ud.role_id + '"]').data('parent')
                    ) === String(roleId)
                );

            });


        subordinates.forEach(function (user) {

            htmlSub += `
                <option value="${user.id}">
                    ${escapeHtml(user.name)} (${escapeHtml(user.email)})
                </option>
            `;

        });


        $subSelect
            .html(htmlSub)
            .trigger('change');

        $subPanel.toggleClass('hidden', htmlSub === '');

    }


    /*
    |--------------------------------------------------------------------------
    | EDIT USER
    |--------------------------------------------------------------------------
    */

    function editUser(id) {

        const user =
            allUsers.find(function (item) {

                return String(item.id) ===
                    String(id);

            });


        if (!user) {

            if (typeof Swal !== 'undefined') {

                Swal.fire(
                    'Error',
                    'Data tidak ditemukan',
                    'error'
                );

            } else {

                alert(
                    'Data tidak ditemukan'
                );

            }

            return;

        }


        $('#formEditUser')
            .attr(
                'action',
                `/manajemen_user/${id}`
            );


        $('#editModalTitle')
            .text(
                `Edit User: ${user.name}`
            );


        $('#edit_name')
            .val(user.name);

        $('#edit_email')
            .val(user.email);


        const currentPlacement =
            user.user_divisions?.[0];


        if (currentPlacement) {

            $('#edit_divisi')
                .val(
                    currentPlacement.division_id
                )
                .trigger('change');


            if (isExecutivePlacement(currentPlacement.division_id)) {
                $('#edit_jabatan').val(user.jabatan ?? '');
                $('#hierarchy_panel_edit').addClass('hidden');
            } else {
                setTimeout(function () {

                    $('#edit_role')
                        .val(
                            currentPlacement.role_id
                        )
                        .trigger('change');


                    setTimeout(function () {

                    $('#edit_parent')
                        .val(
                            currentPlacement.parent_id
                        );


                    const currentSubordinateIds =
                        allUsers
                            .filter(function (userItem) {

                                const ud =
                                    (userItem.user_divisions ?? [])
                                        .find(function (division) {

                                            return division.division_id ==
                                                currentPlacement.division_id;

                                        });


                                return (
                                    ud &&
                                    ud.parent_id ==
                                    user.id
                                );

                            })
                            .map(function (userItem) {

                                return userItem.id;

                            });


                    $('#edit_subordinates')
                        .val(currentSubordinateIds)
                        .trigger('change');

                    }, 200);

                }, 100);
            }

        } else {

            $('#edit_divisi')
                .val('')
                .trigger('change');

        }


        openModal(
            'editModal'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | LOCK / UNLOCK USER
    |--------------------------------------------------------------------------
    */

    function toggleLockUser(id, name, isLocked) {
        const actionLabel = isLocked ? 'membuka kunci' : 'mengunci';
        const submit = function () {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/manajemen_user/${id}/lock`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content ?? ''}">
                <input type="hidden" name="_method" value="PATCH">
            `;
            document.body.appendChild(form);
            form.submit();
        };

        // Native confirmation opens immediately and avoids waiting for the
        // large SweetAlert runtime/animation on the user management page.
        const description = isLocked
            ? `${name} akan dapat login kembali.`
            : `${name} tidak akan dapat login sampai kuncinya dibuka.`;
        if (window.confirm(`Yakin ingin ${actionLabel} akun ${name}?\n\n${description}`)) submit();
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    function deleteUser(
        id,
        name
    ) {

        $('#formDeleteUser')
            .attr(
                'action',
                `/manajemen_user/${id}`
            );


        $('#delete_user_name')
            .text(name);


        openModal(
            'deleteModal'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | EDIT ROLE OPTIONS
    |--------------------------------------------------------------------------
    */

    function updateRoleOptionsEdit(
        divisiId
    ) {

        const $roleSelect =
            $('#edit_role');

        const $panel =
            $('#hierarchy_panel_edit');

        const $parentPanel =
            $('#edit_parent_panel');

        const isLmg = isExecutivePlacement(divisiId);

        $('#edit_jabatan_group').toggleClass('hidden', !isLmg);
        $('#edit_role_group').toggleClass('hidden', isLmg);
        $('#edit_jabatan')
            .prop('disabled', !isLmg)
            .prop('required', isLmg);

        if (!isLmg) {
            $('#edit_jabatan').val('');
        }


        $roleSelect
            .val('')
            .prop(
                'disabled',
                !divisiId || isLmg
            )
            .prop('required', !!divisiId && !isLmg);


        $panel.addClass(
            'hidden'
        );

        if (isLmg) {
            return;
        }


        $roleSelect.find('option').each(function () {

            const roleDivId =
                $(this).data('divisi');


            if (
                !roleDivId ||
                roleDivId == divisiId
            ) {

                $(this).show();

            } else {

                $(this).hide();

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | EDIT HIERARCHY
    |--------------------------------------------------------------------------
    */

    function updateHierarchyPanelEdit() {

        const divisiId =
            $('#edit_divisi').val();


        const selectedLevel =
            parseInt(
                $('#edit_role option:selected')
                    .data('level')
            );

        const roleId = $('#edit_role').val();
        const parentRoleId = $('#edit_role option:selected').data('parent');


        const currentUserId =
            $('#formEditUser')
                .attr('action')
                .split('/')
                .pop();


        const $parentSelect =
            $('#edit_parent');

        const $subSelect =
            $('#edit_subordinates');

        const $panel =
            $('#hierarchy_panel_edit');

        const $parentPanel =
            $('#edit_parent_panel');


        if (
            !divisiId ||
            isNaN(selectedLevel)
        ) {

            $panel.addClass(
                'hidden'
            );

            return;

        }


        $panel.removeClass(
            'hidden'
        );

        const isFinance = isFinancePlacement(divisiId);
        const requiresParent = isFinance || !!parentRoleId;
        $parentPanel.toggleClass('hidden', !requiresParent);
        $parentSelect.prop('disabled', !requiresParent);


        const allData =
            getCurrentDivisionUsers();


        let htmlParent =
            '<option value="">-- Tanpa Atasan --</option>';


        const potentialParents = isFinance
            ? cfoUsers(currentUserId)
            : allData.filter(function (user) {

                const ud =
                    (user.user_divisions ?? [])
                        .find(function (division) {

                            return division.division_id ==
                                divisiId;

                        });


                return (
                    ud &&
                    String(ud.role_id) === String(parentRoleId) &&
                    String(user.id) !==
                    String(currentUserId)
                );

            });


        potentialParents.forEach(function (parent) {

            htmlParent += `
                <option value="${parent.id}">
                    ${escapeHtml(parent.name)} (${escapeHtml(parent.email)})
                </option>
            `;

        });


        $parentSelect.html(
            htmlParent
        );


        let htmlSub = '';


        const potentialSubordinates = isFinance
            ? []
            : allData.filter(function (user) {

                const ud =
                    (user.user_divisions ?? [])
                        .find(function (division) {

                            return division.division_id ==
                                divisiId;

                        });


                if (!ud) {
                    return false;
                }


                const userLevel =
                    parseInt(ud.level);


                return (
                    String(
                        $('#edit_role option[value="' + ud.role_id + '"]').data('parent')
                    ) === String(roleId) &&
                    String(user.id) !==
                    String(currentUserId)
                );

            });


        potentialSubordinates.forEach(function (user) {

            htmlSub += `
                <option value="${user.id}">
                    ${escapeHtml(user.name)} (${escapeHtml(user.email)})
                </option>
            `;

        });


        $subSelect.html(
            htmlSub
        );


        if ($.isFunction($.fn.select2)) {

            if (
                $subSelect.hasClass(
                    'select2-hidden-accessible'
                )
            ) {

                $subSelect.select2('destroy');

            }


            $subSelect.select2({

                placeholder:
                    "Pilih satu atau lebih bawahan",

                allowClear:
                    true,

                width:
                    '100%',

                dropdownParent:
                    $('#editModal')

            });

        }

    }


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '[data-password-toggle]', function () {

        const $button = $(this);
        const $input = $button.siblings('input[name="password"]');
        const showPassword = $input.attr('type') === 'password';

        $input.attr('type', showPassword ? 'text' : 'password');
        $button
            .attr('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password')
            .attr('title', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
        $button.find('i')
            .toggleClass('fa-eye', !showPassword)
            .toggleClass('fa-eye-slash', showPassword);

    });

    $(document).on('hidden.bs.modal', '.modal', function () {

        $(this).find('input[name="password"]').attr('type', 'password');
        $(this).find('[data-password-toggle]')
            .attr('aria-label', 'Tampilkan password')
            .attr('title', 'Tampilkan password')
            .find('i')
            .removeClass('fa-eye-slash')
            .addClass('fa-eye');

    });

    function getInitials(name) {

        if (!name) {
            return '?';
        }


        const parts =
            name
                .trim()
                .split(/\s+/)
                .slice(0, 2);


        return parts
            .map(function (part) {
                return part.charAt(0);
            })
            .join('')
            .toUpperCase();

    }


    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    function escapeJs(value) {

        return String(value ?? '')
            .replace(/\\/g, '\\\\')
            .replace(/'/g, "\\'")
            .replace(/"/g, '\\"')
            .replace(/\n/g, '\\n')
            .replace(/\r/g, '\\r');

    }

</script>

@endpush

@endsection
