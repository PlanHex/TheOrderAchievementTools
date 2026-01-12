# The Order — Achievements Tool (Dev README)

Quick start (dev):

```powershell
php -S 127.0.0.1:8000 -t public
```

Open `http://127.0.0.1:8000/` in your browser.

Modes
- Edit `config/app.php` and set `'mode' => 'demo'` (default) or `'production'`.
- In production set DB credentials in `config/database.php` and optional Basic Auth in `config/app.php` under `'auth' => ['user' => 'name','pass' => 'secret']`.

Data
- Demo mode reads CSV files from `data/` and keeps changes in PHP session.
- Production mode uses MySQL schema in `sql/sql_tables.sql`.

Developer notes
- Views are under `src/Modules/<Feature>/Views/`.
- Repositories live in `src/Infrastructure/Persistence/{InMemory,MySQL}` and are chosen by `src/Core/Container.php` using `config/app.php` `mode`.
- Use `public/assets/js/sortable.js` to enable drag-and-drop ordering on lists with `data-id` attributes.

Smoke tests
- Run the basic smoke script to validate CSV loading and repository counts:

```powershell
php scripts/smoke.php
```

Running tests (PHPUnit)

1. Install dev dependencies:

```powershell
composer install --dev
```

2. Run PHPUnit:

```powershell
./vendor/bin/phpunit --colors=always
```

Server setup (PHP + MySQL)

These steps install and configure the application on a server that has PHP and MySQL available. Composer is not strictly required to run the app (the runtime code is self-contained), but it is needed for running tests and installing dev tools.

1. Copy the repository files to your webroot (e.g., `/var/www/achievements`).

2. Configure PHP and webserver:
	- Ensure `document root` points to the `public/` folder.
	- If using Apache, enable `mod_rewrite` and point a VirtualHost to the `public/` folder. For Nginx, configure the root and try_files to `index.php`.

3. Configure application:
	- Edit `config/database.php` with your MySQL credentials.
	- Edit `config/app.php` and set `'mode' => 'production'`.
	- Add credentials for Basic Auth in `config/app.php` (example):

```php
'auth' => [
	 'user' => 'admin',
	 'pass' => 'change-this-secret'
]
```

4. Initialize the database:

```bash
mysql -u root -p < sql/sql_tables.sql
```

Create the database and tables using the provided SQL. Then verify MySQL connectivity with the values in `config/database.php`.

5. File permissions:
	- Ensure the webserver user can read the project files. No special writable directories are required for production mode unless you add logging or uploads.

6. Optional: Install Composer (recommended for dev/test):

```bash
php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
php composer-setup.php
mv composer.phar /usr/local/bin/composer
composer install --no-dev --optimize-autoloader
```

7. Restart your webserver and visit your site.

If you run into environment-specific issues (permissions, PHP modules), collect `php -v`, `php -m`, and the webserver error logs and I can help troubleshoot.


If you want I can add more tests or CI config.
# TheOrderAchievementTools

