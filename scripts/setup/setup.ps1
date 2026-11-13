<#
.SYNOPSIS
    Prepara el entorno local del proyecto SaaS Inversiones R&P.

.DESCRIPTION
    Verifica herramientas, copia plantillas de entorno, instala dependencias
    de backend y frontend, genera la clave de la aplicación y ejecuta las
    migraciones.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts/setup/setup.ps1
#>

[CmdletBinding()]
param(
    [string]$PhpPath = "php",
    [string]$ComposerPath = "composer",
    [switch]$SkipMigrations
)

$ErrorActionPreference = "Stop"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $root

function Write-Step([string]$Message) {
    Write-Host "`n==> $Message" -ForegroundColor Cyan
}

function Invoke-Checked([scriptblock]$Command, [string]$ErrorMessage) {
    & $Command
    if ($LASTEXITCODE -ne 0) {
        throw $ErrorMessage
    }
}

Write-Step "Verificando herramientas"
Invoke-Checked { & $PhpPath -v } "PHP no está disponible. Ajuste -PhpPath."
Invoke-Checked { & $ComposerPath -V } "Composer no está disponible. Ajuste -ComposerPath."
Invoke-Checked { node -v } "Node.js no está disponible."
Invoke-Checked { npm -v } "npm no está disponible."

Write-Step "Preparando variables de entorno"
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Creado .env a partir de .env.example"
}
if (-not (Test-Path "backend/.env")) {
    Copy-Item "backend/.env.example" "backend/.env"
    Write-Host "Creado backend/.env a partir de backend/.env.example"
}

Write-Step "Instalando dependencias del backend"
Push-Location backend
Invoke-Checked { & $ComposerPath install --no-interaction --prefer-dist } "Falló composer install."
Invoke-Checked { & $PhpPath artisan key:generate } "Falló key:generate."

if (-not $SkipMigrations) {
    Write-Step "Ejecutando migraciones"
    & $PhpPath artisan migrate --force
    if ($LASTEXITCODE -ne 0) {
        Write-Warning "No se pudieron ejecutar las migraciones. Verifique MySQL en backend/.env."
    }
}
Pop-Location

Write-Step "Instalando dependencias del frontend"
Push-Location frontend
Invoke-Checked { & npm install } "Falló npm install del frontend."
Pop-Location

Write-Step "Instalando dependencias de pruebas E2E"
Invoke-Checked { & npm install } "Falló npm install de la raíz."

Write-Step "Verificación"
Push-Location backend
Invoke-Checked { & $PhpPath artisan test } "Fallaron las pruebas del backend."
Pop-Location

Push-Location frontend
Invoke-Checked { & npm run test } "Fallaron las pruebas del frontend."
Pop-Location

Write-Host "`nEntorno preparado correctamente." -ForegroundColor Green
Write-Host "Backend:  php artisan serve (en backend/)"
Write-Host "Frontend: npm run dev (en frontend/)"
Write-Host "E2E:      npm run e2e (con el frontend levantado)"
