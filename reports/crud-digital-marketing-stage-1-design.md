# Tahap 1 — Desain dan Permission CRUD Digital Marketing

Status: desain saja. Belum ada tool CRUD final, route write, migrasi, atau perubahan operasi tulis.

## 1. Modul yang boleh ditulis

Write scope awal hanya untuk data operasional Digital Marketing yang sudah memiliki relasi client/project:

- market research: 4P, SWOT, segmentasi, target audience, positioning;
- branding: identity, value, tagline, tone/communication, composition, moodboard, alat branding;
- content plan/calendar: `data_jasa` dan metadata jadwal;
- campaign: metadata campaign yang tersimpan di ERP, bukan sinkronisasi platform eksternal;
- task/KPI produksi: assignment, status, deadline, priority, note kerja;
- approval/revisi: transition status dan event approval/revision;
- aset/dokumen marketing: metadata dan versi, dengan file pipeline terpisah;
- data client DM: hanya field profil non-sensitif yang memang diizinkan.

Di luar scope: OAuth connection, token/credential, invoice/payment, kontrak bernilai finansial, user/role/division, audit log, dan data CRM/meeting tanpa relasi client yang kanonik.

## 2. Allow-list field

### Boleh dibuat/diubah

- semua modul: `client_id`/`project_id` hanya sebagai selector yang diverifikasi server-side;
- market research/branding: field domain, platform, judul, deskripsi, note bisnis, dan relasi referensi yang tervalidasi;
- content plan: judul, inspirasi, type/strategy/pilar/hooks/body/CTA, durasi, platform, tanggal kerja, priority, PIC, note;
- task/KPI: nama, PIC dalam scope, priority, menit/point, tanggal mulai/deadline, note, status transition yang sah;
- approval/revisi: action, alasan, komentar terbatas, dan transition target;
- aset/dokumen: nama, label, division, status, pin, dan versi melalui server-generated metadata.

### Server-derived atau dilarang

`created_by`, `updated_by`, `approved_by`, ownership, division/client relation, timestamps keamanan, audit actor, status policy, storage path, binary path, `editor_content` mentah, completion-proof path, OAuth token/secret/assets/scopes, invoice/payment/contract value, email/telepon pribadi, dan field mass-assignment lain.

## 3. Permission dan role

Permission registry yang diusulkan:

```text
marketing.<module>.create
marketing.<module>.update
marketing.<module>.submit_approval
marketing.<module>.request_revision
marketing.<module>.archive
marketing.<module>.delete
marketing.<module>.restore
marketing.<module>.view_sensitive
```

| Role | Create/update | Approval/revisi | Archive | Delete/restore | Sensitive |
|---|---|---|---|---|---|
| Admin | semua module dalam scope sistem | ya | ya | ya, audited | permission khusus |
| DM Manager/BU Head | module pada client DM yang ditugaskan division | submit/approve sesuai SoD | ya | restore; delete perlu admin | tidak otomatis |
| Staf DM/creative | draft dan field operasional client assignment | submit/request revision | hanya bila permission eksplisit | tidak | tidak |
| PIC | task/project yang ditugaskan | submit; tidak approve sendiri | tidak | tidak | tidak |
| Reviewer/approver | objek client/project yang berwenang | approve/reject/request revision | tidak | tidak | sesuai permission |
| Division/client lain | tidak ada | tidak ada | tidak | tidak | tidak |

Role saja tidak cukup: policy wajib mengevaluasi akun aktif, action, module, object, client, project, division, assignment, current state, dan separation-of-duties.

## 4. Aturan client access

1. Identitas berasal dari OAuth/session server-side.
2. `MarketingAccessService`/policy memverifikasi akun tidak terkunci/terhapus.
3. Client harus termasuk `accessibleClientQuery(user)` dan memiliki assignment division aktif.
4. Project/task/asset harus memiliki relasi client yang sama; `client_id` parameter tidak memberi akses.
5. PIC filter tidak boleh memperluas scope; hanya menyaring objek yang sudah berwenang.
6. Semua alternate route, export, upload, version, archive, dan restore mengulang object-level check.
7. Policy error atau data division/assignment hilang harus fail closed.

## 5. Format konfirmasi operasi tulis

Operasi create/update biasa memerlukan request idempotency key. Archive/delete, approval, revision, dan perubahan sensitif memerlukan confirmation token sekali pakai:

```json
{
  "action": "archive|delete|submit_approval|request_revision",
  "module": "content_plan",
  "object_type": "data_jasa",
  "object_id": 123,
  "client_id": 45,
  "payload_hash": "sha256:...",
  "reason": "wajib untuk archive/delete/revision",
  "confirmation_token": "opaque-short-lived-token"
}
```

Token terikat pada user, OAuth client, object, action, payload hash, dan expiry singkat; single-use dan ditolak jika object version berubah. UI confirmation saja bukan kontrol keamanan.

## 6. Audit log

Audit append-only mencatat `request_id`, actor id, OAuth client id, action, module, object type/id, client/project/division, allow-listed before/after diff, reason, confirmation id, policy result, status, timestamp, dan minimal request metadata.

Tidak boleh dicatat: access token, secret, password, raw payload, binary/content dokumen, full PII, credential platform, atau private storage path. Penolakan authorization tetap dicatat dengan data minimum. Audit log tidak boleh diubah oleh pengguna biasa dan memerlukan retention/akses admin terpisah.

## 7. Archive, delete, dan restore

- Default: soft delete/archive dengan `archived_at`, `archived_by`, `archive_reason` atau tabel lifecycle terpisah.
- Hard delete dilarang untuk approval, audit, dokumen, versi, task history, dan data yang memiliki kewajiban retensi.
- Hard delete hanya untuk data draft yang tidak direferensikan, admin-only, confirmation ganda, dan audit lengkap.
- Restore hanya admin/manager dengan permission `restore`, validasi parent client/project masih aktif, dan audit event baru.
- Archive tidak boleh menghapus file atau memutus audit history secara fisik.

## 8. Validasi dan transaksi

- DTO allow-list, strict type/enum/date/size validation, foreign-key dan object-scope validation.
- Transaction untuk mutation lintas tabel.
- Optimistic locking/version check untuk update.
- Idempotency key untuk create/retry.
- State machine menolak transition ilegal, approval diri sendiri, dan update object archived tanpa restore.
- Error publik konsisten: `401`, `403`, `404`, `409`, `422`; tanpa stack trace atau detail SQL.

## 9. Kriteria persetujuan sebelum implementasi

- Permission registry dan role mapping disetujui.
- Mekanisme archive/restore serta kebijakan hard delete disetujui.
- Format confirmation token dan expiry disetujui.
- Audit schema, retention, dan akses audit disetujui.
- Policy test matrix admin/staf/PIC/reviewer/lintas client/division tersedia.

**Keputusan tahap:** desain permission CRUD telah ditetapkan sebagai baseline; belum ada tool final atau operasi tulis yang diaktifkan.
