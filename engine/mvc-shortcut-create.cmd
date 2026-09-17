@echo off
setlocal

echo ============================================
echo   Travel Planner - MVC System Shortcuts
echo ============================================
echo.

REM ============================================
REM Directory configuration
REM ============================================

REM Script is inside: travel-planner\engine\
set "ENGINE_DIR=%~dp0"

REM Laravel project root: travel-planner\
set "PROJECT_DIR=%ENGINE_DIR%.."

REM Shortcut folder: travel-planner\mvc-system\
set "SHORTCUT_DIR=%PROJECT_DIR%\mvc-system"

REM MVC shortcut folder
set "MVC_DIR=%SHORTCUT_DIR%\MVC"

REM ============================================
REM Normalize paths
REM ============================================

for %%I in ("%PROJECT_DIR%") do set "PROJECT_DIR=%%~fI"

echo Project:
echo %PROJECT_DIR%
echo.

REM ============================================
REM Check Laravel project
REM ============================================

if not exist "%PROJECT_DIR%\artisan" (
    echo [ERROR] Laravel project not found.
    echo.
    echo Expected:
    echo %PROJECT_DIR%\artisan
    echo.
    pause
    exit /b 1
)

echo [OK] Laravel project found.
echo.

REM ============================================
REM Create Services folder if needed
REM ============================================

if not exist "%PROJECT_DIR%\app\Services" (
    echo [INFO] Creating app\Services...
    mkdir "%PROJECT_DIR%\app\Services"
)

REM ============================================
REM Create shortcut directories
REM ============================================

if not exist "%SHORTCUT_DIR%" (
    mkdir "%SHORTCUT_DIR%"
)

if not exist "%MVC_DIR%" (
    mkdir "%MVC_DIR%"
)

echo [OK] Shortcut directories ready.
echo.

REM ============================================
REM Controllers
REM ============================================

echo [1/5] Creating Controllers shortcut...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$ws = New-Object -ComObject WScript.Shell; ^
$sc = $ws.CreateShortcut('%MVC_DIR%\Controllers.lnk'); ^
$sc.TargetPath = '%PROJECT_DIR%\app\Http\Controllers'; ^
$sc.WorkingDirectory = '%PROJECT_DIR%'; ^
$sc.Description = 'Laravel Controllers'; ^
$sc.Save()"

echo       Controllers.lnk created.
echo.

REM ============================================
REM Models
REM ============================================

echo [2/5] Creating Models shortcut...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$ws = New-Object -ComObject WScript.Shell; ^
$sc = $ws.CreateShortcut('%MVC_DIR%\Models.lnk'); ^
$sc.TargetPath = '%PROJECT_DIR%\app\Models'; ^
$sc.WorkingDirectory = '%PROJECT_DIR%'; ^
$sc.Description = 'Laravel Models'; ^
$sc.Save()"

echo       Models.lnk created.
echo.

REM ============================================
REM Views
REM ============================================

echo [3/5] Creating Views shortcut...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$ws = New-Object -ComObject WScript.Shell; ^
$sc = $ws.CreateShortcut('%MVC_DIR%\Views.lnk'); ^
$sc.TargetPath = '%PROJECT_DIR%\resources\views'; ^
$sc.WorkingDirectory = '%PROJECT_DIR%'; ^
$sc.Description = 'Laravel Views'; ^
$sc.Save()"

echo       Views.lnk created.
echo.

REM ============================================
REM Routes
REM ============================================

echo [4/5] Creating Routes shortcut...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$ws = New-Object -ComObject WScript.Shell; ^
$sc = $ws.CreateShortcut('%SHORTCUT_DIR%\Routes.lnk'); ^
$sc.TargetPath = '%PROJECT_DIR%\routes'; ^
$sc.WorkingDirectory = '%PROJECT_DIR%'; ^
$sc.Description = 'Laravel Routes'; ^
$sc.Save()"

echo       Routes.lnk created.
echo.

REM ============================================
REM Database
REM ============================================

echo [5/5] Creating Database shortcut...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$ws = New-Object -ComObject WScript.Shell; ^
$sc = $ws.CreateShortcut('%SHORTCUT_DIR%\Database.lnk'); ^
$sc.TargetPath = '%PROJECT_DIR%\database'; ^
$sc.WorkingDirectory = '%PROJECT_DIR%'; ^
$sc.Description = 'Laravel Database'; ^
$sc.Save()"

echo       Database.lnk created.
echo.

REM ============================================
REM Finished
REM ============================================

echo ============================================
echo   Shortcut setup completed successfully
echo ============================================
echo.

echo Created:
echo.
echo   mvc-system\MVC\Controllers.lnk
echo   mvc-system\MVC\Models.lnk
echo   mvc-system\MVC\Views.lnk
echo   mvc-system\Routes.lnk
echo   mvc-system\Database.lnk
echo.

pause