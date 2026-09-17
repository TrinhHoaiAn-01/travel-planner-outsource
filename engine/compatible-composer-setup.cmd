@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo.
echo [Composer] Checking environment...

REM ============================================================
REM PATHS
REM ============================================================

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."
for %%I in ("%PROJECT_DIR%") do set "PROJECT_DIR=%%~fI"

set "PHP_DIR=C:\php83"
set "PHP_EXE=%PHP_DIR%\php.exe"
set "PHP_SETUP=%ENGINE_DIR%compatible-php-setup.cmd"

set "COMPOSER_ZIP=%PROJECT_DIR%\data\composer.zip"
set "COMPOSER_ROOT=C:\ProgramData\ComposerSetup"
set "COMPOSER_BIN=%COMPOSER_ROOT%\bin"
set "COMPOSER_BAT=%COMPOSER_BIN%\composer.bat"
set "COMPOSER_PHAR=%COMPOSER_BIN%\composer.phar"

REM ============================================================
REM ADMINISTRATOR
REM ============================================================

net session >nul 2>&1
if errorlevel 1 (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b 0
)

REM ============================================================
REM PHP
REM ============================================================

echo [Composer] Checking PHP environment...

if not exist "%PHP_EXE%" (
    echo [Composer] [INFO] PHP environment not found.
    echo [Composer] [INFO] Running compatible-php-setup.cmd...

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
echo [Composer] [OK] PHP environment ready.

REM ============================================================
REM COMPOSER
REM ============================================================

echo [Composer] Checking Composer...

if exist "%COMPOSER_BAT%" (
    echo [Composer] [OK] Composer environment ready.
    set "PATH=%COMPOSER_BIN%;%PHP_DIR%;%PATH%"
    exit /b 0
)

echo [Composer] [INFO] Composer not found. Installing...

if not exist "%COMPOSER_ZIP%" (
    echo [Composer] [ERROR] composer.zip not found.
    exit /b 1
)

if exist "%COMPOSER_ROOT%" rmdir /s /q "%COMPOSER_ROOT%" >nul 2>&1

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "try { Expand-Archive -LiteralPath '%COMPOSER_ZIP%' -DestinationPath 'C:\ProgramData' -Force -ErrorAction Stop; exit 0 } catch { exit 1 }"

if errorlevel 1 (
    echo [Composer] [ERROR] Composer installation failed.
    exit /b 1
)

if not exist "%COMPOSER_BAT%" (
    echo [Composer] [ERROR] Composer installation failed.
    exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$bin='C:\ProgramData\ComposerSetup\bin'; $p=[Environment]::GetEnvironmentVariable('Path','Machine'); if($null -eq $p){$p=''}; $items=$p -split ';' | Where-Object { $_ -and ($_.Trim().TrimEnd('\') -ine $bin.TrimEnd('\')) }; [Environment]::SetEnvironmentVariable('Path',((@($bin)+$items)-join ';'),'Machine')"

if errorlevel 1 (
    echo [Composer] [ERROR] Failed to configure PATH.
    exit /b 1
)

set "PATH=%COMPOSER_BIN%;%PHP_DIR%;%PATH%"

echo [Composer] [OK] Composer environment ready.
exit /b 0
