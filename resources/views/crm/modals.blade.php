{{-- =========================================================
     MODAL TAMBAH / EDIT LEAD
========================================================= --}}
<div
    id="leadFormModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="leadFormTitle"
    aria-hidden="true"
>
    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">

        {{-- Backdrop --}}
        <div
            class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-modal-backdrop="leadFormModal"
        ></div>

        {{-- Modal --}}
        <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <form id="leadForm">

                {{-- Header --}}
                <div class="border-b border-zinc-200 bg-zinc-950 px-5 py-5 text-white sm:px-6">
                    <div class="flex items-start justify-between gap-4">

                        <div>
                            <div class="mb-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                                Lighter Media Group
                            </div>

                            <h2
                                id="leadFormTitle"
                                class="text-lg font-semibold tracking-tight"
                            >
                                Tambah Lead
                            </h2>

                            <p class="mt-1 text-xs text-zinc-400">
                                Masukkan informasi lead dan kebutuhan calon klien.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                            data-modal-close="leadFormModal"
                            aria-label="Tutup"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M6 6l12 12M18 6 6 18" />
                            </svg>
                        </button>

                    </div>
                </div>


                {{-- Body --}}
                <div class="max-h-[75vh] overflow-y-auto px-5 py-6 sm:px-6">

                    {{-- Error --}}
                    <div
                        id="leadFormError"
                        class="mb-6 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                    ></div>


                    <div class="grid grid-cols-1 gap-x-5 gap-y-5 md:grid-cols-2">

                        {{-- Nama --}}
                        <div>
                            <label
                                for="leadNama"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Nama
                            </label>

                            <input
                                type="text"
                                id="leadNama"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Brand --}}
                        <div>
                            <label
                                for="leadBrand"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Brand / Nama Usaha
                            </label>

                            <input
                                type="text"
                                id="leadBrand"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Phone --}}
                        <div>
                            <label
                                for="leadPhone"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Nomor WhatsApp
                            </label>

                            <input
                                type="text"
                                id="leadPhone"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Email --}}
                        <div id="emailField">
                            <label
                                for="leadEmail"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Email
                            </label>

                            <input
                                type="email"
                                id="leadEmail"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Kategori --}}
                        <div>
                            <label
                                for="leadKategori"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Kategori
                            </label>

                            <input
                                type="text"
                                id="leadKategori"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Layanan --}}
                        <div>
                            <label
                                for="leadLayanan"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Layanan
                            </label>

                            <input
                                type="text"
                                id="leadLayanan"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                        </div>


                        {{-- Prospek --}}
                        <div>
                            <label
                                for="leadProspek"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Prospek
                            </label>

                            <select
                                id="leadProspek"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="cold">Cold</option>
                                <option value="warm">Warm</option>
                                <option value="hot">Hot</option>
                            </select>
                        </div>


                        {{-- AI Status --}}
                        <div id="aiStatusField">
                            <label
                                for="leadAiStatus"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Status
                            </label>

                            <select
                                id="leadAiStatus"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="">
                                    Pilih Status
                                </option>

                                <option value="1">
                                    In Progress
                                </option>

                                <option value="2">
                                    Meeting Schedule
                                </option>
                            </select>
                        </div>


                        {{-- Human Status --}}
                        <div
                            id="humanStatusField"
                            class="hidden"
                        >
                            <label
                                for="leadStatus"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Status
                            </label>

                            <select
                                id="leadStatus"
                                class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            >
                                <option value="">
                                    Pilih Status
                                </option>

                                <option value="meeting confirm">
                                    Meeting Confirm
                                </option>

                                <option value="meeting schedule">
                                    Meeting Schedule
                                </option>

                                <option value="on progress">
                                    On Progress
                                </option>

                                <option value="after meeting">
                                    After Meeting
                                </option>

                                <option value="administrasi progress">
                                    Administrasi Progress
                                </option>

                                <option value="ghosting">
                                    Ghosting
                                </option>

                                <option value="no respon">
                                    No Respon
                                </option>

                                <option value="deals">
                                    Deals
                                </option>

                                <option value="no deals">
                                    No Deals
                                </option>
                            </select>
                        </div>


                        {{-- Social Media --}}
                        <div class="md:col-span-2">
                            <div class="mb-3">
                                <p class="text-xs font-semibold text-zinc-700">
                                    Social Media
                                </p>

                                <p class="mt-1 text-xs text-zinc-400">
                                    Masukkan link social media lead jika tersedia.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                                <div>
                                    <label
                                        for="leadInstagram"
                                        class="mb-2 block text-xs text-zinc-500"
                                    >
                                        Instagram
                                    </label>

                                    <input
                                        type="url"
                                        id="leadInstagram"
                                        class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                    >
                                </div>

                                <div>
                                    <label
                                        for="leadFacebook"
                                        class="mb-2 block text-xs text-zinc-500"
                                    >
                                        Facebook
                                    </label>

                                    <input
                                        type="url"
                                        id="leadFacebook"
                                        class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                    >
                                </div>

                                <div>
                                    <label
                                        for="leadShopee"
                                        class="mb-2 block text-xs text-zinc-500"
                                    >
                                        Shopee
                                    </label>

                                    <input
                                        type="url"
                                        id="leadShopee"
                                        class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                    >
                                </div>

                            </div>
                        </div>


                        {{-- Kebutuhan --}}
                        <div class="md:col-span-2">
                            <label
                                for="leadKebutuhan"
                                class="mb-2 block text-xs font-semibold text-zinc-700"
                            >
                                Kebutuhan
                            </label>

                            <textarea
                                id="leadKebutuhan"
                                rows="3"
                                class="block w-full resize-y rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm leading-6 text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                            ></textarea>
                        </div>


                        {{-- AI Fields --}}
                        <div
                            id="aiFields"
                            class="md:col-span-2 space-y-5"
                        >
                            <div class="border-t border-zinc-100 pt-5">
                                <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-400">
                                    AI Lead Detail
                                </p>

                                <div class="space-y-5">

                                    <div>
                                        <label
                                            for="leadKendala"
                                            class="mb-2 block text-xs font-semibold text-zinc-700"
                                        >
                                            Kendala
                                        </label>

                                        <textarea
                                            id="leadKendala"
                                            rows="3"
                                            class="block w-full resize-y rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm leading-6 text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label
                                            for="leadKesimpulan"
                                            class="mb-2 block text-xs font-semibold text-zinc-700"
                                        >
                                            Kesimpulan
                                        </label>

                                        <textarea
                                            id="leadKesimpulan"
                                            rows="3"
                                            class="block w-full resize-y rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm leading-6 text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        ></textarea>
                                    </div>

                                </div>
                            </div>
                        </div>


                        {{-- Human Fields --}}
                        <div
                            id="humanFields"
                            class="hidden md:col-span-2"
                        >
                            <div class="border-t border-zinc-100 pt-5">

                                <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-zinc-400">
                                    Human Lead Detail
                                </p>

                                <div class="space-y-5">

                                    <div>
                                        <label
                                            for="leadPrice"
                                            class="mb-2 block text-xs font-semibold text-zinc-700"
                                        >
                                            Harga
                                        </label>

                                        <input
                                            type="number"
                                            id="leadPrice"
                                            min="0"
                                            step="0.01"
                                            inputmode="decimal"
                                            placeholder="Contoh: 1500000"
                                            class="block w-full rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        >
                                    </div>

                                    <div>
                                        <label
                                            for="leadNote"
                                            class="mb-2 block text-xs font-semibold text-zinc-700"
                                        >
                                            Note
                                        </label>

                                        <textarea
                                            id="leadNote"
                                            rows="3"
                                            class="block w-full resize-y rounded-lg border border-zinc-200 bg-white px-3.5 py-2.5 text-sm leading-6 text-zinc-900 outline-none transition focus:border-zinc-950 focus:ring-1 focus:ring-zinc-950"
                                        ></textarea>
                                    </div>

                                    <label
                                        for="leadCanPush"
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 transition hover:bg-zinc-100"
                                    >
                                        <input
                                            type="checkbox"
                                            id="leadCanPush"
                                            class="h-4 w-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950"
                                        >

                                        <span>
                                            <span class="block text-sm font-semibold text-zinc-800">
                                                Can Push
                                            </span>

                                            <span class="block text-xs text-zinc-400">
                                                Lead dapat diteruskan ke proses berikutnya.
                                            </span>
                                        </span>
                                    </label>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                {{-- Footer --}}
                <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">

                    <button
                        type="button"
                        data-modal-close="leadFormModal"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-600 transition hover:border-zinc-300 hover:bg-zinc-100 hover:text-zinc-950"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-950 focus:ring-offset-2"
                    >
                        Simpan
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>


