# 🎭 E2E Testing dengan Playwright

Test end-to-end (E2E) untuk **Agenda Kelas Digital** — menguji alur nyata lewat
browser terhadap server Laravel yang berjalan.

## 🏗️ Arsitektur: Database Test Terpisah

Test **tidak menyentuh database development kamu**. Ada environment khusus:

- `.env.e2e` → `DB_CONNECTION=sqlite` → `database/e2e.sqlite`
- Server test jalan di **port 8010** (server dev kamu di 8000 tidak terganggu)
- Setiap kali server test dinyalakan, Playwright otomatis menjalankan:
  `php artisan migrate:fresh --env=e2e` + seed `E2eDatabaseSeeder`
  → data test **selalu fresh & deterministik**

```
npm run test:e2e
  └─ webServer: migrate:fresh → db:seed (E2eDatabaseSeeder) → php artisan serve :8010
```

### 🛡️ Pengamanan Berlapis (DB Dev Tidak Bisa Ke-wipe)

Ada **2 lapis pengamanan** supaya `migrate:fresh --env=e2e` tidak mungkin
menghapus database dev (`agenda_kelas`):

1. **webServer memaksa DB test** — command Playwright meng-`set` variabel
   `DB_CONNECTION=sqlite`, `DB_DATABASE=database/e2e.sqlite`, `APP_ENV=e2e`
   secara eksplisit sebelum menjalankan artisan. Ini penting karena Laravel
   Env repository bersifat **immutable**: kalau OS sudah punya variabel
   `DB_CONNECTION=pgsql` (mis. di-set tool lain), nilainya akan MENIMPA
   `.env.e2e` — bikin `migrate:fresh` nyasar ke DB dev. Dengan `set` eksplisit,
   nilai OS dikalahkan.

2. **DatabaseSafetyProvider** (`app/Providers/DatabaseSafetyProvider.php`) —
   guard di level aplikasi: perintah berbahaya (`migrate:fresh`, `db:wipe`,
   `db:seed`, `serve`, dll.) yang diminta dengan `--env=e2e` **hanya boleh
   jalan kalau koneksi yang ter-resolve benar-benar sqlite
   `database/e2e.sqlite`**. Kalau bukan — perintah dibatalkan dengan error
   yang jelas, sebelum menyentuh database apa pun. Jadi walau seseorang
   menjalankan `php artisan migrate:fresh --env=e2e` manual saat `.env.e2e`
   hilang/rusak, DB dev tetap aman.

## 📁 Struktur File

```
playwright.config.js              ← konfigurasi (port 8010, DB test, webServer)
.env.e2e(.example)                ← env khusus E2E (sqlite test)
database/seeders/E2eDatabaseSeeder.php  ← data test: users, kelas, mapel, jadwal
tests/e2e/
├── helpers.js                    ← akun test + fungsi login()/logout()
├── auth.spec.js                  ← test autentikasi (login, logout, password salah)
├── dashboard.spec.js             ← smoke test dashboard tiap role
├── role-access.spec.js           ← kontrol akses berbasis role (403, redirect)
├── guru-agenda.spec.js           ← alur isi jurnal mengajar guru → arsip
├── siswa-attendance.spec.js      ← alur presensi harian: check-in/check-out + status
├── setup-env.mjs                 ← script pembuat .env.e2e (sekali saja)
└── README.md                     ← file ini
```

## 🚀 Cara Menjalankan

```bash
# (Pertama kali saja) buat .env.e2e dari .env.e2e.example
npm run test:e2e:setup

# Jalankan semua test (otomatis migrate:fresh + seed + server di port 8010)
npm run test:e2e

# Browser terlihat
npm run test:e2e:headed

# Mode UI interaktif (cari selector, step-by-step)
npm run test:e2e:ui

# Lihat laporan HTML
npm run test:e2e:report

# Satu file / satu test saja
npx playwright test tests/e2e/guru-agenda.spec.js
npx playwright test -g "isi jurnal"
```

