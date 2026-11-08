@echo off
cd /d "%~dp0"
where py >nul 2>nul
if errorlevel 1 (
  echo Python nao encontrado. Instale Python 3.11 ou superior e reabra o terminal.
  pause
  exit /b 1
)
py -3 -m pip install -r mcp_servidor\requirements.txt
if errorlevel 1 (
  echo Falha ao instalar MCP. Confira internet e Python.
  pause
  exit /b 1
)
py -3 mcp_servidor\testar_protocolo.py
pause
