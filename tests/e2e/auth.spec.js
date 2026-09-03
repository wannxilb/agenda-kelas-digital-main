// tests/e2e/auth.spec.js
// Test alur autentikasi: redirect, login, login gagal, logout.
import { test, expect } from '@playwright/test';
import { login, logout, USERS } from './helpers.js';

test.describe('Autentikasi', () => {
  test('root (/) redirect ke halaman login', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('#email')).toBeVisible();
    await expect(page.locator('#password')).toBeVisible();
  });

  test('login sebagai Admin → redirect ke /admin/dashboard', async ({ page }) => {
    await login(page, USERS.admin);
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    await expect(
      page.getByRole('heading', { name: /Selamat Datang/ })
    ).toBeVisible();
  });

  test('login sebagai Super Admin → redirect ke /super-admin/dashboard', async ({ page }) => {
    await login(page, USERS.superAdmin);
    await expect(page).toHaveURL(/\/super-admin\/dashboard/);
  });

  test('login sebagai Guru → redirect ke /guru/dashboard', async ({ page }) => {
    await login(page, USERS.teacher);
    await expect(page).toHaveURL(/\/guru\/dashboard/);
  });

  test('password salah → muncul pesan error dan tetap di /login', async ({ page }) => {
    await page.goto('/login');
    await page.fill('#email', `salah-${Date.now()}@test.com`);
    await page.fill('#password', 'password-salah');
    await page.click('button[type="submit"]');

    await expect(
      page.getByText('Email atau password yang Anda masukkan salah.')
    ).toBeVisible();
    await expect(page).toHaveURL(/\/login/);
  });

  test('logout dari dashboard → kembali ke halaman login', async ({ page }) => {
    await login(page, USERS.admin);
    await expect(page).toHaveURL(/\/admin\/dashboard/);

    await logout(page);
    await expect(page).toHaveURL(/\/login/);
    await expect(page.getByText('Anda berhasil logout.')).toBeVisible();
  });
});
