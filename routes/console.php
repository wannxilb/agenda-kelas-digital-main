<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Jadwal Backup Database Otomatis
|--------------------------------------------------------------------------
|
| Backup database dev (PostgreSQL agenda_kelas) dijalankan SETIAP HARI pukul
| 23:00 WIB + cleanup backup lama (retensi sesuai config/backup.php).
|
| PENTING: supaya jadwal ini benar-benar berjalan di Windows, aktifkan
| scheduler Laravel (jalankan sekali di terminal dan biarkan terbuka):
|
|     php artisan schedule:work
|
| atau daftarkan Task Scheduler Windows untuk menjalankan perintah tersebut
| setiap 1 menit (dokumentasi lengkap: tests/e2e/README.md).
|
| Backup tersimpan di: storage/app/private/agenda-kelas-digital/...
| Restore:  php artisan backup:list   → lihat file backup
|           lalu restore dump SQL ke PostgreSQL (psql/pgAdmin).
*/

Schedule::command('backup:run --only-db')
    ->daily()
    ->at('23:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));

Schedule::command('backup:clean')
    ->daily()
    ->at('23:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup.log'));

/*
|--------------------------------------------------------------------------
| Auto-Expire Pengajuan Izin/Tugas Luar Guru
|--------------------------------------------------------------------------
|
| Pengajuan pending yang tanggalnya sudah lewat (effectiveEndDate < hari ini)
| otomatis dibatalkan setiap pagi pukul 06:00 WIB, supaya tidak menggantung
| dan tidak memblokir guru mengajukan ulang rentang yang sama.
| (lihat docs/design-izin-tugas-luar-guru.md §7.8)
*/

Schedule::command('teacher-status:expire-pending')
    ->daily()
    ->at('06:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/teacher-status.log'));

/*
|--------------------------------------------------------------------------
| Penandaan Dispen Tanpa Bukti Absen (Perlu Verifikasi)
|--------------------------------------------------------------------------
|
| Setiap hari pukul 18:00 WIB, hari yang masuk dalam rentang dispen
| (lomba/kegiatan) yang sudah lewat jam operasional namun siswa tidak
| melakukan absen masuk akan ditandai 'unknown' (perlu verifikasi wali kelas).
| Jika siswa absen, statusnya dikunci 'present'.
*/

Schedule::command('attendance:flag-dispen-without-checkin')
    ->daily()
    ->at('18:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/attendance-flag-dispen.log'));

/*
|--------------------------------------------------------------------------
| Notifikasi WhatsApp Siswa Tidak Hadir (Alpha)
|--------------------------------------------------------------------------
|
| Berjalan SETIAP MENIT. Institusi hanya diproses jika jam verifikasi
| (check_in_verification_deadline, default 08:00) sudah lewat: siswa yang
| belum ada kabar (tidak absen masuk, tanpa pengajuan pending/approved)
| langsung dianggap alpha dan notifikasi langsung terkirim ke orang tua.
| Idempoten — tiap siswa hanya dinotif sekali per hari.
*/

Schedule::command('attendance:notify-absent')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/attendance-notify-absent.log'));
