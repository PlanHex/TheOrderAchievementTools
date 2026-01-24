# PHP Helper Module for PowerShell
# Provides functions for PHP detection and execution

$ErrorActionPreference = 'Stop'

<#
.SYNOPSIS
    Tests if PHP is installed and available in PATH
.PARAMETER Version
    Specific PHP version to require (e.g., "8.1")
.OUTPUTS
    Boolean indicating if PHP is available
#>
function Test-PhpAvailable {
    param(
        [string]$Version
    )
    
    try {
        $output = php -v 2>$null
        if (-not $output) {
            return $false
        }
        
        if ($Version) {
            $match = $output[0] -match "PHP $Version"
            return $match
        }
        
        return $true
    } catch {
        return $false
    }
}

<#
.SYNOPSIS
    Gets PHP version information
.OUTPUTS
    Object with Version, BuildDate, and other info
#>
function Get-PhpVersion {
    try {
        $output = php -v
        if ($output) {
            $match = $output[0] -match "PHP (\d+\.\d+\.\d+)"
            if ($match) {
                return @{
                    Version = $matches[1]
                    Available = $true
                    Output = $output[0]
                }
            }
        }
        return @{ Available = $false }
    } catch {
        return @{ Available = $false }
    }
}

<#
.SYNOPSIS
    Tests if Composer is installed
.OUTPUTS
    Boolean indicating if composer is available
#>
function Test-ComposerAvailable {
    try {
        $null = composer --version 2>$null
        return $true
    } catch {
        return $false
    }
}

<#
.SYNOPSIS
    Gets Composer version information
.OUTPUTS
    Object with Version and other info
#>
function Get-ComposerVersion {
    try {
        $output = composer --version
        return @{
            Available = $true
            Output = $output
        }
    } catch {
        return @{ Available = $false }
    }
}

<#
.SYNOPSIS
    Executes a PHP command/script locally
.PARAMETER Script
    PHP script path or inline PHP code
.PARAMETER Arguments
    Arguments to pass to PHP
.PARAMETER Inline
    Treat Script as inline PHP code instead of file
#>
function Invoke-PhpLocally {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Script,
        
        [string[]]$Arguments,
        
        [switch]$Inline,
        
        [switch]$PassThru
    )
    
    try {
        if (-not (Test-PhpAvailable)) {
            throw "PHP is not available in PATH"
        }
        
        if ($Inline) {
            $result = php -r $Script
        } else {
            $result = php $Script @Arguments
        }
        
        if ($PassThru) {
            return $result
        }
        return $true
    } catch {
        Write-Host "❌ PHP execution failed: $_" -ForegroundColor Red
        return $false
    }
}

<#
.SYNOPSIS
    Installs Composer dependencies
.PARAMETER Path
    Directory containing composer.json (default: current directory)
.PARAMETER NoDev
    Skip development dependencies
#>
function Install-ComposerDependencies {
    param(
        [string]$Path = ".",
        [switch]$NoDev
    )
    
    if (-not (Test-ComposerAvailable)) {
        Write-Host "❌ Composer is not available" -ForegroundColor Red
        Write-Host "   See: https://getcomposer.org/download/" -ForegroundColor Yellow
        return $false
    }
    
    Write-Host "📦 Installing Composer dependencies..." -ForegroundColor Cyan
    
    try {
        $args = @("install", "--working-dir=$Path")
        if ($NoDev) {
            $args += "--no-dev"
        }
        & composer @args
        Write-Host "✅ Dependencies installed" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "❌ Failed to install dependencies: $_" -ForegroundColor Red
        return $false
    }
}

<#
.SYNOPSIS
    Runs PHPUnit tests locally
.PARAMETER TestSuite
    Specific test suite to run (Unit, Integration, etc.)
.PARAMETER Path
    Project path (default: current directory)
