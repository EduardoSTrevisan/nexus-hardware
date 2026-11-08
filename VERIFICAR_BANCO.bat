@echo off
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\executar_php.ps1" "scripts/verificar_banco.php"
pause
