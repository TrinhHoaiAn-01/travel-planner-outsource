@echo off
setlocal EnableExtensions

set "TARGET_VERSION=2.10.3"

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\composer.zip"

set "COMPOSER_DIR=%LOCALAPPDATA%\Programs\ComposerSetup\bin"
set "COMPOSER_EXE=%COMPOSER_DIR%\composer.bat"

echo Checking Composer...

if exist "%COMPOSER_EXE%" goto SETUP_PATH

echo Installing Composer...

if not exist "%ZIP_FILE%" (
    echo Failed Composer
    exit /b 1
)

powershell -NoProfile -Command "$zip='%ZIP_FILE%'; $dest='%LOCALAPPDATA%\Programs\ComposerSetup'; Expand-Archive -Path $zip -DestinationPath $dest -Force; $entries=Get-ChildItem $dest -Directory; if($entries.Count -eq 1){Move-Item ($entries[0].FullName+'\\*') $dest -Force; Remove-Item $entries[0].FullName -Recurse -Force}"

if errorlevel 1 (
    echo Failed Composer
    exit /b 1
)

if not exist "%COMPOSER_EXE%" (
    echo Failed Composer
    exit /b 1
)

goto SETUP_PATH


:SETUP_PATH

echo Setting Composer environment...

powershell -NoProfile -ExecutionPolicy Bypass -Command "$composerDir='%COMPOSER_DIR%'; $path=[Environment]::GetEnvironmentVariable('Path','User'); if($null -eq $path){$path=''}; $items=$path -split ';'; $found=$false; foreach($item in $items){if($item.Trim().TrimEnd('\') -ieq $composerDir.TrimEnd('\')){$found=$true}}; if(-not $found){if($path.Trim() -eq ''){$newPath=$composerDir}else{$newPath=$path+';'+$composerDir}; [Environment]::SetEnvironmentVariable('Path',$newPath,'User')}"

if errorlevel 1 (
    echo Failed Composer
    exit /b 1
)

echo Installed Composer %TARGET_VERSION%
echo Setup Environment Composer Successfully

exit /b 0