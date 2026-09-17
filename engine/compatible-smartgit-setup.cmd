@echo off
setlocal EnableExtensions

REM ============================================================
REM SmartGit Setup
REM Required version: 20.2.6
REM ============================================================

set "REQUIRED_VERSION=20.2.6"
set "INSTALLER=%~dp0..\data\smartgit.exe"

REM Installation locations
set "SMARTGIT_EXE=C:\Program Files\SmartGit\bin\smartgit.exe"
set "SMARTGIT_EXE_X86=C:\Program Files (x86)\SmartGit\bin\smartgit.exe"

REM Project marker
set "MARKER=%ProgramData%\TravelPlanner\SmartGit-20.2.6.installed"

echo.
echo [SmartGit] Checking installation...
echo.

REM ============================================================
REM [1/4] Checking SmartGit
REM ============================================================

echo [1/4] Checking SmartGit...

REM ------------------------------------------------------------
REM Find SmartGit executable
REM ------------------------------------------------------------

set "SMARTGIT_PATH="

if exist "%SMARTGIT_EXE%" (
    set "SMARTGIT_PATH=%SMARTGIT_EXE%"
    goto CHECK_MARKER
)

if exist "%SMARTGIT_EXE_X86%" (
    set "SMARTGIT_PATH=%SMARTGIT_EXE_X86%"
    goto CHECK_MARKER
)

if exist "%LOCALAPPDATA%\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_PATH=%LOCALAPPDATA%\SmartGit\bin\smartgit.exe"
    goto CHECK_MARKER
)

if exist "%LOCALAPPDATA%\syntevo\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_PATH=%LOCALAPPDATA%\syntevo\SmartGit\bin\smartgit.exe"
    goto CHECK_MARKER
)

echo [SmartGit] [INFO] SmartGit was not found.
goto INSTALL


REM ============================================================
REM Check marker
REM ============================================================

:CHECK_MARKER

echo [SmartGit] [INFO] Found:
echo [SmartGit] %SMARTGIT_PATH%

if exist "%MARKER%" (
    echo [SmartGit] [INFO] Required version marker found.
    echo [SmartGit] [INFO] Required version: %REQUIRED_VERSION%
    echo [SmartGit] [SKIP] Correct version already installed.
    goto CREATE_SHORTCUT
)

REM ============================================================
REM No marker
REM ============================================================

echo [SmartGit] [INFO] SmartGit is installed.
echo [SmartGit] [INFO] Required version marker was not found.
echo [SmartGit] [INFO] Installing required version...
echo.

goto INSTALL


REM ============================================================
REM [3/4] Install SmartGit
REM ============================================================

:INSTALL

if not exist "%INSTALLER%" (
    echo [SmartGit] [ERROR] Installer not found.
    echo [SmartGit] %INSTALLER%
    exit /b 1
)

echo [SmartGit] [INFO] Installing SmartGit %REQUIRED_VERSION%...
echo [SmartGit] [INFO] Silent installation...

start /wait "" "%INSTALLER%" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART

set "INSTALL_RESULT=%ERRORLEVEL%"

echo [SmartGit] [INFO] Installer exit code: %INSTALL_RESULT%

if not "%INSTALL_RESULT%"=="0" (
    echo [SmartGit] [ERROR] SmartGit installation failed.
    exit /b 1
)

echo [SmartGit] [OK] Installer completed.

timeout /t 2 /nobreak >nul


REM ============================================================
REM Find SmartGit after installation
REM ============================================================

set "SMARTGIT_PATH="

if exist "%SMARTGIT_EXE%" (
    set "SMARTGIT_PATH=%SMARTGIT_EXE%"
    goto CREATE_MARKER
)

if exist "%SMARTGIT_EXE_X86%" (
    set "SMARTGIT_PATH=%SMARTGIT_EXE_X86%"
    goto CREATE_MARKER
)

if exist "%LOCALAPPDATA%\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_PATH=%LOCALAPPDATA%\SmartGit\bin\smartgit.exe"
    goto CREATE_MARKER
)

if exist "%LOCALAPPDATA%\syntevo\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_PATH=%LOCALAPPDATA%\syntevo\SmartGit\bin\smartgit.exe"
    goto CREATE_MARKER
)

echo [SmartGit] [ERROR] smartgit.exe not found after installation.
exit /b 1


REM ============================================================
REM Create version marker
REM ============================================================

:CREATE_MARKER

echo [SmartGit] [INFO] Creating installation marker...

if not exist "%ProgramData%\TravelPlanner" (
    mkdir "%ProgramData%\TravelPlanner"
)

echo %REQUIRED_VERSION%> "%MARKER%"

if not exist "%MARKER%" (
    echo [SmartGit] [ERROR] Could not create installation marker.
    exit /b 1
)

echo [SmartGit] [OK] SmartGit %REQUIRED_VERSION% verified.
echo.


REM ============================================================
REM [4/4] Create Desktop shortcut
REM ============================================================

:CREATE_SHORTCUT

echo [4/4] Creating Desktop shortcut...

if not defined SMARTGIT_PATH (
    echo [SmartGit] [ERROR] SmartGit executable not found.
    exit /b 1
)

set "DESKTOP=%USERPROFILE%\Desktop"
set "SHORTCUT=%DESKTOP%\SmartGit.lnk"

powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$shell=New-Object -ComObject WScript.Shell; $shortcut=$shell.CreateShortcut('%SHORTCUT%'); $shortcut.TargetPath='%SMARTGIT_PATH%'; $shortcut.WorkingDirectory=Split-Path '%SMARTGIT_PATH%'; $shortcut.IconLocation='%SMARTGIT_PATH%,0'; $shortcut.Save()"

if errorlevel 1 (
    echo [SmartGit] [ERROR] Failed to create Desktop shortcut.
    exit /b 1
)

if not exist "%SHORTCUT%" (
    echo [SmartGit] [ERROR] Desktop shortcut was not created.
    exit /b 1
)

echo [SmartGit] [OK] Desktop shortcut created.
echo.
echo [SmartGit] [OK] SmartGit %REQUIRED_VERSION% setup completed.
echo.

exit /b 0