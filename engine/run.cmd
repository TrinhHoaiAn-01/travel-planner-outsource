@echo off
setlocal EnableExtensions EnableDelayedExpansion

title Travel Planner - Team Setup

rem ============================================================
rem TRAVEL PLANNER - TEAM SETUP
rem
rem Menu:
rem   1. Install ALL environment + programs
rem   2. Install environment + Composer
rem   3. Install programs ONLY
rem   4. Create MVC-System shortcuts
rem   0. Exit
rem
rem Target versions:
rem   PHP              8.3.33
rem   Composer         2.10.3
rem   MySQL            8.4.11
rem   WampServer       3.4.0
rem   MySQL Workbench  26.7.0
rem   SmartGit         20.2.6
rem
rem Rules:
rem   - Correct version already installed = SKIP
rem   - Missing/wrong version = run individual setup
rem   - Verify after setup
rem   - Retry exactly once if verification fails
rem   - Continue to next component after failure
rem   - No pause during setup
rem ============================================================


rem ============================================================
rem PATH
rem ============================================================

set "ENGINE_DIR=%~dp0"

for %%I in ("%ENGINE_DIR%..") do (
    set "PROJECT_DIR=%%~fI"
)


rem ============================================================
rem COUNTERS
rem ============================================================

set "OK_COUNT=0"
set "SKIP_COUNT=0"
set "FAIL_COUNT=0"
set "RUN_COUNT=0"


rem ============================================================
rem STATUS VARIABLES
rem ============================================================

set "STATUS_PHP="
set "STATUS_MYSQL="
set "STATUS_COMPOSER="
set "STATUS_WAMP="
set "STATUS_WORKBENCH="
set "STATUS_SMARTGIT="
set "STATUS_MVC="


rem ============================================================
rem REQUIRE ADMINISTRATOR
rem ============================================================

call :require_admin

if errorlevel 1 (
    exit /b 1
)


rem ============================================================
rem MAIN MENU
rem ============================================================

:MENU

cls

echo.
echo ============================================================
echo              TRAVEL PLANNER - TEAM SETUP
echo ============================================================
echo.
echo   1. Install ALL environment + programs
echo   2. Install environment + Composer
echo   3. Install programs ONLY
echo   4. Create MVC-System shortcuts
echo   0. Exit
echo.
echo ============================================================
echo.

set "CHOICE="

set /p "CHOICE=Select an option [0-4]: "


if "%CHOICE%"=="1" goto :OPTION_ALL

if "%CHOICE%"=="2" goto :OPTION_ENV

if "%CHOICE%"=="3" goto :OPTION_PROGRAM

if "%CHOICE%"=="4" goto :OPTION_MVC

if "%CHOICE%"=="0" (
    exit /b 0
)


echo.
echo [ERROR] Invalid option.
echo.

goto :MENU



rem ============================================================
rem OPTION 1
rem
rem ALL ENVIRONMENT + PROGRAMS
rem
rem Order:
rem   1. PHP
rem   2. MySQL
rem   3. Composer Client
rem   4. WampServer
rem   5. MySQL Workbench
rem   6. SmartGit
rem ============================================================

:OPTION_ALL

echo.
echo ============================================================
echo OPTION 1 - ALL ENVIRONMENT + PROGRAMS
echo ============================================================
echo.

echo [1/6] PHP 8.3.33

call :RUN_COMPONENT ^
    "PHP 8.3.33" ^
    "%ENGINE_DIR%compatible-php-setup.cmd" ^
    VERIFY_PHP ^
    STATUS_PHP


echo.
echo [2/6] MySQL 8.4.11

call :RUN_COMPONENT ^
    "MySQL 8.4.11" ^
    "%ENGINE_DIR%compatible-mysql-setup.cmd" ^
    VERIFY_MYSQL ^
    STATUS_MYSQL


echo.
echo [3/6] Composer 2.10.3

