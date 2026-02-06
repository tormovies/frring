@echo off
chcp 65001 >nul
echo ========================================
echo 🚀 ДЕПЛОЙ НА ПРОДАКШЕН
echo ========================================
echo.
echo Подключение к серверу: adminfeg@adminfeg.beget.tech
echo Путь к проекту: ~/neurozvuk.ru/laravel
echo.
echo Выполняем деплой...
echo.

ssh adminfeg@adminfeg.beget.tech "cd ~/neurozvuk.ru/laravel && git pull origin master && php8.3 artisan cache:clear && php8.3 artisan config:clear && php8.3 artisan route:clear && php8.3 artisan view:clear && php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan optimize && echo '✓ Deploy complete!'"

echo.
echo ========================================
echo ✓ Готово!
echo ========================================
pause

