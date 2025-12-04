@echo off
echo ========================================
echo   THIET LAP MAT KHAU TAI KHOAN TEST
echo ========================================
echo.

cd /d "%~dp0"

echo Dang chay script setup_test_accounts.php...
echo.

C:\xampp\php\php.exe setup_test_accounts.php

echo.
echo ========================================
echo   HOAN THANH!
echo ========================================
echo.
echo Nhan phim bat ky de dong cua so...
pause > nul
