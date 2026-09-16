@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo ============================================
echo   Composer 2.10.3 - Global Setup
echo ============================================
echo.

REM ============================================================
REM Configuration
REM ============================================================

set "REQUIRED_VERSION=2.10.3"
set "COMPOSER_URL=https://getcomposer.org/download/2.10.3/composer.phar"

REM Official Windows Composer global location
set "COMPOSER_BIN=C:\ProgramData\ComposerSetup\bin"
set "COMPOSER_PHAR=%COMPOSER_BIN%\composer.phar"
set "COMPOSER_BAT=%COMPOSER_BIN%\composer.bat"

set "TEMP_DIR=%TEMP%\composer-2.10.3-setup"
set "TEMP_PHAR=%TEMP_DIR%\composer.phar"

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
REM Check existing Composer
REM ============================================================

set "CURRENT_COMPOSER="

if exist "%COMPOSER_BAT%" (
    set "CURRENT_COMPOSER=%COMPOSER_BAT%"
)

if defined CURRENT_COMPOSER (
    echo [INFO] Composer found at:
    echo        %CURRENT_COMPOSER%
    echo.

    set "CURRENT_VERSION="

    for /f "tokens=3" %%V in ('"%COMPOSER_BAT%" --version 2^>nul') do (
        if not defined CURRENT_VERSION set "CURRENT_VERSION=%%V"
    )

    echo [INFO] Current version: !CURRENT_VERSION!
    echo.

    if "!CURRENT_VERSION!"=="%REQUIRED_VERSION%" (
        echo [OK] Composer %REQUIRED_VERSION% is already installed.
        echo [OK] No installation required.
        goto :VERIFY_PATH
    )

    echo [INFO] Current Composer is not %REQUIRED_VERSION%.
    echo [INFO] Replacing it with Composer %REQUIRED_VERSION%.
    echo.
) else (
    echo [INFO] Global Composer was not found.
    echo [INFO] Installing Composer %REQUIRED_VERSION%.
    echo.
)

REM ============================================================
REM Download Composer
REM ============================================================

echo [1/4] Downloading Composer %REQUIRED_VERSION%...
echo.

if exist "%TEMP_DIR%" (
    rmdir /s /q "%TEMP_DIR%" 2>nul
)

mkdir "%TEMP_DIR%"

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"Invoke-WebRequest -Uri '%COMPOSER_URL%' -OutFile '%TEMP_PHAR%'"

if errorlevel 1 (
    echo.
    echo [ERROR] Failed to download Composer.
    echo.
    rmdir /s /q "%TEMP_DIR%" 2>nul
    pause
    exit /b 1
)

if not exist "%TEMP_PHAR%" (
    echo.
    echo [ERROR] composer.phar was not downloaded.
    echo.
    rmdir /s /q "%TEMP_DIR%" 2>nul
    pause
    exit /b 1
)

echo [OK] Composer downloaded.
echo.

REM ============================================================
REM Create official global Composer directory
REM ============================================================

echo [2/4] Installing to:
echo        %COMPOSER_BIN%
echo.

if not exist "%COMPOSER_BIN%" (
    mkdir "%COMPOSER_BIN%"
)

copy /Y "%TEMP_PHAR%" "%COMPOSER_PHAR%" >nul

if errorlevel 1 (
    echo [ERROR] Failed to install composer.phar.
    rmdir /s /q "%TEMP_DIR%" 2>nul
    pause
    exit /b 1
)

REM ============================================================
REM Create Windows Composer launcher
REM ============================================================

(
    echo @echo off
    echo php "%%~dp0composer.phar" %%*
) > "%COMPOSER_BAT%"

if not exist "%COMPOSER_BAT%" (
    echo [ERROR] Failed to create composer.bat.
    rmdir /s /q "%TEMP_DIR%" 2>nul
    pause
    exit /b 1
)

echo [OK] Composer installed.
echo.

REM ============================================================
REM Add official Composer directory to System PATH
REM ============================================================

echo [3/4] Setting Composer as system default...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$bin='C:\ProgramData\ComposerSetup\bin'; ^
$p=[Environment]::GetEnvironmentVariable('Path','Machine'); ^
$a=@(); ^
if($p){$a=$p -split ';' | Where-Object {$_ -and ($_ -ne $bin)}}; ^
[Environment]::SetEnvironmentVariable('Path',( @($bin)+$a -join ';'),'Machine')"

if errorlevel 1 (
    echo [ERROR] Failed to update System PATH.
    rmdir /s /q "%TEMP_DIR%" 2>nul
    pause
    exit /b 1
)

REM Update current CMD PATH
set "PATH=%COMPOSER_BIN%;%PATH%"

echo [OK] Composer added to System PATH.
echo.

REM ============================================================
REM Verify Composer
REM ============================================================

:VERIFY_PATH

echo [4/4] Verifying Composer...
echo.

set "VERIFY_VERSION="

for /f "tokens=3" %%V in ('"%COMPOSER_BAT%" --version 2^>nul') do (
    if not defined VERIFY_VERSION set "VERIFY_VERSION=%%V"
)

echo Composer version: !VERIFY_VERSION!
echo.

if not "!VERIFY_VERSION!"=="%REQUIRED_VERSION%" (
    echo [ERROR] Composer version verification failed.
    echo Expected: %REQUIRED_VERSION%
    echo Found:    !VERIFY_VERSION!
    echo.
    if exist "%TEMP_DIR%" rmdir /s /q "%TEMP_DIR%" 2>nul
    pause
    exit /b 1
)

echo [OK] Composer %REQUIRED_VERSION% is ready.
echo.

REM ============================================================
REM Remove temporary files
REM ============================================================

if exist "%TEMP_DIR%" (
    rmdir /s /q "%TEMP_DIR%"
)

echo [OK] Temporary files removed.
echo.

echo ============================================
echo   Composer setup completed successfully
echo ============================================
echo.
echo Version:
echo   Composer %REQUIRED_VERSION%
echo.
echo Global location:
echo   %COMPOSER_BIN%
echo.
echo PATH:
echo   System PATH
echo.
echo Default:
echo   YES
echo.
echo ============================================
echo.
echo Open a NEW CMD window and test:
echo.
echo   composer --version
echo   where composer
echo.

pause
