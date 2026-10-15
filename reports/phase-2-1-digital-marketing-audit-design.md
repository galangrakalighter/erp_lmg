# Fase 2 — Digital Marketing

## Tahap 2.1: Audit dan Perancangan

Tanggal audit: 15 Oktober 2026  
Metode: inventaris route/model/tabel, penelusuran source-to-sink, pemetaan policy akses, dan klasifikasi kesiapan data. Tidak ada kode aplikasi yang diubah pada tahap ini.

## Ringkasan eksekutif

Fondasi MCP Fase 1 sudah memadai untuk delapan tool analitik read-only yang ada. Domain Digital Marketing yang lebih luas belum aman untuk langsung dibuka melalui MCP karena route lama masih memiliki policy akses yang berbeda dari fondasi tersebut.

Tiga jalur terkonfirmasi perlu diperbaiki sebelum perluasan MCP: akses dokumen lintas-client, pembacaan fitur lama lintas-client, serta perubahan content plan/CMS lintas-client. Social Media Research, Advertising Research, dan Explore masih berupa placeholder. Campaign belum mempunyai entitas internal yang kanonik; data campaign hanya muncul dari payload provider/snapshot.

## Temuan keamanan

### F-01 — High — Dokumen client dapat dibaca dan diubah lintas-client

- **Location:** `routes/web.php:406-410`; `app/Http/Controllers/PDFController.php:246-282`, `307-333`, `360-410`, `509-572`.
- **Issue:** pemeriksaan dokumen hanya membatasi pengguna Multimedia. Pengguna terautentikasi dari division lain melewati `ensureMultimediaDocumentScope()` tanpa pemeriksaan assignment client atau division.
- **Exploit Path:** akun biasa → ubah ID file/client pada route PDF → `ensureFileAccessible()`/`ensureClientDivision()` → policy mengembalikan tanpa penolakan untuk non-Multimedia → baca, unggah, ubah, atau hapus dokumen client lain.
- **Impact:** kebocoran dan perubahan dokumen client; pinned document hanya melindungi sebagian operasi edit/hapus, bukan pembacaan.
- **Remediation:** gunakan satu policy client/division kanonik pada setiap operasi daftar, preview, versi, upload, create, update, dan delete. Pisahkan permission read, write, serta pinned-document administration.
- **Test:** user tanpa assignment harus menerima 403/404 untuk seluruh ID file dan client target; admin dan user terotorisasi tetap lulus.
- **PoC Requirements:** akun sesi non-admin/non-Multimedia, ID file client yang tidak dimiliki, lingkungan test. PoC default dry-run dan hanya menyediakan opsi GET read-only: `reports/poc_digital_marketing_document_idor.py`.

### F-02 — High — Route lama melewati pembatasan client Digital Marketing

- **Location:** `routes/web.php:117-126`, `165`; `routes/api.php:146`; `marketResearchController.php:20`; `brandingController.php:13-94`; `AnalisisController.php:36-63`; `ManajemenKontenController.php:18-145`; `PDFController.php:39-113`.
- **Issue:** route lama hanya memakai autentikasi umum. Controller mempercayai ID client dari URL dan membaca tabel/client credential tanpa memanggil policy client Fase 1 atau middleware `digital-marketing.client`.
- **Exploit Path:** akun terautentikasi → ganti `id_produk`/`produk_id` → controller melakukan query berdasarkan ID tersebut → data market research, branding, content plan, performance, atau export PDF client lain dikembalikan.
- **Impact:** kebocoran strategi, brand, kalender konten, PIC/email, dan data performance. Route analisis juga dapat memanggil provider memakai credential client dan menyimpan snapshot saat GET.
- **Remediation:** arahkan semua jalur lama ke policy yang sama dengan `MarketingAccessService`; lindungi export dan endpoint DataTable; ubah proses capture snapshot menjadi command/job eksplisit, bukan efek samping GET.
- **Test:** matriks admin/non-admin, lintas division, lintas client, export, DataTable, dan setiap platform harus fail closed.
- **PoC Requirements:** akun biasa, ID client tak terotorisasi, platform valid. PoC tidak memanggil route analisis yang berefek samping: `reports/poc_digital_marketing_legacy_read_idor.py`.

### F-03 — High — Specialist dapat mengubah content plan client yang tidak ditugaskan

- **Location:** `ManajemenKontenController.php:150-154`, `311-318`, `491-497`, `536-540`; `DigitalMarketingPermissions.php:29-34`; route mutasi pada `routes/web.php:166-168`.
- **Issue:** `authorizeManagerClient()` memeriksa assignment hanya ketika role tepat `Digital Marketing Manager`. Content Creator, Sosial Media Spesialis, dan Advertising langsung lolos dari pemeriksaan client lalu diizinkan berdasarkan jenis sumber konten.
- **Exploit Path:** specialist dengan permission organic/paid → kirim client ID milik pengguna lain → pemeriksaan role-source lulus → insert/update/delete pada `data_jasa`, task terkait, dan salinan mirror.
- **Impact:** perubahan kalender/content workflow lintas-client dan potensi perubahan task turunan.
- **Remediation:** cek client assignment untuk semua role non-admin; berikan akses division-wide hanya kepada role yang secara eksplisit disetujui; lakukan object-level authorization ulang pada update/delete.
- **Test:** setiap role diuji terhadap assigned dan unassigned client untuk create/update/delete serta organic/paid.
- **PoC Requirements:** akun specialist dan client yang tidak ditugaskan. Scaffold hanya mencetak request uji dan sengaja tidak mengeksekusi POST: `reports/poc_digital_marketing_cross_client_write.py`.

