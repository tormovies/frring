@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ПРИМЕНЯЕМ ИЗМЕНЕНИЯ
echo ========================================
echo.

echo Очищаем кеш...
php artisan cache:clear
php artisan view:clear
php artisan config:clear

echo.
echo ========================================
echo ✓ Готово!
echo ========================================
echo.
echo Изменение: В блоке "6 типов контента"
echo теперь выводится по 6 материалов (было 3)
echo.
echo Обновите страницу http://localhost:3000
echo.

pause



