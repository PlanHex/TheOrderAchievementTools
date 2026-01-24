Param()

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Write-Host "Building docker images..."
docker compose build --pull --no-cache

Write-Host "Installing PHP dependencies inside web container (Composer)..."
docker compose run --rm web composer install --no-interaction --prefer-dist --optimize-autoloader

Write-Host "Bringing up docker-compose stack..."
docker compose up -d

Write-Host "Waiting for DB (mysql) to be ready..."
for ($i = 0; $i -lt 30; $i++) {
    try {
        docker compose exec -T db mysqladmin ping -h "127.0.0.1" --silent | Out-Null
        Write-Host "DB is up"
        break
    } catch {
        Start-Sleep -Seconds 1
    }
}

Write-Host "Running PHPUnit inside web container..."
docker compose exec -T web php vendor/bin/phpunit --colors=always

Write-Host "Tearing down docker-compose stack..."
docker compose down -v
