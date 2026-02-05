@echo off
REM Outputs full path to PHP (first in PATH) or PHP_NOT_FOUND
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo PHP_NOT_FOUND
    exit /b 1
)
for /f "delims=" %%i in ('where php 2^>nul') do (
    echo %%i
    exit /b 0
)
echo PHP_NOT_FOUND
exit /b 1
