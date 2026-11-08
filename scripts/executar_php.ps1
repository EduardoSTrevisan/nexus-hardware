param([Parameter(Mandatory=$true)][string]$Arquivo)
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$phps = @(Get-ChildItem 'C:\wamp64\bin\php\php*\php.exe' -ErrorAction SilentlyContinue | Sort-Object FullName -Descending)
if ($phps.Count -eq 0) { Write-Host 'PHP nao encontrado no WampServer (C:\wamp64).' -ForegroundColor Red; exit 1 }
& $phps[0].FullName $Arquivo
if ($LASTEXITCODE -ne 0) { Write-Host 'O comando encontrou um problema (veja acima).' -ForegroundColor Red; exit $LASTEXITCODE }
