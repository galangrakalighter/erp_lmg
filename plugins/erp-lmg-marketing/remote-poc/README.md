# Remote MCP OAuth — ERP LMG Digital Marketing

Remote MCP produksi mengekspos 86 tool Digital Marketing: 36 read-only dan operasi CRUD/approval/upload yang terotorisasi. Definisi tool, schema, annotation, validator, dan pemetaan endpoint dipakai bersama dengan transport stdio dari `../server/index.mjs`. Transport HTTP selalu meneruskan bearer token pengguna ke endpoint OAuth Laravel; endpoint Sanctum tidak digunakan.

## Persiapan ERP

1. Jalankan migrasi Passport di lingkungan yang dituju.
2. Buat key Passport dengan `php artisan passport:keys` dan simpan sebagai secret server.
3. Buat public client PKCE dengan `php artisan passport:client --public --provider=oauth_users`.
4. Daftarkan redirect URI yang diberikan Codex saat konfigurasi koneksi.
5. Isi `APP_URL`, `MARKETING_MCP_OAUTH_ISSUER`, dan `MARKETING_MCP_RESOURCE` dengan URL HTTPS publik.
6. Minta scope `marketing:read offline_access` agar ChatGPT dapat memperbarui token tanpa login berulang.

Jangan menjalankan migrasi atau membuat client langsung di produksi sebelum backup dan pemeriksaan konfigurasi.

## Menjalankan server

Konfigurasi Docker menjalankan server Node sebagai service `mcp`. Nginx meneruskan endpoint publik `https://erp.lightermediagroup.com/mcp` ke service tersebut. Integrasi lokal stdio tetap tidak berubah.

Endpoint discovery OAuth berada di:

- `/.well-known/oauth-protected-resource`
- `/.well-known/oauth-authorization-server`

Endpoint ERP yang dilindungi OAuth berada di bawah `/api/oauth-marketing-mcp`. Laravel tetap menentukan akses client berdasarkan identitas OAuth, role, division, dan assignment pengguna.

Server menyediakan:

- `POST /mcp`
- `GET /health`

Nama server: `erp-lmg-digital-marketing`. Versi: `1.7.5`.

## Pengujian kontrak

```bash
node --check plugins/erp-lmg-marketing/server/index.mjs
node --check plugins/erp-lmg-marketing/remote-poc/server.mjs
node --test --test-isolation=none plugins/erp-lmg-marketing/remote-poc/server.test.mjs
```

Suite memeriksa 86 tool, annotation read-only/write, upload multipart PDF, mapping endpoint OAuth, forwarding token tanpa kebocoran, challenge OAuth, validasi input, timeout, non-JSON, serta error Laravel 401/403/404/422/500.

## Smoke test OAuth lokal

Setelah Laravel dan MCP lokal aktif, jalankan `oauth-local-smoke.mjs` dengan `OAUTH_CLIENT_ID`, `OAUTH_ISSUER`, dan `MCP_URL`. Script hanya menyimpan token di memori, tidak menampilkannya, lalu memeriksa PKCE, refresh token, daftar tool, dan sampel client yang dapat diakses.
