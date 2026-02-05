@echo off
echo Stopping all PHP processes...
taskkill /F /IM php.exe /T >nul 2>&1
timeout /t 1 /nobreak >nul

echo Clearing caches...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1

echo Starting server...
start cmd /k "php -S localhost:3000 -t public"

echo.
echo Server starting in new window...
echo Open http://localhost:3000
pause

