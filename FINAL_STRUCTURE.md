# Final Repository Structure

**Updated:** January 24, 2026

## Summary

The documentation has been moved from `production/docs/` to a root-level `docs/` folder. This separation makes it clear that:
- **Production code** is in `production/`
- **Development tools & data** are in `development/`
- **Local infrastructure** (Docker/tests) is in `local/`
- **Documentation** is at the root level in `docs/`

## Final Structure

```
TheOrderAchievementTools/
│
├── production/               ← Deploy this to servers
│   ├── config/              Configuration (app.php, database.php)
│   ├── public/              Web root (index.php, assets)
│   ├── src/                 Application code (Controllers, Models, Repos)
│   ├── templates/           Shared layout files
│   └── composer.json        PHP dependencies
│
├── development/             ← Local development only
│   ├── data/               CSV files, SQL schema
│   └── scripts/            Utility scripts (smoke.php, seed_demo.php, etc.)
│
├── local/                   ← Local/CI infrastructure
│   ├── docker/             Dockerfile, docker-compose.yml
│   └── tests/              PHPUnit test suite
│
├── docs/                    ← Documentation (NOT deployed!)
│   ├── architecture.md      System architecture & design
│   ├── requirements.md      Features & specifications
│   └── ALIGNMENT_CHECKLIST.md  Implementation checklist
│
├── .github/
│   └── copilot-instructions.md  AI agent guidelines (UPDATED)
│
└── Root Documentation & Config
    ├── README.md             Quick overview
    ├── DEVELOPMENT.md        Local setup guide
    ├── DEPLOYMENT.md         Production deployment
    ├── QUICK_REFERENCE.md    Cheat sheet
    ├── REPOSITORY_STRUCTURE.md  Detailed structure
    ├── REORGANIZATION.md     What changed & why
    ├── composer.json         Root config
    ├── phpunit.xml           Test config
    ├── .gitignore            Git ignore rules
    └── .dockerignore         Docker ignore rules
```

## What Changed

### Files Moved
- `production/docs/architecture.md` → `docs/architecture.md`
- `production/docs/requirements.md` → `docs/requirements.md`
- `production/docs/ALIGNMENT_CHECKLIST.md` → `docs/ALIGNMENT_CHECKLIST.md`
- `production/docs/` folder deleted

### Files Updated
- ✅ `.github/copilot-instructions.md` - Updated all paths to reflect new structure
- ✅ `README.md` - Updated doc references
- ✅ `DEVELOPMENT.md` - Updated doc references
- ✅ `QUICK_REFERENCE.md` - Updated doc references
- ✅ `REPOSITORY_STRUCTURE.md` - Updated structure diagram
- ✅ `REORGANIZATION.md` - Updated with new info

## Path Reference

### Old Paths → New Paths

| Old | New |
|-----|-----|
| `production/docs/architecture.md` | `docs/architecture.md` |
| `production/docs/requirements.md` | `docs/requirements.md` |
| `data/sql/sql_tables.sql` | `development/data/sql/sql_tables.sql` |
| `scripts/smoke.php` | `development/scripts/smoke.php` |

## Documentation Files

### Architecture & Design
- **`docs/architecture.md`** - System architecture, MVC structure, data flow
- **`docs/requirements.md`** - Feature specifications and requirements
- **`docs/ALIGNMENT_CHECKLIST.md`** - Implementation checklist and alignment

### Setup Guides
- **`README.md`** - Quick overview and quick start
- **`DEVELOPMENT.md`** - Complete local development setup
- **`DEPLOYMENT.md`** - Production server deployment guide
- **`QUICK_REFERENCE.md`** - Cheat sheet for common tasks

### Organization
- **`REPOSITORY_STRUCTURE.md`** - Detailed folder structure
- **`REORGANIZATION.md`** - History of changes and why

## Key Points

1. **For Developers:**
   - See `DEVELOPMENT.md` for local setup
   - See `docs/architecture.md` for system design
   - See `QUICK_REFERENCE.md` for common commands

2. **For Deployment:**
   - Copy only `production/` folder to server
   - Follow `DEPLOYMENT.md` for setup
   - Use schema from `development/data/sql/`

3. **For AI Agents:**
   - See `.github/copilot-instructions.md` for guidelines
   - All paths updated to reflect new structure
   - Production code is self-contained in `production/`

## Deployment Checklist

✅ Docs moved to root-level `docs/` folder  
✅ All documentation files updated  
✅ All path references in guides updated  
✅ Copilot instructions updated  
✅ Old `production/docs/` removed  
✅ Directory structure verified  
✅ All key files present and accessible  

---

For quick start, see [README.md](README.md)  
For detailed setup, see [DEVELOPMENT.md](DEVELOPMENT.md)  
For deployment, see [DEPLOYMENT.md](DEPLOYMENT.md)  
For architecture, see [docs/architecture.md](docs/architecture.md)
