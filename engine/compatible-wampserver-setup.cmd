@echo off
setlocal EnableExtensions

set "REQUIRED_VERSION=3.4.0"
set "INSTALLER=%~dp0..\data\wampserver.exe"

echo.
echo [WampServer] Checking installation...

set "INSTALLED_VERSION="

for /f "delims=" %%V in ('powershell.exe -NoProfile -Command "$p='HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*','HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*','HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*'; $x=Get-ItemProperty $p -ErrorAction SilentlyContinue | Where-Object {$_.DisplayName -like 'WampServer*'} | Select-Object -First 1; if($x.DisplayVersion){$x.DisplayVersion}"') do set "INSTALLED_VERSION=%%V"

if defined INSTALLED_VERSION (
    echo [WampServer] [INFO] Installed version: %INSTALLED_VERSION%
    echo [WampServer] [INFO] Required version: %REQUIRED_VERSION%

    if "%INSTALLED_VERSION%"=="%REQUIRED_VERSION%" (
        echo [WampServer] [SKIP] Correct version already installed.
        exit /b 0
    )

    echo [WampServer] [INFO] Wrong version detected.
    echo [WampServer] [INFO] Removing old version...

    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$p='HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*','HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*','HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*'; $x=Get-ItemProperty $p -ErrorAction SilentlyContinue | Where-Object {$_.DisplayName -like 'WampServer*'} | Select-Object -First 1; if($x.UninstallString){Start-Process -FilePath 'cmd.exe' -ArgumentList '/c',$x.UninstallString,'/VERYSILENT','/SUPPRESSMSGBOXES','/NORESTART' -Wait}"

    timeout /t 2 /nobreak >nul
)

if not exist "%INSTALLER%" (
    echo [WampServer] [ERROR] Installer not found.
    echo [WampServer] %INSTALLER%
    exit /b 1
)

echo [WampServer] [INFO] Installing WampServer %REQUIRED_VERSION%...

start /wait "" "%INSTALLER%" /VERYSILENT /SUPPRESSMSGBOXES /NORESTART

if errorlevel 1 (
    echo [WampServer] [ERROR] Installer returned an error.
    exit /b 1
)

set "INSTALLED_VERSION="

for /f "delims=" %%V in ('powershell.exe -NoProfile -Command "$p='HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*','HKLM:\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*','HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*'; $x=Get-ItemProperty $p -ErrorAction SilentlyContinue | Where-Object {$_.DisplayName -like 'WampServer*'} | Select-Object -First 1; if($x.DisplayVersion){$x.DisplayVersion}"') do set "INSTALLED_VERSION=%%V"

if not "%INSTALLED_VERSION%"=="%REQUIRED_VERSION%" (
    echo [WampServer] [ERROR] Version verification failed.
    echo [WampServer] [ERROR] Detected: %INSTALLED_VERSION%
    echo [WampServer] [ERROR] Required: %REQUIRED_VERSION%
    exit /b 1
)

echo [WampServer] [OK] Version %REQUIRED_VERSION% installed.
exit /b 0