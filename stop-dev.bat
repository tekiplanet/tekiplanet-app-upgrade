@echo off
echo Stopping TekiPlanet Development Environment...
echo.

REM Kill all related processes
taskkill /f /im "php.exe" 2>nul
taskkill /f /im "node.exe" 2>nul
taskkill /f /im "cloudflared.exe" 2>nul

REM Stop XAMPP Services (background)
echo Stopping XAMPP Services...
start /min "Stop Apache" cmd /c "C:\xampp\apache_stop.bat"
start /min "Stop MySQL" cmd /c "C:\xampp\mysql_stop.bat"

REM Wait for services to stop
timeout /t 3 /nobreak >nul

echo All development processes stopped.
echo XAMPP services are being stopped in the background.
pause