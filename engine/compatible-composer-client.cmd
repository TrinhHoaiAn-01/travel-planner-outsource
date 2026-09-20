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

powershell -NoProfile -Command "Expand-Archive -Path \"%ZIP_FILE%\" -DestinationPath \"%PROJECT_DIR%\" -Force"

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