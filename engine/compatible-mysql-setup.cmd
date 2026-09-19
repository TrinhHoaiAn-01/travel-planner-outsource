@echo off
setlocal EnableExtensions

set "TARGET_VERSION=8.4.11"

set "MYSQL_DIR=C:\Program Files\MySQL\MySQL Server 8.4"
set "MYSQL_BIN=%MYSQL_DIR%\bin"
set "MYSQL_EXE=%MYSQL_BIN%\mysql.exe"
set "MYSQLD_EXE=%MYSQL_BIN%\mysqld.exe"

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\mysql.zip"

echo Checking MySQL...

if exist "%MYSQLD_EXE%" goto SETUP_PATH

echo Installing MySQL...

if not exist "%ZIP_FILE%" (
    echo Failed MySQL
    exit /b 1
)

tar -xf "%ZIP_FILE%" -C "C:\" >nul 2>&1

if errorlevel 1 (
    echo Failed MySQL
    exit /b 1
)

if not exist "%MYSQLD_EXE%" (
    echo Failed MySQL
    exit /b 1
)

if not exist "%MYSQL_EXE%" (
    echo Failed MySQL
    exit /b 1
)

goto SETUP_PATH


:SETUP_PATH

echo Setting MySQL environment...

powershell -NoProfile -ExecutionPolicy Bypass -Command "$mysqlBin='C:\Program Files\MySQL\MySQL Server 8.4\bin'; $path=[Environment]::GetEnvironmentVariable('Path','User'); if($null -eq $path){$path=''}; $items=$path -split ';'; $found=$false; foreach($item in $items){if($item.Trim().TrimEnd('\') -ieq $mysqlBin.TrimEnd('\')){$found=$true}}; if(-not $found){if($path.Trim() -eq ''){$newPath=$mysqlBin}else{$newPath=$path+';'+$mysqlBin}; [Environment]::SetEnvironmentVariable('Path',$newPath,'User')}"

if errorlevel 1 (
    echo Failed MySQL
    exit /b 1
)

echo Installed MySQL %TARGET_VERSION%
echo Setup Environment MySQL Successfully

exit /b 0