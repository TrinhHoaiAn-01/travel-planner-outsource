@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "SOURCE_CONTROLLERS=%PROJECT_DIR%\app\Http\Controllers"
set "SOURCE_MODELS=%PROJECT_DIR%\app\Models"
set "SOURCE_VIEWS=%PROJECT_DIR%\resources\views"
set "SOURCE_DATABASES=%PROJECT_DIR%\database"
set "SOURCE_ROUTE=%PROJECT_DIR%\routes"

set "OUTPUT=%PROJECT_DIR%\mvc-system"

echo Checking Laravel source...

if not exist "%SOURCE_CONTROLLERS%" (
    echo Failed Controllers
    exit /b 1
)

if not exist "%SOURCE_MODELS%" (
    echo Failed Models
    exit /b 1
)

if not exist "%SOURCE_VIEWS%" (
    echo Failed Views
    exit /b 1
)

if not exist "%SOURCE_DATABASES%" (
    echo Failed Databases
    exit /b 1
)

if not exist "%SOURCE_ROUTE%" (
    echo Failed Route
    exit /b 1
)

echo Creating MVC system...

if not exist "%OUTPUT%" mkdir "%OUTPUT%"
if not exist "%OUTPUT%\MVC" mkdir "%OUTPUT%\MVC"


REM Remove old junctions
if exist "%OUTPUT%\MVC\Controllers" rmdir "%OUTPUT%\MVC\Controllers" /S /Q >nul 2>&1
if exist "%OUTPUT%\MVC\Models" rmdir "%OUTPUT%\MVC\Models" /S /Q >nul 2>&1
if exist "%OUTPUT%\MVC\Views" rmdir "%OUTPUT%\MVC\Views" /S /Q >nul 2>&1
if exist "%OUTPUT%\Databases" rmdir "%OUTPUT%\Databases" /S /Q >nul 2>&1
if exist "%OUTPUT%\Route" rmdir "%OUTPUT%\Route" /S /Q >nul 2>&1


REM Controllers
mklink /J "%OUTPUT%\MVC\Controllers" "%SOURCE_CONTROLLERS%" >nul

if errorlevel 1 (
    echo Failed Controllers
    exit /b 1
)

echo Created Controllers


REM Models
mklink /J "%OUTPUT%\MVC\Models" "%SOURCE_MODELS%" >nul

if errorlevel 1 (
    echo Failed Models
    exit /b 1
)

echo Created Models


REM Views
mklink /J "%OUTPUT%\MVC\Views" "%SOURCE_VIEWS%" >nul

if errorlevel 1 (
    echo Failed Views
    exit /b 1
)

echo Created Views


REM Databases
mklink /J "%OUTPUT%\Databases" "%SOURCE_DATABASES%" >nul

if errorlevel 1 (
    echo Failed Databases
    exit /b 1
)

echo Created Databases


REM Route
mklink /J "%OUTPUT%\Route" "%SOURCE_ROUTE%" >nul

if errorlevel 1 (
    echo Failed Route
    exit /b 1
)

echo Created Route

exit /b 0