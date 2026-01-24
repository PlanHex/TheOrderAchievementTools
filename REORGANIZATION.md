# Repository Reorganization Summary

**Date:** January 24, 2026

## What Changed

The repository has been reorganized to cleanly separate production code, development tools, and local infrastructure.

### Before

```
TheOrderAchievementTools/
├── config/
├── data/
├── docs/
├── public/
├── scripts/
├── src/
├── templates/
├── tests/
├── docker-compose.yml
├── Dockerfile
├── composer.json
└── [root config files]
```

**Problem:** Everything was mixed at the root level, making it unclear what should be deployed, what's for development, and what's for testing.

### After

```
TheOrderAchievementTools/
├── production/          ← Deploy this entire folder to servers
│   ├── config/
│   ├── docs/
│   ├── public/
│   ├── src/
│   ├── templates/
│   └── composer.json
├── development/         ← Local development tools & data
│   ├── data/
│   └── scripts/
├── local/               ← Local/CI infrastructure only
│   ├── docker/
│   └── tests/
├── README.md            ← Quick overview
├── DEVELOPMENT.md       ← Local setup guide
├── DEPLOYMENT.md        ← Server deployment guide
├── REPOSITORY_STRUCTURE.md  ← This organization
└── [root config files]
```

**Benefits:**
- ✅ Crystal clear what to deploy (copy `production/` to server)
- ✅ Development tools isolated from production code
- ✅ Tests and Docker setup together in `local/`
- ✅ Easy to add CI/CD workflows
- ✅ Cleaner git history (can ignore entire `local/` in some workflows)

---

## Files Moved

### To `production/`
- `config/` → `production/config/`
- `docs/` → `docs/` (moved to root)
- `public/` → `production/public/`
- `src/` → `production/src/`
- `templates/` → `production/templates/`
- `composer.json` → `production/composer.json`

### To `development/`
- `data/` → `development/data/`
- `scripts/` → `development/scripts/`
- `data/sql/` → `development/data/sql/`

### To `local/`
- `tests/` → `local/tests/`
- `docker-compose.yml` → `local/docker/docker-compose.yml`
- `Dockerfile` → `local/docker/Dockerfile`

### Deleted (obsolete root references)
- Old `config/`, `data/`, `public/`, `scripts/`, `src/`, `templates/`, `tests/`, `docs/` directories at root

---

## Files Created/Updated

### New Documentation
- **README.md** - Updated with new structure and paths
- **DEVELOPMENT.md** - Complete local development guide
- **DEPLOYMENT.md** - Server deployment instructions
- **REPOSITORY_STRUCTURE.md** - This repository organization guide

### Updated Configuration
- **.gitignore** - Updated to reflect new structure
- **.dockerignore** - Updated for new paths
- **local/docker/Dockerfile** - Updated to use new relative paths
- **local/docker/docker-compose.yml** - Updated volume paths

### Updated Scripts
- **development/scripts/smoke.php** - Updated autoloader paths
- **development/scripts/seed_demo.php** - Updated autoloader paths

---

## Path Changes for Developers

### If running the dev server
**Before:**
```bash
cd TheOrderAchievementTools
php -S 127.0.0.1:8000 -t public
```

**After:**
```bash
cd TheOrderAchievementTools/production
php -S 127.0.0.1:8000
```

### If running smoke tests
**Before:**
```bash
php scripts/smoke.php
```

**After:**
```bash
php development/scripts/smoke.php
```

### If running tests locally
**Before:**
```bash
./vendor/bin/phpunit
```

**After:**
```bash
cd production
./vendor/bin/phpunit
```

### If using Docker
**Before:**
```bash
docker compose up --build -d
```

**After:**
```bash
cd local/docker
docker compose up --build -d
```

---

## For Deployment

Simply copy the `production/` folder to your server and configure it as described in [DEPLOYMENT.md](DEPLOYMENT.md).

You don't need anything from `development/` or `local/` on the production server.

---

## Key Principles

1. **`production/`** = Everything a server needs to run the app
2. **`development/`** = Data files and scripts for local dev work
3. **`local/`** = Docker and test infrastructure for local/CI
4. **Root level** = Documentation and config files only

---

## Migration Checklist

- ✅ Directories reorganized
- ✅ Files moved to correct locations
- ✅ Old root directories removed
- ✅ Path references updated in scripts
- ✅ Docker configuration updated
- ✅ Documentation created/updated
- ✅ .gitignore and .dockerignore updated
- ✅ Directory structure verified

---

## Next Steps

1. **For Development:** Read [DEVELOPMENT.md](DEVELOPMENT.md)
2. **For Deployment:** Read [DEPLOYMENT.md](DEPLOYMENT.md)
3. **For Architecture:** See `docs/architecture.md`
4. **For Requirements:** See `docs/requirements.md`

---

## Questions?

- Local setup: See [DEVELOPMENT.md](DEVELOPMENT.md)
- Server setup: See [DEPLOYMENT.md](DEPLOYMENT.md)
- Repository structure: See [REPOSITORY_STRUCTURE.md](REPOSITORY_STRUCTURE.md)
- Architecture: See `production/docs/architecture.md`
