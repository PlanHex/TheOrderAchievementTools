#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Sets up the database for development/testing
.DESCRIPTION
    Initializes MySQL database with schema and optionally seeds sample data.
    Runs via Docker if PHP is not available locally.
.PARAMETER Seed
    Also seed database with sample CSV data
.PARAMETER Config
    Path to custom database config file
.PARAMETER UseLocal
    Force using local PHP if available
.PARAMETER DropExisting
    Drop existing database before creating (dangerous!)
.EXAMPLE
    .\Setup-Database.ps1
    # Creates database and tables

    .\Setup-Database.ps1 -Seed
    # Creates database, tables, and seeds with sample data

    .\Setup-Database.ps1 -UseLocal
    # Uses local PHP if available
#>

param(
    [switch]$Seed,
    [string]$Config,
    [switch]$UseLocal,
    [switch]$DropExisting
)

$ErrorActionPreference = 'Stop'
$RootPath = Split-Path -Parent $MyInvocation.MyCommand.Path | Split-Path -Parent

# Import helper modules
$helperPath = Join-Path $RootPath "scripts\lib"
Import-Module "$helperPath\Docker-Helper.psm1" -Force
Import-Module "$helperPath\Php-Helper.psm1" -Force

Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  🗄️  Database Setup" -ForegroundColor Cyan
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Check if database is already set up
Write-Host "📋 Checking current state..." -ForegroundColor Yellow

$dbConfigPath = Join-Path $RootPath "production\config\database.php"
if (-not (Test-Path $dbConfigPath)) {
    Write-Host "❌ Database config not found: $dbConfigPath" -ForegroundColor Red
    exit 1
}

# Determine execution method
$method = if ($UseLocal -and (Test-PhpAvailable)) { "local" } elseif (Test-Docker) { "docker" } else { "none" }

if ($method -eq "none") {
    Write-Host "❌ No execution method available (Docker or local PHP required)" -ForegroundColor Red
    exit 1
}

# Build arguments
$scriptArgs = @()
if ($Config) {
    $scriptArgs += "--config=$Config"
}
if ($Seed) {
    $scriptArgs += "--seed"
}
if ($DropExisting) {
    Write-Host "⚠️  WARNING: Will drop existing database!" -ForegroundColor Red
    $confirm = Read-Host "Type 'yes' to confirm"
    if ($confirm -ne "yes") {
        Write-Host "Cancelled" -ForegroundColor Yellow
        exit 0
    }
    $scriptArgs += "--drop"
}

# Execute
Write-Host ""

if ($method -eq "docker") {
    Write-Host "🐳 Running via Docker..." -ForegroundColor Cyan
    $composeFile = Join-Path $RootPath "local\docker\docker-compose.yml"
    
    if (-not (Test-Path $composeFile)) {
        Write-Host "❌ Docker compose file not found" -ForegroundColor Red
        Write-Host "   Run: .\scripts\Initialize-Development.ps1" -ForegroundColor Yellow
        exit 1
    }
    
    # Ensure containers are running
    $status = Get-DockerStatus -ComposeFile $composeFile
    if (-not $status -or $status[0].State -ne "running") {
        Write-Host "⏳ Starting Docker services..." -ForegroundColor Cyan
        & docker compose -f $composeFile up -d 2>&1 | Where-Object { $_ -notmatch "already in use" }
        Write-Host ""
    }
    
    Write-Host "⏳ Executing setup script..." -ForegroundColor Cyan
    Write-Host ""
    
    $cmd = @("php", "development/scripts/setup-db.php") + $scriptArgs
    & docker compose -f $composeFile exec -T web @cmd
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✅ Database setup complete!" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "❌ Database setup failed" -ForegroundColor Red
        exit 1
    }
    
} else {
    # Local PHP
    Write-Host "💻 Running locally..." -ForegroundColor Cyan
    
    $scriptPath = Join-Path $RootPath "development\scripts\setup-db.php"
    
    try {
        & php $scriptPath @scriptArgs
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "✅ Database setup complete!" -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "❌ Database setup failed" -ForegroundColor Red
            exit 1
        }
    } catch {
        Write-Host "❌ Error: $_" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Green

# Offer next steps
Write-Host ""
Write-Host "📝 Next Steps:" -ForegroundColor Cyan
Write-Host ""
if (-not $Seed) {
    Write-Host "  • Seed sample data:    .\scripts\Setup-Database.ps1 -Seed" -ForegroundColor Gray
} else {
    Write-Host "  • Start development:   .\scripts\Start-Development.ps1" -ForegroundColor Gray
    Write-Host "  • Run tests:           .\scripts\Test-Application.ps1" -ForegroundColor Gray
}
Write-Host ""
