@echo off
setlocal

title Travel Planner - Composer Client

echo.
echo ==========================================
echo   Extract Composer Package
echo ==========================================
echo.

REM Project root = parent folder of engine
set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

REM Composer package
set "ZIP_FILE=%PROJECT_DIR%\data\compatible-composer.zip"

echo Project: %PROJECT_DIR%
echo Package: %ZIP_FILE%
echo.

REM Check ZIP
if not exist "%ZIP_FILE%" (
    echo ERROR: compatible-composer.zip not found.
    echo.
    exit /b 1
)

echo Extracting compatible-composer.zip...
echo.

tar -xf "%ZIP_FILE%" -C "%PROJECT_DIR%"

if errorlevel 1 (
    echo.
    echo ERROR: Failed to extract compatible-composer.zip.
    echo.
    exit /b 1
)

echo.
echo ==========================================
echo   Extraction completed
echo ==========================================
echo.

exit /b 0