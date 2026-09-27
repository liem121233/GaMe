@echo off
title Ember Spire - Game Server

echo ==========================================
echo        Ember Spire - Game Server
echo ==========================================
echo.

cd /d "%~dp0"

if not exist "C:\php\php.exe" (
    echo [ERROR] PHP not found at C:\php\php.exe
    pause
    exit /b
)

echo Starting game server...
echo.
echo Open your browser at:
echo http://localhost:8000
echo.
echo Press Ctrl+C to stop the server.
echo.

"C:\php\php.exe" -S localhost:8000
pause