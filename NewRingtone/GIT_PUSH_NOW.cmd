@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo Добавляем файлы...
git add app/Http/Controllers/MainController.php
git add resources/views/layouts/header.blade.php
git add resources/views/layouts/mobile-menu.blade.php
git add resources/views/layouts/footer.blade.php

echo.
echo Статус:
git status

echo.
echo Создаем коммит...
git commit -m "Update: Изменения в меню и увеличение количества материалов" -m "- MainController: увеличено количество материалов с 3 до 6 в блоке типов" -m "- Header: заменена ссылка Звуки на Мелодии (type/melodii)" -m "- Header: удалена ссылка на Статьи" -m "- Mobile menu: заменена ссылка Звуки на Мелодии" -m "- Mobile menu: удалены ссылки на Цитаты и Статьи" -m "- Footer: заменена ссылка Звуки на Мелодии"

echo.
echo Отправляем на GitHub...
git push

echo.
echo ========================================
echo ✓ Готово!
echo ========================================
echo.

pause



