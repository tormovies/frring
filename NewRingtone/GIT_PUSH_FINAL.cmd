@echo off
chcp 65001 >nul
cd /d C:\projects\Ringtone

echo ========================================
echo ПРОВЕРКА И ОТПРАВКА ИЗМЕНЕНИЙ
echo ========================================
echo.

echo [1] Текущий статус:
git status
echo.

echo [2] Последние 3 коммита:
git log --oneline -3
echo.

echo [3] Добавляем все изменения...
git add -A
echo.

echo [4] Статус после добавления:
git status
echo.

echo [5] Создаем коммит (если есть изменения)...
git commit -m "Update: CSS стили и шаблоны для компактных views" || echo Нет изменений для коммита
echo.

echo [6] Проверяем разницу с origin/master...
git log origin/master..HEAD --oneline
echo.

echo [7] Отправляем на GitHub...
git push origin master
echo.

echo ========================================
echo ГОТОВО! Проверь вывод выше.
echo ========================================
pause

