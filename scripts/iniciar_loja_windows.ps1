$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$phps = @(Get-ChildItem 'C:\wamp64\bin\php\php*\php.exe' -ErrorAction SilentlyContinue | Sort-Object FullName -Descending)
if ($phps.Count -eq 0) { Write-Host 'PHP nao localizado em C:\wamp64. Verifique sua instalacao do Wamp.' -ForegroundColor Red; exit 1 }
$exe = $phps[0].FullName
Write-Host "PHP escolhido: $exe" -ForegroundColor Cyan
Write-Host 'Abra no navegador: http://localhost:8000' -ForegroundColor Green
Write-Host 'Deixe esta janela aberta. Para parar: CTRL+C.'
& $exe -S 127.0.0.1:8000 -t public public/router.php
