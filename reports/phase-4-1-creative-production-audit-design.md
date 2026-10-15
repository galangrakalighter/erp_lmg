# Fase 4.1 — Audit Data dan Hak Akses Creative & Production

Status: audit dan desain saja. Tidak ada fitur, route, tool MCP, database, credential, atau package yang diubah.

## Kesimpulan

Data produksi saat ini terutama direpresentasikan oleh `data_jasa` (content/creative plan), `task` (pekerjaan produksi), `file_pdf` dan versinya (aset/dokumen), serta field approval/revisi pada `task`. Belum ditemukan model kanonik terpisah untuk creative request, creative brief, copy, desain, atau event history approval. Karena itu, desain MCP harus mengembalikan fakta yang tersimpan dan menandai data yang tidak tersedia sebagai `unavailable`/`partial`, bukan menyimpulkan status.

Tidak ada temuan kerentanan baru yang dapat dikonfirmasi end-to-end dalam audit ini. Beberapa gap authorization dan kelengkapan data tetap harus ditutup sebelum tool produksi diekspos.

## Trust boundary dan aktor

```text
OAuth identity / web session
          │
          ▼
MarketingAccessService (akun aktif, role, division, user_client)
          │
          ├── produk_client ── client_division / user_client
          ├── data_jasa ───── creative/content plan
          ├── task ─────────── PIC, deadline, status, approval/revisi
          └── file_pdf ─────── asset metadata / versions / private storage
```

Aktor yang perlu dibedakan: admin; pimpinan/role division-wide; staf production/creative pada division; PIC yang ditugaskan; reviewer/approver; dan pengguna division atau client lain. `client_id`, `project`, `pic`, `division_id`, nama file, dan filter periode adalah input yang tidak boleh dipercaya sebagai bukti akses.

## Peta data dan status ketersediaan

| Kebutuhan | Sumber yang ditemukan | Field aman untuk ringkasan | Keterbatasan |
|---|---|---|---|
| Creative request | belum ada tabel/model kanonik | `unavailable` | request intake dan requester tidak terdefinisi |
| Creative brief | `data_jasa` | `judul`, inspirasi, note ringkas, platform, tanggal | note dapat berisi copy/PII; perlu redaksi/permission |
| Produksi konten | `data_jasa`, `task` | id, judul/nama, platform, tanggal, status, PIC | tidak ada state machine creative yang lengkap |
| Desain | `file_pdf`, `file_pdf_versions`, aset platform | nama/version/status/pin/division | lokasi, isi editor, binary, path storage rahasia |
| Copy | `data_jasa.note`, field konten terkait | hanya metadata/copy note jika permission | caption/copy kanonik belum selalu tersedia |
| Aset | `file_pdf` dan `DigitalMarketingDataService::assets` | nama, division, uploader id/name, version count | tidak boleh mengembalikan lokasi/binary/email uploader |
| PIC | `task.pic` → `users`, `user_client` | user id, nama, role/division bila tersedia | email/telepon PII; assignment dan PIC task dapat berbeda |
| Deadline | `data_jasa.tanggal_deadline`, `task.tanggal_deadline` | tanggal dan freshness | timezone/status harus dinormalisasi |
| Approval | `task.tanggal_approve`, status | status dan timestamp terakhir | approver/event history belum tersimpan kanonik |
| Revisi | `task.tanggal_revisi`, status revisi | timestamp/status terakhir | riwayat multi-event dan alasan revisi belum tersedia |

## Aturan akses rancangan

Semua read harus memanggil policy terpusat dan melakukan object-level check terhadap client/project. Pengguna terautentikasi tidak otomatis memiliki akses ke semua project.

| Scope | Akses produksi yang disarankan |
|---|---|
| Admin | seluruh client/project, tetapi tetap tanpa secret dan field pribadi default |
| Pimpinan division-wide | project client yang memiliki assignment aktif pada division terkait |
| Staf creative/production | hanya client yang terhubung melalui `user_client` dan project/task yang berada di client tersebut |
| PIC | task yang ditugaskan; akses brief/aset client mengikuti client scope, bukan hanya `pic` parameter |
| Reviewer/approver | approval metadata pada project yang berwenang; nilai kontrak/finansial memerlukan permission tambahan |
| Division/client lain | deny-by-default; tidak boleh menembus dengan mengganti id |
| Akun terkunci/terhapus | seluruh akses ditolak sebelum query data |

