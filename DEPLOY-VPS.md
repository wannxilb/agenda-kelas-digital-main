# Checklist Deploy VPS — Agenda Kelas Digital

Panduan deploy aplikasi Laravel ini ke VPS untuk skala ±2.000 user (puncak beban di jam absen pagi, ±06.30–07.00).

> Pasang dulu `.env` production dari template: `cp .env.production.example .env`, lalu isi nilai `GANTI`.

---

## 1. Spesifikasi VPS yang disarankan

| Skala | CPU | RAM | Disk | Catatan |
|---|---|---|---|---|
| **Nyaman (disarankan)** | 4 vCPU | 8 GB | 80 GB SSD | Muat untuk 2.000 user + beban laporan |
| Minimal | 2 vCPU | 4 GB | 60 GB SSD | Bisa jalan, tapi tuning wajib diikuti semua |

**Jangan pakai**: shared hosting / VPS 1 GB RAM. Pilih OS **Ubuntu 22.04/24.04 LTS**.

Estimasi disk: foto absen terenkripsi ±10–15 GB/bulan (2.000 siswa × 2 foto × ±150 KB × 22 hari sekolah). Command `attendance:purge-media` (otomatis via scheduler) menghapus foto > 365 hari.

---

## 2. Arsitektur target

```
Browser (HTTPS)
   └── Nginx (reverse proxy + static)
         └── PHP-FPM 8.2 (pool aplikasi)
               ├── MySQL/MariaDB (data)
               ├── Redis (session + cache + queue)   ← KUNCI untuk 2.000 user
               └── Supervisor
                     ├── queue:work (2–4 worker, notifikasi WA)
                     └── schedule:run (scheduler tiap menit)
```

---

## 3. Install stack server

```bash
sudo apt update && sudo apt upgrade -y

# Nginx + PHP 8.2 + ekstensi
sudo apt install -y nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
     php8.2-xml php8.2-curl php8.2-gd php8.2-zip php8.2-bcmath php8.2-intl \
     php8.2-redis php8.2-opcache

# MySQL / MariaDB + Redis + Supervisor + utilitas
sudo apt install -y mysql-server redis-server supervisor composer git unzip certbot python3-certbot-nginx
```

Cek versi PHP (harus ≥ 8.2):
```bash
php -v
```

---

## 4. Setup MySQL

```bash
sudo mysql
```

```sql
-- Buat database & user khusus aplikasi (JANGAN pakai root untuk aplikasi)
CREATE DATABASE agenda_kelas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'agenda_app'@'localhost' IDENTIFIED BY 'PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON agenda_kelas.* TO 'agenda_app'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Tuning MySQL (`/etc/mysql/mysql.conf.d/99-tuning.cnf`)

```ini
[mysqld]
# 50–60% dari RAM. Contoh untuk 8 GB RAM:
innodb_buffer_pool_size = 4G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
# max_connections cukup 150–300
max_connections = 200
# Timeout request PHP yang panjang (export laporan)
wait_timeout = 120
interactive_timeout = 300
```

```bash
sudo systemctl restart mysql
```

---

## 5. Setup Redis

Ubah `/etc/redis/redis.conf`:

```ini
bind 127.0.0.1
protected-mode yes
maxmemory 1gb
maxmemory-policy allkeys-lru
# Kalau pakai password: requirepass PASSWORD_KUAT
# lalu set REDIS_PASSWORD di .env sesuai password tadi.
```

```bash
sudo systemctl restart redis
redis-cli ping   # harus balas PONG
```

---

## 6. Deploy aplikasi

```bash
# 1. Letakkan kode (contoh: /var/www/agenda)
sudo mkdir -p /var/www/agenda
sudo chown -R $USER:www-data /var/www/agenda
git clone <repo-url> /var/www/agenda
cd /var/www/agenda

# 2. Environment
cp .env.production.example .env
nano .env                        # isi APP_KEY kosong dulu, sisanya sesuai server
php artisan key:generate         # mengisi APP_KEY otomatis

# 3. Dependensi (tanpa package dev)
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Build frontend (butuh Node ≥ 20; atau build di lokal & upload folder public/build)
npm install
npm run build

# 5. Database
php artisan migrate --force
php artisan db:seed --force      # HANYA kalau database masih kosong

# 6. Storage & permission
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 7. Optimasi Laravel (WAJIB setelah deploy)
php artisan optimize             # config:cache + route:cache + view:cache + event:cache
```

> **Catatan**: `php artisan optimize` wajib dijalankan ulang **setiap kali deploy kode baru**, lalu `php artisan queue:restart`.

---

## 7. Setup PHP-FPM

Ubah `/etc/php/8.2/fpm/pool.d/www.conf`:

```ini
[www]
user = www-data
group = www-data

; Rumus: (RAM - 1GB) / 128MB per worker. Contoh 8GB RAM → 7GB/128MB ≈ 50
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 1000

