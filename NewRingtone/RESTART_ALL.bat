@echo off
chcp 65001 >nul
cls
echo ========================================
echo ПОЛНАЯ ПЕРЕЗАГРУЗКА СЕРВЕРА
echo ========================================
echo.

echo [1] Останавливаем все процессы PHP...
taskkill /F /IM php.exe /T >nul 2>&1
timeout /t 2 /nobreak >nul
echo Готово!
echo.

echo [2] Очищаем все кэши...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1
echo Готово!
echo.

echo [3] Проверяем базу данных...
php diagnose_and_fix.php
echo.

echo [4] Запускаем сервер на порту 3000...
echo.
echo ========================================
echo СЕРВЕР ЗАПУЩЕН
echo Откройте: http://localhost:3000
echo Нажмите Ctrl+C для остановки
echo ========================================
echo.
php -S localhost:3000 -t public