{{-- =========================================================
     MODAL DETAIL TEXT
========================================================= --}}
<div
    id="crmTextDetailModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="crmTextDetailModalLabel"
    aria-hidden="true"
>
    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">

        <div
            class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-modal-backdrop="crmTextDetailModal"
        ></div>

        <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between gap-4 border-b border-zinc-200 bg-zinc-950 px-5 py-4 text-white sm:px-6">

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                        CRM Detail
                    </p>

                    <h2
                        class="mt-1 text-lg font-semibold"
                        id="crmTextDetailModalLabel"
                    >
                        Detail Data
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="crmTextDetailModal"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

            </div>

            <div class="p-5 sm:p-6">
                <div
                    id="crmTextDetailContent"
                    class="max-h-[500px] overflow-y-auto whitespace-pre-wrap break-words rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm leading-7 text-zinc-700"
                ></div>
            </div>

            <div class="flex justify-end border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                <button
                    type="button"
                    data-modal-close="crmTextDetailModal"
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                >
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>


{{-- =========================================================
     MODAL CHAT HISTORY
========================================================= --}}
<div
    id="crmChatHistoryModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="crmChatHistoryModalLabel"
    aria-hidden="true"
>
    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">

        <div
            class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-modal-backdrop="crmChatHistoryModal"
        ></div>

        <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between gap-4 border-b border-zinc-200 bg-zinc-950 px-5 py-4 text-white sm:px-6">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10">
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M21 11.5a8.4 8.4 0 0 1-9 8.5 9.5 9.5 0 0 1-4.1-.9L3 20l1.2-4.1A8.2 8.2 0 0 1 3 11.5 8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5Z" />
                        </svg>
                    </div>

                    <div>
                        <h2
                            class="text-lg font-semibold"
                            id="crmChatHistoryModalLabel"
                        >
                            History Chat
                        </h2>

                        <small
                            id="crmChatHistoryPhone"
                            class="text-xs text-zinc-400"
                        ></small>
                    </div>

                </div>

                <button
                    type="button"
                    data-modal-close="crmChatHistoryModal"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

            </div>


            <div
                id="crmChatHistoryBody"
                class="max-h-[600px] min-h-[250px] overflow-y-auto bg-zinc-50 p-5 sm:p-6"
            >
                <div class="flex min-h-[200px] items-center justify-center text-center">
                    <div>
                        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100">
                            <svg
                                class="h-5 w-5 text-zinc-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path d="M8 10h8M8 14h5" />
                                <path d="M19 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Z" />
                            </svg>
                        </div>

                        <p class="text-sm font-medium text-zinc-600">
                            Belum ada chat yang dipilih.
                        </p>
                    </div>
                </div>
            </div>


            <div class="flex flex-col-reverse gap-2 border-t border-zinc-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">

                <button
                    type="button"
                    data-modal-close="crmChatHistoryModal"
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-950"
                >
                    Tutup
                </button>

                <a
                    id="crmOpenWhatsApp"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hidden items-center justify-center gap-2 rounded-lg bg-zinc-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M21 11.5a8.4 8.4 0 0 1-9 8.5 9.5 9.5 0 0 1-4.1-.9L3 20l1.2-4.1A8.2 8.2 0 0 1 3 11.5 8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5Z" />
                    </svg>

                    Buka WhatsApp
                </a>

            </div>

        </div>
    </div>
