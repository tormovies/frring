@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo КОММИТ ИЗМЕНЕНИЙ
echo ========================================
echo.

echo Добавляем основные изменения...
git add .gitignore
git add app/Models/Material.php
git add resources/views/material/show.blade.php

echo.
echo Добавляем package-lock.json (зависимости Node.js)...
git add package-lock.json

echo.
echo ========================================
echo Проверяем что будет закоммичено:
echo ========================================
git status
echo.

echo Продолжить коммит? (Y/N)
pause

echo.
echo Создаем коммит...
git commit -m "Fix: Исправлена проверка существования аудио файлов и обновлены зависимости

- Material::hasFile() теперь реально проверяет существование файла на диске
- Material::fileUrl() возвращает null если файла нет
- Добавлены проверки в show.blade.php для предотвращения ошибок
- Обновлен .gitignore (игнорируются временные скрипты и дампы БД)
- Обновлен package-lock.json"

echo.
echo ========================================
echo ✓ Коммит создан!
echo ========================================
echo.
echo Для отправки на сервер выполните:
echo   git push
echo.

pause



