// tests/e2e/siswa-attendance.spec.js
// Alur presensi harian siswa:
//   siswa login → buka absensi harian → check-in (foto + kirim) →
//   status tercatat → check-out → status pulang tercatat.
//
// Prasyarat data (E2eDatabaseSeeder di database test terpisah):
//   - e2e.siswa@school.com aktif, terdaftar di kelas X RPL E2E.
//   - Setting absensi harian dibuka 24 jam (00:00–23:59), tanpa foto wajib,
//     WhatsApp nonaktif → test bisa check-in/out kapan saja.
//   - Jam operasional di-override sampai 2099.
//
// Kamera: form absensi selalu butuh foto (JS). Test memakai kamera PALSU
// Chrome (--use-fake-device-for-media-stream, diatur di playwright.config.js).
// Kalau kamera palsu gagal di environment tertentu, ada fallback: injeksi
// foto ke state Alpine lalu kirim lewat tombol submit yang sama.
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

// Foto JPEG 1x1 (data URL) untuk fallback kamera.
const FAKE_PHOTO =
  'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AKp//2Q==';

/**
 * Alur foto + kirim untuk satu kartu absensi (check-in / check-out).
 * - Buka kamera → tunggu frame video → ambil foto → tombol kirim aktif → submit.
 * - Fallback: kalau kamera palsu tidak tersedia, injeksi foto + fingerprint
 *   ke state Alpine, lalu tetap kirim lewat tombol submit.
 */
async function captureAndSubmit(page, cardTitle, submitLabel, successMessage) {
  const form = page
    .locator('form[data-skip-global-loading]')
    .filter({ hasText: cardTitle });

  await expect(form.getByRole('button', { name: 'Buka Kamera' })).toBeVisible({ timeout: 10_000 });

  try {
    // Path A: kamera palsu
    await form.getByRole('button', { name: 'Buka Kamera' }).click();
    // Tunggu frame video sungguhan (videoWidth > 0) sebelum ambil foto
    await page.waitForFunction(
      () => {
        const forms = [...document.querySelectorAll('form[data-skip-global-loading]')];
        const visible = forms.find((f) => f.offsetParent !== null);
        const video = visible ? visible.querySelector('video') : null;
        return !!video && video.videoWidth > 0;
      },
      undefined,
      { timeout: 10_000 }
    );
    await form.getByRole('button', { name: 'Ambil Foto' }).click();
  } catch {
    // Path B (fallback): set foto & fingerprint langsung ke state Alpine.
    // Tombol submit otomatis aktif (reaktivitas Alpine), lalu kirim normal.
    await form.evaluate((el) => {
      const data = window.Alpine.$data(el);
      data.photoData = FAKE_PHOTO;
      data.deviceFingerprint = 'e2e-device-fingerprint';
    });
  }

  const submit = form.getByRole('button', { name: submitLabel });
  await expect(submit).toBeEnabled({ timeout: 10_000 });
  await submit.click();

  await expect(page.getByText(successMessage)).toBeVisible({ timeout: 15_000 });
}

test.describe('Alur Presensi Harian Siswa', () => {
  // Test ini stateful terhadap TANGGAL HARI INI (data absensi tidak bisa
  // digeser tanggalnya). Kalau CI me-retry setelah check-in tersimpan,
  // retry akan selalu gagal ('Anda sudah absen masuk hari ini.'). Karena
  // retry tidak bisa menyelamatkan test ini, nonaktifkan retry di blok ini.
  test.describe.configure({ retries: 0 });

  test('siswa check-in lalu check-out dan statusnya tampil', async ({ page, context, baseURL }) => {
    // Izin kamera (berpasangan dengan --use-fake-ui-for-media-stream).
    // Origin diambil dari baseURL config — tidak bisa pakai page.url() karena
    // halaman belum dinavigasi (masih about:blank → origin 'null' → error).
    await context.grantPermissions(['camera'], { origin: baseURL });

    await login(page, USERS.siswa);

    // 1) Buka halaman absensi harian
    await page.goto('/siswa/daily-attendance');
    await expect(page.getByRole('heading', { name: 'Absensi hari ini' })).toBeVisible();
    // Status awal: belum absen
    await expect(page.getByText('Belum absen', { exact: true })).toBeVisible();

    // Kartu status ("Masuk" & "Pulang") di header selalu terlihat — tidak
    // tergantung tab kamera mana yang aktif.
    const header = page
      .locator('section')
      .filter({ has: page.getByRole('heading', { name: 'Absensi hari ini' }) });
    const masukCard = header.locator('div.rounded-2xl').filter({ hasText: 'Masuk' }).first();
    const pulangCard = header.locator('div.rounded-2xl').filter({ hasText: 'Pulang' }).first();

    // 2) Check-in
    await captureAndSubmit(page, 'Absen Masuk', 'Kirim Masuk', 'Absensi masuk berhasil disimpan.');

    // 3) Status check-in tampil: waktu masuk tercatat (bukan "-")
    await expect(masukCard.locator('p.truncate')).toHaveText(/\d{2}:\d{2}/);
    await expect(masukCard.getByText('Masuk', { exact: true }).first()).toBeVisible();

    // 4) Buka tab pulang (setelah check-in tab aktif otomatis pindah ke
    //    pulang; klik eksplisit supaya deterministik), lalu check-out
    await page.getByText('Jam pulang', { exact: true }).click();
    await captureAndSubmit(page, 'Absen Pulang', 'Kirim Pulang', 'Absensi pulang berhasil disimpan.');

    // 5) Status check-out tampil: waktu pulang tercatat (bukan "-")
    await expect(pulangCard.locator('p.truncate')).toHaveText(/\d{2}:\d{2}/);
    await expect(pulangCard.getByText('Pulang', { exact: true }).first()).toBeVisible();

    // 6) Riwayat terbaru mencatat hari ini dengan jam masuk & pulang
    const history = page.locator('section').filter({ hasText: 'Riwayat terbaru' });
    await expect(history.getByText(/\d{2}:\d{2}/).first()).toBeVisible();
  });
});
