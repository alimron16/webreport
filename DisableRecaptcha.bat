@echo off
title NONAKTIFKAN RECAPTCHA - WEBREPORT
color 0E
cd /d "%~dp0"

echo ================================================================
echo           NONAKTIFKAN FITUR RECAPTCHA WEBREPORT
echo ================================================================
echo.

set "PHP_BIN="
if exist "%~dp0php\php.exe" (
    set "PHP_BIN=%~dp0php\php.exe"
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
) else if exist "D:\xampp\php\php.exe" (
    set "PHP_BIN=D:\xampp\php\php.exe"
) else (
    set "PHP_BIN=php"
)

echo [INFO] Memproses penonaktifan reCAPTCHA...
"%PHP_BIN%" artisan recaptcha:off
"%PHP_BIN%" artisan cache:clear >nul 2>nul

echo.
echo ================================================================
echo [SUKSES] reCAPTCHA telah DINONAKTIFKAN!
echo Anda sekarang bisa langsung login ke Admin / Reseller.
echo ================================================================
echo.
pause
