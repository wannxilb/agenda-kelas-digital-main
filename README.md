<div align="center">
  <div style="background: linear-gradient(to right, #4f46e5, #3b82f6); padding: 20px; border-radius: 15px; display: inline-block; margin-bottom: 20px;">
    <h1 style="color: white; margin: 0;">Agenda Kelas Digital</h1>
  </div>
  <p><strong>Sistem Informasi Manajemen Agenda dan Presensi Sekolah Terpadu</strong></p>
</div>

---

## Apa itu Agenda Kelas Digital?

**Agenda Kelas Digital** adalah solusi cerdas pengganti buku agenda kelas fisik yang selama ini digunakan di sekolah. Aplikasi berbasis web ini didesain khusus untuk mendigitalkan proses pencatatan kehadiran siswa dan jurnal mengajar guru secara **otomatis, terintegrasi, dan real-time**.

Dengan sistem ini, komunikasi dan pelaporan antar Guru, Wali Kelas, Admin, dan Wakil Kepala Sekolah menjadi lebih transparan, aman, dan efisien. Tidak ada lagi buku agenda yang hilang, rusak, atau sulit direkap!

---

## 7 Peran Pengguna & Fitur Masing-Masing

Sistem ini memiliki **7 role** yang masing-masing memiliki dashboard, menu, dan hak akses yang berbeda sesuai dengan tugas pokok dan fungsinya (tupoksi).

---

### 1. Super Admin

Super Admin memiliki akses penuh ke seluruh sistem dan melewati semua batasan (maintenance mode, dll).

| Fitur | Deskripsi |
|-------|-----------|
| **Manajemen Instansi** | CRUD lengkap untuk sekolah/institusi (nama, alamat, logo, favicon), termasuk soft delete & restore |
| **Manajemen Admin** | CRUD akun Admin/Kepala Sekolah di setiap institusi |
| **Pengaturan Sistem** | Konfigurasi global: nama aplikasi, timezone, bahasa, batas sesi login, mode maintenance |
| **Audit Logs** | Melihat seluruh jejak aktivitas (siapa yang mengubah/menghapus data, kapan, dari IP mana) |
| **Backup & Restore** | Backup database & file, restore dari backup sebelumnya |
| **Toggle Fitur** | Mengaktifkan/menonaktifkan fitur per instansi (jadwal, ruangan, impor data, ekspor PDF/Excel) |
| **Switch Periode** | Beralih antar tahun ajaran/semester untuk melihat data periode lama (arsip) |
| **Maintenance Mode** | Mengaktifkan mode maintenance (hanya Super Admin yang tetap bisa mengakses) |
| **Test Email** | Mengirim email uji coba untuk memverifikasi konfigurasi mail server |

---

### 2. Admin (Kepala Sekolah)

Admin adalah pengelola data utama sekolah. Mengelola data master yang menjadi fondasi seluruh aktivitas di sistem.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan statistik: jumlah siswa, guru, kelas, kehadiran hari ini, agenda terbaru |
| **Kelas** | CRUD kelas (nama, tingkat, wali kelas, kapasitas) dengan impor massal |
| **Siswa** | CRUD siswa, impor dari Excel/CSV, ekspor template, penghapusan massal, mutasi keluar, riwayat siswa |
| **Guru** | CRUD guru, impor dari Excel/CSV, ekspor template |
| **Mata Pelajaran** | CRUD mata pelajaran, penugasan guru ke mata pelajaran |
| **Jadwal Pelajaran** | CRUD jadwal per kelas per hari (jam ke-, mata pelajaran, guru, ruangan) |
| **Ruangan** | CRUD ruangan (jika fitur `rooms` aktif) |
| **Tahun Ajaran** | CRUD tahun ajaran & semester, menetapkan tahun ajaran aktif |
| **Kenaikan Kelas** | Preview dan eksekusi kenaikan kelas massal siswa ke tingkat berikutnya |
| **Rapor Akademik** | Pengelolaan data rapor akademik siswa |
| **Koreksi Presensi** | Meninjau dan menyetujui/menolak koreksi presensi harian dari siswa |
| **Monitoring Ruangan** | Memantau penggunaan ruangan di seluruh sekolah |
| **Rekap Izin Guru** | Laporan ringkas pengajuan izin/sakit/tugas luar seluruh guru (CSV/PDF) |
| **Laporan** | Rekap kehadiran siswa, rekap per kelas, rekap per siswa (PDF/Excel) |
| **Pengaturan Sekolah** | Konfigurasi jam operasional, batas waktu absensi, pengaturan presensi harian |
| **Profil** | Mengelola profil dan kata sandi sendiri |

