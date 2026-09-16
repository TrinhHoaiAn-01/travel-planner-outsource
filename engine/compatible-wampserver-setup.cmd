@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Travel Planner - WampServer 3.4.0

set "ENGINE_DIR=%~dp0"
set "PROJECT_DIR=%ENGINE_DIR%.."
set "INSTALLER=%PROJECT_DIR%\data\wampserver.exe"
set "TARGET_VERSION=3.4.0"

call :require_admin
if errorlevel 1 exit /b 1

if not exist "%INSTALLER%" (
  echo [ERROR] Missing: %INSTALLER%
  exit /b 1
)

echo.
echo [WAMP] Checking installed version...
call :get_wamp_version WAMP_VERSION
echo [WAMP] Detected: !WAMP_VERSION!

if "!WAMP_VERSION!"=="%TARGET_VERSION%" (
  echo [WAMP] Exact version %TARGET_VERSION% is already installed. SKIP.
  exit /b 0
)

if defined WAMP_VERSION (
  echo [WAMP] Wrong version detected. Uninstalling before installation...
  call :uninstall_wamp
  if errorlevel 1 exit /b 1
)

echo [WAMP] Installing WampServer %TARGET_VERSION%...
start "" /wait "%INSTALLER%" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART /DIR="C:\wamp"
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" (
  echo [ERROR] WampServer installer returned code %RC%.
  exit /b 1
)

call :wait_for_wamp
if errorlevel 1 (
  echo [ERROR] WampServer %TARGET_VERSION% was not verified after installation.
  exit /b 1
)

echo [WAMP] WampServer %TARGET_VERSION% verified successfully.
echo [WAMP] NOTE: This does not make WampServer's bundled PHP/MySQL the
echo [WAMP] canonical runtime. The project PHP/MySQL installers remain separate.
exit /b 0

:require_admin
net session >nul 2>&1
if not errorlevel 1 exit /b 0
echo [INFO] Administrator privileges required. Relaunching elevated...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs -Wait"
exit /b 1

:get_wamp_version
set "%~1="
for /f "tokens=2,*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"WampServer"') do (
  set "NAME=%%B"
  for /f "tokens=2,*" %%C in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\%%A" /v DisplayVersion 2^>nul') do set "%~1=%%D"
)
for /f "tokens=2,*" %%A in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"WampServer"') do (
  for /f "tokens=2,*" %%C in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\%%A" /v DisplayVersion 2^>nul') do set "%~1=%%D"
)
exit /b 0

:uninstall_wamp
for /f "tokens=1,*" %%A in ('reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"WampServer"') do (
  set "KEY=%%A"
  call :uninstall_key "%%A"
)
for /f "tokens=1,*" %%A in ('reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"WampServer"') do (
  call :uninstall_key "%%A"
)
if exist "C:\wamp" (
  echo [WAMP] Removing remaining C:\wamp...
  rmdir /s /q "C:\wamp"
)
call :get_wamp_version AFTER_VERSION
if defined AFTER_VERSION (
  echo [ERROR] Old WampServer is still registered.
  exit /b 1
)
exit /b 0

:uninstall_key
set "KEY=%~1"
set "UNINSTALL="
for /f "tokens=2,*" %%A in ('reg query "%KEY%" /v UninstallString 2^>nul') do set "UNINSTALL=%%B"
if defined UNINSTALL (
  echo [WAMP] Running uninstall...
  start "" /wait cmd /c "%UNINSTALL%"
  timeout /t 2 /nobreak >nul
)
exit /b 0

:wait_for_wamp
for /l %%N in (1,1,30) do (
  call :get_wamp_version CHECK_VERSION
  if "!CHECK_VERSION!"=="%TARGET_VERSION%" exit /b 0
  timeout /t 2 /nobreak >nul
)
exit /b 1
