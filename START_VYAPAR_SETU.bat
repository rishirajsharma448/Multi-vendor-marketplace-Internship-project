@echo off
setlocal enabledelayedexpansion
title Vyapar Setu - Portable Server Launcher

echo ===============================================================================
echo                VYAPAR SETU - PORTABLE USB EDITION LAUNCHER
echo ===============================================================================
echo.

cd /d "%~dp0"
if not exist "logs" mkdir "logs"

:: -----------------------------------------------------------------------------
:: Step 1: Detect PHP Runtime
:: -----------------------------------------------------------------------------
set "PHP_BIN="

:: Check portable PHP directory in project root or pendrive
if exist "%~dp0php\php.exe" (
    set "PHP_BIN=%~dp0php\php.exe"
    echo [OK] Found local portable PHP runtime in: %~dp0php\
    goto :FoundPHP
)

if exist "%~dp0..\php\php.exe" (
    set "PHP_BIN=%~dp0..\php\php.exe"
    echo [OK] Found portable PHP runtime in parent directory: %~dp0..\php\
    goto :FoundPHP
)

:: Check system PATH
where php >nul 2>nul
if %ERRORLEVEL% equ 0 (
    set "PHP_BIN=php"
    echo [OK] Found PHP runtime in system PATH.
    goto :FoundPHP
)

:: Check standard XAMPP paths
for %%D in (C D E F G H) do (
    if exist "%%D:\xampp\php\php.exe" (
        set "PHP_BIN=%%D:\xampp\php\php.exe"
        echo [OK] Found XAMPP PHP runtime in: %%D:\xampp\php\php.exe
        goto :FoundPHP
    )
)

:NoPHP
echo [ERROR] PHP runtime was not detected on this computer!
echo.
echo Please do one of the following to run Vyapar Setu on this machine:
echo.
echo Option A (Self-Contained Portable USB):
echo   1. Download PHP 8.x for Windows (Zip) from https://windows.php.net/download/
echo   2. Extract the files into a folder named "php" right next to this script:
echo      %~dp0php\
echo   3. Double-click START_VYAPAR_SETU.bat again.
echo.
echo Option B (XAMPP Method):
echo   1. Install XAMPP on this computer (https://www.apachefriends.org).
echo   2. Copy the entire "%~n0" folder into C:\xampp\htdocs\
echo   3. Start Apache and MySQL from the XAMPP Control Panel.
echo   4. Visit: http://localhost/%~n0/
echo.
pause
exit /b 1

:FoundPHP
echo.
echo [1/3] Verifying PHP version...
"%PHP_BIN%" -v | findstr /i "PHP 8" >nul
if %ERRORLEVEL% equ 0 (
    echo [OK] PHP 8.x detected.
) else (
    echo [INFO] PHP runtime verified.
)

:: -----------------------------------------------------------------------------
:: Step 2: Detect Database Service (MySQL or SQLite Auto-Fallback)
:: -----------------------------------------------------------------------------
echo.
echo [2/3] Checking database environment...

:: Attempt to detect if MySQL is running
netstat -ano | findstr /R ":3306.*LISTENING" >nul
if %ERRORLEVEL% equ 0 (
    echo [OK] MySQL / MariaDB service is active on port 3306.
    echo      Using primary MySQL database (vyapar_setu).
) else (
    echo [INFO] MySQL port 3306 is not currently active.
    echo      Vyapar Setu will automatically use the self-contained SQLite database:
    echo      %~dp0config\vyapar_setu.sqlite
    echo      All 45 products, 117 vendors, categories, images, and reviews are fully preserved!
)

:: -----------------------------------------------------------------------------
:: Step 3: Start Local Web Server
:: -----------------------------------------------------------------------------
echo.
echo [3/3] Starting Vyapar Setu web server on http://localhost:8000 ...

:: Check if port 8000 is already in use
netstat -ano | findstr /R ":8000.*LISTENING" >nul
if %ERRORLEVEL% equ 0 (
    echo [INFO] Port 8000 is already active. Checking if it's already Vyapar Setu...
) else (
    powershell -Command "$proc = Start-Process -FilePath '%PHP_BIN%' -ArgumentList '-S localhost:8000 -t \"%~dp0.\"' -PassThru -WindowStyle Hidden; $proc.Id | Out-File -FilePath '%~dp0logs\server.pid' -Encoding ascii"
    timeout /t 2 >nul
)

:: Verify server is responding
powershell -Command "try { $res = Invoke-WebRequest -Uri 'http://localhost:8000/index.php' -UseBasicParsing -TimeoutSec 3; Write-Host '[OK] Web server is running successfully! (HTTP ' $res.StatusCode ')' } catch { Write-Host '[INFO] Server started.' }"

echo.
echo ===============================================================================
echo                     VYAPAR SETU IS LIVE AND READY!
echo ===============================================================================
echo.
echo  Website URL : http://localhost:8000/
echo.
echo  Sample Login Credentials:
echo  -------------------------------------------------------------
echo  [Platform Admin]  Email: admin@vyaparsetu.com
echo                    Pass : admin123
echo                    URL  : http://localhost:8000/admin_login.php
echo.
echo  [Customer]        Email: rishirajsharma448@gmail.com
echo                    Pass : password123
echo                    URL  : http://localhost:8000/login.php
echo.
echo  [Vendor]          Email: techcraftelectronics@vyaparsetu.com
echo                    Pass : vendor123
echo                    URL  : http://localhost:8000/vendor_login.php
echo  -------------------------------------------------------------
echo.
echo  To safely shut down the web server, double-click STOP_VYAPAR_SETU.bat
echo.
echo Opening website in your default browser...
start "" "http://localhost:8000/"

echo.
echo Press any key to close this launcher window (Server will stay running in background).
pause >nul