Data contract, nilai finansial, kontak pribadi, token OAuth, dan isi dokumen perlu permission terpisah. `isAdmin` saja bukan pengganti permission domain untuk jangka panjang; permission produksi sebaiknya eksplisit dan diaudit.

## Endpoint dan tool yang telah ada

- MCP Digital Marketing sudah menyediakan `marketing_get_content_plan`, `marketing_get_content_calendar`, `marketing_get_tasks`, `marketing_get_approvals`, dan `marketing_get_assets`.
- Tool Account/Client yang telah ditambahkan menyediakan brief, status project, milestone, dokumen/riwayat, summary, dan search.
- Route web production/content dan controller lama menggunakan session/web auth; belum semuanya memiliki kontrak object-level yang sama dengan MCP.
- `DigitalMarketingDataService::approvals` secara eksplisit menyatakan hanya approval/revisi terakhir yang tersedia; tidak boleh dipresentasikan sebagai event history lengkap.
- `Meeting` berada pada koneksi `pgsql_supabase` dan terhubung ke CRM human, tetapi tidak memiliki relasi kanonik ke `produk_client`; meeting notes belum layak diekspos per-client.

## Data sensitif

- **Rahasia kritis:** `produk_client.access_token`, `oauth_connections.access_token`, assets/selected assets, client secret, refresh token.
- **PII:** email/telepon PIC, CRM human, requester/approver, link meeting, isi komunikasi.
- **Confidential produksi:** brief note/copy yang belum dipublikasikan, komentar revisi, Jira, completion proof, lokasi file, editor content.
- **Finansial/kontrak:** invoice, payment, budget, nilai kontrak, dokumen legal, path file kontrak.

Serializer harus memakai allow-list kolom. Jangan melakukan `select *`, `toArray()` atas model credential-bearing, atau mengembalikan URL storage private.

## Rancangan tool Fase 4 berikutnya (belum diimplementasikan)

1. `creative_list_requests(client_id, project_id, status, pagination)` — request intake, requester terotorisasi, prioritas, deadline.
2. `creative_get_brief(client_id, project_id)` — brief aman dengan content/copy gated.
3. `creative_get_production_status(client_id, project_id, period)` — state produksi, PIC, deadline, overdue, progress berdasarkan data aktual.
4. `creative_list_assets(client_id, project_id, pagination)` — metadata aset dan versi, tanpa path/binary.
5. `creative_get_approval_history(client_id, project_id, period)` — approval/revisi event jika sumber history tersedia; jika tidak, laporkan last-known only.
6. `creative_search(client_id, q, project_id, pagination)` — pencarian terotorisasi atas judul, status, metadata dan note yang diizinkan; query dibatasi panjang dan tidak dicatat mentah.
7. `creative_get_communication_summary(client_id, project_id, period)` — hanya setelah relasi meeting/CRM–client ditetapkan dan PII masking tersedia.

Semua tool: GET/read-only, `readOnlyHint=true`, `destructiveHint=false`, `openWorldHint=false`, OAuth identity, validasi id/periode/pagination, timeout, error envelope aman, dan audit event tanpa token atau isi sensitif.

## Pengujian desain yang wajib sebelum implementasi

- admin, division-wide, staf biasa, PIC, reviewer, division lain, dan client lain;
- akun terkunci/terhapus, scope OAuth salah, tanpa autentikasi;
- manipulasi `client_id`, `project_id`, `pic_id`, `division_id`, dan file/version id;
- dokumen private, versi lama, completion proof, copy/brief sensitif;
- approval terakhir versus history yang tidak tersedia;
- data kosong, deadline null, status tidak dikenal, timezone, dan periode invalid;
- seluruh route/tool hanya GET/read-only dan tidak memutasi tabel produksi;
- regresi tool Digital Marketing dan Account/Client yang telah ada.

## Coverage dan open questions

- Belum ada sumber kanonik creative request/brief terpisah.
- Belum ada permission produksi/kontrak yang eksplisit dan terdokumentasi.
- Belum ada relasi Meeting/CRM human → `produk_client` yang aman.
- Approval/revisi masih last-known fields pada `task`, bukan append-only history.
- Belum dilakukan pengujian runtime terhadap storage private dan koneksi Supabase.

**Keputusan Fase 4.1:** peta data, boundary akses, gap, dan rancangan tool sudah disusun. Implementasi ditunda ke tahap berikutnya.
