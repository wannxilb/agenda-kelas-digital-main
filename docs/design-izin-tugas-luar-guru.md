# Desain Fitur: Izin / Tugas Luar Guru (versi 2.1)

Status: **Draft untuk review — belum ada kode.**
Tanggal: 2026-08-12 (revisi 2.1: penutupan gap hasil analisis kode)
Pemilik keputusan: RIDWAN (approver = **Wakasek**, notifikasi = **WhatsApp/Fonnte**)

> **Riwayat revisi**
> - **v2.1 (2026-08-12)** — Menutup gap yang ditemukan saat verifikasi terhadap kode:
>   1. Migrasi/backfill data lama ditambahkan (§5.4).
>   2. Query monitoring diubah untuk multi-hari + filter `approved` (§11.1) — bukan cuma formula.
>   3. Route download lampiran ditambahkan (§8) + urutan route diperingatkan.
>   4. Kebijakan auto-expire `pending` yang lewat tanggal (§7.8).
>   5. Unique index notifikasi anti-duplikat (§10.3).
>   6. Semua keputusan terbuka §16 diresolusi dengan rekomendasi.
>   7. Koreksi rujukan `compressImage` (ada di `Sekretaris\AgendaController`, bukan Guru).

---

## 1. Ringkasan

Fitur "Izin / Tugas Luar" memungkinkan guru melaporkan ketidakhadiran mengajar
(izin / dinas luar), lalu **disetujui oleh Wakasek** sebelum dihitung sebagai
"slot terisi" di monitoring agenda. Laporan punya siklus hidup yang jelas
(`pending → approved | rejected | cancelled`) dan terhubung ke monitoring
Wakasek, notifikasi WhatsApp, serta rekap bulanan.

Dokumen ini menggantikan perilaku mentah pada versi sebelumnya yang hanya
mencatat "izin/tugas_luar" untuk hari ini tanpa approval, tanpa status,
tanpa pembatalan, dan tanpa notifikasi.

---

## 2. Masalah pada Alur Saat Ini

File terkait: `app/Http/Controllers/Guru/TeacherStatusController.php`,
`app/Models/TeacherStatus.php`, `database/migrations/2026_06_25_233000_create_teacher_statuses_table.php`.

| # | Masalah | Dampak |
|---|---------|--------|
| 1 | Tidak ada approval | Guru lapor sendiri → slot langsung "terisi" di monitoring (`DashboardController:71`, `monitoring/agenda.blade.php:345`). Tidak ada verifikasi. |
| 2 | `date_equals:today` | Tidak bisa merencanakan izin/tugas luar untuk hari ke depan. |
| 3 | Tidak ada status lifecycle | Record tidak punya status `pending/approved/rejected/cancelled`. |
| 4 | `status` dipakai untuk tipe (izin/tugas_luar) | Tidak ada ruang untuk status workflow. |
| 5 | Tidak bisa membatalkan | Guru hanya bisa hapus; tidak ada alur tarik/`cancelled`. |
| 6 | Tidak ada validasi jadwal | Guru tanpa jadwal di hari itu tetap bisa melapor. |
| 7 | Tidak ada pengganti mengajar | Kelas yang gurunya izin tidak diarahkan siapa penggantinya. |
| 8 | Tidak ada lampiran surat | Tidak ada bukti (surat izin/dinas). |
| 9 | Tidak ada notifikasi | Wakasek tidak tahu ada pengajuan; guru tidak tahu hasilnya. |
| 10 | Monitoring tidak membedakan izin vs tugas_luar | Keduanya jadi titik "amber". |

---

## 3. Tujuan

1. Guru bisa mengajukan izin / tugas luar untuk **hari ini atau masa depan**
   (tidak boleh masa lalu), lengkap dengan alasan & lampiran.
2. Wakasek menyetujui / menolak pengajuan. Hasilnya masuk ke monitoring
   **hanya jika APPROVED**.
3. Ada alur pembatalan: guru tarik saat `pending`, wakasek batalkan saat `approved`.
4. Notifikasi WhatsApp (Fonnte) ke wakasek (saat pengajuan) dan ke guru (hasil).
5. Monitoring Wakasek membedakan izin vs tugas_luar dan menampilkan pengganti.
6. Rekap bulanan izin/tugas luar per guru (approved) untuk wakasek/admin.
7. Pengajuan `pending` yang tidak diproses hingga tanggal lewat **otomatis
   kadaluarsa** (tidak menggantung, tidak memblokir guru).

