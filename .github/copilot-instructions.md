# AI Agent Instructions for The Order Achievements Tool

## Architecture Overview

**No-Framework PHP/MySQL MVC** with dual-mode operation (production SQL or demo CSV):
- Controllers route to Domain models via Repository pattern
- All persistence logic abstracted through interfaces in `src/src/Modules/*/Repository/*Interface.php`
- Container in `src/src/Core/Container.php` injects SQL or CSV-backed implementations based on `src/config/app.php` mode
- Renderer in `src/src/Core/Renderer.php` wraps HTML views with `src/templates/{header,footer}.php`

**Critical constraint**: No external libraries. Standard PHP SPL only. PDO prepared statements mandatory for SQL injection prevention.

## Dual Mode Strategy (Production vs Demo)

| Mode | Data Source | Auth | Use Case |
|------|------------|------|----------|
| **production** | MySQL 8.0.44 via PDO | Basic Auth (credentials in app.php) | Live deployment |
| **demo** | CSV files in `data/` loaded into `$_SESSION` | Bypassed (auto-login) | Local development & testing |

When implementing features: write against `RepositoryInterface` contracts. The Container automatically swaps implementations—no code changes needed to switch modes.

## Project-Specific Conventions

### Repository Pattern
- All data access flows through interfaces: `RepositoryInterface.php`
- Implementations in `src/src/Infrastructure/Persistence/{MySQL,InMemory}/`
- MySQL repos use `Core\Database` (PDO wrapper) with prepared statements
- InMemory repos load CSV files via `CsvLoader.php` into session arrays
- **Critical**: Always sort results by `display_order ASC` in repository methods (not in views)

### Entity Model Classes
Located in `Modules/*/Domain/*.php`. Example `Achievement.php`:
- Public properties map directly to database columns (snake_case in DB, camelCase in PHP)
- Constructor accepts all fields + optional `$createdAt`
- `toArray()` method for serialization (maps camelCase back to snake_case)
- Domain classes are **value objects**—no business logic, just data containers

### Security Patterns
- **CSRF**: Call `Core\Csrf::token()` in views, validate with `Core\Csrf::validate($_POST['csrf_token'])` in controllers
- **XSS**: Always use `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` for user output in HTML/BBCode
- **SQL Injection**: PDO prepared statements only. Example: `$pdo->prepare("SELECT * FROM users WHERE id = ?")->execute([$id])`

### Display Ordering
- Entities have a `display_order` field (numeric)
- Repositories always return sorted results (`ORDER BY display_order ASC`)
- Views accept sorted lists directly—no re-sorting in templates
- Reordering UI: numeric input fields update `display_order`, then POST to controller

### View Rendering
- HTML views: `$renderer->renderWithLayout('src/Modules/Feature/Views/view_name', ['var' => $data])`
- BBCode/plaintext: `echo $renderer->render('src/Modules/Reports/Views/export', [], false)` (third param = false skips layout)
- All views receive data as array keys; access via `$var` directly

## Service Registration (Container)

Repositories are auto-registered in `Container::registerDefaults()` based on app mode:
```php
$this->set('category_repository', function ($c) use ($mode) {
    return ($mode === 'production') 
        ? new MySQL\CategoryRepository($c->get('database'))
        : new InMemory\CategoryRepository($dataDir);
});
```
Controllers fetch via: `$repo = $container->get('category_repository')` → guaranteed correct implementation for mode.

## Key Entry Points & Flows

1. **Front Controller**: `src/public/index.php` → PSR-4 autoloader + Container bootstrap + Router dispatch
2. **Database**: `src/config/database.php` (MySQL credentials). CSV loader in `Infrastructure/Persistence/InMemory/CsvLoader.php`
3. **CSV Format**: Headers must match DB column names (snake_case). Session key: `$_SESSION['<entity_name>s']` (plural)
4. **BBCode Export**: `Modules/Reports/Controller/ReportsController.php` → aggregates data → renders text views in `Modules/Reports/Views/`

## Development Workflow

**Local Docker**:
```bash
docker-compose -f local/docker/docker-compose.yml up
# DB initializes from data/sql/sql_tables.sql
# Test via http://localhost:8000 (demo mode by default)
```

**Toggle Modes Locally**: Edit `src/config/app.php` → change `'mode' => 'demo'` ↔ `'production'`

**Routing** (in `index.php`): 
- `$router->add('GET|POST', '/path', callable)` 
- Callables are closures capturing `$container`, `$renderer`
- POST requests: manually validate CSRF before executing state changes

## Data Model

| Entity | Key Fields | Notes |
|--------|-----------|-------|
| **Category** | `id`, `name`, `display_order` | One-to-Many → Achievements |
| **Achievement** | `id`, `title`, `description`, `points`, `image_url`, `category_id`, `display_order` | Many-to-One ← Category |
| **User** | `id`, `name` | Many-to-Many → Achievements (via User_Achievement) |
| **User_Achievement** | `user_id`, `achievement_id`, `display_order` (composite PK) | Manages user achievement assignments & ordering |

All `*_id` fields are foreign keys. Deletion cascades must be enforced in MySQL repos.

## Common Pitfalls

- ❌ Interpolating variables into SQL strings (use prepared statements)
- ❌ Forgetting `htmlspecialchars()` on user output
- ❌ Sorting in views instead of repositories
- ❌ Creating fixtures/seeds outside CSV data format (use `data/` CSVs for demo mode)
- ✅ Always validate CSRF tokens for POST
- ✅ Test both SQL & CSV modes if modifying persistence layer
