@echo off
setlocal EnableExtensions

set "TARGET_VERSION=26.7.0"

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "INSTALLER=%PROJECT_DIR%\data\mysql-workbench.msi"

REM ==========================================================
REM CHECK IF MYSQL WORKBENCH IS ALREADY INSTALLED
REM ==========================================================

set "WORKBENCH_FOUND="

for /f "delims=" %%A in ('powershell -NoProfile -ExecutionPolicy Bypass -Command "$roots=@('HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*','HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*','HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*'); $apps=Get-ItemProperty $roots -ErrorAction SilentlyContinue; foreach($app in $apps){if($app.DisplayName -like '*MySQL Workbench*'){Write-Output 'FOUND'; exit}}"') do (
    set "WORKBENCH_FOUND=%%A"
)

if defined WORKBENCH_FOUND (
    echo Installed Successfully
    exit /b 0
)

REM ==========================================================
REM CHECK COMMON INSTALL PATHS
REM ==========================================================

if exist "C:\Program Files\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\Program Files\MySQL\MySQL Workbench\MySQLWorkbench.exe" (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\Program Files (x86)\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    echo Installed Successfully
    exit /b 0
)

REM ==========================================================
REM INSTALL
REM ==========================================================

echo Installing MySQL Workbench...

if not exist "%INSTALLER%" (
    echo Installation Failed
    exit /b 1
)

msiexec /i "%INSTALLER%" /qn /norestart

REM ==========================================================
REM VERIFY AFTER INSTALL
REM ==========================================================

set "WORKBENCH_FOUND="

for /f "delims=" %%A in ('powershell -NoProfile -ExecutionPolicy Bypass -Command "$roots=@('HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*','HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*','HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*'); $apps=Get-ItemProperty $roots -ErrorAction SilentlyContinue; foreach($app in $apps){if($app.DisplayName -like '*MySQL Workbench*'){Write-Output 'FOUND'; exit}}"') do (
    set "WORKBENCH_FOUND=%%A"
)

if defined WORKBENCH_FOUND (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\Program Files\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\Program Files\MySQL\MySQL Workbench\MySQLWorkbench.exe" (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\Program Files (x86)\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    echo Installed Successfully
    exit /b 0
)

echo Installation Failed

exit /b 1