---

## 4. Peran & Izin Akses

| Peran | Kemampuan | Cara otorisasi |
|-------|-----------|----------------|
| **Guru** | Ajukan, lihat & tarik pengajuannya sendiri (hanya `pending`) | route group `role:guru` + cek `teacher_id === Auth::id()` |
| **Wakasek** | Approve / reject `pending`, batalkan `approved`, lihat semua, dashboard badge | route group `role:wakasek\|super_admin` |
| **Admin** | Lihat semua + rekap bulanan (opsional batalkan) | route group admin |

Super admin dapat bertindak sebagai wakasek (pola yang sudah ada di
`routes/web.php:232` `role:wakasek|super_admin`).

**Keputusan (rekomendasi):** route guru baru ini TIDAK di-gate middleware
`feature:*` — mengikuti perilaku route `teacher-status` saat ini yang juga
tidak di-gate. Jika nanti ada kebijakan per-institusi, bungkus dalam group
`feature:izin_guru` (fase lanjutan).

---

## 5. Model Data

### 5.1 Kolom baru di `teacher_statuses`

Tabel tetap `teacher_statuses`. Perubahan migrasi baru (bukan edit migrasi lama):

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `type` | string | Nilai **lama** `status` dipindah ke sini: `izin` \| `tugas_luar` |
| `status` | string | Nilai **baru** (workflow): `pending` \| `approved` \| `rejected` \| `cancelled`, default `pending` |
| `date` | date | Tanggal mulai (tetap ada) |
| `date_end` | date nullable | Tanggal selesai; `null` = satu hari (`date` saja) |
| `start_time` | time nullable | Jam mulai (opsional, untuk paruh hari) |
| `end_time` | time nullable | Jam selesai (opsional, wajib jika `start_time` diisi) |
| `attachment` | string nullable | Path file surat izin/dinas (disimpan di `storage/app/public/teacher-statuses`) |
| `rejection_reason` | text nullable | Wajib diisi saat `rejected` |
| `approver_id` | FK users nullable | Wakasek yang memproses |
| `processed_at` | timestamp nullable | Waktu approve/reject **terakhir** (nama lebih netral dari `approved_at` karena juga terisi saat reject) |
| `cancelled_at` | timestamp nullable | Waktu pembatalan / auto-expire |
| `cancelled_by` | FK users nullable | Guru / wakasek yang membatalkan; `null` = auto-expire sistem |
| `substitute_teacher_id` | FK users nullable | Guru pengganti (opsional, saat approve) |
| `cancellation_reason` | text nullable | Alasan wakasek membatalkan `approved`, atau alasan auto-expire |

Kolom yang tetap: `teacher_id`, `note`, `academic_year_id`, `institution_id`
(dua terakhir via trait `HasAcademicYear` + `BelongsToInstitution`).

### 5.2 Aturan constraint

- Index: `(teacher_id, status)`, `(date)`, `(date_end)` untuk query monitoring/rekap.
- **Tidak** pakai unique constraint DB untuk `(teacher_id, date)` karena rentang
  tanggal bisa tumpang tindih; cek duplikat/overlap **di level aplikasi**
  (lihat §7.4).

### 5.3 Model `TeacherStatus`

- `$fillable` ditambah kolom di atas.
- `$casts`: `date`, `date_end` → date.
- Relasi baru: `approver()` (User), `substituteTeacher()` (User).
- Helper:
  - `isPending()`, `isApproved()`, `isRejected()`, `isCancelled()`
  - `typeLabel()` → "Izin" / "Tugas Luar"
  - `scopeActive()` → `status = approved`
  - `scopePending()` → `status = pending`
  - `scopeApproved()` → alias `scopeActive()`
  - `effectiveEndDate()` → `date_end ?? date` (pola sama dengan `StudentEarlyLeaveRequest`)
  - `coversDate($tanggal)` → `date <= tanggal <= effectiveEndDate()` (pola sama dengan `StudentEarlyLeaveRequest`)
  - `isOverlapping($teacherId, $start, $end)` (static) — cek `pending`/`approved` saja

### 5.4 Migrasi data lama (WAJIB — gap v2.0)

