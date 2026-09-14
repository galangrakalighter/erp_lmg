@extends('layout.app')

@section('content')
<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- ================= HEADER ================= --}}
        <div class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-px w-8 bg-zinc-900"></span>
                    <span class="text-[11px] font-semibold uppercase tracking-[0.22em] text-zinc-500">
                        Company Products
                    </span>
                </div>

                <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">
                    Produk Perusahaan
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                    Kelola daftar perusahaan dan status publikasinya dalam satu tempat.
                </p>
            </div>

            <button
                type="button"
                onclick="addPerusahaan()"
                class="inline-flex w-fit items-center gap-2 rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>
                Tambah Produk
            </button>
        </div>

        {{-- ================= TABLE CARD ================= --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.04)]">

            {{-- Card Header --}}
            <div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-950">
                        Daftar Perusahaan
                    </h2>
                    <p class="mt-1 text-xs text-zinc-500">
                        Daftar produk/perusahaan yang tersedia di sistem.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-xs font-medium text-zinc-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-900"></span>
                        Data Perusahaan
                    </span>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="p-5 sm:p-6">
                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[720px] text-left text-sm"
                        id="perusahaanTable"
                    >
                        <thead>
                            <tr class="border-b border-zinc-200">
                                <th class="w-[10%] px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    No
                                </th>

                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Perusahaan
                                </th>

                                <th class="w-[20%] px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Status
                                </th>

                                <th class="w-[20%] px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.14em] text-zinc-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-100">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- =========================================================
     MODAL TAMBAH / EDIT
========================================================= --}}
<div
    id="produkModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="produkModal"
    >
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modalTitle"
        >
            <form id="produkForm">
                @csrf

                <input type="hidden" id="produk_id" name="id">

                {{-- Modal Header --}}
                <div class="border-b border-zinc-200 bg-zinc-950 px-5 py-5 sm:px-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                Company Product
                            </div>

                            <h2
                                id="modalTitle"
                                class="text-lg font-semibold tracking-tight text-white"
                            >
                                Tambah Produk Perusahaan
                            </h2>
                        </div>

                        <button
                            type="button"
                            data-modal-close="produkModal"
                            class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-white/10 hover:text-white focus:outline-none"
                            aria-label="Close"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="space-y-5 px-5 py-6 sm:px-6">

                    <div>
                        <label
                            for="nama_perusahaan"
                            class="mb-2 block text-sm font-medium text-zinc-900"
                        >
                            Nama Perusahaan
                        </label>

                        <input
                            type="text"
                            id="nama_perusahaan"
                            name="nama_perusahaan"
                            required
                            placeholder="Masukkan nama perusahaan"
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                    </div>

                    <div>
                        <label
                            for="status"
                            class="mb-2 block text-sm font-medium text-zinc-900"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                            class="block w-full rounded-xl border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-950 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                        >
                            <option value="1">Ditampilkan</option>
                            <option value="0">Disembunyikan</option>
                        </select>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                    <button
                        type="button"
                        data-modal-close="produkModal"
                        class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-300"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        id="saveBtn"
                        class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- =========================================================
     MODAL HAPUS
========================================================= --}}
<div
    id="deleteModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-hidden="true"
>
    <div
        class="flex min-h-full items-center justify-center p-4 sm:p-6"
        data-modal-backdrop="deleteModal"
    >
        <div
            class="relative w-full max-w-md overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteModalLabel"
        >

            {{-- Header --}}
            <div class="border-b border-zinc-200 px-5 py-5 sm:px-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.04 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.75-3.14l-8.04-14a2 2 0 0 0-3.42 0Z"/>
                            </svg>
                        </div>

                        <div>
                            <h2
                                id="deleteModalLabel"
                                class="text-base font-semibold text-zinc-950"
                            >
                                Konfirmasi Hapus
                            </h2>

                            <p class="mt-0.5 text-xs text-zinc-500">
                                Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        data-modal-close="deleteModal"
                        class="rounded-lg p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-900 focus:outline-none"
                        aria-label="Close"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-5 py-6 sm:px-6">
                <p class="text-sm leading-6 text-zinc-600">
                    Apakah Anda yakin ingin menghapus data perusahaan
                    <span
                        id="delete_nama"
                        class="font-semibold text-zinc-950"
                    ></span>?
                </p>

                <input type="hidden" id="delete_id">
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                <button
                    type="button"
                    data-modal-close="deleteModal"
                    class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-zinc-300"
                >
                    Batal
                </button>

                <button
                    type="button"
                    id="confirmDeleteBtn"
                    onclick="deleteDataConfirm()"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 15h8l1-15M10 11v6m4-6v6"/>
                    </svg>
                    Hapus
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =========================================================
     SCRIPT