> **Server test otomatis dibuat & dihentikan sendiri oleh Playwright.**
> Kalau port 8010 sedang dipakai (server e2e lama masih nyala), matikan dulu
> atau ganti port: `PORT=8011 npm run test:e2e`.

## 👤 Akun Test (password semua: `password`)

Dibuat oleh `E2eDatabaseSeeder` di database test terpisah:

| Role | Email | Dashboard |
|------|-------|-----------|
| Super Admin | `superadmin@school.com` | `/super-admin/dashboard` |
| Admin | `admin@school.com` | `/admin/dashboard` |
| Guru (punya jadwal) | `e2e.guru@school.com` | `/guru/dashboard` |
| Wali Kelas | `e2e.walikelas@school.com` | `/wali-kelas/dashboard` |
| Sekretaris | `e2e.sekretaris@school.com` | `/sekretaris/dashboard` |
| Siswa | `e2e.siswa@school.com` | `/siswa/dashboard` |
| Wakasek | `e2e.wakasek@school.com` | `/wakasek/dashboard` |

Kredensial bisa di-override via env untuk CI:
```bash
E2E_TEACHER_EMAIL=guru.lain@school.com E2E_TEACHER_PASSWORD=rahasia npm run test:e2e
```

## 🧪 Yang Diuji

| File | Cakupan |
|------|---------|
| `auth.spec.js` | Redirect root → login, login 3 role, password salah, logout |
| `dashboard.spec.js` | 6 role login → dashboard sesuai role tampil |
| `role-access.spec.js` | Belum login → redirect; role salah → 403; role benar → 200 |
| `guru-agenda.spec.js` | Guru login → buka agenda → isi jurnal (tanggal, kelas, mapel, ruangan, judul, detail via TomSelect) → simpan → muncul di daftar & arsip |
| `siswa-attendance.spec.js` | Siswa login → absensi harian → check-in (foto kamera palsu) → status masuk tercatat → check-out → status pulang tercatat + riwayat terisi |
| `guru-teacher-status.spec.js` | Guru login → buat pengajuan izin multi-hari → badge Menunggu → edit → tarik → Dibatalkan |
| `wakasek-teacher-status.spec.js` | Guru buat 2 pengajuan → wakasek setujui via modal → tab Disetujui → tolak via modal (alasan) → Ditolak → monitoring tampil "Tugas Luar" |

Data uji agenda (disediakan seeder):
- Guru E2E punya jadwal **Senin–Jumat** 07:00–08:00 di kelas **X RPL E2E**,
  mapel **Pemrograman Web E2E**, ruang **Ruang E2E**.
- Test memakai tanggal **Senin depan** (selalu masa depan & selalu ada jadwal)
  supaya tidak kena batasan waktu jadwal (hanya berlaku untuk tanggal hari ini).
- Jam operasional di-override sampai 2099 → test bisa jalan kapan saja.

Data uji presensi harian (disediakan seeder):
- Setting absensi harian dibuka **24 jam** (00:00–23:59), foto & WhatsApp
  opsional → test bisa check-in/out kapan saja tanpa efek samping.
- Form absensi selalu butuh foto (logika JS). Test memakai **kamera palsu**
  Chrome (`--use-fake-device-for-media-stream` di `playwright.config.js`);
  ada fallback injeksi foto ke state Alpine kalau kamera tidak tersedia.

## 🖥️ Kenapa Pakai Chrome Sistem?

`npx playwright install chromium` gagal di jaringan tertentu (CDN Playwright
terblokir). Solusinya `channel: 'chrome'` → Playwright memakai **Google Chrome
yang sudah terinstall** di sistem, tanpa download browser.