call :RUN_COMPONENT ^
    "Composer 2.10.3" ^
    "%ENGINE_DIR%compatible-composer-client.cmd" ^
    VERIFY_COMPOSER ^
    STATUS_COMPOSER


echo.
echo [4/6] WampServer 3.4.0

call :RUN_COMPONENT ^
    "WampServer 3.4.0" ^
    "%ENGINE_DIR%compatible-wampserver-setup.cmd" ^
    VERIFY_WAMP ^
    STATUS_WAMP


echo.
echo [5/6] MySQL Workbench 26.7.0

call :RUN_COMPONENT ^
    "MySQL Workbench 26.7.0" ^
    "%ENGINE_DIR%compatible-workbench-setup.cmd" ^
    VERIFY_WORKBENCH ^
    STATUS_WORKBENCH


echo.
echo [6/6] SmartGit 20.2.6

call :RUN_COMPONENT ^
    "SmartGit 20.2.6" ^
    "%ENGINE_DIR%compatible-smartgit-setup.cmd" ^
    VERIFY_SMARTGIT ^
    STATUS_SMARTGIT


goto :SUMMARY



rem ============================================================
rem OPTION 2
rem
rem ENVIRONMENT + COMPOSER
rem
rem Order:
rem   1. PHP
rem   2. MySQL
rem   3. Composer Client
rem ============================================================

:OPTION_ENV

echo.
echo ============================================================
echo OPTION 2 - ENVIRONMENT + COMPOSER
echo ============================================================
echo.

echo [1/3] PHP 8.3.33

call :RUN_COMPONENT ^
    "PHP 8.3.33" ^
    "%ENGINE_DIR%compatible-php-setup.cmd" ^
    VERIFY_PHP ^
    STATUS_PHP


echo.
echo [2/3] MySQL 8.4.11

call :RUN_COMPONENT ^
    "MySQL 8.4.11" ^
    "%ENGINE_DIR%compatible-mysql-setup.cmd" ^
    VERIFY_MYSQL ^
    STATUS_MYSQL


echo.
echo [3/3] Composer 2.10.3

call :RUN_COMPONENT ^
    "Composer 2.10.3" ^
    "%ENGINE_DIR%compatible-composer-client.cmd" ^
    VERIFY_COMPOSER ^
    STATUS_COMPOSER


goto :SUMMARY



rem ============================================================
rem OPTION 3
rem
rem PROGRAMS ONLY
rem
rem IMPORTANT:
rem Do NOT use compatible-program-setup.cmd
rem
rem Each program has its own setup script.
rem ============================================================

:OPTION_PROGRAM

echo.
echo ============================================================
echo OPTION 3 - PROGRAMS ONLY
echo ============================================================
echo.

echo [1/3] WampServer 3.4.0

call :RUN_COMPONENT ^
    "WampServer 3.4.0" ^
    "%ENGINE_DIR%compatible-wampserver-setup.cmd" ^
    VERIFY_WAMP ^
    STATUS_WAMP


echo.
echo [2/3] MySQL Workbench 26.7.0

call :RUN_COMPONENT ^
    "MySQL Workbench 26.7.0" ^
    "%ENGINE_DIR%compatible-workbench-setup.cmd" ^
    VERIFY_WORKBENCH ^
    STATUS_WORKBENCH


echo.
echo [3/3] SmartGit 20.2.6

call :RUN_COMPONENT ^
    "SmartGit 20.2.6" ^
    "%ENGINE_DIR%compatible-smartgit-setup.cmd" ^
    VERIFY_SMARTGIT ^
    STATUS_SMARTGIT


goto :SUMMARY



rem ============================================================
rem OPTION 4
rem
rem MVC-SYSTEM SHORTCUTS
rem ============================================================

:OPTION_MVC

echo.
echo ============================================================
echo OPTION 4 - MVC-SYSTEM SHORTCUTS
echo ============================================================
echo.

