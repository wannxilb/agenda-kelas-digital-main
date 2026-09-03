@echo off
title WA Worker %1
echo ===================================================
echo   WA Worker %1 (dari 4) - Notifikasi WhatsApp
echo   Restart otomatis tiap 1 jam agar tidak "stale".
echo   Jangan tutup jendela ini.
echo ===================================================
echo.

:loop
echo [%date% %time%] Worker %1 dimulai/restart...
php artisan queue:work database --queue=notifications,default --tries=3 --sleep=1 --max-time=3600
echo [%date% %time%] Worker %1 berhenti. Restart dalam 3 detik...
timeout /t 3 /nobreak >nul
goto loop
