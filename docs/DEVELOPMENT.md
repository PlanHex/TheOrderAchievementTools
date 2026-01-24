# Development & Testing Guide

**Updated:** January 24, 2026

This guide covers local development, testing, and deployment workflows for the Achievements Tool.

---

## Quick Start

### 1. Install Dependencies (First Time Only)

```bash
composer install
```

This installs PHPUnit and development tools. The `production/` folder remains free of any dependencies.

### 2. Set Up Database (Development Mode with MySQL)

```bash
# Standard setup (creates database and tables)
php development/scripts/setup-db.php

# Setup with seed data from CSV files
php development/scripts/setup-db.php --seed
```

### 3. Start Development Server

```bash
cd production
php -S 127.0.0.1:8000
```

Open browser to `http://127.0.0.1:8000`

### 4. Run Tests

```bash
vendor/bin/phpunit
```

---

## Detailed Workflows

### Working with Demo Mode (CSV + Session)

No database setup needed:

```bash
# 1. Verify config is set to demo mode
cat production/config/app.php | grep "mode"

# 2. Start server
cd production && php -S 127.0.0.1:8000

# 3. Seed test data into session
php development/scripts/seed_demo.php

# 4. Test the app
# Changes persist only in the current session
```

### Working with Production Mode (MySQL)

```bash
# 1. Update database credentials (if needed)
cat production/config/database.php

# 2. Initialize database
php development/scripts/setup-db.php

# 3. Set mode to production
# Edit production/config/app.php:
#   'mode' => 'production',

# 4. Start server
cd production && php -S 127.0.0.1:8000

# 5. Test the app
# Changes persist in MySQL database
```

### Switching Between Modes

Simply change the `'mode'` setting in `production/config/app.php`:

```php
// Demo mode (CSV data + session storage)
'mode' => 'demo',

// Production mode (MySQL database)
'mode' => 'production',
```

No code changes required — repositories handle the switch automatically via the DI container.

---

## Testing

### Local Testing with MySQL

```bash
# 1. Create test database
php development/scripts/setup-db.php --seed

# 2. Update production/config/app.php for testing
#    'mode' => 'production',

# 3. Run tests
vendor/bin/phpunit

# 4. Tests use the database created above
#    (You may want to create a separate test config)
```

### Local Testing with Demo Mode

```bash
# Tests automatically seed from CSV
# No database setup required
vendor/bin/phpunit
```

The test bootstrap (`tests/bootstrap.php`) automatically configures demo mode if no database is available.

### CI/CD Pipeline Testing

**GitHub Actions / GitLab CI / etc:**

```yaml
# Example CI configuration
- name: Setup PHP
  uses: actions/setup-php@v2
  with:
    php-version: '8.1'

- name: Install dependencies
  run: composer install

- name: Run tests
  run: vendor/bin/phpunit
```

Tests will run using demo mode (CSV-based) and don't require MySQL setup in CI.

---

## Database Management

### Initialize Database from Scratch

```bash
php development/scripts/setup-db.php
```

This:
- Creates the `order_achievements` database
- Creates all tables (categories, achievements, users, user_achievements)
- Sets up foreign key relationships

### Seed Database with Sample Data

```bash
php development/scripts/setup-db.php --seed
```

This additionally imports data from CSV files:
- `data/categories.csv`
- `data/achievements.csv`
- `data/users.csv`
- `data/user_achievements.csv`

### Regenerate CSV Files from Database

```bash
.\data\scripts\csv_generator.ps1
```

(Windows PowerShell) Exports current database data to CSV files.

### Use Custom Database Config

```bash
php development/scripts/setup-db.php --config=/path/to/custom-db.php
```

Useful for test database configurations separate from development.

---

## Development Scripts

Located in `development/scripts/`:

### check_csvs.php
Validates CSV file structure and presence.

```bash
php development/scripts/check_csvs.php
```

### seed_demo.php
Seeds PHP session with demo CSV data (demo mode).

```bash
php development/scripts/seed_demo.php
```

### smoke.php
Smoke test validating repository functionality and CSV/DB counts.