call :RUN_COMPONENT ^
    "MVC-System shortcuts" ^
    "%ENGINE_DIR%mvc-shortcut-create.cmd" ^
    VERIFY_MVC ^
    STATUS_MVC


goto :SUMMARY



rem ============================================================
rem RUN COMPONENT
rem
rem Arguments:
rem
rem   %1 = Component display name
rem   %2 = Setup script
rem   %3 = Verification function
rem   %4 = Status variable
rem
rem Flow:
rem
rem   Check
rem      |
rem      +-- Correct --> SKIP
rem      |
rem      +-- Wrong/missing
rem             |
rem             v
rem         Setup #1
rem             |
rem             v
rem          Verify
rem             |
rem             +-- OK --> SUCCESS
rem             |
rem             +-- FAIL
rem                   |
rem                   v
rem                Setup #2
rem                   |
rem                   v
rem                Verify
rem                   |
rem                   +-- OK --> SUCCESS
rem                   |
rem                   +-- FAIL --> FAILED
rem
rem After this function:
rem   return to master setup
rem ============================================================

:RUN_COMPONENT

set "COMPONENT=%~1"
set "SETUP_SCRIPT=%~2"
set "VERIFY_FUNC=%~3"
set "STATUS_VAR=%~4"

set /a RUN_COUNT+=1


echo.
echo ============================================================
echo %COMPONENT%
echo ============================================================
echo.
echo Setup:
echo   %SETUP_SCRIPT%
echo.


rem ------------------------------------------------------------
rem Check setup script exists
rem ------------------------------------------------------------

if not exist "%SETUP_SCRIPT%" (

    echo [ERROR] Setup script not found.
    echo [ERROR] %SETUP_SCRIPT%
    echo.

    set "%STATUS_VAR%=FAILED"

    set /a FAIL_COUNT+=1

    goto :eof
)


rem ------------------------------------------------------------
rem Check existing installation
rem ------------------------------------------------------------

echo [INFO] Checking existing installation...

call :%VERIFY_FUNC%

if not errorlevel 1 (

    echo [OK] %COMPONENT% is already correct.
    echo [SKIP] Installation is not required.
    echo.

    set "%STATUS_VAR%=SKIPPED"

    set /a SKIP_COUNT+=1

    goto :eof
)


rem ------------------------------------------------------------
rem Installation attempt 1
rem ------------------------------------------------------------

echo [INFO] Exact target was not detected.
echo [INFO] Installation attempt 1/2...
echo.

call "%SETUP_SCRIPT%"

set "RC=%ERRORLEVEL%"


rem ------------------------------------------------------------
rem Verify attempt 1
rem ------------------------------------------------------------

echo.
echo [INFO] Verifying %COMPONENT%...

call :%VERIFY_FUNC%

if not errorlevel 1 (

    echo.
    echo [OK] %COMPONENT% verified successfully.
    echo.

    set "%STATUS_VAR%=SUCCESS"

    set /a OK_COUNT+=1

    goto :eof
)


rem ------------------------------------------------------------
rem Attempt 1 failed
rem ------------------------------------------------------------

echo.
echo [WARN] %COMPONENT% failed verification after attempt 1.
echo [INFO] Retrying exactly once...
echo.


rem ------------------------------------------------------------
rem Installation attempt 2
rem ------------------------------------------------------------

call "%SETUP_SCRIPT%"

set "RC=%ERRORLEVEL%"


rem ------------------------------------------------------------
rem Verify attempt 2
rem ------------------------------------------------------------

echo.
echo [INFO] Verifying %COMPONENT% again...

call :%VERIFY_FUNC%

if not errorlevel 1 (

    echo.
    echo [OK] %COMPONENT% verified successfully after retry.
    echo.

    set "%STATUS_VAR%=SUCCESS"

    set /a OK_COUNT+=1

    goto :eof
)


rem ------------------------------------------------------------
rem Final failure
rem ------------------------------------------------------------

echo.
echo [ERROR] %COMPONENT% failed after 2 attempts.
echo [INFO] Skipping this component and continuing.
echo.

