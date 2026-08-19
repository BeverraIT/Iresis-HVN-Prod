@echo off
setlocal
cd /d "%~dp0"

if "%~1"=="" (
  echo.
  echo   Cara pakai: DRAG file export Jubelio ^(.xlsx^) lalu DROP ke file .bat ini.
  echo.
  pause
  exit /b 1
)

set "NORESI=%~2"
if "%NORESI%"=="" set /p NORESI=Masukkan nomor resi yang mau dicek [JY1312613006]:
if "%NORESI%"=="" set "NORESI=JY1312613006"

echo.
"C:\xampp\php\php.exe" "%~dp0cek_export_jubelio.php" "%~1" "%NORESI%"

echo.
pause
