@echo off
title NAWASENA WEBREPORT - SERVER H2H
color 0B
cd /d "%~dp0"

echo ================================================================
echo        NAWASENA WEBREPORT - SERVER H2H ^& RESELLER PORTAL
echo ================================================================
echo.

:: 1. Deteksi PHP Executable
set "PHP_BIN="

if exist "%~dp0php\php.exe" (
    set "PHP_BIN=%~dp0php\php.exe"
    echo [INFO] Menggunakan Portable PHP bawaan folder:
    echo        %~dp0php\php.exe
    goto :PHP_CHECK_DONE
)

if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
    goto :PHP_CHECK_DONE
)

if exist "D:\xampp\php\php.exe" (
    set "PHP_BIN=D:\xampp\php\php.exe"
    goto :PHP_CHECK_DONE
)

where php >nul 2>nul
if %errorlevel% equ 0 (
    set "PHP_BIN=php"
    goto :PHP_CHECK_DONE
)

:PHP_NOT_FOUND
color 0C
echo [ERROR] PHP tidak ditemukan di folder maupun di komputer ini!
echo Pastikan folder php ada di dalam folder proyek ini.
echo.
pause
exit /b 1

:PHP_CHECK_DONE
echo [OK] PHP siap digunakan.
echo.

:: 2. Bebaskan Port 8000 jika masih dipakai proses sebelumnya
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":8000" ^| findstr "LISTENING"') do (
    echo [INFO] Membebaskan port 8000 dari PID %%a
    taskkill /F /PID %%a >nul 2>nul
)

:: 3. Cek file .env
if exist "%~dp0.env" goto :ENV_OK
echo [SETUP] Menyiapkan file .env dari template
if exist "%~dp0.env.example" (
    copy /y "%~dp0.env.example" "%~dp0.env" >nul
    echo [SETUP] Men-generate Application Key
    "%PHP_BIN%" artisan key:generate --force
)
:ENV_OK

:: 4. Cek database SQLite
if not exist "%~dp0database\database.sqlite" (
    echo [SETUP] Menyiapkan database SQLite
    type nul > "%~dp0database\database.sqlite"
)
echo [SETUP] Sinkronisasi tabel database
"%PHP_BIN%" artisan migrate --force >nul 2>nul

:: 5. Refresh storage symlink otomatis sesuai path folder PC saat ini
if exist "%~dp0public\storage" (
    rmdir "%~dp0public\storage" >nul 2>nul
)
"%PHP_BIN%" artisan storage:link >nul 2>nul

:: 6. Bersihkan cache view
"%PHP_BIN%" artisan view:clear >nul 2>nul

echo ================================================================
echo  Server Webreport berjalan di:
echo    - Lokal PC : http://127.0.0.1:8000
echo    - Jaringan : http://localhost:8000
echo.
echo  Jendela ini HARUS TETAP DIBUKA agar web tetap aktif.
echo  Tekan CTRL + C untuk menghentikan server.
echo ================================================================
echo.

:: 7. Buka Browser otomatis setelah 2 detik
start "" cmd /c "timeout /t 2 /nobreak >nul && start http://127.0.0.1:8000"

:: 8. Jalankan Server Web
"%PHP_BIN%" artisan serve --host=0.0.0.0 --port=8000
if %errorlevel% neq 0 (
    "%PHP_BIN%" -S 0.0.0.0:8000 -t "%~dp0public" "%~dp0server.php"
)

echo.
echo ================================================================
echo [INFO] Server telah berhenti.
echo ================================================================
pause
