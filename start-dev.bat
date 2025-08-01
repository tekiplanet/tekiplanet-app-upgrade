@echo off
echo Starting TekiPlanet Development Environment...
echo.

REM Check if cloudflared is installed
where cloudflared >nul 2>nul
if %errorlevel% neq 0 (
    echo Error: cloudflared is not installed or not in PATH
    echo Please install cloudflared first
    pause
    exit /b 1
)

REM Start XAMPP Services (background)
echo Starting XAMPP Services...
start /min "XAMPP Apache" cmd /c "C:\xampp\apache_start.bat"
start /min "XAMPP MySQL" cmd /c "C:\xampp\mysql_start.bat"

REM Wait for services to start
timeout /t 5 /nobreak >nul

REM Start Backend Tunnel (background)
echo Starting Backend Tunnel...
start /min "Backend Tunnel" cmd /c "cloudflared tunnel --config backend-tunnel.yml run"

REM Wait a moment for tunnel to start
timeout /t 3 /nobreak >nul

REM Start Frontend Tunnel (background)
echo Starting Frontend Tunnel...
start /min "Frontend Tunnel" cmd /c "cloudflared tunnel --config frontend-tunnel.yml run"

REM Wait a moment for tunnel to start
timeout /t 3 /nobreak >nul

REM Start Backend Server (background)
echo Starting Laravel Backend...
cd backend
start /min "Laravel Backend" cmd /c "php artisan serve --host=0.0.0.0 --port=8000"

REM Wait a moment for backend to start
timeout /t 5 /nobreak >nul

REM Start Frontend Server (background)
echo Starting React Frontend...
cd ..\frontend
start /min "React Frontend" cmd /c "npm run dev -- --host 0.0.0.0 --port 5173"

echo.
echo Development environment started!
echo.
echo XAMPP Services: Apache & MySQL
echo Backend: https://api.tekiplanet.org
echo Frontend: https://app.tekiplanet.org
echo.
echo All services are running in the background.
echo Use 'stop-dev.bat' to stop all services.
echo.
echo Press any key to close this window...
pause >nul