## Arsitektur dan relasi data

### Identitas dan scope

| Lapisan | Tabel/model | Relasi utama |
|---|---|---|
| Akun | `users` / `User`, `OAuthUser` | akun ke `user_division` dan `user_client` |
| Organisasi | `divisions`, `roles`, `user_division` | role terikat division; hierarki memakai `parent_id` |
| Client | `produk_client` / `ProdukClient` | kategori, division, tim, KPI, OAuth connection |
| Scope client | `client_division`, `user_client` / `UserClient` | client aktif pada division dan assignment user-client |
| Credential platform | `oauth_connections` / `OAuthConnection` | provider Meta/Threads/TikTok per client; field terenkripsi |
| Credential lama | `produk_client.access_token` | JSON platform/access; format legacy, tidak layak diekspos |

### Market research

Tabel: `analize_4p`, `analize_swot`, `segmentasi`, `target_audience`, `positioning`. Semua terhubung ke `produk_client_id` dan platform. Pembacaan terkonsolidasi pada `MarketResearchServiceImplement`; CRUD lama tersebar pada controller CMS masing-masing.

### Branding

Tabel: `brand_identify`, `brand_image`, `brand_value`, `brand_tagline`, `gaya_komunikasi`, `komposisi`, `audio_brand`, `moodboard`, dan `alat_branding`. Seluruhnya berscope client/platform. Data mayoritas berupa teks bebas atau referensi aset; belum memiliki status publikasi, versi, freshness, dan approval yang seragam.

### Content plan dan workflow

`data_jasa` adalah pusat content plan dan mengacu ke `type_produk`, `strategy`, `status`, `pilar`, `hooks`, `jenis_body`, `jenis_cta`, PIC user, client, platform, tanggal/jam posting, link, serta `content_source` organic/paid. `content_compositions` menambah panduan komposisi. Workflow saat ini direpresentasikan oleh status content dan task, tetapi belum ada event history/approval trail yang kanonik.

### Campaign dan performance

Tidak ditemukan tabel campaign internal. `campaign_id`, nama campaign, spend, conversion, dan revenue berasal dari item snapshot/provider. Performance tersimpan di `analysis_snapshots` berdasarkan client, platform, source, dan tanggal; `metrics` serta `items` berupa JSON. Ketersediaannya tergantung capture dari halaman analisis/provider dan dapat parsial.

### Task dan timeline

`task` mengacu ke division, PIC, project/client, content plan (`id_manajement_konten`) dan KPI. `kpi` mengacu ke client melalui `project`, sedangkan `kpi_user` menghubungkan PIC. Completion proof, note, email PIC, serta data hierarki user perlu dianggap data internal/sensitif.

### Aset, dokumen, dan mirroring

Dokumen memakai `file_pdf` dan `file_pdf_versions`, disimpan pada disk private dan dibagi menurut client/division. Mirroring lintas platform memakai `client_platform_mirrors` dan `platform_mirror_copies`. Aset OAuth tersimpan terenkripsi pada `oauth_connections`; isi binary dokumen, token, raw provider assets, dan version history tidak boleh langsung diekspos melalui MCP.

## Pemetaan akses yang direkomendasikan

| Aktor | Client yang dapat dibaca | Domain utama | Mutasi melalui MCP |
|---|---|---|---|
| Super Admin | Semua client | Semua data yang telah disanitasi | Tidak pada Fase 2 awal |
| Busines Unit Head | Client aktif dalam Digital Marketing | Semua domain DM | Tidak |
| Digital Marketing Manager | Client aktif dalam division atau assignment sesuai keputusan bisnis final | Semua domain DM | Tidak |
| Digital Marketing | Client yang ditugaskan | Market research dan branding; domain lain read-only | Tidak |
| Sosial Media Spesialis | Client yang ditugaskan | Social research dan organic content | Tidak |
| Advertising | Client yang ditugaskan | Advertising research, paid content, paid performance | Tidak |
| Content Creator | Client yang ditugaskan | Organic content plan | Tidak |
| Performance & Reporting | Perlu keputusan eksplisit; saat ini workspace memberi akses luas | Performance dan reporting | Tidak |
| Ecommerce/Multimedia/Business Dev | Hanya bila client+division aktif dan ada assignment/policy berbagi | Data bersama yang disetujui | Tidak |

