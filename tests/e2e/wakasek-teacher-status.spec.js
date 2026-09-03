// tests/e2e/wakasek-teacher-status.spec.js
// Alur izin/tugas luar dari sisi WAKASEK:
//   guru membuat pengajuan (izin + tugas luar) → logout →
//   wakasek login → buka halaman persetujuan → tab Menunggu →
//   SETUJUI via modal (opsional pengganti) → pindah ke tab Disetujui →
//   TOLAK via modal (wajib alasan) → status Ditolak →
//   cek monitoring agenda menampilkan guru sebagai "Tugas Luar".
//
// Prasyarat data (E2eDatabaseSeeder di database test terpisah):
//   - e2e.guru@school.com (role teacher) & e2e.wakasek@school.com (role wakasek).
//   - Guru E2E punya jadwal Senin–Jumat → monitoring bisa memverifikasi slot.
//   - DB selalu fresh tiap run.
import { test, expect } from '@playwright/test';
import { login, logout, USERS } from './helpers.js';

// Tanggal "Senin depan" (YYYY-MM-DD) — selalu masa depan & ada jadwal guru.
function nextMonday() {
  const now = new Date();
  let diff = (1 - now.getDay() + 7) % 7;
  if (diff === 0) diff = 7;
  const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() + diff);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

function addDays(dateStr, days) {
  const d = new Date(`${dateStr}T00:00:00`);
  d.setDate(d.getDate() + days);
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${mm}-${dd}`;
}

// Helper: guru E2E membuat pengajuan (agar wakasek punya data untuk diproses).
async function createRequestAsGuru(page, { type, date, dateEnd, note }) {
  await page.goto('/guru/teacher-status/create');
  await expect(page.locator('#teacherStatusForm')).toBeVisible();

  const typeLabel = page.locator('label').filter({ has: page.locator(`input[name="type"][value="${type}"]`) });
  await typeLabel.click();
  await expect(page.locator(`input[name="type"][value="${type}"]`)).toBeChecked();
  await page.fill('#date', date);
  if (dateEnd) {
    await page.locator('label:has-text("Banyak hari") input[type="checkbox"]').check();
    await expect(page.locator('#date_end')).toBeVisible();
    await page.fill('#date_end', dateEnd);
  }
  await page.fill('#note', note);
  await page.locator('#attachment').setInputFiles({
    name: 'surat-e2e.pdf',
    mimeType: 'application/pdf',
    buffer: Buffer.from('%PDF-1.4\nE2E surat pengajuan'),
  });
  await page.locator('#submitBtn').click();
  await expect(page).toHaveURL(/\/guru\/teacher-status$/);
}

test.describe('Alur Persetujuan Wakasek', () => {
  test('wakasek setujui & tolak pengajuan, status berubah, muncul di monitoring', async ({ page }, testInfo) => {
    // Offset minggu LEBIH JAUH dari spec guru (nextMonday + 28 & 35): spec
    // guru memakai nextMonday + retry*7 (maks +14 saat retry ke-2 di CI), jadi
    // offset +28/+35 menjamin tidak pernah bentrok validasi overlap di DB test
    // yang sama, bahkan ketika kedua spec berjalan paralel dengan retry.
    const approveDate = addDays(nextMonday(), 28 + testInfo.retry * 7);
    const rejectDate = addDays(nextMonday(), 35 + testInfo.retry * 7);
    const approveNote = `E2E tugas luar approve ${Date.now()}`;
    const rejectNote = `E2E izin reject ${Date.now()}`;

    // 1) Guru membuat 2 pengajuan: tugas luar multi-hari (untuk approve) &
    //    izin (untuk reject)
    await login(page, USERS.teacher);
    await createRequestAsGuru(page, {
      type: 'tugas_luar',
      date: approveDate,
      dateEnd: addDays(approveDate, 1),
      note: approveNote,
    });
    await createRequestAsGuru(page, {
      type: 'izin',
      date: rejectDate,
      note: rejectNote,
    });
    await logout(page);

    // 2) Login sebagai wakasek → halaman persetujuan → tab Menunggu
    await login(page, USERS.wakasek);
    await page.goto('/wakasek/teacher-status');
    await expect(page).toHaveURL(/\/wakasek\/teacher-status$/);
    await expect(page.getByRole('heading', { name: /persetujuan izin/i }).first()).toBeVisible();
    await expect(page.getByText(approveNote).first()).toBeVisible();
    await expect(page.getByText(rejectNote).first()).toBeVisible();

    // 3) SETUJUI pengajuan tugas luar via modal
    const approveCard = page.locator('div.bg-white.rounded-2xl').filter({ hasText: approveNote });
    await approveCard.getByRole('button', { name: 'Setujui' }).first().click();
    const approveModal = page.locator('div.x-cloak, [x-show]').filter({ hasText: 'Setujui Pengajuan' }).last();
    await expect(approveModal.getByText('Setujui Pengajuan')).toBeVisible();
    // Biarkan tanpa guru pengganti → langsung submit
    await approveModal.locator('button[type="submit"]').click();

    await expect(page.getByText('Pengajuan disetujui.')).toBeVisible();

    // 4) Pengajuan pindah ke tab Disetujui (badge hijau)
    await page.goto('/wakasek/teacher-status?tab=approved');
    await expect(page.getByText(approveNote).first()).toBeVisible();

    // 5) TOLAK pengajuan izin via modal (wajib alasan)
    await page.goto('/wakasek/teacher-status');
    const rejectCard = page.locator('div.bg-white.rounded-2xl').filter({ hasText: rejectNote });
    await rejectCard.getByRole('button', { name: 'Tolak' }).first().click();
    const rejectModal = page.locator('[x-show]').filter({ hasText: 'Tolak Pengajuan' }).last();
    await expect(rejectModal.getByText('Tolak Pengajuan')).toBeVisible();
    await rejectModal.locator('textarea[name="rejection_reason"]').fill('E2E: jadwal padat');
    await rejectModal.locator('button[type="submit"]').click();

    await expect(page.getByText('Pengajuan ditolak.')).toBeVisible();

    // Record Ditolak keluar dari tab pending → cek di tab "Semua"
    await page.goto('/wakasek/teacher-status?tab=all');
    const rejectedCard = page.locator('div.bg-white.rounded-2xl').filter({ hasText: rejectNote });
    await expect(rejectedCard.getByText('Ditolak')).toBeVisible();
    await expect(rejectedCard.getByText('Alasan: E2E: jadwal padat')).toBeVisible();

    // 6) Monitoring agenda menampilkan guru sebagai "Tugas Luar" di tanggal approve
    await page.goto(`/wakasek/monitoring/agenda?date=${approveDate}`);
    await expect(page).toHaveURL(/\/wakasek\/monitoring\/agenda/);
    // Slot tugas luar tampil (badge/tooltip "Tugas Luar")
    await expect(page.getByText('Tugas Luar').first()).toBeVisible();
  });
});
