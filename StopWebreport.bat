@echo off
title HENTIKAN SERVER WEBREPORT
color 0E
cd /d "%~dp0"

echo ================================================================
echo             MENGHENTIKAN SERVER WEBREPORT (PORT 8000)
echo ================================================================
echo.

:: Cari PID proses yang memakai port 8000
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":8000" ^| findstr "LISTENING"') do (
    echo [INFO] Menutup proses PID: %%a yang menggunakan port 8000...
    taskkill /F /PID %%a >nul 2>nul
)

echo [OK] Server pada port 8000 berhasil dihentikan.
echo.
timeout /t 2 >nul
exit
