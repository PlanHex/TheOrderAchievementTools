# Data Folder Reorganization - Complete

**Date:** January 24, 2026

## Summary

The `data/` folder has been moved from `development/data/` to the root level (`data/`), and the CSV data directory path is now **configurable** in the application configuration.

## What Changed

### Folder Structure
```
Before:
development/
├── data/
│   ├── achievements.csv
│   ├── categories.csv
│   ├── user_achievements.csv
│   ├── users.csv
│   ├── forumdata/
│   ├── scripts/
│   └── sql/

After (NOW):
data/                    ← Root level
├── achievements.csv
├── categories.csv
├── user_achievements.csv
├── users.csv
├── forumdata/
├── scripts/
└── sql/
```

### Code Changes

#### 1. Configuration (`production/config/app.php`)
Added configurable data directory:
```php
'data_dir' => __DIR__ . '/../../data',
```

Can be customized to any path (absolute or relative):
```php
'data_dir' => '/var/data/achievements',  // Absolute path
'data_dir' => __DIR__ . '/../../../shared/data',  // Relative path
```

#### 2. Dependency Container (`production/src/Core/Container.php`)
Updated to read data path from configuration:
```php
$dataDir = $this->config['app']['data_dir'] ?? __DIR__ . '/../../data';
```

All repository instantiations now use the configurable path.

#### 3. Development Scripts
Updated paths:
- `development/scripts/smoke.php` - Now uses `../../data/`
- `development/scripts/seed_demo.php` - Now uses `../../data/`
- `development/scripts/check_csvs.php` - Now uses `../../data/`

#### 4. Docker Configuration
Updated volume mappings:
- **Dockerfile:** `COPY ../data /var/www/html/../data`
- **docker-compose.yml:** `- ../../data:/var/www/html/../data`

### All Updated Files
- ✅ `production/config/app.php` - Added `data_dir` config
- ✅ `production/src/Core/Container.php` - Reads `data_dir` from config
- ✅ `development/scripts/smoke.php` - Updated paths
- ✅ `development/scripts/seed_demo.php` - Updated paths
- ✅ `development/scripts/check_csvs.php` - Updated paths
- ✅ `local/docker/Dockerfile` - Updated COPY path
- ✅ `local/docker/docker-compose.yml` - Updated volume path
- ✅ `.github/copilot-instructions.md` - Updated references
- ✅ `README.md` - Updated references
- ✅ `DEVELOPMENT.md` - Updated references
- ✅ `QUICK_REFERENCE.md` - Updated references

## How to Use

### Default Setup (Root Level Data)
No changes needed - the app uses `production/config/app.php`:
```php
'data_dir' => __DIR__ . '/../../data',
```

The CSV files are automatically loaded from `/data/` at the repository root.

### Custom Data Location
To use a different data directory, update `production/config/app.php`:

```php
// Example 1: Use a different local location
'data_dir' => __DIR__ . '/../../shared/data',

// Example 2: Use an absolute path
'data_dir' => '/var/data/achievements',

// Example 3: Use an environment variable
'data_dir' => getenv('ACHIEVEMENTS_DATA_DIR') ?: __DIR__ . '/../../data',
```

## Benefits

1. **Separation of Concerns**
   - Data is no longer bundled with production code
   - Production folder can be deployed independently
   - Makes it clear what's production vs. development data

2. **Flexibility**
   - Data path is fully configurable
   - Can point to different directories per environment
   - Supports absolute and relative paths

3. **Scalability**
   - Can use shared network storage (NFS, S3, etc.)
   - Can point to different data for different deployments
   - Easy to implement environment-specific data

4. **Docker Friendly**
   - Docker volumes map correctly
   - Can mount data from different sources
   - Makes containerization cleaner

## File Locations

| File | Location | Purpose |
|------|----------|---------|
| CSV Data | `data/` | Demo mode data files |
| SQL Schema | `data/sql/sql_tables.sql` | Database initialization |
| Scripts | `data/scripts/` | CSV generation utilities |
| Forum Output | `data/forumdata/` | Generated forum posts |

## Accessing Data in Code

### In Production Code
The Container automatically provides the correct path to repositories:
```php
// In Controller
$categoryRepo = $container->get('category_repository');
// Internally uses the configured 'data_dir'
```

### In Scripts
Scripts can load the config and use it:
```php
$appConfig = require __DIR__ . '/../../production/config/app.php';
$dataDir = $appConfig['data_dir'];
```

## Testing the Configuration

To verify the data folder is accessible:
```bash
php development/scripts/check_csvs.php
```

To seed demo data:
```bash
php development/scripts/seed_demo.php
```

## Migration Notes

- Old references to `development/data/` have been updated to `data/`
- Scripts now reference the root-level `data/` folder
- No changes required to production code if using default config
- Docker volume mappings updated for new location

---

**All systems operational!** The data folder is now properly organized at the root level with a configurable path in the application.
