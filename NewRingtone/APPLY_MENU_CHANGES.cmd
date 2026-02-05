@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ПРИМЕНЯЕМ ИЗМЕНЕНИЯ МЕНЮ
echo ========================================
echo.

echo Очищаем кеш представлений...
php artisan view:clear
php artisan cache:clear

echo.
echo ========================================
echo ✓ Готово!
echo ========================================
echo.
echo Изменения в меню:
echo - Header: "Звуки" → "Мелодии" (type/melodii)
echo - Гамбургер меню: "Звуки" → "Мелодии" (type/melodii)
echo - Footer: "Звуки" → "Мелодии" (type/melodii)
echo.
echo Обновите страницу http://localhost:3000
echo.

pause