## 🔧 Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `Executable doesn't exist` (ffmpeg) | Video sengaja dimatikan. Aktifkan dengan `npx playwright install ffmpeg` + `video: 'retain-on-failure'` di config. |
| Port 8010 sudah dipakai | Ada server e2e lama. Matikan, atau `PORT=8011 npm run test:e2e`. |
| `.env.e2e` tidak ada | **Otomatis dibuat** oleh webServer dari `.env.e2e.example` saat `npm run test:e2e`. (Opsional: `npm run test:e2e:setup`). |
| Halaman dashboard blank (`x-cloak`) | Pastikan asset ter-build: `npm run build` (server e2e memakai `public/build` yang sama). |
| Test login ke-lock | Di DB test rate limit dilonggarkan (20x). Kalau terlanjur: `npm run test:e2e` otomatis migrate:fresh (reset). |
| Test izin/tugas luar gagal validasi overlap | Spec guru & wakasek memakai tanggal di **minggu berbeda** (offset +14/+21 hari) supaya test paralel di DB test yang sama tidak saling menabrak validasi overlap. Jangan samakan tanggal antar spec. |
| Login timeout padahal dashboard sudah kebuka | Asset CDN (tom-select/font) lambat → event `load` tidak pernah selesai. Sudah diakali: `login()`/`logout()` pakai `waitUntil: 'domcontentloaded'` (tidak menunggu asset eksternal). |
| `grantPermissions: Invalid URL` | Origin harus dari `baseURL` config, bukan `new URL(page.url()).origin` (masih `about:blank` sebelum navigasi). Sudah pakai fixture `baseURL`. |
| `⛔ E2E SAFETY GUARD ... DIBATALKAN` | DatabaseSafetyProvider memblokir karena `--env=e2e` tidak resolve ke sqlite. Pastikan `.env.e2e` ada (otomatis dibuat) dan tidak ada env OS `DB_CONNECTION=...` yang menimpa (webServer sudah meng-`set` sendiri). |
| Ingin akses DB test manual | `php artisan tinker --env=e2e` |

## 💾 Backup Database Otomatis (Anti-Wipe)

Sebagai jaring pengaman terakhir, database dev di-backup otomatis **setiap
hari** memakai `spatie/laravel-backup` (sudah terpasang di `composer.json`):

- **Jadwal**: `backup:run --only-db` jam 23:00 + `backup:clean` jam 23:30
  (lihat `routes/console.php`).
- **Isi**: dump penuh PostgreSQL `agenda_kelas` (semua tabel + data), dikompres
  jadi zip.
- **Lokasi**: `storage/app/private/agenda-kelas-digital/<tanggal>.zip`
  (otomatis di-gitignore).
- **Retensi**: 7 hari penuh, lalu harian 16 hari, mingguan 8 minggu, bulanan
  4 bulan, tahunan 2 tahun (config/backup.php).

```bash
# Aktifkan scheduler (biarkan terminal terbuka)
php artisan schedule:work

# Atau: backup manual kapan saja
php artisan backup:run

# Lihat daftar backup
php artisan backup:list
```

> **Cara restore**: `php artisan backup:list` → buka zip terbaru di
> `storage/app/private/agenda-kelas-digital/` → restore `postgresql-agenda_kelas.sql`
> ke PostgreSQL (mis. via pgAdmin → Restore, atau `psql -f`).

## ✍️ Tips Nulis Test Baru

```js
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

test('guru bisa buka halaman jurnal', async ({ page }) => {
  await login(page, USERS.teacher);
  await page.goto('/guru/agenda');
  await expect(page).toHaveURL(/\/guru\/agenda/);
});
```

- Pakai selector stabil: `#id`, `[name="..."]`, `getByRole()`, `getByText()`.
- Interaksi **TomSelect** (dropdown custom): pakai helper `chooseTomSelect`
  di `guru-agenda.spec.js` (dropdown bisa tertutup oleh refresh AJAX → helper
  sudah retry otomatis).
- Kalau test butuh data baru, tambahkan di `E2eDatabaseSeeder` (idempotent).
- Debug pakai `npm run test:e2e:ui`.
