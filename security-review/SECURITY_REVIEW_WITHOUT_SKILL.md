# Audit Keamanan Laravel — Tanpa Skill Khusus

Tanggal pemeriksaan: 12 Oktober 2026  
Ruang lingkup: source code dan dependency proyek `erp_lmg`  
Metode: pemeriksaan statis langsung, daftar route Laravel, pencarian pola sensitif, serta `composer audit`.

## Ringkasan

Audit langsung ini menemukan enam kelompok masalah utama:

| Prioritas | Temuan | Status |
|---|---|---|
| Kritis | Endpoint bisnis/API dapat diakses tanpa autentikasi | Terkonfirmasi |
| Kritis | Pengguna biasa dapat mencapai fungsi manajemen pengguna dan mengatur status admin | Terkonfirmasi |
| Tinggi | Kunci API WAHA ditulis langsung di source code | Terkonfirmasi |
| Tinggi | Dokumen PDF client disimpan pada disk publik | Terkonfirmasi, dampak bergantung nama/path file |
| Tinggi | Dependency Composer memiliki advisori keamanan aktif | Terkonfirmasi |
| Sedang | Login tidak memiliki pembatasan percobaan pada level aplikasi | Terkonfirmasi |

Kesimpulan singkat: aplikasi belum aman untuk diekspos ke internet sebelum dua temuan kritis diperbaiki. Pemeriksaan ini tidak mengubah kode dan tidak melakukan eksploitasi terhadap sistem aktif.

## 1. Endpoint bisnis/API tanpa autentikasi

**Prioritas: Kritis**

Sejumlah route sensitif pada `routes/api.php` tidak berada di dalam grup `auth`. Hasil `php artisan route:list -v` menunjukkan endpoint CRM hanya memiliki middleware `api`, bukan autentikasi.

Contoh bukti:

- `routes/api.php:83-86`: update selection, data PIC, data HC, dan daftar pengguna.
- `routes/api.php:95-108`: KPI, divisi, produk, invoice, payroll, dan audit trail.
- `routes/api.php:110-121`: baca, tambah, ubah, dan hapus data CRM serta checklist/follow-up.

**Dampak:** pihak yang tidak login berpotensi membaca data internal, mengubah atau menghapus data CRM/client, dan memperoleh informasi pengguna atau payroll.

**Perbaikan:** tempatkan seluruh endpoint internal di grup `web`, `auth`, dan middleware otorisasi yang sesuai. Gunakan policy/permission per resource; jangan hanya mengandalkan penyembunyian tombol di tampilan.

## 2. Eskalasi hak akses melalui manajemen pengguna

**Prioritas: Kritis**

Route `manajemen_user` berada di grup autentikasi umum, tetapi tidak di dalam middleware `EnsureUserIsAdmin` (`routes/web.php:107-109`). Daftar route juga hanya menunjukkan middleware `Authenticate`.

Pada proses pembuatan dan pembaruan user, nilai `isAdmin` ditentukan dari input `divisi_id`:

- `app/Http/Controllers/UserController.php:222-250`
- `app/Http/Controllers/UserController.php:287-317`

Pengguna yang sudah login berpotensi mengirim permintaan langsung dengan nilai divisi tertentu dan membuat atau mengubah akun menjadi admin.

**Perbaikan:** lindungi seluruh route manajemen user dengan `EnsureUserIsAdmin`; validasi hak akses di controller/policy; jangan menentukan `isAdmin` langsung dari input yang dikendalikan pengguna.

## 3. Kunci API WAHA tersimpan di source code

**Prioritas: Tinggi**

Kunci didefinisikan di `app/Http/Controllers/CRMController.php:2444` dan dikirim sebagai header pada `CRMController.php:3744-3745`.

**Dampak:** siapa pun yang memperoleh source code, backup, atau riwayat Git dapat memakai kredensial tersebut. Menghapusnya dari versi terbaru saja tidak mencabut salinan lama.

**Perbaikan:** cabut dan buat ulang kunci WAHA, simpan nilai baru pada environment/secret manager, akses melalui konfigurasi Laravel, dan pastikan nilai rahasia tidak masuk log atau Git.

## 4. Dokumen client berada pada storage publik

