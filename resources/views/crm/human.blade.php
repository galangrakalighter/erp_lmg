<div id="humanTabContent" class="crm-tab-content hidden bg-white">
    <div
        id="humanRefreshNotice"
        class="mx-4 mt-4 hidden rounded-xl border px-4 py-3 sm:mx-6"
        role="status"
        aria-live="polite"
    >
        <div class="flex items-start justify-between gap-3">
            <div>
                <p id="humanRefreshNoticeTitle" class="text-sm font-semibold"></p>
                <p id="humanRefreshNoticeMessage" class="mt-1 text-xs leading-5"></p>
            </div>
            <button
                type="button"
                id="humanRefreshNoticeClose"
                class="shrink-0 rounded-md p-1 opacity-60 transition hover:bg-black/5 hover:opacity-100"
                aria-label="Tutup pemberitahuan"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    <div class="crm-table-shell overflow-auto">
        <table class="w-full min-w-[2290px] text-left text-sm">
            <thead><tr class="crm-column-labels crm-single-header">
                <th class="w-14 px-4 py-3 text-center">No</th><th class="min-w-[190px] px-4 py-3">Aktivitas</th>
                <th class="min-w-[190px] px-4 py-3">Identitas</th><th class="min-w-[190px] px-4 py-3">Sosial Media</th>
                <th class="min-w-[130px] px-4 py-3">Kategori</th><th class="min-w-[190px] px-4 py-3">Kontak</th>
                <th class="min-w-[150px] px-4 py-3">Akuisisi</th><th class="min-w-[140px] px-4 py-3">Layanan</th>
                <th class="min-w-[140px] px-4 py-3">Harga</th>
                <th class="min-w-[190px] px-4 py-3">Kebutuhan</th><th class="min-w-[110px] px-4 py-3">Prospek</th>
                <th class="min-w-[150px] px-4 py-3">Status</th><th class="min-w-[320px] px-4 py-3">Progress</th>
                <th class="min-w-[180px] px-4 py-3">Catatan</th><th class="w-[132px] px-4 py-3 text-center">Action</th>
            </tr></thead>
            <tbody id="humanTableBody"><tr><td colspan="15" class="px-6 py-12 text-center text-zinc-400">Data Human sedang dimuat...</td></tr></tbody>
        </table>
    </div>
</div>
