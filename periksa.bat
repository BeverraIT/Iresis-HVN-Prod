@echo off
setlocal
cd /d "%~dp0"

if "%~1"=="" (
  echo.
  echo   Cara pakai: SERET file export .xlsx lalu JATUHKAN ke file periksa.bat ini.
  echo.
  echo   Perintah ini hanya MEMBACA. Tidak ada data yang diubah.
  echo.
  pause
  exit /b 1
)

echo.
"C:\xampp\php\php.exe" "%~dp0resync.php" periksa "%~1" 2>nul

echo.
echo ============================================================
echo  Tidak ada data yang diubah. Ini laporan saja.
echo ============================================================
echo.
pause
