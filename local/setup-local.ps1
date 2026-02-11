param()

$composeFile = "local/docker/docker-compose.yml"
Write-Host "Bringing up containers (docker-compose: $composeFile)..."
docker-compose -f $composeFile up -d --build

Write-Host "Waiting for MySQL to accept connections on 127.0.0.1:3306..."
$ready = $false
for ($i = 0; $i -lt 60; $i++) {
    if (Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet) {
        $ready = $true
        break
    }
    Start-Sleep -Seconds 2
}
if (-not $ready) {
    Write-Error "MySQL did not become ready within timeout. Check Docker Desktop and containers."
    exit 1
}

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$importSqlPath = Join-Path $scriptDir "import_data.sql"

Write-Host "Writing import SQL to $importSqlPath"
$schemaPath = Join-Path $scriptDir "..\data\sql\sql_tables.sql"
if (-not (Test-Path $schemaPath)) {
    Write-Error "Schema file not found at $schemaPath"
    exit 1
}
$schemaSql = Get-Content -Path $schemaPath -Raw -Encoding UTF8
$append = @'
SET FOREIGN_KEY_CHECKS=0;
SET GLOBAL local_infile = 1;

-- Import CSVs (pipe-separated, fields enclosed by ")
LOAD DATA LOCAL INFILE '/data/categories.csv' INTO TABLE categories
FIELDS TERMINATED BY '|' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES (id,name,display_order);

LOAD DATA LOCAL INFILE '/data/users.csv' INTO TABLE users
FIELDS TERMINATED BY '|' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES (id,name);

LOAD DATA LOCAL INFILE '/data/achievements.csv' INTO TABLE achievements
FIELDS TERMINATED BY '|' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES (id,category_id,title,description,points,image_url,display_order);

LOAD DATA LOCAL INFILE '/data/user_achievements.csv' INTO TABLE user_achievements
FIELDS TERMINATED BY '|' OPTIONALLY ENCLOSED BY '"' LINES TERMINATED BY '\r\n' IGNORE 1 LINES (user_id,achievement_id,display_order);

SET FOREIGN_KEY_CHECKS=1;
'@

$dropStmt = @'
-- Drop existing tables to ensure idempotent import
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS user_achievements;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
SET FOREIGN_KEY_CHECKS=1;
'@
## Split schema SQL into header (up to and including USE <db>;) and body (the CREATE TABLE statements)
$useMatch = [regex]::Match($schemaSql, '(?i)USE\s+[^;]+;')
if ($useMatch.Success) {
    $schemaHeader = $schemaSql.Substring(0, $useMatch.Index + $useMatch.Length)
    $schemaBody = $schemaSql.Substring($useMatch.Index + $useMatch.Length)
} else {
    # Fallback: no explicit USE found — treat entire schema as body
    $schemaHeader = ""
    $schemaBody = $schemaSql
}

# Ensure database is created and selected before dropping tables
$sql = $schemaHeader + "`n" + $dropStmt + "`n" + $schemaBody + "`n" + $append
Set-Content -Path $importSqlPath -Value $sql -Encoding UTF8

Write-Host "Running import by piping SQL into the running 'db' service via docker-compose exec..."
try {
    $cwd = (Get-Location).ProviderPath
    $composeFull = Resolve-Path -Path (Join-Path $cwd $composeFile) -ErrorAction Stop
    Write-Host "Using compose file: $composeFull"
    # Force TCP protocol to avoid socket connection errors inside the container
    Get-Content -Path $importSqlPath -Raw -Encoding UTF8 | & docker-compose -f $composeFull exec -T db sh -c "mysql --local-infile=1 --protocol=TCP -h 127.0.0.1 -P 3306 -u root -proot"
} catch {
    Write-Error "Import failed: $_"
    exit 1
}

Write-Host "Import finished. You can now open http://localhost:8000 to view the app."
