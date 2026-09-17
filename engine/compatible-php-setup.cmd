@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo.
echo [PHP] Checking environment...

REM ============================================================
REM PATHS
REM ============================================================

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."
for %%I in ("%PROJECT_DIR%") do set "PROJECT_DIR=%%~fI"

set "PHP_DIR=C:\php83"
set "PHP_EXE=%PHP_DIR%\php.exe"

set "PHP_ZIP=%PROJECT_DIR%\data\php.zip"
set "RUNTIME_EXE=%PROJECT_DIR%\data\vs-runtime.exe"

set "PHP_URL=https://windows.php.net/downloads/releases/archives/php-8.3.33-nts-Win32-vs16-x64.zip"
set "PHP_DOWNLOAD=%TEMP%\travel-planner-php.zip"

REM ============================================================
REM ADMINISTRATOR
REM ============================================================

net session >nul 2>&1
if errorlevel 1 (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b 0
)

REM ============================================================
REM VISUAL C++ RUNTIME
REM ============================================================

echo [PHP] Checking Visual C++ Runtime...

set "RUNTIME_INSTALLED="
for /f "delims=" %%R in ('powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$v=Get-ItemPropertyValue -Path 'HKLM:\SOFTWARE\Microsoft\VisualStudio\14.0\VC\Runtimes\x64' -Name Installed -ErrorAction SilentlyContinue; if($v -eq 1){'YES'}else{'NO'}"') do set "RUNTIME_INSTALLED=%%R"

if /I "!RUNTIME_INSTALLED!"=="YES" (
    echo [PHP] [OK] Runtime ready.
) else (
    echo [PHP] [INFO] Runtime not found. Installing...

    if not exist "%RUNTIME_EXE%" (
        echo [PHP] [ERROR] vs-runtime.exe not found.
        exit /b 1
    )

    start /wait "" "%RUNTIME_EXE%" /install /quiet /norestart

    set "RUNTIME_INSTALLED="
    for /f "delims=" %%R in ('powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$v=Get-ItemPropertyValue -Path 'HKLM:\SOFTWARE\Microsoft\VisualStudio\14.0\VC\Runtimes\x64' -Name Installed -ErrorAction SilentlyContinue; if($v -eq 1){'YES'}else{'NO'}"') do set "RUNTIME_INSTALLED=%%R"

    if /I not "!RUNTIME_INSTALLED!"=="YES" (
        echo [PHP] [ERROR] Runtime installation failed.
        exit /b 1
    )

    echo [PHP] [OK] Runtime ready.
)

REM ============================================================
REM PHP
REM ============================================================

echo [PHP] Checking PHP environment...

if exist "%PHP_EXE%" (
    echo [PHP] [OK] PHP environment ready.
    set "PATH=%PHP_DIR%;%PATH%"
    exit /b 0
)

echo [PHP] [INFO] PHP not found. Installing...

if exist "%PHP_ZIP%" (
    set "PHP_SOURCE=%PHP_ZIP%"
) else (
    echo [PHP] [INFO] php.zip not found. Downloading PHP...

    del "%PHP_DOWNLOAD%" >nul 2>&1

    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "try { Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_DOWNLOAD%' -UseBasicParsing -ErrorAction Stop; exit 0 } catch { exit 1 }"

    if errorlevel 1 (
        echo [PHP] [ERROR] PHP download failed.
        exit /b 1
    )

    set "PHP_SOURCE=%PHP_DOWNLOAD%"
)

if exist "%PHP_DIR%" rmdir /s /q "%PHP_DIR%" >nul 2>&1

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "try { Expand-Archive -LiteralPath '%PHP_SOURCE%' -DestinationPath 'C:\' -Force -ErrorAction Stop; exit 0 } catch { exit 1 }"

if errorlevel 1 (
    echo [PHP] [ERROR] PHP installation failed.
    exit /b 1
)

if not exist "%PHP_EXE%" (
    echo [PHP] [ERROR] PHP installation failed.
    exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$php='C:\php83'; $p=[Environment]::GetEnvironmentVariable('Path','Machine'); if($null -eq $p){$p=''}; $items=$p -split ';' | Where-Object { $_ -and ($_.Trim().TrimEnd('\') -ine $php.TrimEnd('\')) }; [Environment]::SetEnvironmentVariable('Path',((@($php)+$items)-join ';'),'Machine')"

if errorlevel 1 (
    echo [PHP] [ERROR] Failed to configure PATH.
    exit /b 1
)

set "PATH=%PHP_DIR%;%PATH%"
del "%PHP_DOWNLOAD%" >nul 2>&1

echo [PHP] [OK] PHP environment ready.
exit /b 0
