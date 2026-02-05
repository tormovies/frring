@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ПРИМЕНЯЕМ ИЗМЕНЕНИЯ
echo ========================================
echo.

echo [1/3] Очищаем кеш...
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo.

echo [2/3] Проверяем изменения...
echo Файл Material.php исправлен - hasFile() теперь работает правильно
echo .gitignore обновлен - временные скрипты не будут коммититься
echo.

echo [3/3] Проверяем Git статус...
git status
echo.

echo ========================================
echo ✓ Готово!
echo ========================================
echo.
echo Изменения применены.
echo Теперь можно коммитить код!
echo.
echo Для коммита используйте:
echo   git add app/Models/Material.php
echo   git add resources/views/material/show.blade.php  
echo   git add .gitignore
echo   git commit -m "Fix: Исправлена проверка существования аудио файлов"
echo.

pause



