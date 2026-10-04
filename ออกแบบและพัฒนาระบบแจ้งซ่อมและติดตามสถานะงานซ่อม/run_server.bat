@echo off
title Smart IT Helpdesk Server
cd /d "%~dp0"
echo ========================================================
echo  Starting Smart IT Helpdesk Local Development Server
echo ========================================================
echo.
echo Application will be running at: http://localhost:8080
echo Press Ctrl+C to stop the server.
echo.
php -S localhost:8080 -t public
pause
