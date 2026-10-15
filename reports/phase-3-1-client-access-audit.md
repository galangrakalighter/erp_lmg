# Fase 3.1 — Audit Fondasi Account dan Client Management

Status: audit dan rancangan saja. Tidak ada route, tool MCP, database, login, OAuth, atau kode bisnis yang diubah pada tahap ini.

## Kesimpulan

`produk_client` adalah sumber utama identitas client. Cakupan akses dibentuk oleh status akun pengguna, administrator, relasi `client_division`, relasi `user_client`, dan role pada division. Relasi project–task sudah tersedia melalui `produk_client_id`/`project`, tetapi profil kontak client belum memiliki model kanonik tersendiri. Data kontak tersebar pada PIC pengguna dan data CRM, sehingga tidak boleh digabung sebagai profil client tanpa aturan relasi dan izin yang eksplisit.

## Ruang lingkup dan sumber yang diperiksa

- `app/Models/ProdukClient.php`, `User.php`, `UserClient.php`, `Task.php`, `FilePdf.php`, `CRM.php`.
- `app/Services/MarketingAccessService.php` dan `app/Support/DigitalMarketingPermissions.php`.
- `ProdukController`, `BarangClientController`, serta route web/API terkait client, division, workspace, dan MCP.
- Migration `produk_client`, `client_division`, `user_client`, `data_jasa`, `kpi`, `task`, `file_pdf`, CRM, invoice, OAuth connection, dan field profil pengguna.
- `plugins/erp-lmg-marketing/server/index.mjs` dan layanan data Digital Marketing yang sudah ada.

## Peta data

| Domain | Sumber | Relasi utama | Klasifikasi awal |
|---|---|---|---|
| Identitas client | `produk_client` | `kategori_jasa_id`, `client_division`, `user_client` | aman terbatas: id, nama, status, kategori |
| Division client | `client_division` | client ↔ division, memiliki `status` | aman setelah authorization; status assignment wajib dihormati |
| Assignment pengguna | `user_client` | user ↔ client, `assigned_at` | internal; jangan tampilkan assignment pengguna lain tanpa izin |
| PIC | `users` melalui `UserClient`/`Task.user` | user ↔ client/task | id, nama, role/division dapat ditampilkan; email/telepon perlu izin eksplisit |
| Project/content | `data_jasa`, KPI | `produk_client_id`, `project` | hanya ringkasan dalam scope client yang diizinkan |
| Task | `task` | `project` → `ProdukClient`, `pic` → user | metadata kerja; catatan, Jira, dan bukti penyelesaian perlu pembatasan |
| Dokumen/aset | `file_pdf`, versions | client/division/uploader | metadata saja; lokasi, isi editor, dan binary tidak boleh diekspos |
| CRM/prospek | `crm_baru` (koneksi `pgsql_supabase`; legacy `crm` juga ada) | belum ada relasi kanonik ke `produk_client` | PII/confidential; belum layak dijadikan kontak client otomatis |
| Finance | invoice/payment | `customer_id`, `invoice_id` | finansial; di luar profil client dasar |
| Integrasi platform | `oauth_connections`, `produk_client.access_token` | client ↔ provider | rahasia kritis; tidak boleh dipilih, diserialisasi, atau dilog |

Relasi konseptual:

```text
User ── user_client ── ProdukClient ── client_division ── Division
  │                         │
  └─ PIC/role               ├─ data_jasa / KPI / Task
                            ├─ FilePdf / versions
                            └─ OAuth connection / invoice
```

## Aturan akses yang terkonfirmasi

`MarketingAccessService` menolak akun yang terkunci atau terhapus. Administrator dapat melihat seluruh `produk_client`. Untuk pengguna non-admin, client harus memiliki assignment aktif ke division Digital Marketing. Role division-wide yang terkonfigurasi adalah `Busines Unit Head` dan `Digital Marketing Manager`; role tersebut dapat melihat client aktif pada division tersebut. Role biasa dibatasi lagi oleh baris `user_client` miliknya. Authorization harus selalu dihitung server-side dari OAuth identity; `client_id` pada parameter hanya selector, bukan bukti hak akses.

| Aktor | Cakupan yang diizinkan | Catatan |
|---|---|---|
| Admin | seluruh client yang tersedia | tetap tidak boleh menerima secret/credential |
| Pimpinan/role division-wide DM | client dengan assignment aktif ke DM | tidak otomatis mendapat client division lain |
| Staf DM biasa | client DM yang juga memiliki `user_client` aktif | tidak boleh melihat total/list client division secara implisit |
| Pengguna division lain | hanya scope division/assignment-nya sendiri | akses silang ke DM harus ditolak |
| Akun terkunci/nonaktif/terhapus | tidak ada | berlaku sebelum query data |

