@echo off
setlocal EnableExtensions EnableDelayedExpansion

REM ============================================================
REM MySQL 8.4.11 packaged environment installer
REM Package:
REM   data\mysql.zip
REM
REM Installs:
REM   C:\Program Files\MySQL\MySQL Server 8.4\
REM   C:\ProgramData\MySQL\MySQL Server 8.4\
REM
REM Service:
REM   MySQL84
REM
REM MySQL account:
REM   root / root@admin
REM ============================================================

set "ENGINE_DIR=%~dp0"
set "PROJECT_ROOT=%ENGINE_DIR%.."
set "ZIP_FILE=%PROJECT_ROOT%\data\mysql.zip"

set "PROGRAM_ROOT=C:\Program Files\MySQL"
set "PROGRAM_DIR=%PROGRAM_ROOT%\MySQL Server 8.4"
set "PROGRAMDATA_ROOT=C:\ProgramData\MySQL"
set "PROGRAMDATA_DIR=%PROGRAMDATA_ROOT%\MySQL Server 8.4"
set "MY_INI=%PROGRAMDATA_DIR%\my.ini"
set "MYSQLD=%PROGRAM_DIR%\bin\mysqld.exe"
set "MYSQL=%PROGRAM_DIR%\bin\mysql.exe"
set "SERVICE_NAME=MySQL84"

set "TEMP_DIR=%TEMP%\travel-planner-mysql-setup"
set "EXTRACT_DIR=%TEMP_DIR%\package"


REM ---------- Require Administrator ----------
net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo [INFO] Administrator permission is required.
    echo [INFO] Restarting this installer as Administrator...
    powershell -NoProfile -ExecutionPolicy Bypass -Command ^
        "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b 0
)

REM ---------- Check existing environment ----------
echo [MySQL] Checking environment...

set "MYSQL_READY="

if exist "%PROGRAM_DIR%\bin\mysqld.exe" if exist "%PROGRAM_DIR%\bin\mysql.exe" if exist "%MY_INI%" if exist "%PROGRAMDATA_DIR%\Data" (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$s=Get-Service -Name '%SERVICE_NAME%' -ErrorAction SilentlyContinue; $p=[Environment]::GetEnvironmentVariable('Path','Machine'); if($s -and $s.Status -eq 'Running' -and (($p -split ';') -contains '%PROGRAM_DIR%\bin')){exit 0}else{exit 1}" >nul 2>&1
    if not errorlevel 1 set "MYSQL_READY=YES"
)

if /I "%MYSQL_READY%"=="YES" (
    echo [MySQL] [SKIP] MySQL environment already exists.
    exit /b 0
)

echo [MySQL] [INFO] MySQL environment not found. Installing...
echo.

REM ---------- Check package ----------
if not exist "%ZIP_FILE%" (
    echo [ERROR] mysql.zip was not found:
    echo         %ZIP_FILE%
    echo.
    exit /b 1
)

REM ---------- Clean temporary workspace ----------
if exist "%TEMP_DIR%" rd /s /q "%TEMP_DIR%"
mkdir "%EXTRACT_DIR%" >nul 2>&1

echo [MySQL] [INFO] Extracting package...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
    "Expand-Archive -LiteralPath '%ZIP_FILE%' -DestinationPath '%EXTRACT_DIR%' -Force"
if errorlevel 1 (
    echo [ERROR] Failed to extract mysql.zip.
    exit /b 1
)

if not exist "%EXTRACT_DIR%\mysql-part-1\MySQL Server 8.4\bin\mysqld.exe" (
    echo [ERROR] mysql.zip has an unexpected structure.
    echo         Expected:
    echo         mysql-part-1\MySQL Server 8.4\bin\mysqld.exe
    exit /b 1
)

if not exist "%EXTRACT_DIR%\mysql-part-2\MySQL Server 8.4\my.ini" (
    echo [ERROR] mysql.zip has an unexpected structure.
    echo         Expected:
    echo         mysql-part-2\MySQL Server 8.4\my.ini
    exit /b 1
)

REM ---------- Stop/remove existing service if present ----------
echo [2/8] Preparing Windows service %SERVICE_NAME%...
sc.exe query "%SERVICE_NAME%" >nul 2>&1
if "%errorlevel%"=="0" (
    echo [INFO] Existing %SERVICE_NAME% service found. Stopping...
    sc.exe stop "%SERVICE_NAME%" >nul 2>&1
    timeout /t 3 /nobreak >nul

    echo [INFO] Removing existing %SERVICE_NAME% service...
    sc.exe delete "%SERVICE_NAME%" >nul 2>&1
    timeout /t 2 /nobreak >nul
)

