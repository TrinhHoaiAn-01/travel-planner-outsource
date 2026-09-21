@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"

:MENU

cls

echo 1. Install All
echo 2. Install Environment Only
echo 3. Install Program Only
echo 4. Create MVC-System Shortcut
echo 0. Exit
echo.

set /p "CHOICE=Select: "

if "%CHOICE%"=="1" goto INSTALL_ALL
if "%CHOICE%"=="2" goto INSTALL_ENVIRONMENT
if "%CHOICE%"=="3" goto INSTALL_PROGRAM
if "%CHOICE%"=="4" goto CREATE_SHORTCUT
if "%CHOICE%"=="0" exit /b 0

goto MENU


:INSTALL_ALL

call "%ENGINE_DIR%compatible-php-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-mysql-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-composer-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-composer-client.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-wampserver-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-workbench-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-smartgit-setup.cmd"
if errorlevel 1 goto RETURN_MENU

goto RETURN_MENU


:INSTALL_ENVIRONMENT

call "%ENGINE_DIR%compatible-php-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-mysql-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-composer-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-composer-client.cmd"
if errorlevel 1 goto RETURN_MENU

goto RETURN_MENU


:INSTALL_PROGRAM

call "%ENGINE_DIR%compatible-wampserver-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-workbench-setup.cmd"
if errorlevel 1 goto RETURN_MENU

call "%ENGINE_DIR%compatible-smartgit-setup.cmd"
if errorlevel 1 goto RETURN_MENU

goto RETURN_MENU


:CREATE_SHORTCUT

call "%ENGINE_DIR%mvc-shortcut-create.cmd"

goto RETURN_MENU


:RETURN_MENU

goto MENU