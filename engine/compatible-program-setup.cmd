@echo off
setlocal

set "ENGINE_DIR=%~dp0"

call "%ENGINE_DIR%compatible-wampserver-setup.cmd"
if errorlevel 1 exit /b 1

call "%ENGINE_DIR%compatible-workbench-setup.cmd"
if errorlevel 1 exit /b 1

call "%ENGINE_DIR%compatible-smartgit-setup.cmd"
if errorlevel 1 exit /b 1

exit /b 0