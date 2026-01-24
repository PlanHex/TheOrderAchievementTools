# The Order  Achievements Tool

A PHP MVC application for managing forum achievements with dual-mode support (Demo/Production).

## Repository Structure

This repository is organized into three distinct areas:

### production/  Production Server Code
Everything needed to run the application on a server:
- config/  Application configuration (mode, database credentials, authentication)
- public/  Web root with entry point (index.php) and static assets
- src/  Application source code (Controllers, Domain, Repositories, Views)
- 	emplates/  Shared layout files
- composer.json  PHP dependencies

**Deploy this folder to your server** and configure config/app.php and config/database.php for your environment.

### development/  Development Tools & Data
Tools and data for local development:
- data/  CSV files for Demo mode and forum output files
- scripts/  Utility scripts:
  - smoke.php  Validate CSV loading and repository counts
  - seed_demo.php  Seed demo data into session
  - check_csvs.php  Verify CSV file integrity
  - csv_generator.ps1  Regenerate CSV files from seed data
  - csv_to_forumpost_generator.ps1  Generate forum-formatted output

### local/  Local Development & Testing Infrastructure
Docker and test setup for development and CI:
- docker/  Dockerfile and docker-compose.yml for local MySQL + Xdebug setup
- 	ests/  PHPUnit test suite

---

## Quick Start (Development)

### Without Docker

1. **Start the dev server:**
   `powershell
   php -S 127.0.0.1:8000 -t production/public
   `

2. **Open in browser:**
   `
   http://127.0.0.1:8000/
   `

3. **Run smoke tests:**
   `powershell
   cd production
   php ../development/scripts/smoke.php
   `

### With Docker

1. **Build and start:**
   `powershell
   cd local/docker
   docker compose up --build -d
   `

2. **Access the app:**
   `
   http://localhost:8000/
   `

---

## Configuration

**Development (Demo Mode):**
- Edit production/config/app.php and set 'mode' => 'demo'
- Demo mode reads CSV files from development/data/ and stores changes in session

**Production (MySQL Mode):**
- Edit production/config/app.php and set 'mode' => 'production'
- Update production/config/database.php with your MySQL credentials
- Run the SQL schema from development/data/sql/sql_tables.sql

---

## Testing

### Run Tests Locally

`powershell
cd production
composer install --dev
./vendor/bin/phpunit --colors=always
`

### Run Tests in Docker

`powershell
cd local/docker
docker compose run --rm app php ./vendor/bin/phpunit
`

---

## Architecture

- **Feature-First MVC:** Each feature (Achievement, Category, User) lives under src/Modules/<Feature>/
- **Dual Persistence:** Repositories have MySQL and InMemory (CSV+Session) implementations
- **Dependency Injection:** src/Core/Container.php wires services based on configuration

See `docs/architecture.md` for detailed design documentation.

---

## Key Files Reference

| Path | Purpose |
|------|---------|
| production/config/app.php | Mode selection & auth config |
| production/config/database.php | MySQL credentials |
| production/public/index.php | Front controller & routes |
| production/src/Core/Container.php | Dependency injection |
| development/data/ | CSV data files & forum outputs |
| development/scripts/ | Utility scripts |
| local/docker/docker-compose.yml | Docker setup |

---

## For Deployment

1. Copy the entire production/ folder to your server
2. Update config/app.php and config/database.php
3. Run the SQL schema to initialize the database (if using production mode)
4. Point your web server to production/public/

---

## License

See LICENSE file.
