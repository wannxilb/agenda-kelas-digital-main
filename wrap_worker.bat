@echo off
title Queue Worker (WhatsApp) x4
echo ===================================================
echo   Queue Worker - Notifikasi WhatsApp (4 worker paralel)
echo   Setiap worker memproses 1 pesan bergantian, jadi
echo   4 pesan bisa dikirim bersamaan = jauh lebih cepat.
echo ===================================================
echo.

start "WA Worker 1" /min cmd /k "wrap_worker_one.bat 1"
start "WA Worker 2" /min cmd /k "wrap_worker_one.bat 2"
start "WA Worker 3" /min cmd /k "wrap_worker_one.bat 3"
start "WA Worker 4" /min cmd /k "wrap_worker_one.bat 4"

echo.
echo 4 worker dinyalakan (masing-masing window terpisah).
echo Tutup 4 window "WA Worker" hanya jika ingin menghentikan.
echo Anda bisa menutup jendela ini.
echo.
pause
