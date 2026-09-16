@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Travel Planner - SmartGit 20.2.6

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."
set "INSTALLER=%PROJECT_DIR%\data\smartgit.exe"
set "TARGET_VERSION=20.2.6"

call :require_admin
if errorlevel 1 exit /b 1

if not exist "%INSTALLER%" (
  echo [ERROR] Missing: %INSTALLER%
  exit /b 1
)

call :get_smartgit_version SG_VERSION
echo [SMARTGIT] Detected: !SG_VERSION!

if "!SG_VERSION!"=="%TARGET_VERSION%" (
  echo [SMARTGIT] Exact version %TARGET_VERSION% is already installed. SKIP.
  exit /b 0
)

if defined SG_VERSION (
  echo [SMARTGIT] Wrong version detected. Uninstalling...
  call :uninstall_smartgit
  if errorlevel 1 exit /b 1
)

echo [SMARTGIT] Installing %TARGET_VERSION%...
start "" /wait "%INSTALLER%" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART /SP-
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" (
  echo [ERROR] SmartGit installer returned code %RC%.
  exit /b 1
)

call :wait_for_smartgit
if errorlevel 1 (
  echo [ERROR] SmartGit %TARGET_VERSION% was not verified.
  exit /b 1
)

echo [SMARTGIT] %TARGET_VERSION% verified successfully.
exit /b 0

:require_admin
net session >nul 2>&1
if not errorlevel 1 exit /b 0
echo [INFO] Administrator privileges required. Relaunching elevated...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs -Wait"
exit /b 1

:get_smartgit_version
set "%~1="
for /f "tokens=2,*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"SmartGit"') do (
  for /f "tokens=2,*" %%C in ('reg query "%%A" /v DisplayVersion 2^>nul') do set "%~1=%%D"
)
for /f "tokens=2,*" %%A in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"SmartGit"') do (
  for /f "tokens=2,*" %%C in ('reg query "%%A" /v DisplayVersion 2^>nul') do set "%~1=%%D"
)
exit /b 0

:uninstall_smartgit
for /f "tokens=1,*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"SmartGit"') do (
  call :uninstall_key "%%A"
)
for /f "tokens=1,*" %%A in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"SmartGit"') do (
  call :uninstall_key "%%A"
)
call :get_smartgit_version AFTER
if defined AFTER (
  echo [ERROR] Old SmartGit is still registered.
  exit /b 1
)
exit /b 0

:uninstall_key
set "KEY=%~1"
set "UNINSTALL="
for /f "tokens=2,*" %%A in ('reg query "%KEY%" /v UninstallString 2^>nul') do set "UNINSTALL=%%B"
if defined UNINSTALL (
  echo [SMARTGIT] Running uninstall...
  start "" /wait cmd /c "%UNINSTALL%"
  timeout /t 2 /nobreak >nul
)
exit /b 0

:wait_for_smartgit
for /l %%N in (1,1,30) do (
  call :get_smartgit_version CHECK
  if "!CHECK!"=="%TARGET_VERSION%" exit /b 0
  timeout /t 2 /nobreak >nul
)
exit /b 1