========================================================= --}}
<script>
    let isEditing = false;

    document.addEventListener('DOMContentLoaded', function () {
        loadData();

        const produkForm = document.getElementById('produkForm');

        if (produkForm) {
            produkForm.addEventListener('submit', function (e) {
                e.preventDefault();
                savePerusahaan();
            });
        }

        // Close modal
        document.addEventListener('click', function (event) {
            const closeButton = event.target.closest('[data-modal-close]');

            if (closeButton) {
                event.preventDefault();

                const modalId = closeButton.getAttribute('data-modal-close');
                closeModal(modalId);

                return;
            }

            const backdrop = event.target.closest('[data-modal-backdrop]');

            if (backdrop && event.target === backdrop) {
                closeModal(backdrop.getAttribute('data-modal-backdrop'));
            }
        });

        // ESC close
        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            const visibleModal = document.querySelector(
                '[id$="Modal"]:not(.hidden)'
            );

            if (visibleModal) {
                closeModal(visibleModal.id);
            }
        });
    });


    // =========================================================
    // LOAD DATA
    // =========================================================
    async function loadData() {
        const url = `{{ route('perusahaan.getDataPerusahaan') }}`;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil data perusahaan.');
            }

            const result = await response.json();

            $('#perusahaanTable').DataTable({
                destroy: true,
                data: result.data,

                // Tetap menggunakan DataTables,
                // tetapi visual table mengikuti Tailwind.
                dom:
                    '<"mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"lf>' +
                    't' +
                    '<"mt-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"ip>',

                language: {
                    search: '',
                    searchPlaceholder: 'Cari perusahaan...',
                    lengthMenu: 'Tampilkan _MENU_',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data perusahaan tidak ditemukan.',
                    paginate: {
                        previous: '‹',
                        next: '›'
                    }
                },

                columns: [
                    {
                        data: null,
                        className: 'text-center align-middle',
                        render: (data, type, row, meta) => {
                            return `
                                <span class="text-xs font-medium text-zinc-500">
                                    ${meta.row + 1}
                                </span>
                            `;
                        }
                    },

                    {
                        data: 'nama_perusahaan',
                        className: 'align-middle',
                        render: function (data) {
                            return `
                                <div class="font-semibold text-zinc-950">
                                    ${escapeHtml(data)}
                                </div>
                            `;
                        }
                    },

                    {
                        data: 'status',
                        className: 'text-center align-middle',
                        render: function (data) {
                            if (data == 1) {
                                return `
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Ditampilkan
                                    </span>
                                `;
                            }

                            return `
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600 ring-1 ring-inset ring-zinc-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                    Disembunyikan
                                </span>
                            `;
                        }
                    },

                    {
                        data: null,
                        className: 'text-center align-middle',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            const rowJson = JSON.stringify(row)
                                .replace(/'/g, '&#39;');

                            const nama = escapeHtml(row.nama_perusahaan);

                            return `
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 transition hover:border-zinc-950 hover:bg-zinc-950 hover:text-white focus:outline-none focus:ring-2 focus:ring-zinc-300"
                                        onclick='editPerusahaan(${rowJson})'
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.86 3.49 3.65 3.65M4 20l3.1-.62a2 2 0 0 0 1.04-.57L19.5 7.45a2.59 2.59 0 0 0-3.66-3.66L4.48 15.5a2 2 0 0 0-.57 1.04L3.3 19.64A.3.3 0 0 0 4 20Z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-200"
                                        onclick='deletePerusahaan(${row.id}, "${nama}")'
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6V4h8v2m-9 0 1 15h8l1-15M10 11v6m4-6v6"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            styleDataTable();

        } catch (err) {
            console.error(err);
            alert('Gagal memuat data perusahaan.');
        }
    }


    // =========================================================
    // ADD
    // =========================================================
    function addPerusahaan() {
        isEditing = false;

        const form = document.getElementById('produkForm');

        if (form) {
            form.reset();
        }

        document.getElementById('produk_id').value = '';
        document.getElementById('modalTitle').textContent =
            'Tambah Produk Perusahaan';

        document.getElementById('saveBtn').textContent = 'Simpan';

        openModal('produkModal');
    }


    // =========================================================
    // EDIT
    // =========================================================
    function editPerusahaan(row) {
        isEditing = true;

        const form = document.getElementById('produkForm');

        if (form) {
            form.reset();
        }

        document.getElementById('produk_id').value = row.id;
        document.getElementById('nama_perusahaan').value =
            row.nama_perusahaan;

        const statusValue =
            row.status === true || row.status == 1
                ? '1'
                : '0';

        document.getElementById('status').value = statusValue;

        document.getElementById('modalTitle').textContent =
            'Edit Produk Perusahaan';

        document.getElementById('saveBtn').textContent = 'Update';

        openModal('produkModal');
    }


    // =========================================================
    // SAVE
    // =========================================================
    async function savePerusahaan() {
        const id = document.getElementById('produk_id').value;

        const url = isEditing
            ? `/perusahaan/${id}`
            : `{{ route('perusahaan.store') }}`;

        const formData = {
            _token: '{{ csrf_token() }}',
            nama_perusahaan:
                document.getElementById('nama_perusahaan').value,
            status:
                document.getElementById('status').value
        };

        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.textContent;

        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan...';

        try {
            const response = await fetch(url, {
                method: isEditing ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (response.ok) {
                closeModal('produkModal');
                loadData();
            } else {
                alert(
                    result.message ||
                    'Terjadi kesalahan saat menyimpan data.'
                );
            }

        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi.');

        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        }
    }


    // =========================================================
    // DELETE MODAL
    // =========================================================
    function deletePerusahaan(id, nama) {
        document.getElementById('delete_id').value = id;
        document.getElementById('delete_nama').textContent = nama;

        openModal('deleteModal');
    }


    // =========================================================
    // DELETE
    // =========================================================
    async function deleteDataConfirm() {
        const id = document.getElementById('delete_id').value;
        const url = `/perusahaan/${id}`;

        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const originalText = deleteBtn.innerHTML;

        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Menghapus...';

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (response.ok) {
                closeModal('deleteModal');
                loadData();
            } else {
                alert('Gagal menghapus data.');
            }

        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi.');

        } finally {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        }
    }


    // =========================================================
    // MODAL HELPERS
    // =========================================================
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

        const visibleModal = document.querySelector(
            '[id$="Modal"]:not(.hidden)'
        );

        if (!visibleModal) {
            document.body.classList.remove('overflow-hidden');
        }
    }


    // =========================================================
    // ESCAPE HTML
    // =========================================================
    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }


    // =========================================================
    // DATATABLE VISUAL TWEAK
    // =========================================================
    function styleDataTable() {
        const tableWrapper = document.getElementById('perusahaanTable');

        if (!tableWrapper) return;

        // DataTables akan generate wrapper.
        const wrapper = tableWrapper.closest('.dataTables_wrapper');

        if (!wrapper) return;

        wrapper.classList.add('text-sm');

        const filterInput = wrapper.querySelector(
            '.dataTables_filter input'
        );

        if (filterInput) {
            filterInput.classList.add(
                'rounded-xl',
                'border',
                'border-zinc-300',
                'bg-white',
                'px-3',
                'py-2',
                'text-sm',
                'text-zinc-900',
                'outline-none',
                'focus:border-zinc-950',
                'focus:ring-1',
                'focus:ring-zinc-950'
            );
        }

        const lengthSelect = wrapper.querySelector(
            '.dataTables_length select'
        );

        if (lengthSelect) {
            lengthSelect.classList.add(
                'rounded-lg',
                'border',
                'border-zinc-300',
            'bg-white',
                'px-2',
                'py-1.5',
                'text-sm',
                'outline-none',
                'focus:border-zinc-950'
            );
        }

        wrapper.querySelectorAll('tbody tr').forEach(row => {
            row.classList.add(
                'transition-colors',
                'hover:bg-zinc-50'
            );
        });
    }
</script>
@endsection