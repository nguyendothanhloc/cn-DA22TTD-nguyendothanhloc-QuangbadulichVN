@echo off
echo ========================================
echo   THIET LAP MAT KHAU TAI KHOAN TEST
echo ========================================
echo.

cd /d "%~dp0"

echo Dang tim PHP...
echo.

REM Thu cac duong dan PHP pho bien
if exist "C:\xampp\php\php.exe" (
    echo Tim thay PHP tai C:\xampp\php\
    C:\xampp\php\php.exe setup_test_accounts.php
    goto done
)

if exist "C:\xampp81\php\php.exe" (
    echo Tim thay PHP tai C:\xampp81\php\
    C:\xampp81\php\php.exe setup_test_accounts.php
    goto done
)

if exist "C:\Program Files\xampp\php\php.exe" (
    echo Tim thay PHP tai C:\Program Files\xampp\php\
    "C:\Program Files\xampp\php\php.exe" setup_test_accounts.php
    goto done
)

echo.
echo KHONG TIM THAY PHP!
echo Vui long chay truc tiep qua trinh duyet:
echo http://localhost:81/setup_test_accounts.php
echo.
goto end

:done
echo.
echo ========================================
echo   HOAN THANH!
echo ========================================

:end
echo.
echo Nhan phim bat ky de dong cua so...
pause > nul