Record lama di DB punya `status = 'izin' | 'tugas_luar'` dan saat ini sudah
dihitung sebagai slot "terisi" di monitoring. Supaya perilaku itu tidak hilang:

```sql
-- 1. Tambah kolom baru (type, dst.) via Schema::table
-- 2. Salin nilai lama status ke type
UPDATE teacher_statuses SET type = status WHERE status IN ('izin', 'tugas_luar');
-- 3. Anggap record lama sebagai sudah disetujui (menjaga kontinuitas monitoring)
UPDATE teacher_statuses SET status = 'approved' WHERE status IN ('izin', 'tugas_luar');
```

- **Record lama → `approved`** (bukan `pending`) supaya monitoring tidak
  kehilangan data dan wakasek tidak dibanjiri pengajuan historis.
- `processed_at` record lama diisi `now()` (tidak wajib presisi).
- Migration ini harus punya test (lihat §14.2).

---

## 6. State Machine

```
                 ┌─────────── withdraw (guru, hanya oleh pemilik) ──▶ CANCELLED
                 │
   CREATE        ▼
  ────────▶  [PENDING] ─── approve (wakasek) ──▶ [APPROVED]
                 │  │                               │
                 │  └── reject (wakasek, wajib alasan) │
                 │        └────▶ [REJECTED]           └── cancel (wakasek, wajib alasan) ──▶ [CANCELLED]
                 │
                 └── expire (SISTEM, scheduler otomatis) ───────────────────────────────────▶ [CANCELLED]
```

Aturan transisi:

| Aksi | Dari | Ke | Pelaku | Syarat |
|------|------|----|--------|--------|
| `store` | - | pending | guru | validasi §7 |
| `withdraw` | pending | cancelled | guru pemilik | tanggal belum lewat |
| `approve` | pending | approved | wakasek | - |
| `reject` | pending | rejected | wakasek | `rejection_reason` wajib |
| `cancel` | approved | cancelled | wakasek | `cancellation_reason` wajib |
| `expire` | pending | cancelled | **sistem (scheduler)** | `effectiveEndDate()` < hari ini; `cancelled_by = null`, `cancellation_reason = "Otomatis: pengajuan melewati tanggal"` |

- Transisi lain → `abort(422)`/flash error "Status pengajuan tidak dapat diubah".
- Setelah `rejected`/`cancelled`, guru boleh **mengajukan ulang** untuk rentang
  yang sama (record lama tetap tersimpan sebagai riwayat).
- Record `approved` tetap tersimpan setelah tanggal lewat (historis).

---

## 7. Aturan Bisnis & Validasi

### 7.1 Tanggal
- `date` **required**, **`>= hari ini`** (tidak boleh masa lalu).
- `date_end` opsional; jika diisi harus `>= date`.
- Nilai lama `date_equals:today` dihapus.

### 7.2 Tipe
- `type` wajib: `izin` atau `tugas_luar`.

### 7.3 Waktu paruh hari (opsional)
- `start_time` tanpa `end_time` → tolak validasi.
- `end_time` tanpa `start_time` → tolak validasi.
- `end_time > start_time`.
- **Keputusan (rekomendasi):** cek overlap TETAP day-level (lihat §7.4) — dua
  record di tanggal sama dengan jam tidak bentrok TETAP ditolak pada v2.
  Time-aware overlap dicatat sebagai penyempurnaan fase berikutnya (tidak
  mengubah struktur data).

### 7.4 Cek duplikat / overlap
- Tolak jika guru sudah punya record **pending/approved** dengan rentang yang
  tumpang tindih:
  `date <= existing.effectiveEndDate()` DAN `request.effectiveEndDate() >= existing.date`.
- Record `rejected`/`cancelled` tidak menghalangi.
- Gunakan helper `effectiveEndDate()` (menangani `date_end` null) agar formula
  tidak ambigu.

### 7.5 Jadwal
- **Opsional (default): peringatan, bukan blokir** — jika guru tidak punya
  jadwal di rentang tanggal tersebut, muncul konfirmasi "Guru tidak memiliki
  jadwal pada tanggal ini, tetap ajukan?" (tidak memblokir).
- Pengganti (jika diisi saat approve) harus `role:teacher`, institusi sama,
  bukan guru yang bersangkutan.

### 7.6 Lampiran
- `mimes:pdf,jpg,jpeg,png`, `max:2048` KB — **sengaja lebih ketat** dari
  lampiran agenda (`pdf,doc,docx,jpg,png,xlsx`) karena surat izin/dinas
  umumnya PDF/gambar.
