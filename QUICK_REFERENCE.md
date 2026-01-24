# Quick Reference Guide

A quick cheat sheet for common tasks with the reorganized repository.

## Essential Folders

| Folder | Purpose | Deploy? |
|--------|---------|---------|
| `production/` | Application code | ✅ **YES** |
| `development/` | Data & dev scripts | ❌ No |
| `local/` | Docker & tests | ❌ No |

## Common Tasks

### Start Development Server
```bash
cd production
php -S 127.0.0.1:8000
```

### Run Tests
```bash
cd production
./vendor/bin/phpunit
```

### Run Development Utilities
```bash
# Validate CSV files
php development/scripts/check_csvs.php

# Smoke test repositories
php development/scripts/smoke.php

# Regenerate CSVs
cd development/data/scripts
./csv_generator.ps1
```

### Use Docker
```bash
cd local/docker
docker compose up --build -d
# App at http://localhost:8000/
docker compose down
```

### Deploy to Server
1. Copy `production/` to server
2. Update `production/config/app.php` and `production/config/database.php`
3. Import `data/sql/sql_tables.sql` into MySQL
4. Point web server to `production/public/`

## Directory Structure

```
production/          ← Deploy this folder to servers
  config/            Configuration files
  public/            Web root (index.php, assets)
  src/               Application source code
  templates/         Shared HTML layouts
  docs/              Architecture documentation
  composer.json

development/         ← Development-only tools
  data/              CSV files and demo data
  scripts/           Utility scripts

local/               ← Local infrastructure
  docker/            Docker setup
  tests/             PHPUnit tests
```

## Key Files

| File | Purpose |
|------|---------|
| `production/config/app.php` | Mode selection & auth settings |
| `production/config/database.php` | MySQL credentials |
| `production/public/index.php` | Application entry point |
| `development/data/*.csv` | Demo data files |
| `development/data/sql/sql_tables.sql` | Database schema |
| `local/docker/docker-compose.yml` | Docker configuration |

## Configuration

### Demo Mode (Local Development)
Edit `production/config/app.php`:
```php
'mode' => 'demo'
```

### Production Mode (MySQL)
Edit `production/config/app.php`:
```php
'mode' => 'production'
```

Edit `production/config/database.php`:
```php
'host'     => 'your-database-host',
'user'     => 'your-database-user',
'password' => 'your-database-password',
'database' => 'your-database-name',
```

## Documentation

- **README.md** - Overview and quick start
- **DEVELOPMENT.md** - Complete local development guide
- **DEPLOYMENT.md** - Server deployment instructions
- **REPOSITORY_STRUCTURE.md** - Detailed structure explanation
- **REORGANIZATION.md** - What changed and why
- **docs/architecture.md** - System architecture
- **docs/requirements.md** - Features and requirements

## Scripts

### Check CSVs
```bash
php development/scripts/check_csvs.php
```

### Smoke Test
```bash
php development/scripts/smoke.php
```

### Seed Demo Data
```bash
php development/scripts/seed_demo.php
```

### Regenerate CSVs
```bash
cd data/scripts
./csv_generator.ps1
```

## Docker Commands

```bash
# Start services
cd local/docker
docker compose up --build -d

# Stop services
docker compose down

# View logs
docker compose logs -f

# Run tests inside Docker
docker compose exec web php vendor/bin/phpunit

# Access MySQL
mysql -h 127.0.0.1 -u app -p -D order_achievements
# Password: secret
```

## Troubleshooting

### 404 Errors (No Routes Working)
- Are you in `production/` directory?
- Is `.htaccess` in `production/public/`?
- Check `config/app.php` mode is correct

### Can't Connect to Database
- Check `config/database.php` credentials
- Is MySQL running?
- Try: `mysql -h host -u user -p database`

### CSV Files Not Loading
- Run: `php development/scripts/check_csvs.php`
- Check files exist in `data/`
- Set mode to `'demo'` in `config/app.php`

### Tests Failing
- Run: `cd production && ./vendor/bin/phpunit`
- Check error messages carefully
- Read `local/tests/` for test code

---

For detailed guides, see:
- Local setup: [DEVELOPMENT.md](DEVELOPMENT.md)
- Server setup: [DEPLOYMENT.md](DEPLOYMENT.md)
- Architecture: [docs/architecture.md](docs/architecture.md)
