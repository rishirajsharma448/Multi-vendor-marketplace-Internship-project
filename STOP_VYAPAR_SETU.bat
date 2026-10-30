@echo off
setlocal enabledelayedexpansion
title Vyapar Setu - Stop Server

echo ===============================================================================
echo                 VYAPAR SETU - WEB SERVER SHUTDOWN
echo ===============================================================================
echo.

cd /d "%~dp0"

set "STOPPED=0"

:: 1. Check PID file
if exist "logs\server.pid" (
    set /p SERVER_PID=<"logs\server.pid"
    if defined SERVER_PID (
        echo [INFO] Stopping server process ID !SERVER_PID!...
        taskkill /F /PID !SERVER_PID! >nul 2>nul
        if !ERRORLEVEL! equ 0 (
            echo [OK] Process !SERVER_PID! stopped successfully.
            set "STOPPED=1"
        )
    )
    del /f /q "logs\server.pid" >nul 2>nul
)

:: 2. If port 8000 is still listening by php, clean it up
powershell -Command "$pids = (Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue | Select-Object -ExpandProperty OwningProcess); foreach ($id in $pids) { $p = Get-Process -Id $id -ErrorAction SilentlyContinue; if ($p.ProcessName -like '*php*') { Stop-Process -Id $id -Force; Write-Host '[OK] Stopped PHP server on port 8000 (PID '$id')' } }"

echo.
echo [OK] Vyapar Setu local server has been safely stopped.
echo      Your database records, product images, and files remain intact.
echo.
echo Press any key to exit.
pause >nul
