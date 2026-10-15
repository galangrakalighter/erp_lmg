# Fase CRUD Digital Marketing — Fondasi Keamanan dan Desain

Status: audit dan desain. Operasi create, update, archive, dan delete belum dibuat atau diubah.

## Kesimpulan

Digital Marketing saat ini memiliki dua jalur tulis: route web `cms_market/*` dan `cms_branding/*` yang memanggil controller CRUD langsung, serta beberapa route task/KPI/workspace. MCP OAuth sengaja hanya read-only. Belum ada permission CRUD produksi yang seragam lintas route; sebagian besar route lama hanya berada di bawah `web, auth`, sehingga pemeriksaan client/division/role harus dinormalisasi sebelum operasi tulis baru dibuat.

Tidak ada kerentanan baru yang dikonfirmasi end-to-end dari audit statis ini. Namun, object-level authorization pada route CRUD lama adalah coverage gap berisiko tinggi dan tidak boleh dijadikan baseline tanpa verifikasi per controller.

## Permukaan yang diaudit

- Model: `DataJasa`, `Task`, `KPI`, `ProdukClient`, `TargetAudience`, `Positioning`, `Segmentasi`, `Swot`, `Identify`, `Moodboard`, `FilePdf`, `FilePdfVersion`, serta tabel branding/market research.
- Controller CRUD: `analize4PController`, `analizeSWOTController`, `segmentasiController`, `targetAudienceController`, `positioningController`, `brandIdentifyController`, `brandImageController`, `brandValueController`, `taglineController`, `gayaKomunikasiController`, `komposisiController`, `audioBrandingController`, `alatBrandingController`, `moodBoardController`, `HCOverviewController`, dan `DigitalMarketingWorkspaceController`.
- Route: `cms_market/*`, `cms_branding/*`, `kpi/*`, `/digital-marketing/client/{id}/platform-mirroring`, serta route task/KPI pada `routes/api.php`.
- Fondasi akses existing: OAuth `marketing:read`, `EnsureOAuthAccountIsUnlocked`, `MarketingAccessService`, `user_client`, `client_division`, role/division assignment, dan audit activity untuk MCP.

## Peta permission CRUD

| Operasi | Default | Syarat minimum desain |
|---|---|---|
| Create | deny | authenticated + permission module.create + active account + client/project scope |
| Update | deny | module.update + object-level client/division check + optimistic version check |
| Archive | deny | module.archive + manager/owner policy + reason + confirmation |
| Delete | deny | manager/admin-only, soft-delete preferred, reason, second confirmation; hard delete exceptional |
| Restore | deny | admin/manager + explicit restore permission + audit trail |

Role bukan satu-satunya keputusan. Policy harus mengevaluasi subject, action, object, client, project, division, status object, dan environment. `client_id`/`project_id` dari request tidak boleh memperluas scope pengguna.

## Matriks akses yang direkomendasikan

| Aktor | Client scope | Create/update content & research | Archive/delete | Approval/financial |
|---|---|---|---|---|
| Admin | seluruh client | full dengan audit | full terbatas | permission khusus |
| Manager/BU Head division | client assignment aktif pada division | create/update module sesuai division | archive; delete memerlukan admin | approval sesuai permission |
| Staf DM/creative | `user_client` dan project yang ditugaskan | create draft/update milik scope | tidak boleh delete; archive jika diberi permission | tidak boleh kontrak/finansial |
| PIC | task/project yang ditugaskan | update field produksi yang diizinkan | tidak boleh archive/delete | tidak boleh menyetujui diri sendiri |
| Division/client lain | tidak ada | deny | deny | deny |
| Akun locked/deleted | tidak ada | deny sebelum query | deny | deny |

## Allow-list field

### Boleh dibuat/diubah setelah policy lulus

