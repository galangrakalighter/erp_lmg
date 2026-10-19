# OWASP Security Code Review — ERP LMG

Tanggal review: 13 Oktober 2026  
Mode: baseline manual review, kondisi working tree saat review  
Framework: Laravel 12 / PHP 8.4 target / PostgreSQL / Nginx dan PHP-FPM dalam Docker

## Threat model ringkas

### Aktor

- Pengunjung internet tanpa akun.
- User ERP biasa dari satu divisi.
- User terautentikasi yang mencoba mengakses divisi atau fungsi Finance lain.
- Penyerang pada jalur jaringan antara ERP dan API Meta.
- Penyerang yang dapat menjangkau port PostgreSQL host.

### Aset utama

- Data payroll, invoice, jurnal, audit trail, data client, dan file operasional.
- Kredensial database serta access token Meta/Instagram/Facebook.
- Integritas workflow n8n dan biaya/kapasitas layanan AI.
- Kredensial dan sesi user ERP.

### Trust boundary

- Browser publik -> route Laravel.
- User divisi -> data divisi/client/Finance.
- Laravel -> n8n dan API pihak ketiga.
- Jaringan host -> PostgreSQL Docker.
- Repository/configuration -> secret runtime.

## Temuan

### 1. Critical — User biasa dapat membaca dan mengubah data Finance serta payroll

**Severity:** Critical  
**Location:** `routes/web.php:335-383`, `routes/api.php:107-110`, `app/Http/Controllers/PayrollController.php:22-30`, `app/Http/Controllers/PayrollController.php:33-125`, `app/Http/Controllers/PayrollController.php:129-215`, `app/Http/Controllers/PayrollController.php:306-323`, `app/Http/Controllers/DashboardController.php` method `getDataAudit()`  
**Issue:** Route Finance, invoice, journal, payroll, pembayaran, report, dan audit trail hanya dilindungi `auth`. Controller payroll tidak melakukan pemeriksaan admin, divisi Finance, perusahaan, maupun object-level authorization.  
**Exploit Path:** User ERP biasa login -> mengganti URL/ID perusahaan atau payroll -> membaca seluruh payroll melalui `/api/get-data-payroll` atau membuat/mengubah/menghapus payroll dan jurnal melalui route Finance.  
**Impact:** Kebocoran data gaji seluruh pegawai, manipulasi catatan keuangan, pembayaran, invoice, jurnal, dan audit trail.  
**Remediation:** Buat middleware/policy Finance deny-by-default; izinkan hanya admin dan anggota divisi Finance yang sesuai. Terapkan company-level authorization dan object-level checks di controller/policy, bukan hanya sidebar. Tambahkan policy untuk setiap mutasi dan export.  
**Test:** Feature test harus memastikan user non-Finance menerima 403 untuk seluruh route Finance dan API payroll/audit; anggota Finance hanya boleh mengakses perusahaan yang diizinkan.  
**PoC Requirements:** `poc_authenticated_finance_access.py`; memerlukan akun user biasa dan cookie sesi. Default hanya mencetak request; `--execute` hanya melakukan GET baca.

### 2. Critical — Kredensial PostgreSQL tertanam dan database dipublikasikan ke host

**Severity:** Critical jika port host dapat dijangkau jaringan; High jika firewall membatasi port hanya ke localhost.  
**Location:** `docker-compose.yml:38-46`  
**Issue:** Password superuser PostgreSQL tertanam di repository dan port container dipetakan sebagai `5432:5432`, yang secara default bind ke seluruh interface host. Username juga menggunakan superuser `postgres`.  
**Exploit Path:** Penyerang memperoleh source/repository -> mengambil password -> menjangkau TCP/5432 host -> login sebagai `postgres`.  
**Impact:** Kontrol penuh database ERP, termasuk data akun, payroll, invoice, client, token, dan kemampuan mengubah seluruh data aplikasi.  
**Remediation:** Rotasi password segera; gunakan secret dari `.env`/Docker secret; buat role aplikasi non-superuser; hapus publikasi port jika hanya app container yang membutuhkan database, atau bind `127.0.0.1:5432:5432` bila akses host benar-benar diperlukan; batasi firewall.  
**Test:** `docker compose config` tidak boleh mengandung secret literal; `ss -lntp` tidak boleh menunjukkan PostgreSQL pada `0.0.0.0:5432`; koneksi dengan password lama harus gagal.  
**PoC Requirements:** `poc_database_credentials_exposure.py`; default memindai konfigurasi lokal. Uji koneksi hanya dengan flag eksplisit dan tetap tidak mengirim kredensial.

