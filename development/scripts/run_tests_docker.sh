#!/usr/bin/env bash
set -euo pipefail

# Builds the docker compose stack and runs PHPUnit inside the `web` service.
# Requires docker & docker-compose v2 (docker compose) available locally.


echo "Building docker images..."
docker compose build --pull --no-cache

echo "Installing PHP dependencies inside web container (Composer)..."
# Run composer inside a short-lived container based on the web service image
docker compose run --rm web composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Bringing up docker-compose stack..."
docker compose up -d

echo "Waiting for DB (mysql) to be ready..."
for i in {1..30}; do
  if docker compose exec -T db mysqladmin ping -h "127.0.0.1" --silent; then
    echo "DB is up"
    break
  fi
  sleep 1
done

echo "Running PHPUnit inside web container..."
docker compose exec -T web php vendor/bin/phpunit --colors=always

echo "Tearing down docker-compose stack..."
docker compose down -v
