<#
.SYNOPSIS
    Genera un respaldo de la base de datos MySQL del proyecto.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts/database/backup.ps1
#>

[CmdletBinding()]
param(
    [string]$HostName = "127.0.0.1",
    [int]$Port = 3306,
    [string]$Database = "saas_inversiones_rp",
    [string]$User = "saas",
    [string]$Password = "secret",
    [string]$OutputDirectory = "database/backups"
)

$ErrorActionPreference = "Stop"
$root = Resolve-Path (Join-Path $PSScriptRoot "..\..")
Set-Location $root

if (-not (Test-Path $OutputDirectory)) {
    New-Item -ItemType Directory -Path $OutputDirectory | Out-Null
}

$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$outputFile = Join-Path $OutputDirectory "$Database-$timestamp.sql"

Write-Host "Generando respaldo en $outputFile ..." -ForegroundColor Cyan

& mysqldump `
    --host=$HostName `
    --port=$Port `
    --user=$User `
    --password=$Password `
    --single-transaction `
    --routines `
    --triggers `
    --default-character-set=utf8mb4 `
    $Database | Out-File -FilePath $outputFile -Encoding utf8

if ($LASTEXITCODE -ne 0) {
    throw "Falló mysqldump. Verifique que MySQL esté disponible y las credenciales."
}

Write-Host "Respaldo generado correctamente." -ForegroundColor Green