</div>


{{-- =========================================================
     MODAL FOLLOW UP DETAIL
========================================================= --}}
<div
    id="humanFollowUpDetailModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto"
    aria-labelledby="humanFollowUpDetailModalLabel"
    aria-hidden="true"
>
    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">

        <div
            class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm"
            data-modal-backdrop="humanFollowUpDetailModal"
        ></div>

        <div class="relative z-10 w-full max-w-xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">

            <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-5 py-4 sm:px-6">

                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-400">
                        Human CRM
                    </p>

                    <h2
                        class="mt-1 text-lg font-semibold tracking-tight text-zinc-950"
                        id="humanFollowUpDetailModalLabel"
                    >
                        Detail Follow Up
                    </h2>
                </div>

                <button
                    type="button"
                    data-modal-close="humanFollowUpDetailModal"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-950"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

            </div>


            <div
                class="max-h-[70vh] overflow-y-auto px-5 py-6 sm:px-6"
                id="humanFollowUpDetailBody"
            >
                <div class="flex min-h-[120px] items-center justify-center text-center">

                    <div>
                        <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100">
                            <svg
                                class="h-5 w-5 animate-spin text-zinc-400"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    class="opacity-25"
                                    stroke="currentColor"
                                    stroke-width="2"
                                />

                                <path
                                    d="M21 12a9 9 0 0 0-9-9"
                                    stroke="currentColor"
                                    stroke-width="2"
                                />
                            </svg>
                        </div>

                        <p class="text-sm text-zinc-500">
                            Memuat detail follow up...
                        </p>
                    </div>

                </div>
            </div>


            <div class="flex justify-end border-t border-zinc-200 bg-zinc-50 px-5 py-4 sm:px-6">
                <button
                    type="button"
                    data-modal-close="humanFollowUpDetailModal"
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-5 py-2.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950"
                >
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
