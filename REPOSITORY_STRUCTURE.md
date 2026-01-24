# Repository Organization

This document describes the reorganized repository structure.

## Overview

The repository has been reorganized to clearly separate **production code** from **development tools** and **local infrastructure**.

### Three Main Areas

```
TheOrderAchievementTools/
├── production/          ← Deploy this to servers
├── development/         ← Local development tools and data
├── local/               ← Local/CI infrastructure (Docker, tests)
├── docs/                ← Documentation (architecture, requirements)
└── [root files]         ← Project config and guides
```

## Production (`production/`)

**Everything needed to run the application on a server.**

```
production/
├── config/              # Configuration files
│   ├── app.php         # Mode, auth settings
│   └── database.php    # MySQL credentials
├── public/              # Web root
│   ├── index.php       # Front controller
│   └── assets/         # CSS, JS
├── src/                 # Application source code
│   ├── Core/           # Router, Container, Renderer, Database, Auth, Csrf
│   ├── Modules/        # Feature modules (Achievement, Category, User, Auth)
│   └── Infrastructure/ # Persistence implementations (MySQL, InMemory)
├── templates/           # Shared layout files
└── composer.json        # PHP dependencies
```

**To deploy:** Copy the entire `production/` folder to your server and configure `config/app.php` and `config/database.php`.

**To develop:** Start with `php -S 127.0.0.1:8000` from the `production/` directory.

## Development (`development/`)

**Tools and data for local development.**

```
development/
├── data/
│   ├── achievements.csv
│   ├── categories.csv
│   ├── users.csv
│   ├── user_achievements.csv
│   ├── forumdata/      # Forum output examples
│   └── sql/
│       └── sql_tables.sql  # Database schema
└── scripts/
    ├── smoke.php           # Validate CSV/repo counts
    ├── seed_demo.php       # Seed demo data
    ├── check_csvs.php      # CSV integrity check
    ├── csv_generator.ps1   # Regenerate CSVs
    └── csv_to_forumpost_generator.ps1  # Forum output generation
```

**Usage:**
- Run smoke tests: `php development/scripts/smoke.php`
- Regenerate CSVs: `cd development/scripts && .\csv_generator.ps1`
- Initialize DB: `mysql < development/data/sql/sql_tables.sql`

## Local (`local/`)

**Local development and testing infrastructure.**

```
local/
├── docker/
│   ├── Dockerfile       # PHP 8.3 + Apache + MySQL
│   └── docker-compose.yml  # Multi-container setup
└── tests/
    ├── bootstrap.php
    ├── Unit/            # Unit tests
    └── Integration/     # Integration tests
```

**Usage:**
- Start Docker: `cd local/docker && docker compose up --build -d`
- Run tests: `cd local/docker && docker compose exec web php vendor/bin/phpunit`

## Root Level Files

| File | Purpose |
|------|---------|
| `README.md` | Quick overview and repository structure |
| `DEVELOPMENT.md` | Detailed development setup guide |
| `DEPLOYMENT.md` | Production deployment instructions |
| `composer.json` | Root composer config (for dev dependencies) |
| `composer.lock` | Locked dependency versions |
| `phpunit.xml` | PHPUnit configuration |
| `LICENSE` | License file |
| `.gitignore` | Git ignore rules |
| `.gitattributes` | Git attributes |
| `.dockerignore` | Docker build ignore rules |

## How to Use

### For Local Development
1. Read [DEVELOPMENT.md](DEVELOPMENT.md)
2. Choose no-Docker or Docker setup
3. Development happens in `production/` directory
4. Use `development/scripts/` utilities as needed

### For Deployment
1. Read [DEPLOYMENT.md](DEPLOYMENT.md)
2. Copy `production/` to your server
3. Update `production/config/app.php` and `production/config/database.php`
4. Initialize database with `development/data/sql/sql_tables.sql`

### For Testing
1. Run locally: `cd production && ./vendor/bin/phpunit`
2. Run in Docker: `cd local/docker && docker compose exec web php vendor/bin/phpunit`

## Key Principles

- **Production code is isolated**: Only `production/` needs to be deployed
- **Development tools are separated**: `development/` is for local work only
- **Testing is self-contained**: `local/` includes all test infrastructure
- **Documentation is organized**: `docs/` at root contains architecture and requirements
- **Dual-mode persistence**: Both MySQL and CSV/session implementations work the same

## Directory Checklist

- ✅ `production/` contains all runnable code
- ✅ `development/` contains CSV data and utility scripts
- ✅ `local/` contains Docker and test setup
- ✅ `docs/` contains architecture and requirements documentation
- ✅ Old root-level `config/`, `data/`, `public/`, `scripts/`, `src/`, `templates/`, `tests/` have been moved
- ✅ Root-level files are documentation and config only

---

See [README.md](README.md) for quick start, [DEVELOPMENT.md](DEVELOPMENT.md) for local setup, and [DEPLOYMENT.md](DEPLOYMENT.md) for server deployment.