---

### 3. Guru

Guru adalah pengguna inti yang melakukan pencatatan jurnal mengajar (agenda) dan presensi siswa di kelas yang diampunya.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan: jurnal hari ini, presensi tercatat, pengajuan izin aktif |
| **Jurnal Mengajar** | Input/edit jurnal harian: pilih kelas, mata pelajaran, alokasi jam, judul materi, deskripsi, lampiran. Dibatasi oleh jam operasional dan (opsional) lokasi geografis |
| **Arsip Jurnal** | Melihat riwayat seluruh jurnal mengajar yang sudah dibuat, bisa filter per kelas/tanggal |
| **Presensi Siswa** | Input presensi siswa per kelas per hari (Hadir, Terlambat, Izin, Sakit, Alpa). Bisa dilakukan dari daftar siswa atau dari jurnal yang sudah dibuat |
| **Laporan Presensi** | Rekap kehadiran siswa yang diajar (per kelas, per rentang tanggal), bisa di-export |
| **Nilai Tugas** | Input dan mengelola nilai tugas/ulangan siswa per kelas per mata pelajaran. Termasuk summary dan export |
| **Pengajuan Izin/Sakit/Tugas Luar** | Guru bisa mengajukan izin, sakit, atau tugas luar (multi-hari), melampirkan surat, lalu bisa menarik pengajuannya |
| **Export** | Export laporan kehadiran dan jurnal ke PDF/Excel |
| **Switch Periode** | Beralih ke tahun ajaran lama untuk melihat data arsip |
| **Profil** | Mengelola profil dan kata sandi sendiri |

> **Catatan Multi-Role:** Guru yang juga memiliki role **Wali Kelas** akan melihat link "Portal Wali Kelas" di dropdown profil. Guru yang juga **Wakasek** akan melihat link "Portal Wakasek". Keduanya tetap bisa mengakses seluruh fitur guru.

---

### 4. Wali Kelas

Wali Kelas memiliki akses monitoring yang lebih luas terhadap kelas yang diampunya. Bisa melihat data semua mata pelajaran di kelasnya (bukan hanya yang diajar sendiri).

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Dashboard kelas: grafik kehadiran mingguan, agenda terkini, jumlah siswa, rekap hari ini |
| **Presensi Siswa (Monitoring)** | Memantau presensi seluruh siswa di kelas yang diampunya, semua mata pelajaran |
| **Kehadiran Harian** | Melihat rekap kehadiran harian per siswa, termasuk check-in/check-out |
| **Verifikasi Presensi** | Memverifikasi presensi siswa, membantu siswa yang belum check-out, membatalkan dispensasi |
| **Koreksi Presensi** | Meninjau dan menyetujui/menolak pengajuan koreksi presensi dari siswa |
| **Pulang Cepat** | Meninjau pengajuan pulang cepat (early leave) dari siswa, menyetujui/menolak secara batch |
| **Kirim Ulang WhatsApp** | Retry pengiriman notifikasi WhatsApp ke ortu/wali siswa |
| **Agenda (Read-Only)** | Melihat jurnal mengajar semua guru di kelasnya, termasuk arsip |
| **Nilai Siswa** | Melihat rekap nilai seluruh siswa di kelasnya dari semua mata pelajaran |
| **Laporan Bulanan** | Rekap kehadiran siswa bulanan per kelas |
| **Rekap Semester** | Export rekap kehadiran semester ke PDF |
| **Switch Periode** | Beralih ke tahun ajaran lama untuk melihat data arsip |
| **Profil** | Mengelola profil dan kata sandi sendiri |

> **Catatan Multi-Role:** Wali Kelas yang juga **Guru** bisa mengakses portal guru (input jurnal & presensi). Wali Kelas yang juga **Wakasek** bisa mengakses portal wakasek.

---

