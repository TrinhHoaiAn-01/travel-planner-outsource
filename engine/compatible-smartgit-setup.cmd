@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "INSTALLER=%PROJECT_DIR%\data\smartgit.exe"
set "SMARTGIT_EXE=%LOCALAPPDATA%\Programs\SmartGit\bin\smartgit.exe"

REM ==========================================================
REM CHECK EXISTING SMARTGIT
REM ==========================================================

if exist "%SMARTGIT_EXE%" goto CREATE_SHORTCUT

if exist "%LOCALAPPDATA%\Programs\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_EXE=%LOCALAPPDATA%\Programs\SmartGit\bin\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "%LOCALAPPDATA%\Programs\SmartGit\smartgit.exe" (
    set "SMARTGIT_EXE=%LOCALAPPDATA%\Programs\SmartGit\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files\SmartGit\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files\SmartGit\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files (x86)\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files (x86)\SmartGit\bin\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files (x86)\SmartGit\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files (x86)\SmartGit\smartgit.exe"
    goto CREATE_SHORTCUT
)

REM ==========================================================
REM INSTALL
REM ==========================================================

echo Installing SmartGit...

if not exist "%INSTALLER%" (
    echo Installation Failed
    exit /b 1
)

start "" /wait "%INSTALLER%" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART /DIR="%LOCALAPPDATA%\Programs\SmartGit"

REM ==========================================================
REM DO NOT USE INSTALLER ERRORLEVEL
REM CHECK WHETHER SMARTGIT WAS ACTUALLY INSTALLED
REM ==========================================================

if exist "%LOCALAPPDATA%\Programs\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_EXE=%LOCALAPPDATA%\Programs\SmartGit\bin\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "%LOCALAPPDATA%\Programs\SmartGit\smartgit.exe" (
    set "SMARTGIT_EXE=%LOCALAPPDATA%\Programs\SmartGit\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files\SmartGit\bin\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files\SmartGit\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files\SmartGit\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files (x86)\SmartGit\bin\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files (x86)\SmartGit\bin\smartgit.exe"
    goto CREATE_SHORTCUT
)

if exist "C:\Program Files (x86)\SmartGit\smartgit.exe" (
    set "SMARTGIT_EXE=C:\Program Files (x86)\SmartGit\smartgit.exe"
    goto CREATE_SHORTCUT
)

echo Installation Failed
exit /b 1


REM ==========================================================
REM CREATE DESKTOP SHORTCUT
REM ==========================================================

:CREATE_SHORTCUT

powershell -NoProfile -ExecutionPolicy Bypass -Command "$desktop=[Environment]::GetFolderPath('Desktop'); $shortcut=Join-Path $desktop 'SmartGit.lnk'; $target='%SMARTGIT_EXE%'; $ws=New-Object -ComObject WScript.Shell; $s=$ws.CreateShortcut($shortcut); $s.TargetPath=$target; $s.WorkingDirectory=(Split-Path -Parent $target); $s.IconLocation=$target+',0'; $s.Description='SmartGit'; $s.Save()"

if errorlevel 1 (
    echo Installation Failed
    exit /b 1
)

echo Installed Successfully

exit /b 0