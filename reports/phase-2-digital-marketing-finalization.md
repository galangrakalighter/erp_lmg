# Finalisasi Fase 2 — Digital Marketing

Tanggal verifikasi: 15 Oktober 2026

## Hasil

Fase 2 menyediakan 19 tool read-only untuk daftar client, ketersediaan data, pencarian, market research, branding, content plan/calendar, task, approval, metadata aset, campaign, performance, tren, top content, paid performance, perbandingan periode, dan analisis klasifikasi kanal.

Pencarian hanya berjalan dalam satu `client_id` yang telah lolos pemeriksaan akses. Dataset yang dicari adalah market research, brand guideline, content calendar, task, approval, metadata aset, dan item campaign snapshot. Lokasi/isi dokumen, password, token, API key, dan credential tidak dicari atau dikembalikan.

## Cakupan pengujian

- admin dan non-admin;
- akun terkunci/nonaktif, scope salah, serta tanpa autentikasi;
- role yang tidak sesuai division;
- pengguna berbeda division dan client;
- client assignment aktif dan nonaktif;
- data kosong, JSON rusak, field hilang, baseline nol, dan currency campuran/hilang;
- metadata dokumen/aset tanpa lokasi file atau isi binary;
- parameter asing, allowlist, pagination, serta periode maksimum 366 hari;
- semua route MCP hanya `GET/HEAD`;
- audit log tidak menyimpan query pencarian mentah atau credential;
- regression test tool lama;
- pertanyaan nyata tentang audience, positioning, tone of voice, kalender/PIC, paid versus organic, perubahan periode, ATL/TTL/BTL, dan pencarian tema campaign.

## Hasil pengujian

- Contract test Marketing MCP: 46 tes lulus, 609 assertion.
- Manifest dan server MCP: sintaks valid, versi 0.2.0, 19 tool terdaftar.
- Regression suite proyek: 115 tes lulus, 3 tes gagal yang tidak terkait Fase 2.

Tiga kegagalan yang tersisa berada pada `Tests\Unit\CrmHumanRefreshWebhookTest`: tes memanggil `CRMController::refreshHumanData()` tanpa argumen `Request`, sementara method controller mewajibkan satu argumen. Kegagalan ini sudah ada sebelum finalisasi Fase 2 dan tidak menyentuh jalur Digital Marketing.

## Keamanan

Tidak ditemukan kerentanan baru yang terkonfirmasi pada jalur pencarian dan tool Fase 2. Authorization tetap deny-by-default melalui identitas pengguna, division, role, client assignment, dan object-level client check. Input memakai allowlist/parameter binding, respons dilindungi redaction berbasis key, serta audit hanya merekam filter aman.

Risiko data yang tetap harus dipahami:

- kualitas analisis bergantung pada snapshot dan relasi ERP yang tersedia;
- field yang tidak tersedia tidak boleh diasumsikan nol;
- klasifikasi ATL/TTL/BTL tidak boleh ditebak;
- spend tidak boleh dijumlahkan ketika currency berbeda atau tidak diketahui;
- metadata dokumen tersedia, tetapi isi dokumen sengaja tidak diekspos.

## Paket plugin

Paket lokal diperbarui ke versi 0.2.0. Server MCP, schema input/output, metadata plugin, README, dan skill instruction telah diselaraskan. Paket distribusi OAuth juga diperbarui pada metadata dan instruksi analisisnya.
