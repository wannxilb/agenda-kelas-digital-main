// tests/e2e/guru-agenda.spec.js
// Alur inti agenda guru:
//   guru login → halaman agenda → buka form jurnal baru → isi jurnal
//   (tanggal, kelas, mapel, ruangan, judul, detail) → simpan →
//   verifikasi muncul di daftar jurnal & arsip.
//
// Prasyarat data (disediakan E2eDatabaseSeeder di database test terpisah):
//   - Guru E2E (e2e.guru@school.com) punya jadwal Senin–Jumat di
//     kelas "X RPL E2E" mapel "Pemrograman Web E2E" ruang "Ruang E2E".
//   - Jam operasional di-override sampai 2099 (test bisa jalan kapan saja).
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

// Helper: tanggal "Senin depan" (format YYYY-MM-DD).
// Selalu di masa depan → tidak kena batasan waktu jadwal (hanya berlaku
// kalau tanggalnya hari ini), dan selalu ada jadwal guru (jadwal dibuat
// Senin–Jumat).
function nextMonday() {
  const now = new Date();
  let diff = (1 - now.getDay() + 7) % 7;
  if (diff === 0) diff = 7; // hari ini Senin → ambil Senin pekan depan
  const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() + diff);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

// Tambah N minggu ke tanggal YYYY-MM-DD.
function addWeeks(dateStr, weeks) {
  const d = new Date(`${dateStr}T00:00:00`);
  d.setDate(d.getDate() + weeks * 7);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

// Helper: pilih opsi pada dropdown TomSelect (kontrol UI, bukan <select> asli).
// Struktur DOM TomSelect (terverifikasi via debug):
//   <label id="class_id-ts-label">...</label>
//   <select id="..." class="ts-hidden-accessible">   ← select DISEMBUNYIKAN
//   <div class="ts-wrapper"><div class="ts-control">...</div>...</div>
// Wrapper adalah FOLLOWING SIBLING dari select (bukan parent).
//
// Kenapa pakai retry loop: setelah pilih kelas/tanggal, aplikasi memanggil
// fetch get-schedule-info (AJAX) yang merefresh opsi mapel & ruangan.
// Saat respons tiba, TomSelect memanggil setValue('') yang MENUTUP dropdown
// — kalau kita lagi nunggu opsi, dropdown jadi tertutup dan opsi tersembunyi.
// Retry membuka dropdown lagi sampai klik opsi berhasil.
async function chooseTomSelect(page, selectId, optionText) {
  const control = page
    .locator('#' + selectId)
    .locator(
      'xpath=following-sibling::div[contains(@class, "ts-wrapper")][1]//div[contains(@class, "ts-control")]'
    );
  const option = page
    .locator('.ts-dropdown .option')
    .filter({ hasText: optionText })
    .first();

  for (let attempt = 0; attempt < 5; attempt++) {
    await control.click();
    try {
      await option.click({ timeout: 3_000 });
      return;
    } catch {
      // Dropdown tertutup oleh refresh AJAX / opsi belum muncul — coba lagi.
    }
  }
  throw new Error(`Gagal memilih "${optionText}" di #${selectId} setelah beberapa percobaan.`);
}

test.describe('Alur Agenda Guru', () => {
  test('guru isi jurnal mengajar dan muncul di daftar & arsip', async ({ page }, testInfo) => {
    const title = `E2E Jurnal ${Date.now()}`;
    // +testInfo.retry minggu: kalau CI me-retry test ini (retries:2), tanggal
    // dipakai berbeda tiap attempt → tidak bentrok constraint unik agenda
    // (teacher + class + subject + date) yang sudah tersimpan di attempt
    // sebelumnya.
    const date = addWeeks(nextMonday(), testInfo.retry);

    await login(page, USERS.teacher);

    // 1) Buka halaman agenda
    await page.goto('/guru/agenda');
    await expect(page).toHaveURL(/\/guru\/agenda$/);
    await expect(page.getByRole('heading', { name: /jurnal/i }).first()).toBeVisible();

    // 2) Buka form jurnal baru
    await page.goto('/guru/agenda/create');
    await expect(page.locator('#guruAgendaForm')).toBeVisible();

    // 3) Isi tanggal (Senin depan — selalu masa depan & ada jadwal guru).
    //    Perubahan tanggal memicu AJAX reload mapel & ruangan (kelas sudah
    //    auto-terpilih karena guru punya jadwal hari ini).
    await page.fill('#date', date);

    // 4) Pilih kelas (TomSelect)
    await chooseTomSelect(page, 'class_id', 'X RPL E2E');
    await expect(page.locator('#class_id')).toHaveValue(/\d+/);

    // 5) Pilih mapel (opsi dimuat via AJAX setelah kelas & tanggal dipilih)
    await chooseTomSelect(page, 'subject_id', 'Pemrograman Web E2E');
    await expect(page.locator('#subject_id')).toHaveValue(/\d+/);

    // 6) Pilih ruangan
    await chooseTomSelect(page, 'room', 'Ruang E2E');
    await expect(page.locator('#room')).not.toHaveValue('');

    // 7) Isi konten jurnal
    await page.fill('#title', title);
    await page.fill(
      '#description',
      `Deskripsi jurnal E2E untuk memverifikasi alur pengisian agenda (${title}).`
    );

    // 8) Simpan (status default: published)
    await page.locator('#submitBtn').click();

    // 9) Redirect ke daftar jurnal + pesan sukses
    await expect(page).toHaveURL(/\/guru\/agenda$/);
    await expect(page.getByText('Jurnal mengajar berhasil disimpan.')).toBeVisible();

    // 10) Jurnal muncul di daftar jurnal
    await expect(page.getByText(title).first()).toBeVisible();

    // 11) Jurnal muncul di arsip (status published)
    await page.goto('/guru/agenda/archive');
    await expect(page).toHaveURL(/\/guru\/agenda\/archive/);
    await expect(page.getByText(title).first()).toBeVisible();
  });
});
