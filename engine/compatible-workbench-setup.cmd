@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Travel Planner - MySQL Workbench 26.7.0

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."
set "INSTALLER=%PROJECT_DIR%\data\mysql-workbench.msi"
set "TARGET_VERSION=26.7.0"

call :require_admin
if errorlevel 1 exit /b 1

if not exist "%INSTALLER%" (
  echo [ERROR] Missing: %INSTALLER%
  exit /b 1
)

call :get_workbench_version WB_VERSION
echo [WORKBENCH] Detected: !WB_VERSION!

if "!WB_VERSION!"=="%TARGET_VERSION%" (
  echo [WORKBENCH] Exact version %TARGET_VERSION% is already installed. SKIP.
  exit /b 0
)

if defined WB_VERSION (
  echo [WORKBENCH] Wrong version detected. Uninstalling...
  call :uninstall_workbench
  if errorlevel 1 exit /b 1
)

echo [WORKBENCH] Installing %TARGET_VERSION%...
msiexec.exe /i "%INSTALLER%" /qn /norestart
set "RC=%ERRORLEVEL%"
if "%RC%"=="3010" set "RC=0"
if not "%RC%"=="0" (
  echo [ERROR] Workbench MSI returned code %ERRORLEVEL%.
  exit /b 1
)

call :wait_for_workbench
if errorlevel 1 (
  echo [ERROR] MySQL Workbench %TARGET_VERSION% was not verified.
  exit /b 1
)

echo [WORKBENCH] %TARGET_VERSION% verified successfully.
exit /b 0

:require_admin
net session >nul 2>&1
if not errorlevel 1 exit /b 0
echo [INFO] Administrator privileges required. Relaunching elevated...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs -Wait"
exit /b 1

:get_workbench_version
set "%~1="
for /f "tokens=2,*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"MySQL Workbench"') do (
  for /f "tokens=2,*" %%C in ('reg query "%%A" /v DisplayVersion 2^>nul') do set "%~1=%%D"
)
for /f "tokens=2,*" %%A in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"MySQL Workbench"') do (
  for /f "tokens=2,*" %%C in ('reg query "%%A" /v DisplayVersion 2^>nul') do set "%~1=%%D"
)
exit /b 0

:uninstall_workbench
for /f "tokens=1,*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"MySQL Workbench"') do (
  set "KEY=%%A"
  call :uninstall_key "%%A"
)
for /f "tokens=1,*" %%A in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"MySQL Workbench"') do (
  call :uninstall_key "%%A"
)
call :get_workbench_version AFTER
if defined AFTER (
  echo [ERROR] Old Workbench is still registered.
  exit /b 1
)
exit /b 0

:uninstall_key
set "KEY=%~1"
set "UNINSTALL="
for /f "tokens=2,*" %%A in ('reg query "%KEY%" /v UninstallString 2^>nul') do set "UNINSTALL=%%B"
if defined UNINSTALL (
  echo [WORKBENCH] Running uninstall...
  start "" /wait cmd /c "%UNINSTALL%"
  timeout /t 2 /nobreak >nul
)
exit /b 0

:wait_for_workbench
for /l %%N in (1,1,30) do (
  call :get_workbench_version CHECK
  if "!CHECK!"=="%TARGET_VERSION%" exit /b 0
  timeout /t 2 /nobreak >nul
)
exit /b 1
