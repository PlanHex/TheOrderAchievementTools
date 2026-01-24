# Quick Reference: Testing & Development Commands

**Keep this handy while developing.**

---

## 🚀 Get Started (First Time)

```bash
# Install dependencies
composer install

# Choose one setup below...
```

---

## 🐬 Option A: Demo Mode (CSV-Based, No Database)

**Best for:** Quick testing, CI/CD, learning  
**Speed:** ⚡⚡⚡ Instant

```bash
# 1. Ensure demo mode is set
cat production/config/app.php | grep "'mode'"

# 2. Start server
cd production
php -S 127.0.0.1:8000

# 3. (Optional) Seed session with test data
php development/scripts/seed_demo.php

# 4. Visit http://127.0.0.1:8000
```

---

## 🗄️ Option B: MySQL Mode (Persistent Database)

**Best for:** Realistic testing, data persistence  
**Speed:** ⚡ Normal

```bash
# 1. Initialize database
php development/scripts/setup-db.php --seed

# 2. Update config to MySQL
# Edit production/config/app.php:
#   'mode' => 'production',

# 3. Update DB credentials if needed
cat production/config/database.php

# 4. Start server
cd production
php -S 127.0.0.1:8000

# 5. Visit http://127.0.0.1:8000
```

---

## 🐳 Option C: Docker (Containerized)

**Best for:** Isolated environment, CI/CD  
**Speed:** ⚡ Normal (includes container startup)

```bash
# 1. Start containers
cd local/docker
docker compose up -d

# 2. Watch logs
docker compose logs -f app

# 3. Access app
# http://localhost:8000

# 4. Run tests in container
docker exec app vendor/bin/phpunit

# 5. Stop when done
docker compose down
```

---

## 🧪 Running Tests

```bash
# All tests
vendor/bin/phpunit

# Specific suite
vendor/bin/phpunit --testsuite Unit
vendor/bin/phpunit --testsuite Integration

# Single test file
vendor/bin/phpunit tests/Unit/ContainerTest.php

# Stop on first failure
vendor/bin/phpunit --stop-on-failure

# Verbose output
vendor/bin/phpunit --verbose
```

---

## 🔧 Database Commands

```bash
# Create fresh database (schema only)
php development/scripts/setup-db.php

# Create database with sample data
php development/scripts/setup-db.php --seed

# Use custom database config
php development/scripts/setup-db.php --config=/path/to/config.php

# Help and options
php development/scripts/setup-db.php --help
```

---

## 📊 Verification Scripts

```bash
# Verify CSV files
php development/scripts/check_csvs.php

# Smoke test (verify repos work)
php development/scripts/smoke.php

# Seed demo session
php development/scripts/seed_demo.php
```

---

## 🔄 Switching Modes

```bash
# Check current mode
grep "mode" production/config/app.php

# Switch to demo (no database needed)
# Edit production/config/app.php:
#   'mode' => 'demo',

# Switch to production (MySQL)
# Edit production/config/app.php:
#   'mode' => 'production',
```

---

## 🌐 Development Server Ports

```bash
# Default port
php -S 127.0.0.1:8000

# Different port (if 8000 is in use)
php -S 127.0.0.1:8001

# Listen on all interfaces
php -S 0.0.0.0:8000
```

---

## 📁 Important Files

| File | Purpose |
|------|---------|
| `production/config/app.php` | Mode selection (demo/production) |
| `production/config/database.php` | MySQL credentials |
| `docs/DEVELOPMENT.md` | Full development guide |
| `development/scripts/setup-db.php` | Database initialization |
| `data/sql/sql_tables.sql` | Database schema |

---

## ⚡ Speed Comparison

| Approach | Setup Time | Startup | Test Run |
|----------|------------|---------|----------|
| Demo Mode | 0s | <1s | ~5s |
| MySQL | 2-3s | 1s | ~5s |
| Docker | 20-30s | 2-3s | ~5s |

---

## 🐛 Troubleshooting

### Port 8000 already in use
```bash
php -S 127.0.0.1:8001  # Use 8001 instead
```

### Database connection error
```bash
# Check MySQL is running
mysql -u app -p  # password: secret

# Reinitialize database
php development/scripts/setup-db.php
```

### Tests fail with "table doesn't exist"
```bash
# Ensure demo mode is set
grep "'mode'" production/config/app.php

# Or initialize MySQL
php development/scripts/setup-db.php
```

### Docker won't start (port conflict)
```bash
# Edit local/docker/docker-compose.yml
# Change ports: "8001:80" and "3307:3306"
```

---

## 📚 Full Docs

- **Development Guide:** `docs/DEVELOPMENT.md`
- **Requirements:** `docs/requirements.md`
- **Architecture:** `docs/architecture.md`
- **Testing Options:** `TESTING_OPTIONS.md`
- **Alignment Checklist:** `ALIGNMENT_CHECKLIST.md`

---

## 🎯 Typical Workflow

```bash
# Day 1: Setup
composer install
php development/scripts/setup-db.php --seed

# Daily: Develop
cd production && php -S 127.0.0.1:8000
# Make changes...

# Before commit: Test
vendor/bin/phpunit

# To switch testing mode
# Edit production/config/app.php and restart server
```

---

**💡 Tip:** Bookmark this file for quick reference!