set "%STATUS_VAR%=FAILED"

set /a FAIL_COUNT+=1

goto :eof



rem ============================================================
rem VERIFY PHP
rem
rem Target:
rem   PHP 8.3.33
rem
rem Location:
rem   C:\php83\php.exe
rem ============================================================

:VERIFY_PHP

if not exist "C:\php83\php.exe" (
    exit /b 1
)


"C:\php83\php.exe" --version 2>nul | findstr /C:"PHP 8.3.33" >nul

if errorlevel 1 (
    exit /b 1
)


exit /b 0



rem ============================================================
rem VERIFY COMPOSER
rem
rem Target:
rem   Composer 2.10.3
rem
rem Location:
rem   C:\ProgramData\ComposerSetup\bin\composer.bat
rem ============================================================

:VERIFY_COMPOSER

if not exist "C:\ProgramData\ComposerSetup\bin\composer.bat" (
    exit /b 1
)


for /f "tokens=3" %%V in (
    'call "C:\ProgramData\ComposerSetup\bin\composer.bat" --version 2^>nul'
) do (

    if "%%V"=="2.10.3" (
        exit /b 0
    )
)


exit /b 1



rem ============================================================
rem VERIFY MYSQL
rem
rem Target:
rem   MySQL 8.4.11
rem
rem Service:
rem   MySQL84
rem ============================================================

:VERIFY_MYSQL

if not exist "C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe" (
    exit /b 1
)


sc.exe query MySQL84 >nul 2>&1

if errorlevel 1 (
    exit /b 1
)


"C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe" ^
    -u root ^
    -proot@admin ^
    -e "SELECT VERSION();" 2>nul ^
    | findstr /C:"8.4.11" >nul


if errorlevel 1 (
    exit /b 1
)


exit /b 0



rem ============================================================
rem VERIFY WAMPSERVER
rem
rem Target:
rem   WampServer 3.4.0
rem ============================================================

:VERIFY_WAMP

set "CHECK_VERSION="


rem 64-bit uninstall registry

for /f "tokens=2,*" %%A in (
    'reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"WampServer"'
) do (

    for /f "tokens=2,*" %%C in (
        'reg query "%%A" /v DisplayVersion 2^>nul'
    ) do (

        set "CHECK_VERSION=%%D"
    )
)


rem 32-bit uninstall registry

for /f "tokens=2,*" %%A in (
    'reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"WampServer"'
) do (

    for /f "tokens=2,*" %%C in (
        'reg query "%%A" /v DisplayVersion 2^>nul'
    ) do (

        set "CHECK_VERSION=%%D"
    )
)


if "!CHECK_VERSION!"=="3.4.0" (
    exit /b 0
)


exit /b 1



rem ============================================================
rem VERIFY MYSQL WORKBENCH
rem
rem Target:
rem   MySQL Workbench 26.7.0
rem ============================================================

:VERIFY_WORKBENCH

set "CHECK_VERSION="


rem 64-bit uninstall registry

for /f "tokens=2,*" %%A in (
    'reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"MySQL Workbench"'
) do (

    for /f "tokens=2,*" %%C in (
        'reg query "%%A" /v DisplayVersion 2^>nul'
    ) do (

        set "CHECK_VERSION=%%D"
    )
)


rem 32-bit uninstall registry

for /f "tokens=2,*" %%A in (
    'reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"MySQL Workbench"'
) do (

    for /f "tokens=2,*" %%C in (
        'reg query "%%A" /v DisplayVersion 2^>nul'
    ) do (

        set "CHECK_VERSION=%%D"
    )
)


if "!CHECK_VERSION!"=="26.7.0" (
    exit /b 0
)


exit /b 1



rem ============================================================
rem VERIFY SMARTGIT
rem
rem Target:
rem   SmartGit 20.2.6
rem ============================================================

:VERIFY_SMARTGIT

set "CHECK_VERSION="


rem 64-bit uninstall registry

