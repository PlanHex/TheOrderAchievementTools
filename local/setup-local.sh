#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="local/docker/docker-compose.yml"
echo "Bringing up containers (docker-compose: $COMPOSE_FILE)..."
docker-compose -f "$COMPOSE_FILE" up -d --build

echo "Waiting for MySQL to accept connections on 127.0.0.1:3306..."
for i in {1..60}; do
  if bash -c "</dev/tcp/127.0.0.1/3306" >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

if ! bash -c "</dev/tcp/127.0.0.1/3306" >/dev/null 2>&1; then
  echo "MySQL did not become ready within timeout. Check Docker Desktop and containers." >&2
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
IMPORT_SQL_PATH="$SCRIPT_DIR/import_data.sql"

SCHEMA_PATH="$(dirname "$SCRIPT_DIR")/data/sql/sql_tables.sql"
if [ ! -f "$SCHEMA_PATH" ]; then
  echo "Schema file not found: $SCHEMA_PATH" >&2
  exit 1
fi

# Write header (up to and including the USE <db>; line) first so the database exists/selected
sed -n '1,/USE [^;]*;/p' "$SCHEMA_PATH" > "$IMPORT_SQL_PATH"

# Add drops to make import idempotent
cat >> "$IMPORT_SQL_PATH" <<'SQL'
-- Drop existing tables to ensure idempotent import
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS user_achievements;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
SET FOREIGN_KEY_CHECKS=1;
SQL

# Append the rest of the schema (table creation & indexes)
sed '1,/USE [^;]*;/d' "$SCHEMA_PATH" >> "$IMPORT_SQL_PATH"

# Then append imports and settings
cat >> "$IMPORT_SQL_PATH" <<'SQL'
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
SQL

echo "Running import by piping SQL into the running 'db' service via docker-compose exec..."
if ! docker-compose -f "$COMPOSE_FILE" exec -T db sh -c "mysql --local-infile=1 --protocol=TCP -h 127.0.0.1 -P 3306 -u root -proot" < "$IMPORT_SQL_PATH"; then
  echo "Import failed." >&2
  exit 1
fi

echo "Import finished. You can now open http://localhost:8000 to view the app."