- Kompresi gambar JPG/PNG: **koreksi rujukan** — pola `compressImage` ada di
  `app/Http/Controllers/Sekretaris/AgendaController.php:509` (bukan
  `Guru\AgendaController`). **Rekomendasi:** ekstrak ke helper bersama
  `app/Support/ImageCompressor.php` agar bisa dipakai guru + wakasek tanpa
  duplikasi.

### 7.7 Duplikasi global per institusi
- Semua query `TeacherStatus` otomatis terfilter `institution_id` + tahun aktif
  via trait (perilaku yang sudah ada, dipertahankan).

### 7.8 Kadaluarsa otomatis pending (BARU — gap v2.0)
- Command baru `php artisan teacher-status:expire-pending`:
  - Kriteria: `status = pending` DAN `effectiveEndDate() < hari ini`.
  - Aksi: `status = cancelled`, `cancelled_by = null`,
    `cancellation_reason = "Otomatis: pengajuan melewati tanggal"`,
    `cancelled_at = now()`.
- Dijadwalkan harian di `routes/console.php` (jam 06:00, `withoutOverlapping`,
  pola yang sama dengan jadwal backup yang sudah ada).
- Manfaat: (1) tidak ada record pending menggantung, (2) guru bisa langsung
  mengajukan ulang rentang yang sama (record yang sudah cancelled tidak
  menghalangi overlap), (3) wakasek tidak perlu menolak manual.
- Pengingat: auto-expire juga boleh mengirim notifikasi ke guru (opsional,
  fase lanjutan — hindari notif malam hari).

---

## 8. Rute (Baru)

Semua di `routes/web.php`. Nama rute lama yang dipakai test
(`SearchCaseInsensitiveTest`) tetap dipertahankan untuk `index` guru.

```
# Guru (role:guru) — prefix /guru, name guru.teacher-status.*
GET    /guru/teacher-status                              → index        (list milik sendiri)
GET    /guru/teacher-status/create                       → create
POST   /guru/teacher-status                              → store        (→ pending)
GET    /guru/teacher-status/{teacherStatus}              → show         (detail + status)
PUT    /guru/teacher-status/{teacherStatus}              → update       (hanya pending, milik sendiri)
POST   /guru/teacher-status/{teacherStatus}/withdraw     → withdraw     (pending → cancelled)
GET    /guru/teacher-status/{teacherStatus}/attachment   → attachment   (download surat, hanya pemilik)

# Wakasek (role:wakasek|super_admin) — prefix /wakasek, name wakasek.teacher-status.*
GET    /wakasek/teacher-status                           → index        (semua, filter status/date)
GET    /wakasek/teacher-status/{teacherStatus}           → show         (detail + aksi)
POST   /wakasek/teacher-status/{teacherStatus}/approve   → approve
POST   /wakasek/teacher-status/{teacherStatus}/reject    → reject       (body: rejection_reason)
POST   /wakasek/teacher-status/{teacherStatus}/cancel    → cancel       (approved → cancelled)
GET    /wakasek/teacher-status/{teacherStatus}/attachment → attachment  (download surat)

# Report (wakasek & admin)
GET    /wakasek/teacher-status/report                    → report bulanan
GET    /admin/teacher-status/report                      → report bulanan (opsional)
```

⚠️ **PENTING — urutan route:** `GET .../report` dan `GET .../attachment`
harus didefinisikan **SEBELUM** route `{teacherStatus}`, karena route model
binding akan memperlakukan string `"report"` / `"attachment"` sebagai ID
record (error 404). Pola yang sama sudah diterapkan di `guru/agenda` dkk.

Controller baru: `app/Http/Controllers/Wakasek/TeacherStatusController.php`.
Controller guru di-refactor (pisahkan logika workflow ke service).

### 8.1 Service layer (disarankan, bukan opsional lagi)
`app/Services/TeacherStatusService.php`:
- `create(...)`, `update(...)`, `withdraw(...)`, `approve(...)`, `reject(...)`, `cancel(...)`
- Satu tempat untuk validasi transisi + trigger notifikasi → mudah di-test.
- Semua transisi state memakai **update atomik kondisional**
  (`where('id', x)->where('status', 'pending')->update(...)` dan cek
  `updated` count) untuk mencegah race condition dua wakasek approve
  bersamaan.

