@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ОТПРАВКА CSS ФАЙЛА В РЕПОЗИТОРИЙ
echo ========================================
echo.

echo [1/4] Проверяем статус CSS файла...
git status public/css/styles.css
echo.

echo [2/4] Добавляем CSS файл...
git add public/css/styles.css
echo.

echo [3/4] Создаем коммит...
git commit -m "Update: CSS стили для компактных grid и list views" -m "- Добавлены стили для btn-download-grid и btn-download-icon" -m "- Добавлены стили для like-count-grid и audio-actions-grid" -m "- Обновлены стили для мобильных версий"
echo.

echo [4/4] Отправляем на GitHub...
git push origin master
echo.

echo ========================================
echo ✓ Готово! Теперь выполни на сервере:
echo git pull origin master
echo ========================================
echo.

pause