#>
function Test-PhpUnitLocally {
    param(
        [string]$TestSuite,
        [string]$Path = "."
    )
    
    if (-not (Test-PhpAvailable)) {
        Write-Host "⚠️  PHP not available locally, use Test-PhpUnitInDocker instead" -ForegroundColor Yellow
        return $false
    }
    
    Write-Host "🧪 Running PHPUnit tests..." -ForegroundColor Cyan
    
    try {
        $args = @("vendor/bin/phpunit")
        if ($TestSuite) {
            $args += "--testsuite", $TestSuite
        }
        
        & cmd /c $($args -join " ")
        return $true
    } catch {
        Write-Host "❌ Tests failed: $_" -ForegroundColor Red
        return $false
    }
}

<#
.SYNOPSIS
    Checks if local PHP can connect to database
.PARAMETER Host
    Database host
.PARAMETER User
    Database user
.PARAMETER Password
    Database password
.PARAMETER Database
    Database name
#>
function Test-DatabaseConnection {
    param(
        [string]$Host = "localhost",
        [string]$User = "app",
        [string]$Password = "secret",
        [string]$Database = "order_achievements"
    )
    
    $script = @"
try {
    `$pdo = new PDO('mysql:host=$Host;dbname=$Database', '$User', '$Password');
    echo 'OK';
} catch (PDOException `$e) {
    echo 'FAIL: ' . `$e->getMessage();
    exit(1);
}
"@
    
    try {
        $result = php -r $script 2>&1
        if ($result -eq "OK") {
            return $true
        }
        Write-Host "   Connection failed: $result" -ForegroundColor Yellow
        return $false
    } catch {
        return $false
    }
}

<#
.SYNOPSIS
    Gets information about local PHP installation
.OUTPUTS
    Object with detailed PHP information
#>
function Get-PhpInfo {
    try {
        $version = Get-PhpVersion
        $composer = Get-ComposerVersion
        
        return @{
            PhpAvailable = $version.Available
            PhpVersion = $version.Version
            Composer = @{
                Available = $composer.Available
                Output = $composer.Output
            }
            Extensions = (php -m 2>$null | Where-Object { $_ -match '\w+' })
        }
    } catch {
        return @{ Error = $_ }
    }
}

<#
.SYNOPSIS
    Shows local PHP configuration summary
#>
function Show-PhpInfo {
    $info = Get-PhpInfo
    
    Write-Host "📋 PHP Configuration:" -ForegroundColor Cyan
    Write-Host ""
    
    if ($info.PhpAvailable) {
        Write-Host "  ✅ PHP Version: $($info.PhpVersion)" -ForegroundColor Green
    } else {
        Write-Host "  ❌ PHP: Not available" -ForegroundColor Red
    }
    
    if ($info.Composer.Available) {
        Write-Host "  ✅ Composer: Available" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Composer: Not available" -ForegroundColor Yellow
    }
    
    Write-Host ""
    Write-Host "  Key Extensions:" -ForegroundColor Gray
    $key = $info.Extensions | Where-Object { $_ -match "pdo|mysql|curl|json" }
    foreach ($ext in $key) {
        Write-Host "    • $ext" -ForegroundColor Gray
    }
    Write-Host ""
}

<#
.SYNOPSIS
    Determines whether to use local PHP or Docker
.PARAMETER PreferDocker
    Force Docker even if PHP is available
.OUTPUTS
    String: "local" or "docker"
#>
function Get-PhpExecutionMethod {
    param(
        [switch]$PreferDocker
    )
    
    if ($PreferDocker -or -not (Test-PhpAvailable)) {
        return "docker"
    }
    return "local"
}

# Export all functions
Export-ModuleMember -Function @(
    'Test-PhpAvailable',
    'Get-PhpVersion',
    'Test-ComposerAvailable',
    'Get-ComposerVersion',
    'Invoke-PhpLocally',
    'Install-ComposerDependencies',
    'Test-PhpUnitLocally',
    'Test-DatabaseConnection',
    'Get-PhpInfo',
    'Show-PhpInfo',
    'Get-PhpExecutionMethod'
)