### 3. High — Isolasi divisi/client dapat dilewati pada workspace generik

**Severity:** High  
**Location:** `app/Http/Controllers/DivisionClientWorkspaceController.php:35-43`, `app/Http/Controllers/ClientKpiTimelineController.php:13-38`, `app/Http/Controllers/ClientContentGuideController.php:118-122`, route `client/{client}/kpi-timeline`, `client/{client}/content-guide`, dan `division/{division}/client/{id_produk}` di `routes/web.php`  
**Issue:** `DivisionClientWorkspaceController` memakai kondisi OR: role `Busines Unit Head` dari divisi mana pun, keanggotaan divisi, atau assignment client mana pun cukup untuk membuka workspace. Timeline tidak lebih dulu memastikan actor ditugaskan pada client/divisi. Content guide bahkan mengizinkan seluruh client saat resolver role menghasilkan string kosong.  
**Exploit Path:** User satu divisi login -> menebak ID client/divisi lain -> membuka route workspace/timeline/guide secara langsung, meskipun sidebar menyembunyikannya.  
**Impact:** Kebocoran informasi client lintas divisi, KPI, PIC/email, timeline dan panduan operasional; kemungkinan akses lanjutan melalui link dalam workspace.  
**Remediation:** Sentralisasi policy `viewClientInDivision(user, client, division)` dan wajibkan kombinasi keanggotaan divisi DAN assignment client, kecuali admin. Ikat role BUH pada division assignment yang sama. Jangan pernah fail-open ketika role kosong. Terapkan policy yang sama pada semua sub-route, API, export dan file.  
**Test:** Matriks feature test user x divisi x assignment x client; setiap kombinasi yang bukan miliknya harus 403/404, termasuk akses URL langsung.  
**PoC Requirements:** `poc_cross_division_workspace_access.py`; memerlukan cookie sesi user non-admin serta ID client/divisi pembanding; default dry-run, `--execute` hanya GET.

### 4. High — Verifikasi sertifikat TLS dimatikan untuk API Meta

**Severity:** High  
**Location:** `app/Services/InstagramService.php:39,108,124,203,226,234,260`, `app/Services/FacebookService.php:35,62,107,127,164,168,203,237`, `app/Services/MetaAdsService.php:55,87`  
**Issue:** Request HTTPS menggunakan `withOptions(['verify' => false])`. Ini menonaktifkan validasi sertifikat dan hostname. Request membawa access token dan mengambil data akun.  
**Exploit Path:** Penyerang berada pada jalur jaringan/DNS/proxy -> menyajikan sertifikat palsu -> ERP menerima endpoint palsu -> access token atau data sosial dicuri/dimodifikasi.  
**Impact:** Pengambilalihan atau penyalahgunaan akun Meta yang terhubung, kebocoran insight, dan manipulasi data analitik.  
**Remediation:** Hapus seluruh `verify => false`; perbaiki CA bundle pada image/server; gunakan CA resmi atau CA internal yang eksplisit jika ada proxy perusahaan; jangan membuat fallback yang menonaktifkan TLS. Rotasi token bila pernah melewati jaringan tak tepercaya.  
**Test:** Unit test memalsukan request dan memastikan opsi TLS verification tidak dinonaktifkan; integration test harus menolak sertifikat self-signed/hostname salah.  
**PoC Requirements:** `poc_disabled_tls_verification.py`; pemeriksaan statis aman, tanpa melakukan MITM.

### 5. High — Endpoint n8n/AI dapat dipicu tanpa login

