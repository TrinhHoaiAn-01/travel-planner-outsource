@echo off
setlocal EnableExtensions

REM ============================================================
REM MySQL Workbench Setup
REM Required version: 26.7.0 ONLY
REM ============================================================

set "REQUIRED_VERSION=26.7.0"
set "ENGINE_DIR=%~dp0"
set "DATA_DIR=%ENGINE_DIR%..\data"
set "INSTALLER=%DATA_DIR%\mysql-workbench.msi"

echo.
echo [MySQL Workbench] Checking installation...
echo.

REM ============================================================
REM [1/4] Find installed MySQL Workbench
REM ============================================================

echo [1/4] Checking MySQL Workbench...

set "WORKBENCH_EXE="

if exist "C:\Program Files\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe"
    goto CHECK_VERSION
)

if exist "C:\Program Files\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe"
    goto CHECK_VERSION
)

if exist "C:\Program Files (x86)\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files (x86)\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe"
    goto CHECK_VERSION
)

if exist "C:\Program Files (x86)\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files (x86)\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe"
    goto CHECK_VERSION
)

goto INSTALL

REM ============================================================
REM [2/4] Check installed version
REM ============================================================

:CHECK_VERSION

set "INSTALLED_VERSION="

for /f "delims=" %%V in ('powershell.exe -NoProfile -Command "(Get-Item '%WORKBENCH_EXE%').VersionInfo.ProductVersion"') do (
    set "INSTALLED_VERSION=%%V"
)

echo [MySQL Workbench] [INFO] Installed version: %INSTALLED_VERSION%
echo [MySQL Workbench] [INFO] Required version: %REQUIRED_VERSION%

if "%INSTALLED_VERSION%"=="%REQUIRED_VERSION%" (
    echo [MySQL Workbench] [SKIP] Correct version already installed.
    goto CREATE_SHORTCUT
)

echo [MySQL Workbench] [INFO] Wrong version detected.
echo [MySQL Workbench] [INFO] Installing required version...
echo.


REM ============================================================
REM [3/4] Install MySQL Workbench
REM ============================================================

:INSTALL

if not exist "%INSTALLER%" (
    echo [MySQL Workbench] [ERROR] Installer not found.
    echo [MySQL Workbench] %INSTALLER%
    exit /b 1
)

echo [MySQL Workbench] [INFO] Installing MySQL Workbench %REQUIRED_VERSION%...
echo [MySQL Workbench] [INFO] Silent installation...

pushd "%DATA_DIR%"

if errorlevel 1 (
    echo [MySQL Workbench] [ERROR] Could not access data directory.
    exit /b 1
)

msiexec.exe /i "mysql-workbench.msi" /qn /norestart

set "INSTALL_RESULT=%ERRORLEVEL%"

popd

echo [MySQL Workbench] [INFO] Installer exit code: %INSTALL_RESULT%

if "%INSTALL_RESULT%"=="0" goto INSTALL_OK

if "%INSTALL_RESULT%"=="3010" goto INSTALL_OK

echo [MySQL Workbench] [ERROR] Installer returned error code %INSTALL_RESULT%.
exit /b 1


:INSTALL_OK

echo [MySQL Workbench] [OK] Installer completed.

timeout /t 2 /nobreak >nul


REM ============================================================
REM Find MySQL Workbench after installation
REM ============================================================

set "WORKBENCH_EXE="

if exist "C:\Program Files\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe"
    goto VERIFY_VERSION
)

if exist "C:\Program Files\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe"
    goto VERIFY_VERSION
)

if exist "C:\Program Files (x86)\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files (x86)\MySQL\MySQL Workbench 8.0 CE\MySQLWorkbench.exe"
    goto VERIFY_VERSION
)

if exist "C:\Program Files (x86)\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe" (
    set "WORKBENCH_EXE=C:\Program Files (x86)\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe"
    goto VERIFY_VERSION
)

echo [MySQL Workbench] [ERROR] MySQLWorkbench.exe not found after installation.
exit /b 1


REM ============================================================
REM Verify version
REM ============================================================

:VERIFY_VERSION

set "INSTALLED_VERSION="

for /f "delims=" %%V in ('powershell.exe -NoProfile -Command "(Get-Item '%WORKBENCH_EXE%').VersionInfo.ProductVersion"') do (
    set "INSTALLED_VERSION=%%V"
)

echo [MySQL Workbench] [INFO] Installed version: %INSTALLED_VERSION%

if not "%INSTALLED_VERSION%"=="%REQUIRED_VERSION%" (
    echo [MySQL Workbench] [ERROR] Version verification failed.
    echo [MySQL Workbench] [ERROR] Required version: %REQUIRED_VERSION%
    exit /b 1
)

echo [MySQL Workbench] [OK] Version %REQUIRED_VERSION% verified.


REM ============================================================
REM [4/4] Create Desktop shortcut
REM ============================================================

:CREATE_SHORTCUT

echo.
echo [4/4] Creating Desktop shortcut...

if not defined WORKBENCH_EXE (
    echo [MySQL Workbench] [ERROR] MySQLWorkbench.exe not found.
    exit /b 1
)

set "DESKTOP=%USERPROFILE%\Desktop"
set "SHORTCUT=%DESKTOP%\MySQL Workbench.lnk"

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$shell=New-Object -ComObject WScript.Shell; $shortcut=$shell.CreateShortcut('%SHORTCUT%'); $shortcut.TargetPath='%WORKBENCH_EXE%'; $shortcut.WorkingDirectory=Split-Path '%WORKBENCH_EXE%'; $shortcut.IconLocation='%WORKBENCH_EXE%,0'; $shortcut.Save()"

if errorlevel 1 (
    echo [MySQL Workbench] [ERROR] Failed to create Desktop shortcut.
    exit /b 1
)

if not exist "%SHORTCUT%" (
    echo [MySQL Workbench] [ERROR] Desktop shortcut was not created.
    exit /b 1
)

echo [MySQL Workbench] [OK] Desktop shortcut created.
echo.
echo [MySQL Workbench] [OK] MySQL Workbench %REQUIRED_VERSION% setup completed.
echo.

exit /b 0