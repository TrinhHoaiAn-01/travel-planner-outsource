@echo off
setlocal

set "ENGINE_DIR=%~dp0"

call "%ENGINE_DIR%compatible-wampserver-setup.cmd"
if errorlevel 1 echo [!] Lỗi cài đặt WampServer. Bỏ qua...

call "%ENGINE_DIR%compatible-workbench-setup.cmd"
if errorlevel 1 echo [!] Lỗi cài đặt MySQL Workbench. Bỏ qua...

call "%ENGINE_DIR%compatible-smartgit-setup.cmd"
if errorlevel 1 echo [!] Lỗi cài đặt SmartGit. Bỏ qua...

exit /b 0