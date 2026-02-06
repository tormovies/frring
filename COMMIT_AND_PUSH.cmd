@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ДОБАВЛЕНИЕ, КОММИТ И ОТПРАВКА ИЗМЕНЕНИЙ
echo ========================================
echo.

echo [1/4] Проверяем статус...
git status
echo.

echo [2/4] Добавляем измененные файлы...
git add resources/views/search/index.blade.php
git add resources/views/layouts/footer.blade.php
echo.

echo [3/4] Создаем коммит...
git commit -m "Update: Стили страницы поиска и обновление ссылок в футере" -m "- Поиск: применены компактные стили списка и плитки" -m "- Footer: обновлены ссылки на Телеграм (itarakani) и ВКонтакте (utarakana)" -m "- Footer: ссылка Контакты ведет на /author/neurozvuk"
echo.

echo [4/4] Отправляем на GitHub...
git push origin master
echo.

echo ========================================
echo ✓ Готово!
echo ========================================
echo.

pause

