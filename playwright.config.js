// playwright.config.js
// Konfigurasi E2E testing dengan Playwright untuk Agenda Kelas Digital.
//
// Arsitektur testing:
// - Test dijalankan terhadap DATABASE TEST TERPISAH (.env.e2e → database/e2e.sqlite),
//   BUKAN database development kamu. Data dev kamu AMAN.
// - Setiap server test dinyalakan: migrate:fresh + E2eDatabaseSeeder, jadi
//   data test selalu fresh & deterministik.
// - Memakai Chrome yang SUDAH terinstall di sistem (channel: 'chrome'),
//   jadi tidak perlu `npx playwright install chromium`.
// - Server test jalan di port 8010 → tidak bentrok dengan `php artisan serve`
//   kamu di port 8000.
import { defineConfig, devices } from '@playwright/test';

const PORT = process.env.PORT || 8010;
const BASE_URL = process.env.BASE_URL || `http://127.0.0.1:${PORT}`;

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60_000,
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,

  reporter: [
    ['list'],
    ['html', { open: 'never' }],
  ],

  use: {
    baseURL: BASE_URL,
    channel: 'chrome', // pakai Chrome sistem, bukan Chromium bawaan Playwright
    locale: 'id-ID',
    timezoneId: 'Asia/Jakarta',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    // Kamera palsu: alur absensi harian siswa (check-in/out) memakai kamera
    // via getUserMedia. Flag ini membuat Chrome menyediakan stream video
    // palsu + izin kamera otomatis tanpa prompt.
    launchOptions: {
      args: ['--use-fake-device-for-media-stream', '--use-fake-ui-for-media-stream'],
    },
  },

  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],

  webServer: {
    // Setiap run: pastikan .env.e2e ada → migrate:fresh + seed → nyalakan
    // server test. reuseExistingServer: false → DB selalu dibuat ulang.
    //
    // PENTING (keamanan data): kalau .env.e2e hilang, `--env=e2e` di Laravel
    // diam-diam memakai config default dan migrate:fresh bisa menargetkan
    // DATABASE DEV. Karena itu command pertama selalu membuat .env.e2e dari
    // .env.e2e.example kalau belum ada (one-liner PHP = cross-platform, jalan
    // di cmd.exe maupun bash).
    //
    // Tambahan penting: environment OS bisa saja sudah punya variabel
    // DB_CONNECTION / DB_DATABASE (mis. di-set oleh tool lain). Laravel Env
    // repository bersifat immutable → nilai dari OS MENIMPA .env.e2e, sehingga
    // migrate:fresh --env=e2e bisa nyasar ke DB dev. Maka variabel DB di-`set`
    // EKSPLISIT ke sqlite test dulu sebelum menjalankan artisan apa pun, dan
    // DatabaseSafetyProvider (app/Providers) memblokir kalau masih salah target.
    //
    // Catatan Windows: command dijalankan via cmd.exe, jadi pakai `set` dan `&&`
    // biasa (hindari sintaks bash).
    command:
      `php -r "if(!file_exists('.env.e2e')){copy('.env.e2e.example','.env.e2e');}" && ` +
      `set "DB_CONNECTION=sqlite" && ` +
      `set "DB_DATABASE=database/e2e.sqlite" && ` +
      `set "APP_ENV=e2e" && ` +
      `php artisan migrate:fresh --env=e2e --force && ` +
      `php artisan db:seed --env=e2e --class=E2eDatabaseSeeder --force && ` +
      `php artisan serve --env=e2e --host=127.0.0.1 --port=${PORT}`,
    url: BASE_URL,
    reuseExistingServer: false,
    timeout: 180_000,
  },
});
