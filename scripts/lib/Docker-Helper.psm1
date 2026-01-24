# Docker Helper Module for PowerShell
# Provides functions for Docker and Docker Compose operations

$ErrorActionPreference = 'Stop'

<#
.SYNOPSIS
    Tests if Docker is installed and running
.DESCRIPTION
    Checks for Docker Desktop and verifies the daemon is accessible
.EXAMPLE
    Test-Docker
    # Returns: $true or $false
#>
function Test-Docker {
    try {
        $null = docker version --format "{{.Server.Version}}"
        return $true
    } catch {
        return $false
    }
}

<#
.SYNOPSIS
    Gets Docker version information
.OUTPUTS
    Object with Version and BuildNumber properties
#>
function Get-DockerVersion {
    try {
        $version = docker version --format "{{.Server.Version}}"
        $build = docker version --format "{{.Server.BuildNumber}}"
        return @{
            Version = $version
            BuildNumber = $build
            Available = $true
        }
    } catch {
        return @{ Available = $false }
    }
}

<#
.SYNOPSIS
    Tests if Docker Compose is available
.OUTPUTS
    Boolean indicating if docker compose command is available
#>
function Test-DockerCompose {
    try {
        $null = docker compose version
        return $true
    } catch {
        return $false
    }
}

<#
.SYNOPSIS
    Waits for a service to be healthy in Docker
.PARAMETER Service
    Name of the service (e.g., 'db', 'web')
.PARAMETER Timeout
    Timeout in seconds (default: 60)
.PARAMETER ComposeFile
    Path to docker-compose.yml
#>
function Wait-ForService {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Service,
        
        [int]$Timeout = 60,
        
        [string]$ComposeFile
    )
    
    $elapsed = 0
    $interval = 2
    
    Write-Host "⏳ Waiting for service '$Service' to be ready..." -ForegroundColor Cyan
    
    while ($elapsed -lt $Timeout) {
        try {
            $compose = if ($ComposeFile) { "-f", $ComposeFile } else { @() }
            $output = docker compose @compose ps $Service --format json | ConvertFrom-Json -ErrorAction SilentlyContinue
            
            if ($output -and $output.State -eq "running") {
                Write-Host "✅ Service '$Service' is running" -ForegroundColor Green
                return $true
            }
        } catch {
            # Service not ready yet, continue waiting
        }
        
        Start-Sleep -Seconds $interval
        $elapsed += $interval
        Write-Host "   Still waiting... ($elapsed/$Timeout)" -ForegroundColor Gray
    }
    
    Write-Host "❌ Service '$Service' failed to start within $Timeout seconds" -ForegroundColor Red
    return $false
}

<#
.SYNOPSIS
    Starts Docker containers using docker-compose
.PARAMETER ComposeFile
    Path to docker-compose.yml (default: local/docker/docker-compose.yml)
.PARAMETER BuildFirst
    Force rebuild of images
#>
function Start-DockerServices {
    param(
        [string]$ComposeFile = "local/docker/docker-compose.yml",
        [switch]$BuildFirst
    )
    
    if (-not (Test-Docker)) {
        Write-Host "❌ Docker is not installed or not running" -ForegroundColor Red
        Write-Host "   Please install Docker Desktop from https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
        return $false
    }
    
    Write-Host "🐳 Starting Docker services..." -ForegroundColor Cyan
    
    try {
        $args = if ($BuildFirst) { "up", "-d", "--build" } else { "up", "-d" }
        docker compose -f $ComposeFile @args
        
        # Wait for MySQL to be ready
        Start-Sleep -Seconds 3
        Write-Host "   Waiting for database to be ready..." -ForegroundColor Gray
        
        # Try to connect to MySQL
        $maxAttempts = 30
        $attempt = 0
        while ($attempt -lt $maxAttempts) {
            try {
                docker compose -f $ComposeFile exec db mysqladmin ping --user=app --password=secret 2>$null | Select-String "mysqld is alive" >$null
                Write-Host "✅ Database is ready" -ForegroundColor Green
                return $true
            } catch {
                $attempt++
                if ($attempt -lt $maxAttempts) {
                    Start-Sleep -Seconds 1
                }
            }
        }
        
        Write-Host "⚠️  Database may not be fully ready, but containers are running" -ForegroundColor Yellow
        return $true
    } catch {
        Write-Host "❌ Failed to start Docker services: $_" -ForegroundColor Red
        return $false
    }
}

<#
.SYNOPSIS
    Stops Docker containers
.PARAMETER ComposeFile
    Path to docker-compose.yml
.PARAMETER RemoveVolumes
    Also remove volumes
#>
function Stop-DockerServices {
    param(
        [string]$ComposeFile = "local/docker/docker-compose.yml",
        [switch]$RemoveVolumes
    )
    
    Write-Host "🛑 Stopping Docker services..." -ForegroundColor Cyan
    
    try {
        $args = @("down")
        if ($RemoveVolumes) {
            $args += "-v"
        }
        docker compose -f $ComposeFile @args
        Write-Host "✅ Services stopped" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "❌ Failed to stop services: $_" -ForegroundColor Red
        return $false
    }
}

