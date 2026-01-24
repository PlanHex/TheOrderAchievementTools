# Development Guide

This guide covers setting up the project for local development.

## Project Structure

```
.
├── production/          # Production code (deploy this to servers)
│   ├── config/
│   ├── public/
│   ├── src/
│   ├── templates/
│   └── composer.json
├── development/         # Development tools and data
│   ├── data/           # CSV files and demo data
│   └── scripts/        # Utility scripts
├── local/              # Local development infrastructure
│   ├── docker/         # Docker setup
│   └── tests/          # PHPUnit tests
├── README.md           # Quick overview
└── DEPLOYMENT.md       # Production deployment guide
```

## Quick Start (No Docker)

### Prerequisites
- PHP 8.1+
- Composer (optional, for dev tools)

### 1. Start Development Server

```powershell
cd production
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000` in your browser.

### 2. Switch Modes

**Demo Mode (Default)**
- Edit `production/config/app.php` and set `'mode' => 'demo'`
- The app reads CSV files from `data/`
- Changes are stored in PHP session (not persistent)

**Production Mode (MySQL)**
- Install MySQL 8.0+
- Create a database: `CREATE DATABASE achievements;`
- Import schema: `mysql achievements < data/sql/sql_tables.sql`
- Update `production/config/database.php` with your credentials
- Edit `production/config/app.php` and set `'mode' => 'production'`

### 3. Run Tests

```powershell
cd production
composer install --dev
./vendor/bin/phpunit
```

### 4. Run Development Utilities

```powershell
# Validate CSV files
php development/scripts/check_csvs.php

# Run smoke tests
php development/scripts/smoke.php

# Seed demo data into session
php development/scripts/seed_demo.php
```

---

## Quick Start (With Docker)

### Prerequisites
- Docker Desktop (or Docker Engine + Docker Compose)

### 1. Start Services

```powershell
cd local/docker
docker compose up --build -d
```

The app will be available at `http://localhost:8000/`

### 2. Access MySQL from Host

```powershell
mysql -h 127.0.0.1 -u app -p -D order_achievements
# Password: secret
```

### 3. Run Tests in Docker

```powershell
cd local/docker
docker compose exec web php vendor/bin/phpunit
```

### 4. Stop Services

```powershell
cd local/docker
docker compose down
```

---

## Code Organization

### Feature Modules

Each feature lives in `production/src/Modules/<Feature>/`:

- **Controller/** — Handles HTTP requests
- **Domain/** — Entity definitions
- **Repository/** — Data access interface
- **Views/** — HTML templates

Example: `production/src/Modules/Achievement/`

### Core Services

Located in `production/src/Core/`:

- **Container.php** — Dependency injection (wires repositories based on mode)
- **Router.php** — Routes URLs to controllers
- **Renderer.php** — Renders views with data
- **Database.php** — PDO wrapper
- **Auth.php** — Basic authentication
- **Csrf.php** — CSRF protection

### Persistence Layer

Located in `production/src/Infrastructure/Persistence/`:

- **MySQL/** — Real database implementations
- **InMemory/** — CSV + session implementations (demo mode)

Both implement the same interfaces, so controllers are agnostic to persistence.

---

## Common Tasks

### Add a New Achievement Field

1. Update `production/src/Modules/Achievement/Domain/Achievement.php`
2. Update both repository implementations:
   - `production/src/Infrastructure/Persistence/MySQL/AchievementRepository.php`
   - `production/src/Infrastructure/Persistence/InMemory/AchievementRepository.php`
3. Update `data/achievements.csv` if in demo mode
4. Update the views in `production/src/Modules/Achievement/Views/`

### Add a New Route

1. Edit `production/public/index.php` to add the route
2. Create/update the corresponding controller method
3. Add or update the view file

### Modify a View

Edit the `.php` files in `production/src/Modules/<Feature>/Views/`

Remember to use `<?= $renderer->e($variable) ?>` to escape user data.

### Regenerate CSV Files

If you modify seed data, regenerate the CSVs:

```powershell
cd data/scripts
./csv_generator.ps1
```

---

## Debugging with Xdebug

### Docker Setup

Xdebug is pre-configured in the Docker image:
- Default client_host: `host.docker.internal`
- Default port: `9003`

Configure your IDE (VS Code, PhpStorm) to listen on port 9003.

### Local PHP Setup

If running PHP locally, install Xdebug:

```bash
pecl install xdebug
```

Add to `php.ini`:
```ini
zend_extension=xdebug
xdebug.mode=debug
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
```

---

## Testing

### Write a Test

Create a test file in `local/tests/Unit/` or `local/tests/Integration/`:

```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testSomething()
    {
        $this->assertTrue(true);
    }
}
```

### Run Tests

```powershell
cd production
./vendor/bin/phpunit --colors=always
```

Or run a specific test:

```powershell
./vendor/bin/phpunit local/tests/Unit/MyTest.php
```

---

## Deployment Checklist

Before deploying to production:

- [ ] Switch `production/config/app.php` to `'mode' => 'production'`
- [ ] Update `production/config/database.php` with production credentials
- [ ] Test with production database locally
- [ ] Run `php development/scripts/check_csvs.php` (should pass)
- [ ] Run `php development/scripts/smoke.php` (should pass)
- [ ] Run full test suite: `./vendor/bin/phpunit`

See [DEPLOYMENT.md](DEPLOYMENT.md) for server setup instructions.

---

## Troubleshooting

### Routes return 404 in demo mode
- Make sure `.htaccess` is in `production/public/`
- Check `config/app.php` mode is `'demo'`

### Database connection errors
- Verify MySQL is running
- Check credentials in `production/config/database.php`
- Ensure database exists

### CSV files not loading
- Run `php development/scripts/check_csvs.php`
- Verify CSV files exist in `data/`

---

## Contributing

When making changes:

1. Keep persistence logic behind repository interfaces
2. Update both MySQL and InMemory implementations
3. Use `$renderer->e()` for HTML escaping
4. Run tests before committing
5. Update documentation if adding features

---

For API and architecture details, see `docs/architecture.md`.
