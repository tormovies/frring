@echo off
cd /d "%~dp0"
echo Starting Laravel on http://127.0.0.1:3000 (PHP built-in server, document root = public)
php -S 127.0.0.1:3000 -t public
pause
