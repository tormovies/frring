@echo off
echo ========================================
echo Настройка проекта Neurozvuk
echo ========================================
echo.

echo [1/6] Установка PHP зависимостей...
call composer install
if %errorlevel% neq 0 (
    echo Ошибка при установке зависимостей!
    pause
    exit /b 1
)

echo.
echo [2/6] Проверка .env файла...
if not exist .env (
    echo Создание .env файла из .env.example...
    if exist .env.example (
        copy .env.example .env
    ) else (
        echo ВНИМАНИЕ: .env.example не найден! Создайте .env вручную.
        pause
    )
) else (
    echo .env файл уже существует.
)

echo.
echo [3/6] Генерация ключа приложения...
php artisan key:generate
if %errorlevel% neq 0 (
    echo Ошибка при генерации ключа!
    pause
    exit /b 1
)

echo.
echo [4/6] Проверка базы данных...
REM Проверяем, какой тип БД используется
findstr /C:"DB_CONNECTION=mysql" .env >nul 2>&1
if %errorlevel% equ 0 (
    echo Используется MySQL база данных.
    echo Убедитесь, что база данных создана и дамп импортирован.
    echo Для импорта дампа используйте: import_dump.bat
) else (
    echo Создание базы данных SQLite...
    if not exist database\database.sqlite (
        type nul > database\database.sqlite
        echo База данных SQLite создана.
    ) else (
        echo База данных SQLite уже существует.
    )
)

echo.
echo [5/6] Запуск миграций...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo Ошибка при выполнении миграций!
    pause
    exit /b 1
)

echo.
echo [6/6] Установка Node.js зависимостей...
call npm install
if %errorlevel% neq 0 (
    echo Ошибка при установке npm пакетов!
    pause
    exit /b 1
)

echo.
echo ========================================
echo Настройка завершена!
echo ========================================
echo.
echo Следующие шаги:
echo 1. Запустите сборку frontend: npm run build
echo 2. Запустите сервер: php artisan serve
echo 3. Откройте в браузере: http://localhost:8000
echo.
pause