REM ---------- Install program files ----------
echo [MySQL] [INFO] Installing MySQL files...
if exist "%PROGRAM_DIR%" rd /s /q "%PROGRAM_DIR%"
mkdir "%PROGRAM_ROOT%" >nul 2>&1

robocopy "%EXTRACT_DIR%\mysql-part-1\MySQL Server 8.4" "%PROGRAM_DIR%" /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /NFL /NDL /NJH /NJS >nul
if errorlevel 8 (
    echo [ERROR] Failed to copy MySQL program files.
    exit /b 1
)

if not exist "%MYSQLD%" (
    echo [ERROR] mysqld.exe was not installed correctly.
    exit /b 1
)

REM ---------- Install ProgramData/config/data ----------
echo [MySQL] [INFO] Installing configuration and data...
if exist "%PROGRAMDATA_DIR%" rd /s /q "%PROGRAMDATA_DIR%"
mkdir "%PROGRAMDATA_ROOT%" >nul 2>&1

robocopy "%EXTRACT_DIR%\mysql-part-2\MySQL Server 8.4" "%PROGRAMDATA_DIR%" /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /NFL /NDL /NJH /NJS >nul
if errorlevel 8 (
    echo [ERROR] Failed to copy MySQL ProgramData.
    exit /b 1
)

if not exist "%MY_INI%" (
    echo [ERROR] my.ini was not installed correctly.
    exit /b 1
)

if not exist "%PROGRAMDATA_DIR%\Data" (
    echo [ERROR] MySQL Data directory was not installed correctly.
    exit /b 1
)

REM ---------- Keep packaged MySQL configuration unchanged ----------
echo [MySQL] [INFO] Keeping packaged configuration and data...

REM ---------- Grant service account access to data ----------
echo [MySQL] [INFO] Configuring data permissions...
icacls "%PROGRAMDATA_DIR%" /grant "NT AUTHORITY\NetworkService:(OI)(CI)M" /T /C >nul
if errorlevel 1 (
    echo [WARNING] Could not fully set NetworkService permissions.
    echo          MySQL startup will be tested next.
)

REM ---------- Register service ----------
echo [MySQL] [INFO] Starting MySQL service...

sc.exe create "%SERVICE_NAME%" ^
    binPath= "\"%MYSQLD%\" --defaults-file=\"%MY_INI%\" %SERVICE_NAME%" ^
    start= auto ^
    obj= "NT AUTHORITY\NetworkService" ^
    DisplayName= "MySQL84" >nul

if errorlevel 1 (
    echo [MySQL] [ERROR] Failed to create MySQL service.
    exit /b 1
)

sc.exe description "%SERVICE_NAME%" "MySQL Server 8.4.11 for Travel Planner" >nul 2>&1

sc.exe start "%SERVICE_NAME%" >nul 2>&1

echo [MySQL] [INFO] Waiting for MySQL service...

set "MYSQL_RUNNING="
for /L %%N in (1,1,15) do (
    if not defined MYSQL_RUNNING (
        powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$s=Get-Service -Name '%SERVICE_NAME%' -ErrorAction SilentlyContinue; if($s -and $s.Status -eq 'Running'){exit 0}else{exit 1}" >nul 2>&1
        if not errorlevel 1 set "MYSQL_RUNNING=YES"
        if not defined MYSQL_RUNNING timeout /t 1 /nobreak >nul
    )
)

if not defined MYSQL_RUNNING (
    echo [MySQL] [ERROR] MySQL service could not start.
    echo [MySQL] [ERROR] Check the MySQL error log in:
    echo          %PROGRAMDATA_DIR%\Data
    exit /b 1
)

echo [MySQL] [OK] MySQL service is running.

REM ---------- Add MySQL bin to System PATH ----------
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$d='%PROGRAM_DIR%\bin'; $p=[Environment]::GetEnvironmentVariable('Path','Machine'); if($null -eq $p){$p=''}; $items=$p -split ';' | Where-Object { $_ -and ($_.Trim().TrimEnd('\') -ine $d.TrimEnd('\')) }; [Environment]::SetEnvironmentVariable('Path',((@($d)+$items)-join ';'),'Machine')"

if errorlevel 1 (
    echo [MySQL] [ERROR] Failed to configure PATH.
    exit /b 1
)

REM ---------- Refresh current process PATH ----------
set "PATH=%PROGRAM_DIR%\bin;%PATH%"

REM ---------- Clean temporary files ----------
rd /s /q "%TEMP_DIR%" >nul 2>&1

echo.
echo MySQL is ready for Travel Planner.
echo.
exit /b 0
