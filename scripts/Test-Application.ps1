#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Runs application tests
.DESCRIPTION
    Executes PHPUnit tests using local PHP or Docker.
    Can run all tests or filter by suite.
.PARAMETER Suite
    Run specific test suite (Unit, Integration)
.PARAMETER File
    Run specific test file
.PARAMETER UseLocal
    Use local PHP if available (default: auto-detect)
.PARAMETER Coverage
    Generate code coverage report
.PARAMETER Verbose
    Show verbose output
.EXAMPLE
    .\Test-Application.ps1
    # Run all tests

    .\Test-Application.ps1 -Suite Unit
    # Run only unit tests

    .\Test-Application.ps1 -Suite Integration -Verbose
    # Run integration tests with verbose output
#>

param(
    [string]$Suite,
    [string]$File,
    [switch]$UseLocal,
    [switch]$Coverage,
    [switch]$Verbose
)

$ErrorActionPreference = 'Stop'
$RootPath = Split-Path -Parent $MyInvocation.MyCommand.Path | Split-Path -Parent

# Import helper modules
$helperPath = Join-Path $RootPath "scripts\lib"
Import-Module "$helperPath\Docker-Helper.psm1" -Force
Import-Module "$helperPath\Php-Helper.psm1" -Force

Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  🧪 Running Tests" -ForegroundColor Cyan
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Check composer dependencies
$composerPath = Join-Path $RootPath "composer.json"
$vendorPath = Join-Path $RootPath "vendor"

if (-not (Test-Path $vendorPath)) {
    Write-Host "⚠️  Dependencies not installed" -ForegroundColor Yellow
    Write-Host "   Installing with Composer..." -ForegroundColor Gray
    Write-Host ""
    
    if (Test-ComposerAvailable) {
        & composer install --working-dir=$RootPath
    } else {
        Write-Host "❌ Composer not available" -ForegroundColor Red
        exit 1
    }
}

# Determine execution method
$method = if ($UseLocal -and (Test-PhpAvailable)) { "local" } elseif (Test-Docker) { "docker" } else { "none" }

if ($method -eq "none") {
    Write-Host "❌ No execution method available (Docker or local PHP required)" -ForegroundColor Red
    exit 1
}

# Build arguments
$testArgs = @("--colors=always")

if ($Suite) {
    $testArgs += "--testsuite", $Suite
}

if ($File) {
    $testArgs += $File
}

if ($Coverage) {
    $testArgs += "--coverage-text"
}

if ($Verbose) {
    $testArgs += "--verbose"
}

Write-Host "📊 Test Configuration:" -ForegroundColor Yellow
Write-Host "   Suite: $(if ($Suite) { $Suite } else { 'All' })" -ForegroundColor Gray
Write-Host "   Coverage: $(if ($Coverage) { 'Yes' } else { 'No' })" -ForegroundColor Gray
Write-Host "   Method: $(if ($method -eq 'docker') { 'Docker' } else { 'Local PHP' })" -ForegroundColor Gray
Write-Host ""
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""

# Run tests
if ($method -eq "docker") {
    Write-Host "🐳 Running tests in Docker..." -ForegroundColor Cyan
    Write-Host ""
    
    $composeFile = Join-Path $RootPath "local\docker\docker-compose.yml"
    
    if (-not (Test-Path $composeFile)) {
        Write-Host "❌ Docker compose file not found" -ForegroundColor Red
        exit 1
    }
    
    # Ensure containers are running
    $status = Get-DockerStatus -ComposeFile $composeFile
    if (-not $status -or $status[0].State -ne "running") {
        Write-Host "⏳ Starting Docker services..." -ForegroundColor Cyan
        & docker compose -f $composeFile up -d 2>&1 | Where-Object { $_ -notmatch "already in use" }
        Write-Host ""
    }
    
    $cmd = @("vendor/bin/phpunit") + $testArgs
    $result = & docker compose -f $composeFile exec -T web @cmd
    
} else {
    # Local PHP
    Write-Host "💻 Running tests locally..." -ForegroundColor Cyan
    Write-Host ""
    
    $phpunitPath = Join-Path $RootPath "vendor\bin\phpunit"
    if (-not (Test-Path $phpunitPath)) {
        Write-Host "❌ PHPUnit not found. Run composer install first" -ForegroundColor Red
        exit 1
    }
    
    $result = & php $phpunitPath @testArgs
}

Write-Host ""

if ($LASTEXITCODE -eq 0) {
    Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Green
    Write-Host "  ✅ All tests passed!" -ForegroundColor Green
    Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Green
} else {
    Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Red
    Write-Host "  ❌ Tests failed" -ForegroundColor Red
    Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor Red
    exit 1
}

Write-Host ""
