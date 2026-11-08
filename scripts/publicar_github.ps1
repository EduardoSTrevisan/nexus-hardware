# Executar apos criar um repositorio vazio em github.com/new
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path -Parent $PSScriptRoot)
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
  Write-Host 'Instale o Git para Windows: https://git-scm.com/download/win' -ForegroundColor Yellow
  exit 1
}
$usuario = Read-Host 'Seu nome de USUARIO do GitHub'
if ($usuario -notmatch '^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$') { throw 'Nome de usuario invalido.' }
$nome = Read-Host 'Nome do repositorio (ENTER = nexus-hardware)'
if (-not $nome) { $nome = 'nexus-hardware' }
if ($nome -notmatch '^[a-zA-Z0-9._-]+$') { throw 'Nome de repositorio invalido.' }
$origem = "https://github.com/$usuario/$nome.git"
Write-Host "Abra https://github.com/new e crie o repositorio PUBLICO e VAZIO: $nome" -ForegroundColor Cyan
$ok = Read-Host 'Depois de criar o repositorio, digite SIM para continuar'
if ($ok -ne 'SIM') { Write-Host 'Cancelado: nenhum arquivo enviado.'; exit 0 }
if (-not (Test-Path .git)) { git init; if ($LASTEXITCODE -ne 0) { throw 'git init falhou' } }
git branch -M main
if ($LASTEXITCODE -ne 0) { throw 'git branch falhou' }
if (Test-Path .env) { Write-Host 'O .env sera ignorado pelo Git.' }
git add .
if ($LASTEXITCODE -ne 0) { throw 'git add falhou' }
$trackedEnv = git ls-files --cached -- .env
if ($trackedEnv) { git rm --cached .env; throw 'O arquivo .env estava versionado; corrija antes de publicar.' }
Write-Host 'Confira os arquivos que serao publicados:' -ForegroundColor Cyan
git status --short
$ok = Read-Host 'Digite SIM para criar commit e enviar ao GitHub'
if ($ok -ne 'SIM') { Write-Host 'Cancelado.'; exit 0 }
git diff --cached --quiet
if ($LASTEXITCODE -ne 0) {
  git commit -m 'NEXUS Hardware - frontend, API PHP e MCP'
  if ($LASTEXITCODE -ne 0) { throw 'Commit falhou: configure nome e email no Git.' }
}
$remotes = @(git remote)
if ($remotes -contains 'origin') { git remote set-url origin $origem }
else { git remote add origin $origem }
git push -u origin main
if ($LASTEXITCODE -ne 0) { throw 'Push falhou. Verifique login do GitHub e o repositorio.' }
Write-Host "OK: https://github.com/$usuario/$nome" -ForegroundColor Green
Write-Host 'Acesse Settings > Pages > Deploy from a branch > main > /docs > Save.'
Write-Host "Seu site costuma ser: https://$usuario.github.io/$nome/"