**Severity:** High  
**Location:** `routes/web.php:393-434`, `routes/web.php:440-441`, `app/Http/Controllers/BarangClientController.php:43-87`  
**Issue:** `/ai/chat-proxy`, `/n8n-trigger`, dan `/n8n-trigger-baru-lagi` berada di luar group `auth` dan tidak memiliki rate limit. CSRF bukan autentikasi; pengunjung dapat mengambil session/token dari halaman login lalu mengirim request. Endpoint produksi n8n menerima URL spreadsheet, template, kategori dan limit dari guest.  
**Exploit Path:** Guest membuka login untuk memperoleh CSRF -> POST ke endpoint -> Laravel memanggil webhook n8n produksi atau AI webhook dengan identitas/divisi yang dapat dipalsukan.  
**Impact:** Penyalahgunaan otomasi dan resource, pemrosesan data tidak sah, spam/DoS terhadap n8n, serta pemalsuan metadata user/divisi. Dampak SSRF downstream belum dikonfirmasi karena workflow n8n tidak termasuk scope.  
**Remediation:** Pindahkan endpoint ke group `auth`; turunkan identity/division dari session, jangan request; tambahkan authorization sesuai fitur, throttle, batas panjang, allowlist spreadsheet host, dan signature secret Laravel->n8n. Hapus endpoint dummy publik.  
**Test:** Guest harus menerima redirect/401; user tanpa izin 403; throttle menghasilkan 429; payload tidak dapat mengganti user/divisi.  
**PoC Requirements:** `poc_public_automation_trigger.py`; default hanya discovery. `--execute-safe` memanggil endpoint dummy non-state-changing; live trigger sengaja tidak diimplementasikan.

### 6. Medium — Login tidak memiliki rate limiting dan OTP tidak ada pada code path aktif

**Severity:** Medium  
**Location:** `routes/web.php:436-439`, `app/Http/Controllers/LoginController.php:18-56`  
**Issue:** Route `/post-login` tidak memakai middleware `throttle`, controller langsung menjalankan `Auth::attempt`, dan tidak ada `RateLimiter`. Repository juga tidak memiliki alur OTP aktif meskipun OTP pernah menjadi requirement operasional.  
**Exploit Path:** Penyerang mengirim percobaan password berulang untuk email yang diketahui tanpa lockout berbasis IP+identity atau respons 429.  
**Impact:** Password spraying dan brute force akun ERP; akun yang berhasil diambil memberikan akses ke data internal dan dapat dirantai dengan temuan otorisasi.  
**Remediation:** Tambahkan Laravel rate limiter berbasis kombinasi email ter-normalisasi dan IP; respons generik; exponential backoff/temporary lock; audit event; implementasikan OTP/MFA server-side dengan kode hashed, single-use, expiry singkat, attempt limit dan session rotation. Pengecualian akun tertentu menurunkan keamanan dan sebaiknya tidak dilakukan.  
**Test:** Setelah batas percobaan, login harus 429/terkunci sementara; sukses mereset counter; OTP tidak dapat digunakan ulang dan kedaluwarsa; session diregenerasi hanya setelah seluruh faktor selesai.  
**PoC Requirements:** `poc_login_bruteforce.py`; default pemeriksaan statis. `--execute` dibatasi tiga password invalid dan tidak mencoba kredensial nyata.

## Open Questions / Assumptions

- Apakah firewall server mengekspos TCP/5432 ke internet/LAN tidak diverifikasi; severity database bergantung pada reachability tetapi secret literal tetap terkonfirmasi.
- Workflow n8n tidak tersedia, sehingga kemungkinan SSRF melalui `sheet_url` dicatat sebagai risiko downstream, bukan temuan SSRF terkonfirmasi.
- `.env` lokal berisi `APP_ENV=local` dan `APP_DEBUG=true`; konfigurasi produksi tidak diperiksa. Pastikan produksi menggunakan `APP_ENV=production`, `APP_DEBUG=false`, cookie Secure, dan HTTPS.
- Reverse proxy/TLS/security headers di luar `nginx.conf` repository tidak tersedia untuk review.
- Git history tidak dipindai untuk secret yang pernah ter-commit.
- Pengujian live terhadap server produksi tidak dilakukan.

## Coverage

- Route web/API dan middleware autentikasi/otorisasi.
- Login, session defaults, admin guard, monitoring, OAuth state, client workspace, Finance/payroll/audit.
- Upload dan penyimpanan file privat, HTML document sanitizer, signed local-storage route.
- Outbound HTTP, TLS verification, webhook n8n, WAHA configuration.
- Dockerfile, Compose, Nginx, database exposure dan secret handling.
- Pencarian SQL raw, command execution, deserialization, XSS sinks, public storage, OAuth/token flow.
- `composer audit --locked`: tidak ada advisory pada saat review.
- `npm audit`: 0 vulnerability pada saat review.

Kontrol yang terverifikasi baik mencakup admin middleware pada Data Center/manajemen user, session regeneration setelah login, logout invalidation, OAuth `state` yang random dan dibandingkan constant-time, file client pada disk privat dengan authorization controller, signed route Laravel untuk penyajian disk lokal, parameter binding pada raw SQL yang ditinjau, dan sanitasi HTML dokumen sebelum raw rendering.
