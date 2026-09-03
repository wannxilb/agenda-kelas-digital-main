// tests/e2e/guru-teacher-status.spec.js
// Alur izin/tugas luar dari sisi GURU:
//   guru login → buka halaman izin/tugas luar → buat pengajuan baru
//   (pilih tipe, isi tanggal multi-hari, keterangan) → badge "Menunggu"
//   → edit pengajuan → simpan → tarik pengajuan → badge "Dibatalkan".
//
// Prasyarat data (E2eDatabaseSeeder di database test terpisah):
//   - e2e.guru@school.com aktif (role teacher).
//   - Jam operasional di-override sampai 2099.
//   - DB selalu fresh tiap run (migrate:fresh + seed).
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

// Tanggal "Senin depan" (YYYY-MM-DD) — selalu masa depan (validasi form:
// tanggal tidak boleh di masa lalu) dan tidak menabrak constraint unik antar
// run karena DB di-reset tiap run.
function nextMonday() {
  const now = new Date();
  let diff = (1 - now.getDay() + 7) % 7;
  if (diff === 0) diff = 7; // hari ini Senin → ambil Senin pekan depan
  const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() + diff);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

// Tambah N hari ke tanggal YYYY-MM-DD.
function addDays(dateStr, days) {
  const d = new Date(`${dateStr}T00:00:00`);
  d.setDate(d.getDate() + days);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

test.describe('Alur Izin / Tugas Luar Guru', () => {
  test('guru ajukan izin multi-hari, edit, lalu tarik pengajuannya', async ({ page }, testInfo) => {
    // +testInfo.retry hari: kalau CI me-retry, tanggalnya berbeda tiap attempt
    // sehingga tidak menabrak pengajuan yang mungkin tersisa dari attempt
    // sebelumnya (validasi overlap menolak rentang yang tumpang tindih).
    const date = addDays(nextMonday(), testInfo.retry * 7);
    const note = `E2E izin guru ${Date.now()}`;

    await login(page, USERS.teacher);

    // 1) Buka halaman izin / tugas luar
    await page.goto('/guru/teacher-status');
    await expect(page).toHaveURL(/\/guru\/teacher-status$/);
    await expect(page.getByRole('heading', { name: /izin.*tugas luar/i }).first()).toBeVisible();

    // 2) Buka form pengajuan baru
    await page.goto('/guru/teacher-status/create');
    await expect(page.locator('#teacherStatusForm')).toBeVisible();

    // 3) Pilih tipe "Izin" — radio custom disembunyikan (display:none), jadi
    //    klik <label> pembungkusnya (yang menampilkan kartu visual).
    const izinLabel = page.locator('label').filter({ has: page.locator('input[name="type"][value="izin"]') });
    await izinLabel.click();
    await expect(page.locator('input[name="type"][value="izin"]')).toBeChecked();

    // 4) Tanggal multi-hari (centang "Banyak hari" → muncul date_end)
    await page.fill('#date', date);
    await page.locator('label:has-text("Banyak hari") input[type="checkbox"]').check();
    await expect(page.locator('#date_end')).toBeVisible();
    await page.fill('#date_end', addDays(date, 2));

    // 5) Isi keterangan
    await page.fill('#note', note);

    // 5b) Upload surat wajib (semua tipe pengajuan wajib melampirkan surat)
    await page.locator('#attachment').setInputFiles({
      name: 'surat-e2e.pdf',
      mimeType: 'application/pdf',
      buffer: Buffer.from('%PDF-1.4\nE2E surat izin guru'),
    });

    // 6) Ajukan
    await page.locator('#submitBtn').click();

    // 7) Redirect ke daftar + pesan sukses + badge Menunggu
    //    (badge di-scope ke baris pengajuan — getByText global bisa menangkap
    //    option "Menunggu" tersembunyi di dropdown filter)
    await expect(page).toHaveURL(/\/guru\/teacher-status$/);
    await expect(page.getByText('berhasil dikirim')).toBeVisible();
    const createdRow = page.locator('tr').filter({ hasText: note });
    await expect(createdRow.getByText('Menunggu')).toBeVisible();

    // 8) Edit pengajuan (tombol Edit pada baris yang berisi note ini)
    await createdRow.locator('a', { hasText: 'Edit' }).click();
    await expect(page).toHaveURL(/\/edit$/);

    // Ubah keterangan lalu simpan
    const editedNote = `${note} (diubah)`;
    await page.fill('#note', editedNote);
    await page.locator('#submitBtn').click();

    await expect(page).toHaveURL(/\/guru\/teacher-status$/);
    await expect(page.getByText('Pengajuan berhasil diperbarui.')).toBeVisible();
    await expect(page.getByText(editedNote).first()).toBeVisible();

    // 9) Tarik pengajuan (tombol Tarik pada baris yang sama, konfirmasi dialog)
    const editedRow = page.locator('tr').filter({ hasText: editedNote });
    page.once('dialog', (dialog) => dialog.accept());
    await editedRow.locator('button', { hasText: 'Tarik' }).click();

    // Pesan sukses + badge berubah jadi Dibatalkan (di-scope ke row pengajuan,
    // bukan option "Dibatalkan" di dropdown filter yang tersembunyi)
    await expect(page.getByText('Pengajuan berhasil ditarik.')).toBeVisible();
    await expect(editedRow.getByText('Dibatalkan')).toBeVisible();
  });
});