### 5. Wakasek (Wakil Kepala Sekolah Bidang Kurikulum)

Wakasek berperan sebagai pengawas dan penyetuju. Memantau kinerja mengajar guru, menyetujui/menolak pengajuan izin, dan mengelola laporan kurikulum.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan: jumlah guru aktif, izin pending, progres kurikulum, kehadiran guru hari ini |
| **Monitoring Kurikulum** | Memantau progres kurikulum: seberapa banyak guru yang sudah mengisi jurnal sesuai jadwal |
| **Monitoring Agenda** | Melihat seluruh jurnal mengajar semua guru di semua kelas, dengan status lengkap/kurang |
| **Izin / Sakit / Tugas Luar** | Menyetujui atau menolak pengajuan izin/sakit/tugas luar guru. Bisa melampirkan catatan, melihat surat, dan melihat histori |
| **Kinerja Mengajar** | Monitoring kinerja mengajar: rekap jumlah jurnal, rata-rata alokasi jam, jurnal terlambat per guru |
| **Evaluasi Akademik** | Evaluasi akademik berdasarkan data kehadiran siswa dan jurnal mengajar guru |
| **Monitoring Kehadiran** | Memantau kehadiran siswa harian di seluruh sekolah (bisa filter per kelas) |
| **Laporan Presensi** | Rekap kehadiran siswa dan guru (per kelas, per bulan), bisa di-export |
| **Export Data** | Export laporan mengajar, kehadiran guru, dan kehadiran siswa ke PDF/Excel |
| **Switch Periode** | Beralih ke tahun ajaran lama untuk melihat data arsip |
| **Profil** | Mengelola profil dan kata sandi sendiri |

> **Catatan:** Wakasek menggunakan **periode akademik sendiri** (tidak terpengaruh switch periode global seperti guru). Ini karena Wakasek perlu melihat data secara konsisten dari perspektif kurikulum.

---

### 6. Sekretaris Kelas

Sekretaris Kelas adalah siswa yang ditunjuk untuk membantu pencatatan. Memiliki kemampuan serupa guru dalam mengisi agenda dan presensi kelasnya.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan agenda dan presensi kelas yang ditugaskan |
| **Jurnal Mengajar** | Input/edit jurnal harian untuk kelas yang ditugaskan (sama seperti guru). Dibatasi jam operasional |
| **Arsip Jurnal** | Melihat riwayat jurnal yang sudah dibuat |
| **Presensi Siswa** | Input presensi siswa per kelas per hari |
| **Kehadiran Harian** | Melihat rekap kehadiran harian siswa |
| **Laporan** | Rekap kehadiran siswa, bisa di-print/export |
| **Cetak Agenda** | Mencetak jurnal mengajar dalam format cetak |
| **Profil** | Mengelola profil dan kata sandi sendiri |

> **Catatan:** Fitur Sekretaris hanya bisa diakses jika fitur `agenda_harian` dan `attendance` aktif di pengaturan instansi.

---

### 7. Siswa

Siswa dapat melihat jadwal, mengisi presensi harian (check-in/check-out), melihat jurnal mengajar, dan melihat nilai tugas.

| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard** | Ringkasan: jadwal hari ini, status presensi, nilai terbaru |
| **Agenda** | Melihat jurnal mengajar guru untuk kelasnya (read-only) per hari |
| **Jadwal Pelajaran** | Melihat jadwal pelajaran mingguan, bisa pilih tanggal tertentu atau geser minggu |
| **Presensi Harian (Check-in/Check-out)** | Check-in saat masuk sekolah dan check-out saat pulang. Terbatas di jam operasional. Bisa melampirkan foto |
| **Koreksi Presensi** | Mengajukan koreksi presensi (misalnya: hadir tapi tercatat alpa) beserta bukti |
| **Pulang Cepat** | Mengajukan izin pulang lebih awal, bisa update/batalkan pengajuan |
| **Nilai Tugas** | Melihat nilai tugas/ulangan dari semua mata pelajaran |
| **Profil** | Mengelola profil dan kata sandi sendiri |

> **Catatan Multi-Role:** Siswa yang juga **Sekretaris** akan melihat link "Portal Sekretaris" di dropdown profil untuk mengakses fitur input agenda & presensi.

