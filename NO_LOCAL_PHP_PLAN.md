# No-Local-PHP Requirement Plan

**Updated:** January 24, 2026  
**Goal:** All local development setup tools runnable via PowerShell or Docker without requiring PHP to be installed locally.

---

## Current State Analysis

### Existing Setup Scripts
Located in `development/scripts/`:
- `setup-db.php` — Database initialization (PHP)
- `check_csvs.php` — CSV validation (PHP)
- `seed_demo.php` — Session seeding (PHP)
- `smoke.php` — Smoke testing (PHP)

**Problem:** All require PHP CLI to be installed locally.

### Existing Docker Support
- `local/docker/docker-compose.yml` — Full dev environment
- `local/docker/Dockerfile` — PHP 8.3 image with MySQL
- `local/docker/run_tests_docker.ps1` — Test runner for Docker
- `local/docker/run_tests_docker.sh` — Test runner for Docker

**Good:** Docker option exists but isn't the default workflow.

---

## Target Workflows (Post-Implementation)

### ✅ Workflow 1: Pure PowerShell (Windows Users)

**Requirements:** PowerShell 5.1+, Docker Desktop (for database)  
**No Local PHP Required:** ✅  
**Setup time:** ~30 seconds

```powershell
# One-time setup
.\scripts\Initialize-Development.ps1

# Start development
.\scripts\Start-Development.ps1

# Run any setup tool (via Docker)
.\scripts\Setup-Database.ps1
.\scripts\Validate-Csvs.ps1
.\scripts\Seed-DemoData.ps1
.\scripts\Test-Application.ps1
```

### ✅ Workflow 2: Docker CLI (Linux/Mac/Windows)

**Requirements:** Docker, Docker Compose  
**No Local PHP Required:** ✅  
**Setup time:** ~1-2 minutes (first build)

```bash
# Start development environment
docker compose -f local/docker/docker-compose.yml up -d

# Run setup inside container
docker compose -f local/docker/docker-compose.yml exec web php development/scripts/setup-db.php

# Run tests inside container
docker compose -f local/docker/docker-compose.yml exec web vendor/bin/phpunit

# Or use convenience script
./scripts/setup-db.sh --docker
```

### ✅ Workflow 3: Hybrid PowerShell + Docker (Recommended for Windows)

**Requirements:** PowerShell 5.1+, Docker Desktop  
**No Local PHP Required:** ✅  
**Setup time:** ~30 seconds + first Docker build

```powershell
# Initialize and start dev environment
.\scripts\Initialize-Development.ps1

# Now use any setup tool (runs in Docker automatically)
.\scripts\Setup-Database.ps1 --seed
.\scripts\Validate-Csvs.ps1
.\scripts\Start-Development.ps1
```

---

## Implementation Plan

### Phase 1: Create PowerShell Setup Scripts

#### `scripts/Initialize-Development.ps1` (NEW)
- Checks for Docker
- Downloads/builds Docker images
- Creates `local/docker-compose.dev.yml` with current paths
- Provides feedback on next steps

#### `scripts/Start-Development.ps1` (NEW)
- Starts Docker containers
- Waits for MySQL to be ready
- Displays access URLs
- Provides troubleshooting tips

#### `scripts/Setup-Database.ps1` (NEW)
- Wrapper for `setup-db.php`
- Runs via Docker if PHP not local
- Supports `--seed` flag
- Supports `--config` flag
- Handles custom database paths

#### `scripts/Validate-Csvs.ps1` (NEW)
- Wrapper for `check_csvs.php`
- Pure PowerShell option available
- Docker fallback if not available

#### `scripts/Seed-DemoData.ps1` (NEW)
- Wrapper for `seed_demo.php`
- Runs via Docker
- Initializes session with test data

#### `scripts/Test-Application.ps1` (NEW)
- Wrapper for `vendor/bin/phpunit`
- Runs locally if PHP available
- Falls back to Docker
- Supports `--testsuite` filtering

#### `scripts/Stop-Development.ps1` (NEW)
- Stops Docker containers
- Cleans up volumes (optional)
- Provides cleanup instructions

### Phase 2: Create Bash/Shell Scripts (For Linux/Mac)

#### `scripts/setup-db.sh` (NEW)
- Bash equivalent of `Setup-Database.ps1`
- Auto-detect Docker vs local PHP
- Support same flags as PowerShell version

#### `scripts/validate-csvs.sh` (NEW)
- Bash equivalent of `Validate-Csvs.ps1`
- Pure bash CSV validation option

#### `scripts/seed-demo.sh` (NEW)
- Bash equivalent of `Seed-DemoData.ps1`

#### `scripts/test-application.sh` (NEW)
- Bash equivalent of `Test-Application.ps1`

#### `scripts/initialize-development.sh` (NEW)
- Bash equivalent of `Initialize-Development.ps1`

### Phase 3: Docker Enhancements

#### `local/docker/Dockerfile` (Update)
- Add setup-db.php as entrypoint option
- Include composer in image
- Add healthcheck for MySQL readiness

#### `local/docker/docker-compose.yml` (Update)
- Add environment for database seeding
- Add healthcheck service
- Add convenience volume for scripts

#### `local/docker/entrypoint.sh` (NEW)
- Smart entrypoint for Docker
- Can run PHP scripts or start web service
- Auto-detects command intent

### Phase 4: Documentation Updates

#### `docs/DEVELOPMENT.md` (Update)
- Emphasize PowerShell-first for Windows users
- Show Docker-first for Linux/Mac
- Update all code examples to use new scripts
- Remove references to local PHP installation

#### `docs/NO_LOCAL_PHP.md` (NEW)
- Detailed explanation of no-local-PHP approach
- Troubleshooting Docker issues
- Performance notes
- Enterprise proxy/network considerations

