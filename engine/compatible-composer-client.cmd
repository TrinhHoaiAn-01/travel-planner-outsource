@echo off
setlocal EnableExtensions

echo [Composer] Checking environment...

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."
set "DATA_DIR=%PROJECT_DIR%\data"

set "PHP_DIR=C:\php83"
set "PHP_EXE=%PHP_DIR%\php.exe"
set "PHP_SETUP=%ENGINE_DIR%compatible-php-setup.cmd"

set "COMPOSER_BIN=C:\ProgramData\ComposerSetup\bin"
set "COMPOSER_BAT=%COMPOSER_BIN%\composer.bat"
set "COMPOSER_PHAR=%COMPOSER_BIN%\composer.phar"
set "COMPOSER_SETUP=%ENGINE_DIR%compatible-composer-setup.cmd"

set "COMPOSER_ZIP=%DATA_DIR%\compatible-composer.zip"

REM ============================================================
REM Administrator
REM ============================================================

net session >nul 2>&1

if errorlevel 1 (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b 0
)

REM ============================================================
REM PHP
REM ============================================================

echo [Composer] Checking PHP...

if not exist "%PHP_EXE%" (
    echo [Composer] [INFO] PHP not found.
    echo [Composer] [INFO] Running PHP setup...

    if not exist "%PHP_SETUP%" (
        echo [Composer] [ERROR] compatible-php-setup.cmd not found.
        exit /b 1
    )

    call "%PHP_SETUP%"

    if errorlevel 1 (
        echo [Composer] [ERROR] PHP setup failed.
        exit /b 1
    )
)

if not exist "%PHP_EXE%" (
    echo [Composer] [ERROR] PHP environment is not ready.
    exit /b 1
)

set "PATH=%PHP_DIR%;%PATH%"

echo [Composer] [SKIP] PHP already exists.

REM ============================================================
REM Composer
REM ============================================================

echo [Composer] Checking Composer...

if exist "%COMPOSER_BAT%" if exist "%COMPOSER_PHAR%" (
    set "PATH=%COMPOSER_BIN%;%PHP_DIR%;%PATH%"
    echo [Composer] [SKIP] Composer already exists.
) else (
    echo [Composer] [INFO] Composer not found.

    if not exist "%COMPOSER_SETUP%" (
        echo [Composer] [ERROR] compatible-composer-setup.cmd not found.
        exit /b 1
    )

    echo [Composer] [INFO] Running Composer setup...

    call "%COMPOSER_SETUP%"

    if errorlevel 1 (
        echo [Composer] [ERROR] Composer setup failed.
        exit /b 1
    )

    if not exist "%COMPOSER_BAT%" (
        echo [Composer] [ERROR] Composer was not installed.
        exit /b 1
    )

    if not exist "%COMPOSER_PHAR%" (
        echo [Composer] [ERROR] composer.phar was not installed.
        exit /b 1
    )

    set "PATH=%COMPOSER_BIN%;%PHP_DIR%;%PATH%"

    echo [Composer] [OK] Composer installed.
)

REM ============================================================
REM Composer Package
REM ============================================================

echo [Composer] Checking Composer package...

if not exist "%COMPOSER_ZIP%" (
    echo [Composer] [ERROR] compatible-composer.zip not found.
    echo [Composer] [ERROR] Expected:
    echo %COMPOSER_ZIP%
    exit /b 1
)

if exist "%PROJECT_DIR%\vendor\autoload.php" (
    echo [Composer] [SKIP] Composer dependencies already exist.
    echo [Composer] [OK] Environment ready.
    exit /b 0
)

echo [Composer] [INFO] Composer dependencies not found.
echo [Composer] [INFO] Extracting compatible-composer.zip...

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "try { Expand-Archive -Path '%COMPOSER_ZIP%' -DestinationPath '%PROJECT_DIR%' -Force -ErrorAction Stop; exit 0 } catch { exit 1 }"

if errorlevel 1 (
    echo [Composer] [ERROR] Failed to extract compatible-composer.zip.
    exit /b 1
)

REM ============================================================
REM Verify Environment
REM ============================================================

if not exist "%PROJECT_DIR%\vendor\autoload.php" (
    echo [Composer] [ERROR] vendor\autoload.php not found.
    echo [Composer] [ERROR] Composer environment is not ready.
    exit /b 1
)

echo [Composer] [OK] Composer dependencies extracted.
echo [Composer] [OK] Environment ready.

exit /b 0