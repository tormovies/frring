@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ПРИНУДИТЕЛЬНАЯ ОТПРАВКА ВСЕХ ИЗМЕНЕНИЙ
echo ========================================
echo.

echo Текущая директория:
cd
echo.

echo [1] Проверяем статус...
git status
echo.

echo [2] Добавляем все файлы...
git add -A
echo.

echo [3] Проверяем что будет закоммичено...
git status
echo.

echo [4] Создаем коммит...
git commit -m "Update: CSS стили и шаблоны для компактных views"
if errorlevel 1 (
    echo ОШИБКА: Не удалось создать коммит (возможно, нет изменений)
    goto :skip_commit
)
echo.

:skip_commit
echo [5] Отправляем на GitHub...
git push origin master
echo.

echo ========================================
echo ГОТОВО!
echo ========================================
pause

