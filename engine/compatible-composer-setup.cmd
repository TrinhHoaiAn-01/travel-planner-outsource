@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo ============================================
echo   Composer 2.10.3 - Global Setup
echo ============================================
echo.

REM ============================================================
REM CONFIGURATION
REM ============================================================

REM Script:
REM   travel-planner\engine\compatible-composer-client.cmd
REM
REM Package:
REM   travel-planner\data\composer.zip
REM
REM Expected ZIP structure:
REM   ComposerSetup\
REM       bin\
REM           composer.bat
REM           composer.phar

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."

for %%I in ("%PROJECT_DIR%") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\composer.zip"

REM Global Composer installation directory
set "COMPOSER_ROOT=C:\ProgramData\ComposerSetup"
set "COMPOSER_BIN=%COMPOSER_ROOT%\bin"
set "COMPOSER_BAT=%COMPOSER_BIN%\composer.bat"
set "COMPOSER_PHAR=%COMPOSER_BIN%\composer.phar"

set "REQUIRED_VERSION=2.10.3"

echo Package:
echo   %ZIP_FILE%
echo.
echo Install location:
echo   %COMPOSER_ROOT%
echo.

REM ============================================================
REM ADMINISTRATOR CHECK
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
REM CHECK PACKAGE
REM ============================================================

if not exist "%ZIP_FILE%" (
    echo [ERROR] composer.zip was not found:
    echo   %ZIP_FILE%
    echo.
    pause
    exit /b 1
)

echo [OK] composer.zip found.
echo.

REM ============================================================
REM CHECK EXISTING GLOBAL COMPOSER
REM ============================================================

if exist "%COMPOSER_BAT%" (
    echo [INFO] Composer already exists:
    echo   %COMPOSER_BAT%
    echo.

    set "CURRENT_VERSION="

    for /f "tokens=3" %%V in ('"%COMPOSER_BAT%" --version 2^>nul') do (
        if not defined CURRENT_VERSION set "CURRENT_VERSION=%%V"
    )

    echo [INFO] Current version: !CURRENT_VERSION!
    echo.

    if "!CURRENT_VERSION!"=="%REQUIRED_VERSION%" (
        echo [OK] Composer %REQUIRED_VERSION% already installed.
        echo [OK] No extraction required.
        echo.
        goto :SET_DEFAULT
    )

    echo [INFO] Existing Composer is not %REQUIRED_VERSION%.
    echo [INFO] Replacing it with the packaged Composer.
    echo.

    rmdir /s /q "%COMPOSER_ROOT%" 2>nul

    if exist "%COMPOSER_ROOT%" (
        echo [ERROR] Cannot remove existing Composer:
        echo   %COMPOSER_ROOT%
        echo.
        pause
        exit /b 1
    )
)

REM ============================================================
REM EXTRACT COMPOSER
REM ============================================================

echo [1/3] Extracting Composer...
echo.
echo Source:
echo   %ZIP_FILE%
echo.
echo Destination:
echo   C:\ProgramData
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"Expand-Archive -LiteralPath '%ZIP_FILE%' -DestinationPath 'C:\ProgramData' -Force"

if errorlevel 1 (
    echo.
    echo [ERROR] Composer extraction failed.
    echo.
    pause
    exit /b 1
)

if not exist "%COMPOSER_BAT%" (
    echo.
    echo [ERROR] composer.bat was not found:
    echo   %COMPOSER_BAT%
    echo.
    echo Expected ZIP structure:
    echo   ComposerSetup\bin\composer.bat
    echo   ComposerSetup\bin\composer.phar
    echo.
    pause
    exit /b 1
)

if not exist "%COMPOSER_PHAR%" (
    echo.
    echo [ERROR] composer.phar was not found:
    echo   %COMPOSER_PHAR%
    echo.
    pause
    exit /b 1
)

echo [OK] Composer extracted.
echo.

REM ============================================================
REM VERIFY VERSION
REM ============================================================

echo [2/3] Verifying Composer version...
echo.

set "VERIFY_VERSION="

for /f "tokens=3" %%V in ('"%COMPOSER_BAT%" --version 2^>nul') do (
    if not defined VERIFY_VERSION set "VERIFY_VERSION=%%V"
)

echo Composer version:
echo   !VERIFY_VERSION!
echo.

if not "!VERIFY_VERSION!"=="%REQUIRED_VERSION%" (
    echo [ERROR] Wrong Composer version.
    echo Expected:
    echo   %REQUIRED_VERSION%
    echo Found:
    echo   !VERIFY_VERSION!
    echo.
    pause
    exit /b 1
)

echo [OK] Composer %REQUIRED_VERSION% verified.
echo.

REM ============================================================
REM SET COMPOSER AS DEFAULT SYSTEM ENVIRONMENT
REM ============================================================

:SET_DEFAULT

echo [3/3] Setting Composer as default environment...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$bin='C:\ProgramData\ComposerSetup\bin'; ^
$p=[Environment]::GetEnvironmentVariable('Path','Machine'); ^
$a=@(); ^
if($p){$a=$p -split ';' | Where-Object {$_ -and ($_ -ne $bin)}}; ^
[Environment]::SetEnvironmentVariable('Path',( @($bin)+$a -join ';'),'Machine')"

if errorlevel 1 (
    echo [ERROR] Failed to update System PATH.
    echo.
    pause
    exit /b 1
)

REM Update PATH of current CMD session
set "PATH=C:\ProgramData\ComposerSetup\bin;%PATH%"

echo [OK] Composer directory added to System PATH.
echo [OK] Composer is now the default Composer.
echo.

REM ============================================================
REM FINAL VERIFICATION
REM ============================================================

echo ============================================
echo   Composer setup completed successfully
echo ============================================
echo.

echo Version:
composer --version
echo.

echo Composer executable:
where composer
echo.

echo Global Composer directory:
echo   C:\ProgramData\ComposerSetup\bin
echo.

echo Environment:
echo   System PATH
echo.

echo Default Composer:
echo   YES
echo.

echo IMPORTANT:
echo Open a NEW CMD window to refresh the
echo environment for other applications.
echo.

pause
