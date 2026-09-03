// tests/e2e/period-switcher.spec.js
// Verifikasi period-switcher berfungsi untuk berbagai skenario role guru:
//   1. Guru pengajar (role teacher saja)
//   2. Guru + wali_kelas (multi-role, default redirect ke wali-kelas)
//   3. Guru + wakasek (multi-role, default redirect ke wakasek)
//
// Period-switcher ada di profile dropdown pada layouts/guru.blade.php.
// Guru pengajar bisa akses /guru/* langsung.
// Guru+waliKelas & guru+wakasek di-redirect ke portal masing-masing,
// tapi TETAP bisa akses /guru/* karena punya role teacher.
//
// Prasyarat: E2eDatabaseSeeder sudah membuat 2 academic years
// (2026/2027 aktif + 2025/2026 arsip) dan user multi-role.
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

async function getSelectedOptionText(select) {
  return select.evaluate((el) => el.options[el.selectedIndex]?.textContent ?? '');
}

async function assertPeriodSwitcherWorks(page) {
  // Buka profile dropdown (klik avatar)
  const profileBtn = page.locator('button').filter({ has: page.locator('.from-blue-500') }).first();
  await profileBtn.click();

  // Period-switcher ada di dropdown — ada 2 (desktop + mobile), pakai .first()
  const periodSelect = page.locator('form[action*="academic-period/switch"] select[name="academic_year_id"]').first();
  await expect(periodSelect).toBeVisible();

  // Harusnya ada minimal 2 opsi (2026/2027 + 2025/2026)
  const options = periodSelect.locator('option');
  const count = await options.count();
  expect(count).toBeGreaterThanOrEqual(2);

  // Opsi aktif (2026/2027) harus selected
  const selectedText = await getSelectedOptionText(periodSelect);
  expect(selectedText).toContain('2026/2027');

  // Switch ke periode arsip (2025/2026)
  const archiveOption = periodSelect.locator('option', { hasText: '2025/2026' });
  const archiveValue = await archiveOption.getAttribute('value');
  await periodSelect.selectOption(archiveValue);

  // Form auto-submits → halaman reload dengan academic_year_id di URL
  await page.waitForURL((url) => url.searchParams.has('academic_year_id'), { timeout: 10_000 });

  // Banner arsip harus muncul — cari div dengan border amber (period-banner partial)
  const archiveBanner = page.locator('.border-amber-200.bg-amber-50');
  await expect(archiveBanner).toBeVisible({ timeout: 10_000 });
  await expect(archiveBanner.getByText('Mode Arsip:')).toBeVisible();
}

test.describe('Period Switcher — Guru Pengajar', () => {
  test('guru pengajar bisa lihat & switch periode di profile dropdown', async ({ page }) => {
    await login(page, USERS.teacher);
    await page.goto('/guru/dashboard');
    await expect(page).toHaveURL(/\/guru\/dashboard/);
    await assertPeriodSwitcherWorks(page);
  });
});

test.describe('Period Switcher — Guru + Wali Kelas', () => {
  test('guru+waliKelas bisa akses guru portal & switch periode', async ({ page }) => {
    // Login → default redirect ke /wali-kelas/dashboard
    await login(page, USERS.guruWaliKelas);
    await expect(page).toHaveURL(/\/wali-kelas\/dashboard/);

    // Buka guru dashboard langsung (user punya role teacher → bisa akses /guru/*)
    await page.goto('/guru/dashboard');
    await expect(page).toHaveURL(/\/guru\/dashboard/);
    await assertPeriodSwitcherWorks(page);
  });

  test('guru+waliKelas TIDAK lihat period-switcher di portal wali kelas', async ({ page }) => {
    await login(page, USERS.guruWaliKelas);
    const periodSelect = page.locator('form[action*="academic-period/switch"] select[name="academic_year_id"]');
    await expect(periodSelect).toHaveCount(0);
  });
});

test.describe('Period Switcher — Guru + Wakasek', () => {
  test('guru+wakasek bisa akses guru portal & switch periode', async ({ page }) => {
    // Login → default redirect ke /wakasek/dashboard
    await login(page, USERS.guruWakasek);
    await expect(page).toHaveURL(/\/wakasek\/dashboard/);

    // Buka guru dashboard langsung
    await page.goto('/guru/dashboard');
    await expect(page).toHaveURL(/\/guru\/dashboard/);
    await assertPeriodSwitcherWorks(page);
  });

  test('guru+wakasek TIDAK lihat period-switcher di portal wakasek', async ({ page }) => {
    await login(page, USERS.guruWakasek);
    const periodSelect = page.locator('form[action*="academic-period/switch"] select[name="academic_year_id"]');
    await expect(periodSelect).toHaveCount(0);
  });
});

test.describe('Period Persistence — Guru + Wakasek', () => {
  test('periode arsip tetap aktif setelah pindah halaman', async ({ page }) => {
    // Login sebagai guru+wakasek → redirect ke wakasek, lalu buka guru portal
    await login(page, USERS.guruWakasek);
    await page.goto('/guru/dashboard');
    await expect(page).toHaveURL(/\/guru\/dashboard/);

    // Switch ke periode arsip
    const profileBtn = page.locator('button').filter({ has: page.locator('.from-blue-500') }).first();
    await profileBtn.click();
    const periodSelect = page.locator('form[action*="academic-period/switch"] select[name="academic_year_id"]').first();
    await expect(periodSelect).toBeVisible();
    const archiveOption = periodSelect.locator('option', { hasText: '2025/2026' });
    const archiveValue = await archiveOption.getAttribute('value');
    await periodSelect.selectOption(archiveValue);
    await page.waitForURL((url) => url.searchParams.has('academic_year_id'), { timeout: 10_000 });

    // Banner arsip harus muncul
    const archiveBanner = page.locator('.border-amber-200.bg-amber-50');
    await expect(archiveBanner).toBeVisible({ timeout: 10_000 });

    // Pindah halaman ke guru agenda (tanpa query param)
    await page.goto('/guru/agenda');
    await page.waitForLoadState('domcontentloaded');

    // Banner arsip HARUS masih muncul (periode persist)
    await expect(archiveBanner).toBeVisible({ timeout: 10_000 });
    await expect(archiveBanner.getByText('Mode Arsip:')).toBeVisible();

    // URL harus mengandung academic_year_id
    expect(new URL(page.url()).searchParams.get('academic_year_id')).toBe(archiveValue);
  });
});