---

## Matriks Ringkas Hak Akses

| Fitur | Super Admin | Admin | Guru | Wali Kelas | Wakasek | Sekretaris | Siswa |
|-------|:-----------:|:-----:|:----:|:----------:|:-------:|:----------:|:-----:|
| Manajemen Instansi | Ya | - | - | - | - | - | - |
| Manajemen Admin | Ya | - | - | - | - | - | - |
| Pengaturan Sistem & Backup | Ya | - | - | - | - | - | - |
| Audit Logs | Ya | - | - | - | - | - | - |
| Manajemen Data Master (Kelas, Siswa, Guru, Mata Pelajaran) | Ya | Ya | - | - | - | - | - |
| Manajemen Jadwal & Ruangan | Ya | Ya | - | - | - | - | - |
| Kenaikan Kelas & Rapor | - | Ya | - | - | - | - | - |
| Input/Edit Jurnal Mengajar | Ya | - | Ya | - | - | Ya | - |
| Input Presensi Siswa | Ya | - | Ya | - | - | Ya | - |
| Submit Pengajuan Izin/Sakit | - | - | Ya | - | - | - | - |
| Monitoring Presensi Kelas | - | Ya | - | Ya | Ya | - | - |
| Verifikasi/Koreksi Presensi | - | Ya | - | Ya | - | - | - |
| Lihat Semua Agenda (Monitoring) | Ya | Ya | - | Ya | Ya | - | - |
| Setujui/Tolak Izin Guru | - | - | - | - | Ya | - | - |
| Monitoring Kurikulum & Kinerja Guru | - | - | - | - | Ya | - | - |
| Evaluasi Akademik | - | - | - | - | Ya | - | - |
| Lihat Nilai Siswa (per Kelas) | - | - | - | Ya | - | - | - |
| Input/Export Nilai | Ya | - | Ya | - | - | - | - |
| Lihat Nilai Sendiri | - | - | - | - | - | - | Ya |
| Check-in/Check-out Harian | - | - | - | - | - | - | Ya |
| Lihat Jadwal Pelajaran | - | - | - | - | - | - | Ya |
| Export Laporan (PDF/Excel) | Ya | Ya | Ya | Ya | Ya | - | - |
| Print/Cetak Laporan | Ya | - | Ya | - | - | Ya | - |
| Switch Periode (Arsip) | Ya | - | Ya | Ya | Ya | Ya | - |

---

## Fitur Lainnya yang Mendukung

### Jam Operasional
Sistem membatasi waktu input data (jurnal & presensi) sesuai jam operasional sekolah yang diatur oleh Admin. Di luar jam tersebut, guru/sekretaris tidak bisa mengisi agenda atau presensi baru.

### Notifikasi WhatsApp
Sistem dapat mengirim notifikasi kehadiran siswa ke orang tua/wali melalui WhatsApp (jika dikonfigurasi). Termasuk notifikasi check-in, check-out, dan keterlambatan.

### Lokasi Geografis (Opsional)
Guru dapat dibatasi untuk mengisi jurnal hanya dari lokasi sekolah (geofencing). Dapat diaktifkan/nonaktifkan oleh Admin.

### Multi-Role
Satu pengguna bisa memiliki lebih dari satu role. Misalnya:
- **Guru + Wali Kelas**: Guru yang juga menjadi wali kelas, bisa mengakses portal guru dan portal wali kelas
- **Guru + Wakasek**: Guru yang juga menjabat sebagai wakasek, bisa mengakses portal guru dan portal wakasek
- **Siswa + Sekretaris**: Siswa yang ditunjuk sebagai sekretaris kelas

Role dengan prioritas tertinggi akan menjadi default redirect setelah login, tetapi pengguna bisa mengakses portal lain melalui dropdown profil.

### Fitur Guard (Sistem Fitur)
Setiap modul bisa diaktifkan/nonaktifkan per instansi melalui pengaturan Super Admin:
- `agenda_harian` -- Jurnal mengajar harian
- `attendance` -- Sistem presensi
- `schedule` -- Jadwal pelajaran
- `rooms` -- Manajemen ruangan
- `import_data` -- Impor data dari Excel
- `export_pdf` -- Export PDF
- `export_excel` -- Export Excel
- `nilai_tugas` -- Penilaian tugas

