# Laporan Kesiapan Fase 1 — Digital Marketing

Tanggal pengujian: 15 Oktober 2026  
Lingkup: endpoint Marketing MCP berbasis Sanctum dan OAuth, fondasi akses Digital Marketing, perlindungan data, serta regresi tool lama.

## Ringkasan keputusan

**Status: SIAP BERSYARAT untuk masuk ke Digital Marketing.**

Semua pengujian yang berada dalam lingkup Fase 1 lulus. Kontrol autentikasi, otorisasi, isolasi division/client, validasi input, perlindungan data sensitif, audit, dan sifat read-only bekerja sesuai desain. Quality gate seluruh repository belum hijau karena tiga tes lama pada webhook CRM gagal akibat ketidaksesuaian pemanggilan method test dengan signature controller. Kegagalan tersebut tidak berada pada jalur Digital Marketing atau MCP.

## Hasil pengujian Fase 1

| Area | Skenario utama | Hasil |
|---|---|---|
| Admin | Admin dapat membaca seluruh client tanpa assignment division/client | Lulus |
| Non-admin | Pengguna hanya melihat client yang diberikan kepadanya | Lulus |
| Division | Pengguna dari division lain tetap ditolak walaupun mempunyai baris assignment client | Lulus |
| Client/project | Akses silang ke client pengguna lain ditolak pada seluruh tool OAuth | Lulus |
| Role | Role harus berasal dari division yang sama; manager Digital Marketing memperoleh cakupan division | Lulus |
| Akun terkunci | Token Sanctum lama dan identitas OAuth ditolak | Lulus |
| Akun nonaktif | Akun soft-deleted tidak dapat menggunakan kembali token yang sudah ada | Lulus |
| Scope salah | Token tanpa `marketing:read` ditolak | Lulus |
| Tanpa autentikasi | Endpoint mengembalikan 401 dengan envelope error standar | Lulus |
| Data kosong | Mengembalikan struktur `data`, `pagination`, dan `meta` yang konsisten | Lulus |
| Data rusak | Metric tidak valid diabaikan dan kualitas data ditandai `partial` tanpa menjadi nilai nol palsu | Lulus |
| Parameter | Tanggal, periode maksimum, filter, metric, pagination, dan parameter tak dikenal divalidasi | Lulus |
| Data sensitif | Token/credential tidak keluar pada respons dan disamarkan pada audit log | Lulus |
| Read-only | Seluruh 16 route Sanctum/OAuth hanya menggunakan GET/HEAD dan tidak mengubah tabel domain | Lulus |

## Bukti eksekusi

### Matriks keamanan dan regresi terarah

- 63 tes lulus.
- 638 assertions lulus.
- Mencakup Marketing MCP, Digital Marketing permissions, activity log, token platform lama, koneksi OAuth terenkripsi, dan autentikasi API bisnis.

### Regression suite seluruh repository

- 112 tes lulus.
- 760 assertions dijalankan.
- 3 tes gagal pada `Tests\\Unit\\CrmHumanRefreshWebhookTest`.

Ketiga kegagalan memiliki akar masalah yang sama: tes memanggil `CRMController::refreshHumanData()` tanpa argumen, sedangkan method saat ini mewajibkan `Request $request`. Lokasi kegagalan ada pada baris 33, 61, dan 82 file tes CRM. Tidak ada kegagalan pada suite Marketing Analytics/MCP atau permission Digital Marketing.

## Risiko tersisa dan batas pengujian

1. Belum dilakukan smoke test terhadap penyedia OAuth eksternal dan database produksi; verifikasi menggunakan lingkungan pengujian lokal dan identitas Passport/Sanctum terkontrol.
2. Belum dilakukan pengujian beban terhadap throttle dan volume data produksi.
3. Quality gate global tetap merah sampai tiga tes CRM diperbaiki atau diselaraskan dengan signature controller.

## Rekomendasi masuk Digital Marketing

1. Fase 1 dapat dipromosikan ke lingkungan Digital Marketing terbatas/staging.
2. Gunakan akun uji admin dan non-admin dari division Digital Marketing untuk smoke test pascadeploy.
3. Verifikasi satu client yang diizinkan dan satu client yang dilarang melalui OAuth nyata.
4. Pantau audit log dan respons 401/403/422/429 selama masa observasi awal.
5. Perbaiki tiga tes CRM sebelum menjadikan suite seluruh repository sebagai quality gate wajib untuk produksi.

## Kriteria akhir

- **Go untuk staging/pilot Digital Marketing:** Ya.
- **Go untuk produksi terbatas:** Ya, setelah smoke test OAuth nyata dan database target.
- **Quality gate repository sepenuhnya hijau:** Belum, karena tiga kegagalan tes CRM di luar lingkup Fase 1.