---

## 9. Perubahan UI

### 9.1 Guru — form ajukan (`guru/teacher-status/create.blade.php`)
- Input tanggal (min = hari ini).
- Toggle "Banyak hari" → muncul `date_end`.
- Opsional "Jam" (mulai–selesai).
- Kartu tipe (izin/tugas_luar) tetap seperti sekarang.
- Upload lampiran surat.
- Setelah submit → kembali ke index dengan badge status `Pending`.

### 9.2 Guru — daftar (`guru/teacher-status/index.blade.php`)
- Badge status: `Pending` (amber), `Disetujui` (hijau), `Ditolak` (merah), `Dibatalkan` (abu).
- Aksi (keputusan: **tidak ada hapus permanen** — semua lewat state machine):
  - `pending` & belum lewat → **Edit**, **Tarik Pengajuan**
  - lainnya → read-only.
- Search/filter tetap + filter status baru.

### 9.3 Wakasek — halaman baru `wakasek/teacher-status/index.blade.php`
- Tabs: **Menunggu Persetujuan** (default) / **Disetujui** / **Semua**.
- Card pengajuan: guru, tipe, rentang tanggal, jam, alasan, lampiran.
- Tombol **Setujui** / **Tolak** (modal isi alasan) / **Batalkan** (modal alasan).
- Status rekap: jumlah pending hari ini.

### 9.4 Dashboard Wakasek
- Badge "X pengajuan menunggu" → link ke halaman approval.
- Baris monitoring: slot dengan izin **approved** → titik **amber** ("Izin") /
  **biru** ("Tugas Luar"), nama guru diganti dengan nama pengganti bila ada.

### 9.5 Monitoring agenda (`wakasek/monitoring/agenda.blade.php`)
- Ubah `isset($teacherStatuses[$teacher_id])` menjadi **hanya status `approved`**
  (sebenarnya sudah ter-filter di level query — lihat §11.1).
- Tampilkan `type` (izin/tugas_luar) + nama pengganti pada tooltip/badge slot.

---

## 10. Notifikasi WhatsApp (Fonnte)

Mengikuti pola yang sudah ada: `DailyAttendanceWhatsappNotifier` +
`WhatsappNotificationLog` + job antrian + `FonnteWhatsappClient`.

### 10.1 Konfigurasi
- Token/api diambil dari `DailyAttendanceSetting::forInstitution(...)`
  (`whatsapp_token`, `whatsapp_api_url`, `whatsapp_country_code`) — memakai
  setting yang sudah ada, tanpa tabel setting baru.
- ⚠️ **Catatan dependensi:** fitur ini bergantung pada toggle
  `whatsapp_enabled` di setting absensi (default `false`). Jika mati →
  notifikasi di-skip. **Rekomendasi:** tetap buat baris log dengan
  `status = 'skipped'` agar skip-nya terlihat/di-debug, lalu beri catatan
  di halaman setting bahwa toggle ini juga mengontrol notifikasi izin guru.

### 10.2 Event & pesan

| Event | Target | Template |
|-------|--------|----------|
| Pengajuan dibuat | Semua Wakasek (`users.phone`, role wakasek, institusi sama) | `{guru} mengajukan {izin/tugas luar} {rentang tanggal} {jam}. Alasan: {note}. Mohon review di aplikasi.` |
| Disetujui | Guru | `Pengajuan {izin/tugas luar} tanggal {rentang} telah DISETUJUI.` |
| Ditolak | Guru | `Pengajuan {izin/tugas luar} tanggal {rentang} DITOLAK. Alasan: {rejection_reason}.` |
| Dibatalkan (wakasek) | Guru | `Pengajuan {izin/tugas luar} tanggal {rentang} DIBATALKAN. Alasan: {cancellation_reason}.` |

**Keputusan (rekomendasi):** kirim ke **SEMUA** wakasek institusi (bukan satu),
agar tidak ada pengajuan yang terlewat; dedupe dijamin oleh unique index §10.3.

### 10.3 Implementasi
- Log masuk ke `whatsapp_notification_logs`; kolom tambahan
  `teacher_status_id` nullable, `event_type` = `teacher_status_*`.