for /f "tokens=2,*" %%A in (
    'reg query "HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"SmartGit"'
) do (

    for /f "tokens=2,*" %%C in (
        'reg query "%%A" /v DisplayVersion 2^>nul'
    ) do (

        set "CHECK_VERSION=%%D"
    )
)


rem 32-bit uninstall registry

for /f "tokens=2,*" %%A in (
    'reg query "HKLM\SOFTWARE\WOW6432Node\Microsoft\Windows\CurrentVersion\Uninstall" /s /v DisplayName 2^>nul ^| findstr /i /c:"SmartGit"'
) do (

    for /f "tokens=2,*" %%C in (
        'reg query "%%A" /v DisplayVersion 2^>nul'
    ) do (

        set "CHECK_VERSION=%%D"
    )
)


if "!CHECK_VERSION!"=="20.2.6" (
    exit /b 0
)


exit /b 1



rem ============================================================
rem VERIFY MVC
rem
rem Required shortcuts:
rem
rem   mvc-system
rem   ├── MVC
rem   │   ├── Controllers.lnk
rem   │   ├── Models.lnk
rem   │   └── Views.lnk
rem   │
rem   ├── Routes.lnk
rem   └── Database.lnk
rem
rem Services.lnk is NOT required.
rem ============================================================

:VERIFY_MVC

set "MVC_DIR=%PROJECT_DIR%\mvc-system"


if not exist "%MVC_DIR%" (
    exit /b 1
)


if not exist "%MVC_DIR%\MVC\Controllers.lnk" (
    exit /b 1
)


if not exist "%MVC_DIR%\MVC\Models.lnk" (
    exit /b 1
)


if not exist "%MVC_DIR%\MVC\Views.lnk" (
    exit /b 1
)


if not exist "%MVC_DIR%\Routes.lnk" (
    exit /b 1
)


if not exist "%MVC_DIR%\Database.lnk" (
    exit /b 1
)


exit /b 0



rem ============================================================
rem FINAL SUMMARY
rem ============================================================

:SUMMARY

echo.
echo.
echo ============================================================
echo                    SETUP SUMMARY
echo ============================================================
echo.


if defined STATUS_PHP (
    echo PHP 8.3.33                 : !STATUS_PHP!
)


if defined STATUS_MYSQL (
    echo MySQL 8.4.11               : !STATUS_MYSQL!
)


if defined STATUS_COMPOSER (
    echo Composer 2.10.3            : !STATUS_COMPOSER!
)


if defined STATUS_WAMP (
    echo WampServer 3.4.0           : !STATUS_WAMP!
)


if defined STATUS_WORKBENCH (
    echo MySQL Workbench 26.7.0     : !STATUS_WORKBENCH!
)


if defined STATUS_SMARTGIT (
    echo SmartGit 20.2.6            : !STATUS_SMARTGIT!
)


if defined STATUS_MVC (
    echo MVC-System shortcuts       : !STATUS_MVC!
)


echo.
echo ------------------------------------------------------------
echo Successful installs/verifications : %OK_COUNT%
echo Already correct (SKIPPED)          : %SKIP_COUNT%
echo Failed and skipped                 : %FAIL_COUNT%
echo ------------------------------------------------------------
echo.


if "%FAIL_COUNT%"=="0" (

    echo RESULT: ALL SELECTED COMPONENTS ARE READY.

) else (

    echo RESULT: SETUP FINISHED WITH ERRORS.
    echo Failed components were skipped so the setup could continue.
)


echo.
echo Setup finished.
echo.


exit /b 0



rem ============================================================
rem ADMINISTRATOR
rem ============================================================

:require_admin

net session >nul 2>&1


if not errorlevel 1 (
    exit /b 0
)


echo [INFO] Administrator permission is required.
echo [INFO] Relaunching this setup as Administrator...
echo.


powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"Start-Process -FilePath '%~f0' -Verb RunAs -Wait"


exit /b 1