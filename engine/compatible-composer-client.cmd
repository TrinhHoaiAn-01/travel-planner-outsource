@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\compatible-composer.zip"

echo Checking Composer package...

if not exist "%ZIP_FILE%" (
    echo Failed Composer package
    exit /b 1
)

echo Installing Composer package...

tar -xf "%ZIP_FILE%" -C "%PROJECT_DIR%" >nul 2>&1

if errorlevel 1 (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\vendor\autoload.php" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\.env" (
    echo Failed Composer package
    exit /b 1
)

echo Installed Composer package

exit /b 0