---

## Keunggulan Sistem

- **Paperless & Ramah Lingkungan**: Mengurangi penggunaan kertas secara signifikan
- **Data Aman & Historis Terjaga**: Fitur kenaikan kelas menjaga riwayat akademik tanpa menghapus data lama
- **Rekap Otomatis**: Generate laporan kehadiran dan jurnal mengajar dalam hitungan detik
- **Pembatasan Waktu Disiplin**: Sistem blokir otomatis di luar jam operasional sekolah
- **Antarmuka Modern & Cepat**: Responsive, ringan, dan mudah dipahami
- **Audit Trail**: Seluruh aktivitas krusial dicatat untuk transparansi dan pertanggungjawaban
- **Soft Deletes**: Data yang terhapus masih bisa dikembalikan

---

## Standar Keamanan

1. **Enkripsi Kata Sandi (Bcrypt)** -- Kata sandi tidak pernah disimpan dalam bentuk teks asli
2. **Proteksi Anti-Hacking** -- Terlindungi dari SQL Injection, CSRF, dan serangan siber umum lainnya
3. **Validasi Data Ketat** -- Sistem menolak input tidak logis (agenda kelas kosong, input di luar jam operasional)
4. **Audit Trail** -- Seluruh aktivitas krusial dicatat oleh Spatie Activity Log

---

## Kekurangan & Ruang Pengembangan

- **Ketergantungan Internet & Perangkat**: Membutuhkan koneksi internet stabil serta perangkat bagi guru/siswa
- **Belum Native Mobile**: Saat ini masih berbasis Web yang dioptimalkan untuk mobile (responsif)
- **Integrasi Pihak Ketiga**: Belum terintegrasi langsung dengan Dapodik atau mesin fingerprint

---

## Teknologi yang Digunakan

### Backend
- PHP 8.2+
- Laravel 12
- SQLite (development/testing) / PostgreSQL (production)

### Frontend
- Blade Templating
- Tailwind CSS v4
- Alpine.js
- Vite
- Chart.js
- Tom Select

### Paket Utama
- **Spatie Permission** -- Role & Permission management
- **Spatie Activity Log** -- Audit trail
- **Spatie Backup** -- Database backup & restore
- **Laravel Excel** (Maatwebsite) -- Ekspor laporan ke Excel
- **Laravel DomPDF** -- Ekspor laporan ke PDF
- **Laravel Breeze** -- Autentikasi

---

## Panduan Instalasi

<details>
<summary><b>Klik di sini untuk melihat langkah instalasi lokal</b></summary>

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL / MariaDB

### Langkah

```bash
# 1. Clone repository
git clone https://github.com/username/agenda-kelas-digital.git
cd agenda-kelas-digital

# 2. Install dependencies PHP
composer install

# 3. Install dependencies JavaScript
npm install

# 4. Copy .env
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=agenda_kelas_digital
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Jalankan migrasi & seed
php artisan migrate --seed

# 8. Build asset frontend
npm run build

# 9. Jalankan server
php artisan serve
```

Aplikasi bisa diakses di `http://localhost:8000`

### Akun Default
- **Super Admin**: `superadmin@admin.com` / `password`
- **Admin**: `admin@admin.com` / `password`

</details>

---

## Panduan E2E Testing

<details>
<summary><b>Klik di sini untuk melihat langkah menjalankan E2E testing</b></summary>

### Prasyarat
- Node.js & NPM
- Google Chrome (sudah terinstall di sistem)
- PHP & Composer

### Langkah

```bash
# Install dependencies
npm install

# Install browser Playwright (hanya sekali)
npx playwright install chromium

# Jalankan seluruh E2E test (29 test)
npm run test:e2e

# Jalankan test tertentu
npx playwright test tests/e2e/period-switcher.spec.js
```

### Arsitektur Testing
- Database test terpisah (`database/e2e.sqlite`) -- data development AMAN
- Setiap run: `migrate:fresh` + `E2eDatabaseSeeder` -- data selalu fresh & deterministik
- Server test jalan di port 8010 -- tidak bentrok dengan `php artisan serve`

</details>

---

## License

Proprietary Software.