#### `QUICK_COMMANDS.md` (Update)
- Replace PHP commands with PowerShell/Bash
- Show three workflows side-by-side
- Update examples

#### `.github/copilot-instructions.md` (Update)
- Update developer workflow section
- Recommend PowerShell-first for Windows

### Phase 5: Helper Modules

#### `scripts/lib/Docker-Helper.ps1` (NEW)
PowerShell module for Docker operations:
```powershell
- Test-Docker
- Start-DockerServices
- Stop-DockerServices
- Invoke-PhpInDocker
- Wait-ForMySql
- Get-DockerStatus
```

#### `scripts/lib/Php-Helper.ps1` (NEW)
PowerShell module for PHP detection:
```powershell
- Get-PhpAvailable
- Invoke-PhpLocally
- Test-Composer
- Get-PhpVersion
```

#### `scripts/lib/setup-helpers.sh` (NEW)
Bash helper functions:
```bash
- detect_docker()
- run_php_command()
- wait_for_mysql()
- docker_status()
```

---

## File Structure (Post-Implementation)

```
scripts/                      ← NEW: User-facing entry points
├── Initialize-Development.ps1
├── Start-Development.ps1
├── Setup-Database.ps1
├── Validate-Csvs.ps1
├── Seed-DemoData.ps1
├── Test-Application.ps1
├── Stop-Development.ps1
├── lib/                       ← Helper modules
│   ├── Docker-Helper.ps1
│   └── Php-Helper.ps1
├── setup-db.sh                ← Bash equivalents
├── validate-csvs.sh
├── seed-demo.sh
├── test-application.sh
├── initialize-development.sh
└── lib/
    └── setup-helpers.sh

development/scripts/          ← Keep existing PHP scripts
├── setup-db.php              ← Still used by Docker
├── check_csvs.php
├── seed_demo.php
├── smoke.php
└── (run via Docker only)

local/docker/
├── Dockerfile                ← Updated
├── docker-compose.yml        ← Updated
└── entrypoint.sh             ← NEW
```

---

## Feature Comparison

| Feature | PowerShell (Windows) | Docker (Any OS) | Local PHP |
|---------|---------------------|-----------------|-----------|
| Setup time | 30s | 1-2m | 5m |
| Local install | None | Docker only | PHP + MySQL |
| Performance | Fast | Normal | Fastest |
| Complexity | Simple | Moderate | Low |
| Cross-platform | Windows only | All platforms | All platforms |
| IDE debugging | Possible | Limited | Excellent |
| Production-like | No | Yes | Depends |

---

## Implementation Sequence

### Week 1: Core PowerShell Scripts
1. Create `scripts/lib/Docker-Helper.ps1`
2. Create `scripts/lib/Php-Helper.ps1`
3. Create `scripts/Initialize-Development.ps1`
4. Create `scripts/Start-Development.ps1`
5. Create `scripts/Stop-Development.ps1`

### Week 2: Setup Wrappers
1. Create `scripts/Setup-Database.ps1`
2. Create `scripts/Validate-Csvs.ps1`
3. Create `scripts/Seed-DemoData.ps1`
4. Create `scripts/Test-Application.ps1`

### Week 3: Shell Scripts
1. Create `scripts/lib/setup-helpers.sh`
2. Create all bash equivalents

### Week 4: Docker & Documentation
1. Update `local/docker/Dockerfile`
2. Create `local/docker/entrypoint.sh`
3. Update all documentation
4. Update `QUICK_COMMANDS.md`

---

## Benefits

✅ **No Local PHP Requirement** — Use Docker or PowerShell  
✅ **Faster Onboarding** — Skip PHP/MySQL installation  
✅ **Consistent Environment** — Docker guarantees same setup everywhere  
✅ **Windows-Friendly** — PowerShell scripts for native Windows users  
✅ **Cross-Platform** — Shell scripts for Linux/Mac users  
✅ **Developer Choice** — Pick PowerShell or Docker based on preference  
✅ **Production-Like** — Docker option mirrors production closer  
✅ **Enterprise Ready** — Handles proxy/network constraints  

---

## Backward Compatibility

✅ Existing PHP scripts remain unchanged  
✅ Existing Docker compose file still works  
✅ Local PHP installation still supported (optional)  
✅ Can mix local PHP with Docker database  
✅ All old workflows continue to function  

---

## Success Criteria

- [ ] Developer can start work with ZERO local PHP installed
- [ ] Setup time is < 1 minute after initial Docker pull
- [ ] PowerShell scripts work on Windows 10+ 
- [ ] Bash scripts work on macOS and Linux
- [ ] `docker compose up -d` is optional (PowerShell provides simpler UI)
- [ ] All docs reflect no-local-PHP approach
- [ ] Setup errors are clear and actionable

---

## Next Steps

1. Review and approve this plan
2. Begin Phase 1: Create core PowerShell helper modules
3. Implement Setup-Database.ps1 as proof-of-concept
4. Test with clean Windows machine (no PHP installed)
5. Iterate based on feedback
6. Roll out remaining scripts
7. Update documentation
8. Final testing on multiple OS platforms

---

## Open Questions

1. **Default for Windows users?** PowerShell or Docker?
   - Recommendation: PowerShell (simpler, no container overhead)
   
2. **Default for Linux/Mac users?** Docker or local PHP?
   - Recommendation: Docker (guaranteed consistency)

3. **Should we auto-start Docker?** Or require manual `up -d`?
   - Recommendation: Auto-start with user confirmation

4. **GitHub Codespaces support?** Special handling needed?
   - Recommendation: Detect and provide `.devcontainer/` setup

5. **Enterprise proxy support?** How to handle?
   - Recommendation: Document environment variables, provide examples