**Prioritas: Tinggi**

PDF ditulis ke disk `public`, antara lain pada:

- `app/Http/Controllers/PDFController.php:339`
- `app/Http/Controllers/PDFController.php:441`
- `app/Http/Controllers/ProdukController.php:908`

Disk tersebut dipetakan ke `public/storage` melalui `config/filesystems.php:41-45` dan `config/filesystems.php:76-78`. Walaupun endpoint preview memakai autentikasi, file pada disk publik dapat dilewati melalui URL langsung apabila path-nya diketahui atau dapat ditebak.

**Perbaikan:** pindahkan dokumen client ke disk private/local di luar web root dan sajikan hanya melalui controller yang melakukan autentikasi serta pemeriksaan hak akses terhadap client terkait.

## 5. Dependency memiliki advisori keamanan aktif

**Prioritas: Tinggi**

`composer audit --locked` melaporkan 68 advisori pada 20 package:

- 2 kritis
- 22 tinggi
- 33 sedang
- 10 rendah
- 1 tanpa klasifikasi severity

Package terdampak mencakup komponen aplikasi seperti Laravel, PhpSpreadsheet, Dompdf, Guzzle, dan beberapa komponen Symfony. Risiko PhpSpreadsheet relevan karena aplikasi menerima file `xlsx`, `xls`, atau `csv` lalu mengimpornya (`app/Http/Controllers/SosialCreatorController.php:182-189`).

**Perbaikan:** buat branch khusus pembaruan dependency, jalankan `composer update` secara terkontrol menuju versi yang sudah diperbaiki, lalu uji fitur login, impor spreadsheet, PDF, email, HTTP client, dan seluruh test suite. Jangan memperbarui lockfile langsung di produksi tanpa pengujian regresi.

## 6. Login tanpa rate limiting

**Prioritas: Sedang**

`app/Http/Controllers/LoginController.php:17-28` memvalidasi kredensial dan langsung menjalankan `Auth::attempt`. Tidak ditemukan `RateLimiter`, middleware `throttle`, atau mekanisme pembatasan percobaan login pada jalur tersebut.

**Dampak:** endpoint lebih mudah diserang dengan percobaan password berulang atau credential stuffing.

**Perbaikan:** batasi percobaan berdasarkan kombinasi email dan IP, beri jeda/lock sementara, catat kejadian mencurigakan, dan tetap gunakan pesan gagal yang tidak membocorkan apakah email terdaftar.

## Catatan tambahan

- Beberapa endpoint lain pada awal `routes/api.php` juga tidak memakai autentikasi. Semua route API perlu ditinjau, bukan hanya contoh di atas.
- Penyimpanan ke disk publik tidak selalu merupakan kerentanan jika isinya memang aset publik. Temuan di atas diprioritaskan karena konteksnya adalah dokumen client.
- Pemeriksaan ini tidak menguji konfigurasi reverse proxy, firewall, database production, permission server, atau kredensial yang sudah terlanjur tersebar.
- Tidak ditemukan bukti langsung command injection atau unsafe `unserialize` dalam pencarian statis sederhana ini. Ini bukan jaminan bahwa kelas masalah tersebut tidak ada.

## Urutan penanganan yang disarankan

1. Tutup semua API internal dengan autentikasi dan otorisasi per resource.
2. Batasi manajemen user hanya untuk admin dan putuskan hubungan input user dengan `isAdmin`.
3. Cabut serta rotasi kunci WAHA yang tertanam di source code.
4. Pindahkan dokumen client ke storage private.
5. Perbarui dependency terdampak dan jalankan regression test.
6. Tambahkan rate limiting pada login.

## Perbandingan singkat dengan audit berbasis skill

Audit tanpa skill ini tetap menemukan enam kelompok risiko utama yang sama, tetapi hasilnya lebih ringkas dan tidak menghasilkan threat model formal, matriks trust boundary, maupun PoC per temuan. Audit berbasis skill memberikan proses validasi dan artefak bukti yang lebih sistematis; audit langsung lebih cepat dan cukup untuk menemukan masalah yang tampak jelas, tetapi lebih mudah melewatkan jalur serangan yang kompleks atau mengukur severity secara kurang konsisten.
