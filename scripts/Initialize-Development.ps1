#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Initializes the development environment (No Local PHP Required)
.DESCRIPTION
    Sets up Docker containers and validates everything is ready for development.
    Supports both local PHP and Docker-based execution.
.PARAMETER UseLocalPHP
    Use local PHP if available (default: use Docker for consistency)
.PARAMETER SkipDockerCheck
    Skip Docker validation and setup
.PARAMETER BuildImages
    Force rebuild of Docker images
.EXAMPLE
    .\Initialize-Development.ps1
    # Checks Docker, starts containers, displays next steps

    .\Initialize-Development.ps1 -UseLocalPHP
    # Uses local PHP if available
#>

param(
    [switch]$UseLocalPHP,
    [switch]$SkipDockerCheck,
    [switch]$BuildImages
)

$ErrorActionPreference = 'Stop'
$RootPath = Split-Path -Parent $MyInvocation.MyCommand.Path | Split-Path -Parent

# Import helper modules
$helperPath = Join-Path $RootPath "scripts\lib"
if (Test-Path "$helperPath\Docker-Helper.psm1") {
    Import-Module "$helperPath\Docker-Helper.psm1" -Force
} else {
    Write-Host "❌ Helper modules not found" -ForegroundColor Red
    exit 1
}

if (Test-Path "$helperPath\Php-Helper.psm1") {
    Import-Module "$helperPath\Php-Helper.psm1" -Force
}

Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  🚀 Development Environment Initialization" -ForegroundColor Cyan
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Step 1: Check system requirements
Write-Host "📋 Checking system requirements..." -ForegroundColor Yellow
Write-Host ""

$hasDocker = $false
$hasLocalPHP = $false

if (-not $SkipDockerCheck) {
    if (Test-Docker) {
        Write-Host "  ✅ Docker installed" -ForegroundColor Green
        $version = Get-DockerVersion
        Write-Host "     Version: $($version.Version)" -ForegroundColor Gray
        $hasDocker = $true
    } else {
        Write-Host "  ❌ Docker not found or not running" -ForegroundColor Red
        Write-Host ""
        Write-Host "     Install Docker Desktop:" -ForegroundColor Yellow
        Write-Host "     https://www.docker.com/products/docker-desktop" -ForegroundColor Blue
        Write-Host ""
    }
    
    if (Test-DockerCompose) {
        Write-Host "  ✅ Docker Compose available" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  Docker Compose not found (usually included with Docker Desktop)" -ForegroundColor Yellow
    }
}

if (Test-PhpAvailable) {
    Write-Host "  ✅ PHP installed locally" -ForegroundColor Green
    $phpVersion = Get-PhpVersion
    Write-Host "     Version: $($phpVersion.Version)" -ForegroundColor Gray
    $hasLocalPHP = $true
} else {
    Write-Host "  ℹ️  PHP not installed locally (using Docker instead)" -ForegroundColor Gray
}

Write-Host ""

# Step 2: Determine execution method
$method = if ($UseLocalPHP -and $hasLocalPHP) { "local" } elseif ($hasDocker) { "docker" } else { "none" }

Write-Host "🔧 Execution Method Selection:" -ForegroundColor Yellow

if ($method -eq "none") {
    Write-Host "  ❌ No valid execution method available" -ForegroundColor Red
    Write-Host ""
    Write-Host "     You need either:" -ForegroundColor Yellow
    Write-Host "     • Docker Desktop" -ForegroundColor Gray
    Write-Host "     • PHP 8.1+ with MySQL client" -ForegroundColor Gray
    exit 1
} elseif ($method -eq "docker") {
    Write-Host "  🐳 Using Docker (recommended, consistent environment)" -ForegroundColor Cyan
    Write-Host ""
} elseif ($method -eq "local") {
    Write-Host "  💻 Using local PHP" -ForegroundColor Cyan
    Write-Host ""
}

# Step 3: Start Docker if needed
if ($method -eq "docker") {
    Write-Host "🐳 Docker Setup:" -ForegroundColor Yellow
    
    $composeFile = Join-Path $RootPath "local\docker\docker-compose.yml"
    
    if (-not (Test-Path $composeFile)) {
        Write-Host "  ❌ docker-compose.yml not found" -ForegroundColor Red
        exit 1
    }
    
    $status = Get-DockerStatus -ComposeFile $composeFile
    
    if ($status -and $status[0].State -eq "running") {
        Write-Host "  ✅ Docker containers already running" -ForegroundColor Green
    } else {
        Write-Host "  ⏳ Starting Docker containers..." -ForegroundColor Cyan
        
        $args = if ($BuildImages) { @("-d", "--build") } else { @("-d") }
        $result = & docker compose -f $composeFile up @args 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✅ Containers started" -ForegroundColor Green
            Write-Host ""
            Write-Host "  ⏳ Waiting for services to be ready..." -ForegroundColor Gray
            Start-Sleep -Seconds 5
        } else {
            Write-Host "  ❌ Failed to start containers" -ForegroundColor Red
            Write-Host "     $result" -ForegroundColor Red
            exit 1
        }
    }
    
    Write-Host ""
}