Semua keputusan harus memakai subject + action + client + division + role + status assignment. Jangan mengandalkan tab read-only pada UI sebagai kontrol otorisasi.

## Daftar MCP tools yang diusulkan

### Pertahankan dari Fase 1

1. `list_clients`
2. `get_data_availability`
3. `get_performance_summary`
4. `get_performance_trend`
5. `get_top_content`
6. `get_paid_performance`
7. `get_content_plan`
8. `get_content_mix`

### Kandidat Fase 2 setelah policy diperbaiki

1. `get_market_research` — gabungan 4P, SWOT, segmentasi, target audience, positioning.
2. `get_brand_profile` — identity, values, tagline, voice, composition; tanpa raw credential/path.
3. `get_content_workflow` — content plan dikelompokkan status/PIC/periode, dengan PII minimal.
4. `get_project_tasks` — task per client/periode/PIC, tanpa completion proof binary.
5. `get_kpi_timeline` — milestone KPI dan status, email PIC dikeluarkan secara default.
6. `list_client_assets` — metadata aset/dokumen yang telah diotorisasi; tidak mengembalikan binary atau storage path.
7. `get_platform_connections_status` — hanya status koneksi, expiry, dan kelengkapan; tidak pernah token/scopes mentah.
8. `get_campaign_performance` — baru setelah model campaign/attribution dibuat kanonik.

Semua tool awal tetap read-only, memakai `marketing:read`, pagination/filter allowlist, period maksimum, response envelope Fase 1, audit aman, serta policy client yang sama.

## Data belum tersedia atau belum layak diekspos

| Data/fitur | Status | Alasan |
|---|---|---|
| Social Media Research | Belum tersedia | Route workspace masih placeholder |
| Advertising Research | Belum tersedia | Route workspace masih placeholder |
| Explore | Belum tersedia | Route workspace masih placeholder |
| Campaign master | Belum tersedia | Tidak ada tabel/model campaign kanonik |
| Attribution campaign | Belum matang | Hanya berasal dari JSON provider/snapshot; coverage dan currency dapat parsial |
| Workflow approval history | Belum tersedia | Status ada, tetapi event/approval trail kanonik tidak ada |
| Performance real-time | Belum stabil | Bergantung provider, token, scope, capture UI, dan freshness snapshot |
| Branding/market research quality | Perlu kurasi | Teks bebas, tanpa lifecycle/version/freshness seragam |
| Dokumen dan version binary | Belum layak | Sensitif dan terdapat temuan authorization F-01 |
| Completion proof/task note | Perlu klasifikasi | Dapat berisi data personal/internal |
| Email/nama PIC lengkap | Batasi | Gunakan identifier/nama seperlunya; email bukan default output |
| OAuth token, legacy access token, raw assets | Dilarang | Credential dan provider metadata sensitif |
| Storage path/internal IDs mirror | Dilarang | Detail implementasi, tidak diperlukan konsumen MCP |

## Desain fondasi Fase 2

1. Gunakan satu `MarketingAccessService`/policy sebagai sumber keputusan untuk route web, API lama, export, dokumen, dan MCP.
2. Tambahkan permission berbasis aksi: `market.read`, `branding.read`, `content.read`, `performance.read`, `task.read`, `asset.metadata.read`.
3. Pisahkan adapter read-only dari controller UI lama; jangan menggunakan view controller sebagai sumber MCP.
4. Buat DTO/serializer allowlist per domain dan redaksi PII/credential di lapisan terakhir.
5. Jadikan capture provider sebagai job terjadwal/idempotent; MCP hanya membaca snapshot.
6. Tambahkan metadata `freshness`, `completeness`, `source`, `period`, dan `warnings` pada seluruh tool.
7. Terapkan contract test lintas-role/client serta non-mutation test untuk setiap tool baru.

## Open Questions / Assumptions

1. Apakah Digital Marketing Manager memang division-wide atau harus tetap memiliki `user_client`? Implementasi web dan MCP saat ini berbeda.
2. Apakah Performance & Reporting dan Ecommerce boleh membaca seluruh client Digital Marketing? Workspace web saat ini memberi akses luas tanpa assignment client.
3. Siapa pemilik market research/branding bersama ketika satu client berada di beberapa division?
4. Apakah task/KPI boleh dilihat semua anggota client atau hanya PIC dan atasannya?
5. Apakah dokumen tertentu boleh dibaca lintas-division, dan bagaimana klasifikasi pinned documents?
6. Sumber kanonik campaign, conversion, revenue, dan currency belum ditentukan.

## Coverage

Ditinjau: route web/API Digital Marketing dan legacy, controller workspace/market/branding/content/performance/document, middleware dan permission helper, model dan migration utama, OAuth connection, snapshot, task/KPI, dokumen, serta mirroring. Tidak dilakukan request terhadap provider eksternal, eksploitasi live, pembacaan database produksi, atau perubahan kode aplikasi. PoC hanya divalidasi secara lokal dalam mode dry-run.
