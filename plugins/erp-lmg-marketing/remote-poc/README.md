# Remote MCP OAuth POC

Tahap 2 hanya mengekspos `marketing_list_clients`. Server lokal stdio yang sudah ada tidak diubah.

## Persiapan ERP

1. Jalankan migrasi Passport di lingkungan yang dituju.
2. Buat key Passport dengan `php artisan passport:keys` dan simpan sebagai secret server.
3. Buat public client PKCE dengan `php artisan passport:client --public --provider=oauth_users`.
4. Daftarkan redirect URI yang diberikan Codex saat konfigurasi koneksi.
5. Isi `APP_URL`, `MARKETING_MCP_OAUTH_ISSUER`, dan `MARKETING_MCP_RESOURCE` dengan URL HTTPS publik.
6. Minta scope `marketing:read offline_access` agar ChatGPT dapat memperbarui token tanpa login berulang.

Jangan menjalankan migrasi atau membuat client langsung di produksi sebelum backup dan pemeriksaan konfigurasi.

## Menjalankan POC

Konfigurasi Docker menjalankan server Node sebagai service `mcp`. Nginx meneruskan endpoint publik `https://erp.lightermediagroup.com/mcp` ke service tersebut. Integrasi lokal stdio tetap tidak berubah.

Endpoint discovery OAuth berada di:

- `/.well-known/oauth-protected-resource`
- `/.well-known/oauth-authorization-server`

Endpoint ERP yang dilindungi OAuth berada di `/api/oauth-marketing-mcp/clients`.

## Smoke test OAuth lokal

Setelah Laravel dan MCP lokal aktif, jalankan `oauth-local-smoke.mjs` dengan `OAUTH_CLIENT_ID`, `OAUTH_ISSUER`, dan `MCP_URL`. Script hanya menyimpan token di memori, tidak menampilkannya, lalu memeriksa PKCE, refresh token, daftar tool, dan sampel client yang dapat diakses.