- 🔒 **Anti-duplikat (BARU):** tambahkan unique index
  `(teacher_status_id, event_type, recipient_phone)` — kolom
  `student_daily_attendance_id` yang punya unique index lama akan NULL di
  sini, sehingga tanpa index baru job retry (`tries: 3`) bisa mengirim pesan
  ganda. `recipient_phone` IKUT disertakan karena satu pengajuan dikirim ke
  SEMUA wakasek (keputusan §16.8): beberapa log sah berbagi
  `(teacher_status_id, event_type)` yang sama selama penerimanya berbeda —
  index tanpa `recipient_phone` akan menolak log wakasek ke-2.
- Job baru: `SendTeacherStatusNotification` (copy pola
  `SendDailyAttendanceWhatsappNotification`).
- Pesan dikirim **async** via `dispatch()`.

---

## 11. Integrasi Monitoring (logika terisi)

### 11.1 Query data — WAJIB diubah, bukan hanya formula (gap v2.0)

`Wakasek\MonitoringController.php:24` dan `Wakasek\DashboardController.php:45`
saat ini memakai `whereDate('date', $date)->get()->keyBy('teacher_id')`.
Masalah: (a) izin multi-hari tidak ketemu di hari ke-2/3, (b) semua status ikut
termasuk `pending`, (c) `keyBy` menimpa kalau satu guru punya >1 record.

Ganti dengan:

```php
$teacherStatuses = TeacherStatus::query()
    ->where('status', 'approved')
    ->where('date', '<=', $tanggal)
    ->where(function ($q) use ($tanggal) {
        $q->whereNull('date_end')->orWhere('date_end', '>=', $tanggal);
    })
    ->get()
    ->keyBy('teacher_id');
```

- `keyBy('teacher_id')` kini aman: cek overlap §7.4 menjamin 1 guru maksimal
  1 record pending/approved pada rentang yang sama.

### 11.2 Formula slot

```
$isFilled = $hasAgenda
         || isset($teacherStatuses[$schedule->teacher_id]);
```

- `pending/rejected/cancelled` **tidak** ikut (sudah difilter di query).
- Titik slot: izin → amber, tugas_luar → biru; tooltip menampilkan pengganti bila ada.
- **Keputusan (rekomendasi): day-level** — izin paruh hari (`start_time`/
  `end_time`) tetap menandai SELURUH slot guru di hari itu sebagai terisi.
  Penilaian per-jam (time-aware) dicatat sebagai penyempurnaan fase berikutnya.

### 11.3 File yang diubah
- `Wakasek\MonitoringController.php` (query, baris 24)
- `Wakasek\DashboardController.php` (query baris 45 + formula baris 71)
- `wakasek/monitoring/agenda.blade.php` (baris 67, 293, 345, 457, 568)

---

## 12. Rekap / Laporan

- Halaman `wakasek/teacher-status/report`: parameter bulan + tahun.
- Grup: per guru → jumlah `izin` (approved) dan `tugas_luar` (approved) + total.
- Kolom: Nama guru, total hari izin, total hari tugas luar, total.
- **Definisi "total hari" (BARU):** `diffInDays(date, date_end) + 1` per record
  approved (record multi-hari dihitung sesuai jumlah hari, bukan 1).
- Ekspor CSV/PDF mengikuti pola `wakasek/export` yang sudah ada (opsional fase 2).

---

## 13. Keamanan & Audit

- Semua aksi wakasek mencatat `approver_id` + `processed_at`.
- Guru hanya bisa edit/tarik miliknya sendiri (`teacher_id === Auth::id()`, selain → `403`).
- Transisi state invalid → flash error, bukan error 500.
- **Lampiran:** route download (§8) melayani file via controller
  (`Storage::disk('public')->download(...)` / `Response::file`) dengan guard
  pemilik/wakasek/admin — **bukan** expose langsung dari `public/storage`
  (file ditaruh di subfolder `teacher-statuses`; akses langsung ke
  `storage/app/public` bisa disambungkan ke route terproteksi saja).
- Otorisasi rute wakasek memakai `role:wakasek|super_admin` (pola existing).

---

## 14. Rencana Pengujian

### 14.1 Unit
- Validasi: tanggal masa lalu ditolak; `date_end < date` ditolak; jam parsial invalid; overlap terdeteksi; lampiran mime/size.
- State transition: tiap aksi hanya valid pada state yang benar.
- Helper: `effectiveEndDate()`, `coversDate()`, `isOverlapping()`.

