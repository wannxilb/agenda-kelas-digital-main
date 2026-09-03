// tests/e2e/helpers.js
// Kredensial & fungsi bantu untuk test E2E.
//
// Akun di bawah dibuat oleh database/seeders/E2eDatabaseSeeder.php di
// DATABASE TEST TERPISAH (database/e2e.sqlite) — bukan data dev kamu.
// Semua password: password
//
// Bisa di-override lewat env (berguna untuk CI):
//   E2E_ADMIN_EMAIL=... E2E_ADMIN_PASSWORD=... npm run test:e2e
import { expect } from '@playwright/test';

const env = (key, fallback) => process.env[key] || fallback;

export const USERS = {
  superAdmin: {
    name: env('E2E_SUPER_ADMIN_NAME', 'Super Admin E2E'),
    email: env('E2E_SUPER_ADMIN_EMAIL', 'superadmin@school.com'),
    password: env('E2E_SUPER_ADMIN_PASSWORD', 'password'),
    dashboardPath: '/super-admin/dashboard',
  },
  admin: {
    name: env('E2E_ADMIN_NAME', 'Admin E2E'),
    email: env('E2E_ADMIN_EMAIL', 'admin@school.com'),
    password: env('E2E_ADMIN_PASSWORD', 'password'),
    dashboardPath: '/admin/dashboard',
  },
  teacher: {
    name: env('E2E_TEACHER_NAME', 'Guru E2E'),
    email: env('E2E_TEACHER_EMAIL', 'e2e.guru@school.com'),
    password: env('E2E_TEACHER_PASSWORD', 'password'),
    dashboardPath: '/guru/dashboard',
  },
  guruWaliKelas: {
    name: env('E2E_GURU_WALIKELAS_NAME', 'Guru Wali Kelas E2E'),
    email: env('E2E_GURU_WALIKELAS_EMAIL', 'e2e.guru.walikelas@school.com'),
    password: env('E2E_GURU_WALIKELAS_PASSWORD', 'password'),
    dashboardPath: '/wali-kelas/dashboard',
  },
  guruWakasek: {
    name: env('E2E_GURU_WAKASEK_NAME', 'Guru Wakasek E2E'),
    email: env('E2E_GURU_WAKASEK_EMAIL', 'e2e.guru.wakasek@school.com'),
    password: env('E2E_GURU_WAKASEK_PASSWORD', 'password'),
    dashboardPath: '/wakasek/dashboard',
  },
  waliKelas: {
    name: env('E2E_WALIKELAS_NAME', 'Wali Kelas E2E'),
    email: env('E2E_WALIKELAS_EMAIL', 'e2e.walikelas@school.com'),
    password: env('E2E_WALIKELAS_PASSWORD', 'password'),
    dashboardPath: '/wali-kelas/dashboard',
  },
  sekretaris: {
    name: env('E2E_SEKRETARIS_NAME', 'Sekretaris E2E'),
    email: env('E2E_SEKRETARIS_EMAIL', 'e2e.sekretaris@school.com'),
    password: env('E2E_SEKRETARIS_PASSWORD', 'password'),
    dashboardPath: '/sekretaris/dashboard',
  },
  wakasek: {
    name: env('E2E_WAKASEK_NAME', 'Wakasek E2E'),
    email: env('E2E_WAKASEK_EMAIL', 'e2e.wakasek@school.com'),
    password: env('E2E_WAKASEK_PASSWORD', 'password'),
    dashboardPath: '/wakasek/dashboard',
  },
  siswa: {
    name: env('E2E_SISWA_NAME', 'Siswa E2E'),
    email: env('E2E_SISWA_EMAIL', 'e2e.siswa@school.com'),
    password: env('E2E_SISWA_PASSWORD', 'password'),
    dashboardPath: '/siswa/dashboard',
  },
};

/**
 * Login lewat halaman /login dan tunggu sampai keluar dari halaman login.
 * @param {import('@playwright/test').Page} page
 * @param {{ email: string, password: string }} credentials
 */
export async function login(page, { email, password }) {
  // domcontentloaded: tidak menunggu font/asset eksternal (Google Fonts dsb.)
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('#email')).toBeVisible();
  await page.fill('#email', email);
  await page.fill('#password', password);
  await page.click('button[type="submit"]');
  // Tunggu redirect keluar dari /login (dashboard sesuai role user).
  // waitUntil domcontentloaded: halaman dashboard dianggap sampai begitu DOM
  // siap, TIDAK menunggu event 'load' (asset CDN seperti tom-select/font bisa
  // lambat/macet dan membuat 'load' tidak pernah fire → timeout palsu).
  await page.waitForURL((url) => !url.pathname.startsWith('/login'), {
    waitUntil: 'domcontentloaded',
    timeout: 30_000,
  });
}

/**
 * Logout lewat form logout di sidebar.
 * Catatan: action form dirender sebagai URL absolut (http://.../logout),
 * jadi pakai selector contains `action*="logout"`.
 */
export async function logout(page) {
  const logoutButton = page
    .locator('form[action*="logout"] button[type="submit"]')
    .first();
  await logoutButton.click();
  await page.waitForURL(/\/login/, { waitUntil: 'domcontentloaded', timeout: 30_000 });
}
