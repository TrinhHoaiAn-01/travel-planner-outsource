@echo off
setlocal EnableExtensions

echo.
echo ============================================
echo   Travel Planner - Program Setup
echo ============================================
echo.

REM ============================================
REM Directory configuration
REM ============================================

REM Script is inside: travel-planner\engine\
set "ENGINE_DIR=%~dp0"

echo Engine: %ENGINE_DIR%
echo.

REM ============================================
REM WampServer
REM ============================================

echo [1/3] WampServer
echo.

if not exist "%ENGINE_DIR%compatible-wampserver-setup.cmd" (
    echo [WampServer] [ERROR] Setup file not found.
    echo [WampServer] %ENGINE_DIR%compatible-wampserver-setup.cmd
    echo.
    echo [Program] Setup stopped.
    exit /b 1
)

echo [WampServer] [INFO] Starting setup...

call "%ENGINE_DIR%compatible-wampserver-setup.cmd"

if errorlevel 1 (
    echo.
    echo [WampServer] [ERROR] Setup failed.
    echo.
    echo [Program] Setup stopped.
    exit /b 1
)

echo.
echo [WampServer] [OK] Setup completed.
echo.


REM ============================================
REM SmartGit
REM ============================================

echo [2/3] SmartGit
echo.

if not exist "%ENGINE_DIR%compatible-smartgit-setup.cmd" (
    echo [SmartGit] [ERROR] Setup file not found.
    echo [SmartGit] %ENGINE_DIR%compatible-smartgit-setup.cmd
    echo.
    echo [Program] Setup stopped.
    exit /b 1
)

echo [SmartGit] [INFO] Starting setup...

call "%ENGINE_DIR%compatible-smartgit-setup.cmd"

if errorlevel 1 (
    echo.
    echo [SmartGit] [ERROR] Setup failed.
    echo.
    echo [Program] Setup stopped.
    exit /b 1
)

echo.
echo [SmartGit] [OK] Setup completed.
echo.


REM ============================================
REM MySQL Workbench
REM ============================================

echo [3/3] MySQL Workbench
echo.

if not exist "%ENGINE_DIR%compatible-workbench-setup.cmd" (
    echo [MySQL Workbench] [ERROR] Setup file not found.
    echo [MySQL Workbench] %ENGINE_DIR%compatible-workbench-setup.cmd
    echo.
    echo [Program] Setup stopped.
    exit /b 1
)

echo [MySQL Workbench] [INFO] Starting setup...

call "%ENGINE_DIR%compatible-workbench-setup.cmd"

if errorlevel 1 (
    echo.
    echo [MySQL Workbench] [ERROR] Setup failed.
    echo.
    echo [Program] Setup stopped.
    exit /b 1
)

echo.
echo [MySQL Workbench] [OK] Setup completed.
echo.


REM ============================================
REM Finished
REM ============================================

echo ============================================
echo   Program setup completed successfully
echo ============================================
echo.

exit /b 0