- `data_jasa`: `judul`, `inspirasi`, relasi type/strategy/pilar/hooks/body/CTA, `durasi`, `background`, `komposisi`, `note`, tanggal kerja, platform, status draft, PIC/priority/menit sesuai role.
- Research/branding: field domain yang terkait `produk_client_id`, platform, dan konten bisnis yang sudah didefinisikan skemanya.
- `task`: nama, PIC yang berada pada scope, priority, menit, tanggal, status transition yang diizinkan, note produksi, dan relasi project.
- `KPI`: nama, point, periode, note, status, project, dan PIC melalui policy division.
- Asset metadata: nama, label, division, pin/status/version; binary upload memerlukan pipeline file terpisah.

### Tidak boleh diterima langsung dari client tanpa policy khusus

- `produk_client.access_token`, OAuth connection token/assets/scopes, secret, refresh token.
- `file_pdf.location`, `editor_content`, private path, completion proof path/binary.
- invoice/payment amount, contract value, bank/credential, PII kontak.
- `created_by`, `approved_by`, audit actor, client/division ownership, dan timestamp keamanan; semuanya server-derived.

## State transition dan konfirmasi tulis

Gunakan state machine terpusat, misalnya draft → in_progress → review → approved/revision → published/archived. Setiap transition harus memvalidasi actor, current state, project scope, dan concurrency/version. Approval tidak boleh dilakukan oleh PIC yang sama bila kebijakan separation-of-duties berlaku.

Operasi archive/delete wajib memakai confirmation token sekali pakai yang terikat pada user, object, action, payload hash, dan expiry singkat. UI confirmation bukan kontrol keamanan. Hard delete harus dilarang untuk dokumen/audit/approval; gunakan archive/soft delete dan retention policy.

## Audit log

Catat append-only: request id, actor id, OAuth client id (bukan token), action, module, object type/id, client/project/division, before/after allow-listed diff, reason, approval/confirmation id, result, timestamp, IP/device metadata sesuai kebijakan. Jangan catat password, token, secret, full PII, file content, atau payload mentah. Denied attempts tetap dicatat dengan data minimum.

## Validasi dan error contract

- Validasi tipe, enum status, tanggal/order periode, ukuran text/file, MIME/content, pagination, dan relasi foreign key sebelum mutation.
- Re-check authorization setelah lookup object dan sebelum commit.
- Gunakan transaction untuk perubahan lintas tabel; fail closed pada policy error/missing division.
- Error publik konsisten (`401/403/404/409/422`) tanpa stack trace atau SQL detail.
- Idempotency key untuk create dan optimistic locking untuk update agar retry tidak menggandakan data.

## Gap dan risiko yang harus ditutup sebelum implementasi

1. Route CRUD lama banyak hanya menggunakan `web, auth`; kontrak permission CRUD dan object-level check belum satu standar.
2. Controller branding/research melakukan query berdasarkan id client; perlu bukti `MarketingAccessService`/policy sebelum digunakan ulang.
3. Belum ada permission khusus untuk kontrak, finansial, approval, dan archive/delete.
4. Belum ada append-only approval/revision event store; field timestamp terakhir tidak cukup untuk audit history.
5. Upload asset dan dokumen memerlukan validasi content-based, private storage, ukuran, MIME, dan antivirus/pipeline.
6. Platform mirroring adalah operasi tulis lintas client/project dan memerlukan confirmation, idempotency, serta audit khusus.

## Rencana implementasi setelah desain disetujui

1. Tetapkan permission registry: `marketing.<module>.<action>` dan policy resource.
2. Bungkus seluruh mutation dalam application service/transaction dengan allow-list DTO.
3. Tambahkan policy test admin, manager, staf, PIC, lintas division/client, locked account, dan stale version.
4. Tambahkan audit log append-only dan confirmation token.
5. Migrasikan route lama satu modul per satu modul; MCP tetap read-only sampai regression selesai.

**Keputusan:** desain fondasi CRUD selesai; operasi tulis belum diimplementasikan menunggu persetujuan desain.
