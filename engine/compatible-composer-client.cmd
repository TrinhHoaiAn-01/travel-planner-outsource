@echo off
setlocal EnableExtensions

set "ENGINE_DIR=%~dp0"
for %%I in ("%ENGINE_DIR%..") do set "PROJECT_DIR=%%~fI"

set "ZIP_FILE=%PROJECT_DIR%\data\compatible-composer.zip"

echo Checking Composer package...

if not exist "%ZIP_FILE%" (
    echo Failed Composer package
    exit /b 1
)

echo Installing Composer package...

powershell -NoProfile -Command "$zip='%ZIP_FILE%'; $dest='%PROJECT_DIR%'; Expand-Archive -Path $zip -DestinationPath $dest -Force; $entries=Get-ChildItem $dest -Directory; if($entries.Count -eq 1){Move-Item ($entries[0].FullName+'\\*') $dest -Force; Remove-Item $entries[0].FullName -Recurse -Force}"

if errorlevel 1 (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\vendor\autoload.php" (
    echo Failed Composer package
    exit /b 1
)

if not exist "%PROJECT_DIR%\.env" (
    echo Failed Composer package
    exit /b 1
)

echo Installed Composer package

exit /b 0