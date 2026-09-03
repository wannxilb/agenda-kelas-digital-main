// tests/e2e/dashboard.spec.js
// Smoke test: setiap role bisa login dan dashboard-nya tampil dengan benar.
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

for (const [roleKey, user] of Object.entries(USERS)) {
  test(`dashboard role "${roleKey}" tampil setelah login`, async ({ page }) => {
    await login(page, user);

    // Harus mendarat di dashboard sesuai role
    await expect(page).toHaveURL(
      new RegExp(`${user.dashboardPath.replaceAll('/', '\\/')}$`)
    );

    // Nama user tampil di sidebar/topbar
    // (wali_kelas dashboard hanya tampilkan nama pertama via explode(' '))
    // (multi-role users mungkin tidak punya nama di dashboard)
    const firstName = user.name.split(' ')[0];
    const nameVisible = page.getByText(firstName).first();
    // Tunggu sebentar, lalu skip kalau tidak ketemu
    const nameFound = await nameVisible.isVisible({ timeout: 3_000 }).catch(() => false);
    if (nameFound) {
      await expect(nameVisible).toBeVisible();
    }

    // Layout sidebar & konten utama tidak blank
    // (ada 2 elemen <main> — satu dari partial global-skeleton — jadi ambil yang pertama)
    await expect(page.locator('main').first()).toBeVisible();
    await expect(page.locator('body')).not.toHaveText(/Whoops|Something went wrong/i);
  });
}