; Proteksi request yang menggantung (export besar bisa lama)
request_terminate_timeout = 300
```

OPcache — cek `/etc/php/8.2/cli/conf.d/*-opcache.ini` dan pastikan di FPM aktif:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

> `validate_timestamps=0` = OPcache tidak cek perubahan file tiap request (lebih cepat). Set ke `1` selama development.

```bash
sudo systemctl restart php8.2-fpm
```

---

## 8. Setup Nginx

Buat `/etc/nginx/sites-available/agenda`:

```nginx
server {
    listen 80;
    server_name agenda.example.com;          # GANTI
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name agenda.example.com;          # GANTI

    # Ganti dengan path certbot setelah SSL terpasang
    ssl_certificate     /etc/letsencrypt/live/agenda.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/agenda.example.com/privkey.pem;

    root /var/www/agenda/public;
    index index.php;

    # Foto absen bisa sampai ~4 MB + base64 overhead → naikkan limit
    client_max_body_size 8m;

    # File tersembunyi
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static asset: cache lama + gzip
    location /build/ {
        add_header Cache-Control "public, max-age=31536000, immutable";
        try_files $uri =404;
    }
    location /storage/ {
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript image/svg+xml;
    gzip_min_length 1024;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/agenda /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 9. SSL (Let's Encrypt)

```bash
sudo certbot --nginx -d agenda.example.com
# Ikuti wizard; certbot otomatis mengedit config Nginx + auto-renew.
```

Tes auto-renewal:
```bash
sudo certbot renew --dry-run
```

---

## 10. Supervisor (queue + scheduler) — JANGAN LEWATKAN

Tanpa ini: **notifikasi WhatsApp tidak pernah terkirim** (job numpuk di Redis), purge foto & reminder agenda tidak jalan.

Buat `/etc/supervisor/conf.d/agenda-worker.conf`:

```ini
[program:agenda-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/agenda/artisan queue:work redis --queue=notifications,default --sleep=2 --tries=3 --timeout=120
directory=/var/www/agenda
autostart=true
autorestart=true
stopasgroup=true
killsignal=SIGQUIT
user=www-data
numprocs=4                       ; 2–4 worker cukup untuk notifikasi WA
redirect_stderr=true
stdout_logfile=/var/www/agenda/storage/logs/worker.log
stopwaitsecs=3600
```

Buat `/etc/supervisor/conf.d/agenda-scheduler.conf`:

```ini
[program:agenda-scheduler]
command=/usr/bin/php /var/www/agenda/artisan schedule:work
directory=/var/www/agenda
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/agenda/storage/logs/scheduler.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status          # semua harus RUNNING
```

> Scheduler ini menjalankan: `audit:purge` (harian), `attendance:purge-media` (00.30), dan `agenda:reminder` (06.00) — sudah terdaftar di `bootstrap/app.php`.

---

## 11. Post-deploy checklist

```bash
# Cek dari server
curl -s https://agenda.example.com/up          # harus "OK" (health check Laravel)
curl -sI https://agenda.example.com/login      # harus 200

# Cek log PHP & Laravel kalau ada error
sudo tail -f /var/www/agenda/storage/logs/laravel.log
sudo journalctl -u php8.2-fpm -n 50
```

Manual (via browser):
- [ ] Login admin → dashboard tampil (data polling tiap 30 detik, cache 60 detik)
- [ ] Halaman **Laporan Presensi** → load cepat (default bulan berjalan + cache)
- [ ] Coba **absensi siswa** (check-in foto + lokasi) → sukses & notifikasi WA terkirim
- [ ] Export PDF/Excel laporan → jalan
- [ ] `php artisan about` → cek env/redis/mysql terkoneksi benar
- [ ] `.env`: `APP_DEBUG=false` dan `SESSION_SECURE_COOKIE=true`

---

## 12. Backup & monitoring

```bash
# Backup database harian (via cron/systemd timer)
mysqldump -u agenda_app -p agenda_kelas | gzip > /backup/agenda_$(date +%F).sql.gz
```

- Paket `spatie/laravel-backup` sudah terpasang di `composer.json` — bisa diaktifkan dengan `php artisan vendor:publish --provider="Spatie\LaravelBackup\BackupServiceProvider"` lalu atur schedule.
- Pantau: `free -h` (RAM), `df -h` (disk — foto cepat besar!), `supervisorctl status`, `redis-cli info memory`.
- Alert uptime gratis: UptimeRobot / HetrixTools.

---

## 13. Troubleshooting singkat

| Gejala | Kemungkinan penyebab | Solusi |
|---|---|---|
| Login/absensi dapat **419** | Session cookie tidak secure / domain salah | `APP_URL` harus persis domain, `SESSION_SECURE_COOKIE=true` |
| Notifikasi WA tidak terkirim | Queue worker tidak jalan | `supervisorctl status`, pastikan `queue:work` RUNNING |
| Halaman laporan **500 / memory** | Belum cache config / PHP-FPM max_children kecil | `php artisan optimize`, cek `storage/logs/laravel.log` |
| Foto absen gagal upload | `client_max_body_size` terlalu kecil | Naikkan ke `8m` di Nginx, lalu `sudo nginx -s reload` |
| Disk penuh | Foto absen menumpuk | `php artisan attendance:purge-media` (manual) atau kurangi `--days` |
| Lambat di jam absen pagi | Session/cache/queue masih database | Pindah ke Redis (sesuai template `.env.production.example`) |
| `.env` diubah tapi tidak berefek | Config ter-cache | `php artisan optimize:clear`, lalu `php artisan optimize` |
