// tests/e2e/setup-env.mjs
// Membuat .env.e2e dari .env.e2e.example (kalau belum ada).
// Jalankan sekali: npm run test:e2e:setup
import { existsSync, copyFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const example = join(root, '.env.e2e.example');
const target = join(root, '.env.e2e');

if (existsSync(target)) {
  console.log('.env.e2e sudah ada — tidak diubah.');
} else {
  copyFileSync(example, target);
  console.log('.env.e2e dibuat dari .env.e2e.example.');
}
