@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "OUTPUT=%PROJECT_DIR%\data\compatible-composer.zip"

echo Checking Composer package...

if not exist "%PROJECT_DIR%\.env" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\composer.json" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\composer.lock" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\vendor\autoload.php" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\database\migrations" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\database\seeders" (
    echo Failed Composer package
    exit /b 1
)

echo Creating compatible-composer.zip...

if exist "%OUTPUT%" (
    del /f /q "%OUTPUT%" >nul 2>&1
)

tar -a -c -f "%OUTPUT%" ^
    -C "%PROJECT_DIR%" ^
    .env ^
    composer.json ^
    composer.lock ^
    vendor ^
    database\migrations ^
    database\seeders

if errorlevel 1 (
    echo Failed Composer package
    exit /b 1
)

if not exist "%OUTPUT%" (
    echo Failed Composer package
    exit /b 1
)

echo Created compatible-composer.zip

exit /b 0