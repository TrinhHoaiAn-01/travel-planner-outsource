@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "INSTALLER=%PROJECT_DIR%\data\wampserver.exe"

REM ==========================================================
REM CHECK EXISTING WAMPSERVER
REM ==========================================================

if exist "C:\wamp64\wampmanager.exe" (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\wamp\wampmanager.exe" (
    echo Installed Successfully
    exit /b 0
)

REM ==========================================================
REM INSTALL
REM ==========================================================

echo Installing WampServer...

if not exist "%INSTALLER%" (
    echo Installation Failed
    exit /b 1
)

start "" /wait "%INSTALLER%" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART

REM ==========================================================
REM VERIFY INSTALLATION
REM ==========================================================

if exist "C:\wamp64\wampmanager.exe" (
    echo Installed Successfully
    exit /b 0
)

if exist "C:\wamp\wampmanager.exe" (
    echo Installed Successfully
    exit /b 0
)

echo Installation Failed

exit /b 1