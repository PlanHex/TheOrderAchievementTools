# Getting Started - No Local PHP Required ✨

**TL;DR:** One command to set up everything without needing PHP installed locally.

---

## ⚡ Quick Start (30 seconds)

### Prerequisites
- **Windows 10+** with **PowerShell 5.1+**
- **Docker Desktop** (optional, but recommended)

### Setup

```powershell
# Navigate to project root
cd path\to\achievements

# ONE COMMAND to set up everything
.\scripts\Initialize-Development.ps1

# Then start developing
.\scripts\Start-Development.ps1

# Visit http://127.0.0.1:8000 in your browser
```

That's it! You're ready to develop without installing PHP locally.

---

## 📋 What Each Script Does

### `Initialize-Development.ps1`
Sets up your environment once:
```powershell
.\scripts\Initialize-Development.ps1
```
- ✅ Checks for Docker
- ✅ Starts containers
- ✅ Installs PHP dependencies
- ✅ Validates configuration
- ✅ Shows next steps

### `Start-Development.ps1`
Starts the dev environment:
```powershell
.\scripts\Start-Development.ps1
```
- Starts Docker (if not running)
- Shows access URLs
- Displays useful commands
- Use `-NoDocker` for local PHP

### `Setup-Database.ps1`
Initialize or reset database:
```powershell
.\scripts\Setup-Database.ps1 -Seed
```
- Creates database and tables
- `-Seed` option loads sample data
- `-DropExisting` to recreate (be careful!)

### `Test-Application.ps1`
Run tests:
```powershell
.\scripts\Test-Application.ps1
```
- Runs all tests
- `-Suite Unit` for unit tests only
- `-Suite Integration` for integration tests
- `-Coverage` to see code coverage

---

## 🎯 Three Development Modes

### 🐳 Mode 1: Docker Only (Recommended)

Use Docker for everything. No PHP needed on your machine.

```powershell
.\scripts\Initialize-Development.ps1              # Setup once
.\scripts\Start-Development.ps1                   # Start dev
.\scripts\Setup-Database.ps1 -Seed                # Init database
.\scripts\Test-Application.ps1                    # Run tests
```

**Pros:** No local install, consistent environment, easy cleanup  
**Cons:** Requires Docker Desktop

---

### 💻 Mode 2: Local PHP

If you have PHP installed, use it:

```powershell
.\scripts\Initialize-Development.ps1 -UseLocalPHP    # Setup
cd production
php -S 127.0.0.1:8000                                # Start server
.\scripts\Setup-Database.ps1 -UseLocal               # Init DB
.\scripts\Test-Application.ps1 -UseLocal             # Run tests
```

**Pros:** Faster, better IDE debugging, custom configs  
**Cons:** Need PHP 8.1+, MySQL, Composer installed

---

### 🔀 Mode 3: Hybrid (Smart Auto-Detection)

Scripts pick the best option automatically:

```powershell
.\scripts\Initialize-Development.ps1   # Detects Docker and PHP
.\scripts\Start-Development.ps1        # Uses Docker if available, else local
.\scripts\Test-Application.ps1         # Auto-detects method
```

**Pros:** Maximum flexibility, works anywhere  
**Cons:** Behavior depends on what's installed

---

## 🐛 Troubleshooting

### "Docker is not installed or not running"
```powershell
# Install Docker Desktop
# https://www.docker.com/products/docker-desktop

# Or use local PHP
.\scripts\Initialize-Development.ps1 -UseLocalPHP
```

### "PHP is not available"
```powershell
# Option 1: Use Docker (recommended)
# Option 2: Install PHP
# https://www.php.net/downloads

# Check what's available
.\scripts\Initialize-Development.ps1   # Shows what it found
```

### "Port 8000 already in use"
```powershell
.\scripts\Start-Development.ps1 -Port 8001
# Uses port 8001 instead
```

### "Composer is not available"
```powershell
# Install Composer
# https://getcomposer.org/download/

# Or just run tests (it will try to install via Docker)
.\scripts\Test-Application.ps1
```

---

## 📝 Common Workflows

### Start Fresh Day
```powershell
# Ensure containers are running
.\scripts\Start-Development.ps1

# Visit http://127.0.0.1:8000
```

### First Time Setup
```powershell
.\scripts\Initialize-Development.ps1
.\scripts\Start-Development.ps1
.\scripts\Setup-Database.ps1 -Seed
# Now edit files and visit http://127.0.0.1:8000
```

### Make Changes & Test
```powershell
# Make code changes in your editor
# Refresh browser to see changes

# Run tests
.\scripts\Test-Application.ps1

# Filter by test suite
.\scripts\Test-Application.ps1 -Suite Unit
```

### Reset Database
```powershell
# WARNING: This deletes all data!
.\scripts\Setup-Database.ps1 -Seed -DropExisting
```

### Stop Development
```powershell
# Stop Docker
docker compose -f local/docker/docker-compose.yml down

# Or use helper (when created)
# .\scripts\Stop-Development.ps1
```

---

## 🎓 Understanding the Scripts

### Helper Modules (in `scripts/lib/`)

**Docker-Helper.psm1** — Docker operations
```powershell
Import-Module .\scripts\lib\Docker-Helper.psm1
Test-Docker                    # Check if Docker installed
Start-DockerServices           # Start containers
Show-DockerStatus             # Show status
```

**Php-Helper.psm1** — PHP detection
```powershell
Import-Module .\scripts\lib\Php-Helper.psm1
Test-PhpAvailable             # Check if PHP installed
Get-PhpVersion                # Get version info
Test-ComposerAvailable        # Check Composer
```

### Main Scripts

All main scripts automatically import these modules and use them to decide:
- Use Docker? ✓
- Use local PHP? ✓
- Do both? ✓

---

## 📚 Full Documentation

- **Detailed Guide:** `docs/DEVELOPMENT.md`
- **Strategy Overview:** `TESTING_OPTIONS.md`
- **Implementation Details:** `NO_LOCAL_PHP_IMPLEMENTATION.md`
- **Technical Plan:** `NO_LOCAL_PHP_PLAN.md`

---

## ✅ You're All Set!

Run this once:
```powershell
.\scripts\Initialize-Development.ps1
```

Then use these daily:
```powershell
.\scripts\Start-Development.ps1       # Start dev server
.\scripts\Setup-Database.ps1          # Initialize DB
.\scripts\Test-Application.ps1        # Run tests
```

**No PHP installation needed!** 🎉

---

## 💡 Pro Tips

1. **Set PowerShell execution policy** (first time only):
   ```powershell
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
   ```

2. **Docker Desktop runs at startup** (on most systems)
   - Check System Tray for Docker icon
   - Right-click → Settings for preferences

3. **Database persists** between restarts
   - Data stays even when containers stop
   - Use `-DropExisting` flag to reset

4. **Access Docker container directly**:
   ```powershell
   docker exec -it <app_container> bash
   ```

5. **View container logs**:
   ```powershell
   docker compose -f local/docker/docker-compose.yml logs -f web
   ```

---

## Still Have Questions?

See the full [Development Guide](docs/DEVELOPMENT.md) or check [Implementation Details](NO_LOCAL_PHP_IMPLEMENTATION.md).
