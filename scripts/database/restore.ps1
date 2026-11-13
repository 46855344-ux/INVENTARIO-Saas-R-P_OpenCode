<#
.SYNOPSIS
    Restaura un respaldo SQL en la base de datos MySQL del proyecto.

.PARAMETER File
    Ruta del archivo .sql a restaurar.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File scripts/database/restore.ps1 -File database/backups/saas_inversiones_rp-20261112-120000.sql
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$File,
    [string]$HostName = "127.0.0.1",
    [int]$Port = 3306,
    [string]$Database = "saas_inversiones_rp",
    [string]$User = "saas",
    [string]$Password = "secret"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $File)) {
    throw "No existe el archivo de respaldo: $File"
}

Write-Warning "Esta operación sobrescribe datos de la base '$Database'. Confirme que sea el entorno correcto."
$confirmation = Read-Host "Escriba RESTAURAR para continuar"
if ($confirmation -ne "RESTAURAR") {
    Write-Host "Operación cancelada."
    exit 0
}

Get-Content $File -Raw | & mysql `
    --host=$HostName `
    --port=$Port `
    --user=$User `
    --password=$Password `
    --default-character-set=utf8mb4 `
    $Database

if ($LASTEXITCODE -ne 0) {
    throw "Falló la restauración. Verifique el archivo y las credenciales."
}

Write-Host "Respaldo restaurado correctamente." -ForegroundColor Green
