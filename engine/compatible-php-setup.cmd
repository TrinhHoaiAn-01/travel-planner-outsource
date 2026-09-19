@echo off
setlocal EnableExtensions

set "TARGET_VERSION=8.3.33"
set "PHP_DIR=C:\php83"
set "PHP_EXE=%PHP_DIR%\php.exe"

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\php.zip"

echo Checking PHP...

if exist "%PHP_EXE%" goto SETUP_PATH

echo Installing PHP...

if not exist "%ZIP_FILE%" (
    echo Failed PHP
    exit /b 1
)

tar -xf "%ZIP_FILE%" -C "C:\" >nul 2>&1

if not exist "%PHP_EXE%" (
    echo Failed PHP
    exit /b 1
)

goto SETUP_PATH


:SETUP_PATH

echo Setting PHP environment...

powershell -NoProfile -ExecutionPolicy Bypass -Command "$phpDir='C:\php83'; $path=[Environment]::GetEnvironmentVariable('Path','User'); if($null -eq $path){$path=''}; $items=$path -split ';'; $found=$false; foreach($item in $items){if($item.Trim().TrimEnd('\') -ieq $phpDir.TrimEnd('\')){$found=$true}}; if(-not $found){if($path.Trim() -eq ''){$newPath=$phpDir}else{$newPath=$path+';'+$phpDir}; [Environment]::SetEnvironmentVariable('Path',$newPath,'User')}"

if errorlevel 1 (
    echo Failed PHP
    exit /b 1
)

echo Installed PHP %TARGET_VERSION%
echo Setup Environment PHP Successfully

exit /b 0