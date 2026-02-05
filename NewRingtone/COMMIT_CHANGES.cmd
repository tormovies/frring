@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo КОММИТ ИЗМЕНЕНИЙ
echo ========================================
echo.

echo Проверяем статус...
git status
echo.

echo ========================================
echo Что будет закоммичено:
echo ========================================
echo 1. Увеличено количество материалов с 3 до 6 в блоке "6 типов контента"
echo 2. Заменены ссылки "Звуки" на "Мелодии" (type/melodii) во всех меню
echo 3. Удалены ссылки "Статьи" и "Цитаты" из меню
echo.

pause

echo.
echo Добавляем файлы...
git add app/Http/Controllers/MainController.php
git add resources/views/layouts/header.blade.php
git add resources/views/layouts/mobile-menu.blade.php
git add resources/views/layouts/footer.blade.php

echo.
echo Создаем коммит...
git commit -m "Update: Изменения в меню и увеличение количества материалов

- MainController: увеличено количество материалов с 3 до 6 в блоке типов
- Header: заменена ссылка Звуки на Мелодии (type/melodii)
- Header: удалена ссылка на Статьи
- Mobile menu: заменена ссылка Звуки на Мелодии
- Mobile menu: удалены ссылки на Цитаты и Статьи
- Footer: заменена ссылка Звуки на Мелодии"

echo.
echo ========================================
echo ✓ Коммит создан!
echo ========================================
echo.
echo Для отправки на сервер выполните:
echo   git push
echo.

pause