### 14.2 Feature test (Pest/PHPUnit + RefreshDatabase)
| Test | Ekspektasi |
|------|-----------|
| Guru submit → record `pending` + notif log dibuat | 200/redirect + DB |
| Guru submit tanggal masa lalu | ditolak (validation) |
| Wakasek approve pending → `approved` + `approver_id` terisi | DB berubah |
| Wakasek reject tanpa alasan | ditolak (validation) |
| Guru tarik hanya saat `pending` | pending → cancelled; approved → 403/422 |
| Wakasek cancel hanya saat `approved` | berhasil; pending → ditolak |
| Monitoring hanya menghitung `approved` | class tidak "jamkos" saat izin approved |
| **Monitoring multi-hari (BARU)** | izin approved 08-10 s.d. 08-12 → ketemu saat monitoring 08-11 & 08-12 |
| **Monitoring mengabaikan pending (BARU)** | record pending tidak menandai slot terisi |
| **Expire otomatis (BARU)** | command mengubah pending tanggal lampau → cancelled |
| **Migrasi backfill (BARU)** | record lama `status=izin` → `type=izin`, `status=approved` |
| **Route attachment (BARU)** | pemilik/wakasek 200; guru lain 403 |
| **Dedupe notifikasi (BARU)** | unique `(teacher_status_id, event_type, recipient_phone)` menolak log ganda per penerima; 2 wakasek tetap dapat 1 log masing-masing |
| Akses silang (guru lain) | 403 |
| Update test lama `SearchCaseInsensitiveTest::test_teacher_status_search...` | sesuaikan: `status` → `pending`, `type` = `izin` |

### 14.3 E2E (Playwright, `tests/e2e/`)
- `guru-teacher-status.spec.js`: guru ajukan (pilih tipe, tanggal, lampiran) → badge Pending → tarik.
- `wakasek-teacher-status.spec.js`: wakasek approve/reject via modal → status berubah, muncul di monitoring.

---

## 15. Fase Implementasi

| Fase | Isi | Deliverable |
|------|-----|-------------|
| **1. Data** | Migrasi kolom baru + **backfill record lama (§5.4)** + unique index notifikasi | Tabel `teacher_statuses` v2 |
| **2. Service & Controller guru** | Refactor + validasi + withdraw + auto-expire command | Alur guru lengkap |
| **3. Controller & UI wakasek** | Approve/reject/cancel + halaman approval | Alur wakasek |
| **4. Notifikasi** | Job + log + unique index + template WhatsApp | Notif dua arah |
| **5. Monitoring & Dashboard** | Query multi-hari + rumus terisi + badge + tooltip | Integrasi |
| **6. Laporan** | Rekap bulanan (wakasek/admin) + definisi hari | Laporan |
| **7. Test** | Unit + feature + E2E | Coverage |

Urutan wajib: 1 → 2 → 3. Fase 4–7 bisa paralel setelah 3.

---

## 16. Keputusan Terbuka — SUDAH DIRESOLUSI (rekomendasi, bisa di-override)

| # | Pertanyaan (v2.0) | Keputusan v2.1 (rekomendasi) |
|---|-------------------|------------------------------|
| 1 | Validasi jadwal saat mengajukan | **Peringatan saja** (tidak memblokir) |
| 2 | Multi-hari | **Ya** — `date_end` didukung |
| 3 | Pengganti mengajar | **Opsional** saat approve |
| 4 | Wakasek batalkan `pending` | **Tidak** — guru tarik sendiri; wakasek cukup reject |
| 5 | Rekap ekspor CSV/PDF | **Fase 2** — halaman web dulu |
| 6 | Hapus permanen record | **Tidak** — semua lewat state `cancelled`; riwayat terjaga; route `destroy` dihapus dari §8 dan UI |
| 7 | Monitoring izin paruh hari | **Day-level** untuk v2; time-aware fase berikutnya |
| 8 | Notifikasi ke berapa wakasek | **Semua wakasek** institusi (dedupe via unique index) |
| 9 | Auto-expire pending yang lewat | **Ya** — scheduler harian (§7.8) |

Catatan kecil yang ikut dikoreksi di v2.1: rujukan `compressImage`
(`Sekretaris\AgendaController`, saran ekstrak helper), daftar mime lampiran
yang sengaja lebih ketat dari agenda, dan `approved_at` → `processed_at`.
