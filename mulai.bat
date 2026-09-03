@echo off
title Agenda Kelas Digital
echo ===================================================
echo   Menyiapkan Aplikasi Agenda Kelas Digital...
echo ===================================================
echo.

echo [1/4] Menyalakan PHP Server...
start "PHP Server" cmd /k "php artisan serve"

echo [2/4] Menyalakan Vite (Frontend CSS/JS)...
start "Vite Server" cmd /k "npm run dev"

echo [3/4] Menyalakan Queue Worker (WhatsApp)...
start "Queue Worker" cmd /k "wrap_worker.bat"

echo [4/4] Menyalakan Scheduler (kirim notifikasi alpha otomatis)...
start "Scheduler" cmd /k "php artisan schedule:work"

echo.
echo Selesai! Empat jendela baru (hitam) telah terbuka.
echo Jangan tutup jendela "Queue Worker" dan "Scheduler" supaya
echo notifikasi WhatsApp ke orang tua tetap terkirim otomatis.
echo Anda bisa menutup jendela ini.
echo.
pause
