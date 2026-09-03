// tests/e2e/role-access.spec.js
// Test kontrol akses berbasis role.
//
// - Belum login → akses halaman terproteksi redirect ke /login.
// - Role salah (mis. guru buka /admin/...) → middleware role Spatie abort 403.
import { test, expect } from '@playwright/test';
import { login, USERS } from './helpers.js';

test('belum login → akses /admin/dashboard redirect ke /login', async ({ page }) => {
  await page.goto('/admin/dashboard');
  await expect(page).toHaveURL(/\/login/);
});

test('role admin tidak bisa buka halaman super-admin → 403', async ({ page }) => {
  await login(page, USERS.admin);
  await expect(page).toHaveURL(/\/admin\/dashboard/);

  const response = await page.goto('/super-admin/dashboard');
  await expect(response).not.toBeNull();
  expect(response.status()).toBe(403);
});

test('role guru tidak bisa buka halaman admin → 403', async ({ page }) => {
  await login(page, USERS.teacher);
  await expect(page).toHaveURL(/\/guru\/dashboard/);

  const response = await page.goto('/admin/dashboard');
  await expect(response).not.toBeNull();
  expect(response.status()).toBe(403);
});

test('role guru bisa buka halaman guru sendiri → 200', async ({ page }) => {
  await login(page, USERS.teacher);
  const response = await page.goto('/guru/dashboard');
  await expect(response).not.toBeNull();
  expect(response.status()).toBe(200);
});
