#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Starts the development environment
.DESCRIPTION
    Ensures Docker is running and starts the development server.
    Shows helpful URLs and debugging information.
.PARAMETER NoDocker
    Start local PHP server instead of Docker
.PARAMETER Port
    Port for development server (default: 8000)
.EXAMPLE
    .\Start-Development.ps1
    # Starts Docker and shows access URLs

    .\Start-Development.ps1 -NoDocker
    # Starts local PHP development server
#>

param(
    [switch]$NoDocker,
    [int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
$RootPath = Split-Path -Parent $MyInvocation.MyCommand.Path | Split-Path -Parent

# Import helper modules
$helperPath = Join-Path $RootPath "scripts\lib"
Import-Module "$helperPath\Docker-Helper.psm1" -Force
Import-Module "$helperPath\Php-Helper.psm1" -Force

Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  🚀 Starting Development Environment" -ForegroundColor Cyan
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Determine execution method
$method = if ($NoDocker -and (Test-PhpAvailable)) { "local" } elseif (Test-Docker) { "docker" } else { "none" }

if ($method -eq "none") {
    Write-Host "❌ No execution method available (Docker or PHP required)" -ForegroundColor Red
    exit 1
}

# Start servers
if ($method -eq "docker") {
    Write-Host "🐳 Starting Docker environment..." -ForegroundColor Cyan
    
    $composeFile = Join-Path $RootPath "local\docker\docker-compose.yml"
    
    # Start containers
    Write-Host "   Starting containers..." -ForegroundColor Gray
    & docker compose -f $composeFile up -d 2>&1 | Where-Object { $_ -notmatch "already in use|healthy" } | ForEach-Object {
        Write-Host "   $_" -ForegroundColor Gray
    }
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Failed to start Docker" -ForegroundColor Red
        exit 1
    }
    
    # Wait for MySQL
    Write-Host "   Waiting for MySQL to be ready..." -ForegroundColor Gray
    $maxAttempts = 30
    $attempt = 0
    while ($attempt -lt $maxAttempts) {
        try {
            $result = docker compose -f $composeFile exec db mysqladmin ping --user=app --password=secret 2>&1
            if ($result -match "mysqld is alive") {
                Write-Host "   ✅ MySQL is ready" -ForegroundColor Green
                break
            }
        } catch { }
        $attempt++
        if ($attempt -lt $maxAttempts) {
            Start-Sleep -Seconds 1
        }
    }
    
    Write-Host "✅ Docker environment started" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 Access URLs:" -ForegroundColor Cyan
    Write-Host "   Web:      http://localhost:8000" -ForegroundColor Yellow
    Write-Host "   Database: localhost:3306 (user: app, password: secret)" -ForegroundColor Yellow
    Write-Host ""
    
    Show-DockerStatus -ComposeFile $composeFile
    
    Write-Host "📝 Useful commands:" -ForegroundColor Cyan
    Write-Host "   View logs:     docker compose logs -f web" -ForegroundColor Gray
    Write-Host "   Run tests:     .\scripts\Test-Application.ps1" -ForegroundColor Gray
    Write-Host "   Setup DB:      .\scripts\Setup-Database.ps1 --seed" -ForegroundColor Gray
    Write-Host "   Stop:          .\scripts\Stop-Development.ps1" -ForegroundColor Gray
    Write-Host ""
    
} else {
    # Local PHP
    Write-Host "💻 Starting local PHP development server..." -ForegroundColor Cyan
    Write-Host ""
    
    $productionPath = Join-Path $RootPath "production"
    
    Write-Host "🌐 Access URL:" -ForegroundColor Cyan
    Write-Host "   http://127.0.0.1:$Port" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "✋ Press Ctrl+C to stop the server" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
    Write-Host ""
    
    # Change to production directory and start server
    Set-Location $productionPath
    & php -S "127.0.0.1:$Port"
}

Write-Host ""
