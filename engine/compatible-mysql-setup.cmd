@echo off
setlocal EnableExtensions EnableDelayedExpansion

title Travel Planner - MySQL 8.4.11 Setup

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

echo.
echo ============================================================
echo   Travel Planner - MySQL 8.4.11 Setup
echo ============================================================
echo.

REM ---------- Require Administrator ----------
net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo [INFO] Administrator permission is required.
    echo [INFO] Restarting this installer as Administrator...
    powershell -NoProfile -ExecutionPolicy Bypass -Command ^
        "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b 0
)

REM ---------- Check package ----------
if not exist "%ZIP_FILE%" (
    echo [ERROR] mysql.zip was not found:
    echo         %ZIP_FILE%
    echo.
    pause
    exit /b 1
)

REM ---------- Clean temporary workspace ----------
if exist "%TEMP_DIR%" rd /s /q "%TEMP_DIR%"
mkdir "%EXTRACT_DIR%" >nul 2>&1

echo [1/8] Extracting MySQL package...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
    "Expand-Archive -LiteralPath '%ZIP_FILE%' -DestinationPath '%EXTRACT_DIR%' -Force"
if errorlevel 1 (
    echo [ERROR] Failed to extract mysql.zip.
    pause
    exit /b 1
)

if not exist "%EXTRACT_DIR%\mysql-part-1\MySQL Server 8.4\bin\mysqld.exe" (
    echo [ERROR] mysql.zip has an unexpected structure.
    echo         Expected:
    echo         mysql-part-1\MySQL Server 8.4\bin\mysqld.exe
    pause
    exit /b 1
)

if not exist "%EXTRACT_DIR%\mysql-part-2\MySQL Server 8.4\my.ini" (
    echo [ERROR] mysql.zip has an unexpected structure.
    echo         Expected:
    echo         mysql-part-2\MySQL Server 8.4\my.ini
    pause
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
echo [3/8] Installing MySQL program files...
if exist "%PROGRAM_DIR%" rd /s /q "%PROGRAM_DIR%"
mkdir "%PROGRAM_ROOT%" >nul 2>&1

robocopy "%EXTRACT_DIR%\mysql-part-1\MySQL Server 8.4" "%PROGRAM_DIR%" /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /NFL /NDL /NJH /NJS >nul
if errorlevel 8 (
    echo [ERROR] Failed to copy MySQL program files.
    pause
    exit /b 1
)

if not exist "%MYSQLD%" (
    echo [ERROR] mysqld.exe was not installed correctly.
    pause
    exit /b 1
)

REM ---------- Install ProgramData/config/data ----------
echo [4/8] Installing MySQL configuration and database data...
if exist "%PROGRAMDATA_DIR%" rd /s /q "%PROGRAMDATA_DIR%"
mkdir "%PROGRAMDATA_ROOT%" >nul 2>&1

robocopy "%EXTRACT_DIR%\mysql-part-2\MySQL Server 8.4" "%PROGRAMDATA_DIR%" /E /COPY:DAT /DCOPY:DAT /R:2 /W:1 /NFL /NDL /NJH /NJS >nul
if errorlevel 8 (
    echo [ERROR] Failed to copy MySQL ProgramData.
    pause
    exit /b 1
)

if not exist "%MY_INI%" (
    echo [ERROR] my.ini was not installed correctly.
    pause
    exit /b 1
)

if not exist "%PROGRAMDATA_DIR%\Data" (
    echo [ERROR] MySQL Data directory was not installed correctly.
    pause
    exit /b 1
)

REM ---------- Keep packaged MySQL configuration unchanged ----------
echo [5/8] Keeping packaged MySQL configuration and Data unchanged...

REM ---------- Grant service account access to data ----------
echo [6/8] Configuring MySQL data permissions...
icacls "%PROGRAMDATA_DIR%" /grant "NT AUTHORITY\NetworkService:(OI)(CI)M" /T /C >nul
if errorlevel 1 (
    echo [WARNING] Could not fully set NetworkService permissions.
    echo          MySQL startup will be tested next.
)

REM ---------- Register service ----------
echo [7/8] Registering and starting MySQL84 service...
sc.exe create "%SERVICE_NAME%" ^
    binPath= "\"%MYSQLD%\" --defaults-file=\"%MY_INI%\" %SERVICE_NAME%" ^
    start= auto ^
    obj= "NT AUTHORITY\NetworkService" ^
    DisplayName= "MySQL84" >nul

if errorlevel 1 (
    echo [ERROR] Failed to create MySQL84 Windows service.
    pause
    exit /b 1
)

sc.exe description "%SERVICE_NAME%" "MySQL Server 8.4.11 for Travel Planner" >nul 2>&1

sc.exe start "%SERVICE_NAME%" >nul
if errorlevel 1 (
    echo [ERROR] MySQL84 failed to start.
    echo.
    echo Check:
    echo   %PROGRAMDATA_DIR%\Data
    echo   %MY_INI%
    echo   MySQL84.err
    echo.
    pause
    exit /b 1
)

timeout /t 5 /nobreak >nul

REM ---------- Verify server ----------
echo [8/8] Verifying MySQL...
sc.exe query "%SERVICE_NAME%" | findstr /I "STATE" >nul
if errorlevel 1 (
    echo [ERROR] Could not query MySQL84 service.
    pause
    exit /b 1
)

"%MYSQL%" -u root -proot@admin -e "SELECT VERSION() AS mysql_version, @@port AS port, @@datadir AS datadir;" >"%TEMP_DIR%\verify.txt" 2>&1
if errorlevel 1 (
    echo [ERROR] MySQL started but root login failed.
    echo.
    type "%TEMP_DIR%\verify.txt"
    echo.
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   MySQL setup completed successfully.
echo ============================================================
echo.
echo   Service : MySQL84
echo   Version : 8.4.11
echo   Port    : 3306
echo   User    : root
echo   Password: root@admin
echo   Data    : %PROGRAMDATA_DIR%\Data
echo.
type "%TEMP_DIR%\verify.txt"
echo.

REM ---------- Add MySQL bin to System PATH ----------
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
    "$p=[Environment]::GetEnvironmentVariable('Path','Machine'); $d='%PROGRAM_DIR%\bin'; if (($p -split ';') -notcontains $d) { [Environment]::SetEnvironmentVariable('Path', $d+';'+$p, 'Machine') }"

REM ---------- Refresh current process PATH ----------
set "PATH=%PROGRAM_DIR%\bin;%PATH%"

REM ---------- Clean temporary files ----------
rd /s /q "%TEMP_DIR%" >nul 2>&1

echo.
echo MySQL is ready for Travel Planner.
echo.
pause
exit /b 0