```bash
php development/scripts/smoke.php
```

### setup-db.php
Database initialization script (NEW).

```bash
php development/scripts/setup-db.php [--config=PATH] [--seed]
```

---

## Testing with Docker (Recommended for CI)

### Prerequisites
- Docker Desktop installed
- Docker Compose

### Start Development Container

```bash
cd local/docker
docker compose up -d
```

This:
- Builds PHP 8.3 container with Apache and Xdebug
- Starts MySQL container with initialized database
- Mounts source code into container
- Available at `http://localhost:8000/`

### Run Tests in Container

```bash
docker exec app vendor/bin/phpunit
```

### Stop and Clean Up

```bash
docker compose down
```

### Docker Configuration

**File:** `local/docker/docker-compose.yml`

Environment variables:
- `DB_HOST`: localhost (from container perspective)
- `DB_USER`: app
- `DB_PASS`: secret
- `DB_NAME`: order_achievements

Update `production/config/database.php` to match when using Docker.

---

## Project Structure for Development

```
production/          ← Deployable code (no dependencies)
├── src/             ← All application code
├── public/          ← Web root (index.php entry point)
├── config/          ← Configuration files
├── templates/       ← Layout templates
└── [NO composer.json, NO sql files]

development/        ← Development tools and scripts
├── scripts/
│   ├── check_csvs.php
│   ├── seed_demo.php
│   ├── smoke.php
│   └── setup-db.php       ← Database setup

local/              ← Local infrastructure
├── docker/          ← Docker configuration
    ├── Dockerfile
    └── docker-compose.yml

data/               ← Data files (shared)
├── achievements.csv
├── categories.csv
├── users.csv
├── user_achievements.csv
├── scripts/         ← CSV generation utilities
└── sql/
    └── sql_tables.sql    ← Database schema

composer.json       ← Root level (testing dependencies)
phpunit.xml         ← Root level (test configuration)
tests/              ← Test files
├── bootstrap.php
├── Unit/
└── Integration/
```

---

## Troubleshooting

### "No database available — switching to demo mode"
**Cause:** MySQL credentials are invalid or database doesn't exist  
**Fix:** Run `php development/scripts/setup-db.php`

### Tests are failing with "table doesn't exist"
**Cause:** Test database wasn't initialized  
**Fix:** Ensure `production/config/app.php` is set to `'mode' => 'demo'` for CI testing, OR run setup-db.php before tests

### Port 8000 already in use
**Cause:** Another process is using the port  
**Fix:** Use a different port: `php -S 127.0.0.1:8001`

### Docker container won't start
**Cause:** Port 80 or 3306 already in use  
**Fix:** Edit `local/docker/docker-compose.yml` to use different ports:
```yaml
ports:
  - "8001:80"    # Changed from 8000:80
  - "3307:3306"  # Changed from 3306:3306
```

---

## Deployment Checklist

Before deploying to production:

- [ ] Set `production/config/app.php` → `'mode' => 'production'`
- [ ] Update database credentials in `production/config/database.php`
- [ ] Run `php development/scripts/setup-db.php` on production server
- [ ] Verify `production/` folder contains NO external dependencies
- [ ] Run smoke tests: `php development/scripts/smoke.php`
- [ ] Test both demo and production modes locally first

---

## Environment-Specific Configuration

### Development (Local)

```php
// production/config/app.php
'mode' => 'demo',  // Use CSV-based demo mode
'auth_enabled' => false,
```

### Testing (CI/CD)

```php
'mode' => 'demo',  // Use CSV (no MySQL needed in CI)
'auth_enabled' => false,
```

### Production (Deployed)

```php
'mode' => 'production',  // Use MySQL
'auth_enabled' => true,  // Require Basic Auth
```

---

## Need Help?

See the following docs:
- **Architecture:** `docs/architecture.md`
- **Requirements:** `docs/requirements.md`
- **Testing Options:** `TESTING_OPTIONS.md` (overview of testing approaches)
- **Alignment:** `ALIGNMENT_CHECKLIST.md` (verification of features)
