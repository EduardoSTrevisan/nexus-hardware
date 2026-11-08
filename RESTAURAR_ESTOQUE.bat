@echo off
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\executar_php.ps1" "scripts/restaurar_estoque_cli.php"
pause