Jalur MCP yang sudah ada menggunakan middleware OAuth/Sanctum dan permission read, dengan `MarketingAccessService` sebagai pemeriksaan object-level. Route web lama memakai `web/auth`; beberapa controller lama masih memerlukan audit object-level tambahan sebelum dijadikan sumber MCP.

## Endpoint dan tool yang sudah tersedia

Route bisnis client yang teridentifikasi meliputi resource `barang_client`, `get-data-division`, `produk/{divisi}`, `produk/{id}`, `getProduk`, dan workspace Digital Marketing. Sebagian route tersebut mutatif atau mengembalikan HTML, sehingga bukan kandidat langsung untuk MCP read-only.

Tool MCP Digital Marketing yang sudah tersedia mencakup list client, ringkasan client division, analytics, content plan/mix/calendar, market research, brand guideline, tasks, approvals, assets, campaigns, comparison, channel analysis, dan search. Tidak ada tool Account/Client Management khusus yang ditambahkan dalam audit ini. Tool lama harus tetap melalui authorization terpusat dan tidak boleh mengembalikan field rahasia.

## Field sensitif dan batas eksposur

- **Rahasia kritis:** `produk_client.access_token`, `oauth_connections.access_token`, scopes/assets terpilih, OAuth credential, auth code, dan secret client.
- **PII:** email/telepon pengguna dan CRM, social media/website, session id, link meeting, isi chat, serta data kontak PIC.
- **Confidential bisnis:** budget CRM, invoice/payment, KPI/task notes, Jira, bukti penyelesaian, dan metadata storage internal.
- **Internal asset:** `file_pdf.location`, `editor_content`, binary, serta path/nama bukti upload.

Baseline aman untuk tool baru: id/nama/status/kategori client, division assignment status, PIC id/nama/role/division, ringkasan project/task yang telah diotorisasi, serta nama/version/status dokumen. Email/telepon hanya dengan permission dan kebutuhan bisnis yang terdokumentasi.

## Temuan dan gap

1. Profil client belum memiliki field kanonik kontak (email, telepon, alamat, website) pada `produk_client`.
2. CRM memiliki data kontak sensitif tetapi relasi ke client belum terbukti; penggabungan otomatis berisiko salah identitas dan kebocoran PII.
3. `getDataDivision` mengembalikan email dalam bucket role; pola ini tidak cocok sebagai kontrak MCP tanpa masking/permission.
4. `getProduk` dan endpoint web lama perlu verifikasi object-level authorization sebelum dipakai ulang oleh transport OAuth.
5. `Task`, `FilePdf`, invoice, dan koneksi OAuth memiliki field internal/secret yang harus disaring pada query dan serializer, bukan hanya di UI.
6. Sumber PIC perlu ditetapkan: assignment `user_client` untuk akses client dan `task.pic` untuk pelaksana tugas; keduanya tidak selalu identik.

## Rancangan tools read-only (belum diimplementasikan)

1. `account_get_client_profile(client_id)` — nama, status, kategori, division aktif, dan `as_of`; kontak hanya ringkasan aman.
2. `account_list_client_contacts(client_id, pagination)` — PIC/team id, nama, role, division, status; email/telepon gated oleh permission.
3. `account_get_client_access(client_id)` — scope pengguna saat ini, alasan assignment, role/division; tidak membocorkan daftar akses pengguna lain.
4. `account_get_client_projects(client_id, period, pagination)` — ringkasan content plan/KPI/project dalam scope client.
5. `account_get_client_tasks(client_id, status, pic_id, period, pagination)` — task terotorisasi dengan field kerja minimum.
6. `account_get_client_documents(client_id, pagination)` — nama, versi, status, division, waktu; tanpa lokasi, isi, atau binary.
7. `account_search_clients(query, status, division_id, pagination)` — pencarian hanya atas scope yang dapat diakses pengguna.

Kontrak bersama yang direkomendasikan: `{data, meta: {page, per_page, total, as_of, scope, source}}` dan error `{error: {code, message, request_id}}`. Semua tool harus `readOnlyHint=true`, `destructiveHint=false`, `openWorldHint=false`, menolak parameter tidak valid, memiliki batas pagination/periode, timeout, dan tidak mencatat token atau PII berlebihan.

## Urutan tindak lanjut

1. Tetapkan sumber kanonik profil/contact dan relasi CRM–client.
2. Tambahkan contract test untuk setiap object-level scope (admin, division-wide, staf, lintas division, akun nonaktif).
3. Implementasikan serializer allow-list dan query column allow-list sebelum tool Account/Client dibuat.
4. Audit ulang route web lama sebelum ada pemakaian ulang di MCP.

**Keputusan tahap 3.1:** data map dan rancangan sudah tersedia; fitur baru sengaja belum dibuat.
