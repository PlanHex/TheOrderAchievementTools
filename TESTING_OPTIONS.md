# **Testing Setup Options**

This document outlines options for maintaining local and CI testing without keeping dependencies in the production folder.

## **Current Structure**

- **production/** → Pure PHP MVC (no external deps, no composer.json, no sql files)
- **development/** → Development scripts and testing tools
- **local/** → Docker setup for local development
- **data/** → CSV data, shared by both modes
- **composer.json** (root) → PHPUnit and testing configuration
- **data/sql/sql_tables.sql** → Database schema (removed from production/)

---

## **Option 1: Development-Centric Testing (Recommended)**

### Setup
```
development/
├── scripts/
│   ├── check_csvs.php
│   ├── seed_demo.php
│   ├── smoke.php
│   └── setup-db.php          ← NEW: Initialize MySQL database
├── testing/                   ← NEW: Test utilities
│   ├── create-test-db.php     ← Create test database
│   └── cleanup-test-db.php    ← Cleanup after tests
└── config/                    ← NEW: Dev-only configs
    └── test-db-config.php     ← Test database credentials
```

### How It Works
- **Local Dev:** Run `php development/scripts/setup-db.php` to initialize MySQL
- **Tests:** Use `development/testing/` scripts to set up test database before running PHPUnit
- **CI:** Run setup scripts before test suite
- **Production:** Only needs `production/` folder with no external dependencies

### Advantages
✅ Production remains clean and framework-free  
✅ Development/test concerns isolated in `development/`  
✅ SQL schema stays in `data/sql/` (shared reference)  
✅ Test database separate from production  
✅ Clear separation of concerns  

### Disadvantages
- Requires manual setup step before running tests locally

---

## **Option 2: Separate Testing Folder**

### Setup
```
tests/
├── bootstrap.php             ← Moved from production/
├── setup.php                 ← NEW: Test database setup
├── Unit/
│   └── ContainerTest.php     ← Moved from production/
└── Integration/
    └── RepositoryIntegrationTest.php ← Moved from production/

phpunit.xml                    ← Moved from root to root (unchanged)
composer.json                  ← Root level (unchanged)
```

### How It Works
- Tests reference `production/src/` directly via PSR-4
- Test setup script initializes test database
- PHPUnit runs from root with `vendor/bin/phpunit`
- CI uses same setup: `php tests/setup.php && vendor/bin/phpunit`

### Advantages
✅ Tests are peer to production, not inside it  
✅ Clear test organization  
✅ Uses composer.json at root (already there)  
✅ Standard PHPUnit workflow  

### Disadvantages
- Requires bootstrap configuration to load `production/src/`

---

## **Option 3: Docker-Based Testing (Best for CI)**

### Setup
```
local/docker/
├── Dockerfile                ← Includes test environment
├── docker-compose.yml        ← Includes test MySQL service
├── test.docker-compose.yml   ← NEW: Isolated test environment
└── scripts/
    └── run-tests.sh          ← NEW: Run tests in container
```

### How It Works
1. **Local Dev:** 
   - `docker compose up -d` → start dev environment
   - `docker exec app vendor/bin/phpunit` → run tests in container

2. **CI/CD:**
   - Spin up test containers
   - Run `docker compose -f test.docker-compose.yml up --abort-on-container-exit`
   - Automatically cleans up after

### Advantages
✅ Isolated test environment  
✅ No dependencies on local MySQL  
✅ Reproducible test runs  
✅ Easy CI/CD integration  
✅ Production code untouched  

### Disadvantages
- Requires Docker for local testing
- More complex setup

---

## **Option 4: Hybrid Approach (Flexibility)**

Combine Option 1 + Option 2:
- **development/scripts/** for quick local setup
- **tests/** for organized test code
- **local/docker/** for containerized CI testing
- **composer.json** at root only (testing dependencies)

**Workflow:**
```bash
# Local development with MySQL
php development/scripts/setup-db.php
cd production && php -S 127.0.0.1:8000

# Run tests locally
vendor/bin/phpunit

# Or run tests in Docker
docker compose -f local/docker/test.docker-compose.yml up --abort-on-container-exit
```

---

## **Recommendation**

**Use Option 4 (Hybrid)** because:
1. ✅ Production stays framework-free and clean
2. ✅ Developers have flexibility (local or Docker)
3. ✅ CI/CD can use either approach
4. ✅ Low barrier to entry for local development
5. ✅ Scales to containerized production deployments

### Implementation Steps

**Immediate:**
1. Delete `production/composer.json` and `production/sql_tables.sql`
2. Verify root-level files are in place:
   - `composer.json` (exists at root)
   - `data/sql/sql_tables.sql` (exists at root)
   - `phpunit.xml` (at root)

**Development Scripts** (in `development/scripts/`):
- Add `setup-db.php` to initialize MySQL with schema
- Update `smoke.php` to verify both mode capabilities

**Docker** (optional, for CI):
- Add `local/docker/test.docker-compose.yml`
- Add test initialization to `local/docker/Dockerfile`

**Documentation:**
- Update `docs/DEVELOPMENT.md` with testing workflow options
- Add quick reference for `composer install` and test commands

---

## **Summary**

| Aspect | Option 1 | Option 2 | Option 3 | Option 4 |
|--------|----------|----------|----------|----------|
| Production clean | ✅ | ✅ | ✅ | ✅ |
| Local dev easy | ✅ | ✅ | ⚠️ | ✅ |
| CI/CD ready | ✅ | ✅ | ✅ | ✅ |
| No local setup | ❌ | ⚠️ | ❌ | ✅ |
| Complexity | Low | Medium | High | Medium |

**Best choice for most teams:** Option 4 (Hybrid)