# Step 4: Verify dependencies
Write-Host "📦 Verifying dependencies..." -ForegroundColor Yellow

if ($method -eq "local" -or $method -eq "docker") {
    # Always check for composer.json
    $composerPath = Join-Path $RootPath "composer.json"
    if (Test-Path $composerPath) {
        Write-Host "  ✅ composer.json found" -ForegroundColor Green
        
        if (Test-ComposerAvailable) {
            Write-Host "  ✅ Composer is available" -ForegroundColor Green
            
            # Check if vendor folder exists
            if (-not (Test-Path "vendor/autoload.php")) {
                Write-Host ""
                Write-Host "  ⏳ Installing PHP dependencies..." -ForegroundColor Cyan
                if ($method -eq "docker") {
                    & docker compose -f $composeFile exec -T web composer install 2>&1 | ForEach-Object {
                        Write-Host "     $_" -ForegroundColor Gray
                    }
                } else {
                    & composer install 2>&1 | ForEach-Object {
                        Write-Host "     $_" -ForegroundColor Gray
                    }
                }
                Write-Host "  ✅ Dependencies installed" -ForegroundColor Green
            } else {
                Write-Host "  ✅ Dependencies already installed" -ForegroundColor Green
            }
        } else {
            Write-Host "  ⚠️  Composer not available (can install manually later)" -ForegroundColor Yellow
        }
    }
}

Write-Host ""

# Step 5: Validate configuration
Write-Host "⚙️  Configuration Check:" -ForegroundColor Yellow

$appConfigPath = Join-Path $RootPath "production\config\app.php"
$dbConfigPath = Join-Path $RootPath "production\config\database.php"

if (Test-Path $appConfigPath) {
    Write-Host "  ✅ App configuration found" -ForegroundColor Green
    $mode = Select-String -Path $appConfigPath -Pattern "'mode'\s*=>\s*'(\w+)'" | ForEach-Object { $_.Matches.Groups[1].Value }
    Write-Host "     Mode: $mode" -ForegroundColor Gray
} else {
    Write-Host "  ❌ App configuration missing" -ForegroundColor Red
}

if (Test-Path $dbConfigPath) {
    Write-Host "  ✅ Database configuration found" -ForegroundColor Green
} else {
    Write-Host "  ❌ Database configuration missing" -ForegroundColor Red
}

Write-Host ""

# Step 6: Show quick start guide
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host "  ✅ Setup Complete!" -ForegroundColor Green
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""

Write-Host "📝 Next Steps:" -ForegroundColor Cyan
Write-Host ""

if ($method -eq "docker") {
    Write-Host "1. Start the development server:" -ForegroundColor White
    Write-Host "   .\scripts\Start-Development.ps1" -ForegroundColor Gray
    Write-Host ""
    Write-Host "2. Initialize database:" -ForegroundColor White
    Write-Host "   .\scripts\Setup-Database.ps1 --seed" -ForegroundColor Gray
    Write-Host ""
} else {
    Write-Host "1. Start the PHP development server:" -ForegroundColor White
    Write-Host "   cd production && php -S 127.0.0.1:8000" -ForegroundColor Gray
    Write-Host ""
    Write-Host "2. Initialize database:" -ForegroundColor White
    Write-Host "   php development/scripts/setup-db.php --seed" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "3. Open in browser:" -ForegroundColor White
Write-Host "   http://127.0.0.1:8000" -ForegroundColor Gray
Write-Host ""

Write-Host "📚 Available Commands:" -ForegroundColor Cyan
Write-Host "  .\scripts\Start-Development.ps1       — Start dev environment" -ForegroundColor Gray
Write-Host "  .\scripts\Setup-Database.ps1          — Initialize database" -ForegroundColor Gray
Write-Host "  .\scripts\Validate-Csvs.ps1           — Validate CSV files" -ForegroundColor Gray
Write-Host "  .\scripts\Seed-DemoData.ps1           — Seed demo data" -ForegroundColor Gray
Write-Host "  .\scripts\Test-Application.ps1        — Run tests" -ForegroundColor Gray
Write-Host "  .\scripts\Stop-Development.ps1        — Stop dev environment" -ForegroundColor Gray
Write-Host ""

Write-Host "💡 Tips:" -ForegroundColor Cyan
Write-Host "  • Set execution policy if needed:" -ForegroundColor Gray
Write-Host "    Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser" -ForegroundColor Gray
Write-Host "  • For full documentation: see docs/DEVELOPMENT.md" -ForegroundColor Gray
Write-Host ""

Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Green
