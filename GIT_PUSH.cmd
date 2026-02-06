@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ОТПРАВКА ИЗМЕНЕНИЙ НА СЕРВЕР
echo ========================================
echo.

echo Текущий статус:
git status
echo.

echo Отправляем коммит на origin/master...
git push

echo.
echo ========================================
echo ✓ Готово!
echo ========================================
echo.

pause



