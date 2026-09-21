@echo off
setlocal

set "ENGINE_DIR=%~dp0"

call "%ENGINE_DIR%compatible-wampserver-setup.cmd"
if errorlevel 1 echo [!] WampServer installation failed or skipped.

call "%ENGINE_DIR%compatible-workbench-setup.cmd"
if errorlevel 1 echo [!] MySQL Workbench installation failed or skipped.

call "%ENGINE_DIR%compatible-smartgit-setup.cmd"
if errorlevel 1 echo [!] SmartGit installation failed or skipped.

exit /b 0