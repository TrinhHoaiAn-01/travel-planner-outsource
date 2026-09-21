@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\compatible-composer.zip"

echo Checking Composer package...

if not exist "%ZIP_FILE%" (
    echo Skipped Composer package setup because %ZIP_FILE% is missing.
    exit /b 1
)

for %%A in ("%ZIP_FILE%") do if %%~zA LSS 1024 (
    echo Skipped Composer package setup because %ZIP_FILE% is corrupted.
    exit /b 1
)

echo Installing Composer package...

powershell -NoProfile -Command "$zip='%ZIP_FILE%'; $dest='%PROJECT_DIR%'; Expand-Archive -Path $zip -DestinationPath $dest -Force"

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