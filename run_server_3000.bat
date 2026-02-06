@echo off
cd /d "%~dp0"
REM Если нужен конкретный PHP (например C:\laragon\bin\php\php-8.2-Win32\php.exe), задай его здесь:
if not defined PHP set PHP=php
if not exist "artisan" (
    echo ERROR: Run this from project root folder: c:\projects\freeringtones.ru
    echo (artisan not found here)
    pause
    exit /b 1
)
if not exist "vendor\autoload.php" (
    echo ERROR: Run "composer install" first in this folder.
    pause
    exit /b 1
)
echo Starting Laravel on http://localhost:3000 (document root = public)
"%PHP%" -S localhost:3000 -t public
pause
