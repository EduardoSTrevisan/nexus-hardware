$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$phps = @(Get-ChildItem 'C:\wamp64\bin\php\php*\php.exe' -ErrorAction SilentlyContinue | Sort-Object FullName -Descending)
if ($phps.Count -eq 0) { Write-Host 'PHP nao localizado em C:\wamp64. Confira a instalacao do WampServer.' -ForegroundColor Red; exit 1 }
$exe = $phps[0].FullName
Write-Host '=== INSTALACAO POSTGRESQL - NEXUS HARDWARE ===' -ForegroundColor Cyan
Write-Host "PHP: $exe"
Write-Host 'Banco destinado: nexus_hardware (PostgreSQL 18).'
Write-Host 'Nao modifica o banco washtec_v4.' -ForegroundColor Green
Write-Host 'Este assistente exige que o banco nexus_hardware ja exista no pgAdmin.'
& $exe scripts/verificar_driver.php
if ($LASTEXITCODE -ne 0) {
    Write-Host 'pdo_pgsql nao esta habilitado no PHP usado pelo terminal. Confira o php.ini.' -ForegroundColor Red
    exit 1
}
$usuario = Read-Host 'Usuario PostgreSQL (Enter = postgres)'
if ([string]::IsNullOrWhiteSpace($usuario)) { $usuario = 'postgres' }
$secreta = Read-Host 'Senha LOCAL do PostgreSQL (nao compartilhe)' -AsSecureString
$senha = [pscredential]::new('db',$secreta).GetNetworkCredential().Password
if ($senha.Contains("`n") -or $senha.Contains("`r")) { throw 'Senha contem quebra de linha invalida.' }
$token = (& $exe scripts/gerar_token.php).Trim()
if ($LASTEXITCODE -ne 0 -or $token.Length -ne 48) { throw 'Erro ao gerar token administrativo.' }
$linhas = @(
  'SITE_URL=http://localhost:8000',
  'PUBLIC_INDEXING=0',
  'STORE_REAL=0',
  'DB_DSN=pgsql:host=127.0.0.1;port=5432;dbname=nexus_hardware',
  "DB_USER=$usuario",
  "DB_PASSWORD=$senha",
  "ADMIN_TOKEN=$token",
  'ALLOWED_ORIGINS='
)
if (Test-Path '.env') { Copy-Item '.env' '.env.backup' -Force }
[System.IO.File]::WriteAllLines((Join-Path (Get-Location) '.env'), [string[]]$linhas, (New-Object System.Text.UTF8Encoding($false)))
$senha = $null
$secreta.Dispose()
Write-Host 'Configuracao .env criada. Nunca envie .env ao GitHub.' -ForegroundColor Cyan
& $exe scripts/instalar_postgresql.php
if ($LASTEXITCODE -ne 0) {
    Write-Host 'Instalacao falhou. Confira senha, usuario, banco, porta 5432 e PostgreSQL iniciado.' -ForegroundColor Red
    exit 1
}
& $exe scripts/verificar_banco.php
if ($LASTEXITCODE -ne 0) { exit 1 }
Write-Host 'BANCO CONFIGURADO! Inicie a loja em INICIAR_LOJA.bat.' -ForegroundColor Green
