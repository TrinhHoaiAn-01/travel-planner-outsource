@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo ============================================
echo   Travel Planner - PHP 8.3.33 Setup
echo ============================================
echo.

REM ============================================================
REM Configuration
REM ============================================================

REM Script:
REM   travel-planner\engine\compatible-php-setup.cmd
REM
REM ZIP:
REM   travel-planner\data\php.zip
REM
REM ZIP content:
REM   php83\
REM       php.exe
REM       php.ini
REM       ext\
REM       ...

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."

for %%I in ("%PROJECT_DIR%") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\php.zip"

REM PHP is always installed globally here
set "PHP_DIR=C:\php83"
set "PHP_EXE=%PHP_DIR%\php.exe"

echo PHP package:
echo %ZIP_FILE%
echo.
echo Install location:
echo %PHP_DIR%
echo.

REM ============================================================
REM Administrator check
REM ============================================================

net session >nul 2>&1

if errorlevel 1 (
    echo [INFO] Administrator permission is required.
    echo [INFO] Requesting Administrator permission...
    echo.

    powershell -NoProfile -ExecutionPolicy Bypass -Command ^
    "Start-Process -FilePath '%~f0' -Verb RunAs"

    exit /b 0
)

echo [OK] Administrator permission confirmed.
echo.

REM ============================================================
REM Check PHP ZIP
REM ============================================================

if not exist "%ZIP_FILE%" (
    echo [ERROR] php.zip not found:
    echo %ZIP_FILE%
    echo.
    pause
    exit /b 1
)

echo [OK] php.zip found.
echo.

REM ============================================================
REM Check existing PHP
REM ============================================================

if exist "%PHP_EXE%" (
    echo [INFO] PHP already exists:
    echo        %PHP_EXE%
    echo.

    set "CURRENT_VERSION="

    for /f "tokens=2" %%V in ('"%PHP_EXE%" -r "echo PHP_VERSION;" 2^>nul') do (
        set "CURRENT_VERSION=%%V"
    )

    if not defined CURRENT_VERSION (
        for /f "delims=" %%V in ('"%PHP_EXE%" --version 2^>nul') do (
            set "VERSION_LINE=%%V"
            goto :VERSION_FOUND
        )
    )

    :VERSION_FOUND

    "%PHP_EXE%" --version

    echo.

    "%PHP_EXE%" --version | findstr /C:"PHP 8.3.33" >nul

    if not errorlevel 1 (
        echo [OK] PHP 8.3.33 already installed.
        echo [INFO] Skipping extraction.
        echo.
        goto :SET_PATH
    )

    echo [INFO] Existing PHP is not version 8.3.33.
    echo [INFO] Replacing it with the packaged PHP 8.3.33.
    echo.

    rmdir /s /q "%PHP_DIR%"

    if exist "%PHP_DIR%" (
        echo [ERROR] Cannot remove existing:
        echo %PHP_DIR%
        echo.
        pause
        exit /b 1
    )
)

REM ============================================================
REM Extract PHP to C:\
REM ============================================================

echo [1/3] Extracting PHP 8.3.33...
echo.
echo Source:
echo %ZIP_FILE%
echo.
echo Destination:
echo C:\
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"Expand-Archive -LiteralPath '%ZIP_FILE%' -DestinationPath 'C:\' -Force"

if errorlevel 1 (
    echo.
    echo [ERROR] PHP extraction failed.
    echo.
    pause
    exit /b 1
)

if not exist "%PHP_EXE%" (
    echo.
    echo [ERROR] php.exe was not found after extraction:
    echo %PHP_EXE%
    echo.
    echo Check the internal structure of php.zip.
    echo Expected:
    echo php83\php.exe
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] PHP extracted to C:\php83
echo.

REM ============================================================
REM Verify PHP version
REM ============================================================

echo [2/3] Verifying PHP...
echo.

"%PHP_EXE%" --version

if errorlevel 1 (
    echo.
    echo [ERROR] PHP cannot be executed.
    echo.
    pause
    exit /b 1
)

"%PHP_EXE%" --version | findstr /C:"PHP 8.3.33" >nul

if errorlevel 1 (
    echo.
    echo [ERROR] PHP version is not 8.3.33.
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] PHP 8.3.33 verified.
echo.

REM ============================================================
REM Set PHP as default System PATH
REM ============================================================

:SET_PATH

echo [3/3] Setting PHP as default System PATH...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$php='C:\php83'; ^
$p=[Environment]::GetEnvironmentVariable('Path','Machine'); ^
$a=@(); ^
if($p){$a=$p -split ';' | Where-Object {$_ -and ($_ -ne $php)}}; ^
[Environment]::SetEnvironmentVariable('Path',( @($php)+$a -join ';'),'Machine')"

if errorlevel 1 (
    echo.
    echo [ERROR] Failed to update System PATH.
    echo.
    pause
    exit /b 1
)

REM Update current CMD PATH
set "PATH=C:\php83;%PATH%"

echo [OK] C:\php83 added to the beginning of System PATH.
echo.

REM ============================================================
REM Final verification
REM ============================================================

echo ============================================
echo   PHP setup completed successfully
echo ============================================
echo.

echo PHP location:
echo   C:\php83\php.exe
echo.

echo PHP version:
php --version
echo.

echo PHP executable priority:
where php
echo.

echo Default PHP:
echo   YES - C:\php83
echo.
echo IMPORTANT:
echo Open a NEW CMD window before using PHP
echo from other applications.
echo.

pause