<#
.SYNOPSIS
    Executes a command inside a Docker container
.PARAMETER Container
    Container name or service name
.PARAMETER Command
    Command to execute
.PARAMETER ComposeFile
    Path to docker-compose.yml
#>
function Invoke-CommandInDocker {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Container,
        
        [Parameter(Mandatory = $true)]
        [string[]]$Command,
        
        [string]$ComposeFile = "local/docker/docker-compose.yml",
        
        [switch]$PassThru
    )
    
    try {
        $result = docker compose -f $ComposeFile exec -T $Container @Command
        if ($PassThru) {
            return $result
        }
        return $true
    } catch {
        Write-Host "❌ Command failed in container: $_" -ForegroundColor Red
        return $false
    }
}

<#
.SYNOPSIS
    Executes a PHP script inside Docker container
.PARAMETER Script
    Path to PHP script (relative to project root)
.PARAMETER Arguments
    Arguments to pass to PHP script
.PARAMETER ComposeFile
    Path to docker-compose.yml
#>
function Invoke-PhpInDocker {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Script,
        
        [string[]]$Arguments,
        
        [string]$ComposeFile = "local/docker/docker-compose.yml",
        
        [string]$Container = "web"
    )
    
    $cmd = @("php", $Script) + @($Arguments)
    return Invoke-CommandInDocker -Container $Container -Command $cmd -ComposeFile $ComposeFile
}

<#
.SYNOPSIS
    Gets Docker compose status
.PARAMETER ComposeFile
    Path to docker-compose.yml
.OUTPUTS
    Object with container statuses
#>
function Get-DockerStatus {
    param(
        [string]$ComposeFile = "local/docker/docker-compose.yml"
    )
    
    try {
        $output = docker compose -f $ComposeFile ps --format json
        if ($output) {
            return $output | ConvertFrom-Json
        }
        return @()
    } catch {
        return @()
    }
}

<#
.SYNOPSIS
    Shows Docker status in human-readable format
.PARAMETER ComposeFile
    Path to docker-compose.yml
#>
function Show-DockerStatus {
    param(
        [string]$ComposeFile = "local/docker/docker-compose.yml"
    )
    
    $status = Get-DockerStatus -ComposeFile $ComposeFile
    
    if ($status.Count -eq 0) {
        Write-Host "⚪ No Docker services running" -ForegroundColor Yellow
        return
    }
    
    Write-Host "🐳 Docker Services Status:" -ForegroundColor Cyan
    Write-Host ""
    
    foreach ($container in $status) {
        $state = $container.State
        $color = if ($state -eq "running") { "Green" } elseif ($state -eq "exited") { "Red" } else { "Yellow" }
        $icon = if ($state -eq "running") { "✅" } else { "❌" }
        
        Write-Host "  $icon $($container.Service) : $state" -ForegroundColor $color
        Write-Host "     Image: $($container.Image)" -ForegroundColor Gray
        if ($container.Ports) {
            Write-Host "     Ports: $($container.Ports)" -ForegroundColor Gray
        }
    }
    
    Write-Host ""
}

<#
.SYNOPSIS
    Opens logs for a Docker service
.PARAMETER Service
    Service name to watch
.PARAMETER ComposeFile
    Path to docker-compose.yml
.PARAMETER Follow
    Follow logs in real-time
#>
function Show-DockerLogs {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Service,
        
        [string]$ComposeFile = "local/docker/docker-compose.yml",
        
        [switch]$Follow
    )
    
    try {
        $args = @("logs", $Service)
        if ($Follow) {
            $args += "-f"
        }
        docker compose -f $ComposeFile @args
    } catch {
        Write-Host "❌ Failed to get logs: $_" -ForegroundColor Red
    }
}

<#
.SYNOPSIS
    Rebuilds Docker images
.PARAMETER ComposeFile
    Path to docker-compose.yml
.PARAMETER NoPull
    Don't pull base images
#>
function Rebuild-Docker {
    param(
        [string]$ComposeFile = "local/docker/docker-compose.yml",
        [switch]$NoPull
    )
    
    Write-Host "🔨 Rebuilding Docker images..." -ForegroundColor Cyan
    
    try {
        $args = @("up", "-d", "--build")
        if ($NoPull) {
            $args += "--no-pull"
        }
        docker compose -f $ComposeFile @args
        Write-Host "✅ Images rebuilt and services started" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "❌ Build failed: $_" -ForegroundColor Red
        return $false
    }
}

# Export all functions
Export-ModuleMember -Function @(
    'Test-Docker',
    'Get-DockerVersion',
    'Test-DockerCompose',
    'Wait-ForService',
    'Start-DockerServices',
    'Stop-DockerServices',
    'Invoke-CommandInDocker',
    'Invoke-PhpInDocker',
    'Get-DockerStatus',
    'Show-DockerStatus',
    'Show-DockerLogs',
    'Rebuild-Docker'
)
