@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Travel Planner - Compatible Program Setup

rem ============================================================
rem Travel Planner - Compatible Program Setup
rem
rem This master setup installs ONLY:
rem   1. WampServer 3.4.0
rem   2. MySQL Workbench 26.7.0
rem   3. SmartGit 20.2.6
rem
rem Each component has its own independent setup script.
rem This file only runs them sequentially.
rem
rem RULE:
rem   - Exact version already installed -> SKIP
rem   - Missing or wrong version -> individual setup handles it
rem   - Next program starts ONLY after previous setup returns PASS
rem ============================================================

set "ENGINE_DIR=%~dp0"

echo.
echo ============================================================
echo          TRAVEL PLANNER - COMPATIBLE PROGRAM SETUP
echo ============================================================
echo.
echo Components:
echo   [1] WampServer       3.4.0
echo   [2] MySQL Workbench  26.7.0
echo   [3] SmartGit         20.2.6
echo.
echo Each program is installed and verified before the next
echo program is started.
echo.

echo ============================================================
echo [1/3] WampServer 3.4.0
echo ============================================================
call "%ENGINE_DIR%compatible-wampserver-setup.cmd"
if errorlevel 1 goto :FAILED

echo.
echo ============================================================
echo [2/3] MySQL Workbench 26.7.0
echo ============================================================
call "%ENGINE_DIR%compatible-workbench-setup.cmd"
if errorlevel 1 goto :FAILED

echo.
echo ============================================================
echo [3/3] SmartGit 20.2.6
echo ============================================================
call "%ENGINE_DIR%compatible-smartgit-setup.cmd"
if errorlevel 1 goto :FAILED

echo.
echo ============================================================
echo              ALL PROGRAMS ARE READY
echo ============================================================
echo.
echo WampServer       : 3.4.0
echo MySQL Workbench  : 26.7.0
echo SmartGit        : 20.2.6
echo.
echo Compatible program setup completed successfully.
echo.
exit /b 0

:FAILED
echo.
echo ============================================================
echo              COMPATIBLE PROGRAM SETUP FAILED
echo ============================================================
echo.
echo Setup stopped because the current program did not
echo complete or could not be verified.
echo.
echo No later program was started.
echo.
exit /b 1
